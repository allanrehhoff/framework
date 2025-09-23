<?php

namespace ContentSecurityPolicy;

class Builder {
	/**
	 * Holds the defined policies
	 * @var array
	 */
	private array $policies = [];

	/**
	 * Holds the nonce value
	 * @var null|string
	 */
	private null|string $nonce = null;

	/**
	 * Generate and store a new nonce for this request
	 * @return string The generated nonce
	 */
	public function generateNonce(): string {
		$this->nonce = base64_encode(random_bytes(16));
		return $this->nonce;
	}

	/**
	 * Get the current nonce value
	 * @return string|null The current nonce or null if not generated
	 */
	public function getNonce(): ?string {
		return $this->nonce;
	}

	/**
	 * Add a policy directive
	 * @param string $policy The policy directive (e.g., "script-src")
	 * @param string|array $value The value(s) for the policy. Can be a single string or array of strings.
	 * @return void
	 */
	public function addPolicy(string $policy, string|array $value): void {
		if (is_array($value)) {
			$this->policies[$policy] ??= [];
			$this->policies[$policy] = array_merge($this->policies[$policy], $value);
		} else {
			$this->policies[$policy][] = $value;
		}
	}

	/**
	 * Enable nonce for a specific policy
	 * @param string $policy The policy to enable nonce for (e.g., "script-src", "style-src")
	 * @return void
	 */
	public function enableNonceForPolicy(string $policy): void {
		$this->nonce ??= $this->generateNonce();
		$this->addPolicy($policy, sprintf("'nonce-%s'", $this->nonce));
	}

	/**
	 * Convert the policy to a string
	 * @return string The complete CSP header value
	 */
	public function toString(): string {
		$directives = [];

		foreach ($this->policies as $policy => $values) {
			$directives[] = $policy . ' ' . implode(' ', array_unique($values));
		}

		return implode('; ', $directives);
	}
}
