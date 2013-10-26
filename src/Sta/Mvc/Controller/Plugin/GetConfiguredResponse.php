<?php

namespace Sta\Mvc\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * @author: Stavarengo
 */
class GetConfiguredResponse extends AbstractPlugin implements ServiceLocatorAwareInterface
{

	/**
	 * @var \Zend\ServiceManager\ServiceLocatorInterface
	 */
	private $serviceLocator;

	public function __invoke($statusCode, $body = null, array $responseHeaders = array())
	{
		/** @var $getConfiguredResponse \Sta\Util\GetConfiguredResponse */
		$getConfiguredResponse = $this->getServiceLocator()->getServiceLocator()->get('Sta\Util\GetConfiguredResponse');
		$controller            = $this->getController();
		$response              = $controller->getResponse();
		$format                = $controller->getParam('format');
		return $getConfiguredResponse->getConfiguredResponse($response, $statusCode, $body, $format, $responseHeaders);
	}

	/**
	 * Set service locator
	 *
	 * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
	 */
	public function setServiceLocator(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
	{
		$this->serviceLocator = $serviceLocator;
	}

	/**
	 * Get service locator
	 *
	 * @return \Zend\ServiceManager\ServiceLocatorInterface
	 */
	public function getServiceLocator()
	{
		return $this->serviceLocator;
	}

}
