<?php
namespace Sta\View\Helper;

use Zend\View\Helper\AbstractHelper;

class GetEntityManager extends AbstractHelper
{

	public function __invoke()
	{
		return \Sta\Module::getServiceLocator()->get('Doctrine\ORM\EntityManager');
	}
}