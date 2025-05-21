<?php

namespace EventListeners;

class HttpsRedirect {
	/**
	 * Redirects to HTTPS if current request was done through regular HTTP
	 * @return void
	 */
	public function handle(): void {
		if (IS_CLI) return;

		$baseurl = \Url::getBaseurl();
		$parsedUrl = \Url::parse($baseurl);

		if ($parsedUrl['scheme'] != $_SERVER['REQUEST_SCHEME']) {
			$url = $parsedUrl['scheme'] . "://" . $parsedUrl['host'] . $_SERVER["REQUEST_URI"];
			\Url::redirect($url, __METHOD__);
		}
	}
}
