<?php

namespace App\Services\Utils;

use App\Models\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class FileService
{
    public function store(UploadedFile $file, string $directory): string
    {
        $extension = $file->getClientOriginalExtension();
        $fileName = date('Ymd_His') . '_' . Str::random(8) . '.' . $extension;
        $path = $file->storeAs($directory , $fileName, 'public');

        return $path;
    }

    public function delete(string $path): void
    {
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
