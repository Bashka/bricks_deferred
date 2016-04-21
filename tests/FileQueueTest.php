<?php
namespace Bricks\Deferred;
use Bricks\Deferred\Queue\FileQueue;
require_once('tests/TaskCreatorHelper.php');
require_once('Queue/Queue.php');
require_once('Queue/ArrayQueue.php');
require_once('Queue/FileQueue.php');

/**
 * @author Artur Sh. Mamedbekov
 */
class FileQueueTest extends \PHPUnit_Framework_TestCase{
	use TaskCreatorHelper;

	private function createFullStore(array $tasks){
		assert('count($tasks) > 0');

		$file = fopen(__DIR__ . '/filestore/full_queue.txt', 'a');
		fwrite($file, serialize($tasks));
		fclose($file);

		return new FileQueue(__DIR__ . '/filestore/full_queue.txt');
	}

	private function createEmptyStore(){
		return new FileQueue(__DIR__ . '/filestore/empty_queue.txt');
	}

	private function assertEmptyStoreExistsAndContains(array $tasks){
		$this->assertTrue(file_exists(__DIR__ . '/filestore/empty_queue.txt'));

		$resource = fopen(__DIR__ . '/filestore/empty_queue.txt', 'r');
		clearstatcache();
		$fsize = filesize(__DIR__ . '/filestore/empty_queue.txt');
		$this->assertTrue($fsize > 0);
		$data = unserialize(fread($resource, $fsize));
		$this->assertEquals($tasks, $data, 'Пустое хранилище заполненно задачами');
	}

	private function assertFullStoreContains(array $tasks){
		$this->assertTrue(file_exists(__DIR__ . '/filestore/full_queue.txt'));

		$resource = fopen(__DIR__ . '/filestore/full_queue.txt', 'r');
		clearstatcache();
		$fsize = filesize(__DIR__ . '/filestore/full_queue.txt');
		$this->assertTrue($fsize > 0);
		$data = unserialize(fread($resource, $fsize));
		$this->assertEquals($tasks, $data, 'Полное хранилище расширенно задачами');
	}


	public function tearDown(){
		if(file_exists(__DIR__ . '/filestore/full_queue.txt')){
			unlink(__DIR__ . '/filestore/full_queue.txt');
		}
		if(file_exists(__DIR__ . '/filestore/empty_queue.txt')){
			unlink(__DIR__ . '/filestore/empty_queue.txt');
		}
	}

	public function testPush_emptyStore_shouldCreateFilestore(){
		$task = $this->createTask(1, 'test', 'data');
		$queue = $this->createEmptyStore();

		$queue->push($task);

		$this->assertEmptyStoreExistsAndContains([$task]);
	}

	public function testPush_fullStore_shouldRewriteFilestore(){
		$oldTask = $this->createTask(1, 'test', 'data');
		$queue = $this->createFullStore([$oldTask]);
		$task = $this->createTask(2, 'test', 'data');

		$queue->push($task);

		$this->assertFullStoreContains([
			$oldTask,
			$task,
		]);
	}

	public function testDone_shouldRewriteFilestore(){
		$queue = $this->createFullStore([$this->createTask(1, 'test', 'data', 1)]);

		$queue->done(1);

		$this->assertFullStoreContains([]);
	}

	public function testGet(){
		$tasks = [
			$this->createTask(1, 'test', 'data'),
			$this->createTask(2, 'test', 'data'),
			$this->createTask(3, 'test', 'data'),
		];
		$queue = $this->createFullStore($tasks);

		$this->assertEquals([
			$tasks[0],
			$tasks[1],
		], $queue->get(2), 'Получение актуальных задач из полного хранилища');
	}
}
