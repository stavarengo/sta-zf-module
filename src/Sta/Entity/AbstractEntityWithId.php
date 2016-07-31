<?php
namespace Sta\Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Sta\Entity\Exception\InvalidArgument;

/**
 * Classe base para todas as entidades.
 *
 * Uma entidade é um conjunto de informações que podem ser idenficado por um ID.
 *
 * @author: Stavarengo
 * @ORM\MappedSuperclass
 */
abstract class AbstractEntityWithId extends AbstractEntityWithoutId
{

    abstract public function getId();
    abstract public function setId($id);

}
