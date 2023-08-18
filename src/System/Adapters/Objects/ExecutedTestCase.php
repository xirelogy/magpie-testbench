<?php

namespace MagpieLib\TestBench\System\Adapters\Objects;

use Magpie\General\Packs\PackContext;
use PHPUnit\Event\Code\Test as PhpUnitEventTest;
use PHPUnit\Event\Code\TestMethod as PhpUnitTestMethod;
use PHPUnit\Event\Test\Finished as PhpUnitEventTestFinished;

/**
 * Executed test case
 */
abstract class ExecutedTestCase extends TestObject
{
    /**
     * @var PhpUnitEventTest Underlying value
     */
    protected readonly PhpUnitEventTest $test;
    /**
     * @var array<TestResult> Test results
     */
    protected array $results = [];


    /**
     * Constructor
     * @param PhpUnitEventTest $test
     */
    protected function __construct(PhpUnitEventTest $test)
    {
        $this->test = $test;
    }


    /**
     * ID of the test case
     * @return string
     */
    public function getId() : string
    {
        return $this->test->id();
    }


    /**
     * Category ID
     * @return string|null
     */
    public abstract function getCategoryId() : ?string;


    /**
     * Category
     * @return string|null
     */
    public abstract function getCategory() : ?string;


    /**
     * Name of the test case
     * @return string
     */
    public abstract function getName() : string;


    /**
     * Update statistics into the target statistic collector
     * @param TestStatistic $collector
     * @return void
     */
    public function updateStatistic(TestStatistic $collector) : void
    {
        foreach ($this->getResults() as $result) {
            $result->updateStatistic($collector);
        }
    }


    /**
     * All associated test result
     * @return iterable<TestResult>
     */
    public function getResults() : iterable
    {
        yield from $this->results;
    }


    /**
     * The representational results from all associated test result
     * @return iterable<TestResult>
     */
    public function getRepresentationResults() : iterable
    {
        $hasReturn = false;
        $passedRet = null;

        foreach ($this->results as $result) {
            if ($result->getTypeClass() === PassedTestResult::TYPECLASS) {
                if ($passedRet === null) $passedRet = $result;
            } else {
                $hasReturn = true;
                yield $result;
            }
        }

        if (!$hasReturn && $passedRet !== null) yield $passedRet;
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->id = $this->getId();
        $ret->name = $this->getName();
        $ret->results = $this->results;
    }


    /**
     * Enqueue a test result, associated with current test case
     * @param TestResult $result
     * @return void
     */
    public function onEnqueueResult(TestResult $result) : void
    {
        $this->results[] = $result;
    }


    /**
     * Get notified that execution had finished
     * @param PhpUnitEventTestFinished $event
     * @return void
     */
    public function onPhpUnitFinished(PhpUnitEventTestFinished $event) : void
    {
        // May be understood as passed
        if (count($this->results) <= 0) {
            $result = PassedTestResult::create($event);
            $this->onEnqueueResult($result);
        }
    }


    /**
     * Construct from PHPUnit event's Test
     * @param PhpUnitEventTest $value
     * @return static
     */
    public static final function fromPhpUnitEvent(PhpUnitEventTest $value) : static
    {
        if ($value instanceof PhpUnitTestMethod) {
            return new MethodExecutedTestCase($value);
        }

        return new GenericExecutedTestCase($value);
    }
}
