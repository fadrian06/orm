<?php

namespace Forestry\Orm;

use LogicException;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

class StorageTest extends TestCase {
  /**
   * Create basic SQLite connection.
   */
  function testSetSqliteConnection() {
    $result = Storage::set('default', ['dsn' => 'sqlite::memory:']);

    $this->assertInstanceof('PDO', $result);
  }

  /**
   * Test exception when an already set connection should be set.
   */
  function testLogicExceptionOnAlreadySetConnection() {
    $this->expectException(LogicException::class);
    Storage::set('default', ['dsn' => 'sqlite::memory:']);
  }

  /**
   * Test retrieval of a connection.
   */
  function testGetConnection() {
    $result = Storage::get('default');

    $this->assertInstanceof('PDO', $result);
  }

  /**
   * Test exception when a connection is not set.
   */
  function testOutOfBoundExceptionOnNonSetConnection() {
    $this->expectException(OutOfBoundsException::class);
    Storage::get('notset');
  }

  /**
   * Test connection closing.
   */
  function testDeleteConnection() {
    $result = Storage::delete('default');

    $this->assertTrue($result);
  }

  /**
   * Test exception when closing a connection which is not set.
   */
  function testOutOfBoundExceptionOnClosingNonSetConnection() {
    $this->expectException(OutOfBoundsException::class);
    Storage::delete('notset');
  }
}
