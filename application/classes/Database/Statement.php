<?php
	namespace Database {
		use PDO;
		use PDOStatement;

		class Statement extends PDOStatement {
			private $_connection;

			protected function __construct(PDO $connection) {
				$this->_connection = $connection;
			}

			public function fetchCol() : array {
				return $this->fetchAll(PDO::FETCH_COLUMN);
			}
		}
	}