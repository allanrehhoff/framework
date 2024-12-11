<?php

namespace Core;

use Core\ContentType\ContentTypeInterface;
use Core\ContentType\ContentTypeEnum;

/**
 * Encapsulates the details of a request and simplifies the process of working with incoming HTTP requests.
 * It provides functionality for retrieving request parameters, headers, and simplifying file uploads.
 */
final class Request {
	/**
	 * @var array $get Contents of the $_GET super global
	 */
	public array $get;

	/**
	 * @var array $post Contents of the $_POST super global
	 */
	public array $post;

	/**
	 * @var array $server Contents of the $_SERVER super global
	 */
	public array $server;

	/**
	 * @var array $files Contents of the $_FILES super global
	 */
	public array $files;

	/**
	 * @var array $cookie Contents of the $_COOKIE super global
	 */
	public array $cookie;

	/**
	 * @var array $arguments
	 */
	private array $arguments;

	/**
	 * @var array Results of the parsed HTTP Accept header
	 */
	private array $preferences = [];

	/**
	 * @var \Configuration $configuration
	 */
	private \Configuration $configuration;

	/**
	 * Sets initial state of super globals
	 */
	public function __construct() {
		$this->get = $_GET;
		$this->post = $_POST;
		$this->files = $_FILES;
		$this->cookie = $_COOKIE;
		$this->server = $_SERVER;

		if (empty($this->files) !== true) {
			$this->files = $this->reArrangeFilesArray($this->files);
		}

		if (isset($this->server["HTTP_ACCEPT"])) {
			$this->preferences = $this->parseAcceptHeader($this->server["HTTP_ACCEPT"]);
		}

		if (IS_CLI === true) {
			$args = $this->server["argv"];
			$args = array_slice($args, 1);
		} else {
			$args = trim($this->server["REQUEST_URI"], '/');
			$args = explode('/', $args);
			$args = array_filter($args);
		}

		$this->setArguments($args);

		\Core\Event::trigger("core.request.init");
	}

	/**
	 * Set arguments parsed from path
	 * @param array $arguments Set application arguments parsed from the request.
	 * 						   In a CLI context these are parsed from the arg vectir (argv)
	 * 						   Otherwise should be parsed from the request uri.
	 * @return void
	 */
	public function setArguments(array $arguments): void {
		$this->arguments = $arguments;
	}

	/**
	 * Get path that should be used by the router
	 * @return array
	 */
	public function getArguments(): array {
		return $this->arguments;
	}

	/**
	 * Get arg by an index.
	 * Args are the global arg vector (argv), or the exploded (by /) request URI.
	 * Default args may be optionally provided if given index is out of range.
	 * Should the index not be present in $defaults either, null is returned.
	 * @param int $index The numeric index of argument to get
	 * @param array $defaults Fallback to this index value, default empty array
	 * @return ?string
	 */
	public function getArg(int $index, array $defaults = []): ?string {
		return $this->arguments[$index] ?? $defaults[$index] ?? null;
	}

	/**
	 * Returns request specific configuration file
	 * @return \Configuration
	 */
	public function getConfiguration(): \Configuration {
		return $this->configuration ??= new \Configuration(STORAGE . "/config/request.jsonc");
	}

	/**
	 * Tell the mime content type that the client prefer to recieve
	 * NULL is returned if the Accept header failed negotiation
	 * @return array
	 */
	public function getContentTypePreferences(): array {
		return $this->preferences;
	}

	/**
	 * Parse HTTP Accept header
	 *
	 * @param string $acceptHeader
	 * @return array Parsed client preferences
	 */
	private function parseAcceptHeader(string $acceptHeader): array {
		$mediaTypes = explode(',', $acceptHeader);

		foreach ($mediaTypes as $mediaType) {
			$parts = explode(';', $mediaType);
			$mimeType = trim($parts[0]);
			$quality = 1.0; // Default quality value

			foreach ($parts as $part) {
				if (strpos($part, 'q=') === 0) {
					$quality = (float) substr($part, 2);
					break;
				}
			}

			[$namespace, $dataType] = explode('/', $mimeType);

			$preferences ??= [];
			$preferences[$dataType] = $quality;
		}

		arsort($preferences);

		return $preferences;
	}

	/**
	 * Re-arrange a $_FILES array to a more intuitive format
	 * @param array $files the $_FILES array
	 * @return array The re-arranged files array
	 */
	private function reArrangeFilesArray(array $files): array {
		$result = [];
		$count  = count($files["name"]);
		$keys   = array_keys($files);

		for ($i = 0; $i < $count; $i++) {
			foreach ($keys as $key) {
				$result[$i][$key] = $files[$key][$i];
			}
		}

		return $result;
	}
}
