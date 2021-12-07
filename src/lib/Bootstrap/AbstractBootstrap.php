<?php

declare(strict_types=1);

namespace SchedulerTesting\Bootstrap;

use ErrorException;
use Psr\Log\LoggerInterface;

abstract class AbstractBootstrap
{
    /**
     * Logger.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Inject object.
     *
     * @return AbstractBootstrap
     */
    public function inject($object): self
    {
        return $this;
    }

    /**
     * Set error handler.
     *
     * @return AbstractBootstrap
     */
    protected function setErrorHandler(): self
    {
        set_error_handler(function ($severity, $message, $file, $line) {
            $log = $message.' in '.$file.':'.$line;

            switch ($severity) {
                case E_ERROR:
                case E_USER_ERROR:
                    $this->logger->error($log, [
                        'category' => get_class($this),
                    ]);

                    break;
                case E_WARNING:
                case E_USER_WARNING:
                    $this->logger->warning($log, [
                        'category' => get_class($this),
                    ]);

                    break;
                default:
                    $this->logger->debug($log, [
                        'category' => get_class($this),
                    ]);

                    break;
            }

            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        return $this;
    }
}
