# The Registry Class
Is where all instances that should be globally accessible, and only instantiated once, is stored.  

Once an instance has been set in the Singleton, it is immediately accesible by using `\Registry::get()` instances are keyed by their class name definitions.  
The instance registered, will be returned.  
  
Example:  
```php
<?php
	// Exmaple 1
	$currentUser = \Registry::set(new User($uid));

	// Example 2
	$currentUser = \Registry::get("User");
```

In case an instance is namespaced the namespace should also be specified (without the initial backslash) upon retrieval.

Example:  
```php
<?php
	// Assume \User extends \Database\Entity
	\Registry::set(new \User($userID));

	// Would return the ID associated with this user.  
	print \Registry::get("User")->id();
```

An alias may also be given to any instanced stored.  

Example:  
```php
<?php
	// Assume \User extends \Database\Entity
	\Registry::set(new \Entities\User($userID), "user");

	// Will restrieve the instance of \Entities\User
	print \Registry::get("user");
```