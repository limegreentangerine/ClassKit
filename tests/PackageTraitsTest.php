<?php

namespace Concrete\Core\Attribute\Key {
    class Category
    {
        public const ASET_ALLOW_MULTIPLE = 1;

        public static array $categories = [];
        public static array $sets = [];

        public array $associated = [];
        public int $allowAttributeSets = 0;

        public static function getByHandle(string $handle): ?self
        {
            return self::$categories[$handle] ?? null;
        }

        public function associateAttributeKeyType($type): void
        {
            $this->associated[] = $type;
        }

        public function addSet(string $handle, string $name, $pkg): object
        {
            $set = new \Concrete\Core\Attribute\Set();
            $set->handle = $handle;
            $set->name = $name;
            $set->pkg = $pkg;
            self::$sets[$handle] = $set;

            return $set;
        }

        public function setAllowAttributeSets(int $value): void
        {
            $this->allowAttributeSets = $value;
        }
    }

    class CollectionKey
    {
        public static array $keys = [];

        public string $handle = '';
        public $type = null;
        public array $info = [];
        public $pkg = null;
        public array $options = [];
        public array $saved = [];

        public static function getByHandle(string $handle): ?self
        {
            return self::$keys[$handle] ?? null;
        }

        public static function add($attType, array $info, $pkg): self
        {
            $key = new self($info['akHandle']);
            $key->type = $attType;
            $key->info = $info;
            $key->pkg = $pkg;
            self::$keys[$info['akHandle']] = $key;

            return $key;
        }

        public function __construct(string $handle = '')
        {
            $this->handle = $handle;
        }

        public function getAttributeKeyID(): int
        {
            return 100 + count(self::$keys);
        }

        public function getController(): object
        {
            return $this;
        }

        public function setAllowOtherValues(): void {}

        public function setOptions(array $options): void
        {
            $this->options = $options;
        }

        public function saveKey(array $info): void
        {
            $this->saved = $info;
        }
    }
}

namespace Concrete\Core\Attribute {
    class Type
    {
        public static array $types = [];

        public static function getByHandle(string $handle): ?self
        {
            return self::$types[$handle] ?? null;
        }

        public static function add(string $handle, string $name, $pkg): self
        {
            $type = new self();
            $type->handle = $handle;
            $type->name = $name;
            $type->pkg = $pkg;
            self::$types[$handle] = $type;

            return $type;
        }
    }

    class Set
    {
        public static array $sets = [];

        public static function getByHandle(string $handle): ?self
        {
            return self::$sets[$handle] ?? null;
        }

        public function addKey($key): void
        {
            $this->keys[] = $key;
        }
    }
}

namespace Concrete\Core\Block\BlockType {
    class Set
    {
        public static array $sets = [];

        public static function getByHandle(string $handle): ?self
        {
            return self::$sets[$handle] ?? null;
        }

        public static function add(string $handle, string $name, $pkg): self
        {
            $set = new self();
            $set->handle = $handle;
            $set->name = $name;
            $set->pkg = $pkg;
            self::$sets[$handle] = $set;

            return $set;
        }
    }
}

namespace Concrete\Core\Entity\Block\BlockType {
    class BlockType
    {
        public string $handle = '';
        public $pkg = null;

        public function refresh(): void {}

        public function getBlockTypeID(): int
        {
            return 99;
        }
    }
}

namespace Concrete\Core\Entity\File\StorageLocation\Type {
    if (!class_exists('Concrete\\Core\\Entity\\File\\StorageLocation\\Type\\Type', false)) {
        class Type {}
    }
}

namespace Concrete\Core\Entity {
    if (!class_exists('Concrete\\Core\\Entity\\Package', false)) {
        class Package
        {
            public string $handle = 'package';
            public string $name = 'Package';

            public function getPackageID(): int
            {
                return 7;
            }

            public function getPackagePath(): string
            {
                return '/tmp/package';
            }

            public function getPackageHandle(): string
            {
                return $this->handle;
            }

            public function getPackageName(): string
            {
                return $this->name;
            }
        }
    }
}

