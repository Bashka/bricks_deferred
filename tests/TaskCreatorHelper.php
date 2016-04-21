<?php
namespace Bricks\Deferred;
use Bricks\Deferred\Queue\Task;
require_once('Queue/Task.php');

trait TaskCreatorHelper{
	private function createTask($start, $handler, $data = null, $id = null){
		assert('is_int($start)');
		assert('is_string($handler) && !empty($handler)');

		$task = new Task;
		$task->id = $id;
		$task->start = $start;
		$task->handler = $handler;
		$task->data = $data;

		return $task;
	}
}
