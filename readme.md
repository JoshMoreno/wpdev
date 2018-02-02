# More Features and Docs Coming soon!
**Still in early alpha stages.**

## Controllers
Let's get some separation of concerns going.

This works the same way as default WP templating system. You can override parent theme controllers by including a same named controller in the child theme. You can also override a specific controller by using more specific naming (i.e `single-custom_post_type.php` will override `single.php`). This is independent of template files. Just because you have a `single-custom_post_type.php` controller does not mean you need a `single-custom_post_type.php` template file.  

1. Create a `controllers` folder in your parent and/or child theme. 

2. In the `controllers` folder, create files following the WP template hierarchy naming conventions (i.e `single-custom_post_type.php`). This directory gets searched recursively so you can organize in folders. 

3. Create a class (name it whatever you'd like) that implements the `\WPDev\Controller\ControllerInterface` interface. Implement the interface by defining a `build` method that returns an array.

4. Your data will be available in two ways: as `$data` (array) and as extracted variables. So `$data['some_value']` becomes `$some_value`. Use whatever you prefer.

There is also one more goodie that gets merged into the `$data` array - `$data['Post']` also available as `$Post`. This is an instance of `WPDev\Models\Post`.

### More docs coming soon...