<?php
namespace Sta;

return array(
	'controller_plugins' => array(
		'invokables' => array(
			'entityToArray' => 'Sta\Mvc\Controller\Plugin\EntityToArray',
			'rangeUnit' => 'Sta\Mvc\Controller\Plugin\RangeUnit',
			'getConfiguredResponse' => 'Sta\Mvc\Controller\Plugin\GetConfiguredResponse',
			'getRequestContent' => 'Sta\Mvc\Controller\Plugin\GetRequestContent',
			'cache' => 'Sta\Mvc\Controller\Plugin\Cache',
		),
        'factories' => array(
            'populateEntityFromArray' => \Sta\Util\PopulateEntityFromArrayFactory::class,
        )
	),
	'view_helpers' => array(
		'invokables' => array(
			'isMobile' => __NAMESPACE__ . '\View\Helper\IsMobile',
			'getEntityManager' => __NAMESPACE__ . '\View\Helper\GetEntityManager',
			'getServiceManager' => __NAMESPACE__ . '\View\Helper\GetServiceManager',
            'entityToArray' => 'Sta\View\Helper\EntityToArray',
            'shortNumber' => 'Sta\View\Helper\ShortNumber',
		),
        'factories' => array(
            'populateEntityFromArray' => \Sta\Util\PopulateEntityFromArrayFactory::class,
        ),
    ),
	'service_manager' => array(
		'invokables' => array(
			'Sta\Util\MobileDetect' => 'Sta\Util\MobileDetect',
		),
		'factories' => array(
			'Sta\Util\GetConfiguredResponse' => 'Sta\Util\GetConfiguredResponseFactory', 
			'Sta\Util\EntityToArray' => 'Sta\Util\EntityToArray\Factory',
            'populateEntityFromArray' => \Sta\Util\PopulateEntityFromArrayFactory::class,
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
