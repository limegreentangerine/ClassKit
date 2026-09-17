<?php

namespace ClassKit\Package\Events;

use Core;
use Concrete\Core\Entity\Package;
use Concrete\Core\Package\PackageService;
use Symfony\Component\EventDispatcher\GenericEvent;

class PackageInstallEvent extends GenericEvent
{
    public function getPackage(): ?Package
    {
        $handle = $this->getArgument('package');
        $pkg = Core::make(PackageService::class)->getByHandle($handle);
        return $pkg ?? null;
    }

    public function setPackage(Package $pkg): void
    {
        $this->setArgument('package', $pkg->getPackageHandle());
    }

    public function getInstallType(): string
    {
        return $this->getArgument('package');
    }

    public function setInstallType(string $type): void
    {
        $this->setArgument('type', $type);
    }
}
