<?php
	/**
	* A sample object to demonstrate how you would use data objects.
	* @see README.md
	*/
	class SampleObject extends \Database\DBObject {
		function getKeyField() {return 'primary_key';}
		function getTableName() {return 'table_name';}

		public function __construct() {

		}
	}