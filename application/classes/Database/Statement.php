<?php
	namespace Database {
		use PDO;
		use PDOStatement;

		class Statement extends PDOStatement {
			/**
			 * Dear future me, PDOStatement has no __construct() method
			 * No need not to add a parent::__construct(); call in here
			 */
			protected function __construct(Connection $connection) {}

			/**
			 * Fetch the column queried
			 * 
			 * @return array
			 */
			public function fetchCol() : array {
				$result = $this->fetchAll(PDO::FETCH_COLUMN);
				
				// PHP < 8.0.0 compat. PDOStatement::fetchAll(); will return false
				// if the result set was empty, fixed in PHP 8.0.0
				return $result !== false ? $result : [];
			}

			public function fetchColumn(int $column = 0): mixed {
				$result = parent::fetchColumn($column);

				// PDOStatement::fetchColumn(); will return false
				// We'll normalise it to return null
				return $result !== false ? $result : null;
			}
		}
	}