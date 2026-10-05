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
     * @var Package|null
     */
    protected $package;

    /**
     * Executes getPackage.
     */
    public function getPackage(): ?Package
    {
        // On uninstall the DB row is already removed, so prefer the entity captured in setPackage().
        if ($this->package !== null) {
            return $this->package;
        }

        $handle = $this->getArgument('package');
        $pkg = Core::make(PackageService::class)->getByHandle($handle);
        return $pkg ?? null;
    }

    /**
     * Executes setPackage.
     */
    public function setPackage(Package $pkg): void
    {
        $this->package = $pkg;
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
