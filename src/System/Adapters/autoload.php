<?php

namespace MagpieLib\TestBench\System\Adapters;

use MagpieLib\TestBench\Impls\Adapters\PhpUnitConfig;

// This is a shim to autoload from PHPUnit whenever required
if (class_exists(PhpUnitConfig::class)) {
    PhpUnitConfig::autoloadFromPhpUnit();
}
