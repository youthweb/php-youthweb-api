# PHP Youthweb API

[![Latest Version](https://img.shields.io/github/release/youthweb/php-youthweb-api.svg)](https://github.com/youthweb/php-youthweb-api/releases)
[![Software License GPLv3](http://img.shields.io/badge/License-GPLv3-brightgreen.svg)](LICENSE)
[![Build Status](http://img.shields.io/travis/youthweb/php-youthweb-api.svg)](https://travis-ci.org/youthweb/php-youthweb-api)
[![Coverage Status](https://coveralls.io/repos/youthweb/php-youthweb-api/badge.svg?branch=develop&service=github)](https://coveralls.io/github/youthweb/php-youthweb-api?branch=develop)
[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/youthweb/youthweb-api?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

PHP Youthweb API ist ein objektorientierter Wrapper in PHP 5.6+ für die [Youthweb API](https://github.com/youthweb/youthweb-api).

Unterstütze API Version: 0.14

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

- Zugriff auf `/posts` Resourcen
- Zugriff auf `/{object}/{object_id}/posts` Resourcen
- Zugriff auf `/events` Resourcen
- Zugriff auf `/friends` Resourcen
- Zugriff auf `/{object}/{id}/friends` Resourcen
- Request Error Handling
