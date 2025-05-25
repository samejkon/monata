<?php

namespace App\Services\Utils;

use App\Models\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class FileService
{
    public function store(UploadedFile $file, string $directory): string
    {
        $extension = $file->getClientOriginalExtension();
        $fileName = date('Ymd_His') . '_' . Str::random(8) . '.' . $extension;
        $path = $file->storeAs($directory , $fileName, 'public');

        return $path;
    }
}