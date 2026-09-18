# ClassKit

[![Tests](https://github.com/limegreentangerine/ClassKit/actions/workflows/Tests.yml/badge.svg)](https://github.com/limegreentangerine/ClassKit/actions/workflows/Tests.yml)

ClassKit is a shared utility package for Limegreentangerine projects that build on Concrete CMS and concrete5 package infrastructure. It brings together common helper classes, package lifecycle abstractions, and extension patterns that reduce boilerplate across LGT projects.

## Overview

This package is intended for internal use in Concrete CMS package development. It includes reusable functionality for package registration, page and search utilities, file import helpers, environment checks, logging, and simple API client wrappers.

The goal is to keep common logic in one place while staying lightweight and easy to extend.

## Requirements

- PHP 8.4+
- Concrete CMS 9.5+
- Composer

## Installation

Install the package via Composer:

```bash
composer require limegreentangerine/class_kit
```

If this is being used as part of a local package workspace, make sure Composer autoloading is enabled and the package is installed in the Concrete CMS environment where the parent package is loaded.

## Included helpers

The package currently includes the following functionality:

- Page and page-list extensions for common attribute and topic filtering use cases
- Global area localization for multilingual page layouts
- Shared package controller and package interface patterns
- Environment checks for local, staging, and production mode
- Cached search helper for page and content lookup flows
- File import utility for bringing remote images into the Concrete file manager
- API request wrapper for JSON/XML service integrations
- Logger abstraction and standard response handling

## Package structure

```text
src/
├── Api/
│   └── ConnectionController.php
├── Area/
│   └── GlobalArea.php
├── Environment/
│   └── Environment.php
├── File/
│   └── ImportFileTrait.php
├── Log/
│   └── Logger.php
├── Package/
│   ├── PackageController.php
│   ├── PackageInterface.php
│   └── Traits/
│       ├── AttributeTrait.php
│       ├── BlockTrait.php
│       ├── ExpressTrait.php
│       ├── FileTrait.php
│       ├── PageTrait.php
│       ├── StorageTrait.php
|       └── ThemeTrait.php
├── Page/
│   ├── Page.php
│   ├── PageList.php
│   └── TranslationAdaptorTrait.php
├── Search/
│   ├── CachedSearch.php
│   └── Result.php
controller.php
```

## Example usage

### Environment checks

```php
use ClassKit\Environment\Environment;

if (Environment::isLocal()) {
    // Local-only setup
}

if (Environment::isProduction()) {
    // Production-only setup
}
```

### Localized global area

```php
use ClassKit\Area\GlobalArea;

$area = new GlobalArea('Header');
```

This helps produce locale-aware area handles for multilingual pages.

### Page list filtering

```php
use ClassKit\Page\PageList;

$pageList = new PageList();
$pageList->filterByMultipleTopics([
    [
        'handle' => 'topics',
        'topic' => 42,
    ],
    [
        'handle' => 'related_topics',
        'topic' => 'Example Topic',
    ],
], 'AND');
```

### Cached search results

```php
use ClassKit\Search\CachedSearch;

$search = new CachedSearch(Logger::class, 'search_results', 3600);
$ids = $search->search(
    MySearchClass::class,
    '/search',
    ['category' => 'news'],
    function ($list) {
        $list->filterByKeywords('concrete5');
    }
);
```

### API client base class

```php
use ClassKit\Api\ConnectionController;

class MyApi extends ConnectionController
{
    public function __construct()
    {
        parent::__construct('https://api.example.com', 'json', [
            'Authorization' => 'Bearer token',
        ]);
    }
}
```

The API base class centralizes request URL construction, payload encoding, header management, and response handling for JSON or XML APIs.

### File import helper

```php
use ClassKit\File\ImportFileTrait;

class MyPackageController
{
    use ImportFileTrait;

    public function importFromRemoteUrl(string $imageUrl): void
    {
        $file = $this->importImage($imageUrl, 'Products', 'Imported Images');
    }
}
```

## Development

Run the package test suite using Composer:

```bash
composer test
```

Additional formatting and validation commands are available:

```bash
composer run format:php
composer run format:php:check
composer run format:js
composer run format:js:check
```

## Notes

- This project is a shared helper package, not a standalone customer-facing application.
- It is intended to be consumed by Concrete CMS packages that need a common base layer across multiple projects.
- The package includes targeted aliases and conventions for standard Concrete CMS package workflows.

## License

Proprietary. See [LICENSE](LICENSE) for details.
