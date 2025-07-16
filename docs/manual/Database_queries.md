# Database\Connection class #

**Database\Connection V4**
A library for querying your database in an easy-to-maintain objected oriented manner.  
It features a few classes to speed up CMS / MVC / API and general CRUD development, and abstracts away your database queries.  
 
This documentation assumes you have basic knowledge of PDO. 
  
## Installing ##
**Method 1:**
Install manually: ```<?php require "autoload.php"; ?>```

**Method 2: (recommended)**
Likely will work with standard autoloaders.
Copy the **Database/** directory from any recent release into your project. `cp -r Database path/to/project/libs/`  

### Simple query 
The `\Database\Connection` class wraps around PHP's PDO, so you are able to call all of the built-in PDO functions on the instantiated object as you normally would.  
With the exception of the \Database\Connection::query(); method, this has been overloaded to a more convenient way and usage, such that it supports all the below methods.  

If all you want to do, is a simple parameterized query, this line is the one you're looking for.  
This will return a custom statement class of \Database\Statement, which also extends the default PDOStatement class.  

```php
<?php
	\Database\Connection::getInstance()->query("UPDATE animals SET `extinct` = :value WHERE name = :name", ["value" => true, "name" => "Asian Rhino"]);
```

### Selects

Simple queries with a return value will be fetched as objects, The second argument should be an array of key-value pairs.
The last argument to methods, insert(), update(), upsert() and delete() is an array of column => value pairs, which will become the WHERE ... AND clauses in the query.  

The following query  

```php
<?php
	\Database\Connection::getInstance()->select("animals");
```
and
```
<?php
	\Database\Connection::getInstance()->select("animals", ["name" => "Asian Rhino"]]);
```

Will both return a `Database\Collection` of objects, if the given criterias matched any rows, otherwise the resultset is empty.

This method also supports IN-like requests.

```php
<?php
	\Database\Connection::getInstance()->select("animals", ["name" => ["Asian Rhino", "Platypus"]]);
```

### Inserts
To insert a single row into a table.  

```php
<?php
	\Database\Connection::getInstance()->insert("animals", ["name" => "Asian Rhino", "extinct" => false]]);
```

Multiple rows can be inserted using a single query.

```php
<?php
	\Database\Connection::getInstance()->insertMultiple("animals",
		["name" => "Asian Rhino", "extinct" => true],
		["name" => "Platypus", "extinct" => false]
	]);
```

## Upserts
Upserting can be reffered to as the technique of "isnert or update if exists" without altering the logic by the caller.  
This will attempt to insert the row data with a primary key value of 64, otherwise update if the primary key exists with that value.  

```php
<?php
	\Database\Connection::getInstance()->upsert("animals", ["animalID" => 64, "name" => "Asian Rhino", "extinct" => true]);
```

### Updates
Argument 1: table name  
Argument 2: data to update  
Argument 3: Criteria  

```php
<?php
	\Database\Connection::getInstance()->update("animals", ["extinct" => false], ["name" => "Asian Rhino"]]);
```

### Deletes
Delete rows matching criteria.  
```php
<?php
	\Database\Connection::getInstance()->delete("animals", ["extinct" => true]);
```

Delete **all** rows.  
```php
<?php
	\Database\Connection::getInstance()->delete("animals");
```

## Database entities ##
For easier data manipulation, data objects should extend the **\Database\Entity** class.  
Every class that extends **\Database\Entity** must implement the following methods.  

- getTableName(); // Table in which this data object should store data.  
- getKeyField(); // The primary key of the table in which this object stores data.  

Every data object take an optional parameter [(int) primary_key] upon instantiating,  
identifying whether a new data object should be instantiated or an already existing row should be loaded from the table.  

If you wish to change data use the **->set(['column' => 'value']);**  
This will allow you to call **->save();** on an object and thus saving the data to your database.  
The data object will be saved as a new row if the primary_key key parameter was not present upon instantiating. 

**File:** Animal.php  
```php
<?php
	class Animal extends Database\Entity {
		/**
		 * The primary key of the table this entity interacts with
		 * @return string
		 */
		#[\Override]
		public static function getPrimaryKey(): string { return "test_id"; }

		/**
		 * The table name this entity interacts with
		 * @return string
		 */
		#[\Override]
		public static function getTableName(): string { return "test_table"; }

		// ... Any other methods
	}
```

Entities can be queried by the value of their primary key with the `from`.
For performance reasons, loaded antities are cached statically for the remainder of the request.  
When saved, the instance cache will be updated with the updated instance.  

```php
<?php
$animalID = 64; // Usually this is pulled from the request URI
$iAnimal = Animal::from($animalID);
```

Entities can likewise be queried by other columns than their primary key using `find`  
Using `find` assumes you only need a single entity matching the given criteria.
```php
<?php
$iAnimal = Animal::find("name", "Asian Rhino");
```

In cases where multiple rows are expected use the more advanced `search` method instead.

```php
<?php
/**
 * @var Collection<Entity>
 */
