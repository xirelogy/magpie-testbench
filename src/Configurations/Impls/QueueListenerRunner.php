<?php

namespace MagpieLib\TestBench\Configurations\Impls;

use Magpie\Events\ClosureEventTemporaryReceiver;
use Magpie\Events\Concepts\Eventable;
use Magpie\Events\EventDelivery;
use Magpie\Facades\Log;
use Magpie\General\DateTimes\Duration;
use Magpie\General\DateTimes\SystemTimezone;
use Magpie\Queues\Commands\Features\QueueWorkerFeature;
use Magpie\Queues\Events\QueuedItemCompletedEvent;
use Magpie\Queues\Events\QueuedItemEvent;
use Magpie\Queues\Events\QueuedItemExceptionEvent;
use Magpie\Queues\Events\QueuedItemFailedEvent;
use Magpie\Queues\Events\QueuedItemRunningEvent;
use Magpie\Queues\Events\WorkerRestartingEvent;
use Magpie\Queues\Events\WorkerStartedEvent;
use MagpieLib\TestBench\System\Adapters\Queues\BaseTestQueueRunnable;

/**
 * Host a queue listener in a separated process (for test environment)
 * @internal
 */
final class QueueListenerRunner extends BaseTestQueueRunnable
{
    /**
     * @var string|null Name of the queue
     */
    public readonly ?string $queueName;
    /**
     * @var Duration Queue timeout
     */
    protected readonly Duration $timeout;


    /**
     * Constructor
     * @param string|null $queueName
     * @param Duration $timeout
     */
    public function __construct(?string $queueName, Duration $timeout)
    {
        parent::__construct();

        $this->queueName = $queueName;
        $this->timeout = $timeout;
    }


    /**
     * @inheritDoc
     */
    protected function createDefaultLoggerFilename(int|string $ident) : string
    {
        return empty($this->queueName) ? "queue.$ident.log" : "queue=" . $this->queueName . ".$ident.log";
    }


    /**
     * @inheritDoc
     */
    protected function onRunInTest() : void
    {
        EventDelivery::subscribe(WorkerStartedEvent::class, ClosureEventTemporaryReceiver::create(function (Eventable $event) {
            if (!$event instanceof WorkerStartedEvent) return;
            Log::info('Queue started: ' . $event->startedAt->setTimezone(SystemTimezone::default())->format('Y-m-d H:i:s'));
        }));

        EventDelivery::subscribe(WorkerRestartingEvent::class, ClosureEventTemporaryReceiver::create(function (Eventable $event) {
            if (!$event instanceof WorkerRestartingEvent) return;
            Log::info('Queue restart signal received');
        }));

        /*$receiver = ClosureEventTemporaryReceiver::create(function (Eventable $event) {
            Log::info('Received event: ' . $event::class);
        });//*/

        $queueReceiver = ClosureEventTemporaryReceiver::create(function (Eventable $event) : void {
            if (!$event instanceof QueuedItemEvent) return;

            $displayName = $event->getEventState()->getDisplayName();
            $showExceptionFn = function() use($event) {
                $ex = $event->getEventState()->getLastException();
                if ($ex === null) return;
                Log::error($ex->getMessage());
                Log::warning($ex->getTraceAsString());
            };

            switch ($event::class) {
                case QueuedItemRunningEvent::class:
                    Log::info(_format_safe(_l('{{0}} running...'), $displayName) ?? _l('Job running...'));
                    break;
                case QueuedItemCompletedEvent::class:
                    Log::info(_format_safe(_l('{{0}} completed'), $displayName) ?? _l('Job completed'));
                    break;
                case QueuedItemExceptionEvent::class:
                    Log::error(_format_safe(_l('{{0}} crashed with exception'), $displayName) ?? _l('Job crashed with exception'));
                    $showExceptionFn();
                    break;
                case QueuedItemFailedEvent::class:
                    Log::error(_format_safe(_l('{{0}} failed'), $displayName) ?? _l('Job failed'));
                    $showExceptionFn();
                    break;
                default:
                    break;
            }
        });

        QueueWorkerFeature::run($this->queueName, false, $this->timeout, $queueReceiver);
    }
}