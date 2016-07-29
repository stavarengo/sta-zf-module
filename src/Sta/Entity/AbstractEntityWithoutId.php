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
abstract class AbstractEntityWithoutId
{

    /**
     * @ignore
     * @return EntityManager
     * @deprecated
     */
    public function getEntityManager()
    {
        trigger_error(
            'Este método está depreciado. Você deve obter o EntityManager no construtor da classe dependente.'
        );

        return \Sta\Module::getServiceLocator()->get('Doctrine\ORM\EntityManager');
    }

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
     * @ignore
     *
     * @param string $attributeName
     * @param $value
     *
     * @throws \Sta\Entity\Exception\InvalidArgument
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
