<?php

namespace MagpieLib\TestBench\Configurations\Prepares;

use Magpie\General\DateTimes\Duration;
use Magpie\General\Sugars\Excepts;
use Magpie\Queues\Providers\QueueCreator;
use Magpie\System\Kernel\EasyFiberPromise;
use MagpieLib\TestBench\Configurations\Concepts\TestEnvironmentPreparable;
use MagpieLib\TestBench\Configurations\Concepts\TestEnvironmentReleasable;
use MagpieLib\TestBench\Configurations\Impls\QueueListenerRunner;
use MagpieLib\TestBench\Configurations\Impls\QueueProcessHost;
use MagpieLib\TestBench\Configurations\TestEnvironmentContext;

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
     * @var string|null Queue name
     */
    protected readonly ?string $queueName;
    /**
     * @var Duration Queue timeout
     */
    protected Duration $timeout;
    /**
     * @var int Total processes to be created
     */
    protected readonly int $totalProcesses;
    /**
     * @var array<QueueProcessHost> All queue process hosts
     */
    protected array $queueProcessHosts;


    /**
     * Constructor
     * @param string|null $queueName
     * @param Duration $timeout
     * @param int $totalProcesses
     */
    protected function __construct(?string $queueName, Duration $timeout, int $totalProcesses)
    {
        $this->queueName = $queueName;
        $this->timeout = $timeout;
        $this->queueProcessHosts = [];
        $this->totalProcesses = $totalProcesses;
    }


    /**
     * @inheritDoc
     */
    public function prepare(TestEnvironmentContext $context) : void
    {
        QueueCreator::instance()->initialize($context->getLogger());

        for ($i = 0; $i < $this->totalProcesses; ++$i) {
            $runner = new QueueListenerRunner($this->queueName, $this->timeout);
            $this->queueProcessHosts[] = QueueProcessHost::createAndRun($context, $runner);
        }
    }


    /**
     * @inheritDoc
     */
    public function release(TestEnvironmentContext $context) : void
    {
        if (count($this->queueProcessHosts) <= 0) return;

        EasyFiberPromise::createNonBlocking(function (TestEnvironmentContext $context) {
            $context->getLogger()->warning(_l('Sending restart/terminate signal to queue(s)...'));

            QueueCreator::instance()
                ->getQueue($this->queueName)
                ->signalWorkerRestart();
        }, $context);

        // Get promises
        $promises = [];
        foreach ($this->queueProcessHosts as $queueProcessHost) {
            $promise = $queueProcessHost->release($context);
            if ($promise !== null) $promises[] = $promise;
        }

        // And wait
        foreach ($promises as $promise) {
            Excepts::noThrow(function () use ($promise) {
                $promise->wait();
            });
        }
    }


    /**
     * Create an instance
     * @param string|null $queueName
     * @param Duration|null $timeout
     * @param int $totalProcesses
     * @return static
     */
    public static function create(?string $queueName = null, ?Duration $timeout = null, int $totalProcesses = 1) : static
    {
        return new static($queueName, $timeout ?? Duration::inSeconds(static::DEFAULT_TIMEOUT_SEC), $totalProcesses);
    }
}