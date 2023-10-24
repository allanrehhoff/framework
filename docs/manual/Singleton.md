# The Singleton Class
Is where all instances that should be globally accessible, and only instantiated once, is stored.  

Once an instance has been set in the Singleton, it is immediately accesible by using `\Singleton::get()` instances are keyed by their class name definitions.  
The instance registered, will be returned.  
  
Example:  
```php
<?php
	// Exmaple 1
	$currentUser = \Singleton::set(new User($uid));

	// Example 2
	$currentUser = \Singleton::get("User");
```

In case an instance is namespaced the namespace should also be specified (without the initial backslash) upon retrieval.

Example:  
```php
<?php
	// Assume \User extends \Database\Entity
	\Singleton::set(new \User($userID));

	print \Singleton::get("User")->id(); // Would get whatever ID was passed in to the user object
```

