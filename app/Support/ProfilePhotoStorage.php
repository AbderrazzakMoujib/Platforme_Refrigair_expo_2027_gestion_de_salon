<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ProfilePhotoStorage
{
    public static function store(UploadedFile $photo, string $directory = 'responsables-conferences'): string
    {
        $path = $photo->store($directory, 'public');

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('La photo de profil n’a pas pu être enregistrée.');
        }

        return $path;
    }

    public static function deleteIfUnreferenced(?string $path, User $except): void
    {
        if (! $path) {
            return;
        }

        $isShared = User::query()
            ->where('profile_photo', $path)
            ->whereKeyNot($except->getKey())
            ->exists();

        if (! $isShared) {
            Storage::disk('public')->delete($path);
        }
    }
}
