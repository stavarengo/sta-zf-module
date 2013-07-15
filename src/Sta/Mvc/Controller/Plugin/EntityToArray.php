<?php

namespace Sta\Mvc\Controller\Plugin;

use Sta\Entity\AbstractEntity;
use Sta\Util\EntityToArray as EntityToArrayUtil;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * @author: Stavarengo
 */
class EntityToArray extends AbstractPlugin
{

	/**
	 * @param AbstractEntity|AbstractEntity[] $entity
	 *
	 * @param array $options
	 * 		Veja as opções válidas em {@link \Sta\Util\EntityToArray::_convert()}
	 *
	 * @return array
	 */
	public function convert($entity, array $options = array())
	{
		$serviceLocator = $this->getController()->getServiceLocator();
		$entityToArray  = new EntityToArrayUtil($serviceLocator->get('Doctrine\ORM\EntityManager'), $serviceLocator);
		return $entityToArray->convert($entity, $options);
	}
	
	public function __invoke($entity = null, array $options = array())
	{
		if ($entity) {
			return $this->convert($entity, $options);
		} else {
			return $this;
		}
	}
}
