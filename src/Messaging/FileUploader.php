<?php

namespace MasRodjie\LaravelWhatspie\Messaging;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploader
{
    protected string $disk;
    protected string $path;

    public function __construct(string $disk, string $path)
    {
        $this->disk = $disk;
        $this->path = $path;
    }

    public function upload(string|UploadedFile $file): string
    {
        // If it's already a public URL, return as-is
        if (is_string($file) && $this->isPublicUrl($file)) {
            return $file;
        }

        // Upload UploadedFile
        if ($file instanceof UploadedFile) {
            return $this->uploadUploadedFile($file);
        }

        // Upload local file
        return $this->uploadLocalFile($file);
    }

    protected function isPublicUrl(string $path): bool
    {
        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://');
    }

    protected function uploadUploadedFile(UploadedFile $file): string
    {
        $fileName = $this->generateUniqueFileName($file->getClientOriginalExtension());
        $path = $file->storeAs($this->path, $fileName, ['disk' => $this->disk]);

        return Storage::disk($this->disk)->url($path);
    }

    protected function uploadLocalFile(string $path): string
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $fileName = $this->generateUniqueFileName($extension);

        $contents = file_get_contents($path);
        $fullPath = "{$this->path}/{$fileName}";

        Storage::disk($this->disk)->put($fullPath, $contents);

        return Storage::disk($this->disk)->url($fullPath);
    }

    protected function generateUniqueFileName(string $extension): string
    {
        return Str::random(40) . '.' . $extension;
    }
}
