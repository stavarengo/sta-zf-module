<?php

namespace Sta\Mvc\Controller\Plugin;

use Zend\Http\PhpEnvironment\Request;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * @author: Stavarengo
 */
class GetRequestContent extends AbstractPlugin
{

	public function __invoke()
	{
		return $this->getRequestContent();
	}

	/**
	 * Converte o conteúdo de uma requisição (POST ou PUT) para array, respeitando o parâmetro "format".
	 *
	 * @throws GetRequestContentException
	 * @return array
	 */
	public function getRequestContent()
	{
		/** @var $request Request */
		$request = $this->getController()->getRequest();
		$data    = trim($request->getContent());

		try {
			$format      = $this->getController()->getParam('format', 'json');
			$formatUpper = strtoupper($format);
			if ($format == 'xml') {
				@$data = \Zend\Json\Json::fromXml($data, false);
			}
			$data = \Zend\Json\Json::decode($data, \Zend\Json\Json::TYPE_ARRAY);
		} catch (\Exception $e) {
			throw new GetRequestContentException("O conteúdo da requisição deve ser uma string $formatUpper válida.");
		}
		if (!$data) {
			throw new GetRequestContentException("O conteúdo da requisição deve ser uma string $formatUpper válida.");
		} else {
			if ($format == 'xml') {
				if (!isset($data['root'])) {
					throw new GetRequestContentException('O XML recebido dever ter uma tag root com nome "root".');
				}
				$data = $this->normalizeAttributes($data['root']);
			}
		}

		return $data;
	}
	
	private function normalizeAttributes(array $data)
	{
		foreach ($data as $key => $value) {
			if (is_array($value)) {
				if (isset($value['@attributes']) && is_array($value['@attributes'])) {
					// Verificamos quais dos atributos suportados foram usados, e depois disto ignora os demais atributos.
					
					$newValue = '';// Por padrão os campos XML são string vazias
					if (array_key_exists('@text', $value)) {
						$newValue = $value['@text'];
					}
					
					$attributes = $value['@attributes'];
					if (array_key_exists('nil', $attributes) && $attributes['nil']) {
						// Este campo é nulo
						$newValue = null;
					}
					
					$data[$key] = $newValue;
				} else {
					// Este valor é um array, mas não é um array de atributos, então deve ser um array com mais dados de otura entidade
					$data[$key] = $this->normalizeAttributes($value);
				}
			}
		}
		return $data;
	}
}
