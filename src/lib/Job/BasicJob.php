<?php

declare(strict_types=1);

namespace SchedulerTesting\Job;

use TaskScheduler;
use SchedulerTesting\Bootstrap\ContainerBuilder;

class BasicJob extends TaskScheduler\AbstractJob
{
    /**
     * {@inheritdoc}
     */
    public function start(): bool
    {
        $composer = require 'vendor/autoload.php';
        $dic      = ContainerBuilder::get($composer);
        $logger   = $dic->get(\Psr\Log\LoggerInterface::class);

        $logger->debug($this->data, [
            'category' => get_class($this)
        ]);

        return true;
    }
}
