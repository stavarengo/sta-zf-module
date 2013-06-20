<?php
namespace Sta\Semaphore;

/**
 * Emula o comportamento das funções de semáforo nativas do PHP (http://www.php.net/manual/en/book.sem.php).
 * @author Stavarengo
 */
class Emulator implements SemaphoreInterface
{

	private static $semaphores = array();
	private static $semaphoresHash = array();
	/**
	 * @var Emulator
	 */
	private static $instance;

	private function __construct()
	{
		$this->path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'semaphores';
		if (!file_exists($this->path)) {
			mkdir($this->path, 0777, true);
		}
	}

	/**
	 * @return Emulator
	 */
	public static function getInstance()
	{
		if (self::$instance == null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @see SemaphoreInterface::acquire()
	 */
	public function acquire($key)
	{
		$inicio   = time();
		$fileName = $this->path . DIRECTORY_SEPARATOR . $this->_getHash($key);

		$fp = false;
		do {
			// Gera o código usada para garantir que somente a mesma função que chamou este método poderá
			// liberar o semaforo.
			$code = microtime() . mt_rand();
			if (!file_exists($fileName)) {
				$fp = @fopen($fileName, 'x');
				if ($fp) {
					$content = file_get_contents($fileName);
					if (empty($content)) {
						file_put_contents($fileName, $code);
					}
					$content = file_get_contents($fileName);
					if ($content != $code) {
						@fclose($fp);
						$fp = false;
					}
				}
			}
			if (!$fp) {
				usleep(500000);
				$tempoDeEspera = time() - $inicio;
				if ($tempoDeEspera > 15) {
					//mais de 15 segundos esperando
					throw new Exception("Falha ao adiquirir o semáforo.");
				}
			}
		} while (!$fp);

		self::$semaphores[$key] = array(
			'resource' => $fp,
			'fileName' => $fileName,
			'code'     => $code
		);

		return $this->_createSemaphore($key, $code);
	}

	/**
	 * @see SemaphoreInterface::release()
	 */
	public function release($semaphore)
	{
		if (!is_array($semaphore) || !isset($semaphore['code']) || !isset($semaphore['key'])) {
			return;
		}

		$key = $semaphore['key'];
		if (isset(self::$semaphores[$key]) && self::$semaphores[$key] !== false) {
			$semaphore = self::$semaphores[$key];
			$code      = $semaphore['code'];

			//Se os códigos não forem iguais significa que a função que está chamando este método não
			//é a função que bloqueou o semaforo
			if ($code == $semaphore['code']) {
				$fileName = $semaphore['fileName'];
				$content  = file_get_contents($fileName);
				if ($content == $code) {
					self::$semaphores[$key] = false;
					fclose($semaphore['resource']);
					if (!unlink($fileName)) {
						throw new Exception("Falha ao liberar o semáforo.");
					}
				}
			}
		}
	}

	private function _getHash($key)
	{
		$key = (string)$key;
		if (!isset(self::$semaphoresHash[$key])) {
			self::$semaphoresHash[$key] = md5($key);
		}
		return self::$semaphoresHash[$key];
	}

	private function _createSemaphore($key, $code)
	{
		return array('code' => $code, 'key' => $key);
	}

}