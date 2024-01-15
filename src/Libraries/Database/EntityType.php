<?php
/**
* This file is solely used for demonstation purposes.
* Whenever you create a new CRUD'able instance that extends \Database\Entity it must contain at least the two methods defined in this class.
* However it does not neccesarily need to be in a namespace, as long as it extends \Database\Entity.
*
* getKeyField(); Must return the name of the column with the primary key index.
* getTableName(); Must return the name of the table this Entity operates with.
*/
namespace Database {

	/**
	 * Class EntityType
	 */
	class EntityType extends \Database\Entity {
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
	}
}