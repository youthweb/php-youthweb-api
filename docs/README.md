# Navigation

## APIs:

* [Auth](auth.md)
* [Stats](stats.md)
* [Users](users.md)

## Schnellstart

Dieses kleine Beispiel berechnet die Prozentsatz der User, die ein Profilbild hochgeladen haben.

```php
<?php

require 'vendor/autoload.php';

// Client laden
$client = new \Youthweb\Api\Client();

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
