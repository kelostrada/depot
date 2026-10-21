<?php

namespace App\Bootstrap;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\HandleExceptions as BaseHandleExceptions;

/**
 * Laravel 6's HandleExceptions bootstrapper sets error_reporting(-1) and turns
 * every PHP error into an ErrorException. Laravel 6 and the packages locked
 * with it predate PHP 8.1, so on PHP 8.3 they raise E_DEPRECATED notices
 * (ArrayAccess/Countable/IteratorAggregate methods without return types, null
 * passed to non-nullable internal parameters, dynamic properties, ...). As
 * exceptions those would abort requests and artisan commands.
 *
 * Deprecations are not errors: this bootstrapper keeps Laravel's handling of
 * every other error level and ignores E_DEPRECATED / E_USER_DEPRECATED. It is
 * swapped in through bootstrappers() in App\Http\Kernel and App\Console\Kernel.
 */
class HandleExceptions extends BaseHandleExceptions
{
    const IGNORED_LEVELS = E_DEPRECATED | E_USER_DEPRECATED;

    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        parent::bootstrap($app);

        // parent::bootstrap() calls error_reporting(-1); keep deprecations out
        // so PHP's own error logging skips them too.
        error_reporting(E_ALL & ~self::IGNORED_LEVELS);
    }

    /**
     * Convert PHP errors to ErrorException instances, except deprecations.
     *
     * @param  int  $level
     * @param  string  $message
     * @param  string  $file
     * @param  int  $line
     * @param  array  $context
     * @return void
     *
     * @throws \ErrorException
     */
    public function handleError($level, $message, $file = '', $line = 0, $context = [])
    {
        if ($level & self::IGNORED_LEVELS) {
            return;
        }

        parent::handleError($level, $message, $file, $line, $context);
    }

    /**
     * Replace Laravel's HandleExceptions with this class in a kernel's
     * bootstrapper list.
     *
     * @param  string[]  $bootstrappers
     * @return string[]
     */
    public static function replaceIn(array $bootstrappers)
    {
        return array_map(function ($bootstrapper) {
            return $bootstrapper === BaseHandleExceptions::class ? static::class : $bootstrapper;
        }, $bootstrappers);
    }
}
