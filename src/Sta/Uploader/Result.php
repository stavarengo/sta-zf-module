<?php
/**
 * sell Project ${PROJECT_URL}
 *
 * @link      ${GITHUB_URL} Source code
 */

namespace Sta\Uploader;

use Sta\Uploader;

class Result extends \Sta\Util\StdClass
{
    /**
     * O arquivo que gerou este resultado.
     * @var File
     */
    protected $receivedFile;
    /**
     * Caminho completo do arquivo final.
     * É o caminho do arquivo depios que ele já foi movido para o diretório definitivo.
     * Este atributo pode ser definido mesmo que o tenha ocorrido um erro e o arquivo não tenha sido movido.
     * @var string
     */
    protected $finalFileName;
    /**
     * Uma string explicando o que aconteceu de errado.
     * Se for vazia, então não ocorreu erros.
     * @var string
     */
    protected $error;

    public static function toString($uploaderType, Result $result)
    {
        $basename = ($result->getFinalFileName() ? basename($result->getFinalFileName()) : '');
        $data     = array(
            "file"         => $basename,
            'name'         => $basename,
            'type'         => $result->getReceivedFile()->getExtension(),
            'originalFile' => $result->getReceivedFile()->getName(),
            'error'        => $result->getError(),
        );

        if (!$result['error']) {
            unset($result['error']);
        }

        switch ($uploaderType) {
            case Uploader::UPLOADER_TYPE_FLASH:
                $r = '';
                foreach ($result as $k => $v) {
                    if (is_array($v)) {
                        $v = \Zend\Json\Json::encode($v);
                    }
                    $r .= "$k=$v,";
                }
                $r = rtrim($r, ',');
                break;

            case Uploader::UPLOADER_TYPE_IFRAME:
                $r = '<textarea>' . \Zend\Json\Json::encode($data) . '</textarea>';
                break;

            default:
                $r = \Zend\Json\Json::encode($data);
        }

        return $r;
    }
    
    /**
     * @param string $error
     * @return $this
     */
    public function setError($error)
    {
        $this->error = (string)$error;

        return $this;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $finalFileName
     * @return $this
     */
    public function setFinalFileName($finalFileName)
    {
        $this->finalFileName = (string)$finalFileName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFinalFileName()
    {
        return $this->finalFileName;
    }

    /**
     * @param \Sta\Uploader\File $receivedFile
     * @return $this
     */
    public function setReceivedFile(\Sta\Uploader\File $receivedFile)
    {
        $this->receivedFile = $receivedFile;

        return $this;
    }

    /**
     * @return \Sta\Uploader\File
     */
    public function getReceivedFile()
    {
        return $this->receivedFile;
    }

} 