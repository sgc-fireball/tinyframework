<?php

declare(strict_types=1);

namespace TinyFramework\Database;

interface MigrationInterface
{
    public function up(): void;

    public function down(): void;
}
