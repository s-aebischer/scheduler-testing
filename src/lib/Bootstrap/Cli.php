<?php

declare(strict_types=1);

namespace SchedulerTesting\Bootstrap;

use Composer\Autoload\ClassLoader as Composer;
use Psr\Log\LoggerInterface;
use SchedulerTesting\Async\WorkerFactory;
use TaskScheduler\Queue;
use MongoDB\Database;
use TaskScheduler\Scheduler;

class Cli extends AbstractBootstrap
{
    /**
     * Composer.
     *
     * @var Composer
     */
    protected $composer;

    /**
     * Cli.
     */
    public function __construct(LoggerInterface $logger, Composer $composer)
    {
        $this->logger = $logger;
        $this->composer = $composer;
    }

    /**
     * Process.
     */
    public function process(): Cli
    {
        $this->logger->debug('queue node gets startet',
            ['category' => get_class($this)]
        );

        $dic = ContainerBuilder::get($this->composer);
        $queue = new Queue($dic->get(Scheduler::class), $dic->get(Database::class), $dic->get(WorkerFactory::class), $dic->get(LoggerInterface::class));

        $queue->process();

        return $this;
    }
}
