<?php

namespace MagpieLib\TestBench\System\Adapters\Objects;

use PHPUnit\Event\Code\TestMethod as PhpUnitTestMethod;

/**
 * A test case using PHP method
 */
final class MethodExecutedTestCase extends ExecutedTestCase
{
    /**
     * @var PhpUnitTestMethod Underlying object
     */
    protected readonly PhpUnitTestMethod $testMethod;


    /**
     * Constructor
     * @param PhpUnitTestMethod $testMethod
     */
    protected function __construct(PhpUnitTestMethod $testMethod)
    {
        parent::__construct($testMethod);

        $this->testMethod  = $testMethod;
    }


    /**
     * @inheritDoc
     */
    public function getCategoryId() : ?string
    {
        return $this->testMethod->className();
    }


    /**
     * @inheritDoc
     */
    public function getCategory() : ?string
    {
        return $this->testMethod->testDox()->prettifiedClassName();
    }


    /**
     * @inheritDoc
     */
    public function getName() : string
    {
        return $this->testMethod->testDox()->prettifiedMethodName();
    }
}