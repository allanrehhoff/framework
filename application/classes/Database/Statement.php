<?php
	namespace Database {
		use PDO;
		use PDOStatement;

		class Statement extends PDOStatement {
			private $_connection;

<<<<<<< HEAD
			protected function __construct(Connection $connection) {
=======
			protected function __construct(PDO $connection) {
>>>>>>> 469a433d2f6ec69737b48d4fc458f1d6333f463f
				$this->_connection = $connection;
			}

			public function fetchCol() : array {
				return $this->fetchAll(PDO::FETCH_COLUMN);
			}
		}
	}