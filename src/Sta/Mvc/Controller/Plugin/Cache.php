<?php

namespace Sta\Mvc\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * @author: Stavarengo
 */
class Cache extends AbstractPlugin
{

	public function __invoke()
	{
		return $this;
	}

	/**
	 * Configura a resposta para que ela nao seja cachiada.
	 */
	public function noCache()
	{
		$this->addHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		$this->addHeader('Pragma', "no-cache");
		$this->addHeader('Expires', "Thu, 19 Nov 1981 08:52:00 GMT");
	}
	
	/**
	 * Configura a resposta para que ela nao seja cachiada.
	 */
	public function expires($seconds = 0)
	{
		$seconds = (int)$seconds;
		$this->addHeader('Expires', gmdate('D, d M Y H:i:s', time() + $seconds) . ' GMT');
	}
	
	/**
	 * Configura a resposta para que ela nao seja cachiada.
	 */
	public function cache($seconds = 7200)
	{
		$this->addHeader('Cache-Control', 'public, max-age=' . $seconds);
		$this->expires($seconds);
	}

	private function addHeader($name, $value)
	{
		$response = $this->getController()->getResponse();
		$response->getHeaders()->addHeaderLine($name, $value);
	}
}
