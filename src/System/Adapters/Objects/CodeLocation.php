<?php

namespace MagpieLib\TestBench\System\Adapters\Objects;

use Magpie\Codecs\Parsers\IntegerParser;
use Magpie\Exceptions\ArgumentException;
use Magpie\Exceptions\InvalidDataException;
use Magpie\Exceptions\InvalidDataFormatException;
use Magpie\Objects\CommonObject;
use Magpie\Objects\Traits\CommonObjectPackAll;

/**
 * Representation of a code location
 */
class CodeLocation extends CommonObject
{
    use CommonObjectPackAll;

    /**
     * @var string Corresponding file
     */
    public readonly string $file;
    /**
     * @var int Line number
     */
    public readonly int $line;


    /**
     * Constructor
     * @param string $file
     * @param int $line
     */
    public function __construct(string $file, int $line)
    {
        $this->file = $file;
        $this->line = $line;
    }


    /**
     * An instance representing 'unknown'
     * @return static
     */
    public static function unknown() : static
    {
        return new static('', 0);
    }


    /**
     * @param string $text
     * @return static
     * @throws InvalidDataException
     * @throws ArgumentException
     */
    public static function parseLine(string $text) : static
    {
        $colonPos = strpos($text, ':');
        if ($colonPos === false) throw new InvalidDataFormatException();

        $file = substr($text, 0, $colonPos);

        $line = substr($text, $colonPos + 1);
        $line = IntegerParser::create()->withMin(0)->parse($line);

        return new static($file, $line);
    }
}
