# php-gcm

Why
--------
[Google Cloud Messaging for Android](http://developer.android.com/google/gcm/index.html) is very powerful,
but there are a lot of details to handle. This library takes care of the details and makes GCM very easy
to use with PHP.

Support
-------
php-gcm supports the [HTTP server protocol](https://developers.google.com/cloud-messaging/server) for GCM.
There is not currently support for XMPP, but implementations and pull requests for XMPP are welcome.
See [#3](https://github.com/lkorth/php-gcm/issues/3) for more details.

php-gcm supports PHP versions >= 5.3.10. php-gcm may work on older versions of PHP, but has not been
tested on them.

Install
---------
Composer is the easiest way to manage dependencies in your project. Create a file named composer.json with the following:

```json
{
    "require": {
        "php-gcm/php-gcm": "1.1.0"
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
$sender = new Sender($gcmApiKey);
$message = new Message($collapseKey, $payloadData);

try {
    $result = $sender->send($message, $deviceRegistrationId, $numberOfRetryAttempts);
} catch (\InvalidArgumentException $e) {
    // $deviceRegistrationId was null
} catch (PHP_GCM\InvalidRequestException $e) {
    // server returned HTTP code other than 200 or 503
} catch (\Exception $e) {
    // message could not be sent
}
```

License
--------
php-gcm is licensed under the Apache 2.0 License. See the [LICENSE](LICENSE) file for more details.
