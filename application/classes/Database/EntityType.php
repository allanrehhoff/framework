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
		class EntityType extends \Database\Entity {
			const KEYFIELD = "test_field";
			const TABLENAME = "test_table";

			protected function getKeyField() : string { return self::KEYFIELD; }
			protected function getTableName() : string { return self::TABLENAME; }
		}
	}