# ClassKit

[![Tests](https://github.com/limegreentangerine/ClassKit/actions/workflows/Tests.yml/badge.svg)](https://github.com/limegreentangerine/ClassKit/actions/workflows/Tests.yml)

ClassKit is a curated helper library for Concrete CMS package development. It centralizes the repetitive work that shows up across Limegreentangerine projects: package setup, page listing patterns, localized content helpers, API access, environment checks, file imports, and lightweight entity/search utilities.

## Overview

ClassKit is designed to be consumed by Concrete CMS packages instead of being a standalone application. The goal is to keep common package logic in one place while keeping the surface area small and easy to extend.

The library helps with:

- package lifecycle management and installation hooks
- common Concrete CMS package traits for attributes, blocks, themes, pages, and storage
- page and AJAX-page helpers for list rendering and pagination
- localization and global-area helpers for multilingual builds
- environment-aware configuration checks
- cached search flows and reusable result helpers
- API request wrappers and response handling
- remote file import utilities and logging patterns
- simple Doctrine-style entity base classes

## Requirements

- PHP 8.4+
- Concrete CMS 9.2+
- Composer 2.x

## Installation

Install the package via Composer:

```bash
composer require limegreentangerine/class_kit
```

When working in a local package workspace, make sure Composer autoloading is enabled in the Concrete CMS environment where the consuming package is loaded.

## Quick start

Use the base package controller as the foundation for your package:

```php
use ClassKit\Package\PackageController;
use ClassKit\Package\Traits\AttributeTrait;
use ClassKit\Package\Traits\PageTrait;
use ClassKit\Package\Traits\ThemeTrait;

class MyPackageController extends PackageController
{
    use AttributeTrait;
    use PageTrait;
    use ThemeTrait;

    protected $pkgHandle = 'my_package';
    protected $pkgVersion = '1.0.0';
}
```

This gives you a consistent package bootstrap with common install hooks, service registration, and reusable setup helpers.

## Included helpers

ClassKit currently includes the following capabilities:

- Page and page-list extensions for attribute-topic filtering and search use cases
- Global area localization for multilingual page layouts
- Shared package controller and package interface abstractions
- Environment checks for local, staging, and production workflows
- Cached search helper for content and page lookups
- File import utilities for bringing remote images into the Concrete file manager
- API request base classes for JSON/XML integrations
- Logger abstraction and standard response wrappers
- Base entity classes for simple Doctrine-backed models
- AJAX page response helpers for paginated card-based content views

## Package traits

The package includes reusable traits in `src/Package/Traits` for common Concrete CMS package setup tasks. These traits are intended for package controllers or installer classes that need consistent configuration and registration logic.

Available traits:

- `AttributeTrait` - create attribute types, sets, keys, select options, and page-type composer controls
- `BlockTrait` - install block types, register block type sets, and add page-type composer block controls
- `ExpressTrait` - create Express objects, generate simple forms, and manage default view/edit forms
- `FileTrait` - create file sets and reuse the remote image import helpers
- `PageTrait` - add pages, page types, and publish targets
- `StorageTrait` - register remote or custom storage types
- `ThemeTrait` - install or fetch page themes by handle

Example:

```php
use ClassKit\Package\Traits\AttributeTrait;
use ClassKit\Package\Traits\PageTrait;
use ClassKit\Package\Traits\ThemeTrait;

class MyPackageController extends PackageController
{
    use AttributeTrait;
    use PageTrait;
    use ThemeTrait;
}
```

These traits simplify package installation and configuration while preserving a predictable pattern that is easy to reason about across multiple codebases.

## Package structure

```text
src/
├── Api/
│   ├── ConnectionController.php
│   ├── Enum/
│   │   ├── RequestMethod.php
│   │   └── ResponseType.php
│   ├── Interface/
│   │   ├── ConnectionInterface.php
│   │   └── ResponseInterface.php
│   └── Response/
│       ├── ErrorResponse.php
│       └── Response.php
├── Area/
│   └── GlobalArea.php
├── Entity/
│   └── Core/
│       ├── BaseEntity.php
│       ├── GuidEntity.php
│       ├── UpdatedEntity.php
│       └── UpdatedGuidEntity.php
├── Environment/
│   └── Environment.php
├── File/
│   └── ImportFileTrait.php
├── Log/
│   └── Logger.php
├── Package/
│   ├── Events/
│   │   └── PackageInstallEvent.php
│   ├── Traits/
│   │   ├── AttributeTrait.php
│   │   ├── BlockTrait.php
│   │   ├── ExpressTrait.php
│   │   ├── FileTrait.php
│   │   ├── PageTrait.php
│   │   ├── StorageTrait.php
│   │   └── ThemeTrait.php
│   ├── PackageController.php
│   └── PackageInterface.php
├── Page/
│   ├── AjaxPage/
│   │   ├── AjaxPage.php
│   │   ├── AjaxPageConfig.php
│   │   ├── AjaxPageRequest.php
│   │   ├── AjaxPageResponse.php
│   │   └── Enums/
│   │       └── SortOrder.php
│   ├── Theme/
│   │   └── Theme.php
│   ├── Page.php
│   ├── PageList.php
│   └── TranslationAdaptorTrait.php
├── Search/
│   ├── CachedSearch.php
│   ├── ItemList/
│   │   └── ListTrait.php
│   ├── Result/
│   │   └── Item/
│   │       └── ItemTrait.php
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

### Base entity pattern

```php
use ClassKit\Entity\Core\BaseEntity;

class Product extends BaseEntity
{
    protected string $name;

    public function getName(): string
    {
        return $this->name;
    }
}
```

This gives a consistent foundation for package entity classes that need standard ID lookups and Doctrine-friendly structure.

## Development

Run the package test suite using Composer:

```bash
composer test
```

Additional formatting and validation commands are available:

```bash
composer format
```

This runs the following formatting commands:

```bash
composer run format:php
composer run format:js
```

Check formatting without changes:

```bash
composer run format:php:check
composer run format:js:check
```

## Notes

- This project is a shared helper package, not a standalone customer-facing application.
- It is intended to be consumed by Concrete CMS packages that need a common base layer across multiple projects.
- The package includes conventions and reusable abstractions for standard Concrete CMS package workflows.

## License

ClassKit is released under the MIT License. See [LICENSE.TXT](LICENSE.TXT) for details.
