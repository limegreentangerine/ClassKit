<?php

namespace Concrete\Core\Entity\File\StorageLocation\Type {
    class Type {}
}

namespace Concrete\Core\Entity {
    class Package {}
}

namespace Concrete\Core\File\StorageLocation\Type {
    class Type
    {
        public static ?object $existing = null;

        public static array $created = [];

        public static function getByHandle(string $handle): ?object
        {
            return self::$existing;
        }

        public static function add(string $handle, string $name, $pkg = false): object
        {
            self::$created[] = [
                'handle' => $handle,
                'name' => $name,
                'pkg' => $pkg,
            ];

            return new \Concrete\Core\Entity\File\StorageLocation\Type\Type();
        }
    }
}

namespace ClassKit\Tests {

    use ClassKit\Package\Traits\StorageTrait;
    use PHPUnit\Framework\TestCase;

    final class StorageTraitTest extends TestCase
    {
        public function testAddStorageTypeReturnsExistingTypeWhenAlreadyPresent(): void
        {
            $existing = new \Concrete\Core\Entity\File\StorageLocation\Type\Type();
            \Concrete\Core\File\StorageLocation\Type\Type::$existing = $existing;
            \Concrete\Core\File\StorageLocation\Type\Type::$created = [];

            $subject = new class {
                use StorageTrait;

                public function addStorage(string $handle, string $name): object
                {
                    return $this->addStorageType($handle, new \Concrete\Core\Entity\Package(), $name);
                }
            };

            $result = $subject->addStorage('cloud', 'Cloud storage');

            $this->assertSame($existing, $result);
            $this->assertSame([], \Concrete\Core\File\StorageLocation\Type\Type::$created);
        }

        public function testAddStorageTypeCreatesTypeWhenMissing(): void
        {
            \Concrete\Core\File\StorageLocation\Type\Type::$existing = null;
            \Concrete\Core\File\StorageLocation\Type\Type::$created = [];

            $subject = new class {
                use StorageTrait;

                public function addStorage(string $handle, string $name): object
                {
                    return $this->addStorageType($handle, new \Concrete\Core\Entity\Package(), $name);
                }
            };

            $result = $subject->addStorage('s3', 'S3 storage');

            $this->assertInstanceOf(\Concrete\Core\Entity\File\StorageLocation\Type\Type::class, $result);
            $this->assertCount(1, \Concrete\Core\File\StorageLocation\Type\Type::$created);
            $this->assertSame('s3', \Concrete\Core\File\StorageLocation\Type\Type::$created[0]['handle']);
            $this->assertSame('S3 storage', \Concrete\Core\File\StorageLocation\Type\Type::$created[0]['name']);
            $this->assertInstanceOf(\Concrete\Core\Entity\Package::class, \Concrete\Core\File\StorageLocation\Type\Type::$created[0]['pkg']);
        }
    }
}
