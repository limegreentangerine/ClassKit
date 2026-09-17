<?php

namespace LgtToolkit\Page\Theme;

use Events;
use Concrete\Core\Package\Package;
use Concrete\Core\Page\Theme\Theme as CoreTheme;
use Symfony\Component\EventDispatcher\GenericEvent;

class Theme extends CoreTheme
{
    /**
     * Install a theme given its handle.
     *
     * @param string                                                            $pThemeHandle the handle of the theme to be installed.
     * @param \Concrete\Core\Entity\Package|\Concrete\Core\Package\Package|null $pkg
     *
     * @return \Concrete\Core\Page\Theme\Theme|null returns NULL if the directory containing the theme could not be found
     * @throws \Exception                           in case of errors.
     */
    public static function add($pThemeHandle, $pkg = null)
    {
        if (is_object($pkg)) {
            if (is_dir(DIR_PACKAGES . '/' . $pkg->getPackageHandle())) {
                $dir = DIR_PACKAGES . '/' . $pkg->getPackageHandle() . '/' . DIRNAME_THEMES . '/' . $pThemeHandle;
            } else {
                $dir = DIR_PACKAGES_CORE . '/' . $pkg->getPackageHandle() . '/' . DIRNAME_THEMES . '/' . $pThemeHandle;
            }
            $pkgID = $pkg->getPackageID();
        } else {
            if (is_dir(DIR_FILES_THEMES . '/' . $pThemeHandle)) {
                $dir = DIR_FILES_THEMES . '/' . $pThemeHandle;
                $pkgID = 0;
            } else {
                $dir = DIR_FILES_THEMES_CORE . '/' . $pThemeHandle;
                $pkgID = 0;
            }
        }
        $l = static::install($dir, $pThemeHandle, $pkgID);

        self::buildImageMap($pThemeHandle, $pkg);

        return $l;
    }

    public static function buildImageMap(string $themeHandle, Package $pkg)
    {
        $themeInstallEvent = new GenericEvent();
        $themeInstallEvent->setArgument('theme_handle', $themeHandle);
        $themeInstallEvent->setArgument('pkg_handle', $pkg->getPackageHandle());
        Events::dispatch('on_theme_install', $themeInstallEvent);
    }
}
