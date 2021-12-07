#!/usr/bin/env php
<?php

require "vendor/autoload.php";

define('SCHEDULERTESTING_PATH', realpath(__DIR__.DIRECTORY_SEPARATOR.'..'));
define('SCHEDULERTESTING_CONFIG_DIR', constant('SCHEDULERTESTING_PATH').DIRECTORY_SEPARATOR.'config');

$composer = require 'vendor/autoload.php';
$dic      = SchedulerTesting\Bootstrap\ContainerBuilder::get($composer);
$logger   = $dic->get(\Psr\Log\LoggerInterface::class);

$shortopts = "c:";

$longopts  = array(
    "controller:"
);
$options = getopt($shortopts, $longopts);


if (isset($options['c'])) {
    switch ($options['c']) {
        case 'addBasicJob':
            $logger->debug('add new basic job', [
                'category' => 'cli.php'
            ]);
            $dic->get(SchedulerTesting\Job\JobHandling::class)->addBasicJob();
            break;
        case 'addExtendedJob':
            $logger->debug('add new extended job', [
                'category' => 'cli.php'
            ]);
            $dic->get(SchedulerTesting\Job\JobHandling::class)->addExtendedJob();
            break;
        case 'runScheduler':
            $logger->debug('start scheduler', [
                'category' => 'cli.php'
            ]);
            $dic->get(SchedulerTesting\Bootstrap\Cli::class)->process();
            break;
        case 'flushJobs':
            $logger->debug('flush all scheduler jobs', [
                'category' => 'cli.php'
            ]);
            $dic->get(SchedulerTesting\Job\JobHandling::class)->flushJobs();
            break;
        default:
            $logger->error('undefined controller set', [
                'category' => 'cli.php'
            ]);
            break;
    }
} else {
    $logger->error('no controller set', [
        'category' => 'cli.php'
    ]);
}

?>
