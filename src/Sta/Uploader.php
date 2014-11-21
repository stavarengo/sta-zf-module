<?php
namespace Sta;

use Sta\Uploader\Exception;
use Sta\Uploader\File;
use Sta\Uploader\InvalidUploaderTypeException;
use Sta\Uploader\Options;
use Sta\Uploader\Result;
use Zend\Json\Json;
use Zend\Stdlib\ErrorHandler;

class Uploader
{
    const TAG = 'Sta\Uploader\UploaderController: ';

    const UPLOADER_TYPE_HTML5  = 'html5';
    const UPLOADER_TYPE_IFRAME = 'iframe';
    const UPLOADER_TYPE_FLASH  = 'flash';

    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $sl;
    /**
     * @var \Zend\Log\Logger
     */
    protected $logger;

    public function __construct(\Zend\ServiceManager\ServiceLocatorInterface $sl)
    {
        $this->sl     = $sl;
        $this->logger = $sl->get('log');
    }

    /**
     * @param $uploaderType
     *      Use uma das constantes {@link \Sta\Uploader\UploaderController}::UPLOADER_TYPE_*
     *
     * @param Options $options
     * @throws InvalidUploaderTypeException
     * @throws \Exception
     * @return Result[]
     */
    public function upload($uploaderType, Options $options)
    {
        $suportedUploadersTypes = array(
            self::UPLOADER_TYPE_FLASH,
            self::UPLOADER_TYPE_HTML5,
            self::UPLOADER_TYPE_IFRAME
        );
        if (!in_array($uploaderType, $suportedUploadersTypes)) {
            throw new InvalidUploaderTypeException("Invalid value for the parameter \"$uploaderType\".");
        }

        $this->logger->debug(self::TAG . "Iniciou. Conteúdo de \$_FILE = " . print_r($_FILES, true));
        $keyOneFile       = $options->getKeyOneFile();
        $keyMultipleFiles = $options->getKeyMultipleFiles();

        try {
            $files = File::getFilesFromGlobalVar($options);
            if (!count($files)) {
                throw new Exception\NoFilesFoundException('No uploaded file found. The \$_FILE global variable has neither the entry "' . $keyMultipleFiles . '" nor the entry "' . $keyOneFile . '".');
            }

            $this->logger->debug(
                self::TAG . "\$_FILES normalizado: " . print_r(
                    array_map(
                        function (File $file) {
                            return $file->toArray();
                        },
                        $files
                    ),
                    true
                )
            );

            $r = array();
            foreach ($files as $file) {
                $r[] = $this->handleUpload($file, $options);
            }

            $this->logger->debug(
                self::TAG . "'handleUpload' concluído. Result: " . print_r(
                    array_map(
                        function (Result $result) {
                            return $result->toArray();
                        },
                        $r
                    ),
                    true
                )
            );
        } catch (\Exception $e) {
            $this->logger->err(self::TAG . "Exceção: \n$e");
            throw $e;
        }
        
        return $r;
    }

