<?php
/**
 * 
 *
 * @link      ${GITHUB_URL} Source code
 */

namespace Sta\Util;

class StdClass
{

    public function __construct(array $initialData = array())
    {
        $this->fromArray($initialData);
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

    public function fromArray(array $data)
    {
        foreach ($data as $attr => $value) {
            $this->set($attr, $value);
        }
        
        return $this;
    }
    
    /**
     * @return array
     */
    public function toArray()
    {
        return $this->_toArray($this);
    }

    /**
     * @param $value
     * @return array
     */
    private function _toArray($value)
    {
        $result = array();
        if (is_object($value) || is_array($value)) {
            $isMyOwnInstance = false;
            if (is_object($value)) {
                $vars            = get_object_vars($value);
                $isMyOwnInstance = ($value instanceof StdClass);
            } else {
                $vars = $value;
            }
            foreach ($vars as $var => $val) {
                try {
                    if ($isMyOwnInstance) {
                        $val = $this->get($var);
                    }
                } catch (StdClassInvalidArgument $e) {
                    // Ignora essa propiedade se ela não tiver um método get definido.
                    continue;
                }

                if (is_object($val) || is_array($val)) {
                    $val = $this->_toArray($val);
                }
                $result[$var] = $val;
            }
        }

        return $result;
    }
}