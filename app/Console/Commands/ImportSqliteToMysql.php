<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;
use Throwable;

class ImportSqliteToMysql extends Command
{
    protected $signature = 'academyhub:import-sqlite
        {--path= : Path to sqlite db (default: database/database.sqlite)}
        {--chunk=500 : Rows per batch}
        {--only= : Comma-separated list of tables to import}
        {--skip= : Comma-separated list of tables to skip}
        {--dry-run : Show what would be imported without writing}';

    protected $description = 'Import data from the legacy sqlite database into the current MySQL database (safe upsert).';

    public function handle(): int
    {
        $path = $this->option('path') ?: database_path('database.sqlite');
        $chunkSize = max(1, (int) $this->option('chunk'));
        $dryRun = (bool) $this->option('dry-run');

        if (! file_exists($path)) {
            $this->error("sqlite db not found: {$path}");
            return self::FAILURE;
        }

        if (config('database.default') !== 'mysql') {
            $this->error('This command expects the current DB connection to be MySQL.');
            $this->line('Set `DB_CONNECTION=mysql` in `.env` and run `php artisan config:clear`.');
            return self::FAILURE;
        }

        $only = $this->csvOption('only');
        $skip = $this->csvOption('skip');

        $sqlite = new PDO('sqlite:' . $path);
        $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $tables = $this->sqliteTables($sqlite);
        if ($only) {
            $tables = array_values(array_intersect($tables, $only));
        }
        if ($skip) {
            $tables = array_values(array_diff($tables, $skip));
        }

        if (! $tables) {
            $this->warn('No tables to import.');
            return self::SUCCESS;
        }

        $this->info('Import source: ' . $path);
        $this->info('Tables: ' . implode(', ', $tables));
        $this->info('Mode: ' . ($dryRun ? 'dry-run' : 'write'));

        $mysqlDb = (string) config('database.connections.mysql.database');
        $mysqlHost = (string) config('database.connections.mysql.host');
        $this->line("Target MySQL: {$mysqlHost} / {$mysqlDb}");

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            foreach ($tables as $table) {
                $this->importTable($sqlite, $table, $chunkSize, $dryRun);
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->info('Done.');
        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function sqliteTables(PDO $sqlite): array
    {
        $rows = $sqlite
            ->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")
            ->fetchAll(PDO::FETCH_ASSOC);

        return array_values(array_map(static fn ($r) => (string) $r['name'], $rows));
    }

    /**
     * @return list<string>
     */
    private function csvOption(string $name): array
    {
        $raw = (string) ($this->option($name) ?? '');
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }

        return array_values(array_filter(array_map(static fn ($v) => trim($v), explode(',', $raw))));
    }

    private function importTable(PDO $sqlite, string $table, int $chunkSize, bool $dryRun): void
    {
        if (! $this->mysqlTableExists($table)) {
            $this->warn("Skip {$table}: missing in MySQL");
            return;
        }

        $sqliteColumns = $this->sqliteColumns($sqlite, $table);
        $mysqlColumns = $this->mysqlColumns($table);
        $commonColumns = array_values(array_intersect($sqliteColumns, $mysqlColumns));

        if (! $commonColumns) {
            $this->warn("Skip {$table}: no common columns");
            return;
        }

        $uniqueBy = $this->sqlitePrimaryKeyColumns($sqlite, $table);
        $uniqueBy = array_values(array_intersect($uniqueBy, $commonColumns));
        if (! $uniqueBy) {
            if (in_array('id', $commonColumns, true)) {
                $uniqueBy = ['id'];
            } else {
                $this->warn("Skip {$table}: no primary key / unique key detected");
                return;
            }
        }

        $updateColumns = array_values(array_diff($commonColumns, $uniqueBy));

        try {
            $count = (int) $sqlite->query('SELECT COUNT(*) c FROM "' . str_replace('"', '""', $table) . '"')->fetchColumn();
        } catch (Throwable $e) {
            $this->warn("Skip {$table}: failed to count rows ({$e->getMessage()})");
            return;
        }

        $this->line("Import {$table}: {$count} rows");
        $this->line('  columns=' . count($commonColumns) . ' uniqueBy=' . implode(',', $uniqueBy));

        if ($count === 0) {
            return;
        }

        $offset = 0;
        while ($offset < $count) {
            $rows = $sqlite
                ->query('SELECT * FROM "' . str_replace('"', '""', $table) . "\" LIMIT {$chunkSize} OFFSET {$offset}")
                ->fetchAll(PDO::FETCH_ASSOC);

            if (! $rows) {
                break;
            }

            $payload = [];
            foreach ($rows as $row) {
                $item = [];
                foreach ($commonColumns as $col) {
                    if (array_key_exists($col, $row)) {
                        $item[$col] = $row[$col];
                    }
                }
                $payload[] = $item;
            }

            if (! $dryRun) {
                try {
                    DB::table($table)->upsert($payload, $uniqueBy, $updateColumns);
                } catch (Throwable $e) {
                    $this->warn("  upsert failed for {$table} offset={$offset} ({$e->getMessage()})");
                    $this->warn('  Tip: try `--only=' . $table . '` and fix that table first.');
                    break;
                }
            }

            $offset += count($payload);
        }
    }

    private function mysqlTableExists(string $table): bool
    {
        try {
            return DB::getSchemaBuilder()->hasTable($table);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return list<string>
     */
    private function sqliteColumns(PDO $sqlite, string $table): array
    {
        $stmt = $sqlite->query('PRAGMA table_info("' . str_replace('"', '""', $table) . '")');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_values(array_map(static fn ($r) => (string) $r['name'], $rows));
    }

    /**
     * @return list<string>
     */
    private function mysqlColumns(string $table): array
    {
        $rows = DB::select('SHOW COLUMNS FROM `' . str_replace('`', '``', $table) . '`');
        $cols = [];
        foreach ($rows as $r) {
            $cols[] = (string) ($r->Field ?? $r['Field'] ?? '');
        }
        return array_values(array_filter($cols));
    }

    /**
     * @return list<string>
     */
    private function sqlitePrimaryKeyColumns(PDO $sqlite, string $table): array
    {
        $stmt = $sqlite->query('PRAGMA table_info("' . str_replace('"', '""', $table) . '")');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pk = [];
        foreach ($rows as $r) {
            $order = (int) ($r['pk'] ?? 0);
            if ($order > 0) {
                $pk[$order] = (string) $r['name'];
            }
        }

        if (! $pk) {
            return [];
        }

        ksort($pk);
        return array_values($pk);
    }
}

