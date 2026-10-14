<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Alignment;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use RuntimeException;
use Throwable;

class ImageWatermarkService
{
    private ImageManager $imageManager;

    private const MAX_WIDTH = 2560;

    private const WATERMARK_WIDTH_RATIO = 0.5;

    private const WATERMARK_TRANSPARENCY = 0.68;

    public function __construct()
    {
        $this->imageManager = ImageManager::usingDriver(Driver::class);
    }

    public function process(UploadedFile $upload, string $directory): string
    {
        $image = $this->imageManager->decodePath($upload->getRealPath());
        $image->orient();
        $image->scaleDown(width: self::MAX_WIDTH);

        $watermarkPath = public_path('assets/png/mihom-wm.png');

        if (! is_file($watermarkPath)) {
            throw new RuntimeException('The property image watermark asset is unavailable.');
        }

        $watermark = $this->imageManager->decodePath($watermarkPath);
        $watermark->scale(width: max(1, (int) round($image->width() * self::WATERMARK_WIDTH_RATIO)));
        $image->insert($watermark, alignment: Alignment::CENTER, transparency: self::WATERMARK_TRANSPARENCY);

        $extension = match ($upload->getMimeType()) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => throw new RuntimeException('The uploaded image format is not supported.'),
        };

        $encodedImage = in_array($extension, ['jpg', 'webp'], true)
            ? (string) $image->encodeUsingFileExtension($extension, quality: 85)
            : (string) $image->encodeUsingFileExtension($extension);
        $path = trim($directory, '/').'/'.Str::uuid().'.'.$extension;
        $disk = Storage::disk('public');

        try {
            if (! $disk->put($path, $encodedImage)) {
                throw new RuntimeException('The watermarked property image could not be saved.');
            }
        } catch (Throwable $exception) {
            $disk->delete($path);

            throw $exception;
        }

        return $path;
    }
}
