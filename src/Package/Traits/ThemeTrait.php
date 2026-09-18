<?php

namespace ClassKit\Package\Traits;

use PageTheme;
use Concrete\Core\Entity\Package;

/**
 * Trait ThemeTrait.
 */
trait ThemeTrait
{
    /**
     * Add Theme
     *
     * @param string  $handle
     * @param Package $pkg
     *
     * @return object PageTheme
     */
    protected function addTheme(string $handle, Package $pkg): PageTheme
    {
        return PageTheme::getByHandle($handle) ? PageTheme::getByHandle($handle) : PageTheme::add($handle, $pkg);
    }
}
