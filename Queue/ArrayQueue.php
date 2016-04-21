<?php
namespace Bricks\Deferred\Queue;

/**
 * Очередь задач на основе массива.
 * Данная реализация не является перманентной.
 *
 * @author Artur Sh. Mamedbekov
 */
class ArrayQueue implements Queue{
	/**
	 * @var Task[] Упорядоченный по возрастанию временной метки старта массив 
	 * задач.
	 */
	protected $queue;

	public function __construct(){
		$this->queue = [];
	}

	/**
	 * @see Bricks\Deferred\Queue\Queue::push
	 */
	public function push(Task $task){
		$queueLength = count($this->queue);
		$pos = $queueLength - 1;
		if($pos != -1){
			while($this->queue[$pos]->start > $task->start && $pos != 0){
				$pos--;
			}
			$this->queue = array_merge(array_slice($this->queue, 0, $pos + 1), [$task], array_slice($this->queue, $pos + 1));
			$task->id = $queueLength + 1;
		}
		else{
			$this->queue[] = $task;
			$task->id = 1;
		}
	}

	/**
	 * @see Bricks\Deferred\Queue\Queue::get
	 */
	public function get($time){
		$result = [];
		reset($this->queue);
		while(true){
			$current = current($this->queue);
			if($current === false){
				break;
			}
			if($current->start > $time){
				break;
			}
			$result[] = $current;
			if(next($this->queue) === false){
				break;
			}
		}

		return $result;
	}

	/**
	 * @see Bricks\Deferred\Queue\Queue::done
	 */
	public function done($taskId){
		foreach($this->queue as $pos => $task){
			if($task->id == $taskId){
				array_splice($this->queue, $pos, 1);
				break;
			}
		}
	}
}
