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
 * Forwarding printer
 */
class ForwardingPrinter implements Printable
{
    /**
     * @var array<Printable> Receiving printers
     */
    protected readonly array $printers;


    /**
     * Constructor
     * @param iterable<Printable> $printers
     */
    protected function __construct(iterable $printers)
    {
        $this->printers = iter_flatten($printers);
    }


    /**
     * @inheritDoc
     */
    public function onTestRunnerConfigured(PhpUnitEventTestRunnerConfigured $event) : void
    {
        foreach ($this->printers as $printer) {
            $printer->onTestRunnerConfigured($event);
        }
    }


    /**
     * @inheritDoc
     */
    public function onTestRunnerExecutionStarted(PhpUnitEventTestRunnerExecutionStarted $event) : void
    {
        foreach ($this->printers as $printer) {
            $printer->onTestRunnerExecutionStarted($event);
        }
    }


    /**
     * @inheritDoc
     */
    public function onTestRunnerExecutionFinished(PhpUnitEventTestRunnerExecutionFinished $event) : void
    {
        foreach ($this->printers as $printer) {
            $printer->onTestRunnerExecutionFinished($event);
        }
    }


    /**
     * @inheritDoc
     */
    public function onTestSuiteStarted(PhpUnitEventTestSuiteStarted $event) : void
    {
        foreach ($this->printers as $printer) {
            $printer->onTestSuiteStarted($event);
        }
    }


    /**
     * @inheritDoc
     */
    public function onTestSuiteFinished(PhpUnitEventTestSuiteFinished $event) : void
    {
        foreach ($this->printers as $printer) {
            $printer->onTestSuiteFinished($event);
        }
    }


    /**
     * @inheritDoc
     */
    public function onTestBeforeFirstTestMethodErrored(PhpUnitEventTestBeforeFirstTestMethodErrored $event) : void
    {
        foreach ($this->printers as $printer) {
            $printer->onTestBeforeFirstTestMethodErrored($event);
        }
    }


    /**
     * @inheritDoc
     */
    public function onTestPrintedUnexpectedOutput(PhpUnitEventTestPrintedUnexpectedOutput $event) : void
    {
        foreach ($this->printers as $printer) {
            $printer->onTestPrintedUnexpectedOutput($event);
        }
    }


    /**
     * @inheritDoc
     */
    public function onTestPreparationStarted(PhpUnitEventTestPreparationStarted $event) : void
    {
        foreach ($this->printers as $printer) {
            $printer->onTestPreparationStarted($event);
        }
    }


    /**
     * @inheritDoc
     */
    public function onTestFinished(PhpUnitEventTestFinished $event) : void
    {
        foreach ($this->printers as $printer) {
            $printer->onTestFinished($event);
        }
    }


    /**
     * @inheritDoc
     */
    public function onTestRunnerDeprecationTriggered(PhpUnitEventTestRunnerDeprecationTriggered $event) : void
    {
        foreach ($this->printers as $printer) {
            $printer->onTestRunnerDeprecationTriggered($event);
        }
    }


    /**
     * @inheritDoc
     */
    public function onTestRunnerWarningTriggered(PhpUnitEventTestRunnerWarningTriggered $event) : void
    {
        foreach ($this->printers as $printer) {
            $printer->onTestRunnerWarningTriggered($event);
        }
    }


    /**
     * @inheritDoc
     */
    public function onTestConsideredRisky(PhpUnitEventTestConsideredRisky $event) : void
    {
        foreach ($this->printers as $printer) {
            $printer->onTestConsideredRisky($event);
        }
    }


    /**
     * @inheritDoc
     */
    public function onTestDeprecationTriggered(PhpUnitEventTestDeprecationTriggered $event) : void
    {
        foreach ($this->printers as $printer) {
            $printer->onTestDeprecationTriggered($event);
        }
    }


    /**
     * @inheritDoc
     */
    public function onTestPhpDeprecationTriggered(PhpUnitEventTestPhpDeprecationTriggered $event) : void
    {
        foreach ($this->printers as $printer) {
            $printer->onTestPhpDeprecationTriggered($event);
        }
    }


    /**
     * @inheritDoc
     */
    public function onTestPhpNoticeTriggered(PhpUnitEventTestPhpNoticeTriggered $event) : void
    {
        foreach ($this->printers as $printer) {
            $printer->onTestPhpNoticeTriggered($event);
        }
    }


    /**
     * @inheritDoc
     */
    public function onTestPhpWarningTriggered(PhpUnitEventTestPhpWarningTriggered $event) : void
    {
        foreach ($this->printers as $printer) {
            $printer->onTestPhpWarningTriggered($event);
        }
    }


    /**
     * @inheritDoc
     */
    public function onTestPhpunitWarningTriggered(PhpUnitEventTestPhpunitWarningTriggered $event) : void
    {
        foreach ($this->printers as $printer) {
            $printer->onTestPhpunitWarningTriggered($event);
        }
    }


    /**
     * @inheritDoc
     */
    public function onTestErrored(PhpUnitEventTestErrored $event) : void
    {
        foreach ($this->printers as $printer) {
            $printer->onTestErrored($event);
        }
    }


    /**
     * @inheritDoc
     */
    public function onTestFailed(PhpUnitEventTestFailed $event) : void
    {
        foreach ($this->printers as $printer) {
            $printer->onTestFailed($event);
        }
    }


    /**
     * @inheritDoc
     */
    public function onTestMarkedIncomplete(PhpUnitEventTestMarkedIncomplete $event) : void
    {
        foreach ($this->printers as $printer) {
            $printer->onTestMarkedIncomplete($event);
        }
    }


    /**
     * @inheritDoc
     */
    public function onTestNoticeTriggered(PhpUnitEventTestNoticeTriggered $event) : void
    {
        foreach ($this->printers as $printer) {
            $printer->onTestNoticeTriggered($event);
        }
    }


    /**
     * @inheritDoc
     */
    public function onTestPassed(PhpUnitEventTestPassed $event) : void
    {
        foreach ($this->printers as $printer) {
            $printer->onTestPassed($event);
        }
    }


    /**
     * @inheritDoc
     */
    public function onTestSkipped(PhpUnitEventTestSkipped $event) : void
    {
        foreach ($this->printers as $printer) {
            $printer->onTestSkipped($event);
        }
    }


    /**
     * @inheritDoc
     */
    public function onTestWarningTriggered(PhpUnitEventTestWarningTriggered $event) : void
    {
        foreach ($this->printers as $printer) {
            $printer->onTestWarningTriggered($event);
        }
    }


    /**
     * Create an instance
     * @param iterable<Printable> $printers
     * @return static
     */
    public static function create(iterable $printers) : static
    {
        return new static($printers);
    }
}