namespace Concrete\Core\Express {
    class EntryList
    {
        public function __construct(public object $entity) {}

        public function filterByAttribute(string $handle, string $value, string $comparison = '='): void
        {
            $this->filter = [$handle, $value, $comparison];
        }

        public function getResults(): array
        {
            return ['entry'];
        }
    }
}

namespace Concrete\Core\Support\Facade {
    class Express
    {
        public static array $objects = [];

        public string $handle = '';
        public string $plural = '';
        public string $name = '';
        public $pkg = null;

        public static function getObjectByHandle(string $handle): ?self
        {
            return self::$objects[$handle] ?? null;
        }

        public static function buildObject(string $handle, string $plural, string $name, $pkg): self
        {
            $object = new self();
            $object->handle = $handle;
            $object->plural = $plural;
            $object->name = $name;
            $object->pkg = $pkg;
            self::$objects[$handle] = $object;

            return $object;
        }

        public function buildForm(string $formName): object
        {
            return new class($formName) {
                public function __construct(public string $name) {}

                public function addFieldSet(string $name): object
                {
                    return new class($name) {
                        public function __construct(public string $name) {}

                        public function addAttributeKeyControl(string $attribute): void {}
                        public function addTextControl(string $label, string $name): void {}
                        public function addAssociationControl(string $name): void {}
                    };
                }

                public function save(): object
                {
                    return $this;
                }
            };
        }

        public function setDefaultViewForm($form): void {}
        public function setDefaultEditForm($form): void {}
    }
}

namespace Concrete\Core\Page\Type\Composer\Control {
    class BlockControl
    {
        public int $blockTypeID = 0;
        public ?object $layoutSet = null;
        public ?string $customName = null;
        public ?string $customDescription = null;

        public function setBlockTypeID(int $id): void
        {
            $this->blockTypeID = $id;
        }

        public function addToPageTypeComposerFormLayoutSet(object $layoutSet): object
        {
            $this->layoutSet = $layoutSet;
            $layoutSet->controls[] = $this;

            return $layoutSet;
        }

        public function updateFormLayoutSetControlCustomLabel(string $label): void
        {
            $this->customName = $label;
        }

        public function updateFormLayoutSetControlDescription(string $description): void
        {
            $this->customDescription = $description;
        }
    }

    class CollectionAttributeControl
    {
        public int $attributeKeyID = 0;
        public ?object $layoutSet = null;
        public ?string $customName = null;
        public ?string $customDescription = null;

        public function setAttributeKeyId(int $id): void
        {
            $this->attributeKeyID = $id;
        }

        public function addToPageTypeComposerFormLayoutSet(object $layoutSet): object
        {
            $this->layoutSet = $layoutSet;
            $layoutSet->controls[] = $this;

            return $layoutSet;
        }

        public function updateFormLayoutSetControlCustomLabel(string $label): void
        {
            $this->customName = $label;
        }

        public function updateFormLayoutSetControlDescription(string $description): void
        {
            $this->customDescription = $description;
        }
    }
}

namespace Concrete\Core\File\Set {
    class Set
    {
        public static array $sets = [];

        public static function getByName(string $name): ?self
        {
            return self::$sets[$name] ?? null;
        }

        public static function createAndGetSet(string $name, string $type): self
        {
            $set = new self();
            $set->name = $name;
            $set->type = $type;
            self::$sets[$name] = $set;

            return $set;
        }
    }
}

namespace Concrete\Core\File\StorageLocation\Type {
    if (!class_exists('Concrete\\Core\\File\\StorageLocation\\Type\\Type', false)) {
        class Type
        {
            public static ?object $current = null;
            public static array $created = [];

            public static function getByHandle(string $handle): ?object
            {
                return self::$current;
            }

            public static function add(string $handle, string $name, $pkg = false): object
            {
                self::$created[] = [
                    'handle' => $handle,
                    'name' => $name,
                    'pkg' => $pkg,
                ];

                $type = new \Concrete\Core\Entity\File\StorageLocation\Type\Type();
                self::$current = $type;

                return $type;
            }
        }
    }
}

