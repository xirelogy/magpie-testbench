<?php

namespace MagpieLib\TestBench\Impls\Adapters\Subscribers;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\Facades\Console;
use Magpie\HttpServer\ServerCollection;
use Magpie\System\Kernel\ExceptionHandler;
use MagpieLib\TestBench\Impls\Adapters\PhpUnitConfig;
use MagpieLib\TestBench\System\Adapters\Printers\AliasedPrinters;
use MagpieLib\TestBench\System\Adapters\Printers\DefaultPrinter;
use MagpieLib\TestBench\System\Adapters\Printers\Printable;
use MagpieLib\TestBench\System\Adapters\Printers\Printer;
use PHPUnit\Event\Application\Started as PhpUnitEventApplicationStarted;
use PHPUnit\Event\Application\StartedSubscriber as PhpUnitEventApplicationStartedSubscriber;
use PHPUnit\Event\Facade as PhpUnitEventFacade;
use PHPUnit\Event\Subscriber as PhpUnitEventSubscriber;
use PHPUnit\Event\Test\BeforeFirstTestMethodErrored as PhpUnitEventTestBeforeFirstTestMethodErrored;
use PHPUnit\Event\Test\BeforeFirstTestMethodErroredSubscriber as PhpUnitEventTestBeforeFirstTestMethodErroredSubscriber;
use PHPUnit\Event\Test\ConsideredRisky as PhpUnitEventTestConsideredRisky;
use PHPUnit\Event\Test\ConsideredRiskySubscriber as PhpUnitEventTestConsideredRiskySubscriber;
use PHPUnit\Event\Test\DeprecationTriggered as PhpUnitEventTestDeprecationTriggered;
use PHPUnit\Event\Test\DeprecationTriggeredSubscriber as PhpUnitEventTestDeprecationTriggeredSubscriber;
use PHPUnit\Event\Test\Errored as PhpUnitEventTestErrored;
use PHPUnit\Event\Test\ErroredSubscriber as PhpUnitEventTestErroredSubscriber;
use PHPUnit\Event\Test\Failed as PhpUnitEventTestFailed;
use PHPUnit\Event\Test\FailedSubscriber as PhpUnitEventTestFailedSubscriber;
use PHPUnit\Event\Test\Finished as PhpUnitEventTestFinished;
use PHPUnit\Event\Test\FinishedSubscriber as PhpUnitEventTestFinishedSubscriber;
use PHPUnit\Event\Test\MarkedIncomplete as PhpUnitEventTestMarkedIncomplete;
use PHPUnit\Event\Test\MarkedIncompleteSubscriber as PhpUnitEventTestMarkedIncompleteSubscriber;
use PHPUnit\Event\Test\NoticeTriggered as PhpUnitEventTestNoticeTriggered;
use PHPUnit\Event\Test\NoticeTriggeredSubscriber as PhpUnitEventTestNoticeTriggeredSubscriber;
use PHPUnit\Event\Test\Passed as PhpUnitEventTestPassed;
use PHPUnit\Event\Test\PassedSubscriber as PhpUnitEventTestPassedSubscriber;
use PHPUnit\Event\Test\PhpDeprecationTriggered as PhpUnitEventTestPhpDeprecationTriggered;
use PHPUnit\Event\Test\PhpDeprecationTriggeredSubscriber as PhpUnitEventTestPhpDeprecationTriggeredSubscriber;
use PHPUnit\Event\Test\PhpNoticeTriggered as PhpUnitEventTestPhpNoticeTriggered;
use PHPUnit\Event\Test\PhpNoticeTriggeredSubscriber as PhpUnitEventTestPhpNoticeTriggeredSubscriber;
use PHPUnit\Event\Test\PhpunitWarningTriggered as PhpUnitEventTestPhpunitWarningTriggered;
use PHPUnit\Event\Test\PhpunitWarningTriggeredSubscriber as PhpUnitEventTestPhpunitWarningTriggeredSubscriber;
use PHPUnit\Event\Test\PhpWarningTriggered as PhpUnitEventTestPhpWarningTriggered;
use PHPUnit\Event\Test\PhpWarningTriggeredSubscriber as PhpUnitEventTestPhpWarningTriggeredSubscriber;
use PHPUnit\Event\Test\PreparationStarted as PhpUnitEventTestPreparationStarted;
use PHPUnit\Event\Test\PreparationStartedSubscriber as PhpUnitEventTestPreparationStartedSubscriber;
use PHPUnit\Event\Test\PrintedUnexpectedOutput as PhpUnitEventTestPrintedUnexpectedOutput;
use PHPUnit\Event\Test\PrintedUnexpectedOutputSubscriber as PhpUnitEventTestPrintedUnexpectedOutputSubscriber;
use PHPUnit\Event\Test\Skipped as PhpUnitEventTestSkipped;
use PHPUnit\Event\Test\SkippedSubscriber as PhpUnitEventTestSkippedSubscriber;
use PHPUnit\Event\Test\WarningTriggered as PhpUnitEventTestWarningTriggered;
use PHPUnit\Event\Test\WarningTriggeredSubscriber as PhpUnitEventTestWarningTriggeredSubscriber;
use PHPUnit\Event\TestRunner\Configured as PhpUnitEventTestRunnerConfigured;
use PHPUnit\Event\TestRunner\ConfiguredSubscriber as PhpUnitEventTestRunnerConfiguredSubscriber;
use PHPUnit\Event\TestRunner\DeprecationTriggered as PhpUnitEventTestRunnerDeprecationTriggered;
use PHPUnit\Event\TestRunner\DeprecationTriggeredSubscriber as PhpUnitEventTestRunnerDeprecationTriggeredSubscriber;
use PHPUnit\Event\TestRunner\ExecutionFinished as PhpUnitEventTestRunnerExecutionFinished;
use PHPUnit\Event\TestRunner\ExecutionFinishedSubscriber as PhpUnitEventTestRunnerExecutionFinishedSubscriber;
use PHPUnit\Event\TestRunner\ExecutionStarted as PhpUnitEventTestRunnerExecutionStarted;
use PHPUnit\Event\TestRunner\ExecutionStartedSubscriber as PhpUnitEventTestRunnerExecutionStartedSubscriber;
use PHPUnit\Event\TestRunner\WarningTriggered as PhpUnitEventTestRunnerWarningTriggered;
use PHPUnit\Event\TestRunner\WarningTriggeredSubscriber as PhpUnitEventTestRunnerWarningTriggeredSubscriber;
use PHPUnit\Event\TestSuite\Finished as PhpUnitEventTestSuiteFinished;
use PHPUnit\Event\TestSuite\FinishedSubscriber as PhpUnitEventTestSuiteFinishedSubscriber;
use PHPUnit\Event\TestSuite\Started as PhpUnitEventTestSuiteStarted;
use PHPUnit\Event\TestSuite\StartedSubscriber as PhpUnitEventTestSuiteStartedSubscriber;
use Throwable;

