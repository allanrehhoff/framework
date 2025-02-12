# The Url utility class

The `\Url` class offers utility methods for parsing, manipulating, and building URLs.

## getBaseurl
```php
// Retrieves the base URL of the current application.
$baseUrl = \Url::getBaseurl();
```

## fromUri
```php
// Builds a full URL by appending a given URI to the base URL.
\Url::fromUri($uri);
```

## redirect
```php
// Performs an HTTP redirect with a 302 status and a custom "X-Redirect-By" header.
\Url::redirect($location, $xRedirectBy);
```

## parse
```php
// Parses a URL into its components and returns them as an associative array.
\Url::parse($url);
```

## build
```php
// Assembles a URL from an associative array of its components.
$urlParts = [
	"scheme" => "https",
	"host" => "example.com",
	"path" => "/page",
	"query" => "param=value",
	"fragment" => "section",
];
$url = \Url::build($urlParts);
echo $url; // Outputs: "https://example.com/page?param=value#section"
```

## encode
```php
// Encodes a string by replacing special characters with their percent-encoded representation.
\Url::encode($string);
```

## decode
```php
// Decodes a percent-encoded string back to its original form.
\Url::decode($string);
```

## buildQueryString
```php
// Builds a query string from an associative array of URL parameters.
\Url::buildQueryString($params);
```

## parseQueryString
```php
// Parses a query string and returns an associative array of its parameters.
\Url::parseQueryString($queryString);
```