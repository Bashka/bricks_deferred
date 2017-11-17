<?php
namespace Bricks\Deferred;
use Bricks\Deferred\Queue\Task;
use Bricks\Deferred\Queue\Queue;

/**
 * Менеджер отложенных задач.
 *
 * @author Artur Sh. Mamedbekov
 */
class TaskManager{
	/**
	 * @var Queue Очередь отложенных задач.
	 */
	private $queue;

	/**
	 * @var callable Фабрика обработчиков задач.
	 */
	private $handlerFactory;

	/**
	 * @var callable Обработчик исключительных ситуаций.
	 */
	private $errorHandler;

	protected function createHandler($handler){
		return call_user_func_array($this->handlerFactory, [$handler]);
	}

	protected function handleError(\Exception $exception){
		return call_user_func_array($this->errorHandler, [$exception]);
	}

	/**
	 * @param Queue $queue Очередь отложенных задач.
	 */
	public function __construct(Queue $queue){
		$this->queue = $queue;
		$this->handlerFactory = function($handler){
			return new $handler;
		};
		$this->errorHandler = function(\Exception $exception){
		};
	}

	/**
	 * Устанавливает фабрику обработчиков задач.
	 * Фабрика принимает в качестве параметр имя целевого обработчика и должна 
	 * возвращать его проинициализированный экземпляр.
	 *
	 * @param callable $handlerFactory Фабрика обработчиков.
	 */
	public function setHandlerFactory(callable $handlerFactory){
		$this->handlerFactory = $handlerFactory;
	}

	/**
	 * Устанавливает обработчик исключительных ситуаций.
	 * Обработчик принимает в качестве параметра экземпляр исключения.
	 *
	 * @param callable $errorHandler Обработчик исключительных ситуаций.
	 */
	public function setErrorHandler(callable $errorHandler){
		$this->errorHandler = $errorHandler;
	}

	/**
	 * Добавляет отложенную задачу.
	 *
	 * @param int $start Временная метка, определяющая момент выполнения задачи.
	 * @param string $handler Имя обработчика задачи.
	 * @param mixed $data [optional] Данные задачи.
	 */
	public function push($start, $handler, $data = null){
		$task = new Task;
		$task->start = $start;
		$task->handler = $handler;
		$task->data = $data;

		$this->queue->push($task);
	}

	/**
	 * Выполняет обработку актуальных задач.
	 *
	 * @param int $time Временная метка, определяющая момент актуальности задач.	
	 * По умолчанию используется текущее время.
	 */
	public function controll($time = null){
		if(is_null($time)){
			$time = time();
		}

		foreach($this->queue->get($time) as $task){
			$handler = $this->createHandler($task->handler);
			try{
				$handler->handle($task->data);
			}
			catch(\Exception $exception){
				$this->handleError($task->id, $exception);
			}
			$this->queue->done($task->id);
		}
	}
}
