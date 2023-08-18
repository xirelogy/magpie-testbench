<?php

namespace MagpieLib\TestBench\System\Adapters\Printers;

use Magpie\Consoles\Concepts\Consolable;
use Magpie\Consoles\DummyConsole;
use Magpie\Facades\Log;
use Magpie\General\Traits\StaticCreatable;
use Magpie\System\Kernel\Kernel;
use MagpieLib\TestBench\System\Adapters\Objects\DeprecationTriggeredTestResult;
use MagpieLib\TestBench\System\Adapters\Objects\ErroredTestResult;
use MagpieLib\TestBench\System\Adapters\Objects\FailedTestResult;
use MagpieLib\TestBench\System\Adapters\Objects\MarkedIncompleteTestResult;
use MagpieLib\TestBench\System\Adapters\Objects\NoticeTriggeredTestResult;
use MagpieLib\TestBench\System\Adapters\Objects\PassedTestResult;
use MagpieLib\TestBench\System\Adapters\Objects\PhpDeprecationTriggeredTestResult;
use MagpieLib\TestBench\System\Adapters\Objects\PhpNoticeTriggeredTestResult;
use MagpieLib\TestBench\System\Adapters\Objects\PhpunitWarningTriggeredTestResult;
use MagpieLib\TestBench\System\Adapters\Objects\PhpWarningTriggeredTestResult;
use MagpieLib\TestBench\System\Adapters\Objects\RiskyTestResult;
use MagpieLib\TestBench\System\Adapters\Objects\SkippedTestResult;
use MagpieLib\TestBench\System\Adapters\Objects\ExecutedTestCase;
use MagpieLib\TestBench\System\Adapters\Objects\TestResult;
use MagpieLib\TestBench\System\Adapters\Objects\TestSuite;
use MagpieLib\TestBench\System\Adapters\Objects\WarningTriggeredTestResult;
use PHPUnit\Event\Event as PhpUnitEventEvent;
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
 * A printer
 */
abstract class Printer implements Printable
{
    use StaticCreatable;

    /**
     * @var Consolable The captured console
     */
    protected readonly Consolable $console;
    /**
     * @var array<TestSuite> All test suites
     */
    private array $suites = [];
    /**
     * @var TestSuite|null Current suite
     */
    private ?TestSuite $currentSuite = null;
    /**
     * @var array Current test suite stack
     */
    private array $currentSuites = [];
    /**
     * @var ExecutedTestCase|null Current test case
     */
    private ?ExecutedTestCase $currentCase = null;


    /**
     * Constructor
     */
    protected function __construct()
    {
        $this->console = static::getConsole();
    }


    /**
     * @inheritDoc
     */
    public function onTestRunnerConfigured(PhpUnitEventTestRunnerConfigured $event) : void
    {
        // default NOP
    }


    /**
     * @inheritDoc
     */
    public function onTestRunnerExecutionStarted(PhpUnitEventTestRunnerExecutionStarted $event) : void
    {
        if (count($this->currentSuites) !== 0) {
            static::onPrinterWarning('Test runner execution started but with existing test suite in stack');
        }
    }


    /**
     * @inheritDoc
     */
    public function onTestRunnerExecutionFinished(PhpUnitEventTestRunnerExecutionFinished $event) : void
    {
        if (count($this->currentSuites) !== 0) {
            static::onPrinterWarning('Test runner execution ended but with existing test suite in stack');
        }
    }


    /**
     * @inheritDoc
     */
    public final function onTestSuiteStarted(PhpUnitEventTestSuiteStarted $event) : void
    {
        $currentSuite = TestSuite::fromPhpUnitEvent($event->testSuite(), $this->getCurrentSuite());
        $this->onPushTestSuite($currentSuite);
    }


    /**
     * @inheritDoc
     */
    public final function onTestSuiteFinished(PhpUnitEventTestSuiteFinished $event) : void
    {
        $finishingSuite = TestSuite::fromPhpUnitEvent($event->testSuite());
        $this->onPopTestSuite('suite finished', $event, $finishingSuite);
    }


    /**
     * @inheritDoc
     */
    public function onTestRunnerDeprecationTriggered(PhpUnitEventTestRunnerDeprecationTriggered $event) : void
    {
        Log::warning('onTestRunnerDeprecationTriggered: ' . $event->message());
    }


