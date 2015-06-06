# PHP Youthweb API

PHP Youthweb API ist ein objektorientierter Wrapper in PHP für die [Youthweb API](https://github.com/youthweb/youthweb-api).

API Version: [0.1](https://github.com/youthweb/youthweb-api/releases/tag/0.1)

## Installation

### Composer

```
$ php composer.phar require youthweb/php-youthweb-api
```

### Manuell

Dieser Library liegt ein kleiner Autoloader bei, der verwendet werden kann.

```php
<?php

require 'vendor/php-youthweb-api/src/autoload.php';

$client = new Youthweb\Api\Client();
```

Ansonsten funktioniert auch jeder andere [PSR-4](http://www.php-fig.org/psr/psr-4/) Autoloader.

## Benutzung

```php
<?php

require_once 'src/autoload.php';

$client = new \Youthweb\Api\Client();

$account = $client->getResource('account');

var_dump($account->stats());
```

Response:

```
array (size=2)
  'user_total' => int 5727
  'user_online' => int 39
```

## [Changelog](https://github.com/youthweb/php-youthweb-api/blob/master/CHANGELOG.md)

Der Changelog ist [hier](https://github.com/youthweb/php-youthweb-api/blob/master/CHANGELOG.md) zu finden und folgt den Empfehlungen von [keepachangelog.com](http://keepachangelog.com/).

## Todo

- Request Error Handling
