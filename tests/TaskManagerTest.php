<?php
namespace Bricks\Deferred;
use Bricks\Deferred\Queue\Queue;
require_once('tests/TaskCreatorHelper.php');
require_once('Queue/Queue.php');
require_once('Queue/ArrayQueue.php');
require_once('TaskManager.php');

/**
 * @author Artur Sh. Mamedbekov
 */
class TaskManagerTest extends \PHPUnit_Framework_TestCase{
	use TaskCreatorHelper;

	private function createMockHandler(){
		return $this->getMock(Handler::class, ['handle']);
	}

	private function createMockQueue(){
		return $this->getMock(Queue::class);
	}

	private function createMockEmptyQueue(){
		$mock = $this->createMockQueue();
		$mock->expects($this->once())
			->method('get')
			->will($this->returnValue([]));

		return $mock;
	}

	private function createMockFullQueue(array $tasks){
		$mock = $this->createMockQueue();
		$mock->expects($this->once())
			->method('get')
			->will($this->returnValue($tasks));

		return $mock;
	}

	private function createManagerWithMockQueue($mockQueue){
		return new TaskManager($mockQueue);
	}

	private function createMockManagerWithMockQueue($mockQueue, array $methods){
		return $this->getMock(TaskManager::class, $methods, [$mockQueue]);
	}

	private function createRunableMockManager($mockQueue, $task){
		$manager = $this->createMockManagerWithMockQueue($mockQueue, ['createHandler']);
		$this->assertCreateHandler($manager, $task->handler, $this->createMockHandler());

		return $manager;
	}

	private function assertPushTask($mock, $task){
		$mock->expects($this->once())
			->method('push')
			->with($this->equalTo($task));
	}

	private function assertRunHandler($mock, $data){
		$mock->expects($this->once())
			->method('handle')
			->with($this->equalTo($data));
	}

	private function assertCreateHandler($mock, $handlerName, $handler){
		$mock->expects($this->once())
			->method('createHandler')
			->with($this->equalTo($handlerName))
			->will($this->returnValue($handler));
	}


	public function testSetHandlerFactory(){
		$queue = $this->createMockQueue();
		$manager = $this->createManagerWithMockQueue($queue);

		$manager->setHandlerFactory(function($handler){
		});
	}

	public function testSetErrorHandler(){
		$queue = $this->createMockQueue();
		$manager = $this->createManagerWithMockQueue($queue);

		$manager->setErrorHandler(function(\Exception $exception){
		});
	}

	public function testPush_shouldPushTask(){
		$queue = $this->createMockQueue();
		$manager = $this->createManagerWithMockQueue($queue);
		$task = $this->createTask(1, 'test', 'data');

		$this->assertPushTask($queue, $task);

		$manager->push($task->start, $task->handler, $task->data);
	}

	public function testControll_emptyQueue_shouldEndRun(){
		$queue = $this->createMockEmptyQueue();
		$manager = $this->createMockManagerWithMockQueue($queue, ['createHandler']);

		$manager->expects($this->never())
			->method('createHandler');
		
		$manager->controll(1);
	}

	public function testControll_fullQueue_shouldRunHandlers(){
		$task = $this->createTask(1, 'test', 'data');
		$queue = $this->createMockFullQueue([
			$task,
		]);
		$handler = $this->createMockHandler();
		$manager = $this->createMockManagerWithMockQueue($queue, ['createHandler']);

		$this->assertCreateHandler($manager, $task->handler, $handler);
		$this->assertRunHandler($handler, $task->data);
		
		$manager->controll(1);
	}

	public function testControll_fullQueue_shouldDoneTasks(){
		$task = $this->createTask(1, 'test', 'data', 1);
		$queue = $this->createMockFullQueue([
			$task,
		]);
		$manager = $this->createRunableMockManager($queue, $task);

		$queue->expects($this->once())
			->method('done')
			->with($this->equalTo($task->id));
		
		$manager->controll(1);
	}

	public function testControll_withoutParam_shouldUseCurrentTime(){
		$time = time();
		$task = $this->createTask($time - 5, 'test', 'data', 1);
		$queue = $this->createMockFullQueue([
			$task,
		]);
		$manager = $this->createRunableMockManager($queue, $task);

		$manager->controll();
	}
}
