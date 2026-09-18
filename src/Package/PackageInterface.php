<?php

namespace ClassKit\Package;

use Concrete\Core\Entity\Package;

/**
 * Interface PackageInterface.
 */
interface PackageInterface
{
    /**
     * Register Routes
     */
    public function registerRoutes(): void;

    /**
     * Register Events
     */
    public function registerEvents(): void;

    /**
     * Install or Upgrade
     *
     * @var Package $pkg
     */
    public function installOrUpgrade(Package $pkg);
}
