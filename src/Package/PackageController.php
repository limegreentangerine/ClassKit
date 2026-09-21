<?php

namespace ClassKit\Package;

use Log;
use Core;
use Events;
use Request;
use Database;
use Concrete\Core\Package\Package;
use Concrete\Core\Package\PackageService;
use Concrete\Core\Application\Application;
use ClassKit\Package\Events\PackageInstallEvent;
use Concrete\Core\Entity\Package as PackageEntity;
use Concrete\Core\Command\Task\Manager as TaskManager;

/**
 * Class PackageController.
 */
abstract class PackageController extends Package implements PackageInterface
{
    /**
     * The packages handle.
     * Note that this must be unique in the
     * entire concrete5 package ecosystem.
     *
     * @var string
     */
    protected $pkgHandle;

    /**
     * The packages version.
     *
     * @var string
     */
    protected $pkgVersion;

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
     */
    protected $phpVersionRequired = '8.4';

    /**
     * Package service providers to register.
     *
     * eg. '\PackageHandle\Providers\PackageServiceProvider::class'
     *
     * @var array
     */
    protected $providers = [];

    /**
     * An array describing the package dependencies.
     * Keys are package handles.
     * Values may be:
     * - false: this package can't be installed if the other package is already installed.
     * - true: this package can't be installed of the other package is not installed
     * - a string: this package can't be installed of the other package is not installed or it's installed with an older version
     * - an array with two strings, representing the minimum and the maximum version of the other package to be installed.
     *
     * @var array
     *
     * @example [
     *     // This package can't be installed if a package with handle other_package_1 is already installed.
     *     'other_package_1' => false,
     *     // This package can't be installed if a package with handle other_package_2 is not installed.
     *     'other_package_2' => true,
     *     // This package can't be installed if a package with handle other_package_3 is not installed, or it has a version prior to 1.0
     *     'other_package_3' => '1.0',
     *     // This package can't be installed if a package with handle other_package_4 is not installed, or it has a version prior to 2.0, or it has a version after 2.9
     *     'other_package_4' => ['2.0', '2.9'],
     * ]
     */
    protected $packageDependencies = [];

    /**
     * Package class autoloader registrations
     * The package install helper class, included with this boilerplate,
     * is activated by default.
     *
     * eg. [ 'src' => '\ClassKit' ]
     *
     * @see https://goo.gl/4wyRtH
     * @var array
     */
    protected $pkgAutoloaderRegistries = [];

    /**
     * Package tasks to register.
     *
     * eg. 'task_handle' => \PackageHandle\Command\Task\Controller\TaskHandleController::class,
     *
     * @var array
     */
    protected $tasks = [];

    /**
     * Package classes to override core concrete classes
     *
     * eg. \Concrete\Core\SomeClass::class => \PackageHandle\SomeClass:class
     *
     * @var array
     */
    protected $aliases = [];

    /**
     * Concrete Interface overrides to be copied to /application
     *
     * eg. ['blocks/image/view.php']
     *
     * @var array
     */
    protected $applicationOverrides = [];

    /**
     * Does the package provide a full content swap?
     * This feature is often used in theme packages to install 'sample' content on the site.
     *
     * @see https://goo.gl/C4m6BG
     * @var bool
     */
    protected $pkgAllowsFullContentSwap = false;

    /**
     * Does the package provide thumbnails of the files
     * imported via the full content swap above?
     *
     * @see https://goo.gl/C4m6BG
     * @var bool
     */
    protected $pkgContentProvidesFileThumbnails = false;

    /**
     * Should we remove 'Src' from classes that are contained
     * ithin the packages 'src/Concrete' directory automatically?
     *
     * '\Concrete\Package\MyPackage\Src\MyNamespace' becomes '\Concrete\Package\MyPackage\MyNamespace'
     *
     * @see https://goo.gl/4wyRtH
     * @var bool
     */
    protected $pkgAutoloaderMapCoreExtensions = false;

    /**
     * Database tables
     *
     * @var array
     */
    protected $databaseTables = [];

    /**
     * Flag to detect if installing or updating, can be useful to not run features on installation
     *
     * @var bool
     */
    protected $installingOrUpdating = false;

