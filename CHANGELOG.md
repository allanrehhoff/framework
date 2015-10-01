#Changelog#
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
