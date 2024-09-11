<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Feature\Database;

use TinyFramework\Database\BaseModel;
use TinyFramework\Database\DatabaseInterface;
use TinyFramework\Database\Relations\BelongsToMany;
use TinyFramework\Tests\Feature\FeatureTestCase;

class BelongsToManyModelA extends BaseModel
{
    protected string $table = 'test_model_a';
    protected array $fillable = ['id', 'name'];

    public function belongsToManyModelB(): BelongsToMany
    {
        return $this->belongsToMany(BelongsToManyModelB::class);
    }
}

class BelongsToManyModelB extends BaseModel
{
    protected string $table = 'test_model_b';
    protected array $fillable = ['id', 'name'];

    public function belongsToManyModelA(): BelongsToMany
    {
        return $this->belongsToMany(BelongsToManyModelA::class);
    }
}

class BelongsToManyTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $database = $this->container->get('database.' . config('database.default'));
        assert($database instanceof DatabaseInterface);
        $database->execute('DROP TABLE IF EXISTS `test_model_a`');
        $database->execute('DROP TABLE IF EXISTS `test_model_b`');
        $database->execute('DROP TABLE IF EXISTS `test_model_a_2_test_model_b`');
        $database->execute(
            'CREATE TABLE IF NOT EXISTS `test_model_a` (`id` char(36) NOT NULL,`name` char(36) NOT NULL,PRIMARY KEY (`id`))'
        );
        $database->execute(
            'CREATE TABLE IF NOT EXISTS `test_model_b` (`id` char(36) NOT NULL,`name` char(36) NOT NULL,PRIMARY KEY (`id`))'
        );
        $database->execute(
            'CREATE TABLE IF NOT EXISTS `test_model_a_2_test_model_b` (`test_model_a_id` char(36) NOT NULL,`test_model_b_id` char(36) NOT NULL,PRIMARY KEY (`test_model_a_id`,`test_model_b_id`))'
        );
    }

    public function testCouldNotFoundModels(): void
    {
        $this->assertEquals(0, BelongsToManyModelA::query()->count(), 'Found more then 0 BelongsToManyModelA entries.');
        $this->assertEquals(0, BelongsToManyModelB::query()->count(), 'Found more then 0 BelongsToManyModelB entries.');
    }

    public function testFoundModels(): void
    {
        (new BelongsToManyModelA(['name' => 'modela']))->save();
        (new BelongsToManyModelB(['name' => 'modelb']))->save();
        $this->assertEquals(
            1,
            BelongsToManyModelA::query()->count(),
            'Found an invalid count for BelongsToManyModelA entries.'
        );
        $this->assertEquals(
            1,
            BelongsToManyModelB::query()->count(),
            'Found an invalid count for BelongsToManyModelB entries.'
        );
    }

    public function testBelongsToMany(): void
    {
        $modelA = (new BelongsToManyModelA(['name' => 'modela']))->save();
        $modelAA = (new BelongsToManyModelA(['name' => 'modela']))->save();
        $modelB = (new BelongsToManyModelB(['name' => 'modelb']))->save();
        $modelBB = (new BelongsToManyModelB(['name' => 'modelb']))->save();
        $this->assertEquals(
            2,
            BelongsToManyModelA::query()->count(),
            'Found an invalid count for BelongsToManyModelA entries.'
        );
        $this->assertEquals(
            2,
            BelongsToManyModelB::query()->count(),
            'Found an invalid count for BelongsToManyModelB entries.'
        );
        /** @var DatabaseInterface $database */
        $database = container('database');
        $database->execute(
            sprintf(
                'INSERT INTO `test_model_a_2_test_model_b` (test_model_a_id, test_model_b_id) VALUES (%s, %s)',
                $database->escape($modelA->id),
                $database->escape($modelB->id),
            )
        );
        $database->execute(
            sprintf(
                'INSERT INTO `test_model_a_2_test_model_b` (test_model_a_id, test_model_b_id) VALUES (%s, %s)',
                $database->escape($modelAA->id),
                $database->escape($modelBB->id),
            )
        );
        $this->assertEquals(2, $database->query()->table('test_model_a_2_test_model_b')->count());

        $this->assertIsArray($modelA->belongsToManyModelB);
        $this->assertCount(1, $modelA->belongsToManyModelB);
        $this->assertEquals($modelB->id, $modelA->belongsToManyModelB[0]->id);
        $this->assertCount(1, $modelA->belongsToManyModelB[0]->belongsToManyModelA);
        $this->assertEquals($modelA->id, $modelA->belongsToManyModelB[0]->belongsToManyModelA[0]->id);

        $this->assertIsArray($modelB->belongsToManyModelA);
        $this->assertCount(1, $modelB->belongsToManyModelA);
        $this->assertEquals($modelA->id, $modelB->belongsToManyModelA[0]->id);
        $this->assertCount(1, $modelB->belongsToManyModelA[0]->belongsToManyModelB);
        $this->assertEquals($modelB->id, $modelB->belongsToManyModelA[0]->belongsToManyModelB[0]->id);
    }

    public function testBelongsToManyEagerLoading(): void
    {
        $modelA1 = (new BelongsToManyModelA(['name' => 'modela1']))->save();
        $modelA2 = (new BelongsToManyModelA(['name' => 'modela2']))->save();
        $modelB1 = (new BelongsToManyModelB(['name' => 'modelb1']))->save();
        $modelB2 = (new BelongsToManyModelB(['name' => 'modelb2']))->save();
        $modelB3 = (new BelongsToManyModelB(['name' => 'modelb3']))->save();
        $modelB4 = (new BelongsToManyModelB(['name' => 'modelb4']))->save();

        /** @var DatabaseInterface $database */
        $database = container('database');
        $database->execute(
            sprintf(
                'INSERT INTO `test_model_a_2_test_model_b` (test_model_a_id, test_model_b_id) VALUES (%s, %s)',
                $database->escape($modelA1->id),
                $database->escape($modelB1->id),
            )
        );
        $database->execute(
            sprintf(
                'INSERT INTO `test_model_a_2_test_model_b` (test_model_a_id, test_model_b_id) VALUES (%s, %s)',
                $database->escape($modelA1->id),
                $database->escape($modelB2->id),
            )
        );
        $database->execute(
            sprintf(
                'INSERT INTO `test_model_a_2_test_model_b` (test_model_a_id, test_model_b_id) VALUES (%s, %s)',
                $database->escape($modelA2->id),
                $database->escape($modelB3->id),
            )
        );
        $database->execute(
            sprintf(
                'INSERT INTO `test_model_a_2_test_model_b` (test_model_a_id, test_model_b_id) VALUES (%s, %s)',
                $database->escape($modelA2->id),
                $database->escape($modelB4->id),
            )
        );

        $modelAs = BelongsToManyModelA::query()->with('belongsToManyModelB')->get();
        $this->assertIsArray($modelAs);
        $this->assertCount(2, $modelAs);
        $this->assertInstanceOf(BelongsToManyModelA::class, $modelAs[0]);
        $this->assertInstanceOf(BelongsToManyModelA::class, $modelAs[1]);
        $this->assertIsArray($modelAs[0]->belongsToManyModelB);
        $this->assertIsArray($modelAs[1]->belongsToManyModelB);
        $this->assertCount(2, $modelAs[0]->belongsToManyModelB);
        $this->assertCount(2, $modelAs[1]->belongsToManyModelB);
        // @TODO test database counts
    }
}
