<?php

namespace Concrete\Package\ClassKit\Controller\SinglePage\Dashboard;

use Concrete\Core\Config\Repository\Liaison;
use Concrete\Core\Entity\Package;
use Concrete\Core\Http\Response;
use Concrete\Core\Package\PackageService;
use Concrete\Core\Page\Controller\DashboardPageController;
use Exception;

class SvelteComponents extends DashboardPageController
{
    protected Package $pkg;

    protected Liaison $config;

    public $helpers = [
        'form'
    ];

    public function on_start()
    {
        parent::on_start();

        $this->pkg = $this->app->make(PackageService::class)->getByHandle('class_kit');
        $this->config = $this->pkg->getFileConfig();
    }

    public function view()
    {
        $this->set('paths', $this->config->get('svelte_components.paths'));
    }

    public function add()
    {
        if (!$this->request->isPost()) {
            $this->error->add('Invalid request method');
            }

        if (!$this->token->validate('add_path')) {
            $this->error->add($this->token->getErrorMessage());
        }

        $vstrings = $this->app->make('helper/validation/strings');
        if (!$vstrings->notempty($this->request->get('path'))) {
            $this->error->add(t('Path is required'));
        }

        $fullPath = sprintf('%s/%s', ($this->request->get('pkgHandle')) ? REL_DIR_PACKAGES . '/' . trim($this->request->get('pkgHandle'), '/') : REL_DIR_APPLICATION, trim($this->request->get('path'), '/'));
        $currentPaths = $this->config->get('svelte_components.paths') ?? [];

        if (!file_exists(DIR_BASE . $fullPath)) {
            $this->error->add(t('Path %s does not exist', $fullPath));
        }

        if (!$this->error->has()) {
            if (!in_array($fullPath, $currentPaths)) {
                $currentPaths[] = $fullPath;
            }

            try {
                $this->generateJson($currentPaths);
                $this->config->save('svelte_components.paths', $currentPaths);
                return $this->buildRedirect('/dashboard/svelte_components');
            } catch (Exception $e) {
                $this->error->add($e->getMessage());
                $this->set('paths', $this->config->get('svelte_components.paths'));
            }
        }

        $this->set('pathValue', $this->request->get('path'));
        $this->set('pkgHandleValue', $this->request->get('pkgHandle'));
    }

    protected function generateJson(array $currentPaths): void
    {
        $encoded = json_encode($currentPaths, JSON_THROW_ON_ERROR);
        $filePath = DIR_BASE . '/svelte_components.json';

        if (file_put_contents($filePath, $encoded, LOCK_EX) === false) {
            throw new Exception('Unable to write file: ' . $filePath);
        }
    }
}
