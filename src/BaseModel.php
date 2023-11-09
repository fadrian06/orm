<?php

namespace Forestry\Orm;

abstract class BaseModel {

  /**
   * @var array
   */
  protected $data = [];

  /**
   * @var array
   */
  protected $modified = [];

  /**
   * @var string
   */
  protected static $storage = 'default';

  /**
   * @var string
   */
  protected static $database;

  /**
   * @var string
   */
  protected static $table;

  /**
   * @var string
   */
  protected static $primaryKey = 'id';

  /**
   * Constructor provides option to prefill object.
   *
   * @param array $data
   */
  function __construct(array $data = []) {
    if (!empty($data)) {
      $this->data = $data;
    }
  }

  /**
   * @param string $property
   * @param mixed $value
   */
  function set($property, $value) {
    $property = $this->sanitizePropertyName($property);
    if (!isset($this->data[$property]) || $this->data[$property] != $value) {
      $this->data[$property] = $value;
      $this->modified[] = $property;
    }
  }

  /**
   * @param  string $property
   * @return ?mixed
   */
  function get($property) {
    $property = $this->sanitizePropertyName($property);

    return (isset($this->data[$property]) ? $this->data[$property] : null);
  }

  /**
   * Gets name of the table.
   *
   * Includes name of the database if set.
   *
   * @static
   * @return string
   */
  static function getTable() {
    $database = static::$database ? static::quotePropertyName(static::$database) . '.' : '';
    $table = static::$table ? static::$table : strtolower(get_called_class());

    return $database . static::quotePropertyName($table);
  }

  /**
   * Gets a property.
   *
   * @param string $property
   * @return ?mixed
   */
  function __get($property) {
    $property = $this->sanitizePropertyName($property);

    return (isset($this->data[$property]) ? $this->data[$property] : null);
  }

  /**
   * Sets a property.
   *
   * @param string $property
   * @param mixed $value
   * @return void
   */
  function __set($property, $value) {
    $this->set($property, $value);
  }

  /**
   * Updates the current entry.
   *
   * @return bool
   */
  function update() {
    $storage = Storage::get(static::$storage);
    $values = [];
    $params = [];
    foreach ($this->modified as $property) {
      $values[] = sprintf('%s = :%s', $this->quotePropertyName($property), $property);
      $params[":$property"] = $this->data[$property];
    }
    $params[':' . static::$primaryKey] = $this->__get(static::$primaryKey);
    $sql = sprintf(
      'UPDATE %s SET %s WHERE %s = :%s',
      static::getTable(),
      implode(',', $values),
      $this->quotePropertyName(static::$primaryKey),
      static::$primaryKey
    );
    $query = $storage->prepare($sql);
    $return = $query->execute($params);
    if ($return) {
      $this->modified = [];
    }

    return $return;
  }

  /**
   * Removes an entry from the database and flushes data of current instance.
   *
   * @return bool
   */
  function delete() {
    $storage = Storage::get(static::$storage);
    $sql = sprintf(
      'DELETE FROM %s WHERE %s = ?',
      static::getTable(),
      static::$primaryKey
    );
    $query = $storage->prepare($sql);
    $return = $query->execute([$this->__get(static::$primaryKey)]);
    if ($return) {
      $this->data = [];
      $this->modified = [];
    }

    return $return;
  }

  /**
   * Creates a new entry.
   *
   * @return bool
   */
  function insert() {
    $storage = Storage::get(static::$storage);
    $columns = implode(',', array_map([$this, 'quotePropertyName'], $this->modified));
    $params = [];

    foreach ($this->modified as $property) {
      $params[":$property"] = $this->data[$property];
    }

    $sql = sprintf(
      'INSERT INTO %s (%s) VALUES (%s)',
      static::getTable(),
      $columns,
      implode(',', array_keys($params))
    );

    $query = $storage->prepare($sql);
    $result = $query->execute($params);

    if ($result) {
      $this->data[static::$primaryKey] = $storage->lastInsertId();
      $this->modified = [];
    }

    return $result;
  }

  /**
   * Saves the current entry.
   *
   * Shortcut for insert or update, depending on a set primary key.
   *
   * @return bool
   */
  function save() {
    $return = false;
    if ($this->__get(static::$primaryKey)) {
      $return = $this->update();
    } else {
      $return = $this->insert();
    }

    return $return;
  }

  /**
   * Returns a string representation of the current object.
   *
   * @return string
   */
  function __toString() {
    $return = sprintf(
      '%s.%s@%s {' . PHP_EOL,
      static::$storage,
      static::$table,
      get_class($this)
    );

    foreach ($this->data as $key => $value) {
      $return .= sprintf(
        '    %s: %s' . PHP_EOL,
        $key,
        (strlen($value) > 60 ? substr($value, 0, 60) . '...' : $value)
      );
    }
    $return .= '}' . PHP_EOL;

    return $return;
  }

  /**
   * Returns an array representation of the current object.
   *
   * @return array<string, mixed>
   */
  function toArray() {
    return $this->data;
  }

  /**
   * Get the value of the primary key.
   *
   * @return int
   */
  function getPK() {
    return $this->__get(static::$primaryKey);
  }

  /**
   * Retrieves entry by its primary key.
   *
   * @param mixed $key
   * @return static
   */
  static function retrieveByPK($key) {
    $return = null;
    $storage = Storage::get(static::$storage);

    $sql = sprintf(
      'SELECT * FROM %s WHERE %s = ?',
      static::getTable(),
      static::$primaryKey
    );

    $query = $storage->prepare($sql);
    $query->execute([$key]);
    $data = $query->fetch(\PDO::FETCH_ASSOC);

    if (is_array($data)) {
      $class = get_called_class();
      $return = new $class($data);
    }

    return $return;
  }

  /**
   * Performs a custom query and returns an array of objects of the called class.
   *
   * @static
   * @param string $sql
   * @return array<int, static>
   */
  static function query($sql) {
    $return = null;
    $storage = Storage::get(static::$storage);
    $result = $storage->query($sql);

    if (false !== $result) {
      $class = get_called_class();
      while ($data = $result->fetch(\PDO::FETCH_ASSOC)) {
        $return[] = new $class($data);
      }
    }

    return $return;
  }

  /**
   * Quotes property names like database, field and table names.
   *
   * @param string $property
   * @return string
   */
  private static function quotePropertyName($property) {
    return '`' . static::sanitizePropertyName($property) . '`';
  }

  /**
   * Sanitize property names like database, field and table names.
   *
   * @param string $property
   * @return mixed
   */
  private static function sanitizePropertyName($property) {
    return str_replace(array('`', '\'', '"'), '', $property);
  }
}
