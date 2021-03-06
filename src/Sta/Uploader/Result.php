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
     * Subdiretorios criados dentro da pasta destino do upload.
     * @var string
     */
    protected $subDir;
    /**
     * Uma string explicando o que aconteceu de errado.
     * Se for vazia, então não ocorreu erros.
     * @var string[]
     */
    protected $errors;

    public static function toString($uploaderType, Result $result)
    {
        $basename = ($result->getFinalFileName() ? basename($result->getFinalFileName()) : '');
        $data     = array(
            "file"         => $basename,
            'name'         => $basename,
            'type'         => $result->getReceivedFile()->getExtension(),
            'originalFile' => $result->getReceivedFile()->getName(),
            'errors'       => $result->getErrors(),
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
     * @param string[]|string $errors
     * @return $this
     */
    public function setErrors($errors)
    {
        $this->errors = (array)$errors;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getErrors()
    {
        return $this->errors;
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

    /**
     * @param string $subDir
     * @return $this
     */
    public function setSubDir($subDir)
    {
        $this->subDir = $subDir;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubDir()
    {
        return $this->subDir;
    }
    
} 