<?php
namespace Sta;

return array(
	'controller_plugins' => array(
		'invokables' => array(
			'entityToArray' => 'Sta\Mvc\Controller\Plugin\EntityToArray',
			'populateEntityFromArray' => 'Sta\Util\PopulateEntityFromArray',
			'rangeUnit' => 'Sta\Mvc\Controller\Plugin\RangeUnit',
			'getConfiguredResponse' => 'Sta\Mvc\Controller\Plugin\GetConfiguredResponse',
			'getRequestContent' => 'Sta\Mvc\Controller\Plugin\GetRequestContent',
			'cache' => 'Sta\Mvc\Controller\Plugin\Cache',
		),
	),
	'view_helpers' => array(
		'invokables' => array(
			'isMobile' => __NAMESPACE__ . '\View\Helper\IsMobile',
			'getEntityManager' => __NAMESPACE__ . '\View\Helper\GetEntityManager',
			'getServiceManager' => __NAMESPACE__ . '\View\Helper\GetServiceManager',
		),
	),
	'service_manager' => array(
		'aliases' => array(
			'Em' => 'Doctrine\ORM\EntityManager',
		),
	),
);
