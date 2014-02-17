<?php
namespace Sta\Util\EntityToArray;

/**
 * @author: Stavarengo
 */
class ConverterOptions
{

	/**
	 * Define a profundidade que que devemos alcançar nos relacionamentos entre as classes.
	 * Veja mais em {@link https://github.com/???/???/wiki/Parametro-depth}
	 * @var int
	 */
	protected $depth = 0;

	/**
	 * Quando true cada entidade estara relacionada a um atributo cujo nome é igual ao nome da entidade.
	 * 
	 * Ex: abaixo temos uma entidade Produto, e o valor deste parâmetro é false.
	 * <pre>
	 *    array(
	 *        'id' => 106,
	 *        'descricao' => 'Calça Masculina',
	 *        'unidadeMedida' => array(
	 *            'id' => 15,
	 *            'nome' => 'Unidade',
	 *            'sigla' => 'UN',
	 *        ),
	 *    );
	 * </pre>
	 * 
	 * Agora, no exemplo abaixo, temos a mesma entidade exibida acima, porem o valor deste parametro é true.
	 * <pre>
	 *    array(
	 *        'Produto' => array(
	 *            'id' => 106 ,
	 *            'descricao' => 'Calça Masculina',
	 *            'unidadeMedida' => array(
	 *                '_en' => 'UnidadeMedida',
	 *                'UnidadeMedida' => array(
	 *                    'id' => 15,
	 *                    'nome' => 'Unidade',
	 *                    'sigla' => 'UN',
	 *                ),
	 *            ),
	 *        ),
	 *    );
	 * </pre>                        
	 * @var bool
	 */
	protected $noEntityName = false;

	public function __construct(array $options = array())
	{
		foreach ($options as $option => $value) {
			$this->set($option, $value);
		}
	}
    
    public function toArray()
    {
        $data = array();
        $attributes = get_object_vars($this);
        foreach ($attributes as $attrName => $attrValue) {
            $methodName = 'get' . ucfirst($attrName);
            if (method_exists($this, $methodName)) {
                $data[$attrName] = $attrValue;
            }
        }
        
        return $data;
    }
    
	/**
	 * @param string $name
	 * @param any $value
	 */
	public function set($name, $value)
	{
		if (property_exists($this, $name)) {
			call_user_func(array($this, 'set' . ucfirst($name)), $value);
		} else {
			throw new InvalidOption('Option "' . $name . '" is not valid.');
		}
	}
	
	public function __set($name, $value)
	{
		$this->set($name, $value);
	}

	/**
	 * @param int $depth
	 */
	public function setDepth($depth)
	{
		if ($depth === 'Infinity') {
			$depth = PHP_INT_MAX;
		}
		$this->depth = $depth;
	}

	/**
	 * @return int
	 */
	public function getDepth()
	{
		return $this->depth;
	}

	/**
	 * @param boolean $noEntityName
	 */
	public function setNoEntityName($noEntityName)
	{
		$this->noEntityName = $noEntityName;
	}

	/**
	 * @return boolean
	 */
	public function getNoEntityName()
	{
		return $this->noEntityName;
	}
	
} 