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

	'sta' => array(
		/**
		 * Configurações do php.ini que serão setadas assim que o módulo Sta for carregado.
		 */
		'php-settings' => array(),

		/**
		 * Determina se deve registrar os tipos customizados para o Doctrine.
		 */
		'customDoctrineTypes' => true,

		/**
		 * Está função deve retornar um boolean.
		 * Determina se estamos rodando em um ambiente local ou no servidor real.
		 */
		'isLocal' => function() {
			$sapi_type = php_sapi_name();
			if (substr($sapi_type, 0, 3) == 'cli') {
				// rodando do console - provavelmente é o script do Doctrine p/ criação do DB
				return true;
			} else {
				$hostname = $_SERVER['SERVER_NAME'];
				return ($hostname == 'local.grands.sell');
			}
		},
		
		'ReflectionClass' => array(
			'cache' => new \Doctrine\Common\Cache\FilesystemCache(__DIR__ . '/../../../data/cache/StaReflectionClass', 'sta.ref.class'),
		),
	),
);