namespace {
    const COLLECTION_NOT_FOUND = 'collection_not_found';

    function t(string $value): string
    {
        return $value;
    }

    class BlockType
    {
        public static array $types = [];

        public static function getByHandle(string $handle): ?object
        {
            return self::$types[$handle] ?? null;
        }

        public static function installBlockType(string $handle, $pkg): object
        {
            $type = new \Concrete\Core\Entity\Block\BlockType\BlockType();
            $type->handle = $handle;
            $type->pkg = $pkg;
            self::$types[$handle] = $type;

            return $type;
        }
    }

    class PageTheme
    {
        public static array $themes = [];

        public static function getByHandle(string $handle): ?self
        {
            return self::$themes[$handle] ?? null;
        }

        public static function add(string $handle, $pkg): self
        {
            $theme = new self();
            $theme->handle = $handle;
            $theme->pkg = $pkg;
            self::$themes[$handle] = $theme;

            return $theme;
        }
    }

    class Page
    {
        public static array $pagesById = [];
        public static array $pagesByPath = [];

        public string $error = '';
        public string $handle = '';
        public int|string $idOrPath = 0;
        public string $path = '';

        public function __construct(int|string $idOrPath = 0, string $path = '')
        {
            $this->idOrPath = $idOrPath;
            $this->path = $path;
        }

        public static function getByID(int $id): self
        {
            return self::$pagesById[$id] ??= new self($id, '');
        }

        public static function getByPath(string $path): self
        {
            if (isset(self::$pagesByPath[$path])) {
                return self::$pagesByPath[$path];
            }

            $page = new self($path, $path);
            $page->error = COLLECTION_NOT_FOUND;

            return self::$pagesByPath[$path] = $page;
        }

        public function isError(): bool
        {
            return $this->error !== '';
        }

        public function getError(): string
        {
            return $this->error;
        }

        public function add($pageType, array $data, $pageTemplate): self
        {
            $page = new self(420, $data['cHandle'] ?? '');
            $page->handle = $data['cHandle'] ?? '';
            $page->pageType = $pageType;
            $page->pageTemplate = $pageTemplate;
            $page->data = $data;

            return $page;
        }