/**
 * Subscribe to PHPUnit started event
 * @internal
 */
final class PhpUnitStartedSubscriber implements PhpUnitEventApplicationStartedSubscriber
{
    /**
     * @var bool If registered
     */
    protected static bool $isRegistered = false;


    /**
     * Constructor
     */
    protected function __construct()
    {

    }


    /**
     * Get notified
     * @param PhpUnitEventApplicationStarted $event
     * @return void
     */
    public function notify(PhpUnitEventApplicationStarted $event) : void
    {
        try {
            $printer = $this->createPrinter();

            $subscribers = $this->createSubscribers($printer);
            PhpUnitEventFacade::instance()->registerSubscribers(...$subscribers);
        } catch (Throwable $ex) {
            Console::error($ex->getMessage());
            ExceptionHandler::systemCritical($ex);
        }
    }


    /**
     * Create all subscribers
     * @param Printable $printer
     * @return iterable<PhpUnitEventSubscriber>
     */
    protected function createSubscribers(Printable $printer) : iterable
    {
        // Test runner events
        yield new class($printer) extends PrinterAttachedSubscriber implements PhpUnitEventTestRunnerConfiguredSubscriber {
            public function notify(PhpUnitEventTestRunnerConfigured $event) : void
            {
                $this->printer->onTestRunnerConfigured($event);
            }
        };

        yield new class($printer) extends PrinterAttachedSubscriber implements PhpUnitEventTestRunnerExecutionStartedSubscriber {
            public function notify(PhpUnitEventTestRunnerExecutionStarted $event) : void
            {
                $this->printer->onTestRunnerExecutionStarted($event);
            }
        };

        yield new class($printer) extends PrinterAttachedSubscriber implements PhpUnitEventTestRunnerExecutionFinishedSubscriber {
            public function notify(PhpUnitEventTestRunnerExecutionFinished $event) : void
            {
                $this->printer->onTestRunnerExecutionFinished($event);
            }
        };

        // Test suite events
        yield new class($printer) extends PrinterAttachedSubscriber implements PhpUnitEventTestSuiteStartedSubscriber {
            public function notify(PhpUnitEventTestSuiteStarted $event) : void
            {
                $this->printer->onTestSuiteStarted($event);
            }
        };

        yield new class($printer) extends PrinterAttachedSubscriber implements PhpUnitEventTestSuiteFinishedSubscriber {
            public function notify(PhpUnitEventTestSuiteFinished $event) : void
            {
                $this->printer->onTestSuiteFinished($event);
            }
        };

        // Test events - general
        yield new class($printer) extends PrinterAttachedSubscriber implements PhpUnitEventTestBeforeFirstTestMethodErroredSubscriber {
            public function notify(PhpUnitEventTestBeforeFirstTestMethodErrored $event) : void
            {
                $this->printer->onTestBeforeFirstTestMethodErrored($event);
            }
        };

        yield new class($printer) extends PrinterAttachedSubscriber implements PhpUnitEventTestPrintedUnexpectedOutputSubscriber {
            public function notify(PhpUnitEventTestPrintedUnexpectedOutput $event) : void
            {
                $this->printer->onTestPrintedUnexpectedOutput($event);
            }
        };

        // Test events - lifecycle
        yield new class($printer) extends PrinterAttachedSubscriber implements PhpUnitEventTestPreparationStartedSubscriber {
            public function notify(PhpUnitEventTestPreparationStarted $event) : void
            {
                $this->printer->onTestPreparationStarted($event);
            }
        };

        yield new class($printer) extends PrinterAttachedSubscriber implements PhpUnitEventTestFinishedSubscriber {
            public function notify(PhpUnitEventTestFinished $event) : void
            {
                $this->printer->onTestFinished($event);
            }
        };

        // Test runner events - issues (warnings)
        yield new class($printer) extends PrinterAttachedSubscriber implements PhpUnitEventTestRunnerDeprecationTriggeredSubscriber {
            public function notify(PhpUnitEventTestRunnerDeprecationTriggered $event) : void
            {
                $this->printer->onTestRunnerDeprecationTriggered($event);
            }
        };

        yield new class($printer) extends PrinterAttachedSubscriber implements PhpUnitEventTestRunnerWarningTriggeredSubscriber {
            public function notify(PhpUnitEventTestRunnerWarningTriggered $event) : void
            {
                $this->printer->onTestRunnerWarningTriggered($event);
            }
        };

        // Test events - issues (warnings)
        yield new class($printer) extends PrinterAttachedSubscriber implements PhpUnitEventTestConsideredRiskySubscriber {
            public function notify(PhpUnitEventTestConsideredRisky $event) : void
            {
                $this->printer->onTestConsideredRisky($event);
            }
        };

        yield new class($printer) extends PrinterAttachedSubscriber implements PhpUnitEventTestDeprecationTriggeredSubscriber {
            public function notify(PhpUnitEventTestDeprecationTriggered $event) : void
            {
                $this->printer->onTestDeprecationTriggered($event);
            }
        };

        yield new class($printer) extends PrinterAttachedSubscriber implements PhpUnitEventTestPhpDeprecationTriggeredSubscriber {
            public function notify(PhpUnitEventTestPhpDeprecationTriggered $event) : void
            {
                $this->printer->onTestPhpDeprecationTriggered($event);
            }
        };

        yield new class($printer) extends PrinterAttachedSubscriber implements PhpUnitEventTestPhpNoticeTriggeredSubscriber {
            public function notify(PhpUnitEventTestPhpNoticeTriggered $event) : void
            {
                $this->printer->onTestPhpNoticeTriggered($event);
            }
        };

        yield new class($printer) extends PrinterAttachedSubscriber implements PhpUnitEventTestPhpWarningTriggeredSubscriber {
            public function notify(PhpUnitEventTestPhpWarningTriggered $event) : void
            {
                $this->printer->onTestPhpWarningTriggered($event);
            }
        };

        yield new class($printer) extends PrinterAttachedSubscriber implements PhpUnitEventTestPhpunitWarningTriggeredSubscriber {
            public function notify(PhpUnitEventTestPhpunitWarningTriggered $event) : void
            {
                $this->printer->onTestPhpunitWarningTriggered($event);
            }
        };

        // Test outcomes
        yield new class($printer) extends PrinterAttachedSubscriber implements PhpUnitEventTestErroredSubscriber {
            public function notify(PhpUnitEventTestErrored $event) : void
            {
                $this->printer->onTestErrored($event);
            }
        };

        yield new class($printer) extends PrinterAttachedSubscriber implements PhpUnitEventTestFailedSubscriber {
            public function notify(PhpUnitEventTestFailed $event) : void
            {
                $this->printer->onTestFailed($event);
            }
        };

        yield new class($printer) extends PrinterAttachedSubscriber implements PhpUnitEventTestMarkedIncompleteSubscriber {
            public function notify(PhpUnitEventTestMarkedIncomplete $event) : void
            {
                $this->printer->onTestMarkedIncomplete($event);
            }
        };

        yield new class($printer) extends PrinterAttachedSubscriber implements PhpUnitEventTestNoticeTriggeredSubscriber {
            public function notify(PhpUnitEventTestNoticeTriggered $event) : void
            {
                $this->printer->onTestNoticeTriggered($event);
            }
        };

        yield new class($printer) extends PrinterAttachedSubscriber implements PhpUnitEventTestPassedSubscriber {
            public function notify(PhpUnitEventTestPassed $event) : void
            {
                $this->printer->onTestPassed($event);
            }
        };

        yield new class($printer) extends PrinterAttachedSubscriber implements PhpUnitEventTestSkippedSubscriber {
            public function notify(PhpUnitEventTestSkipped $event) : void
            {
                $this->printer->onTestSkipped($event);
            }
        };

        yield new class($printer) extends PrinterAttachedSubscriber implements PhpUnitEventTestWarningTriggeredSubscriber {
            public function notify(PhpUnitEventTestWarningTriggered $event) : void
            {
                $this->printer->onTestWarningTriggered($event);
            }
        };
    }


    /**
     * Create the printer
     * @return Printable
     * @throws SafetyCommonException
     */
    protected function createPrinter() : Printable
    {
        $serverVars = ServerCollection::capture();
        $printerName = $serverVars->safeOptional(PhpUnitConfig::ENV_NAME_PRINTER);
        if ($printerName !== null) {
            $aliasedPrinter = AliasedPrinters::getPrinterFromName($printerName);
            if ($aliasedPrinter !== null) return $aliasedPrinter;

            if (is_subclass_of($printerName, Printer::class)) {
                return $printerName::create();
            }

            throw new UnsupportedValueException($printerName, _l('printer'));
        }

        return DefaultPrinter::create();
    }


    /**
     * Register
     * @return void
     */
    public static function register() : void
    {
        if (static::$isRegistered) return;

        static::$isRegistered = true;

        $instance = new static();
        PhpUnitEventFacade::instance()->registerSubscriber($instance);
    }
}