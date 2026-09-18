<?php

namespace ClassKit\Package\Events;

use Core;
use Concrete\Core\Entity\Package;
use Concrete\Core\Package\PackageService;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class PackageInstallEvent.
 */
class PackageInstallEvent extends GenericEvent
{
    /**
     * Executes getPackage.
     */
    public function getPackage(): ?Package
    {
        $handle = $this->getArgument('package');
        $pkg = Core::make(PackageService::class)->getByHandle($handle);
        return $pkg ?? null;
    }

    /**
     * Executes setPackage.
     */
    public function setPackage(Package $pkg): void
    {
        $this->setArgument('package', $pkg->getPackageHandle());
    }

    /**
     * Executes getInstallType.
     */
    public function getInstallType(): string
    {
        return $this->getArgument('package');
    }

    /**
     * Executes setInstallType.
     */
    public function setInstallType(string $type): void
    {
        $this->setArgument('type', $type);
    }
}
