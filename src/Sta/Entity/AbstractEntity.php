<?php
namespace Sta\Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Sta\Entity\Exception\InvalidArgument;

/**
 * @ORM\MappedSuperclass
 * @deprecated Use {@link \Sta\Entity\AbstractEntityWithSequentialId} instead
 */
abstract class AbstractEntity extends AbstractEntityWithSequentialId
{
}
