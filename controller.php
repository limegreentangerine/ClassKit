<?php

namespace Concrete\Package\ClassKit;

use Core;
use Package;
use Concrete\Core\Entity\Package as PackageEntity;

class Controller extends Package
{
    /**
     * The packages handle.
     * Note that this must be unique in the
     * entire concrete5 package ecosystem.
     *
     * @var string
     */
    protected $pkgHandle = 'class_kit';

    /**
     * The packages version.
     *
     * @var string
     */
    protected $pkgVersion = '1.0.0';

    /**
     * The minimum Concrete version compatible with the package.
     * Override this value according to the minimum required version for your package.
     *
     * @var string
     */
    protected $appVersionRequired = '9.5.0';

    /**
     * The minimum PHP version compatible with the package.
     * Override this value according to the minimum required version for your package.
     *
     * @var string
     * @var string
     */
    protected $phpVersionRequired = '8.4';

    /**
     * Package classes to override core concrete classes
     *
     * eg. \Concrete\Core\SomeClass::class => \PackageHandle\SomeClass:class
     *
     * @var array
     */
    protected $aliases = [
        'GlobalArea' => \ClassKit\Area\GlobalArea::class,
        'PageList' => \ClassKit\Page\PageList::class,
        'Theme' => \ClassKit\Page\Theme\Theme::class,
    ];

    /**
     * Package class autoloader registrations
     * The package install helper class, included with this boilerplate,
     * is activated by default.
     *
     * @see https://goo.gl/4wyRtH
     * @var array
     */
    protected $pkgAutoloaderRegistries = [
        'src' => '\ClassKit',
    ];

    protected function installOrUpgrade(PackageEntity $pkg): void
    {
        $config = Core::make('config');
        $this->registerAliases($config);
    }

    /**
     * Register Aliases
     *
     * @var mixed $config
     */
    protected function registerAliases(mixed $config): void
    {
        $aliases = $config->get('app.aliases');
        if ($aliases !== null) {
            foreach ($this->aliases as $key => $value) {
                $aliases[$key] = $value;
            }
        }

        $config->save('app.aliases', $aliases);
    }

    public function getPackageName()
    {
        return t('Class Kit');
    }

    public function getPackageDescription()
    {
        return t('Collection of helper classes for LGT packages.');
    }

    /**
     * The packages install routine.
     */
    public function install()
    {
        $pkg = parent::install();
        $this->installOrUpgrade($pkg);
    }

    /**
     * The packages upgrade routine.
     */
    public function upgrade()
    {
        $pkg = Core::make('Concrete\Core\Package\PackageService')->getByHandle($this->pkgHandle);
        parent::upgrade();
        $this->installOrUpgrade($pkg);
    }
}
