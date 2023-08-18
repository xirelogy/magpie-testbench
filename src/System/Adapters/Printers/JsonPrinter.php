<?php

namespace MagpieLib\TestBench\System\Adapters\Printers;

use Magpie\Codecs\Formats\JsonGeneralFormatter;
use Magpie\General\Simples\SimpleJSON;
use Magpie\General\Sugars\Excepts;
use PHPUnit\Event\TestRunner\ExecutionFinished as PhpUnitEventTestRunnerExecutionFinished;

/**
 * Printer with JSON output
 */
class JsonPrinter extends Printer
{
    /**
     * @inheritDoc
     */
    public function onTestRunnerExecutionFinished(PhpUnitEventTestRunnerExecutionFinished $event) : void
    {
        parent::onTestRunnerExecutionFinished($event);

        $ret = [];

        foreach ($this->getSuites() as $suite) {
            foreach ($suite->getCategories() as $category) {
                $ret[] = obj([
                    'categoryName' => $category->name,
                    'suiteName' => $suite->name,
                    'testCases' => $category->getCases(),
                ]);
            }
        }

        $ret = JsonGeneralFormatter::create()->format($ret);

        echo Excepts::noThrow(fn () => SimpleJSON::encode($ret));
        echo PHP_EOL;
    }
}