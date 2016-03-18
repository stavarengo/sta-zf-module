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
            'entityToArray' => 'Sta\View\Helper\EntityToArray',
            'populateEntityFromArray' => 'Sta\Util\PopulateEntityFromArray',
            'shortNumber' => 'Sta\View\Helper\ShortNumber',
		),
	),
	'service_manager' => array(
		'invokables' => array(
			'Sta\Util\MobileDetect' => 'Sta\Util\MobileDetect',
            'populateEntityFromArray' => 'Sta\Util\PopulateEntityFromArray',
		),
		'factories' => array(
			'Sta\Util\GetConfiguredResponse' => 'Sta\Util\GetConfiguredResponseFactory', 
			'Sta\Util\EntityToArray' => 'Sta\Util\EntityToArray\Factory', 
		),
		'aliases' => array(
			'Em' => 'Doctrine\ORM\EntityManager',
		),
	),

	'sta' => array(
		/**
		 * Configurações do php.ini que serão setadas assim que o módulo Sta for carregado.
		 */
		'php-settings' => array(),

		/**
		 * Determina se deve registrar os tipos customizados para o Doctrine.
		 */
		'customDoctrineTypes' => true,

		'ReflectionClass' => array(
			'cache' => 'filesystem',
            'cacheDir' => __DIR__ . '/../../../data/cache/StaReflectionClass',
		),
	),
);
