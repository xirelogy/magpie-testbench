<?php

namespace MagpieLib\TestBench\Impls\Adapters\Subscribers;

use MagpieLib\TestBench\System\Adapters\Printers\Printable;

/**
 * A subscriber with printer attached
 * @internal
 */
abstract class PrinterAttachedSubscriber
{
    /**
     * @var Printable The attached printer
     */
    protected readonly Printable $printer;


    /**
     * Constructor
     * @param Printable $printer
     */
    public function __construct(Printable $printer)
    {
        $this->printer = $printer;
    }
}