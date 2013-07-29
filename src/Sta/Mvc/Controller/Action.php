<?php
namespace Sta\Mvc\Controller;

abstract class Action
{

	/**
	 * @var AbstractActionController
	 */
	public $controller;

	public static function invoke(AbstractActionController $controller, $actionName = null)
	{
		if (!$actionName) {
			$actionName = $controller->params()->fromRoute('action');
		}
		$actionName = ucfirst($actionName);
		$controllerClass = str_replace('\\', DIRECTORY_SEPARATOR, get_class($controller));
		$controllerName = str_replace('Controller', '', basename($controllerClass));
		$controllerNs = dirname($controllerClass);
		$controllerNs = str_replace(DIRECTORY_SEPARATOR, '\\', $controllerNs);
		$actionFqcn = "$controllerNs\\Action\\$controllerName\\$actionName";
		/** @var $action Action */
		$action = new $actionFqcn();
		$action->setController($controller);
		return $action->execute();
	}

	/**
	 * Executa a ação.
	 * @return mixin
	 */
	public abstract function execute();

	/**
	 * @param \Sta\Mvc\Controller\AbstractActionController $controller
	 */
	public function setController(AbstractActionController $controller)
	{
		$this->controller = $controller;
	}

	/**
	 * @return AbstractActionController
	 */
	public function getController()
	{
		return $this->controller;
	}

	/**
	 * @param string $param
	 * @param mixed $default
	 *
	 * @return mixed|\Zend\Mvc\Controller\Plugin\Params
	 */
	public function params($param = null, $default = null)
	{
		return $this->getController()->params($param, $default);
	}
}