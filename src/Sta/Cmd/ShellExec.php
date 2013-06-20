<?php
namespace Sta\Cmd;

class ShellExec implements Command
{

	/**
	 * @var string
	 */
	private $cmd = null;

	public function __construct($cmd)
	{
		$this->cmd = (string)$cmd;
	}

	public function execute()
	{
		if (substr(php_uname(), 0, 7) == "Windows") {
			$cmd = str_replace('>', '^>', $this->cmd);
			pclose(popen($cmd, "r"));
		} else {
			exec($this->cmd);
		}
	}
}