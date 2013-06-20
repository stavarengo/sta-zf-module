<?php
namespace Sta;

use Sta\Semaphore\Emulator;
use Sta\Semaphore\Exception;
use Sta\Semaphore\Native;

/**
 * Esta classe pode ser usada para obter acesso exclusivo a recursos na máquina local.
 * Quando disponível será usado as funções de semáfora nativas do PHP (http://www.php.net/manual/pt_BR/ref.sem.php).
 * Como estas funções não fazem parte da instalação padrão, a {@link Semaphore } vai simular um semáforo
 * usando arquivos temporários para garantir o mesmo comportamento as funções nativas do PHP.
 * @author Stavarengo
 */
class Semaphore
{

	/**
	 * Lista dos semáforos criados. Esta lista é usada para liberar os semáforos quando o script termina.
	 * @see Semaphore::releaseAll()
	 * @var array
	 */
	private static $semaphores = array();
	/**
	 * @var Semaphore
	 */
	private static $instance;
	/**
	 * @var SemaphoreInterface
	 */
	private $_implementation = null;

	private function __construct()
	{
		register_shutdown_function(array($this, 'releaseAll'));

		if (Native::hasNativeSuporte()) {
			$this->_implementation = Native::getInstance();
		} else {
			$this->_implementation = Emulator::getInstance();
		}
	}

	/**
	 * @return Semaphore
	 */
	public static function getInstance()
	{
		if (self::$instance == null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Obtem acesso exclusivo.
	 *
	 * @param int $key
	 *
	 * @return array
	 *        O semáforo criado. Este retorno deve ser usado liberar o semáforo com a função {@link Semaphore::release() }
	 * @throws Exception
	 *        Se não conseguiu obter acesso exclusivo.
	 */
	public static function acquire($key)
	{
		$semaphore              = self::getInstance()->_acquire($key);
		self::$semaphores[$key] = self::getInstance()->_createSemaphore($key, $semaphore);
		return self::$semaphores[$key];
	}

	/**
	 * Libera o semáforo criado com {@link Semaphore::acquire() }.
	 * Se o semafóro não existir nada é feito.
	 *
	 * @param array $semaphore
	 *        O retorno da função {@link Semaphore::acquire() }
	 *
	 * @return void
	 * @throws Exception
	 *        Se não conseguiur liberar o semáforo.
	 */
	public static function release(array $semaphore)
	{
		if (!isset($semaphore['key']) || !isset($semaphore['semaphore'])) {
			//O parâmetro recebido não está no formato válido.
			return;
		}

		self::getInstance()->_release($semaphore['semaphore']);

		$key = $semaphore['key'];
		if (isset(self::$semaphores[$key])) {
			unset(self::$semaphores[$key]);
		}
	}

	/**
	 * Função invocada automaticamente quando o script é finalizado.
	 * O registro desta função é feito com {@link register_shutdown_function() } no construtor desta classe.
	 *
	 * @see register_shutdown_function()
	 */
	public function releaseAll()
	{
		foreach (self::$semaphores as $semaphore) {
			if ($semaphore) {
				self::release($semaphore);
			}
		}
	}

	/* PRIVATE METHODS */

	/**
	 * @see Semaphore::acquire()
	 */
	private function _acquire($key)
	{
		return $this->_implementation->acquire($key);
	}

	/**
	 * @see Semaphore::release()
	 */
	private function _release($semaphore)
	{
		return $this->_implementation->release($semaphore);
	}

	/**
	 *
	 * @param int $key
	 *
	 * @param $semaphore
	 *
	 * @return array
	 *        O retorno da função {@link Semaphore::acquire() }.
	 */
	private function _createSemaphore($key, $semaphore)
	{
		return array('key' => $key, 'semaphore' => $semaphore);
	}
}