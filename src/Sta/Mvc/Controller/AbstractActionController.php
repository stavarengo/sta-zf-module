<?php


namespace Sta\Mvc\Controller;


use Doctrine\ORM\EntityManager;
use Sta\Entity\AbstractEntity;

/**
 * Herança criada apenas para criar um type hint mais eficaz para o code completition.
 *
 *
 * Convenience methods Sta plugins (@see \Zend\Mvc\Controller\AbstractController::__call):
 *
 * @method \Sta\Mvc\Controller\Plugin\RangeUnit rangeUnit()
 * @method \Sta\Mvc\Controller\Plugin\EntityToArray entityToArray($entity = null, array $options = array())
 *
 *
 * Sobrescreve o retorno de alguns métodos que retornam tipos genérios, mas nós sabemos que tipos serão retornados.
 *
 * @method \Zend\Http\PhpEnvironment\Request getRequest()
 * @method \Zend\Http\PhpEnvironment\Response getResponse()
 * @method \Zend\Http\PhpEnvironment\Response getConfiguredResponse($statusCode, $body = null, array $responseHeaders = array())
 * @method populateEntityFromArray(array $data, AbstractEntity $entity, array $options = array())
 *
 * @author: Stavarengo
 */
class AbstractActionController extends \Zend\Mvc\Controller\AbstractActionController
{

	/**
	 * @return EntityManager
	 */
	public function getEntityManager()
	{
		return $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
	}
	
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