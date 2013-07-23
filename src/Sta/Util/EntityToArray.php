<?php


namespace Sta\Util;


use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\DateTimeTzType;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\TimeType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\PersistentCollection;
use Sta\Entity\AbstractEntity;
use Zend\Di\ServiceLocator;
use Zend\ServiceManager\ServiceManager;
use Zend\XmlRpc\Value\DateTime;

/**
 * @author: Stavarengo
 */
class EntityToArray
{

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;
	/**
	 * @var \Zend\ServiceManager\ServiceManager
	 */
	private $serviceLocator;

	/**
	 * @param EntityManager $em
	 * @param ServiceManager $serviceManager
	 */
	public function __construct(EntityManager $em, ServiceManager $serviceManager)
	{
		$this->em             = $em;
		$this->serviceLocator = $serviceManager;
	}

	/**
	 * @param AbstractEntity|AbstractEntity[] $entity
	 *
	 * @param array $options
	 * 		Veja as opções válidas em {@link \Sta\Util\EntityToArray::_convert()}
	 *
	 * @return array
	 */
	public function convert($entity, array $options = array())
	{
		if ($entity instanceof AbstractEntity) {
			return $this->_convert($entity, $options);
		} else {
			$result = array();
			foreach ($entity as $item) {
				$result[] = $this->_convert($item, $options);
			}
			return $result;
		}
	}

	/**
	 * @param AbstractEntity $entity
	 *
	 * @param array $options
	 * 		Opções disponiveis:<ul>
	 * 			<li>depth: int - default 0 <br>
	 * 				Define a profundidade que que devemos alcançar nos relacionamentos entre as classes.
	 * 				Veja mais em {@link https://github.com/grandssistemas/sell/wiki/Parametro-depth}
	 * 			</li>
	 * 			<li>
	 * 				noEntityName: bool - default false <br>
	 * 				Quando true cada entidade estara relacionada a um atributo cujo nome é igual ao nome da entidade.
	 * 				Ex: abaixo temos uma entidade Produto, e o valor deste parâmetro é false.
	 * <pre>
	 * 	array(
	 *		'id' => 106,
	 *		'descricao' => 'Calça Masculina',
	 * 		'unidadeMedida' => array(
	 *			'id' => 15,
	 *			'nome' => 'Unidade',
	 *			'sigla' => 'UN',
	 * 		),
	 * 	);
	 * </pre>
	 * 				Agora, no exemplo abaixo, temos a mesma entidade exibida acima, porem o valor deste parametro é true.
	 * <pre>
	 * 	array(
	 * 		'Produto' => array(
	 *			'id' => 106 ,
	 *			'descricao' => 'Calça Masculina',
	 * 			'unidadeMedida' => array(
	 * 				'_en' => 'UnidadeMedida',
	 * 				'UnidadeMedida' => array(
	 *					'id' => 15,
	 *					'nome' => 'Unidade',
	 *					'sigla' => 'UN',
	 * 				),
	 * 			),
	 * 		),
	 * 	);
	 * </pre>
	 * 			</li>
	 * </ul>
	 * @return array
	 */
	private function _convert(AbstractEntity $entity, array $options = array())
	{
		
		$em              = $this->em;
		$entityClass     = get_class($entity);
		$classMetadata   = $em->getClassMetadata($entityClass);
		$regexp 		 = '`^.*' . preg_quote($classMetadata->namespace . '\\`');
		$entityName		 = preg_replace($regexp, '', $entityClass);
		$fieldMappings   = $classMetadata->fieldMappings;
		$return          = array();
		$noEntityName = $this->_getOptions($options, 'noEntityName', false);
		
		foreach ($fieldMappings as $fieldName => $fieldDefinition) {
//			$reflection = new \ReflectionProperty($entityClass, $fieldName);
//			$reflection->setAccessible(true);

//			$fieldValue  = $reflection->getValue($entity);
			$fieldValue = $entity->get($fieldName);
			$type        = ($fieldDefinition && isset($fieldDefinition['type']) ? strtolower($fieldDefinition['type']) : null);

			$return[$fieldName] = $this->_convertFieldValue($fieldValue, $type, $options);
		}

		$tmpAssocMappings = $classMetadata->associationMappings;
		foreach ($tmpAssocMappings as $fieldName => $fieldDefinition) {
//			$reflection = new \ReflectionProperty($entityClass, $fieldName);
//			$reflection->setAccessible(true);
//
//			$fieldValue         = $reflection->getValue($entity);
			if (!$fieldDefinition['isOwningSide']) {
				continue;
			}
			$fieldValue = $entity->get($fieldName);
			$return[$fieldName] = $this->_convertFieldValue($fieldValue, null, $options);
		}

		if ($noEntityName == false) {
			$return = array($entityName => $return);
		}
		return $return;
	}

