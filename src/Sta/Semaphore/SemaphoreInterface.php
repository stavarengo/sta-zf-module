<?php
namespace Sta\Semaphore;

/**
 * @author Stavarengo
 */
interface SemaphoreInterface
{

	/**
	 * Obtem acesso exclusivo.
	 *
	 * @param int $key
	 *
	 * @return mixed
	 *        O semáforo criado. Este retorno deve ser usado p/ liberar o semáforo com a função
	 *        {@link SemaphoreInterface::release() }
	 * @throws Exception
	 *        Se não conseguiu obter acesso exclusivo.
	 */
	public function acquire($key);

	/**
	 * Libera o semáforo criado com {@link SemaphoreInterface::acquire() }.
	 * Se o semafóro não existir nada é feito.
	 *
	 * @param mixed $semaphore
	 *        O retorno da função {@link SemaphoreInterface::acquire() }
	 *
	 * @return void
	 * @throws Exception
	 *        Se não conseguiur liberar o semáforo.
	 */
	public function release($semaphore);
}