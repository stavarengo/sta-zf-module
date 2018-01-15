<?php

namespace Sta\Mvc\Controller\Plugin;

use Sta\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * @author: Stavarengo
 */
class GetRequestContent extends AbstractPlugin
{

    public function __invoke($objectDecodeType = \Zend\Json\Json::TYPE_ARRAY)
    {
        return $this->getRequestContent($objectDecodeType);
    }

    /**
     * Converte o conteúdo de uma requisição (POST ou PUT) para array, respeitando o parâmetro "format".
     *
     * @throws GetRequestContentException
     * @return array|object
     */
    public function getRequestContent($objectDecodeType = \Zend\Json\Json::TYPE_ARRAY, $autoDetectSource = true)
    {
        /** @var AbstractActionController $controller */
        $controller = $this->getController();
        $request    = $controller->getRequest();

        if ($autoDetectSource) {
            /** @var $contentType \Zend\Http\Header\ContentType */
            $contentType = $request->getHeader('ContentType');
            if ($contentType && $contentType->getMediaType() == 'multipart/form-data') {
                $data = $_POST;
                if ($objectDecodeType == \Zend\Json\Json::TYPE_OBJECT) {
                    $data = (object)$data;
                }

                return $data;
            } else {
                $data = trim($request->getContent());
            }
        } else {
            $data = trim($request->getContent());
        }


        $format      = $controller->getParam('format', 'json');
        $formatUpper = strtoupper($format);
        try {
            if ($format == 'xml') {
                @$data = \Zend\Json\Json::fromXml($data, false);
            }
            $data = \Zend\Json\Json::decode($data, $objectDecodeType);
        } catch (\Exception $e) {
            $data = null;
        }
        if (!$data) {
            throw new GetRequestContentException("The content of the request must be a valid $formatUpper string.");
        } else {
            if ($format == 'xml') {
                if (!isset($data['root'])) {
                    throw new GetRequestContentException('The received XML must have a root tag named "root".');
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

                    $newValue = ''; // Por padrão os campos XML são string vazias
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
