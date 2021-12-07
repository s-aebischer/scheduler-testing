<?php

declare(strict_types=1);

namespace SchedulerTesting\Job;

use Composer\Autoload\ClassLoader as Composer;
use MongoDB\Database;
use Psr\Log\LoggerInterface;
use SchedulerTesting\Bootstrap\ContainerBuilder;
use TaskScheduler\Scheduler;

class JobHandling
{
    /**
     * {@inheritdoc}
     */
    public function __construct(Composer $composer)
    {
        $this->dic = ContainerBuilder::get($composer);
    }

    public function addJob()
    {
        $scheduler = new Scheduler($this->dic->get(Database::class), $this->dic->get(LoggerInterface::class));

        $scheduler->addJob(JobLogger::class, 'dies ist ein Test');
    }

    public function flushJobs()
    {
        $scheduler = new Scheduler($this->dic->get(Database::class), $this->dic->get(LoggerInterface::class));

        $scheduler->flush();
    }
}
