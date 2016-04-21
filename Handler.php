<?php
namespace Bricks\Deferred;

/**
 * Обработчик отложенных задач.
 *
 * @author Artur Sh. Mamedbekov
 */
interface Handler{
	/**
	 * Выполняет отложенную задачу.
	 *
	 * @param mixed $data [optional] Данные задачи.
	 */
	public function handle($data = null);
}