    /**
     * Register Package Tasks
     */
    private function registerTasks(): void
    {
        $manager = Core::make(TaskManager::class);

        foreach ($this->tasks as $handle => $class) {
            $manager->extend($handle, function () use ($class) {
                return Core::make($class);
            });
        }
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

    /**
     * Register the packages defined service providers.
     */
    protected function registerServiceProviders(): void
    {
        $app = Application::getInstance();
        foreach ($this->providers as $class) {
            (new $class($app))->register();
        }
    }

    /**
     * Install Application Overrides
     *
     * Moves any application overrides into their required place in the /application folder
     *
     * @param bool $overwrite
     */
    protected function installApplicationOverrides(bool $overwrite = false): void
    {
        foreach ($this->applicationOverrides as $path) {
            $source = sprintf('%s/%s/overrides/%s', DIR_PACKAGES, $this->pkgHandle, $path);
            $destination = sprintf('%s/%s', DIR_APPLICATION, $path);

            if (!file_exists($source)) {
                throw new \RuntimeException(sprintf(
                    'Application override not found: %s',
                    $source,
                ));
            }

            if (!$overwrite && file_exists($destination)) {
                continue;
            }

            if (!is_dir(dirname($destination))) {
                mkdir(dirname($destination), 0755, true);
            }

            if (!copy($source, $destination)) {
                throw new \RuntimeException(sprintf(
                    'Unable to install application override: %s',
                    $destination,
                ));
            }
        }
    }

    /**
     * Gets packages current working directory
     *
     * @param  PackageEntity $pkg
     * @return string
     */
    protected function getPackageCwd(PackageEntity $pkg)
    {
        return DIR_BASE . $pkg->getRelativePath();
    }

    /**
     * Package on_start function
     */
    public function on_start()
    {
        $this->registerTasks();
        $this->registerRoutes();
        $this->registerEvents();
        $this->registerServiceProviders();
        $this->registerPackageEvent();
    }

    /**
     * The packages install routine.
     */
    public function install()
    {
        $this->installingOrUpdating = true;
        $pkg = parent::install();
        $this->installOrUpgrade($pkg);

        $event = new PackageInstallEvent();
        $event->setPackage($pkg);
        $event->setInstallType(__FUNCTION__);
        Events::dispatch('package_installation_complete', $event);
    }

    /**
     * The packages upgrade routine.
     */
    public function upgrade()
    {
        $this->installingOrUpdating = true;
        $pkg = Core::make(PackageService::class)->getByHandle($this->pkgHandle);
        parent::upgrade();
        $this->installOrUpgrade($pkg);

        $event = new PackageInstallEvent();
        $event->setPackage($pkg);
        $event->setInstallType(__FUNCTION__);
        Events::dispatch('package_installation_event', $event);
    }

    /**
     * The packages uninstall routine.
     */
    public function uninstall()
    {
        $r = Request::getInstance();
        $pkg = Core::make(PackageService::class)->getByHandle($this->pkgHandle);

        parent::uninstall();

        if ($r->request->get('purge_database_tables') && $r->request->get('purge_database_tables') == '1' && count($this->databaseTables) > 0) {
            $db = Database::get();
            $db->Execute('SET FOREIGN_KEY_CHECKS = 0');
            $db->Execute('SET UNIQUE_CHECKS = 0');

            foreach ($this->databaseTables as $table) {
                $db->Execute("TRUNCATE TABLE `{$table}`");
                $db->Execute("DROP TABLE `{$table}`");
            }

            $db->Execute('SET FOREIGN_KEY_CHECKS = 1');
            $db->Execute('SET UNIQUE_CHECKS = 1');
        }

        $event = new PackageInstallEvent();
        $event->setPackage($pkg);
        $event->setInstallType(__FUNCTION__);
        Events::dispatch('package_installation_event', $event);
    }

    /**
     * Executes registerPackageEvent.
     */
    public function registerPackageEvent()
    {
        Events::addListener('package_installation_event', function (PackageInstallEvent $event) {
            $this->installingOrUpdating = false;
            $pkg = $event->getPackage();
            $type = $event->getInstallType();
            Log::addInfo(sprintf('Package Event: %s - %s', $pkg->getPackageName(), $type));
        });
    }
}
