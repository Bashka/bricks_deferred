<?php
namespace Bricks\Deferred\Queue;

/**
 * Очередь отложенных задач.
 *
 * @author Artur Sh. Mamedbekov
 */
interface Queue{
	/**
	 * Добавляет отложенную задачу.
	 *
	 * @param Task $task Отложенная задача.
	 */
	public function push(Task $task);

	/**
	 * Получает все текущие задачи.
	 *
	 * @param int $time Дата актуальности. Данная временная метка определяет 
	 * момент времени, используемый для выявления текущих задач.
	 *
	 * @return Task[] Массив текущих задач.
	 */
	public function get($time);

	/**
	 * Помечает задачу как обработанную.
	 *
	 * @param int $taskId Идентификатор целевой задачи.
	 */
	public function done($taskId);
}
