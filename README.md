# php-gcm

Install
---------
Composer is the easiest way to manage dependencies in your project. Create a file named composer.json with the following:

```json
{
    "require": {
        "php-gcm/php-gcm": "dev-master"
    }
}
```

And run Composer to install php-gcm:

```bash
$ curl -s http://getcomposer.org/installer | php
$ composer.phar install
```

Usage
-------
```php
$sender = new Sender($key);
$message = new Message($collapseKey, $data);
$result = $sender->send($message, $devices, 5);
```
