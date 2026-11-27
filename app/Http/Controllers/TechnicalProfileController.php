<?php

namespace App\Http\Controllers;

use App\Support\ProfilePhotoStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Throwable;

class TechnicalProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('responsable-technique.profile', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validateWithBag('profile', [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:40'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_profile_photo' => ['nullable', 'boolean'],
        ]);

        $previousPhoto = $user->profile_photo;
        $newPhoto = $request->hasFile('profile_photo')
            ? ProfilePhotoStorage::store($request->file('profile_photo'), 'responsables-techniques')
            : null;
        $emailChanged = $user->email !== $data['email'];

        unset($data['profile_photo'], $data['remove_profile_photo']);

        if ($newPhoto) {
            $data['profile_photo'] = $newPhoto;
        } elseif ($request->boolean('remove_profile_photo')) {
            $data['profile_photo'] = null;
        }

        if ($emailChanged) {
            $data['email_verified_at'] = null;
        }

        try {
            $user->forceFill($data)->saveOrFail();
        } catch (Throwable $exception) {
            if ($newPhoto) {
                Storage::disk('public')->delete($newPhoto);
            }

            throw $exception;
        }

        if ($previousPhoto !== $user->profile_photo) {
            ProfilePhotoStorage::deleteIfUnreferenced($previousPhoto, $user);
        }

        return redirect()->route('responsable-technique.profile')
            ->with('status', 'Votre profil a été mis à jour.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('passwordUpdate', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8), 'different:current_password'],
        ]);

        $request->user()->forceFill([
            'password' => Hash::make($data['password']),
        ])->saveOrFail();

        $request->session()->regenerate();

        return redirect()->route('responsable-technique.profile')
            ->with('password_status', 'Votre mot de passe a été modifié.');
    }
}
