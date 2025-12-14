<?php

namespace RingleSoft\LaravelProcessApproval\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process-approval:install {--uuids} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize the Process Approval Package';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $force = (bool) $this->option('force');
        $useUuids = (bool) $this->option('uuids');

        if (!$this->option('uuids')) {
            $useUuids = confirm('Use UUIDs for process approval tables (requires UUID primary keys for users/roles and approvable models)?', false);
        }

        $this->call('vendor:publish', array_filter([
            '--tag' => 'approvals-config',
            '--force' => $force ? true : null,
        ]));

        $this->publishMigrations($useUuids, $force);

        $this->updatePublishedConfig($useUuids);
        info('Process Approval installed successfully.');
    }

    private function publishMigrations(bool $useUuids, bool $force): void
    {
        $sourceDir = $useUuids
            ? __DIR__ . '/../../../database/migrations_uuid'
            : __DIR__ . '/../../../database/migrations';

        if (!File::isDirectory($sourceDir)) {
            $this->error('Migration source directory not found: ' . $sourceDir);
            return;
        }

        $destinationDir = database_path('migrations');
        if (!File::isDirectory($destinationDir)) {
            File::makeDirectory($destinationDir, 0755, true);
        }

        $suffixes = collect(File::files($sourceDir))
            ->filter(static fn ($file) => Str::endsWith($file->getFilename(), '.php'))
            ->map(static function ($file) {
                $originalName = $file->getFilename();
                return preg_replace('/^\d+_/', '', $originalName) ?? $originalName;
            })
            ->values();

        $destinationFiles = collect(File::files($destinationDir));
        $existing = $destinationFiles
            ->map(static fn ($file) => $file->getFilename())
            ->filter(static fn ($name) => $suffixes->contains(static fn ($suffix) => Str::endsWith($name, $suffix)))
            ->values();

        if ($existing->isNotEmpty() && !$force) {
            $this->warn('Process approval migrations already exist in your application. Use --force to overwrite.');
            return;
        }

        if ($existing->isNotEmpty() && $force) {
            foreach ($destinationFiles as $file) {
                $filename = $file->getFilename();
                if ($suffixes->contains(static fn ($suffix) => Str::endsWith($filename, $suffix))) {
                    File::delete($file->getPathname());
                }
            }
        }

        $files = collect(File::files($sourceDir))
            ->filter(static fn ($file) => Str::endsWith($file->getFilename(), '.php'))
            ->sortBy(static fn ($file) => $file->getFilename())
            ->values();

        $now = Carbon::now();
        foreach ($files as $index => $file) {
            $originalName = $file->getFilename();
            $suffix = preg_replace('/^\d+_/', '', $originalName) ?? $originalName;

            $timestamp = $now->copy()->addSeconds($index)->format('Y_m_d_His');
            $targetName = $timestamp . '_' . $suffix;
            $targetPath = $destinationDir . DIRECTORY_SEPARATOR . $targetName;

            if (File::exists($targetPath) && !$force) {
                continue;
            }

            File::put($targetPath, File::get($file->getPathname()));
        }
    }

    private function updatePublishedConfig(bool $useUuids): void
    {
        $path = config_path('process_approval.php');
        if (!File::exists($path)) {
            return;
        }

        $contents = File::get($path);

        if (preg_match("/'use_uuids'\s*=>/", $contents)) {
            $contents = preg_replace(
                "/'use_uuids'\s*=>\s*(true|false)/",
                "'use_uuids' => " . ($useUuids ? 'true' : 'false'),
                $contents,
                1
            );
        } else {
            $contents = preg_replace('/\];\s*$/', "    'use_uuids' => " . ($useUuids ? 'true' : 'false') . ",\n];\n", $contents, 1);
        }

        if (preg_match("/'load_migrations'\s*=>/", $contents)) {
            $contents = preg_replace(
                "/'load_migrations'\s*=>\s*(true|false)/",
                "'load_migrations' => false",
                $contents,
                1
            );
        } else {
            $contents = preg_replace('/\];\s*$/', "    'load_migrations' => false,\n];\n", $contents, 1);
        }

        File::put($path, $contents);
    }
}
