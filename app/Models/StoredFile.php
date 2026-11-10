<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Throwable;

class StoredFile extends Model
{
    protected $fillable = [
        'path',
        'mime_type',
        'content',
        'size',
    ];

    /**
     * Store or update a file payload in the database for permanent persistence across ephemeral restarts.
     */
    public static function persist(string $path, string $absoluteOrDiskPath, ?string $mimeType = null): ?self
    {
        try {
            $realPath = $absoluteOrDiskPath;
            if (! file_exists($realPath)) {
                if (Storage::disk('public')->exists($absoluteOrDiskPath)) {
                    $realPath = Storage::disk('public')->path($absoluteOrDiskPath);
                }
            }

            if (! file_exists($realPath) || ! is_readable($realPath)) {
                return null;
            }

            $rawContent = file_get_contents($realPath);
            if ($rawContent === false) {
                return null;
            }

            $base64 = base64_encode($rawContent);
            $mime = $mimeType ?: (File::mimeType($realPath) ?: 'image/png');
            $size = strlen($rawContent);

            return self::updateOrCreate(
                ['path' => $path],
                [
                    'mime_type' => $mime,
                    'content' => $base64,
                    'size' => $size,
                ]
            );
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Ensure a file exists on local public storage by restoring it from the database if missing.
     */
    public static function ensureOnDisk(string $path): bool
    {
        try {
            if (Storage::disk('public')->exists($path)) {
                return true;
            }

            $record = self::where('path', $path)->first();
            if (! $record || empty($record->content)) {
                return false;
            }

            $decoded = base64_decode($record->content);
            if ($decoded === false) {
                return false;
            }

            Storage::disk('public')->put($path, $decoded);
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Get a data URI fallback if the file cannot be written to disk.
     */
    public static function getDataUri(string $path): ?string
    {
        try {
            $record = self::where('path', $path)->first();
            if (! $record || empty($record->content)) {
                return null;
            }

            return "data:{$record->mime_type};base64,{$record->content}";
        } catch (Throwable) {
            return null;
        }
    }
}
