# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased](https://github.com/youthweb/php-youthweb-api/compare/0.10.0...v0.x)

### Added

- New class `Youthweb\Api\Configuration` was added.
- New interface `Youthweb\Api\Authentication\Authenticator` was added.
- New class `Youthweb\Api\Authentication\NativeAuthenticator` was added.
- The class `Youthweb\Api\Exception\ErrorResponseException` was added.
- New method `Youthweb\Api\Exception\UnauthorizedException::getAuthorizationUrl()` was added.
- Add tests for PHP 8.1, 8.2 and 8.3
- Add static code analyze with PHPStan

### Changed

- **BREAKING:** The method `Youthweb\Api\Client::__construct()` was set to private, use `Youthweb\Api\Client::fromConfig()` instead.
- **BREAKING:** The class `Youthweb\Api\Exception\UnauthorizedException` was set to `final`.
- Replace `Cache\Adapter\Void\VoidCachePool` with new `Youthweb\Api\Cache\NullCacheItemPool` class.
- Add parameter types and return types in nearly all classes
- Change code style to PER coding style
- Declare strict_types=1 in all PHP files
- Move CI tests from Travis-CI to Github Actions
- Move code coverage from Coveralls to Codecov.io

### Removed

- **BREAKING:** The method `Youthweb\Api\ClientInterface::__construct()` was removed.
- **BREAKING:** The deprecated constant `Youthweb\Api\Client::CACHEKEY_ACCESS_TOKEN` was removed.
- **BREAKING:** The interface `Youthweb\Api\AuthenticatorInterface` was removed, use `Youthweb\Api\Authentication\Authenticator` instead.
- **BREAKING:** The class `Youthweb\Api\YouthwebAuthenticator` was removed, use `Youthweb\Api\Authentication\NativeAuthenticator` instead.
- **BREAKING:** The interface `Youthweb\Api\RequestFactoryInterface` was removed, use `Psr\Http\Message\RequestFactoryInterface` instead.
- **BREAKING:** The class `Youthweb\Api\RequestFactory` was removed, use implementation of `Psr\Http\Message\RequestFactoryInterface` instead.
- **BREAKING:** The interface `Youthweb\Api\HttpClientInterface` was removed, use `Psr\Http\Client\ClientInterface` instead.
- **BREAKING:** The class `Youthweb\Api\HttpClient` was removed, use implementation of `Psr\Http\Client\ClientInterface` instead.
- **BREAKING:** The class `Youthweb\Api\JsonObject` was removed.
- Drop support for PHP 7.4

## [0.10.0](https://github.com/youthweb/php-youthweb-api/compare/0.9.0...0.10.0) - 2021-03-05

### Added

- Add support for PHP 7.4 and PHP 8.0

### Changed

