<?php

namespace App\Console\Commands;

use App\Models\StoredFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SyncStoredFilesCommand extends Command
{
    protected $signature = 'storage:sync-db';
    protected $description = 'Synchronize public uploaded files between local disk and database for permanent persistence';

    public function handle(): int
    {
        $this->info('Starting database storage synchronization...');

        // 1. Restore any missing files on disk from database
        $storedRecords = StoredFile::all();
        $restoredCount = 0;
        foreach ($storedRecords as $record) {
            if (! Storage::disk('public')->exists($record->path)) {
                if (StoredFile::ensureOnDisk($record->path)) {
                    $restoredCount++;
                }
            }
        }
        $this->info("Restored {$restoredCount} missing files to public disk from database.");

        // 2. Persist any local public files to database if not yet stored
        $publicPath = storage_path('app/public');
        $persistedCount = 0;
        if (File::isDirectory($publicPath)) {
            $files = File::allFiles($publicPath);
            foreach ($files as $file) {
                $relPath = str_replace('\\', '/', $file->getRelativePathname());
                if ($file->getFilename() === '.gitkeep') {
                    continue;
                }
                $existing = StoredFile::where('path', $relPath)->first();
                if (! $existing) {
                    if (StoredFile::persist($relPath, $file->getRealPath())) {
                        $persistedCount++;
                    }
                }
            }
        }
        $this->info("Persisted {$persistedCount} new local files to database.");

        return Command::SUCCESS;
    }
}

