<?php

namespace MagpieLib\TestBench\System\Adapters\Impls;

use Magpie\General\Sugars\Quote;
use Magpie\Logs\Concepts\Loggable;
use MagpieLib\TestBench\System\Adapters\Printers\Printable;
use PHPUnit\Event\Test\BeforeFirstTestMethodErrored as PhpUnitEventTestBeforeFirstTestMethodErrored;
use PHPUnit\Event\Test\ConsideredRisky as PhpUnitEventTestConsideredRisky;
use PHPUnit\Event\Test\DeprecationTriggered as PhpUnitEventTestDeprecationTriggered;
use PHPUnit\Event\Test\Errored as PhpUnitEventTestErrored;
use PHPUnit\Event\Test\Failed as PhpUnitEventTestFailed;
use PHPUnit\Event\Test\Finished as PhpUnitEventTestFinished;
use PHPUnit\Event\Test\MarkedIncomplete as PhpUnitEventTestMarkedIncomplete;
use PHPUnit\Event\Test\NoticeTriggered as PhpUnitEventTestNoticeTriggered;
use PHPUnit\Event\Test\Passed as PhpUnitEventTestPassed;
use PHPUnit\Event\Test\PhpDeprecationTriggered as PhpUnitEventTestPhpDeprecationTriggered;
use PHPUnit\Event\Test\PhpNoticeTriggered as PhpUnitEventTestPhpNoticeTriggered;
use PHPUnit\Event\Test\PhpunitWarningTriggered as PhpUnitEventTestPhpunitWarningTriggered;
use PHPUnit\Event\Test\PhpWarningTriggered as PhpUnitEventTestPhpWarningTriggered;
use PHPUnit\Event\Test\PreparationStarted as PhpUnitEventTestPreparationStarted;
use PHPUnit\Event\Test\PrintedUnexpectedOutput as PhpUnitEventTestPrintedUnexpectedOutput;
use PHPUnit\Event\Test\Skipped as PhpUnitEventTestSkipped;
use PHPUnit\Event\Test\WarningTriggered as PhpUnitEventTestWarningTriggered;
use PHPUnit\Event\TestRunner\Configured as PhpUnitEventTestRunnerConfigured;
use PHPUnit\Event\TestRunner\DeprecationTriggered as PhpUnitEventTestRunnerDeprecationTriggered;
use PHPUnit\Event\TestRunner\ExecutionFinished as PhpUnitEventTestRunnerExecutionFinished;
use PHPUnit\Event\TestRunner\ExecutionStarted as PhpUnitEventTestRunnerExecutionStarted;
use PHPUnit\Event\TestRunner\WarningTriggered as PhpUnitEventTestRunnerWarningTriggered;
use PHPUnit\Event\TestSuite\Finished as PhpUnitEventTestSuiteFinished;
use PHPUnit\Event\TestSuite\Started as PhpUnitEventTestSuiteStarted;

/**
 * Write test event to the logger
 * @internal
 */
class LoggerPrinter implements Printable
{
    /**
     * @var Loggable Target logger
     */
    protected Loggable $logger;


    /**
     * Constructor
     * @param Loggable $logger
     */
    public function __construct(Loggable $logger)
    {
        $this->logger = $logger;
    }


    /**
     * @inheritDoc
     */
    public function onTestRunnerConfigured(PhpUnitEventTestRunnerConfigured $event) : void
    {
        $this->logger->info(_l('Test runner configured'));
    }


    /**
     * @inheritDoc
     */
    public function onTestRunnerExecutionStarted(PhpUnitEventTestRunnerExecutionStarted $event) : void
    {
        $this->logger->info(_format_l(
            'Execution started',
            'Execution started, total {{0}} item(s)',
            $event->testSuite()->count(),
        ));
    }


    /**
     * @inheritDoc
     */
    public function onTestRunnerExecutionFinished(PhpUnitEventTestRunnerExecutionFinished $event) : void
    {
        $this->logger->info(_l('Execution finished'));
    }


    /**
     * @inheritDoc
     */
    public function onTestSuiteStarted(PhpUnitEventTestSuiteStarted $event) : void
    {
        $this->logger->info(_format_l(
            'Test suite started',
            'Test suite {{0}} started', Quote::single($event->testSuite()->name()),
        ));
    }


    /**
     * @inheritDoc
     */
    public function onTestSuiteFinished(PhpUnitEventTestSuiteFinished $event) : void
    {
        $this->logger->info(_format_l(
            'Test suite finished',
            'Test suite {{0}} finished', Quote::single($event->testSuite()->name()),
        ));
    }


    /**
     * @inheritDoc
     */
    public function onTestBeforeFirstTestMethodErrored(PhpUnitEventTestBeforeFirstTestMethodErrored $event) : void
    {

    }


    /**
     * @inheritDoc
     */
    public function onTestPrintedUnexpectedOutput(PhpUnitEventTestPrintedUnexpectedOutput $event) : void
    {

    }


    /**
     * @inheritDoc
     */
    public function onTestPreparationStarted(PhpUnitEventTestPreparationStarted $event) : void
    {
        $this->logger->info(_format_l(
            'Test preparation started',
            '[Test {{0}}] ### Test preparation started', Quote::single($event->test()->name()),
        ));
    }


