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
	 * @param int $depth
	 *
	 * @return array
	 */
	public function convert($entity, $depth = 0)
	{
		$serviceLocator = $this->getController()->getServiceLocator();
		$entityToArray  = new EntityToArrayUtil($serviceLocator->get('Doctrine\ORM\EntityManager'), $serviceLocator);
		return $entityToArray->convert($entity, $depth);
	}

	public function __invoke($entity = null, $depth = 0)
	{
		if ($entity) {
			return $this->convert($entity, $depth);
		} else {
			return $this;
		}
	}
}
