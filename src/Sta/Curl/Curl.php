<?php
/**
 * irmo Project ${PROJECT_URL}
 *
 * @link      ${GITHUB_URL} Source code
 */

namespace Sta\Curl;

class Curl
{
    public function getContent($url, array $params = array())
    {
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT        => 5,
        );
        foreach ($params as $k => $v) {
            $options[$k] = $v;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $data = curl_exec($ch);
        
        /* Check for 404 (file not found). */
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if($httpCode == 404) {
            $data = null;
        }
        
        curl_close($ch);

        return $data;
    }
} 