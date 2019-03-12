<?php

namespace Sta\View\Helper;

use Sta\Entity\AbstractEntity;
use Sta\Util\EntityToArray as EntityToArrayUtil;
use Web\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * @author: Stavarengo
 */
class EntityToArray extends AbstractHelper implements ServiceLocatorAwareInterface
{

    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceLocator;
    
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
		$entityToArray  = $this->getServiceLocator()->get('Sta\Util\EntityToArray');
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

    /**
     * Set service locator
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Get service locator
     *
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}
