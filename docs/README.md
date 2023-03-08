# Navigation

## APIs:

* [Stats](resource_stats.md)
* [Users](resource_users.md)

## Schnellstart

### Beispiel 1

Dieses Beispiel zeigt, wie ein Login-Button mit der Youthweb-API realisiert wird und die Daten zum autorisierten User ermittelt werden.

Für dieses Beispiel wird `youthweb/php-youthweb-api` über Composer installiert. Um die Access-Token zu speichern, empfiehlt es sich einen PSR-6 kompatiblen Cache-Provider zu verwenden, der die Daten dauerhaft cachen kann.

```
php composer.phar require youthweb/php-youthweb-api:dev-v0.x
php composer.phar require cache/filesystem-adapter
```

In diesem Beispiel verwenden wir `cache/filesystem-adapter`. Hier gibt es ein weitere Auswahl an PSR-6 kompatiblen Cache-Providern: http://www.php-cache.com/en/latest/#cache-pool-implementations

Für die Umsetzung werden eine `client_id` und ein `client_secret` benötigt. Dazu kann sich hier ein Client registriert werden: https://youthweb.net/settings/clients/new

```php
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Config
$client_id = 'CB91ZullPa4ync4l';
$client_secret = 'YC7CXuDXX9pF5SeTKs9enkoPjbV01QIs';
$redirect_url = 'http://localhost/php-youthweb-api/login-button.php';

require 'vendor/autoload.php';

use Youthweb\Api\Exception\UnauthorizedException;

$config = \Youthweb\Api\Configuration::create(

);
$config->setCacheItemPool(new \Symfony\Component\Cache\Adapter\FilesystemAdapter());

$client = \Youthweb\Api\Client::fromConfig($config);

$config = Configuration::create(
    $client_id,
    $client_secret,
    $redirect_url,
    $scope,
    ['user:read'],
    'a24d4387-f4de-4318-929a-57d475162fd4', // A resource owner identifier to separate the caches
], [
    'cache_provider' => $pool,
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

### Beispiel 2

Dieses Beispiel berechnet die Prozentsatz der User, die ein Profilbild hochgeladen haben.

```php
<?php

require 'vendor/autoload.php';

// Client laden
$client = \Youthweb\Api\Client::fromConfig(
    \Youthweb\Api\Configuration::createUnauthorized()
);

// Account Statistiken laden
$stats = $client->getResource('stats')->show('account');

// Die benötigten Daten ermitteln
$total = $stats->get('data.attributes.user_total');
$userpics = $stats->get('data.attributes.userpics');

$percentage = (int) round($userpics / $total * 100, 0);

// Ausgabe
echo $total, ' User haben einen Account', "\n";
echo $userpics, ' User haben ein Profilbild hochgeladen', "\n";
echo $percentage, '% der User haben ein Profilbild hochgeladen';
```

Das Beispiel erzeugt diese Ausgabe:

```
5503 User haben einen Account
3441 User haben ein Profilbild hochgeladen
63% der User haben ein Profilbild hochgeladen
```
