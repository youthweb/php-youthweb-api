## Stats API
[Zurück zur Navigation](README.md)

Lädt statistische Daten. Umfasst die [Youthweb Stats API](http://docs.youthweb.apiary.io/#reference/stats).

### Lädt Statistiken zu den Accounts

```php
$account_stats = $client->getResource('stats')->show('account');
```

Liefert ein [`Art4\JsonApiClient\Document` Objekt](https://github.com/Art4/json-api-client/blob/v1.x/docs/objects-document.md) mit den Daten zurück.

In der Dokumentation kannst du nachsehen, welche Daten verfügbar sind: http://docs.youthweb.apiary.io/#reference/stats/account-stats/retrieve-the-account-stats

### Lädt Statistiken zum Forum

```php
$account_stats = $client->getResource('stats')->show('forum');
```

Liefert ein [`Art4\JsonApiClient\Document` Objekt](https://github.com/Art4/json-api-client/blob/v1.x/docs/objects-document.md) mit den Daten zurück.

In der Dokumentation kannst du nachsehen, welche Daten verfügbar sind: http://docs.youthweb.apiary.io/#reference/stats/forum-stats/retrieve-the-forum-stats

### Lädt Statistiken zu den Gruppen

```php
$account_stats = $client->getResource('stats')->show('groups');
```

Liefert ein [`Art4\JsonApiClient\Document` Objekt](https://github.com/Art4/json-api-client/blob/v1.x/docs/objects-document.md) mit den Daten zurück.

In der Dokumentation kannst du nachsehen, welche Daten verfügbar sind: http://docs.youthweb.apiary.io/#reference/stats/groups-stats/retrieve-the-groups-stats
