<?php

namespace Sta\View\Helper;

use Sta\Entity\AbstractEntity;
use Web\View\Helper\AbstractHelper;

/**
 * @author: Stavarengo
 */
class EntityToArray extends AbstractHelper
{
    /**
     * @var \Sta\Util\EntityToArray
     */
    protected $entityToArray;

    /**
     * EntityToArray constructor.
     * @param \Sta\Util\EntityToArray $entityToArray
     */
    public function __construct(\Sta\Util\EntityToArray $entityToArray)
    {
        $this->entityToArray = $entityToArray;
    }

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
        return $this->entityToArray->convert($entity, $options);
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