    /**
     * @param \Sta\Uploader\File $file
     *      Os arquivos recebidos.
     *
     * @param Options $options
     *
     * @return Result
     */
    private function handleUpload(File $file, Options $options)
    {
        $dir     = $options->getDir();
        $destDir = $this->getDirPath($dir, $options->getDirLevel());

        $this->prepareDirectoryStructure($destDir);

        $extension = $file->getExtension();
        $this->logger->debug(
            self::TAG . "'handleUpload' recebeu: \nExtençao encontrada: '$extension'\n\$file: '" . print_r(
                $file->toArray(),
                true
            )
        );

        $this->logger->debug(self::TAG . "Destino do arquivo: '$destDir'");

        do {
            $newFileName = md5(microtime()) . ".$extension";
            $newFilePath = $destDir . DIRECTORY_SEPARATOR . $newFileName;
        } while (file_exists($newFilePath));

        $validators     = $options->getValidators();
        $realUploadFile = $file->getTempName();
        $erros          = array();
        foreach ($validators as $validator) {
            $msg = "Aplicando regras do validador '" . get_class($validator) . "'. ";
            if (!$validator->isValid($realUploadFile, $file)) {
                $msg .= 'VALIDAÇÃO REPROVADA! Motivos: ';
                foreach ($validator->getMessages() as $erro) {
                    $msg .= "\n\t$erro";
                    $erros[] = $erro;
                }
                $msg .= "\n";
            } else {
                $msg .= " VALIDAÇÃO APROVADA!\n";
            }
        }
        $this->logger->debug(self::TAG . $msg);

        $result = new Result();
        $result->setReceivedFile($file);
        $result->setFinalFileName($newFilePath);

        if (count($erros) > 0) {
            $result->setError(implode('\n', $erros));
            $this->logger->debug(
                self::TAG . "Saindo de 'handleUpdate'. Retorno: '" . Json::encode($result->toArray()) . "'"
            );

            return $result;
        } else {
            try {
                $this->logger->debug(
                    self::TAG . "Vou mover o arquivo de uploade: Origem '$realUploadFile', destino: '$newFilePath'"
                );
                ErrorHandler::start();
                if (!move_uploaded_file($realUploadFile, "$newFilePath")) {
                    $this->logger->debug(self::TAG . "Falha ao mover o arquivo '$realUploadFile' para '$newFilePath'");
                    $result->setError($this->sl->get('translator')->translate('Failed to receive your file.', 'Sta'));
                } else {
                    $this->logger->debug(self::TAG . "Sucesso! Arquivo '$realUploadFile' movido para '$newFilePath'.");
                    $this->logger->debug(
                        self::TAG . "'handleUpdate' concluído. Retorno: '" . Json::encode($result->toArray()) . "'"
                    );
                }
                ErrorHandler::stop();
            } catch (\Exception $e) {
                $this->logger->debug(self::TAG . "Exceção em 'handleUpdate': \n$e\n\n");
                $result->setError($e->getMessage());
                $this->logger->debug(
                    self::TAG . "Saindo de 'handleUpdate'. Retorno: '" . Json::encode($result->toArray()) . "'"
                );

            }
        }

        return $result;
    }

    /**
     * Retorna o caminho completo do diretorio já com os subdiretorios de acordo com quantidade de niveis necessário.
     *
     * @param $destDir
     * @param $dirLevels
     * @return string
     */
    protected function getDirPath($destDir, $dirLevels)
    {
        $destDir .= DIRECTORY_SEPARATOR;

        if ($dirLevels > 0) {
            // create up to 256 directories per directory level
            $hash = md5(time());
            for ($i = 0, $max = ($dirLevels * 2); $i < $max; $i += 2) {
                $destDir .= $hash[$i] . $hash[$i + 1] . DIRECTORY_SEPARATOR;
            }
        }

        return $destDir;
    }

    /**
     * Cria todos os diretórios e subdiretorios do $dirPath
     *
     * @param string $dirPath
     * @return void
     * @throws Exception\RuntimeException
     */
    protected function prepareDirectoryStructure($dirPath)
    {
        // Directory structure already exists
        if (file_exists($dirPath)) {
            return;
        }

        $perm = 0777;

        ErrorHandler::start();

        // build-in mkdir function sets permission together with current umask
        // which doesn't work well on multo threaded webservers
        // -> create directories one by one and set permissions

        // find existing path and missing path parts
        $parts = array();
        $path  = $dirPath;
        while (!file_exists($path)) {
            array_unshift($parts, basename($path));
            $nextPath = dirname($path);
            if ($nextPath === $path) {
                break;
            }
            $path = $nextPath;
        }

        // make all missing path parts
        foreach ($parts as $part) {
            $path .= DIRECTORY_SEPARATOR . $part;

            // create a single directory, set and reset umask immediately
            $res = mkdir($path, ($perm === false) ? 0777 : $perm, false);

            if (!$res) {
                $oct = ($perm === false) ? '777' : decoct($perm);
                ErrorHandler::stop();
                throw new Exception\RuntimeException(
                    "mkdir('{$path}', 0{$oct}, false) failed"
                );
            }

            if ($perm !== false && !chmod($path, $perm)) {
                $oct = decoct($perm);
                ErrorHandler::stop();
                throw new Exception\RuntimeException(
                    "chmod('{$path}', 0{$oct}) failed"
                );
            }
        }

        ErrorHandler::stop();
    }

}