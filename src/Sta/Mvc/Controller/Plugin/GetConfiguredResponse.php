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
		$isDebug  = $config['webapp']['isDebug'];
		$headers  = $response->getHeaders();
		if ($statusCode >= 200 && $statusCode <= 299) {
			if ($this->getController()->getParam('format') == 'xml') {
				$contentType = 'text/xml; charset=utf-8';
				$body        = $this->arrayToXml($body, $isDebug)->flush();
			} else {
				$contentType = 'application/json; charset=utf-8';
				$body        = \Zend\Json\Json::encode($body);
				if ($isDebug) {
					$body = \Zend\Json\Json::prettyPrint($body, array("indent" => "    "));
				}
			}
		} else {
			$contentType = 'text/html; charset=utf-8';
		}

		$headers->addHeaderLine('Content-type', $contentType);
//		$headers->addHeaderLine('Cache-Control', 'max-age=0, no-cache, no-store, must-revalidate');
//		$headers->addHeaderLine('Pragma', "no-cache");
//		$headers->addHeaderLine('Expires', "Wed, 11 Jan 1984 05:00:00 GMT");
		foreach ($responseHeaders as $headerName => $headerContent) {
			$headers->addHeaderLine($headerName, $headerContent);
		}
		$response->setStatusCode($statusCode);
		$response->setContent($body);

		return $response;
	}

	private function arrayToXml($array, $isDebug)
	{
		$writer = new \XMLWriter();
		$writer->openMemory();
		$writer->setIndent($isDebug);
		if ($isDebug) {
			$writer->setIndentString('   ');
		} else {
			$writer->setIndentString('');
		}
		$writer->startDocument('1.0', 'UTF-8');

		$writer->startElement('root');
//		$writer->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema');
		$this->arrayToXmlRecursive($array, $writer);
		$writer->endElement();
		
		return $writer;
	}

	private function arrayToXmlRecursive(array $array, \XMLWriter $writter)
	{
		foreach ($array as $key => $value) {
			$elementName = (string)$key;
			if (is_array($value)) {
				if (is_numeric($key)) {
					$elementName = 'item' . $key;
				}
				
				$writter->startElement($elementName);
				$this->arrayToXmlRecursive($value, $writter);
				$writter->endElement();
			} else {
				if ($value === null) {
					$writter->startElement($elementName);
//					$writter->writeAttribute('xsi:nil', 'true');
					$writter->writeAttribute('nil', 'true');
					$writter->endElement();
				} else if ($value === true) {
					$writter->writeElement($elementName, '1');
				} else if ($value === false) {
					$writter->writeElement($elementName, '0');
				} else if ($value === '') {
					$writter->writeElement($elementName);
				} else {
					$writter->writeElement($elementName, (string)$value);
				}
			}
		}
	}
}
