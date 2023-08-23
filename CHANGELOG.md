# Changelog #
v7.2.0
- Simplified the way HTTP code exceptions are handled
- Added another test related to \Configuration class

v7.1.1
- Support for pulling environment variables, constants, and ini settings through config files.  
- Updated the configuration documentation section in README.  

v7.1.0  
- Added tests support.  
- Refactor argument input handling for lighter coupling.  

v7.0.0  
- **This release is backwards incompatible.** 
- First steps in moving towards a dependency injection pattern.  
- Updating Database\Conection library to latest version.  
- Bundled app binary file for exeucting controllers in cli.  
- Bundling generated documentation of classes.  
- Added helper methods to \Resource.  
- Added new \Core\Router class.  
- Added new \Core\Request class.  
- Added new \Core\Response class.  
- - Controller data now has to be added to \Core\Response class.  
- Added new \Core\MVCStructure class used by \Core\ControllerName and \Core\MethodName.  
- Added new \Console class.  
- Added new \HtmlEscape class.  
- Renamed \Core\ControllerName to \Core\ClassName.  
- Better baseurl support.  
- Various new core exceptions.  
- Restructured index.php.  
- More streamlined coding standards and updated comments.  
- Updated `startup.php` to consider 'application/json' present in HTTP `Accept` header.  
- .htaccess no longer rewrites request URI to $_GET["route"]
- Moved \Core\Configuration to global namespace, as it's likely to be used elsewhere.  
- Updated README.md documentation.  
- Dropping PHP7 support, as it has reached end of life.  

v6.0.2  
- Empty strings in data arrays will now be converted to NULL values

v6.0.1
- Fixed several bugs from previous release.  
- Error handler now responds with JSON if header is set X-Requested-With: XMLHttpRequest.  
- Coding standards
  
v6.0.0
- **This release is backwards incompatible.** 
- Common controller method for all endpoints are now ::start(); instead of ::__construct();  
- Core\ControllerName now validates constrollers using reflection.  
- Support for regions (header/footer) when adding theme assets in config.  
- Database\Connection::debugQuery(); no longer represents booleans as strings.  
- Fixed regression wtih the \Resource class from v5.0.1  
- Moved \Core exceptions to their own namespace.  
- Updated some doc blocks.  

v5.1.1  
- New public method Controller::getName()  get called controller name without without appendix.  
- New public method Core\Application::getExecutedControllerName() returns the master controller name.  
- New public method Core\Application::getCalledMethodName() return the method name called on the master controller.  
- New variable added to the <body> tag, containing system fragments for easier identification.  

v5.1.0
- Allow child controllers to see and modify parent controller data variable.  
- New controller property to get parent controller object.  
- Added new \Core\ForbiddenException to also allow rerouting controllers to an "access denied" page.  
- Coding standards: Updated some variable names, and docblocks formatting.  

v5.0.1
- Fixed a bug where Core\NotFoundException would not properly reroute current controller.  
- Added support for executing namespaced controllers in Core\ControllerName.  
- Resource::get(); will not instantiate a new object, if it doesn't already hold one.  
- Updates database library to latest version.  
  
