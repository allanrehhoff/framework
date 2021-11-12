<?php
	namespace Database {
		use PDO;
		use PDOStatement;

		class Statement extends PDOStatement {
			private $_connection;

			protected function __construct(Connection $connection) {
				$this->_connection = $connection;
			}

			/**
			 * Fetch the column queried
			 * @return array
			 */
			public function fetchCol() : array {
				return $this->fetchAll(PDO::FETCH_COLUMN);
			}
		}
	}