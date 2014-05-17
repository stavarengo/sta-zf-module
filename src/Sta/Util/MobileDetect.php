<?php
namespace Sta\Util;

require_once __DIR__ . '/../../../../../vendor/Mobile-Detect/Mobile_Detect.php';

class MobileDetect extends \Mobile_Detect
{
    protected static $instance;

    /**
     * @return MobileDetect
     */
    public function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
} 