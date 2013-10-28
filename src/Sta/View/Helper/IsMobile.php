<?php
namespace Sta\View\Helper;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\AbstractHelper;

class IsMobile extends AbstractHelper implements ServiceLocatorAwareInterface
{

	/**
	 * @var ServiceLocatorAwareInterface
	 */
	private $serviceLocator;

	/**
	 * @var bool
	 */
	private $isMobile = null;

	/**
	 * @var Mobile_Detect
	 */
	private $detect = null;

	public function __invoke()
	{
		if ($this->isMobile === null && !($this->isMobile = $this->getServiceLocator()->getServiceLocator()->get('isDebug'))) {
			require_once __DIR__ . '/../../../../../../vendor/Mobile-Detect/Mobile_Detect.php';
			$this->detect   = new \Mobile_Detect();
			$this->isMobile = $this->detect->isMobile();
		}
		return $this->isMobile;
	}

	/**
	 * Set service locator
	 *
	 * @param ServiceLocatorInterface $serviceLocator
	 */
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		$this->serviceLocator = $serviceLocator;
	}

	/**
	 * Get service locator
	 *
	 * @return ServiceLocatorInterface
	 */
	public function getServiceLocator()
	{
		return $this->serviceLocator;
	}
}