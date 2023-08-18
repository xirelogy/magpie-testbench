<?php

namespace MagpieLib\TestBench\System\Adapters\Objects;

use Magpie\General\Packs\PackContext;
use PHPUnit\Event\Event as PhpUnitEventEvent;
use PHPUnit\Event\TestSuite\TestSuite as PhpUnitEventTestSuite;

/**
 * A test suite
 */
class TestSuite extends TestObject
{
    /**
     * @var string Suite name
     */
    public readonly string $name;
    /**
     * @var int Total number of tests in suite
     */
    public readonly int $totalTests;
    /**
     * @var TestSuite|null Previous test suite
     */
    public readonly ?TestSuite $previous;
    /**
     * @var array<string, TestCategory> Test categories
     */
    protected array $categories = [];


    /**
     * Constructor
     * @param string $name
     * @param int $totalTests
     * @param TestSuite|null $previous
     */
    protected function __construct(string $name, int $totalTests, ?TestSuite $previous)
    {
        $this->name = $name;
        $this->totalTests = $totalTests;
        $this->previous = $previous;
    }


    /**
     * All test categories
     * @return iterable<TestCategory>
     */
    public function getCategories() : iterable
    {
        yield from $this->categories;
    }


    /**
     * Enqueue a test case
     * @param ExecutedTestCase $case
     * @return void
     */
    public function onEnqueueTestCase(ExecutedTestCase $case) : void
    {
        $categoryId = $case->getCategoryId() ?? '';
        $categoryName = $case->getCategory();

        $category = $this->categories[$categoryId] ?? new TestCategory($categoryId, $categoryName);
        $category->onEnqueueTestCase($case);

        $this->categories[$categoryId] = $category;
    }


    /**
     * Get notified that execution had finished
     * @param PhpUnitEventEvent $event
     * @return void
     */
    public function onPhpUnitFinished(PhpUnitEventEvent $event) : void
    {
        // TODO
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->name = $this->name;
        $ret->categories = $this->getCategories();
    }


    /**
     * Construct from PHPUnit event's TestSuite
     * @param PhpUnitEventTestSuite $value
     * @param TestSuite|null $previous
     * @return static
     */
    public static function fromPhpUnitEvent(PhpUnitEventTestSuite $value, ?TestSuite $previous = null) : static
    {
        return new static($value->name(), $value->count(), $previous);
    }
}