    /**
     * @inheritDoc
     */
    public function onTestRunnerWarningTriggered(PhpUnitEventTestRunnerWarningTriggered $event) : void
    {
        Log::warning('onTestRunnerWarningTriggered: ' . $event->message());
    }


    /**
     * @inheritDoc
     */
    public function onTestConsideredRisky(PhpUnitEventTestConsideredRisky $event) : void
    {
        $result = RiskyTestResult::create($event);
        $this->enqueueTestResultToCurrentCase($result);
    }


    /**
     * @inheritDoc
     */
    public function onTestDeprecationTriggered(PhpUnitEventTestDeprecationTriggered $event) : void
    {
        $result = DeprecationTriggeredTestResult::create($event);
        $this->enqueueTestResultToCurrentCase($result);
    }


    /**
     * @inheritDoc
     */
    public function onTestPhpDeprecationTriggered(PhpUnitEventTestPhpDeprecationTriggered $event) : void
    {
        $result = PhpDeprecationTriggeredTestResult::create($event);
        $this->enqueueTestResultToCurrentCase($result);
    }


    /**
     * @inheritDoc
     */
    public function onTestPhpNoticeTriggered(PhpUnitEventTestPhpNoticeTriggered $event) : void
    {
        $result = PhpNoticeTriggeredTestResult::create($event);
        $this->enqueueTestResultToCurrentCase($result);
    }


    /**
     * @inheritDoc
     */
    public function onTestPhpWarningTriggered(PhpUnitEventTestPhpWarningTriggered $event) : void
    {
        $result = PhpWarningTriggeredTestResult::create($event);
        $this->enqueueTestResultToCurrentCase($result);
    }


    /**
     * @inheritDoc
     */
    public function onTestPhpunitWarningTriggered(PhpUnitEventTestPhpunitWarningTriggered $event) : void
    {
        $result = PhpunitWarningTriggeredTestResult::create($event);
        $this->enqueueTestResultToCurrentCase($result);
    }


    /**
     * @inheritDoc
     */
    public function onTestErrored(PhpUnitEventTestErrored $event) : void
    {
        $result = ErroredTestResult::create($event);
        $this->enqueueTestResultToCurrentCase($result);
    }


    /**
     * @inheritDoc
     */
    public function onTestFailed(PhpUnitEventTestFailed $event) : void
    {
        $result = FailedTestResult::create($event);
        $this->enqueueTestResultToCurrentCase($result);
    }


    /**
     * @inheritDoc
     */
    public function onTestMarkedIncomplete(PhpUnitEventTestMarkedIncomplete $event) : void
    {
        $result = MarkedIncompleteTestResult::create($event);
        $this->enqueueTestResultToCurrentCase($result);
    }


    /**
     * @inheritDoc
     */
    public function onTestNoticeTriggered(PhpUnitEventTestNoticeTriggered $event) : void
    {
        $result = NoticeTriggeredTestResult::create($event);
        $this->enqueueTestResultToCurrentCase($result);
    }


    /**
     * @inheritDoc
     */
    public function onTestPassed(PhpUnitEventTestPassed $event) : void
    {
        $result = PassedTestResult::create($event);
        $this->enqueueTestResultToCurrentCase($result);
    }


    /**
     * @inheritDoc
     */
    public function onTestSkipped(PhpUnitEventTestSkipped $event) : void
    {
        $result = SkippedTestResult::create($event);
        $this->enqueueTestResultToCurrentCase($result);
    }


    /**
     * @inheritDoc
     */
    public function onTestWarningTriggered(PhpUnitEventTestWarningTriggered $event) : void
    {
        $result = WarningTriggeredTestResult::create($event);
        $this->enqueueTestResultToCurrentCase($result);
    }


    /**
     * Push a test suite into the stack
     * @param TestSuite $currentSuite
     * @return void
     */
    private function onPushTestSuite(TestSuite $currentSuite) : void
    {
        $this->currentSuites[] = $currentSuite;
        $this->currentSuite = $currentSuite;
        $this->onCurrentTestSuiteStarted($currentSuite);
    }


