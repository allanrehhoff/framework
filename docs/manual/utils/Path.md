# The Path utility class

The `\Path` class provides utility methods for common file path operations with null safety.  
It supports operations like normalization, joining, and resolution.

## normalize
```php
// Normalizes a file path by removing redundant slashes and resolving relative segments like '.' and '..'
\Path::normalize($path);
```

## join
```php
// Joins multiple path segments into a single normalized path
\Path::join($segment1, $segment2, $segment3);
```

## basename
```php
// Returns the base name of the given path (file or directory name)
\Path::basename($path);
```

## extension
```php
// Returns the file extension from the given path
\Path::extension($path);
```

## dirname
```php
// Returns the directory name of the given path
\Path::dirname($path);
```

## isAbsolute
```php
// Checks if the given path is absolute
\Path::isAbsolute($path);
```

## resolve
```php
// Resolves a relative path to an absolute path based on a base path
\Path::resolve($basePath, $relativePath);
```

## toUri
```php
// Converts a path to a URL-friendly format by replacing backslashes with forward slashes
\Path::toUri($path);
```