$animals = Animal::search(["name = :name"], ["name" => "Asian Rhino"]);
```

... or slightly similar  using a LIKE syntax
```php
<?php
$animals = Animal::search(["name LIKE :name"], ["name" => "Asian%"]);
```

For multiple criteria pass multiple indices.  
```php
<?php
$animals = Animal::search(
	[
		"name LIKE :name",
		"extinct = :extinct",
	], [
		"name" => "Asian%",
		"extinct" => false
	]
);
```

Sorting can also be done using the `search` method.
Apart from `query` this is the only method that supports `ORDER BY`.  

```php
<?php
$animals = Animal::search(
	[
		"name IN :names ORDER BY name DESC"
	], [
		"nameS" => ["Asian Rhino", "Indo Chinese"]
	]
);
```

> [!WARNING]  
> Populating an object with a primary key **is not** the same as loading the entity using `from`, `find` or `search`.  
> Once saved data will be overwritten if a primary key by that value already exists.  
> You must consider appropriate permission checks and validation before engaging with these methods.   

Objects can be populated with the primary key passed as data.  
Doing so will update the object if it exists in the database, otherwise it'll be created.
In the following example `$iAnimal` would be treated as a new object upon saving.  

```php
<?php
$iAnimal = new Animal;
$iAnimal->set([
	"animalID" => 42,
	"extinct" => false
]);
$iAnimal->save();
```

For tables using an auto increment mechanism, the ID will become available after saving.

```php
<?php
$iAnimal = new Animal;
$iAnimal->set([
	"name" => "Tiger"
	"extinct" => false
]);
$iAnimal->save();

$iAnimal->id(); // Returns last insert id
```

For Quick insertion the static metthod `insert` can be used
```php
<?php
AnimalLogger::insert([
	"message" => "Animal was updated"
	// ...
])
```

This will likely trigger a duplicate key error.

## Auto-generating primary keys

By default, entities expect auto-incrementing primary keys.  
However, if you prefer to use UUIDs as primary keys, you can opt-in by utilizing the provided traits.

To enable UUIDs, include one of `\Database\Primary\UuidV4` or `\Database\Primary\UuidV7` traits in your entity class.  
This trait will automatically generate a UUID of the specified version for the primary key when a new entity is created.  

When using a trait, the primary key will be generated as a UUID string before the entity is saved to the database. 

To use a general-purpose unique UUIDv4

```php
<?php

class Animal extends Database\Entity {
	use \Database\PrimaryKey\UuidV4; // Add this line

	public static function getPrimaryKey(): string {
		return "animalID";
	}

	public static function getTableName(): string {
		return "animals";
	}
}
```

To use a time-based and sortable v7 UUID

> [!WARNING]  
> **Version 7 UUIDs are not secure**  
> V7 UUIDs are by design based on timestamps and may leak generation time, which can expose sensitive information about when the UUID was created.
> Avoid using V7 UUIDs in scenarios where security and privacy are critical. Consider using a more secure UUID version, such as V4, for these use cases.

```php
<?php

class Animal extends Database\Entity {
	use \Database\PrimaryKey\UuidV7; // Add this line

	public static function getPrimaryKey(): string {
		return "animalID";
	}

	public static function getTableName(): string {
		return "animals";
	}
}
```

You may also use your own ID-types, simply create a new trait in the `\Database\PrimaryKey` namespace and implement the `generatePrimaryKey` method.

```php
<?php
namespace Database\PrimaryKey;

trait PrefixedHash
{
	/**
	 * @var \Random\Randomizer
	 */
	private \Random\Randomizer $randomizer;

	/**
	 * Generates a version 4 UUID (Universally Unique Identifier).
	 *
	 * @return string A version 4 UUID in the format xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx
	 */
    public function generatePrimaryKey(): string {
		// Initialize the randomizer
		$this->randomizer = new \Random\Randomizer();

		// Retrieve 16 cryptographically secure random bytes
		$bytes = $this->randomizer->getBytes(16);

		// Return a prefixed primary key
		return static::getKeyPrefix() . bin2hex($bytes);
    }
}
```

Above example will require an additional method to entities.  

```php
class Animal extends Database\Entity {
	use \Database\PrimaryKey\PrefixedHash;

	public static function getPrimaryKey(): string {
		return "animalID";
	}

	public static function getTableName(): string {
		return "animals";
	}

	// New method implemented
	public static function getKeyPrefix(): string {
		return "an_"; 
	}
}
```

## Collections / Result sets ##

The `Database\Collection` class is a specialized iterable object that represents a set of database rows returned from a query.  
It behaves similarly to an array of objects, but provides additional convenience methods for working with result sets, such as:
- Iterating over results with `foreach`
- Accessing rows by index (e.g., `$collection[0]`)
- Using helper methods like `getFirst()`, `getColumn($name)`, `count()`, and more
- Chaining methods for filtering, mapping, or reducing results

You can treat a `Database\Collection` much like an array of entity objects, but with extra features for database result handling and manipulation.

```php
<?php
	\Database\Connection::getInstance()->select("animals")->getColumn("name");
```

Get row (assuming your criteria matches only one row) 
```php
<?php
	\Database\Connection::getInstance()->select("animals", ["name" => "Asian Rhino"])->getFirst();
```