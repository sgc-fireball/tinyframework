<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Feature\Database;

use TinyFramework\Database\BaseModel;
use TinyFramework\Database\MySQL\Database;
use TinyFramework\Tests\Feature\FeatureTestCase;

class TestModel extends BaseModel
{
    protected string $connection = 'mysql';
    protected string $table = 'test_model';
    protected array $fillable = ['id', 'name'];
}

class BaseModelTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $database = $this->container->get('database');
        assert($database instanceof Database);
        $database->execute('DROP TABLE IF EXISTS `test_model`');
        $database->execute(
            'CREATE TABLE IF NOT EXISTS `test_model` (`id` char(36) NOT NULL,`name` varchar(255) NOT NULL,PRIMARY KEY (`id`))'
        );
    }

    public function tearDown(): void
    {
        $database = $this->container->get('database');
        assert($database instanceof Database);
        $database->execute('DROP TABLE IF EXISTS `test_model`');
        parent::tearDown();
    }

    public function testCouldNotFoundModels(): void
    {
        $this->assertEquals(0, TestModel::query()->count(), 'Found more then 0 TestModel entries.');
    }

    public function testFoundModels(): void
    {
        (new TestModel(['name' => 'model']))->save();
        $this->assertEquals(1, TestModel::query()->count(), 'Found an invalid count for TestModel entries.');
    }

    public function testReload(): void
    {
        $model = (new TestModel(['name' => 'model']))->save();
        $model->name = 'reload';
        $model->reload();
        $this->assertEquals('model', $model->name, 'The model->name must be "model".');
    }
}
