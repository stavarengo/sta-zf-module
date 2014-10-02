<?php
/**
 * irmo Project ${PROJECT_URL}
 *
 * @link      ${GITHUB_URL} Source code
 */
 
namespace Sta\Gravatar;

use Sta\Curl\Curl;

class Gravatar
{
    public function getPicture($email, $size, $pictureType = 'random', $scheme = null)
    {
        return $this->getPictureFromUrl($this->getUrl($email, $size, $pictureType, $scheme));
    }
    
    public function getPictureFromUrl($url)
    {
        $curl = new Curl();
        return $curl->getContent($url);
    }

    /**
     * @param $email
     *      Só é usado se o $pictureType for 'random'
     * @param $size
     * @param string $pictureType
     *      Veja aqui: https://en.gravatar.com/site/implement/images/
     * @param string $scheme
     *      Use: http, https ou null
     * @return string
     */
    public function getUrl($email, $size, $pictureType = 'random', $scheme = 'http')
    {
        if ($pictureType == 'random') {
            $d = array('identicon', 'monsterid', 'wavatar', 'retro');
            $c = 0;
            for ($i = 0; $i < strlen($email); $i++) {
                $c += ord($email[$i]);
            }
            $d = $d[$c % 4];
        } else {
            $d = $pictureType;
        }

        $gravatar = (!$scheme ? '' : $scheme . ':');
        $gravatar .= '//www.gravatar.com/avatar/' . md5(strtolower(trim($email)));
        $gravatar .= '?s=' . (int)$size;
        $gravatar .= '&d=' . $d;
        return $gravatar;
    }
} 