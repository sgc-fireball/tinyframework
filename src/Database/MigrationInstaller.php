<?php declare(strict_types=1);

namespace TinyFramework\Database;

use ReflectionClass;

class MigrationInstaller
{

    protected DatabaseInterface $database;

    /** @var MigrationInterface[] */
    protected array $migrations;

    public function __construct(
        DatabaseInterface $database,
        array $migrations = []
    )
    {
        $this->database = $database;
        $this->migrations = $migrations;
    }

    protected function sortMigrations(): array
    {
        $this->migrations = array_filter($this->migrations, function ($class) {
            return $class instanceof MigrationInterface;
        });
        usort($this->migrations, function ($migrationA, $migrationB) {
            $timeA = (int)explode('_', (new ReflectionClass($migrationA))->getShortName())[1];
            $timeB = (int)explode('_', (new ReflectionClass($migrationB))->getShortName())[1];
            return $timeA === $timeB ? 0 : ($timeA <= $timeB ? -1 : 1);
        });
        return $this->migrations;
    }

    protected function setupMigrationModel(): DatabaseInterface
    {
        try {
            $this->database->query()->transaction();
            $this->database->createMigrationTable();
            $this->database->query()->commit();
        } catch (\Throwable $e) {
            $this->database->query()->rollback();
            throw $e;
        }
        return $this->database;
    }

    public function up(): void
    {
        $this->sortMigrations();
        $migrated = $this->getRan();
        try {
            $time = time();
            foreach ($this->migrations as $migration) {
                if (in_array($migration, $migrated)) {
                    continue;
                }
                $this->database->query()->transaction();
                $migration->up();
                $this->database->query()->table('migrations')->put([
                    'id' => get_class($migration),
                    'batch' => $time
                ]);
                $this->database->query()->commit();
            }
        } catch (\Throwable $e) {
            $this->database->query()->rollback();
            throw $e;
        }
    }

    public function down(): void
    {
        $migrations = array_reverse($this->sortMigrations());
        $batch = 0;
        $migrated = $this->getRan();
        array_walk($migrated, function (array $row) use (&$batch) {
            $batch = max($batch, $row['batch']);
        });
        $migrated = array_map(
            function (array $row) {
                return $row['id'];
            },
            array_filter($migrated, function (array $row) use (&$batch) {
                return $batch === $row['batch'];
            })
        );

        try {
            foreach ($migrations as $migration) {
                if (!in_array($migration, $migrated)) {
                    continue;
                }

                $this->database->query()->transaction();
                $migration->down();
                $this->database->query()
                    ->table('migrations')
                    ->where('id', '=', get_class($migration))
                    ->delete();
                $this->database->query()->commit();
            }
        } catch (\Throwable $e) {
            $this->database->query()->rollback();
            throw $e;
        }
    }

    private function getRan(): array
    {
        return $this->setupMigrationModel()
            ->query()
            ->table('migrations')
            ->orderBy('batch', 'desc')
            ->orderBy('id', 'desc')
            ->get();
    }

}
