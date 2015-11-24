<?php
namespace Sta\Cmd;

/**
 *  Execute command via shell and return the complete output as a string.
 *
 * @package Sta\Cmd
 */
class ExecPhpFileAsync implements Command
{

    /**
     * @var string
     */
    private $phpFile = null;
    private $args = '';

    public function __construct($phpFile, $args = '')
    {
        $this->phpFile = (string)$phpFile;
        $this->args    = (string)$args;
    }

    public function execute()
    {
        $cmd = 'php -f "' . $this->phpFile . '"';
        if ($this->args) {
            $cmd .= ' -- ' . $this->args;
        }

        return Invoker::invoke(new BackgroundExec($cmd));
    }
}