## Users API
[Zurück zur Navigation](README.md)

Lädt die Daten eines Users. Umfasst die [Youthweb Users API](http://docs.youthweb.apiary.io/#reference/users).

Diese Resource benötigt ein User-Token, dass [hier](https://youthweb.net/settings/token) erstellt werden kann.

### Hole alle Steckbrief-Daten zu einem User

```php
$client->setUserCredentials($username, $user_token);

// $user_id = 123456;
$user = $client->getResource('users')->show($user_id);
```

Liefert ein [`Art4\JsonApiClient\Document` Objekt](https://github.com/Art4/json-api-client/blob/master/docs/objects-document.md) mit den Daten zurück.

In der Dokumentation kannst du nachsehen, welche Daten verfügbar sind: http://docs.youthweb.apiary.io/#reference/users/user/daten-zu-einem-user-abrufen

### Hole alle Steckbrief-Daten des Resource Owners

```php
$client->setUserCredentials($username, $user_token);

$user = $client->getResource('users')->showMe();
```

Liefert ein [`Art4\JsonApiClient\Document` Objekt](https://github.com/Art4/json-api-client/blob/master/docs/objects-document.md) mit den Daten zurück.

In der Dokumentation kannst du nachsehen, welche Daten verfügbar sind: http://docs.youthweb.apiary.io/#reference/users/user/me
