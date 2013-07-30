<?php

namespace Sta\Mvc\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * @author: Stavarengo
 */
class GetConfiguredResponse extends AbstractPlugin
{

	public function __invoke($statusCode, $body = null, array $responseHeaders = array())
	{
		return $this->getConfiguredResponse($statusCode, $body, $responseHeaders);
	}

	/**
	 * Configura a resposta que será retornada.
	 *
	 * @param int $statusCode
	 *        Código HTTP de resposta.
	 *
	 * @param mixed $body
	 *        Opcional. Corpo da resposta.
	 *
	 * @param array $responseHeaders
	 *        Opcional. Cabeçalhos HTTP da resposta.
	 *
	 * @return \Zend\Http\PhpEnvironment\Response
	 */
	public function getConfiguredResponse($statusCode, $body = null, array $responseHeaders = array())
	{
		$response = $this->getController()->getResponse();
		$config   = $this->getController()->getServiceLocator()->get('config');
		$headers  = $response->getHeaders();
		if ($statusCode >= 200 && $statusCode <= 299) {
			$contentType = 'application/json; charset=utf-8';
			$body        = ($body !== null ? \Zend\Json\Json::encode($body) : $body);
			if ($config['webapp']['isDebug']) {
				$body = \Zend\Json\Json::prettyPrint($body, array("indent" => "    "));
			}
		} else {
			$contentType = 'text/html; charset=utf-8';
		}

		$headers->addHeaderLine('Content-type', $contentType);
		foreach ($responseHeaders as $headerName => $headerContent) {
			$headers->addHeaderLine($headerName, $headerContent);
		}
		$response->setStatusCode($statusCode);
		$response->setContent($body);

		return $response;
	}
}
