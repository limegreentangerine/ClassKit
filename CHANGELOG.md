# Changelog

All notable changes to ClassKit are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and
the project uses [Semantic Versioning](https://semver.org/).

## [1.0.7] - 2026-10-06

### Removed

- Removed `ClassKit\Area\GlobalArea` and its `GlobalArea` package alias.

## [1.0.6] - 2026-09-30

### Fixed

- XML API error responses (4xx/5xx) no longer throw a `TypeError`.
  `Response::fromType()` now uses late static binding, so `ErrorResponse::fromType()`
  returns an `ErrorResponse` for both XML and JSON.
- Network failures (timeouts, DNS errors, refused connections) are now caught
  and returned as an `ErrorResponse` instead of escaping `makeRequest()`.
  Timeouts return 504; other failures return 500.
- Error bodies built by `ConnectionController` (network failures and invalid
  request methods) are now valid for XML connections. They are sent as
  `<error><message>…</message></error>` instead of an array, which XML
  responses rejected.
- `Response::getUrl()` now returns the request URL for responses from
  `makeRequest()`, including error responses. It was always empty.

### Changed

- Request timeouts can now be overridden per connection through the
  `$timeout` (default 10s) and `$connectTimeout` (default 5s) properties.

## [1.0.5] - 2026-09-28

### Added

- Cached API search responses: `CachedSearch` now takes class names for the
  logger and search list, supports searches without a query builder, and
  hashes cache keys so URLs containing `/` are stored correctly.

## [1.0.4] - 2026-09-24

### Changed

- Updated the package icon.

## [1.0.3] - 2026-09-22

### Fixed

- Corrected date handling in lifecycle entities by switching ORM timestamp fields
  to immutable datetime types, avoiding formatting and type issues when working
  with entity update dates.

## [1.0.2] - 2026-09-21

### Fixed

- Corrected an incorrect class name in the GUID-based updated entity helper.
- Refreshed tests and documentation for the lifecycle entity helpers.

## [1.0.1] - 2026-09-21

### Added

- Added GUID-based lifecycle tracking via `UpdatedGuidEntity`.

### Changed

- Improved package controller and entity update behavior for package lifecycle
  hooks.
- Fixed package update/uninstall event handling so package state changes are
  reflected correctly.

## [1.0.0] - 2026-09-18

### Added

- Reusable package traits for attributes, blocks, Express objects, files, pages,
  storage, and themes.
- Storage configuration support for remote and custom storage types.
- Expanded package, API, page, entity, search, logging, and environment helpers.

### Changed

- Expanded the README with installation guidance, package structure, helper
  descriptions, trait documentation, and usage examples.
- Improved Composer test scripts to run with the project’s supported PHP error
  reporting configuration.

### Tests

- Added comprehensive coverage for package behavior and package traits.

### Initial release

- Shared Concrete CMS package controller and interface abstractions.
- Page, search, file import, API, logging, environment, and localization
  utilities.
- Base entity and page/theme helper classes.

[1.0.7]: https://github.com/limegreentangerine/ClassKit/releases/tag/1.0.7
[1.0.6]: https://github.com/limegreentangerine/ClassKit/releases/tag/1.0.6
[1.0.5]: https://github.com/limegreentangerine/ClassKit/releases/tag/1.0.5
[1.0.4]: https://github.com/limegreentangerine/ClassKit/releases/tag/1.0.4
[1.0.3]: https://github.com/limegreentangerine/ClassKit/releases/tag/1.0.3
[1.0.2]: https://github.com/limegreentangerine/ClassKit/releases/tag/1.0.2
[1.0.1]: https://github.com/limegreentangerine/ClassKit/releases/tag/1.0.1
[1.0.0]: https://github.com/limegreentangerine/ClassKit/releases/tag/1.0.0
