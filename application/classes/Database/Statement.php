<?php
	namespace Database {
		use PDO;
		use PDOStatement;

		class Statement extends PDOStatement {
			protected function __construct(Connection $connection) {

			}

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
		}
	}