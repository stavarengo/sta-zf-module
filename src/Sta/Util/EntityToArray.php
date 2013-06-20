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
	 * @param int $depth
	 *
	 * @return array
	 */
	public function convert($entity, $depth = 0)
	{
		if ($entity instanceof AbstractEntity) {
			return $this->_convert($entity, $depth);
		} else {
			$result = array();
			foreach ($entity as $item) {
				$result[] = $this->_convert($item, $depth);
			}
			return $result;
		}
	}

	/**
	 * @param AbstractEntity $entity
	 *
	 * @param $depth
	 *
	 * @return array
	 */
	private function _convert(AbstractEntity $entity, $depth)
	{
		$em              = $this->em;
		$entityClass     = get_class($entity);
		$classMetadata   = $em->getClassMetadata($entityClass);
		$fieldMappings   = $classMetadata->fieldMappings;
		$return          = array();

		foreach ($fieldMappings as $fieldName => $fieldDefinition) {
//			$reflection = new \ReflectionProperty($entityClass, $fieldName);
//			$reflection->setAccessible(true);

//			$fieldValue  = $reflection->getValue($entity);
			$fieldValue = $entity->get($fieldName);
			$type        = ($fieldDefinition && isset($fieldDefinition['type']) ? strtolower($fieldDefinition['type']) : null);

			$return[$fieldName] = $this->_convertFieldValue($fieldValue, $type, $depth);
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
			$return[$fieldName] = $this->_convertFieldValue($fieldValue, null, $depth);
		}

		return $return;
	}

	private function _convertFieldValue($fieldValue, $fieldType, $depth)
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
					$returnValue = $this->_convertSubEntities($fieldValue, $depth);
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

	private function _convertSubEntities($subEntity, $depth)
	{
		$returnValue = null;
		if ($subEntity instanceof \Doctrine\ORM\Proxy\Proxy || $subEntity instanceof AbstractEntity) {
			$returnValue = $this->_getDepthEntityOrJustId($subEntity, $depth);
		} else if ($subEntity instanceof \Doctrine\Common\Collections\Collection) {
				$returnValue = array();
				foreach ($subEntity as $subitem) {
					$returnValue[] = $this->_getDepthEntityOrJustId($subitem, $depth);
				}
		} else {
			// @TODO O que fazer nesta situação?
		}
		return $returnValue;
	}

	private function _getDepthEntityOrJustId(AbstractEntity $entity, $depth)
	{
		if ($depth > 0) {
			return $this->_convert($entity, $depth - 1);
		} else {
			// Mesmo que a entidade seja uma instãncia de \Doctrine\ORM\Proxy\Proxy (Lazzy Load), podemos pegar o ID
			// da entidade, sem que ela ja carregada do DB, visto que o ID já foi carregado quando o proxy foi criado.
			return $entity->getId();
		}
	}

}
