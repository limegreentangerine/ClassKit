# ClassKit

A shared utility package for Lime Green Tangerine concrete5 projects and related package development. ClassKit centralizes small, reusable helpers for package bootstrapping, page and search handling, environment checks, caching, and API integrations so that LGT packages can share a consistent base without duplicating boilerplate.

## Overview

ClassKit is designed to be used inside concrete5 packages and custom projects running on Concrete CMS 9.5+ and PHP 8.4 or newer. It includes a mix of concrete5-aware wrappers, interfaces, and helper classes that simplify common tasks while keeping the package lightweight and easy to extend.

The package is intentionally focused on practical code patterns used across LGT package development, including:

- localized global area helpers
- page list extensions and topic filters
- package lifecycle helpers and contracts
- environment detection utilities
- cached search patterns
- API connection abstractions and response formatting
- logging and package orchestration helpers

## Requirements

- PHP 8.4+
- Concrete CMS 9.5+
- Composer

## Installation

Install the package with Composer:

```bash
composer require limegreentangerine/class_kit
```

If you are using this as a concrete5 package in a local package workspace, place it in the relevant package structure and ensure Composer autoloading is active before installing the package in Concrete.

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
│   └── PackageInterface.php
├── Page/
│   ├── Page.php
│   ├── PageList.php
│   └── TranslationAdaptorTrait.php
├── Search/
│   ├── CachedSearch.php
│   └── Result.php
controller.php
```

## Common usage

### Environment checks

```php
use ClassKit\Environment\Environment;

if (Environment::isLocal()) {
    // local environment logic
}

if (Environment::isProduction()) {
    // production-only logic
}
```

### Localized global area

```php
use ClassKit\Area\GlobalArea;

$area = new GlobalArea('Header');
```

This creates a locale-aware area handle for multilingual site builds.

### Filtering pages by multiple topics

```php
use ClassKit\Page\PageList;

$pageList = new PageList();
$pageList->filterByMultipleTopics([
    [
        'handle' => 'topicAttributeHandle',
        'topic' => 42,
    ],
    [
        'handle' => 'anotherTopicAttributeHandle',
        'topic' => 'ExampleTopic',
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

### API connection wrapper

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

The base connection class helps standardize request construction, response handling, and payload formatting for JSON or XML APIs.

## Development

Run the project tests with Composer:

```bash
composer test
```

Additional formatting and validation commands are available through the Composer scripts:

```bash
composer run format:php
composer run format:php:check
composer run format:js
composer run format:js:check
```

## Notes

- This project is a helper package and is not intended to be a standalone consumer-facing application.
- It is designed to be imported into concrete5 package development workflows where shared logic is needed across multiple packages.
- The package registers targeted aliases and helper patterns to support LGT package conventions in Concrete CMS environments.

## License

Proprietary. See [LICENSE](LICENSE) for details.
