## Stats API
[Zurück zur Navigation](README.md)

Lädt statistische Daten. Umfasst die [Youthweb Stats API](http://docs.youthweb.apiary.io/reference/stats).

### Lädt Statistiken zu den Accounts

```php
$account_stats = $client->getResource('stats')->show('account');
```

Liefert ein `StdObject` mit den Daten als JSON API.

### Lädt Statistiken zum Forum

```php
$account_stats = $client->getResource('stats')->show('forum');
```

Liefert ein `StdObject` mit den Daten als JSON API.

### Lädt Statistiken zu den Gruppen

```php
$account_stats = $client->getResource('stats')->show('groups');
```

Liefert ein `StdObject` mit den Daten als JSON API.
