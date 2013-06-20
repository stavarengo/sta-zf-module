<?php
namespace Sta\Semaphore;

/**
 * Cria semáforos usando as funções nativas do PHP (http://www.php.net/manual/en/book.sem.php).
 * @author Stavarengo
 */
class Native implements SemaphoreInterface
{

	/**
	 * @var Native
	 */
	private static $instance;

	/**
	 * @return Native
	 */
	public static function getInstance()
	{
		if (self::$instance == null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function hasNativeSuporte()
	{
		return (function_exists('sem_ release') && function_exists('sem_get') && function_exists('sem_acquire'));
	}

	/**
	 * @see SemaphoreInterface::acquire()
	 */
	public function acquire($key)
	{
		if (!$sem_id = sem_get($key)) {
			throw new Exception("Falha ao adiquirir o ID do semáforo.");
		}
		if (!sem_acquire($sem_id)) {
			throw new Exception("Falha ao adiquirir o semáforo.");
		}
		return $sem_id;
	}

	/**
	 * @see SemaphoreInterface::release()
	 */
	public function release($sem_id)
	{
		if (!sem_release($sem_id)) {
			throw new Exception("Não consegui liberar o semáforo.");
		}
	}

}