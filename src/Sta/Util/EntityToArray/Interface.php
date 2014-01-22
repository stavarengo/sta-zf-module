<?php

namespace Sta\Util\EntityToArray;

use Sta\Entity\AbstractEntity;
use Zend\Di\ServiceLocator;

/**
 * @author: Stavarengo
 */
interface ConverterInterface
{
	/**
	 * @param AbstractEntity $entity
	 * @param ConverterOptions $options
	 *
	 * @return array
	 */
	public function convert(AbstractEntity $entity, ConverterOptions $options = null);
	
	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 */
	public function setEm(\Doctrine\ORM\EntityManager $em);

	/**
	 * @param \Zend\ServiceManager\ServiceManager $serviceLocator
	 */
	public function setServiceLocator(\Zend\ServiceManager\ServiceManager $serviceLocator);

	/**
	 * @param \Zend\Stdlib\RequestInterface $request
	 */
	public function setRequest($request);
}
