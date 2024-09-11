<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Feature\Database;

use TinyFramework\Database\BaseModel;
use TinyFramework\Database\Relations\HasMany;
use TinyFramework\Tests\Feature\FeatureTestCase;

class HasManyModelA extends BaseModel
{
    protected string $table = 'test_model_a';
    protected array $fillable = ['id', 'name'];

    public function modelB(): HasMany
    {
        return $this->hasMany(HasManyModelB::class);
    }
}

class HasManyModelB extends BaseModel
{
    protected string $table = 'test_model_b';
    protected array $fillable = ['id', 'name', 'has_many_model_a_id'];
}

class HasManyTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $database = $this->container->get('database.' . config('database.default'));
        assert($database instanceof DatabaseInterface);
        $database->execute('DROP TABLE IF EXISTS `test_model_a`');
        $database->execute('DROP TABLE IF EXISTS `test_model_b`');
        $database->execute('CREATE TABLE IF NOT EXISTS `test_model_a` (`id` char(36) NOT NULL,`name` char(36) NOT NULL,PRIMARY KEY (`id`))');
        $database->execute('CREATE TABLE IF NOT EXISTS `test_model_b` (`id` char(36) NOT NULL,`name` char(36) NOT NULL,`has_many_model_a_id` char(36) DEFAULT NULL,PRIMARY KEY (`id`))');
    }

    public function testCouldNotFoundModels(): void
    {
        $this->assertEquals(0, HasManyModelA::query()->count(), 'Found more then 0 HasManyModelA entries.');
        $this->assertEquals(0, HasManyModelB::query()->count(), 'Found more then 0 HasManyModelB entries.');
    }

    public function testFoundModels(): void
    {
        $modelA = (new HasManyModelA(['name' => 'HasManyModelA']))->save();
        $modelB = (new HasManyModelB(['name' => 'HasManyModelB', 'has_many_model_a_id' => $modelA->id]))->save();
        $this->assertEquals(1, HasManyModelA::query()->count(), 'Found an invalid count for HasManyModelA entries.');
        $this->assertEquals(1, HasManyModelB::query()->count(), 'Found an invalid count for HasManyModelB entries.');
    }

    public function testHasMany(): void
    {
        $modelA = (new HasManyModelA(['name' => 'HasManyModelA']))->save();
        $modelB = (new HasManyModelB(['name' => 'HasManyModelB', 'has_many_model_a_id' => $modelA->id]))->save();
        $this->assertIsArray($modelA->modelB);
        $this->assertCount(1, $modelA->modelB);
        $this->assertEquals($modelB->id, $modelA->modelB[0]->id);
        $this->assertEquals($modelA->id, $modelA->modelB[0]->has_many_model_a_id);
    }

    public function testHasManyEagerLoading(): void
    {
        $modelA1 = (new HasManyModelA(['name' => 'modela1']))->save();
        $modelA2 = (new HasManyModelA(['name' => 'modela2']))->save();
        (new HasManyModelB(['name' => 'modelb1', 'has_many_model_a_id' => $modelA1->id]))->save();
        (new HasManyModelB(['name' => 'modelb2', 'has_many_model_a_id' => $modelA1->id]))->save();
        (new HasManyModelB(['name' => 'modelb3', 'has_many_model_a_id' => $modelA2->id]))->save();
        (new HasManyModelB(['name' => 'modelb4', 'has_many_model_a_id' => $modelA2->id]))->save();
        $modelAs = HasManyModelA::query()->with('modelB')->get();
        $this->assertIsArray($modelAs);
        $this->assertCount(2, $modelAs);
        $this->assertInstanceOf(HasManyModelA::class, $modelAs[0]);
        $this->assertInstanceOf(HasManyModelA::class, $modelAs[1]);
        $this->assertIsArray($modelAs[0]->modelB);
        $this->assertIsArray($modelAs[1]->modelB);
        $this->assertCount(2, $modelAs[0]->modelB);
        $this->assertInstanceOf(HasManyModelB::class, $modelAs[0]->modelB[0]);
        $this->assertInstanceOf(HasManyModelB::class, $modelAs[0]->modelB[1]);
        $this->assertCount(2, $modelAs[1]->modelB);
        $this->assertInstanceOf(HasManyModelB::class, $modelAs[1]->modelB[0]);
        $this->assertInstanceOf(HasManyModelB::class, $modelAs[1]->modelB[1]);
        $this->assertEquals($modelA1->id, $modelAs[0]->modelB[0]->has_many_model_a_id);
        $this->assertEquals($modelA1->id, $modelAs[0]->modelB[1]->has_many_model_a_id);
        $this->assertEquals($modelA2->id, $modelAs[1]->modelB[0]->has_many_model_a_id);
        $this->assertEquals($modelA2->id, $modelAs[1]->modelB[1]->has_many_model_a_id);
        // @TODO test database counts
    }
}
