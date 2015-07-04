#Changelog#
v1.3 
- Introduced Configuration class  
- Introduced Exception handler  
- Moved $db into the application class  
- Updated README, more to come  
- Application::setArgs() is now private, it was never intented to be used pubicly  
- Renamed Initialize class to Application  
- Renamed Application namespace to Core  
- Cleaned up index.php  

v1.2.5
- Renamed core.php to preprocess.php (For the glory of satan)  
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
