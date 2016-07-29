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
abstract class AbstractEntityWithSequentialId extends AbstractEntityWithoutId
{

    /**
     * Identificação da entidade.
     * O valor deste atributo é controlado pelo WebService.
     * Você deve armazenar este ID em sua base de dados para que você possa fazer uma ligação entre os registros da
     * sua base de dados com as entidades do WebService.
     *
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=true)
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    protected $id;

    /**
     * @ignore
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

}
