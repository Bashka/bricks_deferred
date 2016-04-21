<?php
namespace Bricks\Deferred;
use Bricks\Deferred\Queue\RelationDbQueue;
require_once('tests/TaskCreatorHelper.php');
require_once('Queue/Queue.php');
require_once('Queue/RelationDbQueue.php');

/**
 * @author Artur Sh. Mamedbekov
 */
class RelationDbQueueTest extends \PHPUnit_Framework_TestCase{
	use TaskCreatorHelper;

	private function createMockPDO(array $methods){
		return $this->getMock('PDO', $methods, ['mysql:dbname=test', 'root', 'root']);
	}

	private function assertPrepare($pdoMock, $n, $sql){
		$pdoMock->expects($this->any())
			->method('prepare')
			->will($this->returnValue($this->getMock('PDOStatement')));

		$pdoMock->expects($this->at($n))
			->method('prepare')
			->with($this->equalTo($sql));
	}

  public function testConstruct_shouldPreparePushStatement(){
		$pdo = $this->createMockPDO(['prepare']);

		$this->assertPrepare($pdo, 0,
			'INSERT INTO table (id_field, start_field, handler_field, data_field) VALUES (:id, :start, :handler, :data)',
			'Формирование запроса на добавление отложенной задачи'
		);

		new RelationDbQueue($pdo, 'table', [
			'id_field' => 'id',
			'start_field' => 'start',
			'handler_field' => 'handler',
			'data_field' => 'data',
		]);
  }

  public function testConstruct_shouldPrepareGetStatement(){
		$pdo = $this->createMockPDO(['prepare']);

		$this->assertPrepare($pdo, 1,
			'SELECT id_field AS id, start_field AS start, handler_field AS handler, data_field AS data FROM table WHERE start_field <= :time',
			'Формирование запроса на получение массива актуальных задач'
		);

		new RelationDbQueue($pdo, 'table', [
			'id_field' => 'id',
			'start_field' => 'start',
			'handler_field' => 'handler',
			'data_field' => 'data',
		]);
  }

  public function testConstruct_shouldPrepareDoneStatement(){
		$pdo = $this->createMockPDO(['prepare']);

		$this->assertPrepare($pdo, 2,
			'DELETE FROM table WHERE id_field = :id',
			'Формирование запроса на удаление выполненной задачи'
		);

		new RelationDbQueue($pdo, 'table', [
			'id_field' => 'id',
			'start_field' => 'start',
			'handler_field' => 'handler',
			'data_field' => 'data',
		]);
  }
}
