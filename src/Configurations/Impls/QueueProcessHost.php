<?php

namespace MagpieLib\TestBench\Configurations\Impls;

use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\StreamException;
use Magpie\General\Sugars\Quote;
use Magpie\System\Kernel\EasyFiberPromise;
use Magpie\System\Process\Process;
use MagpieLib\TestBench\Configurations\TestEnvironmentContext;
use MagpieLib\TestBench\System\Adapters\Impls\PhpUnitConfig;

/**
 * Host of queue process
 * @internal
 */
class QueueProcessHost
{
    /**
     * @var EasyFiberPromise|null Associated queue process
     */
    protected ?EasyFiberPromise $queueProcess;
    /**
     * @var int|null Process PID running the queue, if any
     */
    protected readonly ?int $pid;


    /**
     * Constructor
     * @param EasyFiberPromise $queueProcess
     * @param int|null $pid
     */
    protected function __construct(EasyFiberPromise $queueProcess, ?int $pid)
    {
        $this->queueProcess = $queueProcess;
        $this->pid = $pid;
    }


    /**
     * Release current process host
     * @param TestEnvironmentContext $context
     * @return EasyFiberPromise|null
     */
    public function release(TestEnvironmentContext $context) : ?EasyFiberPromise
    {
        if ($this->queueProcess === null) return null;

        $captureQueueProcess = $this->queueProcess;
        $this->queueProcess = null;

        return EasyFiberPromise::create(function (TestEnvironmentContext $context) use ($captureQueueProcess) {
            $processReturn = $captureQueueProcess->wait();

            $context->getLogger()->info(_format_l(
                'Queue listener process exit',
                'Queue listener process {{0}} exit with code: {{1}}', static::createPidTag($this->pid), $processReturn,
            ));
        }, $context);
    }


    /**
     * Create a queue process host and run it
     * @param TestEnvironmentContext $context
     * @param QueueListenerRunner $runner
     * @return static
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     */
    public static function createAndRun(TestEnvironmentContext $context, QueueListenerRunner $runner) : static
    {
        $host = PhpUnitConfig::createHostedQueueRun($runner);

        $runningProcess = $host->createProcess();
        $pid = null;

        $queueProcess = EasyFiberPromise::create(function (TestEnvironmentContext $context, Process $runningProcess) use (&$pid) : int {
            $processAsync = $runningProcess->runAsync();
            $pid = $runningProcess->getPid();

            $context->getLogger()->info(
                _format_l('Started queue listener in separated process',
                    'Started queue listener in separated process {{0}}',
                    static::createPidTag($pid)));

            foreach ($processAsync->getAnyOutputs() as $output) {
                $context->getLogger()->debug($output->content);
            }

            return $processAsync->wait();
        }, $context, $runningProcess);

        return new static($queueProcess, $pid);
    }


    /**
     * Create a PID tag
     * @param int|null $pid
     * @return string
     */
    protected static function createPidTag(?int $pid) : string
    {
        if ($pid === null) return '';
        return Quote::bracket('#' . $pid);
    }
}