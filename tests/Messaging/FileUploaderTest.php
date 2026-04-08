<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use MasRodjie\LaravelWhatspie\Messaging\FileUploader;

beforeEach(function () {
    Storage::fake('public');
    $this->uploader = new FileUploader('public', 'whatspie');
});

test('returns url directly if already public url', function () {
    $url = 'https://example.com/image.jpg';

    $result = $this->uploader->upload($url);

    expect($result)->toBe($url);
});

test('uploads local file and returns url', function () {
    // Create a real temporary file
    $tempFile = tmpfile();
    fwrite($tempFile, 'content');
    $tempPath = stream_get_meta_data($tempFile)['uri'];

    $result = $this->uploader->upload($tempPath);

    expect($result)->toBeString();
    expect($result)->toContain('/storage/whatspie/');
    Storage::disk('public')->assertExists('whatspie/');

    // Clean up
    fclose($tempFile);
});

test('uploads UploadedFile and returns url', function () {
    $file = UploadedFile::fake()->create('document.pdf', 1000);

    $result = $this->uploader->upload($file);

    expect($result)->toBeString();
    Storage::disk('public')->assertExists('whatspie/');
});

test('generates unique filename for uploads', function () {
    $file = UploadedFile::fake()->create('photo.jpg', 500);

    $result1 = $this->uploader->upload($file);
    $result2 = $this->uploader->upload($file);

    expect($result1)->not->toBe($result2);
});
