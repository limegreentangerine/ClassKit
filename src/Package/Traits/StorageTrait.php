<?php

namespace ClassKit\Package\Traits;

use Concrete\Core\Entity\Package;
use Concrete\Core\File\StorageLocation\Type\Type;
use Concrete\Core\Entity\File\StorageLocation\Type\Type as TypeEntity;

trait StorageTrait
{
    /**
     * Add Remote Storage
     * @param  string $handle
     * @param  Package $pkg
     * @param  string $name
     *
     * @return TypeEntity StorageType
     */
    protected function addStorageType(string $handle, Package $pkg, string $name): TypeEntity
    {
        $st = Type::getByHandle($handle);
        if (!is_object($st)) {
            Type::add($handle, $name, $pkg);
        }

        return $st;
    }
}
