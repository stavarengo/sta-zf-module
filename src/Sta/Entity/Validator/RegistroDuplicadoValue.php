<?php
namespace Sta\Entity\Validator;

use Sta\Entity\AbstractEntity;

class RegistroDuplicadoValue
{

	/**
	 * A entidade que deseja verificar se está duplciada.
	 * @var AbstractEntity
	 */
	public $entity;
	/**
	 * O nome do atributo que será usado para comparação com as outras entidades.
	 * Se existir outra entidade que tenha o mesmo valor neste atributo, então a entidade será considerada duplicada.
	 * @var string
	 */
	public $attribute;

	function __construct(AbstractEntity $entity, $attribute)
	{
		$this->attribute = $attribute;
		$this->entity    = $entity;
	}


}