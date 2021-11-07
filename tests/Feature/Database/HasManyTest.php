<?php declare(strict_types=1);

namespace TinyFramework\Tests\Feature\Database;

use TinyFramework\Database\BaseModel;
use TinyFramework\Database\MySQL\Database;
use TinyFramework\Database\Relations\HasMany;
use TinyFramework\Tests\Feature\FeatureTestCase;

class HasManyModelA extends BaseModel
{

    protected string $connection = 'mysql';
    protected string $table = 'test_model_a';
    protected array $fillable = ['id', 'name'];

    public function modelB(): HasMany
    {
        return $this->hasMany(HasManyModelB::class);
    }

}

class HasManyModelB extends BaseModel
{

    protected string $connection = 'mysql';
    protected string $table = 'test_model_b';
    protected array $fillable = ['id', 'name', 'has_many_model_a_id'];

}

class HasManyTest extends FeatureTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $database = $this->container->get('database');
        assert($database instanceof Database);
        $database->execute('DROP TABLE IF EXISTS `test_model_a`');
        $database->execute('DROP TABLE IF EXISTS `test_model_b`');
        $database->execute('CREATE TABLE IF NOT EXISTS `test_model_a` (`id` varchar(36) NOT NULL,`name` varchar(36) NOT NULL,PRIMARY KEY (`id`))');
        $database->execute('CREATE TABLE IF NOT EXISTS `test_model_b` (`id` varchar(36) NOT NULL,`name` varchar(36) NOT NULL,`has_many_model_a_id` varchar(36) DEFAULT NULL,PRIMARY KEY (`id`))');
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

    public function testHasOne(): void
    {
        $modelA = (new HasManyModelA(['name' => 'HasManyModelA']))->save();
        $modelB = (new HasManyModelB(['name' => 'HasManyModelB', 'has_many_model_a_id' => $modelA->id]))->save();
        $this->assertIsArray($modelA->modelB);
        $this->assertCount(1, $modelA->modelB);
        $this->assertEquals($modelB->id, $modelA->modelB[0]->id);
        $this->assertEquals($modelA->id, $modelA->modelB[0]->has_many_model_a_id);
    }

}
