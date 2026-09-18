<?php

namespace ClassKit\Package\Traits;

use ClassKit\File\ImportFileTrait;
use Concrete\Core\File\Set\Set as FileSet;

/**
 * Trait FileTrait.
 */
trait FileTrait
{
    use ImportFileTrait;

    /**
     * Get or create a FileSet by name and type
     *
     * @param  string  $fsName
     * @param  string  $fsType
     * @return FileSet
     */
    protected function addFileSet(string $fsName, string $fsType): FileSet
    {
        $fs = FileSet::getByName($fsName);
        if (!is_object($fs)) {
            $type = match (strtolower($fsType)) {
                'public' => 'TYPE_PUBLIC',
                'starred' => 'TYPE_STARRED',
                default => 'TYPE_PRIVATE',
            };
            $fs = FileSet::createAndGetSet($fsName, $type);
        }
        return $fs;
    }
}
