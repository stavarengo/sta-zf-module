<?php

namespace Sta;

use Doctrine\DBAL\Types\DecimalType;
use Doctrine\DBAL\Types\Type;
use Sta\Dbal\Types\StaDecimalType;
use Sta\Dbal\Types\UriType;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module implements Feature\AutoloaderProviderInterface,
						Feature\ConfigProviderInterface,
						Feature\BootstrapListenerInterface
{

	/**
	 * @var \Zend\ServiceManager\ServiceLocatorInterface
	 */
	private static $staticServiceLocator;

	/**
	 * @return ServiceLocatorInterface
	 */
	public static function getServiceLocator()
	{
		return self::$staticServiceLocator;
	}

	/**
	 * @param ServiceLocatorInterface $serviceLocator
	 */
	public static function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		self::$staticServiceLocator = $serviceLocator;
	}

	public function getAutoloaderConfig()
	{
		return array(
			'Zend\Loader\StandardAutoloader' => array(
				'namespaces' => array(
					__NAMESPACE__ => __DIR__ . '/src/' . str_replace('\\', '/', __NAMESPACE__),
				),
			),
		);
	}

	public function getConfig()
	{
		return include __DIR__ . '/config/module.config.php';
	}

	/**
	 * Listen to the bootstrap event
	 *
	 * @param EventInterface $e
	 *
	 * @return array
	 */
	public function onBootstrap(EventInterface $e)
	{
		$sm     = $e->getApplication()->getServiceManager();
		$config = $sm->get('config');
		$staConfig = $config['sta'];
		if (isset($staConfig['php-settings'])) {
			foreach ($config['sta']['php-settings'] as $name => $value) {
				ini_set($name, $value);
			}
		}

		$e->getApplication()->getEventManager()->attach(array(MvcEvent::EVENT_DISPATCH_ERROR, MvcEvent::EVENT_RENDER_ERROR), function(MvcEvent $e) {
			$e->getViewModel()->ocorreuUmErro = true;
			if (method_exists($e->getResponse(), 'getHeaders')) {
				$e->getResponse()->getHeaders()->addHeaderLine('Content-type', 'text/html; charset=utf-8');
			}
		});

		// Esta função habilita o uso de layouts específicos por módulos.
		// Se dentro do array de configuração existir uma entrada 'modules_layouts' e dentro desta entrada exstir
		// uma entrada com o memo nome do namespace do módulo em execução então o arquivo especificado será usado como layout.
		// Ex: array('modules_layouts' => 'Web' => 'caminho para o arquivo de layout'));
		$e->getApplication()->getEventManager()->getSharedManager()->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', function($e) {
			/** @var $controller \Zend\Mvc\Controller\AbstractActionController */
			$controller      = $e->getTarget();
			$controllerClass = get_class($controller);
			$moduleNamespace = substr($controllerClass, 0, strpos($controllerClass, '\\'));
			$config          = $e->getApplication()->getServiceManager()->get('config');
			if (isset($config['module_layouts'][$moduleNamespace])) {
				$controller->layout($config['module_layouts'][$moduleNamespace]);
			}
		}, 100);

		self::$staticServiceLocator = $e->getApplication()->getServiceManager();

		/** @var $em \Doctrine\ORM\EntityManager */
//		$em = $e->getApplication()->getServiceManager()->get('Doctrine\ORM\EntityManager');
		//$em->getConfiguration()->setSQLLogger(new \Sta\Dbal\Logging\EchoSQLLogger());

		if (isset($staConfig['customDoctrineTypes']) && $staConfig['customDoctrineTypes'] == true) {
			if (!\Doctrine\DBAL\Types\Type::hasType(\Sta\Dbal\Types\PercentageType::PERCENTAGE)) {
				\Doctrine\DBAL\Types\Type::addType(\Sta\Dbal\Types\PercentageType::PERCENTAGE, 'Sta\Dbal\Types\PercentageType');
				\Doctrine\DBAL\Types\Type::addType(\Sta\Dbal\Types\MoneyType::MONEY, 'Sta\Dbal\Types\MoneyType');
				\Doctrine\DBAL\Types\Type::addType(\Sta\Dbal\Types\UriType::URI, UriType::class);
				\Doctrine\DBAL\Types\Type::addType(\Sta\Dbal\Types\DateTimeAlwaysUtcType::DATETIME_ALWAYS_UTC, \Sta\Dbal\Types\DateTimeAlwaysUtcType::class);
				\Doctrine\DBAL\Types\Type::addType(\Sta\Dbal\Types\BigInt64BitsType::BIGINT_64BITS, \Sta\Dbal\Types\BigInt64BitsType::class);
				\Doctrine\DBAL\Types\Type::addType(\Sta\Dbal\Types\MoneyPercentageType::MONEY_PERCENTAGE, 'Sta\Dbal\Types\MoneyPercentageType');
			}
		}

//		Type::addType(OffsetTimeZoneType::OFFSET_TIME_ZONE, __NAMESPACE__ . '\Dbal\Types\OffsetTimeZoneType');
//		Type::addType(\Sta\Dbal\Types\DateTimeType::STA_DATATIME, __NAMESPACE__ . '\Dbal\Types\DateTimeType');
//		Type::addType(StaEnum::STAENUM, 'Sta\Dbal\Types\StaEnum');
//		$em->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('StaEnum', StaEnum::STAENUM);
	}
}
