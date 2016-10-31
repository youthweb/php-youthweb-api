## Auth API
[Zurück zur Navigation](README.md)

Lädt ein Bearer-Token. Umfasst die [Youthweb Users API](http://docs.youthweb.apiary.io/#reference/auth).

Diese Resource benötigt ein User-Token, dass [hier](https://youthweb.net/settings/token) erstellt werden kann.

Diese Resource muss in der Regel nie manuell aufgerufen werden, da der Client sich im Hintergrund selber das Bearer-Token holt.

### Hole ein Bearer-Token

```php
$client->setUserCredentials($username, $user_token);

$access_token = $client->getResource('auth')->getBearerToken();
```

Liefert ein Bearer-Token in der Form "Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWV9.TJVA95OrM7E2cBab30RMHrHDcEfxjoYZgeFONFh7HgQ".