    /**
     * @inheritDoc
     */
    public function onTestFinished(PhpUnitEventTestFinished $event) : void
    {
        $this->logger->info(_format_l(
            'Test finished',
            '[Test {{0}}] ### Test finished', Quote::single($event->test()->name()),
        ));
    }


    /**
     * @inheritDoc
     */
    public function onTestRunnerDeprecationTriggered(PhpUnitEventTestRunnerDeprecationTriggered $event) : void
    {
        $this->logger->warning(_format_l(
            '[Runner] Deprecation triggered',
            '[Runner] Deprecation triggered: {{0}}', $event->message(),
        ));
    }


    /**
     * @inheritDoc
     */
    public function onTestRunnerWarningTriggered(PhpUnitEventTestRunnerWarningTriggered $event) : void
    {
        $this->logger->warning(_format_l(
            '[Runner] Warning triggered',
            '[Runner] Warning triggered: {{0}}', $event->message(),
        ));
    }


    /**
     * @inheritDoc
     */
    public function onTestConsideredRisky(PhpUnitEventTestConsideredRisky $event) : void
    {
        $this->logger->warning(_format_l(
            'Test considered risky',
            '[Test {{0}}] Test considered risky', Quote::single($event->test()->name()),
        ));
    }


    /**
     * @inheritDoc
     */
    public function onTestDeprecationTriggered(PhpUnitEventTestDeprecationTriggered $event) : void
    {
        $this->logger->warning(_format_l(
            'Deprecation triggered',
            '[Test {{0}}] Deprecation triggered: {{1}}', Quote::single($event->test()->name()), $event->message(),
        ));
    }


    /**
     * @inheritDoc
     */
    public function onTestPhpDeprecationTriggered(PhpUnitEventTestPhpDeprecationTriggered $event) : void
    {
        $this->logger->warning(_format_l(
            'PHP deprecation triggered',
            '[Test {{0}}] PHP deprecation triggered: {{1}}', Quote::single($event->test()->name()), $event->message(),
        ));
    }


    /**
     * @inheritDoc
     */
    public function onTestPhpNoticeTriggered(PhpUnitEventTestPhpNoticeTriggered $event) : void
    {
        $this->logger->warning(_format_l(
            'PHP notice triggered',
            '[Test {{0}}] PHP notice triggered: {{1}}', Quote::single($event->test()->name()), $event->message(),
        ));
    }


    /**
     * @inheritDoc
     */
    public function onTestPhpWarningTriggered(PhpUnitEventTestPhpWarningTriggered $event) : void
    {
        $this->logger->warning(_format_l(
            'PHP warning triggered',
            '[Test {{0}}] PHP warning triggered: {{1}}', Quote::single($event->test()->name()), $event->message(),
        ));
    }


    /**
     * @inheritDoc
     */
    public function onTestPhpunitWarningTriggered(PhpUnitEventTestPhpunitWarningTriggered $event) : void
    {
        $this->logger->warning(_format_l(
            'phpunit warning triggered',
            '[Test {{0}}] phpunit warning triggered: {{1}}', Quote::single($event->test()->name()), $event->message(),
        ));
    }


    /**
     * @inheritDoc
     */
    public function onTestErrored(PhpUnitEventTestErrored $event) : void
    {
        $this->logger->warning(_format_l(
            'Test has error',
            '[Test {{0}}] Error: {{1}}', Quote::single($event->test()->name()), $event->throwable()->message(),
        ));
    }


    /**
     * @inheritDoc
     */
    public function onTestFailed(PhpUnitEventTestFailed $event) : void
    {
        $this->logger->warning(_format_l(
            'Test failed',
            '[Test {{0}}] Failed: {{1}}', Quote::single($event->test()->name()), $event->throwable()->message(),
        ));
    }


    /**
     * @inheritDoc
     */
    public function onTestMarkedIncomplete(PhpUnitEventTestMarkedIncomplete $event) : void
    {
        $this->logger->warning(_format_l(
            'Test marked incomplete',
            '[Test {{0}}] Test marked incomplete', Quote::single($event->test()->name()),
        ));
    }


    /**
     * @inheritDoc
     */
    public function onTestNoticeTriggered(PhpUnitEventTestNoticeTriggered $event) : void
    {
        $this->logger->warning(_format_l(
            'Test notice triggered',
            '[Test {{0}}] Notice triggered: {{1}}', Quote::single($event->test()->name()), $event->message(),
        ));
    }


    /**
     * @inheritDoc
     */
    public function onTestPassed(PhpUnitEventTestPassed $event) : void
    {
        $this->logger->info(_format_l(
            'Test passed',
            '[Test {{0}}] Test passed', Quote::single($event->test()->name()),
        ));
    }


    /**
     * @inheritDoc
     */
    public function onTestSkipped(PhpUnitEventTestSkipped $event) : void
    {
        $this->logger->warning(_format_l(
            'Test skipped',
            '[Test {{0}}] Test skipped', Quote::single($event->test()->name()),
        ));
    }


    /**
     * @inheritDoc
     */
    public function onTestWarningTriggered(PhpUnitEventTestWarningTriggered $event) : void
    {
        $this->logger->warning(_format_l(
            'Test warning triggered',
            '[Test {{0}}] Warning triggered: {{1}}', Quote::single($event->test()->name()), $event->message(),
        ));
    }
}