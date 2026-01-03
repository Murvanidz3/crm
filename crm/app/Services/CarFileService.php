<?php

namespace App\Services;

use App\Models\Car;
use App\Models\CarFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

/**
 * Car File Service
 * 
 * Handles file uploads, compression, and thumbnail generation.
 */
class CarFileService
{
    private int $maxWidth;
    private int $quality;
    private array $imageTypes;
    private array $videoTypes;

    public function __construct()
    {
        $this->maxWidth = config('services.uploads.thumbnail_width', 1280);
        $this->quality = config('services.uploads.thumbnail_quality', 70);
        $this->imageTypes = config('services.uploads.allowed_image_types', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        $this->videoTypes = config('services.uploads.allowed_video_types', ['mp4', 'mov']);
    }

    /**
     * Upload and process file for car
     */
    public function uploadFile(Car $car, UploadedFile $file, string $category): CarFile
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $isImage = in_array($extension, $this->imageTypes);
        $isVideo = in_array($extension, $this->videoTypes);

        // Create upload directory based on VIN
        $uploadDir = 'uploads/' . $car->vin;
        $publicPath = public_path($uploadDir);
        
        if (!file_exists($publicPath)) {
            mkdir($publicPath, 0755, true);
        }

        // Generate unique filename
        $filename = time() . '_' . uniqid();

        if ($isImage) {
            $path = $this->processImage($file, $publicPath, $filename);
            $fileType = 'image';
        } elseif ($isVideo) {
            $path = $this->processVideo($file, $publicPath, $filename, $extension);
            $fileType = 'video';
        } else {
            $path = $this->processDocument($file, $publicPath, $filename, $extension);
            $fileType = 'document';
        }

        // Create database record
        return CarFile::create([
            'car_id' => $car->id,
            'file_path' => $uploadDir . '/' . basename($path),
            'file_type' => $fileType,
            'category' => $category,
        ]);
    }

    /**
     * Process and compress image
     */
    private function processImage(UploadedFile $file, string $dir, string $filename): string
    {
        $path = $dir . '/' . $filename . '.jpg';

        // Load and process image with Intervention Image
        $image = Image::read($file->getPathname());

        // Resize if wider than max width
        if ($image->width() > $this->maxWidth) {
            $image->scale(width: $this->maxWidth);
        }

        // Convert to JPEG and save
        $image->toJpeg($this->quality)->save($path);

        return $path;
    }

    /**
     * Process video file
     */
    private function processVideo(UploadedFile $file, string $dir, string $filename, string $extension): string
    {
        $path = $dir . '/' . $filename . '.' . $extension;
        $file->move($dir, $filename . '.' . $extension);
        return $path;
    }

    /**
     * Process document file
     */
    private function processDocument(UploadedFile $file, string $dir, string $filename, string $extension): string
    {
        $path = $dir . '/' . $filename . '.' . $extension;
        $file->move($dir, $filename . '.' . $extension);
        return $path;
    }

    /**
     * Delete file and its record
     */
    public function deleteFile(CarFile $file): bool
    {
        return $file->deleteWithFile();
    }

    /**
     * Generate thumbnail for existing image
     */
    public function generateThumbnail(string $path, int $width = 200): ?string
    {
        $fullPath = public_path($path);
        
        if (!file_exists($fullPath)) {
            return null;
        }

        $pathInfo = pathinfo($fullPath);
        $thumbPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];

        $image = Image::read($fullPath);
        $image->scale(width: $width);
        $image->toJpeg(70)->save($thumbPath);

        return str_replace(public_path(), '', $thumbPath);
    }
}
