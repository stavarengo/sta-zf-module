<?php
/**
 * sell Project ${PROJECT_URL}
 *
 * @link      ${GITHUB_URL} Source code
 */

namespace Sta\Uploader;

class File extends \Sta\Util\StdClass
{
    /**
     * The original name of the file on the client machine.
     * @var string
     */
    protected $name;
    /**
     * The mime type of the file, if the browser provided this information. An example would be "image/gif".
     * This mime type is however not checked on the PHP side and therefore don't take its value for granted.
     * @var string
     */
    protected $type;
    /**
     * The temporary filename of the file in which the uploaded file was stored on the server.
     * Será o caminho completo até o arquivo temporário.
     * @var string
     */
    protected $tempName;
    /**
     * The size, in bytes, of the uploaded file.
     * @var int
     */
    protected $size;
    /**
     * The error code associated with this file upload.
     * Será uma das constantantes UPLOAD_ERR_*
     * @var int
     * @see http://php.net/manual/en/features.file-upload.errors.php
     */
    protected $error;
    /**
     * A extensão do arquivo sem o "ponto".
     * Ex: para o arquivo exemplo.php, a extenão retornada será "php".
     * @var string
     */
    protected $extension = null;

    /**
     * @param Options $options
     * @return \Sta\Uploader\File[]
     */
    public static function getFilesFromGlobalVar(Options $options)
    {
        $keyOneFile       = $options->getKeyOneFile();
        $keyMultipleFiles = $options->getKeyMultipleFiles();

        $files = array();
        if (array_key_exists($keyMultipleFiles, $_FILES)) {
            foreach ($_FILES[$keyMultipleFiles] as $key1 => $value1) {
                foreach ($value1 as $key2 => $value2) {
                    $files[$key2][$key1] = $value2;
                }
            }
        } else if (array_key_exists($keyOneFile, $_FILES)) {
            $files = array($_FILES[$keyOneFile]);
        }

        $result = array();
        foreach ($files as $file) {
            $result[] = new \Sta\Uploader\File(
                array(
                    'name'     => $file['name'],
                    'type'     => $file['type'],
                    'tempName' => $file['tmp_name'],
                    'size'     => $file['size'],
                    'error'    => $file['error'],
                )
            );
        }

        return $result;
    }

    /**
     * Retorna os dados desta instancia em um array igual ao valor que estaria setado na variavel {@link $_FILES}
     * @return array
     */
    public function toStandartPhpArray()
    {
        return array(
            'type'     => $this->getType(),
            'name'     => $this->getName(),
            'tmp_name' => $this->getTempName(),
            'size'     => $this->getSize(),
            'error'    => $this->getError(),
        );
    }
    
    /**
     * @param int $error
     * @return $this
     */
    public function setError($error)
    {
        $this->error = (int)$error;

        return $this;
    }

    /**
     * @return int
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = (string)$name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param int $size
     * @return $this
     */
    public function setSize($size)
    {
        $this->size = (int)$size;

        return $this;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param string $tempName
     * @return $this
     */
    public function setTempName($tempName)
    {
        $this->tempName = (string)$tempName;

        return $this;
    }

    /**
     * @return string
     */
    public function getTempName()
    {
        return $this->tempName;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = (string)$type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    
    public function getExtension() {
        if ($this->extension === null) {
            $this->extension = pathinfo($this->getName(), PATHINFO_EXTENSION);
        }
        return $this->extension;
    }
} 