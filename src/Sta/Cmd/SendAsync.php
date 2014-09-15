<?php
namespace Sta\Cmd;

class SendAsync implements Command
{
    protected $phpScriptFile;

    public function __construct($phpScriptFile, array $args = array())
    {
        $phpScriptFile = realpath($phpScriptFile);
        if (!$phpScriptFile) {
            throw new InvalidArgumentException('O script "' . $phpScriptFile . '" não existe.');
        }
        $this->phpScriptFile = $phpScriptFile;
        $this->args = $args;
    }

    public function execute()
    {
        $cmd = 'php -f "' . $this->phpScriptFile . '" ';
        
        foreach ($this->args as $key => $arg) {
            $cmd .= (is_int($key) ? '' : $key . '=') . $arg . " ";
        }

        return Invoker::invoke(new BackgroundExec($cmd));
    }
}