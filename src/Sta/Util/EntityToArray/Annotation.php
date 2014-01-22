<?php
namespace Sta\Util\EntityToArray;

/**
 * @author: Stavarengo
 * @Annotation
 * @Target({"CLASS"})
 */
class Annotation
{
	/**
	 * Deve ser o nome de uma classe de validação que implemente a interface {@link \Zend\Validator\ValidatorInterface }
	 * @var string
	 */
	public $class;
} 