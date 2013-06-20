<?php
namespace Sta\Cmd;

/**
 * Interface para classes de comando.
 * @author Stavarengo
 */
interface Command
{

	/**
	 * Executa o comando.
	 * @return mixin
	 */
	public function execute();

}