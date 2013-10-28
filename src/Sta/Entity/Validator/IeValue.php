<?php


namespace Sta\Entity\Validator;


/**
 * @author: Stavarengo
 */
class IeValue
{

	/**
	 * O número da inscrição estadual.
	 * @var string
	 */
	public $ie;

	/**
	 * A sigla do Estado que a inscrição pertence.
	 * @var string
	 */
	public $uf;

	/**
	 * @param string $ie
	 * @param string $uf
	 */
	function __construct($ie, $uf)
	{
		$this->ie = $ie;
		$this->uf = $uf;
	}
}