<?php
	namespace Core {
		class Environment {
			/**
			* Tries to determine if we're on a
			* production or development environment
			* @return string
			*/
			public static function determine() :string {
				if(defined("IS_DEV") && IS_DEV) {
					return "dev";
				}

				if(substr($_SERVER["SERVER_NAME"], 0, 3) == "dev") {
					return "dev";
				}

				return "live";
			}
		}
	}