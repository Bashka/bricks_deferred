<?php
namespace Bricks\Deferred;
use Bricks\Deferred\Queue\ArrayQueue;
require_once('tests/TaskCreatorHelper.php');
require_once('Queue/Queue.php');
require_once('Queue/ArrayQueue.php');

/**
 * @author Artur Sh. Mamedbekov
 */
class ArrayQueueTest extends \PHPUnit_Framework_TestCase{
	use TaskCreatorHelper;

	private function createEmptyQueue(){
		return new ArrayQueue;
	}

	private function createFullQueue(array $tasks){
		assert('count($tasks) > 0');

		$queue = new ArrayQueue;
		foreach($tasks as $task){
			$queue->push($task);
		}

		return $queue;
	}


	public function testPush_emptyQueue_shouldPushTask(){
		$queue = $this->createEmptyQueue();
		$task = $this->createTask(1, 'test');

		$queue->push($task);

		$this->assertEquals([$task], $queue->get(1), 'Добавление задачи в пустую очередь');
	}

	public function testPush_emptyQueue_shouldSetId(){
		$queue = $this->createEmptyQueue();
		$task = $this->createTask(1, 'test');

		$queue->push($task);

		$this->assertEquals(1, $task->id, 'Установка идентификатора задаче, при добавлении в пустую очередь');
	}

	public function testPush_maxStartForFullQueue_shouldPushTaskToEndQueue(){
		$startTasks = [
			$this->createTask(1, 'a'),
			$this->createTask(2, 'b'),
			$this->createTask(3, 'c'),
		];
		$queue = $this->createFullQueue($startTasks);
		$task = $this->createTask(4, 'd');

		$queue->push($task);

		$this->assertEquals(array_merge($startTasks, [$task]), $queue->get(4), 'Добавление задачи с максимальным сроком исполнения в полную очередь');
	}

	public function testPush_maxStartForFullQueue_shouldSetNextId(){
		$startTasks = [
			$this->createTask(1, 'a'),
			$this->createTask(2, 'b'),
			$this->createTask(3, 'c'),
		];
		$queue = $this->createFullQueue($startTasks);
		$task = $this->createTask(4, 'd');

		$queue->push($task);

		$this->assertEquals(4, $task->id, 'Установка идентификатора задаче с максимальным сроком исполнения, при добавлении в полную очередь');
	}

	public function testPush_middleStartForFullQueue_shouldSortTasks(){
		$startTasks = [
			$this->createTask(1, 'a'),
			$this->createTask(2, 'b'),
			$this->createTask(4, 'd'),
		];
		$queue = $this->createFullQueue($startTasks);
		$task = $this->createTask(3, 'c');

		$queue->push($task);

		$this->assertEquals([
			$startTasks[0],
			$startTasks[1],
			$task,
			$startTasks[2],
		], $queue->get(4), 'Добавление задачи со средним сроком исполнения в полную очередь');
	}

	public function testPush_middleStartForFullQueue_shouldSetNextId(){
		$startTasks = [
			$this->createTask(1, 'a'),
			$this->createTask(2, 'b'),
			$this->createTask(4, 'd'),
		];
		$queue = $this->createFullQueue($startTasks);
		$task = $this->createTask(3, 'c');

		$queue->push($task);

		$this->assertEquals(4, $task->id, 'Установка идентификатора задаче со средним сроком исполнения, при добавлении в полную очередь');
	}

	public function testGet_emptyQueue_shouldEmptyArrayReturn(){
		$queue = $this->createEmptyQueue();

		$this->assertEquals([], $queue->get(1), 'Получение пустого массива из пустой очереди');
	}

	public function testGet_fullQueue_shouldActualTasksReturn(){
		$startTasks = [
			$this->createTask(1, 'a'),
			$this->createTask(2, 'b'),
			$this->createTask(3, 'c'),
		];
		$queue = $this->createFullQueue($startTasks);
		
		$this->assertEquals([
			$startTasks[0],
			$startTasks[1],
		], $queue->get(2), 'Получение актуальных задач из полной очереди');
	}

	public function testDone(){
		$startTasks = [
			$this->createTask(1, 'a', null, 1),
			$this->createTask(2, 'b', null, 2),
			$this->createTask(3, 'c', null, 3),
		];
		$queue = $this->createFullQueue($startTasks);
		
		$queue->done(1);
		$this->assertEquals([
			$startTasks[1],
		], $queue->get(2), 'Исключение задач из полной очереди');
	}
}
