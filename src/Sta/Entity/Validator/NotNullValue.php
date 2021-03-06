<?php
namespace Sta\Entity\Validator;

use Sta\Entity\AbstractEntity;
use Sta\Entity\EntityInterface;

class NotNullValue
{

	/**
	 * A entidade que deseja verificar os campos obrigatórios.
	 * @var EntityInterface
	 */
	public $entity;
	/**
	 * Um array com os nomes dos atributos obrigatorios.
	 * @var array
	 */
	public $attributes;

	/**
	 * @param AbstractEntity $entity
	 * @param string[]|string $attributes
	 */
	function __construct(AbstractEntity $entity, $attributes)
	{
		$this->attributes = (array)$attributes;
		$this->entity     = $entity;
	}


}
