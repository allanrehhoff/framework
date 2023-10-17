<?php
namespace Database {
	class Statement extends \PDOStatement {
		/**
		 * Dear future me, PDOStatement has no __construct() method
		 * No need not to add a parent::__construct(); call in here
		 * But this is still required in order to avoid this error:
		 * "User-supplied statement does not accept constructor arguments"
		 */
		protected function __construct() {}

		/**
		 * Fetch the column queried
		 * 
		 * @return array
		 */
		public function fetchCol() : array {
			$result = $this->fetchAll(\PDO::FETCH_COLUMN);
			
			// PHP < 8.0.0 compat. PDOStatement::fetchAll(); will return false
			// if the result set was empty, fixed in PHP 8.0.0
			return $result !== false ? $result : []; // @phpstan-ignore-line
		}

		/**
		 * Fetches the next row from a result set 
		 * \PDOStatement::fetchColumn(); will return false
		 * This method will make sure NULL may be return instead
		 * @return mixed
		 */
		public function fetch(int $mode = \PDO::FETCH_DEFAULT, int $cursorOrientation = \PDO::FETCH_ORI_NEXT, int $cursorOffset = 0) : mixed {
			$result = parent::fetch($mode, $cursorOrientation, $cursorOffset);
			
			// PDOStatement::fetchColumn(); will return false
			// We'll normalise it to return null
			return $result !== false ? $result : null;
		}

		/**
		 * Fetch a column by numeric index from the resultset
		 * \PDOStatement::fetchColumn(); will return false
		 * This method will make sure NULL may be return instead
		 * @return mixed
		 */
		public function fetchColumn(int $column = 0): mixed {
			$result = parent::fetchColumn($column);
			return $result !== false ? $result : null;
		}
	}
}