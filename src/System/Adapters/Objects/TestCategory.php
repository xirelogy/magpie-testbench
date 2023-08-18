<?php

namespace MagpieLib\TestBench\System\Adapters\Objects;

use Magpie\General\Packs\PackContext;

/**
 * Test category (class)
 */
class TestCategory extends TestObject
{
    /**
     * @var string ID of current test category
     */
    public readonly string $id;
    /**
     * @var string|null Name of current test category
     */
    public readonly ?string $name;
    /**
     * @var array<ExecutedTestCase> Associated test cases
     */
    protected array $cases;


    /**
     * Constructor
     * @param string $id
     * @param string|null $name
     */
    public function __construct(string $id, ?string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }


    /**
     * All test cases
     * @return iterable<ExecutedTestCase>
     */
    public function getCases() : iterable
    {
        yield from $this->cases;
    }


    /**
     * Enqueue a test case
     * @param ExecutedTestCase $case
     * @return void
     */
    public function onEnqueueTestCase(ExecutedTestCase $case) : void
    {
        $this->cases[] = $case;
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->id = $this->id;
        $ret->name = $this->name;
        $ret->cases = $this->cases;
    }
}