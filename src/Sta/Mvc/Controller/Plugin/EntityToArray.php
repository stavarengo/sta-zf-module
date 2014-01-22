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
	 * @param array|\Sta\Util\EntityToArray\ConverterOptions $options
	 *        Veja as opções válidas em {@link \Sta\Util\EntityToArray::_convert()}
	 *
	 * @return array
	 */
	public function convert($entity, $options = array())
	{
		$serviceLocator = $this->getController()->getServiceLocator();
		$entityToArray  = $serviceLocator->get('\Sta\Util\EntityToArray');
		return $entityToArray->convert($entity, $options);
	}

	public function __invoke($entity = null, array $options = array())
	{
		if ($entity !== null) {
			return $this->convert($entity, $options);
		} else {
			return $this;
		}
	}
}
