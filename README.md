# Forestry ORM

[![Latest Version](https://img.shields.io/github/release/fadrian06/orm.svg?style=flat-square)](https://github.com/fadrian06/orm/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/faslatam/orm.svg?style=flat-square)](https://packagist.org/packages/faslatam/orm)

Small ORM for basic CRUD operations.

## Install

Via Composer

``` bash
$ composer require faslatam/orm
```

## Usage

### Model

#### Create a model

Any model extends the class `\Forestry\Orm\BaseModel`.

```php
class User extends \Forestry\Orm\BaseModel {
	public static $database = 'example';
	public static $table = 'users';
}
```

> You have to define at least the database and table name.

#### Using the model

You can define getters and setter for all table fields.

```php
$user = new User;
$user->setName('Bob');
$user->save();
```

Getters/setters are not mandatory. You can access the properties directly:

```php
$user = new User;
$user->name = 'Bob';
$user->save();
```

> Instead of calling the `save()` method, you can explicitly call `insert()` or `update()`.
> If you set the primary key on a new model object, you have to use the insert() method.

### Connections

`\Forestry\Orm\Storage` provides a registry for PDO instances.

#### Set a connection

A connection is defined with the `set()` method:

```php
\Forestry\Orm\Storage::set('default', [
  'dsn' => 'mysql:host=localhost',
  'user' => 'root',
  'password' => '',
  'option' => [/* any PDO options can be defined here */]
]);
```

A model could use another connection if you configure it:

```php
use \Forestry\Orm\Storage;
use \Forestry\Orm\BaseModel;

Storage::set('myOtherConnection', [
  'dsn' => 'mysql:host=127.0.0.1',
  'user' => 'root',
  'password' => ''
]);

class Acme extends BaseModel {
  public static $storage = 'myOtherConnection';
  public static $table = 'acme_table';
}
```

> If you try to set an already defined connection `set()` throws a `LogicException`.

#### Get a defined connection

You can also freely use the PDO connection like this:

```php
Storage::get('myOtherConnection')->exec('SELECT * FROM example.users');
```

> If you try to get an undefined connection `get()` throws a `OutOfBoundsException`.

#### Close a connection

To close a defined connection use the `delete()` method:

```php
Storage::delete('myOtherConnection');
```

> If you try to close an undefined connection `delte()` throws a `OutOfBoundsException`.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [daniel-melzer](https://github.com/daniel-melzer)
- [fadrian06](https://github.com/fadrian06)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
