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
	 * Deve ser o nome de uma classe que implemente {@link \Sta\Util\EntityToArray\Converter }
	 * @var string
	 */
	public $class;
} 