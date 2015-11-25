Pico Editor Plugin
==================

Provides an online Markdown editor and file manager for Pico.

Install
-------

1. Extract a copy the "PicoEditor" folder to your Pico install "plugins" folder
   - or `git clone git@github.com:theshka/Pico-Editor-Plugin.git PicoEditor`
2. Place the following in your config/config.php file
```php
// Pico Editor Configuration
$config['PicoEditor'] = array(
    'enabled'   => true,
    'password'  => 'YOUR SHA-512 PASSWORD',
    'url'       => 'custom-admin-url'
);
```
3. Create your SHA-512 hashed password (http://crypo.in.ua/tools/eng_sha512.php)
4. Visit http://yoursite.com/?custom-admin-url and login
5. Thats it :)
