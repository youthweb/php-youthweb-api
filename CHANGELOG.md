# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

## [0.4] - 2016-08-01

### Changed

- [Youthweb API 0.5](https://github.com/youthweb/youthweb-api/releases/tag/0.5) Support

### Added

- set user credentials with `setUserCredentials('Username', 'User-Token')`
- new resources `users/<user_id>` and `auth/token` added

## [0.3] - 2015-11-20

### Changed

- [Youthweb API 0.3](https://github.com/youthweb/youthweb-api/releases/tag/0.3) Support

### Breaking

- API Resources return the data as `Art4\JsonApiClient\Document` object instead of `stdClass`

### Removed

- Drop PHP 5.4 support
- Manuel installation via `autoload.php`. Use composer instead

## [0.2] - 2015-06-21

### Added

- [Youthweb API 0.2](https://github.com/youthweb/youthweb-api/releases/tag/0.2) Support
- phpunit tests
- Travis-CI Support
- this CHANGELOG.md

## [0.1] - 2015-04-20

### Added

- First workable client for Youthweb API  [Version 0.1](https://github.com/youthweb/youthweb-api/releases/tag/0.1)
- Http client based on [guzzlehttp/guzzle ~5.0](https://github.com/guzzle/guzzle)

[Unreleased]: https://github.com/youthweb/php-youthweb-api/compare/0.4...HEAD
[0.4]: https://github.com/youthweb/php-youthweb-api/compare/0.3...0.4
[0.3]: https://github.com/youthweb/php-youthweb-api/compare/0.2...0.3
[0.2]: https://github.com/youthweb/php-youthweb-api/compare/0.1...0.2
[0.1]: https://github.com/youthweb/php-youthweb-api/compare/4edfb72fb1c989ac4ee91d8ed7d68d4b32c4a143...0.1
