<?php
namespace Sta\View\Helper;

use Zend\View\Helper\AbstractHelper;

class IsMobile extends AbstractHelper
{
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
		if ($this->isMobile === null) {
			require_once __DIR__ . '/../../../../../../vendor/Mobile-Detect/Mobile_Detect.php';
			$this->detect   = new \Mobile_Detect();
			$this->isMobile = $this->detect->isMobile();
		}
		return $this->isMobile;
	}

}