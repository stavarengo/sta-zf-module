<?php
namespace Sta\Util;

class EstadoBrasileiro
{
	/**
	 * @var string
	 */
	protected $sigla;

	/**
	 * @var string
	 */
	protected $nome;

	function __construct($nome, $sigla)
	{
		$this->nome  = $nome;
		$this->sigla = $sigla;
	}

	/**
	 * @return string
	 */
	public function getNome()
	{
		return $this->nome;
	}

	/**
	 * @return string
	 */
	public function getSigla()
	{
		return $this->sigla;
	}

}