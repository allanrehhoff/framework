# The Console utility class

The `\Console` class provides useful tools for command-line operations and user interaction.  
It simplifies progress tracking, confirmations, and command-line argument parsing.

## showStatus
```php
// Displays a status bar in the console showing progress
\Console::showStatus($done, $total, $size);
```

## progress
```php
// Iterates over an array and applies a callback to each item while showing a progress bar
\Console::progress($iterable, function($key, $value) {
	// Do something with $key and $value
});
```

## confirm
```php
// Prompts the user for confirmation. Exits the program if the answer is not positive
\Console::confirm('Are you sure?');
```

## arguments
```php
// Parses command line arguments and returns an object containing commands, options, flags, and arguments
$args = \Console::arguments($argsArray);
```

## getOption
```php
// Retrieves a value from the parsed command line options
$value = \Console::getOption('option-name');
```