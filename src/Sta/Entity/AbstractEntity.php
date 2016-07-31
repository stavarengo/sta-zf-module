<?php
namespace Sta\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sta\Entity\Exception\InvalidArgument;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractEntity implements EntityInterface
{

    /**
     * Helper que retornar o valor de um atributo sem precisar usar os getters.
     *
     * @param $attributeName
     *
     * @ignore
     * @throws \Sta\Entity\Exception\InvalidArgument
     * @return mixed
     */
    public function get($attributeName)
    {
        $method = 'get' . ucfirst($attributeName);
        if (is_callable([$this, $method])) {
            return $this->$method();
        }
        $method = 'is' . ucfirst($attributeName);
        if (is_callable([$this, $method])) {
            return $this->$method();
        }

        throw new InvalidArgument(
            'Não existe um método para retornar o valor do atributo: "'
            . $attributeName . '"'
        );
    }

    /**
     *
     * @param string $attributeName
     * @param $value
     *
     * @throws \Sta\Entity\Exception\InvalidArgument
     *
     * @return $this
     */
    public function set($attributeName, $value)
    {
        $method = 'set' . ucfirst($attributeName);
        if (is_callable([$this, $method])) {
            $this->$method($value);

            return;
        }

        throw new InvalidArgument(
            'Não existe um método para definir o valor do atributo: "'
            . $attributeName . '"'
        );

    }
}
