<?php

declare(strict_types=1);

namespace SchedulerTesting\Bootstrap;

use Composer\Autoload\ClassLoader as Composer;
use Micro\Container\Container;
use Noodlehaus\Config;
use Psr\Log\LoggerInterface;
use ErrorException;

class ContainerBuilder
{
    /**
     * Init bootstrap.
     */
    public static function get(Composer $composer)
    {
        $config = self::loadConfig();
        $container = new Container($config);
        self::setErrorHandler($container->get(LoggerInterface::class));

        return $container;
    }

    /**
     * Load config.
     */
    protected static function loadConfig(): Config
    {
        $configs = [constant('SCHEDULERTESTING_PATH').DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'.container.config.php'];
        foreach (glob(constant('SCHEDULERTESTING_CONFIG_DIR').DIRECTORY_SEPARATOR.'*.yaml') as $path) {
            clearstatcache(true, $path);
            $configs[] = $path;
        }

        return new Config($configs);
    }

    /**
     * Set error handler.
     */
    protected static function setErrorHandler(LoggerInterface $logger): void
    {
        set_error_handler(function ($severity, $message, $file, $line) use ($logger) {
            $log = $message.' in '.$file.':'.$line;

            switch ($severity) {
                case E_ERROR:
                case E_USER_ERROR:
                    $logger->error($log, [
                        'category' => self::class,
                    ]);

                    break;
                case E_WARNING:
                case E_USER_WARNING:
                    $logger->warning($log, [
                        'category' => self::class,
                    ]);

                    break;
                default:
                    $logger->debug($log, [
                        'category' => self::class,
                    ]);

                    break;
            }

            throw new ErrorException($message, 0, $severity, $file, $line);
        });
    }
}
