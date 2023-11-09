<?php

namespace Forestry\Orm;

use Forestry\Orm\Test\ModelImplementation;
use PHPUnit\Framework\TestCase;

class BaseModelTest extends TestCase {
  function setUp(): void {
    Storage::set('test', [
      'dsn' => 'sqlite::memory:',
      [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
    ]);

    Storage::get('test')->exec('DROP TABLE IF EXISTS test_table');
    Storage::get('test')->exec('CREATE TABLE test_table (id INT PRIMARY KEY, name TEXT)');
  }

  function tearDown(): void {
    Storage::delete('test');
  }

  /**
   * Create basic model.
   */
  function testConstructor() {
    $model = new ModelImplementation;

    $this->assertInstanceof(BaseModel::class, $model);
  }

  /**
   * Create basic model with constructor options.
   */
  function testConstructorWithParameters() {
    $model = new ModelImplementation([
      'id' => 1,
      'name' => 'Test'
    ]);

    $this->assertInstanceof(BaseModel::class, $model);
    $this->assertEquals('Test', $model->name);
  }

  function testSetAndGet() {
    $stub = $this->getMockForAbstractClass(BaseModel::class);
    $stub->set('`name`', 'Test');

    $this->assertEquals('Test', $stub->get('name'));
  }

  function testGetPk() {
    $model = new ModelImplementation([
      'id' => 1,
      'name' => 'Test'
    ]);

    $this->assertEquals(1, $model->getPK());
  }

  function testGetTableName() {
    $table = ModelImplementation::getTable();

    $this->assertEquals('`test_table`', $table);
  }

  function testToArray() {
    $model = new ModelImplementation([
      'id' => 1,
      'name' => 'Test'
    ]);

    $this->assertEquals([
      'id' => 1,
      'name' => 'Test'
    ], $model->toArray());
  }

  function testToString() {
    $model = new ModelImplementation([
      'id' => 1,
      'name' => 'Test'
    ]);

    $this->assertEquals(<<<OUTPUT
    test.test_table@Forestry\Orm\Test\ModelImplementation {\r
        id: 1\r
        name: Test\r
    }\r

    OUTPUT, (string) $model);
  }

  function testSet() {
    $model = new ModelImplementation();
    $model->name = 'Test';

    $this->assertEquals(['name' => 'Test'], $model->toArray());
  }

  function testInsert() {
    $model = new ModelImplementation();
    $model->name = 'test';
    $model->insert();

    $this->assertEquals(1, $model->getPK());
  }

  function testUpdate() {
    $model = new ModelImplementation([
      'id' => 1,
      'name' => 'test'
    ]);
    $model->name = 'test2';
    $model->update();

    $this->assertEquals('test2', $model->name);
  }

  function testSaveUpdate() {
    $model = new ModelImplementation([
      'id' => 1,
      'name' => 'test2'
    ]);
    $model->name = 'test3';
    $model->save();

    $this->assertEquals('test3', $model->name);
  }

  function testSaveInsert() {
    $model = new ModelImplementation();
    $model->name = 'test4';
    $model->save();

    $this->assertEquals('test4', $model->name);
  }

  function testDelete() {
    $model = new ModelImplementation([
      'id' => 1,
      'name' => 'test2'
    ]);
    $model->delete();

    $this->assertEquals(null, $model->name);
  }

  function testRetrieveByPk() {
    $model = new ModelImplementation();
    $model->id = 1;
    $model->name = 'test';
    $model->insert();

    $result = ModelImplementation::retrieveByPK(1);

    $this->assertInstanceof('Forestry\Orm\BaseModel', $result);
    $this->assertEquals('test', $model->name);
  }

  function testQuery() {
    $model = new ModelImplementation();
    $model->id = 1;
    $model->name = 'test';
    $model->insert();

    $result = ModelImplementation::query('SELECT * FROM test_table WHERE name LIKE "test"');

    $this->assertArrayHasKey(0, $result);
  }
}