    /**
     * Pop a test suite from stack
     * @param string $condition
     * @param PhpUnitEventEvent $event
     * @param TestSuite|null $currentSuite Optional current test suite to check for proper pop
     * @return void
     */
    private function onPopTestSuite(string $condition, PhpUnitEventEvent $event, ?TestSuite $currentSuite) : void
    {
        $popCurrentSuite = $this->getCurrentSuite();
        if ($popCurrentSuite === null) {
            static::onPrinterWarning("Current suite is not available when $condition");
            return;
        }

        if ($currentSuite !== null && $currentSuite->name !== $popCurrentSuite->name) {
            static::onPrinterWarning('Current suite does not match the finishing suite');
            return;
        }

        $this->onCurrentTestSuiteEnding($popCurrentSuite);
        $popCurrentSuite->onPhpUnitFinished($event);

        // Pop and adjust
        array_pop($this->currentSuites);
        $this->currentSuite = $this->currentSuites[count($this->currentSuites) - 1] ?? null;

        // Suite completed, add to history
        $this->suites[] = $popCurrentSuite;
    }


    /**
     * When current test suite started
     * @param TestSuite $currentSuite
     * @return void
     */
    protected function onCurrentTestSuiteStarted(TestSuite $currentSuite) : void
    {

    }


    /**
     * When current test suite is about to end
     * @param TestSuite $currentSuite
     * @return void
     */
    protected function onCurrentTestSuiteEnding(TestSuite $currentSuite) : void
    {

    }


    /**
     * All test suites
     * @return iterable<TestSuite>
     */
    protected function getSuites() : iterable
    {
        yield from $this->suites;
    }


    /**
     * Current test suite
     * @return TestSuite|null
     */
    protected function getCurrentSuite() : ?TestSuite
    {
        return $this->currentSuite;
    }


    /**
     * @inheritDoc
     */
    public function onTestPreparationStarted(PhpUnitEventTestPreparationStarted $event) : void
    {
        if ($this->currentCase !== null) {
            static::onPrinterWarning('Current case is not empty when another execution started');
        }

        $this->currentCase = ExecutedTestCase::fromPhpUnitEvent($event->test());

        $this->getCurrentSuite()?->onEnqueueTestCase($this->currentCase);
    }


    /**
     * @inheritDoc
     */
    public function onTestFinished(PhpUnitEventTestFinished $event) : void
    {
        if ($this->currentCase !== null) {
            $this->currentCase->onPhpUnitFinished($event);
        } else {
            static::onPrinterWarning('Current case is not available when execution finished');
        }

        $this->currentCase = null;
    }


    /**
     * Current test case
     * @return ExecutedTestCase|null
     */
    protected function getCurrentCase() : ?ExecutedTestCase
    {
        return $this->currentCase;
    }


    /**
     * @inheritDoc
     */
    public function onTestBeforeFirstTestMethodErrored(PhpUnitEventTestBeforeFirstTestMethodErrored $event) : void
    {
        // default NOP
    }


    /**
     * @inheritDoc
     */
    public function onTestPrintedUnexpectedOutput(PhpUnitEventTestPrintedUnexpectedOutput $event) : void
    {
        // default NOP
    }


    /**
     * Enqueue a test result to current test case
     * @param TestResult $result
     * @return void
     */
    protected function enqueueTestResultToCurrentCase(TestResult $result) : void
    {
        if ($this->currentCase === null) {
            static::onPrinterWarning('Current case is not available to receive test result');
            return;
        }

        $this->currentCase->onEnqueueResult($result);
        $this->onPrinterTestResultEnqueued($this->currentCase, $result);
    }



    /**
     * Get a suitable console
     * @return Consolable
     */
    private static function getConsole() : Consolable
    {
        $console = Kernel::current()->getProvider(Consolable::class);
        if ($console instanceof Consolable) return $console;

        return DummyConsole::instance();
    }


    /**
     * Receive notification that test result is enqueued
     * @param ExecutedTestCase $case
     * @param TestResult $result
     * @return void
     */
    protected function onPrinterTestResultEnqueued(ExecutedTestCase $case, TestResult $result) : void
    {
        _used($case, $result);
    }


    /**
     * Receive warning message for printer events
     * @param string $message
     * @return void
     */
    protected static function onPrinterWarning(string $message) : void
    {
        _used($message);
    }
}