<?php
namespace Core {
	final class Environment {
		/**
		 * Tries to determine if we're on a production or development environment
		 * @todo This class needs to be more integrated with actual env vars.
		 * @return string
		 */
		public static function determine() :string {
			if(IS_CLI) {
				if(in_array($GLOBALS["argv"][1], ["dev", "live"])) {
					return $GLOBALS["argv"][1];	
				}
			} elseif(substr($_SERVER["SERVER_NAME"], 0, 3) == "dev") {
				return "dev";
			}

			return "live";
		}
	}
}