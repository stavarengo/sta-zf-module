<?php
namespace Sta\Util\EntityToArray;

use Sta\Util\EntityToArray;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @author: Stavarengo
 */
class Factory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $serviceLocator)
	{
		return new EntityToArray($serviceLocator);
	}
} 