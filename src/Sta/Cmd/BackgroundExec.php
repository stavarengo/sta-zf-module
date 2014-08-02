<?php
namespace Sta\Cmd;

class BackgroundExec implements Command
{

	const PRIO_MUITO_BAIXA = 1;
	const PRIO_BAIXA       = 2;
	const PRIO_NORMAL      = 3;
	const PRIO_ALTA        = 4;
	const PRIO_MUITO_ALTA  = 5;

	/**
	 * @var string
	 */
	private $cmd;

	/**
	 * @var int
	 */
	private $prioridade;

	/**
	 * @var bool
	 */
	private $hide;

	public function __construct($cmd, $prioridade = self::PRIO_NORMAL, $hide = true)
	{
		$this->cmd        = $cmd;
		$this->prioridade = $prioridade;
		$this->hide       = $hide;
	}

	public function execute()
	{
		if (substr(php_uname(), 0, 7) == "Windows") {
			$prio = $this->getIntPrioToWinPrio($this->prioridade);
			$cmd  = "start $prio " . ($this->hide === true ? '/B' : '') . " $this->cmd";
		} else {
			$cmd = $this->cmd . " > /dev/null &";
		}
        Invoker::invoke(new ShellExec($cmd));
	}

	private function getIntPrioToWinPrio($prio)
	{
		if ($prio == self::PRIO_MUITO_BAIXA) {
			return '/LOW';
		} else if ($prio == self::PRIO_BAIXA) {
			return '/BELOWNORMAL';
		} else if ($prio == self::PRIO_ALTA) {
			return '/ABOVENORMAL';
		} else if ($prio == self::PRIO_MUITO_ALTA) {
			return '/HIGH';
		} else {
			return '/NORMAL';
		}
	}
}