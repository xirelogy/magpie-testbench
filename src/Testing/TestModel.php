<?php

namespace MagpieLib\TestBench\Testing;

use Carbon\CarbonInterface;
use Magpie\General\Traits\StaticClass;
use Magpie\Models\ColumnName;
use Magpie\Models\Model;
use Magpie\Models\Patches\ModelPatch;
use MagpieLib\TestBench\Testing\Impls\ModelInitializerPatch;
use MagpieLib\TestBench\Testing\Impls\ModelTimestampPatch;
use MagpieLib\TestBench\Testing\Impls\TestModelPatch;

/**
 * Model related environment setup
 */
class TestModel
{
    use StaticClass;

    /**
     * @var TestModelPatch|null Current patch
     */
    protected static ?TestModelPatch $patch = null;


    /**
     * Declare the expected initialization value for the next initialization of the given column
     * @param ColumnName $column
     * @param mixed $value
     * @return void
     */
    public static function expectInit(ColumnName $column, mixed $value) : void
    {
        if ($column->table === null) return;

        static::getPatch()->pushInit($column->table->getModelClassName(), $column->name, $value);
    }


    /**
     * Declare the expected creation time value for the next operation
     * @param Model|class-string<Model> $model
     * @param CarbonInterface $value
     * @param bool $isUpdatedAt The same value is also duplicated as the next update time stamp
     * @return void
     */
    public static function expectCreatedAt(Model|string $model, CarbonInterface $value, bool $isUpdatedAt = true) : void
    {
        static::getPatch()->pushCreate(static::resolveModelClassName($model), $value);

        if ($isUpdatedAt) static::expectUpdatedAt($model, $value);
    }


    /**
     * Declare the expected update time value for the next operation
     * @param Model|class-string<Model> $model
     * @param CarbonInterface $value
     * @return void
     */
    public static function expectUpdatedAt(Model|string $model, CarbonInterface $value) : void
    {
        static::getPatch()->pushUpdate(static::resolveModelClassName($model), $value);
    }


    /**
     * Resolve for model class name
     * @param Model|class-string<Model> $model
     * @return class-string<Model>
     */
    protected static function resolveModelClassName(Model|string $model) : string
    {
        if ($model instanceof Model) return $model::class;
        return $model;
    }


    /**
     * Access to current patch instance (listener)
     * @return TestModelPatch
     */
    protected static function getPatch() : TestModelPatch
    {
        if (static::$patch === null) {
            static::$patch = new TestModelPatch();
            ModelPatch::listenInitializer(static::$patch);
            ModelPatch::listenTimestamp(static::$patch);
            ModelPatch::listenSave(static::$patch);
        }

        return static::$patch;
    }
}
