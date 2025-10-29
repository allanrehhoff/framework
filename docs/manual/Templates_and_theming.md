## Templates and theming
> [!WARNING]
> This documentation only applies to version prior to v10
> v10 and beyond does not support different themes
> Feature was removed due to low use.
> Documentation is kept for backwards compatibility.

> [!IMPORTANT] 
> This framework does not (as of yet) bundle any template/theming engine.  
> You'll therefore have to handle escaping of all output using the helper methods `$entity->safe("key")` or `\Str::safe("content")`  
> Alternatively you may composer install/bundle, your preffered engine, and alter `\Core\Renderer` accordingly.  

Template files must be created in your configured theme folder (`default` is the default theme bundled)
Each theme/template should contain at least the following files.  

- header.tpl.php (Required)  
- footer.tpl.php (Required)  
- (default-route).tpl.php (Required) (default-route indicates a filename matching the view set by the controller.)  
- notfound.tpl.php (Required)  
- THEMENAME.theme.jsonc (Required)

It is assumed by the core that your theme has at least the required files, failing to create those files will result in errors.  
The per-theme configuration files should be located in the `storage/config/` directory outside your web root.  
  
Every view file must have the extension `.tpl.php` this is to distinguish them from their representative controller files.  

You can add a new "partial" or "children" by adding it's path to the controllers data.
```php
<?php
$this->response->data["sidebar"] = $this->template->getViewPath("sidebar");
```

And then in your template files

```php
<?php
require $sidebar;
```

Theme assets should be configured in the **THEMENAME.theme.jsonc** file, and paths must reside in the **storage/config/** directory.  

> [!NOTE]
> header.tpl.php, footer.tpl.php, and any other view files you plan to include or require in another view file can have a controller attached, if they were invoked as a child controller, see Controllers and Methods section.