# Requests, Response & Content Types

## Response data

Controllers can set any amount of data to be passed on as variables to view files by accessing the `Response` object.  

```php
<?php
// Assume this url: yourdomain.tld/animals/tiger/indo-chinese
class AnimalController extends Controller {
	public function index(): void {
		$this->response->data["pageTitle"] = "Welcome to Umbrella Corp!";
		$this->response->setView("welcome-page");
	}
}
```

In the above example the string `Welcome to Umbrella Corp!` will be available as `$pageTitle`

```html
<!-- welcome-page.tpl.php -->
<h1><?php print $pageTitle; ?></h1>
```

## Content Types
Framework supports responding with in different data formats adhering to the clients `Accept` header preference.

A `Content-Type` header containing a similar mime type will likewise be returned.  

> [!IMPORTANT]
> It is not guaranteed that the mime type returned the `Content-Type` will be identical to the mime type from the `Accept` header.  
> For instance, if the `Accept` header contains `text/json`, while valid according to standards the `application/json` is widely adeopted by clients and will therefore be included in the `Content-Type` response header

> [!NOTE]
> For security reasons accepting content types `application/json` and `application/xml` are disabled by default.  
> Responding with these types, must be enabled explicitly, depending on use case.

The clients content type preferences will be negotiated the following, in listed order:
- Application wide config
- Class attributes
- Method attributes

Any views set by controllers will not be rendered if client accepted content type is different from HTML.  

Current supported content types are:

> **HTML (Default)**  
> `Accept: */*`  

> **JSON**  
> `Accept: application/json`  

> **XML**  
> `Accept: application/xml`  

A request made with curl to accept JSON:
```sh
curl -s -X GET https://mydomain.tld/welcome-page -H "Accept: application/json" | jq
```

Will output a response similar to:
```json
{
	"pageTitle": "Welcome to Umbrella Corp!"
}
```

As with XML
```sh
curl -s -X GET https://mydomain.tld/welcome-page -H "Accept: application/xml" | jq
```

Will produce something similar to:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<data>
	<pageTitle>Welcome to Umbrella Corp!</pageTitle>
</data>
```

Accepting `application/json` and `application/xml` for areas of the application can be achieved by registering a listener to the `core.request.init` event
See [Events](Events.md) for more information about registering events.  

```php
<?php
namespace Bootstrap;

class EventService {
	public function registerDefaultListeners(): void {
		// ... other event listeners

		\Core\Event::addListener(
			"core.request.init",
			fn(\Request $iRequest) => $iRequest->getConfiguration()->set("contentTypes.json.enable", $iRequest->getArg(0) == "api");
		);
	}
}
```

Content types access can likewise be set with attributes, on a per-class or per-method basis.
```php
<?php
// Allow application/json for all methods to this class
#[\Core\Attributes\AllowedContentTypes('json')]
class ApiController extends Controller {

	// Extraordinarily allow application/xml for this method
	#[\Core\Attributes\AllowedContentTypes('xml')]
	public function index(): void {
		// ...
	}
}
```

## Unacceptable requests (406 Not Acceptable)

You can configure the framework to explicitly send a bodyless **406 Not Acceptable** response when negotiating content type fails.  
Effectively requiring clients to send an `Accept` header with a supported content type preference.

This behavior occurs when:
- The client’s `Accept` header contains MIME types that the application cannot serve.
- The default content type is set to `null`, which prevents the application from falling back to any other content type.

To configure this behavior, set the default content type to `null` in the `request.jsonc` file:

```jsonc
{
  "defaultContentType": null
}
```

## The files array.
> **Note**  
> Since the `$_FILES` array is normalized in the `request` property.
> You should always validate the number of files uploaded or only consume index `0`.  
> Even when expecting a single file upload to ensures your upload process handles unexpected multiple uploads gracefully.

The global state `$_FILES` array is normalized in the controllers `request` property.  
Both single-file and multi-file uploads will be structured in a similar format.  

**Single File Uploads:**
When a single file is uploaded via an input field (e.g., `<input type="file" name="myfile">`), 
The `$this->request->files` property will be similar to:

```php
<?php
[
    'myfile' => [
        0 => [ // Wrapped in an array to maintain consistent structure
            'name'     => 'document.pdf',
            'type'     => 'application/pdf',
            'tmp_name' => '/tmp/phpABCDE',
            'error'    => 0,
            'size'     => 54321,
        ]
    ]
];
```

**Multiple File Uploads:**
When multiple files are uploaded via an input field with array notation and the multiple attribute (e.g., <input type="file" name="recipe-files[]" multiple>).  
The `$this->request->files` property will be similar to:

```php
<?php
[
    'recipe-images' => [
        0 => [
            'name'     => 'cheese.jpg',
            'type'     => 'image/jpeg',
            'tmp_name' => '/tmp/phpFGHIJ',
            'error'    => 0,
            'size'     => 102400,
        ],
        1 => [
            'name'     => 'how-to-cook.mp4',
            'type'     => 'video/mp4',
            'tmp_name' => '/tmp/phpKLMNO',
            'error'    => 0,
            'size'     => 5120000,
        ]
    ]
]
```