v5.0.0
- **This release is backwards incompatible.**  
- Renamed the Document class to Assets  
- Completely restructered the support for children controllers  
- Controllers must now set their own view files.  
- New file structure to enforce configuration files outside of the application directory.  
- Removed Core\Configuration from the registry object, moved to Application::getConfiguration() and Theme::getConfiguration().  
- Throw \Core\NotFoundException to reroute controller stack to NotFoundController
- Rewritten MethodName class.  
- Rewritten ControllerName class.  
- Updated database library to latest version.  
- Updated application url routing.  
- Switched to .jsonc configuration file extension, to support comments in configuration files.  
- Updated documentation.  
- Added more usage examples to database class documentation.  
- Completely removed support for i18n.  
- Renamed properties that are an instance of an object to be prefixed with an i. (lowercase C# style)  
- Updated dockblocks to be more unified.  
- Rename Registry class to more fitting Resource.  
- Removed default support for I18n.  
> *NOTE:* There's no one-size-fits-all solution to i18n, and all systems I've made so far with this framework I've had to make major alterations to the default implementation.  

v4.4.0
- Added support for variables in configuration files.  
- Updated Core\Configuration::parse(); to use JSON TROW_ON_ERROR constant.  
- Updated Core\Configuration::save() to respect the location of the passed configuration file.  

v4.3.3
- Updated database library to latest version.  

v4.3.2  
- Add support for setting global & theme version.  
- Theme supports versio cache busters.  

v4.3.1  
- Finally allow controllers to set views, and automatically execute other view controllers.  
- Fixing error handlerproducing argument count errors.  

v4.3.0
- Updated environment class with cli compatibility.  
- Only add to childrens array if controller is found.  
- Database\Entity::load no longer created redundant objects.  
- Updated error handler. Now converting errors to exceptions.

v4.2.0  
- Updated to latest Database library version..  

v4.1.0  
- Added support for config files one level above current working directory.  
- Added Environment class to core library.  
- Moved database connection to init earlier in the application layer.  

v4.0-beta  
- Added feature for language/i18n, enabled in config files.  
- Renamed Tools class to Url, as it contains url only realted functions.  
- Functions class renamed to Debug.  
- Introduced argument and return types to functions.  
- Core\Application::arg(); no longer returns all args if argument is ommited, instead use Core\Application::getArgs();  
- Added more error types to Core\Configuration::parse();  

v3.2  
- Allow for setting views in subdirectories.  
- Fixed autoloader not allowed short class names.  
- Controller::getView(); now throws exception when view file is not found in theme.  
- Fixes controllers sometimes being dispatched with spaces in them.  

v3.1  
- New constant DS  
- Change some single quote strings to double quotes.  
- Fixed typos in changelog.  
- Assets linked in theme.json can now be absolute urls. e.g. external assets.  
- .htaccess files now includes lines to force HTTPS based websites. (commented as default)
- Adding default controller variable containing the name of the current page.  
- Prevent adding assets to DOM that does not exist in theme.  
- Prevent fatal errors with trailing slashes in urls.  

v3.0-beta8  
- New constant DS  
- Change some single quote strings to double quotes.  

v3.0-beta7  
- **This release is backwards incompatible.**  
- Fixed syntax errors introduced in previous version.  
- Main Controller class moved out from \Core namespace.  
- Removed PHP version check, as it's outdated now.  
- Streamlined the style of xceptions and errors thrown.  
- Errors and exceptions will now clear the output buffer.  
- CWD constant is now equal to the dir of the index.php file.  

v3.0-beta6  
- Only assign configuration errors, if it's actually needed.  
- Controller method names should new be properly sanitized to PHP5 compatible method/function name.  
- Cache headers moved to preprocess.php, reverted from v3.0-alpha6.  
- Updated documentation to reflect the new methodName changes.  

v3.0-beta5  
- Fixing merge conflict.  
- variable naming error in Theme::getDirectoryUri();  
- Cache.max_age default configured time should be 0.  
- New function to get theme path uri.
- \Core\Theme::getDirectoryUri(); now points directly to the theme.  
- DOM\Document no longer uses a static context.  

v3.0-beta4  
- **This release is backwards incompatible.**  
- Better support CLI mode support.  
- Almost completely rewritten exception handler.  
- Coding style changes, mainly apostrophes.   
- Corrected syntax error, from beta3.  
- Theme::getDirectoryUri(); now uses SERVER_NAME instead of HTTP_HOST.  
- Removed branding from docblocks (I don't really like commercials).  
- Bugfixing: one should now again be able to add assets from derived controllers.  
- Bugfixing: Controller::hasView(); didn't check if file existed before returning true.  
- Updated some documentation.  
- Now forcing controllers to extend upon \Core\Controller();  
- Now forcing child controllers to invoke the parent constructor.  
- Methods in \Core\Controller are now final, as they were never meant to be overridden.  
- \Core\Controller:$request should not contain cookie variables.  
- \Core\Controller now forces chlid classes index methods.   

v3.0-beta3  
- Change utf8 to utf8mb4 in MySQL connections, allowing emoji support.

v3.0-beta2  
- \Core\Application(); Is now declared as final, as it wasn't intended to be extended upon.  
- \Core\Controller::$request now also contains COOKIE values.  
- Syntactical changes.  
- Finally removed the Singleton(); class.  
- .htaccess compatibility changes.  
- Theme is now loaded as a seperate object.  
- Functions::url(); ranmed to Theme::getDirectoryUri();  
- Some documentation blocks opdated.  
- Added default timezone to preprocessor

v3.0-beta1  
- The project should now finally, be in a stable release, backwards incompatible updates may still be released.  
- Renamed Controller::getViewPath(); to Controller:getView(); to match the rest of the function names.  
- Moved some logic to IndexController instead.  

v3.0-alpha7  
- PHP Error messages updated to include bootstrap compatibility.  
- Moving stylesheet and javascript logic to seperate variables.  

v3.0-alpha6  
- theme.json and error_log are now inaccessible from URL's  
- Cache control headers moved to Application::__construct();  
- New config value, cache_control.  

v3.0-alpha5  
- Fixed a stupid double function declaration "Functions::url();" resulting in fatal error.  
- Removed Application::setView(); as that is conbtrollers area.  
- Moved view related methods to \Core\Controller allowing a cleaner template syntax in views, aka less function calls.  
- Updated a few docblocks.  
- Controllers can now disable their view, by setting them to null.  
- Finally started updating README.md  
- Added a .gitignore.  

v3.0-alpha4
- Upgraded jQuery to v3.2.1, effectively giving legacy browsers the middle finger.  
- Fixed incorrect call to Exception::getFile(); in bundled exception handler.  
- Cleaned up messy .htaccess.  
- Added CLI constant, getting ready for CLI support.  
- Error & Exception handler now uses CLI constant check.  
- Removed the Tools class, Tools::url(); moved to Functions class.  
- Documented the Registry class by comments.  
- Documented Functions class.  

v3.0-alpha3
- Added \DOM\Document as a Controller property.  
- Added core Configuration as a Controller property.  
- Added a convenience wrapper Controller::setView();  
- Restructured the Application::dispatch(); method to properly construct class names.  
- Fixed fatal errors in the Notfound controller.  

v3.0-alpha2
- Introduced CWD constant, containing current working directory.  
- Implemented a registry class to avoid the use of future singletons.  
- Updated default theme files to match the new structure.  
- ConfigurationParser can now be used to parse additional configuration files.  
- Page title handling, is now handled by Controller class, instead of the Application.  
- Added second argument, EXTR_SKIP,to index.php's extract(); call for security reasons.  
- Merged Debug and Tools classes, to Functions instead, yeah yeah, I know bad practice, screw me later.  
- Themes are now required to define a 'theme.json' config file.  
- Removed 'theme-functions.php' file.  
- Renamed ConfigurationsParser() to Configuration.  
- Controller filenames, and classes are now Capitalized.  

v3.0-alpha1
- Rewritten to a controller based structure, having a controller is now mandatory.

v2.1
- **This release is backwards incompatible. **  
- Updated Tools::url(); functin to not strip filesnames from assets if they matched default route.  
- Added SSL constant  
- Fixed DatabaseConnection parameter order.  
- Fixed Tools::url(); should no longer return paths with the requested path in it.  
- Updated Tools::url(); functin to not strip filesnames from assets if they matched default route.  
- Added SSL constant  
- Updated default 404 page to be more helpful and informative.  
- Added default title to 404 page.  
- Fixed singleton class to not always return the first class with a method containing getInstance();  
- Singleton classes don't need to be instantiated.  
- Removed \Core\Application::getView(); in favour of a more generic Application::getViewPath(); method.
- \Core\Application::getControllerPath(); and \Core\Application::getViewPath(); will now return requested path given no arguments.  
- Minor changes to default theme being compatible with this release.  
- allow_views_override setting is no longer available.  
- Renamed main-functions to theme-functions.  

v2.0
- **This is a major release and is backwards incompatible. **
- Introduced Tools class  
- Fixed a bug in footer.php (javascript not being added properly)  
- Renamed main.php to main-functions.php  
- Removed the public/ folder, stylesheets should belong in the theme directory  
- Moved javascript into the application folder  
- Controllers can now define their own view if allowed by config  
- Removed debug setting from databse section in config  
- Added a 404 page controller  
- Rewrote index.php to better handle controllers/views and 404 pages
- Updated bundled jQuery version  
- Updated README.md documentation  

v1.8
- Totally rewritten the semi-broken DbConnection class  
- Finally documented the DatabaseConnection class  
- Removed Data Objects due to incompatibility  

v1.7.1
- Removed the registry structure again, it wouldn't fit into the general structure.  
- Registry class removed.  
- Renamed functions.php to main.php  

v1.7
- Introduced Registry class to hold all the variables being created within the application.  
- Updated Application class to reflect the new Registry  
- Removed helper class.  
- Moved Helper::url to DOM\Document::url  
- Updated documentation  

v1.6.1
- Configuration parser should no longer var_dump(); after saving content. (was I drunk?)  
- DOM\Document now uses a static context.  
- Singleton class introduced.  
- ConfigurationParser(); and DbConnection(); can now be accessed through Singleton.  
- Renamed ->getConnection(); to getInstance(); in Database::DbConnection();  
- E_WARNING and E_USER_WARNING are now treated as fatal.  
- Classes no longer require a .class.php extension.  
- Minimum required version is now PHP 5.4  

v1.6
- Removed unused style in public/stylesheets/screen.css  
- Introduced \DOM\document class consult README for documentation  
- Moved functions.php include from \Core\Application.class.php to index.php  
- jQuery is now included by default in functions.php  
- screen.css is now included by default in functions.php  
- Modified Helper::url(); to only include one beginning slash.  
- Updated header.php and footer.php to include files added by \DOM\document class  
- Updated README.md to reflect the new changes  
- Removed error reporting check in exception handler, exceptions should always be handled  
- Changed arrays to a more modern syntax  
- Switched echo's to print in preprocess.php  
- Added error type detection to Core\ConfigurationParser.class.php  
- Introduced custom exceptions to core classes  
- PHP 5.3 is now the minimum required version  
  
v1.5  
- Rewrote ->getTitle(); to support if ->setTitle(); was not called in a controller file.  
- Removed ->setArgs(); method, arguments will now be set upon init.  
- Exception handler should no longer generate invalid HTML. (WTF noob!)  
- Removed unnecessary spaces in Application.class.php  
- Moved database related classes to their own namespace, SampleObject() updated to reflect those changes.    
- DbConnection::__construct() now throws exceptions instead of exiting upon error.  
- Renamed Configuration class to ConfigurationParser to avoid confusion about this class actually containing settings.  
- Updated apostrophes in classes to a more C#'ish way.  

v1.4  
- Introduced Core\DBObject class  
- Introduced SampleObject class extends Core\DBObject  
- Changed spl_autoloader variables to camelCasing  
- Updated Documentation to reflect the new changes.  
  
v1.3  
- Introduced Configuration class  
- Introduced Exception handler  
- Moved $db into the application class  
- Updated README, more to come  
- Application::setArgs() is now private, it was never intented to be used publicly  
- Renamed Initialize class to Application  
- Renamed Application namespace to Core  
- Cleaned up index.php  
- Updated database class to throw exceptions instead of trying to handle it's own errors  
- ->execute() will now fetch as object instead of an associative array.  

v1.2.5  
- Renamed core.php to preprocess.php (For the glory of satan of course)  
- Replaced the bloated DB class with a simplified version  
- Added debugging configuration  

v1.2  
- Added core.php
- Cleaned up index.php a bit
- Switched config.php file to config.json file. 
- Updated Application\Initialize::config() method to reflect the new config file structure.
- Updated Application\Initialize::config() method to reflect the new config file structure.
- Updated the README description.
- Added an error handler
- Started writing some documentation, more to come.

v1.1  
- Introduced a namespacing structure.  
- Introduced a database class.  
- Temporarily disabled the broken htaccess (I ought to fix the some day)  
  
v1.0  
- Initial release
