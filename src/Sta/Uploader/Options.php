<?php
/**
 * sell Project ${PROJECT_URL}
 *
 * @link      ${GITHUB_URL} Source code
 */

namespace Sta\Uploader;

use Zend\Validator\ValidatorInterface;

class Options extends \Sta\Util\StdClass
{
    /**
     * Validadores que serão aplicados sobre os arquivos recebidos.
     * @var ValidatorInterface[]
     */
    protected $validators = array();

    /**
     * Directory to store cache files.
     * @var string
     */
    protected $dir;

    /**
     * Defines how much sub-directories should be created.
     * @var int
     */
    protected $dirLevel = 3;

    /**
     * Nome da chave do array {@link $_FILES} quando estiver recebendo apenas um arquivo.
     * @var string
     */
    protected $keyOneFile = 'uploadedfile';

    /**
     * Nome da chave do array {@link $_FILES} quando estiver recebendo múltiplos arquivos.
     * @var string
     */
    protected $keyMultipleFiles = 'uploadedfiles';

    /**
     * @param string $dir
     * @return $this
     */
    public function setDir($dir)
    {
        $this->dir = $dir;

        return $this;
    }

    /**
     * @return string
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * @param int $dirLevel
     * @return $this
     */
    public function setDirLevel($dirLevel)
    {
        $this->dirLevel = $dirLevel;

        return $this;
    }

    /**
     * @return int
     */
    public function getDirLevel()
    {
        return $this->dirLevel;
    }

    /**
     * @param string $keyMultipleFiles
     * @return $this
     */
    public function setKeyMultipleFiles($keyMultipleFiles)
    {
        $this->keyMultipleFiles = $keyMultipleFiles;

        return $this;
    }

    /**
     * @return string
     */
    public function getKeyMultipleFiles()
    {
        return $this->keyMultipleFiles;
    }

    /**
     * @param string $keyOneFile
     * @return $this
     */
    public function setKeyOneFile($keyOneFile)
    {
        $this->keyOneFile = $keyOneFile;

        return $this;
    }

    /**
     * @return string
     */
    public function getKeyOneFile()
    {
        return $this->keyOneFile;
    }
    
    /**
     * @param \Zend\Validator\ValidatorInterface $validator
     * @return $this
     */
    public function addValidator(\Zend\Validator\ValidatorInterface $validator)
    {
        $this->validators[] = $validator;
        
        return $this;
    }
    
    /**
     * @param \Zend\Validator\ValidatorInterface[] $validators
     * @return $this
     */
    public function setValidators($validators)
    {
        foreach ($validators as $validator) {
            $this->addValidator($validator);
        } 

        return $this;
    }

    /**
     * @return \Zend\Validator\ValidatorInterface[]
     */
    public function getValidators()
    {
        return $this->validators;
    }

} 