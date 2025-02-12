# The Arr utility class

The `\Arr` class provides a collection of null-aware array utility functions.  
Handles null values gracefully, ensuring that array manipulations do not cause errors due to null values.

## safe
```php
// Recursively escapes an array of strings for use in HTML
\Arr::safe(["<div>", "hello"]);
```

## get
```php
// Returns the value from the array by key, or default if the key doesn't exist or array is null
\Arr::get($array, "key", "default");
```

## has
```php
// Returns true if the array has the specified key, false if array is null
\Arr::has($array, "key");
```

## set
```php
// Sets a value in the array by key, and returns the array. If the array is null, an empty array is used
\Arr::set($array, "key", "value");
```

## forget
```php
// Removes the key from the array, and returns the array. If the array is null, an empty array is returned
\Arr::forget($array, "key");
```

## isEmpty
```php
// Returns true if the array is empty or null, false otherwise
\Arr::isEmpty($array);
```

## flatten
```php
// Flattens a multi-dimensional array into a single level, returning an empty array if the array is null
\Arr::flatten($array);
```

## slice
```php
// Returns a slice of the array starting at offset with optional length, or an empty array if the array is null
\Arr::slice($array, 1, 3);
```

## merge
```php
// Merges multiple arrays together, returning an empty array if any of the arrays are null
\Arr::merge($array1, $array2);
```

## filter
```php
// Filters the array using a callback function, returning an empty array if the array is null
\Arr::filter($array, fn($item) => $item > 10);
```

## map
```php
// Maps over each element in the array using a callback function, returning an empty array if the array is null
\Arr::map($array, fn($item) => $item * 2);
```