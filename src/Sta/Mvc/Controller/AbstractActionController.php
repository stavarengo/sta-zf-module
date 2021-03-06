<?php


namespace Sta\Mvc\Controller;


use Doctrine\ORM\EntityManager;
use Sta\Entity\AbstractEntity;

/**
 * Herança criada apenas para criar um type hint mais eficaz para o code completition.
 *
 *
 * Sobrescreve o retorno de alguns métodos que retornam tipos genérios, mas nós sabemos que tipos serão retornados.
 *
 * @method \Zend\Http\PhpEnvironment\Request getRequest()
 * @method \Zend\Http\PhpEnvironment\Response getResponse()
 * @method \Zend\Http\PhpEnvironment\Response getConfiguredResponse($statusCode, $body = null, array $responseHeaders = array())
 * 
 * Convenience methods Sta plugins (@see \Zend\Mvc\Controller\AbstractController::__call):
 *
 * @method \Sta\Util\RangeUnit rangeUnit($maxLength, $unit = 'items', \Zend\Http\Header\HeaderInterface $rawHeader = null, $acceptQueryParams = true)
 * @method \Sta\Mvc\Controller\Plugin\EntityToArray entityToArray($entity = null, array $options = array())
 * @method populateEntityFromArray(array $data, AbstractEntity $entity, array $options = array())
 * @method array getRequestContent($objectDecodeType = \Zend\Json\Json::TYPE_ARRAY)
 * @method \Sta\Mvc\Controller\Plugin\Cache cache()
 *
 * @author: Stavarengo
 */
class AbstractActionController extends \Zend\Mvc\Controller\AbstractActionController
{

	public function getParam($param, $default = null)
	{
		$def = '____nao-existe-' . time() . '____';
		if (($value = $this->params()->fromQuery($param, $def)) !== $def
			|| ($value = $this->params()->fromPost($param, $def)) !== $def
			|| ($value = $this->params()->fromRoute($param, $def)) !== $def
		) {
			return $value;
		}
		return ($value === $def ? $default : $value);
	}
}
