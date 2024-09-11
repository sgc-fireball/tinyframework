<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Feature\Database;

use TinyFramework\Database\BaseModel;
use TinyFramework\Database\Relations\HasOne;
use TinyFramework\Tests\Feature\FeatureTestCase;

class ModelA extends BaseModel
{
    public function modelB(): HasOne
    {
        return $this->hasOne(ModelB::class);
    }

    public function modelC(): HasOne
    {
        return $this->hasOne(ModelC::class);
    }

    public function modelE(): HasOne
    {
        return $this->hasOne(ModelE::class);
    }
}

class ModelB extends BaseModel
{
}

class ModelC extends BaseModel
{
    public function modelD(): HasOne
    {
        return $this->hasOne(ModelD::class);
    }
}

class ModelD extends BaseModel
{
}

class ModelE extends BaseModel
{
    public function modelF(): HasOne
    {
        return $this->hasOne(ModelF::class);
    }
    public function modelG(): HasOne
    {
        return $this->hasOne(ModelG::class);
    }
}

class ModelF extends BaseModel
{
}

class ModelG extends BaseModel
{
    public function modelH(): HasOne
    {
        return $this->hasOne(ModelH::class);
    }
}

class ModelH extends BaseModel
{
}

class EagerLoadingTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $database = $this->container->get('database.' . config('database.default'));
        assert($database instanceof DatabaseInterface);
    }

    public function testGetterSetterWith(): void
    {
        $model = new ModelA();
        $model->with('modelB');
        $model->with('modelC.modelD');
        $model->with(['modelE' => ['modelF' => [], 'modelG' => ['modelH']]]);
        $with = $model->with();
        $this->assertIsArray($with);
        $this->assertArrayHasKey('modelB', $with);
        $this->assertIsArray($with['modelB']);
        $this->assertArrayHasKey('modelC', $with);
        $this->assertIsArray($with['modelC']);
        $this->assertArrayHasKey('modelD', $with['modelC']);
        $this->assertIsArray($with['modelC']['modelD']);
        $this->assertEmpty($with['modelC']['modelD']);
        $this->assertArrayHasKey('modelE', $with);
        $this->assertIsArray($with['modelE']);
        $this->assertArrayHasKey('modelF', $with['modelE']);
        $this->assertEmpty($with['modelE']['modelF']);
        $this->assertArrayHasKey('modelG', $with['modelE']);
        $this->assertIsArray($with['modelE']['modelG']);
        $this->assertArrayHasKey('modelH', $with['modelE']['modelG']);
        $this->assertEmpty($with['modelE']['modelG']['modelH']);
    }
}
