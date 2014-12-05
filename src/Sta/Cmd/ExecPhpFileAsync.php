<?php
namespace Sta\Cmd;

/**
 *  Execute command via shell and return the complete output as a string.
 * @package Sta\Cmd
 */
class ExecPhpFileAsync implements Command
{

	/**
	 * @var string
	 */
	private $phpFile = null;

	public function __construct($phpFile)
	{
		$this->phpFile = (string)$phpFile;
	}

	public function execute()
	{
        return Invoker::invoke(new BackgroundExec('php -f "' . $this->phpFile . '"'));
	}
}