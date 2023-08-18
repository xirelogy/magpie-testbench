<?php

namespace MagpieLib\TestBench\System\Adapters\Printers;

use Magpie\General\Traits\StaticClass;

/**
 * Aliased printers
 */
class AliasedPrinters
{
    use StaticClass;


    /**
     * Try to get printer from aliased name
     * @param string $name
     * @return Printable|null
     */
    public static function getPrinterFromName(string $name) : ?Printable
    {
        if ($name === 'json') return JsonPrinter::create();
        if ($name === 'default') return DefaultPrinter::create();

        return null;
    }
}