# The Singleton Class
Otherwise often reffered to as a registry pattern.  

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

	// Would return the ID associated with this user.  
	print \Singleton::get("User")->id();
```

An alias may also be given to any instanced stored.  

Example:  
```php
<?php
	// Assume \User extends \Database\Entity
	\Singleton::set(new \Entities\User($userID), "user");

	// Will restrieve the instance of \Entities\User
	print \Singleton::get("user");
```

