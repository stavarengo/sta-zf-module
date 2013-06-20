<?php
namespace Sta\View\Helper;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\View\Helper\AbstractHelper;

class GetEntityManager extends AbstractHelper
{
	public function __invoke()
	{
		return \Sta\Module::getServiceLocator()->get('Doctrine\ORM\EntityManager');
	}
}