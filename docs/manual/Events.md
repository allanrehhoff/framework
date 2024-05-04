# Events

The Core\Event class provides methods to register, clear, and trigger events.

Events are represented by names (strings), and event listeners (callbacks or methods) and can be associated with these events.
When an event is triggered, all registered listeners for that event are invoked in the order they were added with optional arguments.

## Registering events:

To have your event listener called in a static context provide a callable
```php
<?php
	class EmailNotifier {
		public static function sendWelcomeEmail(\User $iUser) {
			// Your e-mail logic
		}
	}

	\Core\Event::addListener("user.registered", "EmailNotifier::sendWelcomeEmail");
```
... or a closure

```php
<?php
	\Core\Event::addListener("order_created", function(\User $iUser) {
		// Your e-mail logic
	});
```

If given an array the listner will be instantiated and called in an object context
```php
<?php
	class EmailNotifier {
		public function __construct() {
			// Constructors are likely not needed
			// But are supported, but given no arguments.
		}

		public function sendWelcomeEmail(\User $iUser) {
			// Your e-mail logic
		}
	}

// Register an event listener for the 'user.registered' event
\Core\Event::addListener("user.registered", [EmailNotifier::class, "sendWelcomeEmail"])
```

You may fallback to the default `handle` method in object context, providing only the name of the class.  
```php
<?php
	class EmailNotifier {
		public function handle(\User $iUser): mixed {
			// Your e-mail logic
		}
	}

	// Register an event listener for the 'user.registered' event
	\Core\Event::addListener("user.registered", \EmailNotifier::class);
```

## Default event listeners
The `\Bootstrap\EventService` provides a `registerDefaultListeners` utility method, any listener that is needed across all processes
may be registered in here.

The contents of the file may be:
```php
<?php
	namespace Bootstrap;

	class EventService {
		public function registerDefaultListeners(): void {
			\Core\Event::addListener(
				"controller.execute.before",
				\AuthenticationService::class
			);

			\Core\Event::addListener(
				"user.registered",
				fn(\User $iUser) => \EmailService::sendWelcomeEmail($iUser)
			);
		}
	}

```

## Triggering events
```php
<?php
	$iUser = new \User() // Your newly created user
	\Core\Event::trigger("user.registered", $iUser);
```

## Handling Invalid Listeners:

If an unsupported or invalid listener type is provided, the \Core\Event class will throw an `\InvalidArgumentException`.

```php
<?php
	try {
		\Core\Event::addListener("core.application.init", "NonExistentClass::nonExistentMethod");
	} catch (\InvalidArgumentException $e) {
		// Your exception handling logic
	}
```

## Clearing Event Listeners
Call `\Core\Event::clear($event)` to clear event listeners, if given an empty string all listeners across all events will be removed.

```php
<?php
\Core\Event::addListener("core.controller.output.json", \AuthorisationService::class);

\Core\Event::addListener("core.controller.output.html", "\User::requireLogin");

// Clear all listeners for one event
\Core\Event::clear("core.controller.output.html");

// Both 'core.controller.output.json' and 'core.controller.output.html' will be cleared
\Core\Event::clear();
```

## Default events emitted by core

To attach a listener to any of these, you must add your event listeners in the `src/Bootstrap/events.php`

```php
<?php
\Core\Event::addListener("core.controller.method.before", \AuthorisationService::class);
```

```php
<?php
\Core\Event::addListener("core.controller.method.before", "\User::requireLogin");
```

### core.application.init
The very first event emitted before the application has initialized.  
Arguments passed: None

### core.controller.method.before
Event emitted right before a controller method is invoked.  
This event is emitted seperately for any child controllers.  
Arguments passed: `\Core\ClassName $iClassName, \Core\MethodName $iMethodName`

### core.controller.method.after
Event emitted right after a controller method is invoked.  
This event is emitted seperately for any child controllers.  
Arguments passed: `\Core\ClassName $iClassName, \Core\MethodName $iMethodName`

### core.output.json
Event emitted right before JSON output is rendered.  
Arguments passed: `array $data`

### core.output.xml
Event emitted right before XML output is rendered.  
Arguments passed: `string $data`

### core.output.html
Event emitted right before HTML output is rendered.  
Arguments passed: `string $view, array $data`