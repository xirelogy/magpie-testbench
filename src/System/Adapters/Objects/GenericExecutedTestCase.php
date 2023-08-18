<?php

namespace MagpieLib\TestBench\System\Adapters\Objects;

/**
 * A generic test case
 */
class GenericExecutedTestCase extends ExecutedTestCase
{
    /**
     * @inheritDoc
     */
    public function getCategoryId() : ?string
    {
        return null;
    }


    /**
     * @inheritDoc
     */
    public function getCategory() : ?string
    {
        return null;
    }


    /**
     * @inheritDoc
     */
    public function getName() : string
    {
        return $this->test->name();
    }
}