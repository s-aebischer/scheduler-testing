<?php

declare(strict_types=1);

namespace SchedulerTesting\Async;

use MongoDB\BSON\UTCDateTime;
use MongoDB\Database;
use Monolog\Handler\MongoDBHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use TaskScheduler\AbstractJob;
use TaskScheduler\Scheduler;
//use Tubee\Collection\CollectionInterface;
//use Tubee\Endpoint\EndpointInterface;
//use Tubee\Helper;
//use Tubee\Log\MongoDBFormatter;
//use Tubee\ResourceNamespace\Factory as ResourceNamespaceFactory;
//use Tubee\ResourceNamespace\ResourceNamespaceInterface;
//use Zend\Mail\Message;

class Sync extends AbstractJob
{
    /**
     * Log levels.
     */
    public const LOG_LEVELS = [
        'debug' => Logger::DEBUG,
        'info' => Logger::INFO,
        'notice' => Logger::NOTICE,
        'warning' => Logger::WARNING,
        'error' => Logger::ERROR,
        'critical' => Logger::CRITICAL,
        'alert' => Logger::ALERT,
        'emergency' => Logger::EMERGENCY,
    ];

//    /**
//     * ResourceNamespace factory.
//     *
//     * @var ResourceNamespaceFactory
//     */
//    protected $namespace_factory;

    /**
     * Scheduler.
     *
     * @var Scheduler
     */
    protected $scheduler;

    /**
     * Logger.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Error count.
     *
     * @var int
     */
    protected $error_count = 0;

    /**
     * Start timestamp.
     *
     * @var UTCDateTime
     */
    protected $timestamp;

    /**
     * Database.
     *
     * @var Database
     */
    protected $db;

    /**
     * Process stack.
     *
     * @var array
     */
    protected $stack = [];

//    /**
//     * Resource namespace.
//     *
//     * @var ResourceNamespaceInterface
//     */
//    protected $namespace;

    /**
     * Sync.
     */
    public function __construct(Database $db, Scheduler $scheduler, LoggerInterface $logger)
    {
//        $this->namespace_factory = $namespace_factory;
        $this->scheduler = $scheduler;
        $this->logger = $logger;
        $this->db = $db;
    }

    /**
     * Start job.
     */
    public function start(): bool
    {
        $this->timestamp = new UTCDateTime();
//        $this->namespace = $this->namespace_factory->getOne($this->data['namespace']);

        foreach ($this->data['collections'] as $collections) {
            $collections = (array) $collections;
//            $filter = in_array('*', $collections) ? [] : ['name' => ['$in' => $collections]];
//            $collections = iterator_to_array($this->namespace->getCollections($filter));

            $endpoints = (array) $this->data['endpoints'];
            $this->loopCollections($collections, $endpoints);
        }

        return true;
    }

//    /**
//     * Get timestamp.
//     */
//    public function getTimestamp(): ?UTCDateTime
//    {
//        return $this->timestamp;
//    }

    /**
     * Loop collections.
     */
    protected function loopCollections(array $collections, array $endpoints)
    {
        foreach ($endpoints as $ep) {
            foreach ($collections as $collection) {
                $this->loopEndpoints($collection, $collections, (array) $ep, $endpoints);
            }

            $this->logger->debug('wait for child stack ['.count($this->stack).'] to be finished', [
                'category' => get_class($this),
            ]);

//            $i = 0;
//            foreach ($this->stack as $proc) {
//                ++$i;
//                $proc->wait();
//                $this->updateProgress($i / count($this->stack) * 100);
//
//                $record = $this->db->{$this->scheduler->getJobQueue()}->findOne([
//                    '_id' => $proc->getId(),
//                ]);
//
//                $this->error_count += $record['data']['error_count'] ?? 0;
//                $this->increaseErrorCount();
//            }
//
//            $this->stack = [];
        }

        $abc = 123;

    }

