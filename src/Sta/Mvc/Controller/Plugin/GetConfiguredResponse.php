<?php

namespace Sta\Mvc\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * @author: Stavarengo
 */
class GetConfiguredResponse extends AbstractPlugin
{
    /**
     * @var \Sta\Util\GetConfiguredResponse
     */
    private $getConfiguredResponse;

    /**
     * GetConfiguredResponse constructor.
     * @param \Sta\Util\GetConfiguredResponse $getConfiguredResponse
     */
    public function __construct(\Sta\Util\GetConfiguredResponse $getConfiguredResponse)
    {
        $this->getConfiguredResponse = $getConfiguredResponse;
    }

    public function __invoke($statusCode, $body = null, array $responseHeaders = array())
	{
		$controller            = $this->getController();
		$response              = $controller->getResponse();
		$format                = $controller->getParam('format');
		return $this->getConfiguredResponse->getConfiguredResponse($response, $statusCode, $body, $format, $responseHeaders);
	}
}
