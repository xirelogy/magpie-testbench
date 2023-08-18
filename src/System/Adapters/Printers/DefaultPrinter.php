<?php

namespace MagpieLib\TestBench\System\Adapters\Printers;

use Closure;
use Magpie\Consoles\ConsoleTable;
use Magpie\Consoles\DisplayStyle;
use Magpie\Consoles\Texts\StructuredText;
use Magpie\General\Factories\NamedLabelProvider;
use Magpie\General\Factories\NamedPayloadMap;
use Magpie\General\Str;
use Magpie\General\Sugars\Quote;
use MagpieLib\TestBench\System\Adapters\Constants\CommonResult;
use MagpieLib\TestBench\System\Adapters\Objects\TestStatistic;
use PHPUnit\Event\TestRunner\ExecutionFinished as PhpUnitEventTestRunnerExecutionFinished;

/**
 * Default implementation of printer
 */
class DefaultPrinter extends Printer
{
    /**
     * @inheritDoc
     */
    public function onTestRunnerExecutionFinished(PhpUnitEventTestRunnerExecutionFinished $event) : void
    {
        parent::onTestRunnerExecutionFinished($event);

        $statsCollector = $this->createStatisticCollector();

        foreach ($this->getSuites() as $suite) {
            foreach ($suite->getCategories() as $category) {
                // Show the suite/category message
                $categoryMessages = [
                    StructuredText::notice($category->name ?? _l('<uncategorized>')),
                ];
                if (!Str::isNullOrEmpty($suite->name)) {
                    $categoryMessages[] = ' ' . Quote::square($suite->name);
                }
                $this->console->output(StructuredText::compound(...$categoryMessages));

                // Then process the cases and the results
                foreach ($category->getCases() as $case) {
                    $case->updateStatistic($statsCollector);
                    foreach ($case->getRepresentationResults() as $result) {
                        $resultMessages = [
                            ' ',
                            StructuredText::from($result->getSymbol(), $result->getSymbolDisplayStyle()),
                            ' ',
                            StructuredText::debug($case->getName()),
                        ];

                        $resultMessage = $result->getMessage();
                        if (!Str::isNullOrEmpty($resultMessage)) {
                            $resultMessages[] = ' → ';
                            $resultMessages[] = StructuredText::warning($resultMessage);
                        }

                        $this->console->output(StructuredText::compound(...$resultMessages));
                    }
                }

                $this->console->output('');
            }
        }

        $statsCollector->output();
    }


    /**
     * Create a statistic collector
     * @return TestStatistic
     */
    protected function createStatisticCollector() : TestStatistic
    {
        $displayFn = function (array $counters) {
            $this->console->output('');
            $this->console->output(_l('Statistics:'), DisplayStyle::NOTICE);

            $tableRows = [];
            $labelProvider = NamedLabelProvider::from(CommonResult::class);
            $styleFormat = static::createCommonResultStyleProvider()->createFormatter();

            foreach ($counters as $type => $count) {
                $commonType = CommonResult::tryFrom($type);
                if ($commonType === null) continue;
                if ($count <= 0 && $commonType !== CommonResult::PASSED) continue;

                $style = $styleFormat->format($commonType);

                $tableRows[] = [
                    StructuredText::from($labelProvider->getLabel($commonType->value), $style),
                    StructuredText::from($count, $style),
                ];
            }

            $table = new ConsoleTable([
                _l('Result'),
                _l('Count'),
            ], $tableRows);
            $this->console->display($table);
        };

        return new class($displayFn) extends TestStatistic {
            /**
             * @var Closure
             */
            protected Closure $displayFn;


            /**
             * Constructor
             * @param callable(array):void $displayFn
             */
            public function __construct(callable $displayFn)
            {
                parent::__construct();

                $this->displayFn = $displayFn;
            }


            /**
             * Show output
             * @return void
             */
            public function output() : void
            {
                ($this->displayFn)($this->resultCounters);
            }
        };
    }


    /**
     * Get style provider for CommonResult
     * @return NamedPayloadMap<CommonResult>
     */
    protected static function createCommonResultStyleProvider() : NamedPayloadMap
    {
        return new class extends NamedPayloadMap {
            /**
             * Constructor
             */
            public function __construct()
            {
                parent::__construct();
            }


            /**
             * @inheritDoc
             */
            protected static function getConstantClassName() : string
            {
                return CommonResult::class;
            }
        };
    }
}