    /**
     * Update error count.
     */
    protected function increaseErrorCount(): self
    {
        $this->db->{$this->scheduler->getJobQueue()}->updateOne([
            '_id' => $this->getId(),
            'data.error_count' => ['$exists' => true],
        ], [
            '$set' => ['data.error_count' => $this->error_count],
        ]);

        return $this;
    }

    /**
     * Loop endpoints.
     */
    protected function loopEndpoints($collection, array $all_collections, array $endpoints, array $all_endpoints)
    {
//        $filter = in_array('*', $endpoints) ? [] : ['name' => ['$in' => $endpoints]];
//        $endpoints = iterator_to_array($collection->getEndpoints($filter));
//        $endpoints = ['ActiveDirectory', 'Office365'];

        foreach ($endpoints as $endpoint) {
            if (count($all_endpoints) > 1 || count($all_collections) > 1) {
                $data = (array)$this->data;

                $data = array_merge($data, [
                    'collections' => [$collection],
                    'endpoints' => [$endpoint],
                    'parent' => $this->getId(),
                ]);

                $data['notification'] = ['enabled' => false, 'receiver' => []];
                $this->stack[] = $this->scheduler->addJob(self::class, $data);
            } else {
                $this->execute($collection, $endpoint);
                $this->increaseErrorCount();
            }
        }

        $test = 123;
    }

    /**
     * Execute.
     */
    protected function execute($collection, $endpoint)
    {
/*        $this->setupLogger(self::LOG_LEVELS[$this->data['log_level']], [
            'process' => (string) $this->getId(),
            'parent' => isset($this->data['parent']) ? (string) $this->data['parent'] : null,
            'start' => $this->timestamp,
            'namespace' => 'abs',
            'collection' => $collection,
            'endpoint' => $endpoint,
        ]);*/

        $this->logger->debug('run single job for collection: '.$collection.' and endpoint '.$endpoint, [
            'category' => get_class($this)
        ]);

//        if ($endpoint->getType() === EndpointInterface::TYPE_SOURCE) {
//            $this->import($collection, $this->getFilter(), ['name' => $endpoint->getName()], $this->data['simulate'], $this->data['ignore']);
//        } elseif ($endpoint->getType() === EndpointInterface::TYPE_DESTINATION) {
//            $this->export($collection, $this->getFilter(), ['name' => $endpoint], $this->data['simulate'], $this->data['ignore']);
//        } else {
//            $this->logger->warning('skip endpoint ['.$endpoint->getIdentifier().'], endpoint type is neither source nor destination', [
//                'category' => get_class($this),
//            ]);
//        }

        $this->logger->popProcessor();
    }

    /**
     * Decode filter.
     */
    protected function getFilter(): array
    {
        if ($this->data['filter'] === null) {
            return [];
        }

        return (array) Helper::jsonDecode($this->data['filter']);
    }

//    /**
//     * Set logger level.
//     */
//    protected function setupLogger(int $level, array $context): bool
//    {
//        if (isset($this->data['job'])) {
//            $context['job'] = (string) $this->data['job'];
//        }
//
//        foreach ($this->logger->getHandlers() as $handler) {
//            if ($handler instanceof MongoDBHandler) {
//                $handler->setLevel($level);
//                $handler->setFormatter(new MongoDBFormatter());
//            }
//        }
//
//        while (count($this->logger->getProcessors()) > 1) {
//            $this->logger->popProcessor();
//        }
//
//        $this->logger->pushProcessor(function ($record) use ($context) {
//            $record['context'] = array_merge($record['context'], $context);
//
//            return $record;
//        });
//
//        return true;
//    }

