<?php

namespace ClassKit\Environment;

use Core;
use Concrete\Core\Production\Modes;

/**
 * Class Environment.
 */
class Environment
{
    /**
     * Returns the current production mode.
     */
    protected static function mode(): ?string
    {
        return Core::make('config')->get('concrete.security.production.mode');
    }

    /**
     * isLocal
     *
     * @return bool
     */
    public static function isLocal(): bool
    {
        return static::mode() === Modes::MODE_DEVELOPMENT;
    }

    /**
     * isStaging
     *
     * @return bool
     */
    public static function isStaging(): bool
    {
        return static::mode() === Modes::MODE_STAGING;
    }

    /**
     * isProduction
     *
     * @return bool
     */
    public static function isProduction(): bool
    {
        return static::mode() === Modes::MODE_PRODUCTION;
    }
}
