<?php
namespace Sta\Cmd;

class Invoker
{

	private function __construct()
	{
	}

	/**
	 * Executa um comando.
	 *
	 * @param Command $c
	 *
	 * @return mixin
	 *        Retorna o que o método {@link Command::execute()} do comando retornar.
	 */
	public static function invoke(Command $c)
	{
		return $c->execute();
	}

}