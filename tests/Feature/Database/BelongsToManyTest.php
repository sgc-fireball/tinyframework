<?php declare(strict_types=1);

namespace TinyFramework\Tests\Feature\Database;

use TinyFramework\Database\BaseModel;
use TinyFramework\Database\MySQL\Database;
use TinyFramework\Database\Relations\BelongsToMany;
use TinyFramework\Tests\Feature\FeatureTestCase;

class BelongsToManyModelA extends BaseModel
{

    protected string $connection = 'mysql';
    protected string $table = 'test_model_a';
    protected array $fillable = ['id', 'name'];

    public function belongsToManyModelB(): BelongsToMany
    {
        return $this->belongsToMany(BelongsToManyModelB::class);
    }

}

class BelongsToManyModelB extends BaseModel
{

    protected string $connection = 'mysql';
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
        $database = $this->container->get('database');
        assert($database instanceof Database);
        $database->execute('DROP TABLE IF EXISTS `test_model_a`');
        $database->execute('DROP TABLE IF EXISTS `test_model_b`');
        $database->execute('DROP TABLE IF EXISTS `test_model_a_2_test_model_b`');
        $database->execute('CREATE TABLE IF NOT EXISTS `test_model_a` (`id` varchar(36) NOT NULL,`name` varchar(36) NOT NULL,PRIMARY KEY (`id`))');
        $database->execute('CREATE TABLE IF NOT EXISTS `test_model_b` (`id` varchar(36) NOT NULL,`name` varchar(36) NOT NULL,PRIMARY KEY (`id`))');
        $database->execute('CREATE TABLE IF NOT EXISTS `test_model_a_2_test_model_b` (`test_model_a_id` varchar(36) NOT NULL,`test_model_b_id` varchar(36) NOT NULL,PRIMARY KEY (`test_model_a_id`,`test_model_b_id`))');
    }

    public function testCouldNotFoundModels(): void
    {
        $this->assertEquals(0, BelongsToManyModelA::query()->count(), 'Found more then 0 BelongsToManyModelA entries.');
        $this->assertEquals(0, BelongsToManyModelB::query()->count(), 'Found more then 0 BelongsToManyModelB entries.');
    }

    public function testFoundModels(): void
    {
        $modelA = (new BelongsToManyModelA(['name' => 'modela']))->save();
        $modelB = (new BelongsToManyModelB(['name' => 'modelb']))->save();
        $this->assertEquals(1, BelongsToManyModelA::query()->count(), 'Found an invalid count for BelongsToManyModelA entries.');
        $this->assertEquals(1, BelongsToManyModelB::query()->count(), 'Found an invalid count for BelongsToManyModelB entries.');
    }

    public function testBelongsToMany(): void
    {
        $modelA = (new BelongsToManyModelA(['name' => 'modela']))->save();
        $modelAA = (new BelongsToManyModelA(['name' => 'modela']))->save();
        $modelB = (new BelongsToManyModelB(['name' => 'modelb']))->save();
        $modelBB = (new BelongsToManyModelB(['name' => 'modelb']))->save();
        $this->assertEquals(2, BelongsToManyModelA::query()->count(), 'Found an invalid count for BelongsToManyModelA entries.');
        $this->assertEquals(2, BelongsToManyModelB::query()->count(), 'Found an invalid count for BelongsToManyModelB entries.');
        /** @var Database $database */
        $database = container('database');
        $database->execute(sprintf(
            'INSERT INTO `test_model_a_2_test_model_b` SET test_model_a_id=%s, test_model_b_id=%s',
            $database->escape($modelA->id),
            $database->escape($modelB->id),
        ));
        $database->execute(sprintf(
            'INSERT INTO `test_model_a_2_test_model_b` SET test_model_a_id=%s, test_model_b_id=%s',
            $database->escape($modelAA->id),
            $database->escape($modelBB->id),
        ));
        $this->assertEquals(2, $database->query()->table('test_model_a_2_test_model_b')->count());

        $this->assertIsArray($modelA->belongsToManyModelB);
        $this->assertCount(1, $modelA->belongsToManyModelB);
        $this->assertEquals($modelB->id, $modelA->belongsToManyModelB[0]->id);
        $this->assertCount(1, $modelA->belongsToManyModelB->belongsToManyModelA);
        $this->assertEquals($modelA->id, $modelA->belongsToManyModelB[0]->belongsToManyModelA[0]->id);

        $this->assertIsArray($modelB->belongsToManyModelA);
        $this->assertCount(1, $modelB->belongsToManyModelA);
        $this->assertEquals($modelA->id, $modelB->belongsToManyModelA[0]->id);
        $this->assertCount(1, $modelB->belongsToManyModelA->belongsToManyModelB);
        $this->assertEquals($modelB->id, $modelB->belongsToManyModelA[0]->belongsToManyModelB[0]->id);
    }

}
