#Changelog#
v3.0-alpha4
- Fixed incorrect call to Exception::getFile(); in bundles exception handler.  
- Cleaned up messy .htaccess
- Added CLI constant.
- Error & Exception handler now uses CLI constant check.

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
- Application::setArgs() is now private, it was never intented to be used pubicly  
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
