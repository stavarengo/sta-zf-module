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
        return $this->_getContent($url, $params);
    }

    public function getContentByPost($url, $postBody, array $params = array())
    {
        $params[CURLOPT_POST ] = true;
        $params[CURLOPT_POSTFIELDS] = $postBody;
        
        return $this->_getContent($url, $params);
    }
    
    /**
     * @param array $params
     * @return array
     */
    private function _getOptions(array $params)
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

        return $options;
    }

    /**
     * @param $url
     * @param array $params
     * @return mixed|null
     */
    private function _getContent($url, array $params)
    {
        $options = $this->_getOptions($params);

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $data = curl_exec($ch);

        /* Check for 404 (file not found). */
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode == 404) {
            $data = null;
        }

        curl_close($ch);

        return $data;
    }
} 