<?php
namespace Sta\View\Helper;

use App\Env\Env;
use Zend\View\Helper\AbstractHelper;

class IsMobile extends AbstractHelper
{
	/**
	 * @var bool
	 */
	private $isMobile = null;

    /**
     * @var Env
     */
	private $env;

    /**
     * IsMobile constructor.
     * @param Env $env
     */
    public function __construct(Env $env)
    {
        $this->env = $env;
    }

    public function __invoke()
	{
		if ($this->isMobile === null && !($this->isMobile = $this->env->isDev())) {
			$this->isMobile = \App\MobileDetect\MobileDetect::getInstance()->isMobile();
		}
		return $this->isMobile;
	}
}