    /**
     * {@inheritdoc}
     */
    protected function export(CollectionInterface $collection, array $filter = [], array $endpoints = [], bool $simulate = false, bool $ignore = false): bool
    {
        $this->logger->info('start export to destination endpoints from data type ['.$collection.']', [
            'category' => get_class($this),
        ]);

        $endpoints = iterator_to_array($collection->getDestinationEndpoints($endpoints));
        $workflows = [];

//        foreach ($endpoints as $ep) {
//            if ($ep->flushRequired()) {
//                $ep->flush($simulate);
//            }

//            $ep->setup($simulate);
//        }

        $total = $collection->countObjects($filter);
        $i = 0;

        foreach ($collection->getObjects($filter) as $id => $object) {
            $this->updateProgress(($total === 0) ? 0 : $i / $total * 100);
            ++$i;
            $this->logger->debug('process ['.$i.'] export for object ['.(string) $id.'] - [{fields}] from data type ['.$collection->getIdentifier().']', [
                'category' => get_class($this),
                'fields' => array_keys($object->toArray()),
            ]);

            foreach ($endpoints as $ep) {
                $identifier = $ep->getIdentifier();
                $this->logger->info('start export to destination endpoint ['.$identifier.']', [
                    'category' => get_class($this),
                ]);

                if (!isset($workflows[$identifier])) {
                    $workflows[$identifier] = iterator_to_array($ep->getWorkflows(['kind' => 'Workflow']));

                    if (count($workflows[$identifier]) === 0) {
                        $this->logger->warning('no workflows available in destination endpoint ['.$ep->getIdentifier().'], skip export', [
                            'category' => get_class($this),
                        ]);

                        continue;
                    }
                }

                try {
                    foreach ($workflows[$identifier] as $workflow) {
                        $this->logger->debug('start workflow ['.$workflow->getIdentifier().'] [ensure='.$workflow->getEnsure().'] for the current object', [
                            'category' => get_class($this),
                        ]);

                        if ($workflow->export($object, $this, $simulate) === true) {
                            $this->logger->debug('workflow ['.$workflow->getIdentifier().'] executed for the object ['.(string) $id.'], skip any further workflows for the current data object', [
                                'category' => get_class($this),
                            ]);

                            continue 2;
                        }
                        $this->logger->debug('skip workflow ['.$workflow->getIdentifier().'] for object ['.(string) $id.'], condition does not match or unusable ensure', [
                                'category' => get_class($this),
                            ]);
                    }

                    $this->logger->debug('no workflow were executed within endpoint ['.$identifier.'] for the current object', [
                        'category' => get_class($this),
                    ]);
                } catch (\Throwable $e) {
                    ++$this->error_count;

                    $this->logger->error('failed export object to destination endpoint ['.$identifier.']', [
                        'category' => get_class($this),
                        'object' => $object->getId(),
                        'exception' => $e,
                    ]);

                    if ($ignore === false) {
                        return false;
                    }
                }
            }
        }

        if (count($endpoints) === 0) {
            $this->logger->warning('no destination endpoint available for collection ['.$collection->getIdentifier().'], skip export', [
                'category' => get_class($this),
            ]);

            return true;
        }

        foreach ($endpoints as $n => $ep) {
            $ep->shutdown($simulate);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function import(CollectionInterface $collection, array $filter = [], array $endpoints = [], bool $simulate = false, bool $ignore = false): bool
    {
        $this->logger->info('start import from source endpoints into data type ['.$collection->getIdentifier().']', [
            'category' => get_class($this),
        ]);

        $endpoints = $collection->getSourceEndpoints($endpoints);
        $workflows = [];

        foreach ($endpoints as $ep) {
            $identifier = $ep->getIdentifier();
            $this->logger->info('start import from source endpoint ['.$identifier.']', [
                'category' => get_class($this),
            ]);

            if ($ep->flushRequired()) {
                $collection->flush($simulate);
            }

            $ep->setup($simulate);
            if (!isset($workflows[$identifier])) {
                $workflows[$identifier] = iterator_to_array($ep->getWorkflows(['kind' => 'Workflow']));

                if (count($workflows[$identifier]) === 0) {
                    $this->logger->warning('no workflows available in source endpoint ['.$ep->getIdentifier().'], skip import', [
                        'category' => get_class($this),
                    ]);

                    continue;
                }
            }

            $i = 0;
            $total = $ep->count($filter);

            foreach ($ep->getAll($filter) as $id => $object) {
                $this->updateProgress(($total === 0) ? 0 : $i / $total * 100);
                ++$i;
                $this->logger->debug('process object ['.$i.'] import for object ['.$object->getId().'] into data type ['.$collection->getIdentifier().']', [
                    'category' => get_class($this),
                    'attributes' => $object,
                ]);

                try {
                    foreach ($workflows[$identifier] as $workflow) {
                        $this->logger->debug('start workflow ['.$workflow->getIdentifier().'] [ensure='.$workflow->getEnsure().'] for the current object', [
                            'category' => get_class($this),
                        ]);

                        if ($workflow->import($collection, $object, $this, $simulate) === true) {
                            $this->logger->debug('workflow ['.$workflow->getIdentifier().'] executed for the object ['.(string) $object->getId().'], skip any further workflows for the current data object', [
                                'category' => get_class($this),
                            ]);

                            continue 2;
                        }
                        $this->logger->debug('skip workflow ['.$workflow->getIdentifier().'] for object ['.(string) $object->getId().'], condition does not match or unusable ensure', [
                                'category' => get_class($this),
                            ]);
                    }

                    $this->logger->debug('no workflow were executed within endpoint ['.$identifier.'] for the current object', [
                        'category' => get_class($this),
                    ]);
                } catch (\Throwable $e) {
                    ++$this->error_count;

                    $this->logger->error('failed import data object from source endpoint ['.$identifier.']', [
                        'category' => get_class($this),
                        'namespace' => $collection->getResourceNamespace()->getName(),
                        'collection' => $collection->getName(),
                        'endpoint' => $ep->getName(),
                        'exception' => $e,
                    ]);

                    if ($ignore === false) {
                        return false;
                    }
                }
            }

            if (empty($filter)) {
                $this->garbageCollector($collection, $ep, $simulate, $ignore);
            } else {
                $this->logger->info('skip garbage collection, a query has been issued for import', [
                    'category' => get_class($this),
                ]);
            }

            $ep->shutdown($simulate);
        }

        if ($endpoints->getReturn() === 0) {
            $this->logger->warning('no source endpoint available for collection ['.$collection->getIdentifier().'], skip import', [
                'category' => get_class($this),
            ]);

            return true;
        }

        return true;
    }

    /**
     * Garbage.
     */
    protected function garbageCollector(CollectionInterface $collection, EndpointInterface $endpoint, bool $simulate = false, bool $ignore = false): bool
    {
        $this->logger->info('start garbage collector workflows from data type [{identifier}] for data objects older than [{timestamp}] (last_sync)', [
            'timestamp' => $this->timestamp,
            'identifier' => $collection->getIdentifier(),
            'category' => get_class($this),
        ]);

        $workflows = iterator_to_array($endpoint->getWorkflows(['kind' => 'GarbageWorkflow']));
        if (count($workflows) === 0) {
            $this->logger->info('no garbage workflows available in ['.$endpoint->getIdentifier().'], skip garbage collection', [
                'category' => get_class($this),
            ]);

            return false;
        }

        $relationObject = false;
        foreach ($workflows as $workflow) {
            foreach ($workflow->getAttributeMap()->getMap() as $attr) {
                if ($attr['name'] === 'relationObject' || (isset($attr['map']) && $attr['map']['ensure'] === 'absent')) {
                    $relationObject = true;
                }
            }
        }

        if ($relationObject) {
            $this->logger->info('run garbage workflows for relations', [
                'category' => get_class($this),
            ]);

            return $this->relationGarbageCollector($collection, $endpoint, $workflows, $simulate);
        }

        $this->logger->info('run garbage workflows for DataObjects', [
            'category' => get_class($this),
        ]);

        return $this->objectGarbageCollector($endpoint, $collection, $workflows, $simulate, $ignore);
    }

    /**
     * Object garbage collector.
     */
    protected function objectGarbageCollector(EndpointInterface $endpoint, CollectionInterface $collection, $workflows, $simulate, $ignore): bool
    {
        $filter = [
            'endpoints.'.$endpoint->getName().'.last_sync' => [
                '$lt' => $this->timestamp,
            ],
        ];

        $this->db->{$collection->getCollection()}->updateMany($filter, ['$set' => [
            'endpoints.'.$endpoint->getName().'.garbage' => true,
        ]]);

        $i = 0;
        foreach ($collection->getObjects($filter, false) as $id => $object) {
            ++$i;
            $this->logger->debug('process ['.$i.'] garbage workflows for garbage object ['.$id.'] from data type ['.$collection->getIdentifier().']', [
                'category' => get_class($this),
            ]);

            try {
                foreach ($workflows as $workflow) {
                    $this->logger->debug('start workflow ['.$workflow->getIdentifier().'] for the current garbage object', [
                        'category' => get_class($this),
                    ]);

                    if ($workflow->cleanup($object, $this, $simulate) === true) {
                        $this->logger->debug('workflow ['.$workflow->getIdentifier().'] executed for the current garbage object, skip any further workflows for the current garbage object', [
                            'category' => get_class($this),
                        ]);

                        break;
                    }
                }
            } catch (\Exception $e) {
                $this->logger->error('failed execute garbage collector for object ['.$id.'] from collection ['.$collection->getIdentifier().']', [
                    'category' => get_class($this),
                    'exception' => $e,
                ]);

                if ($ignore === false) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Relation garbage collector.
     */
    protected function relationGarbageCollector(CollectionInterface $collection, EndpointInterface $endpoint, $workflows, $simulate): bool
    {
        $namespace = $endpoint->getCollection()->getResourceNamespace();
        $collection = $endpoint->getCollection();
        $key = join('/', [$namespace->getName(), $collection->getName(), $endpoint->getName()]);

        $this->logger->info('mark all relation data objects older than [{timestamp}] (last_sync) as garbage', [
            'class' => get_class($this),
            'timestamp' => $this->timestamp,
        ]);

        $filter = [
            'endpoints.'.$key.'.last_sync' => [
                '$lt' => $this->timestamp,
            ],
        ];

        $this->db->relations->updateMany($filter, ['$set' => [
            'endpoints.'.$key.'.garbage' => true,
        ]]);

        $garbageRelations = $this->db->relations->find(['endpoints.'.$key.'.garbage' => true])->toArray();

        $i = 0;
        foreach ($garbageRelations as $relation) {
            ++$i;
            $this->logger->debug('process ['.$i.'] garbage workflows for garbage relation ['.$relation['name'].'] from data type ['.$collection->getIdentifier().']', [
                'category' => get_class($this),
            ]);

            try {
                foreach ($workflows as $workflow) {
                    $this->logger->debug('start workflow ['.$workflow->getIdentifier().'] for the current garbage relation', [
                        'category' => get_class($this),
                    ]);

                    if ($workflow->relationCleanup($this->db->{'relations'}, $relation, $this, $namespace, $endpoint, $workflow, $simulate) === true) {
                        $this->logger->debug('workflow ['.$workflow->getIdentifier().'] executed for the current garbage object, skip any further workflows for the current garbage object', [
                            'category' => get_class($this),
                        ]);

                        break;
                    }
                }
            } catch (\Exception $e) {
                $this->logger->error('failed execute garbage collector for relation ['.$relation['name'].'] from collection ['.$collection->getIdentifier().']', [
                    'category' => get_class($this),
                    'exception' => $e,
                ]);
            }
        }

        return true;
    }
}
