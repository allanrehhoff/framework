<?php
	namespace Core {
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
			* Determine/negotiate the language to use, redirects to default language if none provided
			* @param (array) $appArgs Arguments provided from Core\Application
			* @return void
			*/
			public function negotiate(array &$appArgs) : void {
				if(preg_match("/^[a-z]{2}$/", strtolower($appArgs[0]))) {
					$this->langcode = array_shift($appArgs);
				} else {
					$this->langcode = \Registry::get("Core\Configuration")->get("default_language");
					\Url::redirect(\Url::fromUri($_SERVER["REQUEST_URI"]), __CLASS__);
				}

				$this->load($this->langcode);
			}

			/**
			* Loads a gíven language by langcode
			* @param (string) $langcode Language code, 2 characters.
			* @return void
			*/
			public function load(string $langcode) : void {
				if($langcode !== \Registry::get("Core\Configuration")->get("default_language")) {
					$langfile = $langcode.".json";
					$fileloc  = CWD.'/language/'.$langfile;

					if(file_exists($fileloc) === false) {
						throw new \Exception("Language file ".$langfile." was not found in the language folder");
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