	private function _convertFieldValue($fieldValue, $fieldType, array $options = array())
	{
		$config          = $this->serviceLocator->get('config');
		$dateTimeFormats = $config['webapp']['datetime'];
		$em              = $this->em;
		$returnValue     = null;

		if ($fieldValue !== null) {
			if (is_object($fieldValue)) {
				if ($fieldValue instanceof \DateTime) {
					$format = null;
					if ($fieldType == DateType::DATE) {
						$format = $dateTimeFormats['date'];
					} else if ($fieldType == TimeType::TIME) {
						$format = $dateTimeFormats['time'];
					} else if ($fieldType == DateTimeType::DATETIME) {
						$format = $dateTimeFormats['datetime'];
					} else if ($fieldType == DateTimeTzType::DATETIMETZ) {
						$format = $em->getConnection()->getDatabasePlatform()->getDateTimeTzFormatString();
					}
					if ($format !== null) {
						$returnValue = $fieldValue->format($format);
					}
				} else {
					$returnValue = $this->_convertSubEntities($fieldValue, $options);
				}
			} else {
				if (in_array($fieldType, array('string', 'text'))) {
					$returnValue = (string)$fieldValue;
				} else if (in_array($fieldType, array('integer', 'smallint', 'bigint'))) {
					$returnValue = (int)$fieldValue;
				} else if (in_array($fieldType, array('decimal', 'float', 'percentage', 'money'))) {
					$returnValue = (float)$fieldValue;
				}
			}
		}
		return ($returnValue === null ? $fieldValue : $returnValue);
	}

	private function _convertSubEntities($subEntity, array $options)
	{
		$returnValue = null;
		if ($subEntity instanceof \Doctrine\ORM\Proxy\Proxy || $subEntity instanceof AbstractEntity) {
			$returnValue = $this->_getDepthEntityOrJustId($subEntity, $options);
		} else if ($subEntity instanceof \Doctrine\Common\Collections\Collection) {
				$returnValue = array();
				foreach ($subEntity as $subitem) {
					$returnValue[] = $this->_getDepthEntityOrJustId($subitem, $options);
				}
		} else {
			// @TODO O que fazer nesta situação?
		}
		return $returnValue;
	}

	private function _getDepthEntityOrJustId(AbstractEntity $entity, array $options)
	{
		$depth = (int)$this->_getOptions($options, 'depth', 0);
		if ($depth > 0) {
			$options['depth'] = $depth - 1;
			return $this->_convert($entity, $options);
		} else {
			// Mesmo que a entidade seja uma instãncia de \Doctrine\ORM\Proxy\Proxy (Lazzy Load), podemos pegar o ID
			// da entidade, sem que ela ja carregada do DB, visto que o ID já foi carregado quando o proxy foi criado.
			if ($this->_getOptions($options, 'noEntityName', false)) {
				return $entity->getId();
			} else {
				$entityName = basename(get_class($entity));
				return array('_en' => $entityName, $entityName => array('id' => $entity->getId()));
			}
		}
	}

	/**
	 * @param array $options
	 * @param string $optionName
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	private function _getOptions(array $options, $optionName, $default = null)
	{
		return (array_key_exists($optionName, $options) ? $options[$optionName] : $default);
	}

}
