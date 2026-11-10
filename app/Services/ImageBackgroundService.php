<?php

namespace App\Services;

use App\Models\StoredFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ImageBackgroundService
{
    /**
     * Process an uploaded product image: auto-removes near-white / solid light background
     * and saves as a transparent PNG in storage.
     */
    public function processAndStore(UploadedFile $file, string $disk = 'public', string $folder = 'products'): string
    {
        try {
            if (! extension_loaded('gd')) {
                $stored = $file->store($folder, $disk);
                StoredFile::persist($stored, Storage::disk($disk)->path($stored));
                return $stored;
            }

            $contents = file_get_contents($file->getRealPath());
            $src = @imagecreatefromstring($contents);
            if (! $src) {
                return $file->store($folder, $disk);
            }

            $width = imagesx($src);
            $height = imagesy($src);

            if ($width <= 0 || $height <= 0) {
                imagedestroy($src);
                return $file->store($folder, $disk);
            }

            // Sample corner colors (top-left, top-right, bottom-left, bottom-right)
            $corners = [
                imagecolorat($src, 0, 0),
                imagecolorat($src, max(0, $width - 1), 0),
                imagecolorat($src, 0, max(0, $height - 1)),
                imagecolorat($src, max(0, $width - 1), max(0, $height - 1)),
            ];

            $cornerRgbs = array_map(function ($c) use ($src) {
                return imagecolorsforindex($src, $c);
            }, $corners);

            // Check if corners are light / white (R,G,B all > 220)
            $isLightBg = collect($cornerRgbs)->every(function ($rgb) {
                return ($rgb['red'] ?? 0) > 220 && ($rgb['green'] ?? 0) > 220 && ($rgb['blue'] ?? 0) > 220;
            });

            if (! $isLightBg) {
                // If not a solid white/light background, store normally
                imagedestroy($src);
                return $file->store($folder, $disk);
            }

            // Create true color destination image with alpha transparency
            $dst = imagecreatetruecolor($width, $height);
            imagealphablending($dst, false);
            imagesavealpha($dst, true);

            $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefill($dst, 0, 0, $transparent);

            // Average corner color as reference
            $avgR = array_sum(array_column($cornerRgbs, 'red')) / 4;
            $avgG = array_sum(array_column($cornerRgbs, 'green')) / 4;
            $avgB = array_sum(array_column($cornerRgbs, 'blue')) / 4;

            $tolerance = 42;

            for ($x = 0; $x < $width; $x++) {
                for ($y = 0; $y < $height; $y++) {
                    $color = imagecolorat($src, $x, $y);
                    $rgba = imagecolorsforindex($src, $color);

                    $dist = sqrt(
                        pow(($rgba['red'] ?? 0) - $avgR, 2) +
                        pow(($rgba['green'] ?? 0) - $avgG, 2) +
                        pow(($rgba['blue'] ?? 0) - $avgB, 2)
                    );

                    if ($dist <= $tolerance || (($rgba['red'] ?? 0) > 242 && ($rgba['green'] ?? 0) > 242 && ($rgba['blue'] ?? 0) > 242)) {
                        // Transparent pixel
                        imagesetpixel($dst, $x, $y, $transparent);
                    } else {
                        // Keep original pixel
                        $pixelColor = imagecolorallocatealpha($dst, $rgba['red'], $rgba['green'], $rgba['blue'], $rgba['alpha'] ?? 0);
                        imagesetpixel($dst, $x, $y, $pixelColor);
                    }
                }
            }

            ob_start();
            imagepng($dst, null, 8);
            $pngData = ob_get_clean();

            imagedestroy($src);
            imagedestroy($dst);

            $filename = $folder . '/' . Str::random(40) . '.png';
            Storage::disk($disk)->put($filename, $pngData);
            StoredFile::persist($filename, Storage::disk($disk)->path($filename));

            return $filename;
        } catch (Throwable $e) {
            Log::warning('ImageBackgroundService fallback: ' . $e->getMessage());
            $stored = $file->store($folder, $disk);
            StoredFile::persist($stored, Storage::disk($disk)->path($stored));
            return $stored;
        }
    }
}

