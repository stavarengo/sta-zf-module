<?php
namespace Sta\Util;

class MobileDetect extends \Mobile_Detect
{
    protected static $instance;

    /**
     * @return MobileDetect
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
} 