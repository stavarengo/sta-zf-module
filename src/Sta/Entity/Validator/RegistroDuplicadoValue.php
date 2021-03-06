<?php
namespace Sta\Entity\Validator;

use Sta\Entity\AbstractEntity;
use Sta\Entity\EntityWithIdInterface;

class RegistroDuplicadoValue
{

	/**
	 * A entidade que deseja verificar se está duplciada.
	 * @var EntityWithIdInterface
	 */
	public $entity;
	/**
	 * Um array com os nomes dos atributos que serão usados para comparação com as outras entidades.
	 * Se existir outra entidade que tenha os mesmos valores nestes atributos, então a entidade será considerada duplicada.
	 * @var array
	 */
	public $attributes;

	/**
	 * @param EntityWithIdInterface $entity
	 * @param string[]|string $attributes
	 */
	function __construct(EntityWithIdInterface $entity, $attributes)
	{
		$this->attributes = (array)$attributes;
		$this->entity     = $entity;
	}


}
