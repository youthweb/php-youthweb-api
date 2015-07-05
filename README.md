# PHP Youthweb API

[![Latest Version](https://img.shields.io/github/release/youthweb/php-youthweb-api.svg?style=flat-square)](https://github.com/youthweb/php-youthweb-api/releases)
[![Software License GLPv2](http://img.shields.io/badge/License-GPLv2-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](http://img.shields.io/travis/youthweb/php-youthweb-api.svg?style=flat-square)](https://travis-ci.org/youthweb/php-youthweb-api)
[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/youthweb/youthweb-api?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

PHP Youthweb API ist ein objektorientierter Wrapper in PHP 5.4+ für die [Youthweb API](https://github.com/youthweb/youthweb-api).

Unterstütze API Version: [0.2](https://github.com/youthweb/youthweb-api/releases/tag/0.2)

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
```

Weitere Informationen zur Anwendung gibt es in der [Dokumentation](docs/README.md).

## [Changelog](https://github.com/youthweb/php-youthweb-api/blob/master/CHANGELOG.md)

Der Changelog ist [hier](https://github.com/youthweb/php-youthweb-api/blob/master/CHANGELOG.md) zu finden und folgt den Empfehlungen von [keepachangelog.com](http://keepachangelog.com/).

## Todo

- Request Error Handling
