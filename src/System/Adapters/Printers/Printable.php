<?php

namespace MagpieLib\TestBench\System\Adapters\Printers;

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
 * May process events from PHPUnit
 */
interface Printable
{
    /**
     * When a test runner is configured
     * @param PhpUnitEventTestRunnerConfigured $event
     * @return void
     */
    public function onTestRunnerConfigured(PhpUnitEventTestRunnerConfigured $event) : void;


    /**
     * When test runner execution started
     * @param PhpUnitEventTestRunnerExecutionStarted $event
     * @return void
     */
    public function onTestRunnerExecutionStarted(PhpUnitEventTestRunnerExecutionStarted $event) : void;


    /**
     * When test runner execution finished
     * @param PhpUnitEventTestRunnerExecutionFinished $event
     * @return void
     */
    public function onTestRunnerExecutionFinished(PhpUnitEventTestRunnerExecutionFinished $event) : void;


    /**
     * When test suite started
     * @param PhpUnitEventTestSuiteStarted $event
     * @return void
     */
    public function onTestSuiteStarted(PhpUnitEventTestSuiteStarted $event) : void;


    /**
     * When test suite finished
     * @param PhpUnitEventTestSuiteFinished $event
     * @return void
     */
    public function onTestSuiteFinished(PhpUnitEventTestSuiteFinished $event) : void;


    /**
     * Right before first test method error
     * @param PhpUnitEventTestBeforeFirstTestMethodErrored $event
     * @return void
     */
    public function onTestBeforeFirstTestMethodErrored(PhpUnitEventTestBeforeFirstTestMethodErrored $event) : void;


    /**
     * When test printed unexpected output
     * @param PhpUnitEventTestPrintedUnexpectedOutput $event
     * @return void
     */
    public function onTestPrintedUnexpectedOutput(PhpUnitEventTestPrintedUnexpectedOutput $event) : void;


    /**
     * When test preparation started
     * @param PhpUnitEventTestPreparationStarted $event
     * @return void
     */
    public function onTestPreparationStarted(PhpUnitEventTestPreparationStarted $event) : void;


    /**
     * When test finished
     * @param PhpUnitEventTestFinished $event
     * @return void
     */
    public function onTestFinished(PhpUnitEventTestFinished $event) : void;


    /**
     * Test runner issue: deprecation triggered
     * @param PhpUnitEventTestRunnerDeprecationTriggered $event
     * @return void
     */
    public function onTestRunnerDeprecationTriggered(PhpUnitEventTestRunnerDeprecationTriggered $event) : void;


    /**
     * Test runner issue: warning triggered
     * @param PhpUnitEventTestRunnerWarningTriggered $event
     * @return void
     */
    public function onTestRunnerWarningTriggered(PhpUnitEventTestRunnerWarningTriggered $event) : void;


    /**
     * Test issue: consider risky
     * @param PhpUnitEventTestConsideredRisky $event
     * @return void
     */
    public function onTestConsideredRisky(PhpUnitEventTestConsideredRisky $event) : void;


    /**
     * Test issue: deprecation triggered
     * @param PhpUnitEventTestDeprecationTriggered $event
     * @return void
     */
    public function onTestDeprecationTriggered(PhpUnitEventTestDeprecationTriggered $event) : void;


    /**
     * Test issue: PHP deprecation triggered
     * @param PhpUnitEventTestPhpDeprecationTriggered $event
     * @return void
     */
    public function onTestPhpDeprecationTriggered(PhpUnitEventTestPhpDeprecationTriggered $event) : void;


    /**
     * Test issue: PHP notice triggered
     * @param PhpUnitEventTestPhpNoticeTriggered $event
     * @return void
     */
    public function onTestPhpNoticeTriggered(PhpUnitEventTestPhpNoticeTriggered $event) : void;


    /**
     * Test issue: PHP warning triggered
     * @param PhpUnitEventTestPhpWarningTriggered $event
     * @return void
     */
    public function onTestPhpWarningTriggered(PhpUnitEventTestPhpWarningTriggered $event) : void;


    /**
     * Test issue: PHPUnit warning triggered
     * @param PhpUnitEventTestPhpunitWarningTriggered $event
     * @return void
     */
    public function onTestPhpunitWarningTriggered(PhpUnitEventTestPhpunitWarningTriggered $event) : void;


    /**
     * Test outcome: errored
     * @param PhpUnitEventTestErrored $event
     * @return void
     */
    public function onTestErrored(PhpUnitEventTestErrored $event) : void;


    /**
     * Test outcome: failed
     * @param PhpUnitEventTestFailed $event
     * @return void
     */
    public function onTestFailed(PhpUnitEventTestFailed $event) : void;


    /**
     * Test outcome: marked incomplete
     * @param PhpUnitEventTestMarkedIncomplete $event
     * @return void
     */
    public function onTestMarkedIncomplete(PhpUnitEventTestMarkedIncomplete $event) : void;


    /**
     * Test outcome: notice triggered
     * @param PhpUnitEventTestNoticeTriggered $event
     * @return void
     */
    public function onTestNoticeTriggered(PhpUnitEventTestNoticeTriggered $event) : void;


    /**
     * Test outcome: passed
     * @param PhpUnitEventTestPassed $event
     * @return void
     */
    public function onTestPassed(PhpUnitEventTestPassed $event) : void;


    /**
     * Test outcome: skipped
     * @param PhpUnitEventTestSkipped $event
     * @return void
     */
    public function onTestSkipped(PhpUnitEventTestSkipped $event) : void;


    /**
     * Test outcome: warning triggered
     * @param PhpUnitEventTestWarningTriggered $event
     * @return void
     */
    public function onTestWarningTriggered(PhpUnitEventTestWarningTriggered $event) : void;
}
