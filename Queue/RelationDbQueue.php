<?php
namespace Bricks\Deferred\Queue;

/**
 * Очередь задач на основе реляционной базы данных.
 *
 * @author Artur Sh. Mamedbekov
 */
class RelationDbQueue implements Queue{
	/**
	 * @var \PDO Используемый интерфейс доступа к реляционной базе данных.
	 */
	private $pdo;

	/**
	 * @var \PDOStatement Запрос на добавления нового отложенного задания.
	 */
	private $pushStatement;

	/**
	 * @var \PDOStatement Запрос на получения массива актуальных заданий.
	 */
	private $getStatement;

	/**
	 * @var \PDOStatement Запрос на завершение отложенного задания.
	 */
	private $doneStatement;

	public function __construct(\PDO $pdo, $table, array $scheme = null){
		if(is_null($scheme)){
			$scheme = [
				'id' => 'id',
				'start' => 'start',
				'handler' => 'handler',
				'data' => 'data',
			];
		}

		$this->pdo = $pdo;

    $this->pushStatement = $pdo->prepare('INSERT INTO ' . $table . ' (' . implode(', ', array_keys($scheme)) . ') VALUES (' . implode(', ', array_map(function($mark){
      return ':' . $mark;
    }, $scheme)) . ')');

		$this->getStatement = $pdo->prepare('SELECT ' . implode(', ', array_map(function($field, $property){
			return $field . ' AS ' . $property;
		}, array_keys($scheme), array_values($scheme))) . ' FROM ' . $table . ' WHERE ' . array_search('start', $scheme) . ' <= :time');
		$this->getStatement->setFetchMode(\PDO::FETCH_CLASS, Task::class);

		$this->doneStatement = $pdo->prepare('DELETE FROM ' . $table . ' WHERE ' . array_search('id', $scheme) . ' = :id');
	}

	/**
	 * @see Bricks\Deferred\Queue\Queue::push
	 */
	public function push(Task $task){
		$this->pushStatement->execute(get_object_vars($task));
		$task->id = $this->pdo->lastInsertId();
	}

	/**
	 * @see Bricks\Deferred\Queue\Queue::get
	 */
	public function get($time){
		return $this->getStatement->execute([
			'time' => $time,
		])->fetchAll();
	}

	/**
	 * @see Bricks\Deferred\Queue\Queue::done
	 */
	public function done($taskId){
		$this->doneStatement->execute([
			'id' => $taskId,
		]);
	}
}
