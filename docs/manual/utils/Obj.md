# The Obj utility class

The `\Obj` class provides a collection of null-aware object utility functions.
Handles null values gracefully, ensuring that object operations do not cause errors due to null values.

## get
```php
// Returns the property value from the object, or default if the property doesn't exist or object is null
\Obj::get($object, "property", "default");
```

## set
```php
// Sets a property value on the object. If the object is null, does nothing
\Obj::set($object, "property", "value");
```

## safe
```php
// Recursively escapes an object's properties for use in HTML, returns an empty object if the input is null
\Obj::safe($object);
```

## has
```php
// Returns true if the object has the specified property, false if the object is null
\Obj::has($object, "property");
```

## toArray
```php
// Converts the object to an array, or an empty array if the object is null
\Obj::toArray($object);
```

## toJson
```php
// Converts the object to a JSON string, or null if the object is null
\Obj::toJson($object);
```