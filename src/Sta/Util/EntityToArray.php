<?php


namespace Sta\Util;


use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\DateTimeTzType;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\TimeType;
use Doctrine\ORM\EntityManager;
use Sta\Entity\AbstractEntity;
use Sta\Util\EntityToArray\Converter;
use Sta\Util\EntityToArray\ConverterOptions;
use Zend\Di\ServiceLocator;
use Zend\ServiceManager\ServiceManager;

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
	public function __construct(ServiceManager $serviceManager)
	{
		$this->em             = $serviceManager->get('Doctrine\ORM\EntityManager');
		$this->serviceLocator = $serviceManager;
	}

	/**
	 * @param AbstractEntity|AbstractEntity[] $entity
	 *
	 * @return array
	 */
	public function convert($entity, array $options = array())
	{
		/** @var $pice AbstractEntity */
		$pice = null;
		if ($entity instanceof AbstractEntity) {
			$pice = $entity;
		} else {
			if (count($entity)) {
				$pice = reset($entity);
            } else {
                return array();
            }
		}
        
		$converter = $this->_createCovnerter($pice);
		foreach ($options as $optionName => $optionValue) {
			$converter->setOption($optionName, $optionValue);
		}
		
		if ($entity instanceof AbstractEntity) {
			return $converter->convert($entity);
		} else {
			$result = array();
			foreach ($entity as $item) {
				$result[] = $converter->convert($item);
			}
			return $result;
		}
	}

	/**
	 * @paraddm $pice
	 *
	 * @return EntityToArray\ConverterInterface
	 */
	private function _createCovnerter(AbstractEntity $entity)
	{
		/** @var $converter EntityToArray\ConverterInterface */
		$converter = null;
		$reflectionClass = \Sta\ReflectionClass::factory($entity);
		/** @var $anno \Sta\Util\EntityToArray\Annotation */
		if ($anno = $reflectionClass->getClassAnnotation('\Sta\Util\EntityToArray\Annotation')) {
			$converter = new $anno->class;
		}
		if (!$converter) {
			$converter = new Converter();
		}

		/** @var $application \Zend\Mvc\Application */
		$application = $this->serviceLocator->get('application');
		
		$converter->setEm($this->em);
		$converter->setServiceLocator($this->serviceLocator);
		$converter->setRequest($application->getRequest());
		return $converter;
	}
}