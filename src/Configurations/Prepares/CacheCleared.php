<?php

namespace MagpieLib\TestBench\Configurations\Prepares;

use Magpie\Caches\CacheProvider;
use Magpie\General\Traits\StaticCreatable;
use MagpieLib\TestBench\Configurations\Concepts\TestEnvironmentPreparable;
use MagpieLib\TestBench\Configurations\TestEnvironmentContext;

/**
 * Clear everything in the corresponding cache provider
 */
class CacheCleared implements TestEnvironmentPreparable
{
    use StaticCreatable;

    /**
     * @inheritDoc
     */
    public function prepare(TestEnvironmentContext $context) : void
    {
        $logger = $context->getLogger();

        $logger->info(_l('Clearing cache...'));
        $provider = CacheProvider::getDefaultProvider();
        $provider->clear();

        $logger->info(_l('Cache cleared'));
    }
}