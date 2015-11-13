<?php
	class SampleObject extends \Database\DBObject {
		function getKeyField() {return 'primary_key';}
		function getTableName() {return 'table_name';}

		public function __construct() {

		}