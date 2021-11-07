<?php declare(strict_types=1);

namespace TinyFramework\Tests\Feature\Database;

use TinyFramework\Database\BaseModel;
use TinyFramework\Database\MySQL\Database;
use TinyFramework\Database\Relations\BelongsToMany;
use TinyFramework\Database\Relations\HasOne;
use TinyFramework\Tests\Feature\FeatureTestCase;

class HasOneModelA extends BaseModel
{

    protected string $connection = 'mysql';
    protected string $table = 'test_model_a';
    protected array $fillable = ['id', 'name'];

    public function modelB(): HasOne
    {
        return $this->hasOne(HasOneModelB::class);
    }

}

class HasOneModelB extends BaseModel
{

    protected string $connection = 'mysql';
    protected string $table = 'test_model_b';
    protected array $fillable = ['id', 'name', 'has_one_model_a_id'];

}

class HasOneTest extends FeatureTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $database = $this->container->get('database');
        assert($database instanceof Database);
        $database->execute('DROP TABLE IF EXISTS `test_model_a`');
        $database->execute('DROP TABLE IF EXISTS `test_model_b`');
        $database->execute('CREATE TABLE IF NOT EXISTS `test_model_a` (`id` varchar(36) NOT NULL,`name` varchar(36) NOT NULL,PRIMARY KEY (`id`))');
        $database->execute('CREATE TABLE IF NOT EXISTS `test_model_b` (`id` varchar(36) NOT NULL,`name` varchar(36) NOT NULL,`has_one_model_a_id` varchar(36) DEFAULT NULL,PRIMARY KEY (`id`))');
    }

    public function testCouldNotFoundModels(): void
    {
        $this->assertEquals(0, HasOneModelA::query()->count(), 'Found more then 0 HasOneModelA entries.');
        $this->assertEquals(0, HasOneModelB::query()->count(), 'Found more then 0 HasOneModelB entries.');
    }

    public function testFoundModels(): void
    {
        $modelA = (new HasOneModelA(['name' => 'modela']))->save();
        $modelB = (new HasOneModelB(['name' => 'modelb', 'has_one_model_a_id' => $modelA->id]))->save();
        $this->assertEquals(1, HasOneModelA::query()->count(), 'Found an invalid count for HasOneModelA entries.');
        $this->assertEquals(1, HasOneModelB::query()->count(), 'Found an invalid count for HasOneModelB entries.');
    }

    public function testHasOne(): void
    {
        $modelA = (new HasOneModelA(['name' => 'modela']))->save();
        $modelB = (new HasOneModelB(['name' => 'modelb', 'has_one_model_a_id' => $modelA->id]))->save();
        $this->assertEquals($modelB->id, $modelA->modelB->id);
        $this->assertEquals($modelA->id, $modelA->modelB->has_one_model_a_id);
    }

}
