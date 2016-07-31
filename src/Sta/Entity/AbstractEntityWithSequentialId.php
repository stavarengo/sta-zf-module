<?php
namespace Sta\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Classe base para todas as entidades.
 *
 * Uma entidade é um conjunto de informações que podem ser idenficado por um ID.
 *
 * @author: Stavarengo
 * @ORM\MappedSuperclass
 */
abstract class AbstractEntityWithSequentialId extends AbstractEntityWithId
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

    public function setId($id)
    {
        $this->id = (int)$id;
    }

}