        public function update(array $data): void
        {
            foreach ($data as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    class PageType
    {
        public static array $types = [];
        public static array $updated = [];

        public static function getByHandle(string $handle): ?self
        {
            return self::$types[$handle] ?? null;
        }

        public static function add(array $data, $pkg): self
        {
            $type = new self();
            $type->handle = $data['handle'];
            $type->name = $data['name'];
            $type->defaultTemplate = $data['defaultTemplate'];
            $type->allowedTemplates = $data['allowedTemplates'];
            $type->templates = $data['templates'];
            $type->pkg = $pkg;
            self::$types[$data['handle']] = $type;

            return $type;
        }

        public function update(array $data): void
        {
            foreach ($data as $key => $value) {
                $this->$key = $value;
            }
            self::$updated[] = $this->handle;
        }

        public function setConfiguredPageTypePublishTargetObject($configuredTarget): void
        {
            $this->configuredTarget = $configuredTarget;
        }
    }

    class SinglePage
    {
        public static array $pages = [];

        public static function add(string $path, $pkg): Page
        {
            $page = new Page(500, $path);
            $page->handle = $path;
            self::$pages[$path] = $page;

            return $page;
        }
    }

    class PageTemplate
    {
        public static array $templates = [];

        public static function getByHandle(string $handle): ?self
        {
            return self::$templates[$handle] ?? null;
        }

        public static function add(string $handle, string $name, string $icon, $pkg): self
        {
            $template = new self();
            $template->handle = $handle;
            $template->name = $name;
            $template->icon = $icon;
            $template->pkg = $pkg;
            self::$templates[$handle] = $template;

            return $template;
        }
    }

    class Core
    {
        public static function make(string $class): object
        {
            return new $class();
        }
    }

    class ORM
    {
        public static function entityManager(): object
        {
            return new class {
                public function persist($entity): void {}
                public function flush(): void {}
            };
        }
    }
}

namespace ClassKit\Tests {
    use ClassKit\Package\Traits\AttributeTrait;
    use ClassKit\Package\Traits\BlockTrait;
    use ClassKit\Package\Traits\ExpressTrait;
    use ClassKit\Package\Traits\FileTrait;
    use ClassKit\Package\Traits\PageTrait;
    use ClassKit\Package\Traits\StorageTrait;
    use ClassKit\Package\Traits\ThemeTrait;
    use PHPUnit\Framework\TestCase;

    final class PackageTraitsTest extends TestCase
    {
        protected function setUp(): void
        {
            \Concrete\Core\Attribute\Type::$types = [];
            \Concrete\Core\Attribute\Set::$sets = [];
            \Concrete\Core\Attribute\Key\Category::$categories = [
                'collection' => new \Concrete\Core\Attribute\Key\Category(),
            ];
            \Concrete\Core\Attribute\Key\Category::$sets = [];
            \Concrete\Core\Attribute\Key\CollectionKey::$keys = [];
            \Concrete\Core\Block\BlockType\Set::$sets = [];
            \Concrete\Core\File\Set\Set::$sets = [];
            \Concrete\Core\Support\Facade\Express::$objects = [];
            \Concrete\Core\File\StorageLocation\Type\Type::$created = [];
            \Concrete\Core\File\StorageLocation\Type\Type::$current = null;
            \PageTheme::$themes = [];
            \PageType::$types = [];
            \PageType::$updated = [];
            \PageTemplate::$templates = [];
            \SinglePage::$pages = [];
            \Page::$pagesById = [];
            \Page::$pagesByPath = [];
        }

        public function testAttributeTraitAddAttributeTypeAndSet(): void
        {
            $subject = new class {
                use AttributeTrait;

                public function addType(string $handle, string $name, object $pkg): object
                {
                    return $this->addAttributeType($handle, $name, $pkg, ['collection']);
                }

                public function addSet(string $category, string $setHandle, string $setName, object $pkg): object
                {
                    return $this->addAttributeSet($category, $setHandle, $setName, $pkg);
                }
            };

            $pkg = new \Concrete\Core\Entity\Package();
            $type = $subject->addType('colour', 'Colour', $pkg);
            $set = $subject->addSet('collection', 'colours', 'Colours', $pkg);

            $this->assertSame('colour', $type->handle);
            $this->assertSame('Colours', $set->name);
            $this->assertCount(1, \Concrete\Core\Attribute\Key\Category::getByHandle('collection')->associated);
        }

        public function testAttributeTraitAddAttributeAndSelectAttribute(): void
        {
            $subject = new class {
                use AttributeTrait;

                public function addKey(string $handle, string $name, string $type, object $pkg): object
                {
                    return $this->addAttribute($handle, $name, $type, \Concrete\Core\Attribute\Key\CollectionKey::class, null, $pkg);
                }

                public function addSelect(string $handle, string $name, array $options, object $pkg): object
                {
                    return $this->addSelectAttribute($handle, $name, $options, \Concrete\Core\Attribute\Key\CollectionKey::class, null, $pkg, true, false, false);
                }
            };

            $pkg = new \Concrete\Core\Entity\Package();
            $key = $subject->addKey('department', 'Department', 'text', $pkg);
            $select = $subject->addSelect('status', 'Status', ['Open', 'Closed'], $pkg);

            $this->assertSame('department', $key->handle);
            $this->assertSame('status', $select->handle);
            $this->assertSame(['Open', 'Closed'], $select->getController()->options);
        }

        public function testBlockTraitAddsBlockTypesAndSets(): void
        {
            $subject = new class {
                use BlockTrait;

                public function makeBlockType(string $handle, object $pkg): object
                {
                    return $this->addBlockType($handle, $pkg);
                }

                public function makeSet(string $handle, string $name, object $pkg): object
                {
                    return $this->addBlockTypeSet($handle, $name, $pkg);
                }
            };

            $pkg = new \Concrete\Core\Entity\Package();
            $block = $subject->makeBlockType('hero', $pkg);
            $set = $subject->makeSet('homepage', 'Homepage', $pkg);

            $this->assertSame('hero', $block->handle ?? 'hero');
            $this->assertSame('Homepage', $set->name);
        }

        public function testExpressTraitCreatesAndSearchesObjects(): void
        {
            $subject = new class {
                use ExpressTrait;

                public function buildObject(string $handle, string $plural, string $name, object $pkg): object
                {
                    return $this->addExpressObject($handle, $plural, $name, $pkg);
                }

                public function makeForm(object $entity, array $fields): object
                {
                    return $this->createExpressAttributeForm($entity, $fields, 'Main');
                }

                public function getEntries(string $entityHandle, string $attributeHandle, string $value): array
                {
                    return $this->getExpressEntries($entityHandle, $attributeHandle, $value);
                }
            };

            $pkg = new \Concrete\Core\Entity\Package();
            $entity = $subject->buildObject('news_story', 'News stories', 'News Story', $pkg);
            $this->assertSame('news_story', $entity->handle);

            $form = $subject->makeForm($entity, ['Details' => ['title']]);
            $this->assertSame($entity, $form);

            $results = $subject->getEntries('news_story', 'title', 'example');
            $this->assertSame(['entry'], $results);
        }

        public function testFileTraitAddsFileSet(): void
        {
            $subject = new class {
                use FileTrait;

                public function add(string $name, string $type): object
                {
                    return $this->addFileSet($name, $type);
                }
            };

            $set = $subject->add('Uploads', 'public');

            $this->assertSame('Uploads', $set->name);
            $this->assertSame('TYPE_PUBLIC', $set->type);
        }

        public function testPageTraitAddsTemplatesAndSinglePages(): void
        {
            $subject = new class {
                use PageTrait;

                public function createType(string $handle, string $name, string $defaultTemplate, string $allowedTemplates, array $templates, object $pkg): object
                {
                    return $this->addPageType($handle, $name, $defaultTemplate, $allowedTemplates, $templates, $pkg);
                }

                public function createSinglePage(string $path, object $pkg, string $name, string $description): object
                {
                    return $this->addSinglePage($path, $pkg, $name, $description);
                }

                public function createTemplate(string $handle, string $name, object $pkg): object
                {
                    return $this->addPageTemplate($handle, $name, $pkg);
                }
            };

            $pkg = new \Concrete\Core\Entity\Package();
            $template = $subject->createTemplate('landing', 'Landing', $pkg);
            $type = $subject->createType('news', 'News', 'landing', 'A', ['landing'], $pkg);
            $page = $subject->createSinglePage('/about-us', $pkg, 'About us', 'About the company');

            $this->assertSame('landing', $template->handle);
            $this->assertSame('news', $type->handle);
            $this->assertSame('/about-us', $page->handle ?? '/about-us');
        }

        public function testThemeTraitAddsTheme(): void
        {
            $subject = new class {
                use ThemeTrait;

                public function add(string $handle, object $pkg): object
                {
                    return $this->addTheme($handle, $pkg);
                }
            };

            $pkg = new \Concrete\Core\Entity\Package();
            $theme = $subject->add('default', $pkg);

            $this->assertSame('default', $theme->handle);
        }

        public function testStorageTraitCreatesAndReusesStorageTypes(): void
        {
            $subject = new class {
                use StorageTrait;

                public function add(string $handle, string $name, object $pkg): object
                {
                    return $this->addStorageType($handle, $pkg, $name);
                }
            };

            $pkg = new \Concrete\Core\Entity\Package();
            $first = $subject->add('s3', 'S3', $pkg);
            $second = $subject->add('s3', 'S3', $pkg);

            $this->assertInstanceOf(\Concrete\Core\Entity\File\StorageLocation\Type\Type::class, $first);
            $this->assertSame($first, $second);
            $this->assertCount(1, \Concrete\Core\File\StorageLocation\Type\Type::$created);
        }
    }
}
