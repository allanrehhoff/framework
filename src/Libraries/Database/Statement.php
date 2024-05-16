<?php

namespace Database;

class Statement extends \PDOStatement {
	/**
	 * Dear future me, PDOStatement has no __construct() method
	 * No need not to add a parent::__construct(); call in here
	 * But this is still required in order to avoid this error:
	 * "User-supplied statement does not accept constructor arguments"
	 */
	protected function __construct() {
	}

	/**
	 * Fetch the column queried
	 * 
	 * @return array
	 */
	public function fetchCol(): array {
		$result = $this->fetchAll(\PDO::FETCH_COLUMN);

		// PHP < 8.0.0 compat. PDOStatement::fetchAll(); will return false
		// if the result set was empty, fixed in PHP 8.0.0
		return $result !== false ? $result : []; // @phpstan-ignore-line
	}

	/**
	 * Fetches the next row from a result set.
	 * \PDOStatement::fetchColumn(); will return false.
	 * This method will make sure NULL may be return instead.
	 * 
	 * @param int $mode (optional) Controls how the next row will be returned to the caller. This value must be one of the PDO::FETCH_* constants, defaulting to value of PDO::ATTR_DEFAULT_FETCH_MODE (which defaults to PDO::FETCH_BOTH).
	 * @param int $cursorOrientation (optional) For a PDOStatement object representing a scrollable cursor, this value determines which row will be returned to the caller. This value must be one of the PDO::FETCH_ORI_* constants, defaulting to PDO::FETCH_ORI_NEXT. To request a scrollable cursor for your PDOStatement object, you must set the PDO::ATTR_CURSOR attribute to PDO::CURSOR_SCROLL when you prepare the SQL statement with PDO::prepare.
	 * @param int $cursorOffset (optional)
	 * @return mixed The return value of this function on success depends on the fetch type. In all cases, NULL is returned on failure.
	 * @link https://php.net/manual/en/pdostatement.fetch.php
	 */
	#[\Override]
	public function fetch(int $mode = \PDO::FETCH_DEFAULT, int $cursorOrientation = \PDO::FETCH_ORI_NEXT, int $cursorOffset = 0): mixed {
		$result = parent::fetch($mode, $cursorOrientation, $cursorOffset);

		// PDOStatement::fetchColumn(); will return false
		// We'll normalise it to return null
		return $result !== false ? $result : null;
	}

	/**
	 * Fetch a column by numeric index from the resultset.
	 * \PDOStatement::fetchColumn(); will return false.
	 * This method will make sure NULL may be return instead.
	 * 
	 * @param int $column (optional) 0-indexed number of the column you wish to retrieve from the row. If no value is supplied, fetches the first column.
	 * @return mixed The return value of this function on success depends on the fetch type. In all cases, NULL is returned on failure.
	 */
	#[\Override]
	public function fetchColumn(int $column = 0): mixed {
		$result = parent::fetchColumn($column);
		return $result !== false ? $result : null;
	}
}
