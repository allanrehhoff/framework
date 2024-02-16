<?php
namespace EventListeners;

class HttpsRedirect {
	/**
	 * Redirects to HTTPS if current request was done through regular HTTP
	 * @return void
	 */
	public function handle(): void {
		if(IS_SSL === false && IS_CLI === false) {
			$url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			\Url::redirect($url, __METHOD__);
		}
	}
}