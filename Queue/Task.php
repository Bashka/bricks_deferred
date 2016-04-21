<?php
namespace Bricks\Deferred\Queue;

/**
 * Отложенная задача.
 *
 * @author Artur Sh. Mamedbekov
 */
class Task{
	/**
	 * @var int Идентификатор.
	 */
	public $id;

	/**
	 * @var int Временная метка, определяющая момент выполнения задачи.
	 */
	public $start;

	/**
	 * @var string Имя обработчика задачи.
	 */
	public $handler;

	/**
	 * @var mixed Данные задачи.
	 */
	public $data;
}
