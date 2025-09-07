<?php

namespace MagpieLib\TestBench\Configurations\Prepares;

use Magpie\General\DateTimes\Duration;
use Magpie\Queues\Providers\QueueCreator;
use Magpie\System\Kernel\EasyFiber;
use Magpie\System\Kernel\EasyFiberPromise;
use Magpie\System\Process\Process;
use MagpieLib\TestBench\Configurations\Concepts\TestEnvironmentPreparable;
use MagpieLib\TestBench\Configurations\Concepts\TestEnvironmentReleasable;
use MagpieLib\TestBench\Configurations\Impls\QueueListenerRunner;
use MagpieLib\TestBench\Configurations\TestEnvironmentContext;
use MagpieLib\TestBench\System\Adapters\Impls\PhpUnitConfig;

/**
 * Listen and process queue event in a separate process in preparation for test
 */
class QueueListenerHosted implements TestEnvironmentPreparable, TestEnvironmentReleasable
{
    /**
     * Default queue dequeue timeout (in seconds)
     */
    public const DEFAULT_TIMEOUT_SEC = 1;

    /**
     * @var QueueListenerRunner Target runner
     */
    protected readonly QueueListenerRunner $runner;
    /**
     * @var EasyFiberPromise|null The queue process
     */
    protected ?EasyFiberPromise $queueProcess;


    /**
     * Constructor
     * @param string|null $queueName
     * @param Duration $timeout
     */
    protected function __construct(?string $queueName, Duration $timeout)
    {
        $this->runner = new QueueListenerRunner($queueName, $timeout);
    }


    /**
     * @inheritDoc
     */
    public function prepare(TestEnvironmentContext $context) : void
    {
        QueueCreator::instance()->initialize($context->getLogger());

        $host = PhpUnitConfig::createHostedQueueRun($this->runner);

        $runningProcess = $host->createProcess();

        $this->queueProcess = EasyFiberPromise::create(function (TestEnvironmentContext $context, Process $runningProcess) : int {
            $processAsync = $runningProcess->runAsync();
            $context->getLogger()->info(_l('Started queue listener in separated process'));

            foreach ($processAsync->getAnyOutputs() as $output) {
                $context->getLogger()->debug($output->content);
            }

            return $processAsync->wait();
        }, $context, $runningProcess);
    }


    /**
     * @inheritDoc
     */
    public function release(TestEnvironmentContext $context) : void
    {
        if ($this->queueProcess === null) return;

        EasyFiberPromise::createNonBlocking(function (TestEnvironmentContext $context) {
            while (true) {
                $context->getLogger()->warning(_l('Sending restart/terminate signal to queue...'));

                QueueCreator::instance()
                    ->getQueue($this->runner->queueName)
                    ->signalWorkerRestart();

                EasyFiber::sleep(1);
            }
        }, $context);

        EasyFiberPromise::create(function (TestEnvironmentContext $context) {
            $processReturn = $this->queueProcess->wait();

            $context->getLogger()->info(_format_l(
                'Queue listener process exit',
                'Queue listener process exit with code: {{0}}', $processReturn,
            ));
        }, $context);
    }


    /**
     * Create an instance
     * @param string|null $queueName
     * @param Duration|null $timeout
     * @return static
     */
    public static function create(?string $queueName = null, ?Duration $timeout = null) : static
    {
        return new static($queueName, $timeout ?? Duration::inSeconds(static::DEFAULT_TIMEOUT_SEC));
    }
}