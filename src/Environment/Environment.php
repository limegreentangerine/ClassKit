<?php

namespace ClassKit\Environment;

use Core;
use Concrete\Core\Production\Modes;
use Concrete\Core\Site\Config\Liaison;

/**
 * Class Environment.
 */
class Environment
{
    protected Liaison $config;

    /**
     * Executes __construct.
     */
    public function __construct()
    {
        $this->config = Core::make('config');
    }

    /**
     * isLocal
     *
     * @return bool
     */
    public static function isLocal(): bool
    {
        return self::$config->get('concrete.security.production.mode') === Modes::MODE_DEVELOPMENT;
    }

    /**
     * isStaging
     *
     * @return bool
     */
    public static function isStaging(): bool
    {
        return self::$config->get('concrete.security.production.mode') === Modes::MODE_STAGING;
    }

    /**
     * isProduction
     *
     * @return bool
     */
    public static function isProduction(): bool
    {
        return self::$config->get('concrete.security.production.mode') === Modes::MODE_PRODUCTION;
    }
}
