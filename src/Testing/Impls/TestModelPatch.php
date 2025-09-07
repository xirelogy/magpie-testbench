<?php

namespace MagpieLib\TestBench\Testing\Impls;

use Carbon\CarbonInterface;
use Magpie\General\Sugars\Quote;
use Magpie\Models\Concepts\ModelInitializePatchable;
use Magpie\Models\Concepts\ModelSavePatchable;
use Magpie\Models\Concepts\ModelTimestampPatchable;

/**
 * Implementation of ModelInitializePatchable, ModelTimestampPatchable, ModelSavePatchable
 * @internal
 */
class TestModelPatch implements ModelInitializePatchable, ModelTimestampPatchable, ModelSavePatchable
{
    /**
     * @var array<string, array> Stack of initializations
     */
    protected array $initStacks = [];
    /**
     * @var array<string, array<CarbonInterface>> Stack of creation timestamps
     */
    protected array $createStacks = [];
    /**
     * @var array<string, array<CarbonInterface>> Stack of creation timestamps
     */
    protected array $updateStacks = [];
    /**
     * @var array<string, array<string, array{columnName: string, value: mixed}>> Stack of save actions
     */
    protected array $saveStacks = [];


    /**
     * Push a new init value on stack
     * @param string $tableModelClass
     * @param string $columnName
     * @param mixed $value
     * @return void
     */
    public function pushInit(string $tableModelClass, string $columnName, mixed $value) : void
    {
        $key = static::createColumnKey($tableModelClass, $columnName);

        $initStack = $this->initStacks[$key] ?? [];
        $initStack[] = $value;

        $this->initStacks[$key] = $initStack;
    }


    /**
     * @inheritDoc
     */
    public function tryInitializeColumn(string $tableModelClass, string $columnName, mixed &$result = null) : bool
    {
        $key = static::createColumnKey($tableModelClass, $columnName);

        if (!array_key_exists($key, $this->initStacks)) return false;

        $initStack = $this->initStacks[$key];
        if (count($initStack) <= 0) return false;

        $result = $initStack[0];

        // Pop only when save triggered
        $resultKey = static::createValueKey($result);
        $this->subscribeSavePopInit($tableModelClass, $columnName, $resultKey, $result);

        return true;
    }


    /**
     * Push a new creation timestamp on stack
     * @param string $tableModelClass
     * @param CarbonInterface $value
     * @return void
     */
    public function pushCreate(string $tableModelClass, CarbonInterface $value) : void
    {
        $stack = $this->createStacks[$tableModelClass] ?? [];
        $stack[] = $value;

        $this->createStacks[$tableModelClass] = $stack;
    }


    /**
     * @inheritDoc
     */
    public function tryCreateTimestamp(string $tableModelClass) : ?CarbonInterface
    {
        if (!array_key_exists($tableModelClass, $this->createStacks)) return null;

        $stack = $this->createStacks[$tableModelClass];
        if (count($stack) <= 0) return null;

        $result = $stack[0];
        array_shift($stack);

        $this->createStacks[$tableModelClass] = $stack;
        return $result;
    }


    /**
     * Push a new update timestamp on stack
     * @param string $tableModelClass
     * @param CarbonInterface $value
     * @return void
     */
    public function pushUpdate(string $tableModelClass, CarbonInterface $value) : void
    {
        $stack = $this->updateStacks[$tableModelClass] ?? [];
        $stack[] = $value;

        $this->updateStacks[$tableModelClass] = $stack;
    }


    /**
     * @inheritDoc
     */
    public function tryUpdateTimestamp(string $tableModelClass) : ?CarbonInterface
    {
        if (!array_key_exists($tableModelClass, $this->updateStacks)) return null;

        $stack = $this->updateStacks[$tableModelClass];
        if (count($stack) <= 0) return null;

        $result = $stack[0];
        array_shift($stack);

        $this->updateStacks[$tableModelClass] = $stack;
        return $result;
    }


    /**
     * @inheritDoc
     */
    public function notifySave(string $tableModelClass) : void
    {
        if (!array_key_exists($tableModelClass, $this->saveStacks)) return;

        /** @var array<string, array{columnName: string, value: mixed}> $map */
        $map = $this->saveStacks[$tableModelClass];
        unset($this->saveStacks[$tableModelClass]);

        foreach ($map as $fullKey => $data) {
            _used($fullKey);
            $columnName = $data['columnName'];
            $value = $data['value'];

            $key = static::createColumnKey($tableModelClass, $columnName);
            $initStack = $this->initStacks[$key];
            if (count($initStack) <= 0) return;

            if ($value != $initStack[0]) return;
            array_shift($initStack);

            $this->initStacks[$key] = $initStack;
        }
    }


    /**
     * Subscribe a save pop init action
     * @param string $tableModelClass
     * @param string $columnName
     * @param string $valueKey
     * @param mixed $value
     * @return void
     */
    protected function subscribeSavePopInit(string $tableModelClass, string $columnName, string $valueKey, mixed $value) : void
    {
        $map = $this->saveStacks[$tableModelClass] ?? [];

        $fullKey = Quote::square($columnName) . '-' . Quote::square($valueKey);
        $map[$fullKey] = [
            'columnName' => $columnName,
            'value' => $value,
        ];

        $this->saveStacks[$tableModelClass] = $map;
    }


    /**
     * Create column key
     * @param string $modelClassName
     * @param string $columnName
     * @return string
     */
    protected static function createColumnKey(string $modelClassName, string $columnName) : string
    {
        return "[$modelClassName]::$columnName";
    }


    /**
     * Create value key
     * @param mixed $value
     * @return string
     */
    protected static function createValueKey(mixed $value) : string
    {
        return stringOf($value);
    }
}