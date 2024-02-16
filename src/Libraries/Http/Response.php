<?php
/**
* Parses and contains all content written to the HTTP stream
* @package Http\Request
* @license MIT
*/
namespace Http {
	class Response {
		/**
		 * @var Request $request The request object that spawned this response.
		 */
		private Request $request;
		
		/**
		 * @var string $rawHeaders Response headers before being parsed
		 */
		private string $rawHeaders;

		/**
		 * @var array $responseHeaders Response headers after parsing
		 */
		private array $responseHeaders = [];

		/**
		 * @var array $xmlErrors Populated with errors when parsing an XML response
		 */
		public array $xmlErrors = [];

		/**
		 * Constructs the response to the request
		 * @param Request $iRequest The request object to construct a response for
		 */
		public function __construct(Request $iRequest) {
			$this->request = $iRequest;
			
			$headerHandle = $this->request->headerHandle;
			$verbosityHandle = $this->request->verbosityHandle;

			// And parse the headers for a client to use.
			rewind($headerHandle); 
			$this->rawHeaders = rtrim(stream_get_contents($headerHandle), "\r\n");

			if($this->request->method !== null) {
				$this->responseHeaders = $this->parseHeaders($this->rawHeaders);
			}

			if(is_resource($verbosityHandle)) {
				rewind($verbosityHandle);
				$this->rawHeaders .= stream_get_contents($verbosityHandle);
			}
		}

		/**
		 * Gives the raw response returned by remote resource.
		 * @return string
		 */
		public function __toString() {
			return $this->getBody();
		}

		/**
		 * Get cURL information regarding this request.
		 * If index is given, returns its value, NULL if index is undefined.
		 * Otherwise, returns an associative array of all available values.
		 *
		 * @param null|string $option An index from curl_getinfo() returned array, default null
		 * @see http://php.net/manual/en/function.curl-getinfo.php
		 * @return mixed
		 */
		public function getInfo(null|string $option = null): mixed {
			$curlInfo = $this->request->curlInfo;

			if($option !== null) {
				return $curlInfo[$option] ?? null;
			}

			return $curlInfo;
		}

		/**
		 * Returns the HTTP code represented by this reponse
		 * @return int
		 */
		public function getHttpCode(): int {
			return (int) $this->getInfo("http_code");
		}

		/**
		 * Finds out whether a request was successful or not.
		 * @return bool
		 */
		public function isSuccess(): bool {
			return $this->getHttpCode() < 400;
		}

		/**
		 * Attempt to parse HTTP headers from raw respnose.
		 * - headers spanning multiple lines will be returning as a single index with line breaks
		 * - headers sent multiple times e.g Set-Cookie will be returned as an array
		 * - regular key-value headers will be returned as-is indexed by its key.
		 * Herein lies deep and dark magic.
		 * @param string $rawHeaders the raw header reponse
		 * @return array The parsed headers.
		 */
		public function parseHeaders(string $rawHeaders): array {
			$headers = [];
			$currentKey = '';
		
			foreach (explode("\n", $rawHeaders) as $headerLine) {
				$headerParts = explode(':', $headerLine, 2);
		
				if (isset($headerParts[1])) {
					// Regular headers with a key and value
					// While RFC 7230 dictates HTTP headers are allowed to be all lowercase the first letter of each word
					// will be capitalized in order to maintain a uniform response across all requests.
					// \b: Word boundary anchor, asserts the position between a word character and a non-word character.
					// \w: Shorthand for any word character (alphanumeric + underscore).
					$headerKey = preg_replace_callback('/\b\w/', function($matches) {
						return strtoupper($matches[0]);
					}, trim($headerParts[0]));
		
					$headerValue = trim($headerParts[1]);
		
					if (!isset($headers[$headerKey])) {
						// If the header key is not set, assign the value
						$headers[$headerKey] = $headerValue;
						$currentKey = $headerKey;
					} elseif (is_array($headers[$headerKey])) {
						// If the header key already exists as an array
						// add the header value to the array
						$headers[$headerKey][] = $headerValue;
					} else {
						// If the header key already exists as a single value, convert it to an array,
						// fx. if Set-Cookie has been sent more than once.
						$headers[$headerKey] = [$headers[$headerKey], $headerValue];
					}
				} elseif (isset($headerParts[0]) && substr($headerParts[0], 0, 1) === "\t" && $currentKey) {
					// Multi-line headers, e.g., Set-Cookie with multiple values
					$headers[$currentKey] .= "\r\n\t" . trim($headerParts[0]);
				} elseif (!$currentKey) {
					// No header key (e.g., the status line)
					$headers[0] = trim($headerParts[0]);
				}
			}
		
			return $headers;
		}

		/**
		 * Returns parsed header values.
		 * If header is given returns that headers value.
		 * Otherwise all response headers is returned.
		 * 
		 * @param null|string $header Name of the header for which to get the value
		 * @return null|string|array
		 */
		public function getHeaders(null|string $header = null): null|string|array {
			if($header !== null) {
				return $this->responseHeaders[$header] ?? null;
			}

			return $this->responseHeaders;
		}

		/**
		 * Get cookies set by the remote server for the performed request, in case a cookiejar wasn't utilized.
		 * @param null|string $cookie Name of the cookie for which to retrieve details, null if it doesn't exist, ommit to get all cookies.
		 * @return array
		 */
		public function getCookie(null|string $cookie = null): array {
			if($cookie !== null) {
				return $this->responseHeaders["Set-Cookie"][$cookie] ?? null;
			}

			return $this->responseHeaders["Set-Cookie"];
		}

		/**
		 * Return value of a given header
		 * @return string|array
		 */
		public function getCookies(): string|array {
			return $this->responseHeaders["Set-Cookie"] ?? [];
		}

		/**
		 * Get the request response text without the headers.
		 * @return string
		 */
		public function getBody(): string {
			if($this->request->returndata === null) {
				throw new \RuntimeException("Perform a request before accessing response data.");
			}

			return $this->request->returndata;
		}

		/**
		 * Decodes and returns an object, assumes HTTP Response is JSON
		 * @return \stdClass
		 */
		public function asObject(): \stdClass {
			return json_decode($this->getBody(), flags: JSON_THROW_ON_ERROR);
		}

		/**
		 * Decodes and returns an associative array, assumes the HTTP Response is JSON
		 * @return array
		 */
		public function asArray(): array {
			return json_decode($this->getBody(), true, flags: JSON_THROW_ON_ERROR);
		}

		/**
		 * Returns a SimpleXML object with containing the response content.
		 * After calling any potential xml error will be available for inspection in the $xmlErrors property.
		 * @param bool $useErrors Toggle xml errors supression. Please be advised that setting this to true will also clear any previous XML errors in the buffer.
		 * @return \SimpleXMLElement
		 */
		public function asXml(bool $useErrors = false): \SimpleXMLElement {
			libxml_use_internal_errors($useErrors);
			$xml = simplexml_load_string($this->getBody());
			if($useErrors == false) $this->xmlErrors = libxml_get_errors();
			return $xml;
		}
	}
}