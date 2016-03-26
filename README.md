# PHP Youthweb API

[![Latest Version](https://img.shields.io/github/release/youthweb/php-youthweb-api.svg)](https://github.com/youthweb/php-youthweb-api/releases)
[![Software License GLPv2](http://img.shields.io/badge/License-GPLv2-brightgreen.svg)](LICENSE)
[![Build Status](http://img.shields.io/travis/youthweb/php-youthweb-api.svg)](https://travis-ci.org/youthweb/php-youthweb-api)
[![Coverage Status](https://coveralls.io/repos/youthweb/php-youthweb-api/badge.svg?branch=develop&service=github)](https://coveralls.io/github/youthweb/php-youthweb-api?branch=develop)
[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/youthweb/youthweb-api?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

PHP Youthweb API ist ein objektorientierter Wrapper in PHP 5.5+ für die [Youthweb API](https://github.com/youthweb/youthweb-api).

Unterstütze API Version: 0.4

## Installation

[Composer](http://getcomposer.org/):

```
$ composer require youthweb/php-youthweb-api
```

## [Dokumentation](docs/README.md) / Anwendung

```php
$client = new \Youthweb\Api\Client();
```

Weitere Informationen zur Anwendung gibt es in der [Dokumentation](docs/README.md).

## Tests

```
phpunit
```

## [Changelog](CHANGELOG.md)

Der Changelog ist [hier](CHANGELOG.md) zu finden und folgt den Empfehlungen von [keepachangelog.com](http://keepachangelog.com/).

## Todo

- Request Error Handling
