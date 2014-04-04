<?php
/**
 * irmo Project ${PROJECT_URL}
 *
 * @link      ${GITHUB_URL} Source code
 */

namespace Sta\Util;

class StdClass
{

    public function __construct(array $initialData = array())
    {
        foreach ($initialData as $attr => $value) {
            $this->set($attr, $value);
        }
    }

    /**
     * @ignore
     * @param string $attributeName
     * @param $value
     *
     * @throws StdClassInvalidArgument
     */
    public function set($attributeName, $value)
    {
        $method = 'set' . ucfirst($attributeName);
        if (is_callable(array($this, $method))) {
            $this->$method($value);
            return;
        }

        throw new StdClassInvalidArgument('Não existe um método para definir o valor do atributo: "' . $attributeName . '"');
    }

    /**
     * @param $attributeName
     * @return mixed
     * @throws StdClassInvalidArgument
     */
    public function get($attributeName)
    {
        $method = 'get' . ucfirst($attributeName);
        if (is_callable(array($this, $method))) {
            return $this->$method();
        }
        $method = 'is' . ucfirst($attributeName);
        if (is_callable(array($this, $method))) {
            return $this->$method();
        }

        throw new StdClassInvalidArgument('Não existe um método para retornar o valor do atributo: "'
            . $attributeName . '"');
    }
} 