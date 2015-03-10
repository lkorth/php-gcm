# php-gcm

Why
--------
[Google Cloud Messaging for Android](http://developer.android.com/google/gcm/index.html) is great, but Google has
only released a Java server side implementation. To use it with PHP we have to resort to writing custom functions or
classes and dealing with HTTP, curl and headers. No More! This library is a loose port of Google's
[com.google.android.gcm.server](http://developer.android.com/reference/com/google/android/gcm/server/package-summary.html)
Java library and makes GCM very easy and powerful in PHP.

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
php-gcm is licensed under the Apache 2.0 License

php-gcm is based heavily on Google's Java [GCM server](http://developer.android.com/reference/com/google/android/gcm/server/package-summary.html) code
