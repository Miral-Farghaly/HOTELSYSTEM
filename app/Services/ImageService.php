<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ImageService
{
    protected $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(['driver' => 'gd']);
    }

    public function uploadRoomImage(UploadedFile $file, string $roomNumber): string
    {
        $filename = "room_{$roomNumber}_" . time() . '.' . $file->getClientOriginalExtension();
        $path = "rooms/{$filename}";

        $image = $this->manager->read($file);

        // Resize for standard display
        $image->scale(width: 800, height: 600);

        // Create thumbnail
        $thumbnail = clone $image;
        $thumbnail->scale(width: 200, height: 150);
        
        // Save both versions
        Storage::put("public/{$path}", $image->toJpeg());
        Storage::put("public/thumbnails/{$path}", $thumbnail->toJpeg());

        return $path;
    }

    public function uploadUserAvatar(UploadedFile $file, int $userId): string
    {
        $filename = "avatar_{$userId}_" . time() . '.' . $file->getClientOriginalExtension();
        $path = "avatars/{$filename}";

        $image = $this->manager->read($file);

        // Resize and crop to square
        $image->coverDown(300, 300);

        // Create thumbnail
        $thumbnail = clone $image;
        $thumbnail->scale(width: 50, height: 50);

        // Save both versions
        Storage::put("public/{$path}", $image->toJpeg());
        Storage::put("public/thumbnails/{$path}", $thumbnail->toJpeg());

        return $path;
    }

    public function deleteImage(string $path): bool
    {
        // Delete both original and thumbnail
        Storage::delete([
            "public/{$path}",
            "public/thumbnails/{$path}"
        ]);

        return true;
    }

    public function optimizeImage(UploadedFile $file): \Intervention\Image\Image
    {
        $image = $this->manager->read($file);

        // Optimize quality
        $image->quality(85);

        // Auto-orient based on EXIF data
        $image->orientate();

        return $image;
    }

    public function createWatermark(string $path, string $text): bool
    {
        $image = $this->manager->read(Storage::get("public/{$path}"));

        // Add watermark text
        $image->text($text, $image->width() / 2, $image->height() - 20, function ($font) {
            $font->size(20);
            $font->color('#ffffff');
            $font->align('center');
            $font->valign('bottom');
        });

        return Storage::put("public/{$path}", $image->toJpeg());
    }
} 