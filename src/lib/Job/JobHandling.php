<?php

declare(strict_types=1);

namespace SchedulerTesting\Job;

use Composer\Autoload\ClassLoader as Composer;
use MongoDB\Database;
use Psr\Log\LoggerInterface;
use SchedulerTesting\Async\Sync;
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
        $this->scheduler = new Scheduler($this->dic->get(Database::class), $this->dic->get(LoggerInterface::class));
    }

    public function addBasicJob()
    {
        $this->scheduler->addJob(BasicJob::class, 'dies ist ein Test');
    }

    public function addExtendedJob()
    {
        $data = [
            'collections' => [
                'accounts'
            ],
            'endpoints' => [
                'offers',
                'relations'
            ],
            'simulate' => false,
            'ignore' => false,
            'log_level' => 'debug'
        ];

        $this->scheduler->addJob(Sync::class, $data);
    }

    public function flushJobs()
    {
        $this->scheduler->flush();
    }
}
