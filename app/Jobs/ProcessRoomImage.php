<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\ImageService;
use Illuminate\Http\UploadedFile;

class ProcessRoomImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $file;
    protected $roomNumber;
    public $tries = 3;
    public $timeout = 120;

    public function __construct(UploadedFile $file, string $roomNumber)
    {
        $this->file = $file;
        $this->roomNumber = $roomNumber;
    }

    public function handle(ImageService $imageService): void
    {
        try {
            // Process and optimize the image
            $optimizedImage = $imageService->optimizeImage($this->file);
            
            // Upload the processed image
            $path = $imageService->uploadRoomImage($this->file, $this->roomNumber);
            
            // Add watermark
            $imageService->createWatermark($path, config('app.name'));

            // Clean up temporary files
            if (file_exists($this->file->getPathname())) {
                unlink($this->file->getPathname());
            }
        } catch (\Exception $e) {
            // Log error and retry
            \Log::error('Failed to process room image', [
                'error' => $e->getMessage(),
                'room_number' => $this->roomNumber
            ]);
            
            $this->release(30); // Retry after 30 seconds
        }
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('Room image processing job failed', [
            'error' => $exception->getMessage(),
            'room_number' => $this->roomNumber
        ]);
    }
} 