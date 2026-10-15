<?php

namespace Concrete\Package\ClassKit\Controller\SinglePages\Dashboard;

use Concrete\Core\Config\Repository\Liaison;
use Concrete\Core\Entity\Package;
use Concrete\Core\Package\PackageServiceProvider;
use Concrete\Core\Page\Controller\DashboardPageController;

class SvelteComponents extends DashboardPageController
{
    protected Package $pkg;

    protected Liaison $config;

    public function on_start()
    {
        parent::on_start();

        $this->pkg = $this->app->make(PackageServiceProvider::class)->getByHandle('class_kit');
        $this->config = $this->pkg->getFileConfig();
    }

    public function view()
    {
        $this->set('paths', $this->config->get('svelte_components.paths'));
    }
}
