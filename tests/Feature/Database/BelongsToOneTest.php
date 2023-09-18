<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Feature\Database;

use TinyFramework\Database\BaseModel;
use TinyFramework\Database\MySQL\Database;
use TinyFramework\Database\Relations\BelongsToOne;
use TinyFramework\Tests\Feature\FeatureTestCase;

class BelongsToOneModelA extends BaseModel
{
    protected string $connection = 'mysql';
    protected string $table = 'test_model_a';
    protected array $fillable = ['id', 'name'];
}

class BelongsToOneModelB extends BaseModel
{
    protected string $connection = 'mysql';
    protected string $table = 'test_model_b';
    protected array $fillable = ['id', 'name', 'belongs_to_one_model_a_id'];

    public function belongsToOneModelA(): BelongsToOne
    {
        return $this->belongsToOne(BelongsToOneModelA::class);
    }
}

class BelongsToOneTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $database = $this->container->get('database');
        assert($database instanceof Database);
        $database->execute('DROP TABLE IF EXISTS `test_model_a`');
        $database->execute('DROP TABLE IF EXISTS `test_model_b`');
        $database->execute('CREATE TABLE IF NOT EXISTS `test_model_a` (`id` char(36) NOT NULL,`name` char(36) NOT NULL,PRIMARY KEY (`id`))');
        $database->execute('CREATE TABLE IF NOT EXISTS `test_model_b` (`id` char(36) NOT NULL,`name` char(36) NOT NULL,`belongs_to_one_model_a_id` char(36) DEFAULT NULL,PRIMARY KEY (`id`))');
    }

    public function testCouldNotFoundModels(): void
    {
        $this->assertEquals(0, BelongsToOneModelA::query()->count(), 'Found more then 0 BelongsToOneModelA entries.');
        $this->assertEquals(0, BelongsToOneModelB::query()->count(), 'Found more then 0 BelongsToOneModelB entries.');
    }

    public function testFoundModels(): void
    {
        $modelA = (new BelongsToOneModelA(['name' => 'modela']))->save();
        $modelB = (new BelongsToOneModelB(['name' => 'modelb', 'belongs_to_one_model_a_id' => $modelA->id]))->save();
        $this->assertEquals(1, BelongsToOneModelA::query()->count(), 'Found an invalid count for BelongsToOneModelA entries.');
        $this->assertEquals(1, BelongsToOneModelB::query()->count(), 'Found an invalid count for BelongsToOneModelB entries.');
    }

    public function testBelongsToOne(): void
    {
        $modelA = (new BelongsToOneModelA(['name' => 'modela']))->save();
        $modelB = (new BelongsToOneModelB(['name' => 'modelb', 'belongs_to_one_model_a_id' => $modelA->id]))->save();
        $this->assertEquals($modelB->belongs_to_one_model_a_id, $modelA->id);
        $this->assertEquals($modelA->id, $modelB->belongsToOneModelA->id);
    }

    public function testBelongsToOneEagerLoading(): void
    {
        $modelA1 = (new BelongsToOneModelA(['name' => 'modela1']))->save();
        $modelA2 = (new BelongsToOneModelA(['name' => 'modela2']))->save();
        (new BelongsToOneModelB(['name' => 'modelb1', 'belongs_to_one_model_a_id' => $modelA1->id]))->save();
        (new BelongsToOneModelB(['name' => 'modelb2', 'belongs_to_one_model_a_id' => $modelA1->id]))->save();
        (new BelongsToOneModelB(['name' => 'modelb3', 'belongs_to_one_model_a_id' => $modelA2->id]))->save();
        (new BelongsToOneModelB(['name' => 'modelb4', 'belongs_to_one_model_a_id' => $modelA2->id]))->save();
        $modelBs = BelongsToOneModelB::query()->with('belongsToOneModelA')->get();
        $this->assertIsArray($modelBs);
        $this->assertCount(4, $modelBs);
        $this->assertInstanceOf(BelongsToOneModelB::class, $modelBs[0]);
        $this->assertInstanceOf(BelongsToOneModelB::class, $modelBs[1]);
        $this->assertInstanceOf(BelongsToOneModelB::class, $modelBs[2]);
        $this->assertInstanceOf(BelongsToOneModelB::class, $modelBs[3]);
        $this->assertInstanceOf(BelongsToOneModelA::class, $modelBs[0]->belongsToOneModelA);
        $this->assertInstanceOf(BelongsToOneModelA::class, $modelBs[1]->belongsToOneModelA);
        $this->assertInstanceOf(BelongsToOneModelA::class, $modelBs[2]->belongsToOneModelA);
        $this->assertInstanceOf(BelongsToOneModelA::class, $modelBs[3]->belongsToOneModelA);
        // @TODO test database counts
    }
}
