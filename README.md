# PHP Youthweb API

[![Latest Version](https://img.shields.io/github/release/youthweb/php-youthweb-api.svg)](https://github.com/youthweb/php-youthweb-api/releases)
[![Software License GPLv3](http://img.shields.io/badge/License-GPLv3-brightgreen.svg)](LICENSE)
[![Build Status](https://github.com/youthweb/php-youthweb-api/actions/workflows/ci.yml/badge.svg?branch=v0.x)](https://github.com/youthweb/php-youthweb-api/actions)
[![codecov](https://codecov.io/gh/youthweb/php-youthweb-api/branch/v0.x/graph/badge.svg?token=vWBAUXTFLI)](https://codecov.io/gh/youthweb/php-youthweb-api)

PHP Youthweb API ist ein objektorientierter Wrapper in PHP 8.0+ für die [Youthweb API](https://github.com/youthweb/youthweb-api).

## Installation

[Composer](http://getcomposer.org/):

```
$ composer require youthweb/php-youthweb-api
```

## [Dokumentation](docs/README.md) / Anwendung

```php
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Config
$client_id = 'CB91ZullPa4ync4l';
$client_secret = 'YC7CXuDXX9pF5SeTKs9enkoPjbV01QIs';
$redirect_url = 'http://localhost/php-youthweb-api/login-button.php';
$scope = ['user:read']; // See http://developer.youthweb.net/api_general_scopes.html
//A resource owner identifier to separate the caches
$resourceOwnerId = 'a24d4387-f4de-4318-929a-57d475162fd4'; // or '12345' or 'user@example.com'

require 'vendor/autoload.php';

$config = Configuration::create(
    $client_id,
    $client_secret,
    $redirect_url,
    $scope,
    $resourceOwnerId,
);

// optional: set other options, providers, etc
$config->setApiVersion('0.20');
$config->setCacheItemPool(new \Symfony\Component\Cache\Adapter\FilesystemAdapter());
$config->setHttpClient(new \GuzzleHttp\Client());

$client = \Youthweb\Api\Client::fromConfig($config);

echo '<h1>Mit Youthweb einloggen</h1>';
echo '<form method="get" action="'.$redirect_url.'">
<input name="go" value="Login" type="submit" />
</form>';

if ( isset($_GET['go']) )
{
    try {
        // (1) Try access the API
        $me = $client->getResource('users')->showMe();
    } catch (\Youthweb\Api\Exception\UnauthorizedException $th) {
        // (2) We need to ask for permission first
        header('Location: '.$th->getAuthorizationUrl());
        exit;
    }

    // (4) We have access to the API \o/
    printf('<p>Hallo %s %s!</p>', $me->get('data.attributes.first_name'), $me->get('data.attributes.last_name'));
    printf('<p>Deine Email-Adresse: %s', $me->get('data.attributes.email'));
}
elseif ( isset($_GET['code']) )
{
    // (3) Here we are if we have a permission
    $client->authorize('authorization_code', [
        'code' => $_GET['code'],
        'state' => $_GET['state'],
    ]);

    header('Location: '.$redirect_url.'?go=Login');
    exit;
}
```

Weitere Informationen zur Anwendung gibt es in der [Dokumentation](docs/README.md).

## Tests

```
phpunit
```

## [Changelog](CHANGELOG.md)

Der Changelog ist [hier](CHANGELOG.md) zu finden und folgt den Empfehlungen von [keepachangelog.com](http://keepachangelog.com/).

## Todo

- Erstellen von Posts auf Endpoint `/{object}/{object_id}/posts`
- Zugriff auf `/events` Resourcen
- Zugriff auf `/friends` Resourcen
- Zugriff auf `/{object}/{id}/friends` Resourcen
