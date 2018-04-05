#Changelog#
v3.0-beta7
- **This release is backwards incompatible. **  
- Fixed syntax errors introduced in previous version.  
- Main Controller class moved out from \Core namespace.  
- Removed PHP version check, as it's outdated now.  
- Streamlined the style of xceptions and errors thrown.  
- Errors and exceptions will now clear the output buffer.  

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
- **This release is backwards incompatible. **  
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
