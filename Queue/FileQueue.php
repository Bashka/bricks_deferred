<?php
namespace Bricks\Deferred\Queue;

/**
 * Очередь задач на основе файла.
 */
class FileQueue extends ArrayQueue{
	/**
	 * @var string Адрес файлового хранилища.
	 */
	private $file;

	private function initQueue(){
		$resource = file_exists($this->file)? fopen($this->file, 'r') : fopen($this->file, 'a');
		clearstatcache();
		$fsize = filesize($this->file);
		$this->queue = ($fsize > 0)? unserialize(fread($resource, $fsize)) : [];
		fclose($resource);
	}

	private function saveQueue(){
		$resource = fopen($this->file, 'w');
		fwrite($resource, serialize($this->queue));
		fclose($resource);
	}

	/**
	 * @param string $file Адрес файла, выступающего в качестве хранилища.
	 */
	public function __construct($file){
		parent::__construct();
		$this->file = $file;
	}

	/**
	 * @see Bricks\Deferred\Queue\Queue::push
	 */
	public function push(Task $task){
		$this->initQueue();
		parent::push($task);
		$this->saveQueue();
	}

	/**
	 * @see Bricks\Deferred\Queue\Queue::get
	 */
	public function get($time){
		$this->initQueue();
		return parent::get($time);
	}

	/**
	 * @see Bricks\Deferred\Queue\Queue::done
	 */
	public function done($taskId){
		$this->initQueue();
		parent::done($taskId);
		$this->saveQueue();
	}
}
