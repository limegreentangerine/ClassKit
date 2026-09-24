# Changelog

All notable changes to ClassKit are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and
the project uses [Semantic Versioning](https://semver.org/).

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

[1.0.4]: https://github.com/limegreentangerine/ClassKit/releases/tag/1.0.4
[1.0.3]: https://github.com/limegreentangerine/ClassKit/releases/tag/1.0.3
[1.0.2]: https://github.com/limegreentangerine/ClassKit/releases/tag/1.0.2
[1.0.1]: https://github.com/limegreentangerine/ClassKit/releases/tag/1.0.1
[1.0.0]: https://github.com/limegreentangerine/ClassKit/releases/tag/1.0.0
