<?php
namespace Sta\View\Helper;

use Zend\View\Helper\AbstractHelper;

class GetServiceManager extends AbstractHelper
{

	public function __invoke()
	{
		return \Sta\Module::getServiceLocator();
	}
}