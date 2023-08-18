<?php

namespace MagpieLib\TestBench\System;

use Magpie\Commands\CommandRegistry;
use Magpie\General\Factories\ClassFactory;
use Magpie\System\Concepts\SystemBootable;
use Magpie\System\Kernel\BootContext;
use Magpie\System\Kernel\BootRegistrar;

/**
 * Test related setup
 */
class TestSetup implements SystemBootable
{
    /**
     * @inheritDoc
     */
    public static function systemBootRegister(BootRegistrar $registrar) : bool
    {
        return true;
    }


    /**
     * @inheritDoc
     */
    public static function systemBoot(BootContext $context) : void
    {
        CommandRegistry::includeDirectory(__DIR__ . '/../Commands');

        ClassFactory::includeDirectory(__DIR__ . '/Adapters/Constants');
    }
}