# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

### Changed

- [Youthweb-API 0.12](https://developer.youthweb.net/20170716-Youthweb-API-0.12.html) support, but there is still code missing for accessing the new resources
- `Youthweb\Api\Client::getUrl()` is deprecated and triggers an `E_USER_DEPRECATED` error if used
- `Youthweb\Api\Client::setUrl()` is deprecated and triggers an `E_USER_DEPRECATED` error if used
- `Youthweb\Api\Client::setUserCredentials()` is deprecated and triggers an `E_USER_DEPRECATED` error if used
- `Youthweb\Api\Client::getUserCredential()` is deprecated and triggers an `E_USER_DEPRECATED` error if used
- `Youthweb\Api\Client::setHttpClient()` is deprecated and triggers an `E_USER_DEPRECATED` error if used
- `Youthweb\Api\Client::setCacheProvider()` is deprecated and triggers an `E_USER_DEPRECATED` error if used
- `Youthweb\Api\Client::getCacheProvider()` is deprecated and triggers an `E_USER_DEPRECATED` error if used
- `Youthweb\Api\Client::buildCacheKey()` is deprecated and triggers an `E_USER_DEPRECATED` error if used
- `Youthweb\Api\Resource\Auth` is deprecated and triggers an `E_USER_DEPRECATED` error if used
- `Youthweb\Api\Resource\AuthInterface` is deprecated and triggers an `E_USER_DEPRECATED` error if used
- Update tests for PHPUnit 6

## [0.5] - 2016-11-01

### Added

- Implementation for OAuth2 Authorization Code Grant was added.
- New setting for config and collaborators through `Client::__construct($config, $collaborators)`.
- New method `Resource\Users::showMe()` for new API endpoint `/me`.
- New factories for `Resource` and PSR-7 `Request` creation.
- New `Client` methods `getCacheItem($key)`, `saveCacheItem($item)` and `deleteCacheItem($item)` in replace for deprecated `Client::setCacheProvider()`.
- New method `Client::isAuthorized()` to check if the client has a valid access_token.
- New method `Client::authorize()` to authorize a grant.
- New method `Client::getAuthorizationUrl()` to get an authorization url.
- New method `Client::getState()` to get a random state.

### Changed

- [Youthweb-API 0.6](https://github.com/youthweb/youthweb-api/releases/tag/0.6) Support.
- **Breaking:** All classes are set to `final` and implement interfaces. All protected methods are now private. If you had extend some classes, implement the interface instead.
- **Breaking:** `$data` in `Client::getUnauthorized()`, `Client::getUnauthorized()` and `Client::postUnauthorized()` must be an array. It cannot be `null` anymore.
- Switch LICENSE from GPLv2 to GPLv3.

### Deprecated

- `Client::setUserCredentials()` is deprecated and will be replaced with OAuth2 client.
- `Client::getUserCredential()` is deprecated and will be replaced with OAuth2 client.
- `Client::setHttpClient()` is deprecated. Use `Client::__construct()` instead.
- `Client::setCacheProvider()` is deprecated. Use `Client::__construct()` instead.
- `Client::getCacheProvider()` is deprecated. Use the new cache methods in `Client` instead.

### Removed

- **Breaking:** Support for PHP 5.5 was dropped. Minimum requirement is now PHP 5.6.

## [0.4] - 2016-08-01

### Changed

- [Youthweb-API 0.5](https://github.com/youthweb/youthweb-api/releases/tag/0.5) Support

### Added

- set user credentials with `setUserCredentials('Username', 'User-Token')`
- new resources `users/<user_id>` and `auth/token` added

## [0.3] - 2015-11-20

### Changed

- [Youthweb-API 0.3](https://github.com/youthweb/youthweb-api/releases/tag/0.3) Support
- **Breaking:** API Resources return the data as `Art4\JsonApiClient\Document` object instead of `stdClass`

### Removed

- **Breaking:** Drop PHP 5.4 support
- **Breaking:** Manuel installation via `autoload.php`. Use composer instead

## [0.2] - 2015-06-21

### Added

- [Youthweb-API 0.2](https://github.com/youthweb/youthweb-api/releases/tag/0.2) Support
- phpunit tests
- Travis-CI Support
- this CHANGELOG.md

## [0.1] - 2015-04-20

### Added

- First workable client for Youthweb-API [Version 0.1](https://github.com/youthweb/youthweb-api/releases/tag/0.1)
- Http client based on [guzzlehttp/guzzle ~5.0](https://github.com/guzzle/guzzle)

[Unreleased]: https://github.com/youthweb/php-youthweb-api/compare/0.5...HEAD
[0.5]: https://github.com/youthweb/php-youthweb-api/compare/0.4...0.5
[0.4]: https://github.com/youthweb/php-youthweb-api/compare/0.3...0.4
[0.3]: https://github.com/youthweb/php-youthweb-api/compare/0.2...0.3
[0.2]: https://github.com/youthweb/php-youthweb-api/compare/0.1...0.2
[0.1]: https://github.com/youthweb/php-youthweb-api/compare/4edfb72fb1c989ac4ee91d8ed7d68d4b32c4a143...0.1
