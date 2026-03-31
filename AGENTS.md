# Agent Instructions for Framework Development
This document provides guidelines and instructions for AI agents assisting with development in this PHP framework.  
Follow these rules to ensure consistency, maintainability, and adherence to project standards.

## Coding Standards
All code must strictly adhere to the standards outlined in `CODING_STANDARDS.md`. Key requirements include:

- Use `<?php` only, no short open tags
- Tab indentation, opening braces on the same line
- Square bracket array syntax `[]`
- Explicit type declarations with union types for nullable values
- Proper docblock formatting with type hints
- No `@todo` comments in production code
- Files under `src/` are subject to style enforcement

Run `composer cs` to check code style compliance before and after any changes.

## Version Control
**Never automatically merge, commit, or push changes without explicit human review and approval.**  
All code changes must be reviewed by a human developer before being committed to version control.

## Third-Party Dependencies
Introducing third-party libraries requires careful consideration and should only be done when:  
- Manual implementation would introduce unnecessary complexity
- Manual implementation would result in unmaintainable code
- Manual implementation would create significant technical debt

When third-party dependencies are necessary:  
- Always specify exact version numbers
- For JavaScript libraries: include version in filename or as a comment in the file
- For PHP packages: specify versions in `composer.json`
- Document the rationale for inclusion in code comments or commit messages

## Database Changes
When database modifications or SQL queries are required:  
- Always output the complete SQL query in your response
- Wait for explicit human confirmation about query being executed, or other instructions.  
- Prefer named parameterized queries to prevent SQL injection
- Prefer using Entity objects for data models (e.g., User, Company, Session) and relationships (e.g., UserSession)
- Entity classes and database table names must always be in singular form, never plural

## CLI Companion
Reference the `bin/app` CLI companion for command-line operations. This tool allows executing controller methods from the command line using the syntax:

```
bin/app controller method [arguments...]
```

Use this for testing, debugging, and administrative tasks. See `docs/manual/Command_line_interface.md` for detailed usage.

## Debugging and Test Scripts
For any test or debugging scripts that can be reasonably implemented in PHP:

- Add methods to a `SandboxController` class
- Place the controller in `src/Controllers/SandboxController.php`
- Ensure methods follow the framework's controller conventions
- Use the CLI companion to execute these methods: `bin/app sandbox methodName`

## JavaScript Preferences
Prefer native JavaScript over the bundled jQuery library.  
While jQuery is available for convenience, use modern JavaScript APIs and methods whenever possible to reduce dependencies and improve performance.

## Testing
When writing tests for the framework:

- New core components must include a corresponding test file in the testsuite
- New classes require an accompanying test file
- Controllers are exempt from unit test requirements
- All tests must pass 100% of the time; prioritize implementation adjustments over test modifications
- Test refactoring should only occur when determined to be contain bugs or necessitated by new features/refactors

Run `composer test` to execute the PHPUnit test suite.

## Framework Architecture

### Core Components
- **Controllers**: Extend the `Controller` class, handle requests, set views, and pass data
- **Libraries**: Core utilities in `src/Libraries/` including Configuration, Logger, Registry, etc.
- **Templates**: Located in `src/Templates/`, use `.tpl.php` files with PHP syntax
- **Database**: Use `\Database\Connection` for queries, prefer `\Database\Entity` for data objects and relationships
- **Assets**: Manage CSS/JS through `Core\Assets` class
- **Configuration**: JSONC files in `storage/config/` for different environments

### Request Flow
1. Requests route to `src/index.php`
2. Routing determines controller and method based on URL
3. Controller processes request and sets response data/view
4. Templates render the final output

### Key Conventions
- Controllers extend `Controller` and use `$this->response` for output
- Data passed to templates via `$this->response->data[]`
- Views set with `$this->response->setView()`
- Child controllers can be executed after parent controllers
- CLI access through `CliController` methods

### Development Tools
- **Code Style**: `composer cs` for PHP CodeSniffer checks
- **Documentation**: `composer build-docs` generates PHPDoc
- **Debugging**: Use `Logger` class and environment-based debug settings

Always consult the documentation in `docs/manual/` for detailed implementation guidance on specific features.
