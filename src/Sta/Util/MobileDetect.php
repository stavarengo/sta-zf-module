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

    public function __construct(array $headers = null, $userAgent = null)
    {
        // Veja http://stackoverflow.com/a/32147456/2397394
        $updateSession = false;
        if ($userAgent === null) {
            if (isset($_SESSION['\Sta\Util\MobileDetect::userAgent'])) {
                $userAgent = $_SESSION['\Sta\Util\MobileDetect::userAgent'];
            } else {
                $updateSession = true;
            }
        }
        
        parent::__construct($headers, $userAgent);
        
        if ($updateSession) {
            $_SESSION['\Sta\Util\MobileDetect::userAgent'] = $this->getUserAgent();
        }
    }

} 