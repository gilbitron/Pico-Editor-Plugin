Pico Editor Plugin
==================

Provides an online Markdown editor and file manager for Pico.

Install
-------

1. Extract a copy the "PicoEditor" folder to your Pico install "plugins" folder
2. Place the following in your config/config.php file
```php
// Pico Editor Configuration
$config['PicoEditor'] = array(
    'enabled' => true,
    'password' => 'YOUR SHA-512 PASSWORD'
);
```
3. Create your SHA-512 hashed password (http://crypo.in.ua/tools/eng_sha512.php)
4. Visit http://yoursite.com/?admin and login
5. Thats it :)
