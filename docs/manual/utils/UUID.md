# The Str utility class

The \UUID class can be used for generating and manipulating UUIDs

**Recommended:**  
v4 - General use random IDs
v7 - Sortable random IDs

**Not recommended:**  
v3, v5, v6 and v8 - Unless you have a clear defined use for thoese.

## v3
```php
// Generate a version 3 UUID based on the MD5 hash of a namespace identifier (UUID) and a name (string)
\UUID::v3(\UUID::NAMESPACE_DNS, "example.com");
```

## v4
```php
// Generate a version 4 (random) UUID
\UUID::v4();
```

## v5
```php
// Generate a version 5 UUID based on the SHA-1 hash of a namespace identifier (UUID) and a name (string)
\UUID::v5(\UUID::NAMESPACE_URL, "example.com");
```

## v6
```php
// Generate a version 6 UUID (time-ordered UUID with reordered fields for DB locality)
\UUID::v6();
```

## v7
```php
// Generate a version 7 UUID (time-ordered UUID based on Unix timestamp)
\UUID::v7();
```

## v8
```php
// Generate a version 8 UUID (lexicographically sortable UUID with Unix timestamp and sub-second precision)
\UUID::v8();
```