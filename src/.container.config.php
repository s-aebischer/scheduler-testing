<?php

use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Processor;
use MongoDB\Client;
use SchedulerTesting\Log\MongoDBFormatter as MongoDBFormatter;
use MongoDB\Database;
use TaskScheduler\WorkerManager;
use TaskScheduler\WorkerFactoryInterface;
use SchedulerTesting\Async\WorkerFactory;

return [
    Database::class => [
        'use' => '{MongoDB\Client}',
        'calls' => [[
            'select' => true,
            'method' => 'selectDatabase',
            'arguments' => [
                'databaseName' => 'scheduler'
            ]
        ]]
    ],
    WorkerManager::class => [
        'services' => [
            WorkerFactoryInterface::class => [
                'use' => WorkerFactory::class
            ]
        ]
    ],
    LoggerInterface::class => [
        'use' => Logger::class,
        'arguments' => [
            'name' => 'default',
            'processors' => [
                '{'.Processor\PsrLogMessageProcessor::class.'}',
            ]
        ],
        'calls' => [
            'mongodb' => [
                'method' => 'pushHandler',
                'arguments' => ['handler' => '{mongodb}']
            ],
            'stderr' => [
                'method' => 'pushHandler',
                'arguments' => ['handler' => '{stderr}']
            ],
            'stdout' => [
                'method' => 'pushHandler',
                'arguments' => ['handler' => '{stdout}']
            ],
        ],
        'services' => [
            Monolog\Formatter\FormatterInterface::class => [
                'use' => Monolog\Formatter\LineFormatter::class,
                'arguments' => [
                    'dateFormat' => 'Y-d-m H:i:s',
                    'format' => "%datetime% [%context.category%,%level_name%]: %message% %context.params% %context.exception%\n"
                ],
                'calls' => [
                    ['method' => 'includeStacktraces']
                ]
            ],
            'mongodb' => [
                'use' => Monolog\Handler\MongoDBHandler::class,
                'arguments' => [
                    'mongodb' => '{'.Client::class.'}',
                    'database' => 'taskscheduler',
                    'collection' => 'logs',
                    'level' => 1000,
                ],
                'calls' => [
                    'formatter' => [
                        'method' => 'setFormatter',
                        'arguments' => [
                            'formatter' => '{'.MongoDBFormatter::class.'}'
                        ]
                    ]
                ],
            ],
            'stderr' => [
                'use' => Monolog\Handler\StreamHandler::class,
                'arguments' => [
                    'stream' => 'php://stderr',
                    'level' => 600,
                ],
                'calls' => [
                    'formatter' => [
                        'method' => 'setFormatter'
                    ]
                ],
            ],
            'stdout' => [
                'use' => Monolog\Handler\FilterHandler::class,
                'arguments' => [
                    'handler' => '{output}',
                    'minLevelOrList' => 100,
                    'maxLevel' => 550
                ],
                'services' => [
                    'output' => [
                        'use' => Monolog\Handler\StreamHandler::class,
                        'arguments' => [
                            'stream' => 'php://stdout',
                            'level' => 100
                        ],
                        'calls' => [
                            'formatter' => [
                                'method' => 'setFormatter'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],
];
