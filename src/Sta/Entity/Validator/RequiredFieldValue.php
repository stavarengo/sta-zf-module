<?php
namespace Sta\Entity\Validator;

use Sta\Entity\AbstractEntity;

class RequiredFieldValue
{

	/**
	 * A entidade que deseja verificar os campos obrigatórios.
	 * @var AbstractEntity
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
		$this->entity    = $entity;
	}


}