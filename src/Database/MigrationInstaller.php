<?php

declare(strict_types=1);

namespace TinyFramework\Database;

use ReflectionClass;
use TinyFramework\Console\Output\OutputInterface;

class MigrationInstaller
{
    protected OutputInterface $output;

    protected DatabaseInterface $database;

    /** @var MigrationInterface[] */
    protected array $migrations;

    public function __construct(
        OutputInterface $output,
        DatabaseInterface $database,
        array $migrations = []
    ) {
        $this->output = $output;
        $this->database = $database;
        $this->migrations = array_filter($migrations, function ($class) {
            return $class instanceof MigrationInterface;
        });
        $this->loadAppMigration();
    }

    protected function loadAppMigration(): void
    {
        $folder = root_dir() . '/database/migrations';
        if (!is_dir($folder)) {
            return;
        }
        $files = glob($folder . '/Migration_*_*.php');
        sort($files);
        foreach ($files as $file) {
            require_once $file;
            $class = '\\' . str_replace('.php', '', basename($file));
            if (!($class instanceof MigrationInterface)) {
                continue;
            }
            $this->migrations[] = container()->call($class);
        }
    }

    protected function sortMigrations(): array
    {
        usort($this->migrations, function ($migrationA, $migrationB) {
            $timeA = (int)explode('_', (new ReflectionClass($migrationA))->getShortName(), 3)[1];
            $timeB = (int)explode('_', (new ReflectionClass($migrationB))->getShortName(), 3)[1];
            if ($timeA === $timeB) {
                return 0;
            }
            return $timeA <= $timeB ? -1 : 1;
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
        $migrated = array_map(
            function (array $row) {
                return $row['id'];
            },
            $migrated
        );

        $time = time();
        foreach ($this->migrations as $migration) {
            if (\in_array($migration::class, $migrated)) {
                continue;
            }

            try {
                $this->output->writeln($migration::class);
                $this->database->query()->transaction();
                $migration->up();
                $this->database->query()->table('migrations')->put([
                    'id' => \get_class($migration),
                    'batch' => $time,
                ]);
                $this->database->query()->commit();
            } catch (\Throwable $e) {
                $this->database->query()->rollback();
                throw $e;
            }
        }
    }

    public function down(): void
    {
        $migrations = array_reverse($this->sortMigrations());
        $batch = 0;
        $migrated = $this->getRan();
        array_walk($migrated, function (array $row) use (&$batch) {
            $batch = \intval(max($batch, $row['batch']));
        });

        $migrated = array_map(
            function (array $row) {
                return $row['id'];
            },
            array_filter($migrated, function (array $row) use (&$batch) {
                return $batch === \intval($row['batch']);
            })
        );

        foreach ($migrations as $migration) {
            if (!in_array($migration::class, $migrated)) {
                continue;
            }

            try {
                $this->output->writeln($migration::class);
                $this->database->query()->transaction();
                $migration->down();
                $this->database->query()
                    ->table('migrations')
                    ->where('id', '=', \get_class($migration))
                    ->delete();
                $this->database->query()->commit();
            } catch (\Throwable $e) {
                $this->database->query()->rollback();
                throw $e;
            }
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
