<?php

namespace Forestry\Orm;

class Storage {
  /**
   * @var array
   */
  private static $instances = ['default' => []];

  /**
   * Set a new connection with the given name.
   *
   * @param string $name
   * @param array<string, mixed> $config
   * @return mixed
   * @throws \LogicException
   * @throws \PDOException
   */
  static function set($name = 'default', array $config = []) {
    if (isset(self::$instances[$name]) && self::$instances[$name] instanceof \PDO) {
      throw new \LogicException(sprintf('Connection "%s" already set', $name));
    }

    self::$instances[$name] = new \PDO(
      isset($config['dsn']) ? $config['dsn'] : 'mysql:host=localhost',
      isset($config['user']) ? $config['user'] : 'root',
      isset($config['password']) ? $config['password'] : '',
      isset($config['option']) ? $config['option'] : []
    );

    return self::$instances[$name];
  }

  /**
   * Creates a new instance of PDO or returns an existing one.
   *
   * @param string $name
   * @return \PDO
   * @throws \OutOfBoundsException
   */
  static function get($name) {
    if (!isset(self::$instances[$name])) {
      throw new \OutOfBoundsException(sprintf('Storage "%s" not set', $name));
    }

    return self::$instances[$name];
  }

  /**
   * Closes the connection.
   *
   * @param string $name
   * @return bool
   * @throws \OutOfBoundsException
   */
  static function delete($name) {
    if (!isset(self::$instances[$name])) {
      throw new \OutOfBoundsException(sprintf('Storage "%s" not set', $name));
    }

    self::$instances[$name] = null;

    return true;
  }
}