- default api version was set to [Youthweb-API 0.18](https://developer.youthweb.net/20210221-Youthweb-API-0.18.html)

### Removed

- Drop support for PHP 7.2 and 7.3

## [0.9.0](https://github.com/youthweb/php-youthweb-api/compare/0.8...0.9.0) - 2019-10-01

### Added

- api version will be automatically set into Oauth2Provider
- default api version was set to [Youthweb-API 0.15](https://developer.youthweb.net/20190908-Youthweb-API-0.15.html)

### Removed

- Drop support for PHP 5.6, 7.0 and 7.1

## [0.8.0](https://github.com/youthweb/php-youthweb-api/compare/0.7...0.8) - 2019-01-15

### Added

- [Youthweb-API 0.14](https://developer.youthweb.net/20190113-Youthweb-API-0.14.html) Support
- New class `Youthweb\Api\Resource\Posts` to get posts.
- New method `Youthweb\Api\Resource\Users::showPosts()` to get posts of a user.

### Removed

- **Breaking:** `Youthweb\Api\Client::setUserCredentials()` was removed, use `Youthweb\Api\AuthenticatorInterface` instead.
- **Breaking:** `Youthweb\Api\Client::getUserCredential()` was removed, use `Youthweb\Api\AuthenticatorInterface` instead.
- **Breaking:** `Youthweb\Api\Client::setHttpClient()` was removed, use `Youthweb\Api\Client::__construct()` instead.
- **Breaking:** `Youthweb\Api\Client::setCacheProvider()` was removed, use `Youthweb\Api\Client::__construct()` instead.
- **Breaking:** `Youthweb\Api\Client::getCacheProvider()` was removed, use `Youthweb\Api\Client::getCacheItem($key)`, `Youthweb\Api\Client::saveCacheItem($item)` and `Youthweb\Api\Client::deleteCacheItem($item)` instead.
- **Breaking:** `Youthweb\Api\Client::getUrl()` was removed.
- **Breaking:** `Youthweb\Api\Client::setUrl()` was removed.
- **Breaking:** `Youthweb\Api\Client::buildCacheKey()` was removed.
- **Breaking:** `Youthweb\Api\ClientInterface::getUserCredential()` was removed.
- **Breaking:** `Youthweb\Api\ClientInterface::setUserCredentials()` was removed.
- **Breaking:** `Youthweb\Api\ClientInterface::getCacheProvider()` was removed.
- **Breaking:** `Youthweb\Api\ClientInterface::getUrl()` was removed.
- **Breaking:** `Youthweb\Api\ClientInterface::setUrl()` was removed.
- **Breaking:** `Youthweb\Api\ClientInterface::buildCacheKey()` was removed.
- **Breaking:** `Youthweb\Api\Resource\Auth` was removed.
- **Breaking:** `Youthweb\Api\Resource\AuthInterface` was removed.

## [0.7.0](https://github.com/youthweb/php-youthweb-api/compare/0.6.1...0.7) - 2018-11-07

### Added

- Support for PHP 7.3 added

### Changed

- **Breaking:** API Resources returning the data as `Art4\JsonApiClient\Accessable` instance instead of `Art4\JsonApiClient\Document`

## [0.6.1](https://github.com/youthweb/php-youthweb-api/compare/0.6...0.6.1) - 2018-09-20

### Added

- Every source file has now a license note
- Allow tests with PHPUnit 7

### Changed

- Code Style was changed to PSR-2

## [0.6.0](https://github.com/youthweb/php-youthweb-api/compare/0.5...0.6) - 2018-09-19

### Changed

- [Youthweb-API 0.12](https://developer.youthweb.net/20170716-Youthweb-API-0.12.html) support, but there is still code missing for accessing the new resources
- Update tests for PHPUnit 6

### Deprecated

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

## [0.5.0](https://github.com/youthweb/php-youthweb-api/compare/0.4...0.5) - 2016-11-01

### Added

- Implementation for OAuth2 Authorization Code Grant was added.
- New setting for config and collaborators through `Youthweb\Api\Client::__construct($config, $collaborators)`.
- New method `Youthweb\Api\Resource\Users::showMe()` for new API endpoint `/me`.
- New factories for `Resource` and PSR-7 `Request` creation.
- New `Youthweb\Api\Client` methods `getCacheItem($key)`, `saveCacheItem($item)` and `deleteCacheItem($item)` in replace for deprecated `Youthweb\Api\Client::setCacheProvider()`.
- New method `Youthweb\Api\Client::isAuthorized()` to check if the client has a valid access_token.
- New method `Youthweb\Api\Client::authorize()` to authorize a grant.
- New method `Youthweb\Api\Client::getAuthorizationUrl()` to get an authorization url.
- New method `Youthweb\Api\Client::getState()` to get a random state.

### Changed

- [Youthweb-API 0.6](https://github.com/youthweb/youthweb-api/releases/tag/0.6) Support.
- **Breaking:** All classes are set to `final` and implement interfaces. All protected methods are now private. If you had extend some classes, implement the interface instead.
- **Breaking:** `$data` in `Youthweb\Api\Client::getUnauthorized()`, `Youthweb\Api\Client::getUnauthorized()` and `Youthweb\Api\Client::postUnauthorized()` must be an array. It cannot be `null` anymore.
- Switch LICENSE from GPLv2 to GPLv3.

### Deprecated

- `Youthweb\Api\Client::setUserCredentials()` is deprecated and will be replaced with OAuth2 client.
- `Youthweb\Api\Client::getUserCredential()` is deprecated and will be replaced with OAuth2 client.
- `Youthweb\Api\Client::setHttpClient()` is deprecated. Use `Youthweb\Api\Client::__construct()` instead.
- `Youthweb\Api\Client::setCacheProvider()` is deprecated. Use `Youthweb\Api\Client::__construct()` instead.
- `Youthweb\Api\Client::getCacheProvider()` is deprecated. Use the new cache methods in `Youthweb\Api\Client` instead.

### Removed

- Support for PHP 5.5 was dropped. Minimum requirement is now PHP 5.6.

## [0.4.0](https://github.com/youthweb/php-youthweb-api/compare/0.3...0.4) - 2016-08-01

### Changed

- [Youthweb-API 0.5](https://github.com/youthweb/youthweb-api/releases/tag/0.5) Support

### Added

- set user credentials with `setUserCredentials('Username', 'User-Token')`
- new resources `users/<user_id>` and `auth/token` added

## [0.3.0](https://github.com/youthweb/php-youthweb-api/compare/0.2...0.3) - 2015-11-20

### Changed

- [Youthweb-API 0.3](https://github.com/youthweb/youthweb-api/releases/tag/0.3) Support
- **Breaking:** API Resources return the data as `Art4\JsonApiClient\Document` object instead of `stdClass`

### Removed

- **Breaking:** Manuel installation via `autoload.php`. Use composer instead
- Drop support for PHP 5.4

## [0.2.0](https://github.com/youthweb/php-youthweb-api/compare/0.1...0.2) - 2015-06-21

### Added

- [Youthweb-API 0.2](https://github.com/youthweb/youthweb-api/releases/tag/0.2) Support
- phpunit tests
- Travis-CI Support
- this CHANGELOG.md

## [0.1.0](https://github.com/youthweb/php-youthweb-api/compare/4edfb72fb1c989ac4ee91d8ed7d68d4b32c4a143...0.1) - 2015-04-20

### Added

- First workable client for Youthweb-API [Version 0.1](https://github.com/youthweb/youthweb-api/releases/tag/0.1)
- Http client based on [guzzlehttp/guzzle ~5.0](https://github.com/guzzle/guzzle)
