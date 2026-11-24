<?php

namespace App\Http\Controllers;

use App\Enums\SlotRequestStatus;
use App\Http\Requests\Slots\AcceptSlotRequestForm;
use App\Http\Requests\Slots\RejectSlotRequestForm;
use App\Models\Slot;
use App\Models\SlotRequest;
use App\Models\SlotRequestDecision;
use App\Models\Space;
use App\Services\SlotService;
use App\Support\ProfilePhotoStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ResponsableConferenceController extends Controller
{
    public function dashboard(): View
    {
        return view('responsable-conferences.dashboard', [
            'pendingCount' => SlotRequest::query()->where('status', SlotRequestStatus::Pending)->count(),
            'confirmedCount' => SlotRequest::query()->where('status', SlotRequestStatus::Accepted)->count(),
            'decisionCount' => SlotRequestDecision::query()->count(),
            'upcomingSlots' => Slot::query()->with('space:id,type,name')->ordered()->limit(8)->get(),
        ]);
    }

    public function planning(): View
    {
        return view('responsable-conferences.planning', [
            'spaces' => Space::query()->orderBy('id')->get(),
            'slots' => Slot::query()->with(['space:id,type,name', 'exhibitor:id,company_name,commercial_id', 'exhibitor.commercial:id,name', 'commercial:id,name'])->ordered()->get(),
        ]);
    }

    public function requests(Request $request): View
    {
        Gate::authorize('viewAny', SlotRequest::class);
        $status = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'accepted', 'rejected', 'all'])],
        ])['status'] ?? SlotRequestStatus::Pending->value;

        $requests = SlotRequest::query()
            ->with(['exhibitor:id,company_name,commercial_id', 'exhibitor.commercial:id,name', 'firstChoice:id,code,date,start_time,end_time,status', 'secondChoice:id,code,date,start_time,end_time,status', 'assignedSlot:id,code', 'processor:id,name'])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('responsable-conferences.requests', compact('requests', 'status'));
    }

    public function history(): View
    {
        Gate::authorize('viewAny', SlotRequest::class);
        $decisions = SlotRequestDecision::query()
            ->with(['assignedSlot:id,code'])
            ->orderByDesc('processed_at')
            ->orderByDesc('id')
            ->paginate(20);

        return view('responsable-conferences.history', compact('decisions'));
    }

    public function confirmed(): View
    {
        Gate::authorize('viewAny', SlotRequest::class);
        $confirmations = SlotRequestDecision::query()
            ->with('assignedSlot:id,code')
            ->where('decision', SlotRequestStatus::Accepted->value)
            ->orderByDesc('processed_at')
            ->paginate(20);

        return view('responsable-conferences.confirmed', compact('confirmations'));
    }

    public function accept(AcceptSlotRequestForm $request, SlotRequest $slotRequest, SlotService $service): RedirectResponse
    {
        try {
            $slot = $service->accept($slotRequest, $request->integer('slot_id'), $request->user());
        } catch (ValidationException $exception) {
            throw $exception->errorBag('slot-request');
        }

        return back()->with('status', "Demande confirmée sur le créneau {$slot->code}.");
    }

    public function reject(RejectSlotRequestForm $request, SlotRequest $slotRequest, SlotService $service): RedirectResponse
    {
        try {
            $service->reject($slotRequest, $request->validated('rejection_reason'), $request->user());
        } catch (ValidationException $exception) {
            throw $exception->errorBag('slot-request');
        }

        return back()->with('status', 'La demande a été refusée.');
    }

    public function profile(Request $request): View
    {
        return view('responsable-conferences.profile', ['user' => $request->user()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_profile_photo' => ['nullable', 'boolean'],
        ]);

        unset($data['profile_photo'], $data['remove_profile_photo']);
        $previousPhoto = $user->profile_photo;
        $newPhoto = $request->hasFile('profile_photo')
            ? ProfilePhotoStorage::store($request->file('profile_photo'))
            : null;

        if ($newPhoto) {
            $data['profile_photo'] = $newPhoto;
        } elseif ($request->boolean('remove_profile_photo')) {
            $data['profile_photo'] = null;
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        if (filled($data['password'] ?? null)) {
            $user->password = Hash::make($data['password']);
        }

        if (array_key_exists('profile_photo', $data)) {
            $user->profile_photo = $data['profile_photo'];
        }

        try {
            if (! $user->save()) {
                throw new \RuntimeException('Votre profil n’a pas pu être mis à jour.');
            }
        } catch (\Throwable $exception) {
            if ($newPhoto) {
                Storage::disk('public')->delete($newPhoto);
            }

            throw $exception;
        }

        if ($previousPhoto !== $user->profile_photo) {
            ProfilePhotoStorage::deleteIfUnreferenced($previousPhoto, $user);
        }

        return back()->with('status', 'Votre profil a été mis à jour.');
    }
}
