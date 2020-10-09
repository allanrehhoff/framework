<?php
	namespace Core {
		use Exception;
		use Registry
		use Url;

		class Language {
			private $langcode;
			private $strings = [];

			/**
			* Class constructor
			* @param (string) $langcode Language code to load translations for.
			*/
			public function __construct($langcode = null) {
				if($langcode !== null) {
					$this->load($langcode);
				}
			}

			/**
			* Negotiate the language to use, redirects to default language if none provided
			* We don't use Language::load(); or merge this into load(); because we want the
			* application to serve a 404 if the language isn't found or enabled.
			* @param (array) $appArgs Arguments provided from Core\Application
			* @return void
			*/
			public function negotiate(array &$appArgs) : void {
				if($this->validateCode($appArgs[0]) && $this->enabled($appArgs[0])) {
					$this->langcode = array_shift($appArgs);
				} else {
					$this->langcode = Registry::get("Core\Configuration")->get("default_language");
					Url::redirect(Url::fromUri($_SERVER["REQUEST_URI"]), __CLASS__);
				}

				$this->load($this->langcode);
			}

			/**
			* Validates given language against a 2 character pattern
			* @param (string) $langcode Language code to validate
			* @return bool Whether the language code is valid or not
			*/
			public function validateCode(string $langcode) : bool {
				return preg_match("/^[a-z]{2}$/", $langcode);
			}

			/**
			* Determine if a language is enabled or not by looking at the filesystem
			* @param (string) $langcode Language code to check
			* @return bool Enabled or not
			*/
			public function enabled(string $langcode) : bool {
				$fileloc  = CWD.'/language/'.$langcode.".json";
				return file_exists($fileloc) !== false;
			}

			/**
			* Loads a gíven language by langcode
			* @param (string) $langcode Language code, 2 characters.
			* @return void
			*/
			public function load(string $langcode) : void {
				if($langcode !== \Registry::get("Core\Configuration")->get("default_language")) {
					if($this->enabled($langcode) !== true) {
						throw new Exception("Language file ".$langfile." was not found in the language folder");
					}

					$json = file_get_contents($fileloc);
					$this->strings = json_decode($json);
				}
			}

			/**
			* Gets the current language code for this language object
			* @return string
			*/
			public function getLangcode() : string {
				return $this->langcode;
			}

			/**
			* Translate a string
			* @param (string) $string The string to translate
			* @return string
			*/
			public function get(string $string) : string {
				return $this->strings[$string] ?? $string;
			}
		}
	}