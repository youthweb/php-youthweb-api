# PHP Youthweb API

[![Latest Version](https://img.shields.io/github/release/youthweb/php-youthweb-api.svg)](https://github.com/youthweb/php-youthweb-api/releases)
[![Software License GPLv3](http://img.shields.io/badge/License-GPLv3-brightgreen.svg)](LICENSE)
[![Build Status](https://github.com/youthweb/php-youthweb-api/actions/workflows/ci.yml/badge.svg?branch=v0.x)](https://github.com/youthweb/php-youthweb-api/actions)
[![codecov](https://codecov.io/gh/youthweb/php-youthweb-api/branch/v0.x/graph/badge.svg?token=vWBAUXTFLI)](https://codecov.io/gh/youthweb/php-youthweb-api)

PHP Youthweb API ist ein objektorientierter Wrapper in PHP 5.6+ für die [Youthweb API](https://github.com/youthweb/youthweb-api).

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

require 'vendor/autoload.php';

$client = new Youthweb\Api\Client([
    'api_version'   => '0.18',
    'client_id'     => $client_id,
    'client_secret' => $client_secret,
    'redirect_url'  => $redirect_url,
    'scope'         => $scope,
]);

echo '<h1>Mit Youthweb einloggen</h1>';
echo '<form method="get" action="'.$redirect_url.'">
<input name="go" value="Login" type="submit" />
</form>';

if ( isset($_GET['go']) )
{
    if ( ! $client->isAuthorized() )
    {
        header('Location: '.$client->getAuthorizationUrl());
        exit;
    }

    $me = $client->getResource('users')->showMe();

    printf('<p>Hallo %s %s!</p>', $me->get('data.attributes.first_name'), $me->get('data.attributes.last_name'));
    printf('<p>Deine Email-Adresse: %s', $me->get('data.attributes.email'));
}
elseif ( isset($_GET['code']) )
{
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
- Request Error Handling
