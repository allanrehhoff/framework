<?php
class SampleObject extends Core\DBObject {
	function getKeyField() {return 'primary_key';}
	function getTableName() {return 'table_name';}

	public function __construct() {

	}
}