<?php
namespace Sta;

return array(
	'controller_plugins' => array(
		'invokables' => array(
			'entityToArray' => 'Sta\Mvc\Controller\Plugin\EntityToArray',
			'rangeUnit' => 'Sta\Mvc\Controller\Plugin\RangeUnit',
			'getRequestContent' => 'Sta\Mvc\Controller\Plugin\GetRequestContent',
			'cache' => 'Sta\Mvc\Controller\Plugin\Cache',
		),
        'factories' => array(
			'getConfiguredResponse' => \Sta\Mvc\Controller\Plugin\GetConfiguredResponseFactory::class,
            'populateEntityFromArray' => \Sta\Util\PopulateEntityFromArrayFactory::class,
        )
	),
	'view_helpers' => array(
		'invokables' => array(
			'getEntityManager' => __NAMESPACE__ . '\View\Helper\GetEntityManager',
			'getServiceManager' => __NAMESPACE__ . '\View\Helper\GetServiceManager',
            'shortNumber' => 'Sta\View\Helper\ShortNumber',
		),
        'factories' => array(
			'isMobile' => \Sta\View\Helper\IsMobileFactory::class,
            'entityToArray' => \Sta\View\Helper\EntityToArrayFactory::class,
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
