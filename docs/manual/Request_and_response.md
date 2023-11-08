# Requests and Response Handling

## Response data

Controllers can set any amount of data to be passed on as variables to view files by accessing the `Response` object.  

```php
<?php
	// Assume this url: yourdomain.tld/animals/tiger/indo-chinese
	class AnimalController extends Controller {
		public function index() {
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
Framework supports rendering response data in different formats adhering to the clients `Accept` header preference.

A `Content-Type` header containing a similar mime type will likewise be returned.  

> [!IMPORTANT]
> It is not guaranteed that the mime type returned the `Content-Type` will be identical to the mime type from the `Accept` header.  
> This can happen if `Accept` header contains `text/json`, while valid according to standards the `application/json` is widely adeopted by clients and will therefore be included in the `Content-Type` response header

Any views set by controllers will not be rendered if client accepted content type is different from HTML.  

Current supported formats are:

> **HTML (Default)**  
> `Accept: */*`  

> **JSON**  
> `Accept: application/json`  

> **XML**  
> `Accept: application/xml`  
