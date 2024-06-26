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

This framework has mainly been designed to work with apache webservers, but have confirmed compatibility with nginx in conjunction with php-fpm.  
Make sure that every request is routed to `index.php`  

For **Apache** webservers use the bundled `.htaccess` file.  

For **nginx** webservers your `nginx.conf` file may include the following in the `http.server` block.  

```
http {
	server {
		...

		location / {
			index index.php;
			try_files $uri $uri/ /index.php$args;
		}

		...
	}
}
```

## 3. Your database
The default bundled database library currently only supports MySQL/MariaDB.
Please refer to the documentation for your database of choice on how to set up these.  
You'll need to alter `storage/config/global.jsonc` accordingly with your credentials.  

You may change this behaviour to using environment variables by altering `index.php` using the `\Registry::getEnvironment()` in place of `\Registry::getConfiguration()`

## 4. Start coding
Once you see a blank "hello world" screen, you're all good to start your next journey.  