<?php

if (!function_exists('mb_strimwidth')) {
    /**
     * Polyfill for mb_strimwidth when php-mbstring extension is not loaded in PHP.
     */
    function mb_strimwidth(string $string, int $start, int $width, string $trimmarker = '', ?string $encoding = null): string
    {
        $encoding = $encoding ?? 'UTF-8';
        $len = function_exists('mb_strlen') ? mb_strlen($string, $encoding) : strlen($string);
        if ($len <= $width) {
            return $string;
        }

        $markerLen = function_exists('mb_strlen') ? mb_strlen($trimmarker, $encoding) : strlen($trimmarker);
        $cutWidth = max(0, $width - $markerLen);

        if (function_exists('mb_substr')) {
            $sub = mb_substr($string, $start, $cutWidth, $encoding);
        } else {
            $sub = substr($string, $start, $cutWidth);
        }
        return $sub . $trimmarker;
    }
}

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;
