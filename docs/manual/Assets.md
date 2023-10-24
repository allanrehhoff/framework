## The Assets class
In the Core namespace you'll find the Assets class, this can be used to add stylesheets and javscript to the page.  

Do either of the following to achieve this.  
`$this->template->assets->addStylesheet();`, `$this->template->assets->addJavascript();` methods, inside any controller.  
assets are rendered in the same order they are added  
  
If you desire to add custom media stylesheets make use of the second parameter `$media` in `$this->template->assets->addJavascript()`  
Same goes for the `$this->template->assets->addStylesheet();` method for other regions than the footer.  