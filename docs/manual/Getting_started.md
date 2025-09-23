# Getting started

> [!IMPORTANT]
> As you'll likely find yourself in the need of modifying core files and libraries, there is no official update channel provided for this framework.
> Semantic versioning is however respected, which means you will theoretically be able to manually apply patches up to the next major version.  

## 1. Getting the files
Download the latest release from github, and upload it to your server cloud or the like.  
Or include it in your docker project, however compatible, no builds or Dockerfiles are officially provided as of yet.  

## 2. Webserver configuration
Because configuration files should not reside in the same directory as the application root, you must configure your server to set the `src/` as its document root.  

The exception being the dotenv `.env` file typically used for local development.  

This framework has mainly been designed to work with apache webservers, but have confirmed compatibility with nginx and Caddy in conjunction with php-fpm.  
Make sure that every request is routed to `index.php`  

**Apache**
Use the bundled `.htaccess` file.  

**nginx**
Your `nginx.conf` file may include the following in the `http.server` block.  

```
http {
    server_name example.com;

	# Set this path to your site's directory.
	root /var/www/html/src;

	location / {
		index index.php;
		try_files $uri $uri/ /index.php$args;
	}
}
```

**Caddy**
Your `Caddyfile` file may include the following.
Change port numbers accordingly.  

```
:80 {
	# Set this path to your site's directory.
	root * /var/www/html

	php_fastcgi * php:9000
	file_server
}
```

## 3. Your database
The default bundled database library currently only supports MySQL/MariaDB.
Please refer to the documentation for your database of choice on how to set up these.  
You'll need to alter `storage/config/global.jsonc` accordingly with your credentials.  

You may change this behaviour to using environment variables by altering `index.php` using the `\Registry::getEnvironment()` in place of `\Registry::getConfiguration()`

## 4. Set up your baseurl
To set up your base URL, you need to define it in the `storage/config/global.jsonc` file. Locate the `baseurl` property and set it to the root URL of your application. For example:

```jsonc
{
	"baseurl": "http://localhost"
}
```

If your application is installed in a subdirectory or listens on a non-default port, these needs to be specified as well.

```jsonc
{
	"baseurl": "https://yourdomain.com:8080/app"
}
```

Leverage the `\Core\Configuration` class' ability to pull configuration values from the environment.  
Refer to [Configuration files](Configuration_files.md) documentation for more information.  

```jsonc
{
	"baseurl": "getenv('APP_BASEURL')"
}
```

Ensure that the URL matches the domain or subdomain where your application is hosted.   
Proper configuration is essential for generating correct links and redirects.

## 5. Start coding
Once you see a blank "hello world" screen, you're all good to start your next journey.  