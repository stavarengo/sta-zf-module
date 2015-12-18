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
     * @param \Zend\ServiceManager\ServiceManager $serviceLocator
     * @param \DateTime $dateTime
     * @param string $selector
     * @throws Exception\InvalidArgumentException
     * @return mixed
     */
    public static function convertDateTimeToStr(ServiceManager $serviceLocator, \DateTime $dateTime, $selector = 'datetime')
    {
        $config          = $serviceLocator->get('config');
        $dateTimeFormats = $config['webapp']['datetime'];
        $format = null;
        if ($selector == 'date') {
            $format = $dateTimeFormats['date'];
        } else if ($selector == 'time') {
            $format = $dateTimeFormats['time'];
        } else if ($selector == 'datetime') {
            $format = $dateTimeFormats['datetime'];
        } else {
            throw new Exception\InvalidArgumentException('The parameter "$selector" should be one of "date", "time" or "datetime" values.');
        }

		$tzStr = str_replace('.',':', sprintf('%+06.2f', \Web\Module::getCurrTimeZone()));
		$timezoneFromCookie = \DateTime::createFromFormat('O', $tzStr)->getTimezone();
		$d1 = new \DateTime('now', $timezoneFromCookie);
		$offsetFromCookie = $d1->getOffset();
		$offsetFromDateTime = $dateTime->getOffset();

		if ($offsetFromCookie != $offsetFromDateTime) {
			$dateTime->setTimezone($timezoneFromCookie);
		}

		$str = $dateTime->format($format);

		return $str;
    }

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
		$entityClass   = \App\Entity\AbstractEntity::getClass($entity);
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
        
        $return = $this->processEntityArray($return, $entity);
		if ($noEntityName == false) {
			$return = array('_en' => $entityName, $entityName => $return);
		}
		return $return;
	}

    /**
     * As subclasses podem sobrescrever este método.
     * @param array $entityData
     * @param \Sta\Entity\AbstractEntity $entity
     * @return array
     */
    protected function processEntityArray(array $entityData, \Sta\Entity\AbstractEntity $entity)
    {
        return $entityData;
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
		$em              = $this->em;
		$returnValue     = null;

		if ($fieldValue !== null) {
			if (is_object($fieldValue)) {
				if ($fieldValue instanceof \DateTime) {
					$selector = null;
					if ($fieldType == DateType::DATE) {
						$selector = 'date';
					} else if ($fieldType == TimeType::TIME) {
						$selector = 'time';
					} else if ($fieldType == DateTimeType::DATETIME) {
						$selector = 'datetime';
					} else if ($fieldType == DateTimeTzType::DATETIMETZ) {
						$selector = 'datetime';
//						$format = $em->getConnection()->getDatabasePlatform()->getDateTimeTzFormatString();
					}
					if ($selector !== null) {
                        $returnValue = self::convertDateTimeToStr($this->getServiceLocator(), $fieldValue, $selector);
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
            $optionsArray = $options->toArray();
            $optionsArray['depth'] = --$depth;
			return $this->serviceLocator->get('Sta\Util\EntityToArray')->convert($entity, $optionsArray);
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
        $noEntityName = false;
        $depth = 0;
            
        $config = $this->getServiceLocator()->get('config');
        if (isset($config['sta']['entityToArray-converter']['defaults']['noEntityName'])) {
            $noEntityName = $config['sta']['entityToArray-converter']['defaults']['noEntityName'];
        }
        if (isset($config['sta']['entityToArray-converter']['defaults']['depth'])) {
            $depth = $config['sta']['entityToArray-converter']['defaults']['depth'];
        }
        
		return array(
			'depth'        => $this->getRequest()->getQuery('depth', $depth),
			'noEntityName' => $this->getRequest()->getQuery('noEntityName', $noEntityName),
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
