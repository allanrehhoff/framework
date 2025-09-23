<?php

namespace EventListeners;

class ContentSecurityPolicy {
	/**
	 * Handles the Content-Security-Policy header injection
	 * @param \Core\Response $iResponse The response object to modify
	 * @return void
	 */
	public function handle(\Core\Response $iResponse): void {
		$iCspBuilder = \Registry::get(\ContentSecurityPolicy\Builder::class);

		foreach (glob(STORAGE . "/config/content-security-policies/*.jsonc") as $policyFile) {
			$iConfiguration = new \Configuration($policyFile);

			// Merge policies from the configuration file
			// Example file content:
			// {
			//     "default-src": ["'self'"],
			//     "img-src": ["'self'", "data:", "https://example.com"],
			//     "connect-src": ["'self'", "https://api.example.com"]
			// }
			foreach ($iConfiguration->get() as $policy => $value) {
				$iCspBuilder->addPolicy($policy, $value);
			}
		}

		// Enable nonces for script and style tags
		$iCspBuilder->enableNonceForPolicy("script-src");
		$iCspBuilder->enableNonceForPolicy("style-src");

		$iResponse->addHeader(sprintf(
			"Content-Security-Policy: %s",
			$iCspBuilder->toString()
		));

		// Pass the nonce to the response data for use in views/templates
		$iResponse->data["nonce"] = $iCspBuilder->getNonce();
	}
}
