<?php

namespace App\Support;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Image\Image as LaravelImage;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ImageService
{
    public const DISK = 'public';

    public const MAX_WIDTH = 1920;

    public const WEBP_QUALITY = 82;

    public const JPG_QUALITY = 85;

    /**
     * Compress and store an uploaded image as WebP (JPG fallback).
     * No hard file-size reject — large files are compressed first.
     */
    public function store(UploadedFile $file, string $directory, ?string $oldPath = null): string
    {
        $this->assertValidImage($file);

        $image = Image::fromUpload($file);
        $this->resizeIfNeeded($image);

        $path = $this->persist($image, $directory);

        if ($oldPath) {
            $this->delete($oldPath);
        }

        return $path;
    }

    /**
     * Compress a local filesystem image path (used by seeders/imports).
     */
    public function storeFromPath(string $sourcePath, string $directory, ?string $oldPath = null): string
    {
        if (! is_file($sourcePath)) {
            throw new RuntimeException(ErrorCatalog::message('image_invalid'));
        }

        $image = Image::fromPath($sourcePath);
        $this->resizeIfNeeded($image);

        $path = $this->persist($image, $directory);

        if ($oldPath) {
            $this->delete($oldPath);
        }

        return $path;
    }

    /**
     * Delete a stored image path if it exists on the public disk.
     */
    public function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (Storage::disk(self::DISK)->exists($path)) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    /**
     * Replace an existing image with a newly uploaded compressed one.
     */
    public function replace(UploadedFile $file, string $directory, ?string $oldPath): string
    {
        return $this->store($file, $directory, $oldPath);
    }

    public function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk(self::DISK);

        return $disk->url($path);
    }

    protected function persist(LaravelImage $image, string $directory): string
    {
        $relativeDir = trim($directory, '/');
        $filename = Str::uuid()->toString();

        try {
            $path = "{$relativeDir}/{$filename}.webp";
            $image->quality(self::WEBP_QUALITY)->toWebp()->storePubliclyAs(
                $relativeDir,
                "{$filename}.webp",
                self::DISK,
            );

            return $path;
        } catch (\Throwable) {
            $path = "{$relativeDir}/{$filename}.jpg";
            $image->quality(self::JPG_QUALITY)->toJpeg()->storePubliclyAs(
                $relativeDir,
                "{$filename}.jpg",
                self::DISK,
            );

            return $path;
        }
    }

    protected function assertValidImage(UploadedFile $file): void
    {
        $mime = (string) $file->getMimeType();

        if (! str_starts_with($mime, 'image/')) {
            throw new RuntimeException(ErrorCatalog::message('image_invalid'));
        }
    }

    protected function resizeIfNeeded(LaravelImage $image): void
    {
        if ($image->width() > self::MAX_WIDTH) {
            $image->scale(width: self::MAX_WIDTH);
        }
    }
}
