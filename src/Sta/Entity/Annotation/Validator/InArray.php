<?php
namespace Sta\Entity\Annotation\Validator;

/**
 * Aplica o validator {@link \Zend\Validator\InArray).
 *
 * @author: Stavarengo
 * @Annotation
 * @Target({"PROPERTY"})
 */
class InArray
{

	/**
	 * Um array de valores permitidos.
	 * @var array
	 */
	public $value;
}