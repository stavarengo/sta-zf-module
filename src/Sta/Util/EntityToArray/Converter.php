<?php


namespace Sta\Util\EntityToArray;


use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\DateTimeTzType;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\TimeType;
use Doctrine\ORM\EntityManager;
use Sta\Entity\AbstractEntity;
use Zend\Di\ServiceLocator;
use Zend\ServiceManager\ServiceManager;

/**
 * @author: Stavarengo
 */
class Converter
{

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;
	/**
	 * @var \Zend\ServiceManager\ServiceManager
	 */
	protected $serviceLocator;

	/**
	 * @var \Zend\Stdlib\RequestInterface
	 */
	protected $request;
	
	/**
	 * @var ConverterOptions
	 */
	protected $options;
	
	/**
	 * @param AbstractEntity $entity
	 * @param ConverterOptions $options
	 *
	 * @return array
	 */
	public function convert(AbstractEntity $entity)
	{
		$options       = $this->getOptions();
		$em            = $this->em;
		$entityClass   = get_class($entity);
		$classMetadata = $em->getClassMetadata($entityClass);
        $entityName    = $this->_getEntityName($entity);
		$fieldMappings = $classMetadata->fieldMappings;
		$return        = array();
		$noEntityName  = $options->getNoEntityName();

		foreach ($fieldMappings as $fieldName => $fieldDefinition) {
			$fieldValue = $entity->get($fieldName);
			$type       = ($fieldDefinition && isset($fieldDefinition['type']) ? strtolower($fieldDefinition['type']) : null);
			
			$return[$fieldName] = $this->_convertFieldValue($fieldValue, $type);
		}

		$tmpAssocMappings = $classMetadata->associationMappings;
		foreach ($tmpAssocMappings as $fieldName => $fieldDefinition) {
			if (!$fieldDefinition['isOwningSide']) {
				continue;
			}
			$fieldValue         = $entity->get($fieldName);
			$return[$fieldName] = $this->_convertFieldValue($fieldValue, null);
		}

		if ($noEntityName == false) {
			$return = array('_en' => $entityName, $entityName => $return);
		}
		return $return;
	}

	/**
	 * @param string $name
	 * @param any $value
	 */
	public function setOption($name, $value)
	{
		$this->getOptions()->set($name, $value);
	}

	/**
	 * @return ConverterOptions
	 */
	public function getOptions()
	{
		if (!$this->options) {
			$this->options = new ConverterOptions($this->getDefaultOptionsValues());
		}
		return $this->options;
	}
	
	protected function _convertFieldValue($fieldValue, $fieldType)
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
					$returnValue = $this->_convertSubEntities($fieldValue);
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

	protected function _convertSubEntities($subEntity)
	{
		$returnValue = null;
		if ($subEntity instanceof \Doctrine\ORM\Proxy\Proxy || $subEntity instanceof AbstractEntity) {
			$returnValue = $this->_getDepthEntityOrJustId($subEntity);
		} else if ($subEntity instanceof \Doctrine\Common\Collections\Collection) {
			$returnValue = array();
			foreach ($subEntity as $subitem) {
				$returnValue[] = $this->_getDepthEntityOrJustId($subitem);
			}
		} else {
			// @TODO O que fazer nesta situação?
		}
		return $returnValue;
	}

	protected function _getDepthEntityOrJustId(AbstractEntity $entity)
	{
		$options = $this->getOptions();
		$depth = $options->getDepth();
		if ($depth > 0) {
			return $this->serviceLocator->get('Sta\Util\EntityToArray')->convert($entity, array(
				'depth' => --$depth,
			));
		} else {
			// Mesmo que a entidade seja uma instãncia de \Doctrine\ORM\Proxy\Proxy (Lazzy Load), podemos pegar o ID
			// da entidade, sem que ela ja carregada do DB, visto que o ID já foi carregado quando o proxy foi criado.
			if ($options->getNoEntityName()) {
				return $entity->getId();
			} else {
                $entityName = $this->_getEntityName($entity);
				return array('_en' => $entityName, $entityName => array('id' => $entity->getId()));
			}
		}
	}

	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 */
	public function setEm(\Doctrine\ORM\EntityManager $em)
	{
		$this->em = $em;
	}

	/**
	 * @param \Zend\ServiceManager\ServiceManager $serviceLocator
	 */
	public function setServiceLocator(\Zend\ServiceManager\ServiceManager $serviceLocator)
	{
		$this->serviceLocator = $serviceLocator;
	}

	/**
	 * @param \Zend\Stdlib\RequestInterface $request
	 */
	public function setRequest($request)
	{
		$this->request = $request;
	}

	/**
	 * @return \Doctrine\ORM\EntityManager
	 */
	public function getEm()
	{
		return $this->em;
	}

	/**
	 * @return \Zend\Stdlib\RequestInterface
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * @return \Zend\ServiceManager\ServiceManager
	 */
	public function getServiceLocator()
	{
		return $this->serviceLocator;
	}

	/**
	 * @return array
	 */
	protected function getDefaultOptionsValues()
	{
		return array(
			'depth'        => $this->getRequest()->getQuery('depth', 0),
			'noEntityName' => $this->getRequest()->getQuery('noEntityName', true),
		);
	}
    
    private function _getEntityName(AbstractEntity $entity)
    {
        $em            = $this->em;
        $entityClass   = get_class($entity);
        $classMetadata = $em->getClassMetadata($entityClass);
        $regexp        = '`^.*' . preg_quote($classMetadata->namespace . '\\`');
        return preg_replace($regexp, '', $entityClass);
    }
    
}
