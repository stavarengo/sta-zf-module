<?php
namespace Sta\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractEntityWithRandomId extends AbstractEntity implements EntityWithIdInterface
{

    /**
     * Identificação da entidade.
     * O valor deste atributo é controlado pelo WebService.
     * Você deve armazenar este ID em sua base de dados para que você possa fazer uma ligação entre os registros da
     * sua base de dados com as entidades do WebService.
     *
     * @ORM\Id
     * @ORM\Column(type="string", length=15, nullable=false)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="\Sta\Entity\Id\RandomIdGenerator")
     * @var string
     */
    protected $id;

    /**
     * @ignore
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public static function generateNewId()
    {
        return \Sta\Entity\Id\RandomIdGenerator::generateNewId();
    }

}
