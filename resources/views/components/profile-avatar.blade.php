@props(['user', 'class' => 'size-12'])

@if ($user->profile_photo_url)
    <img src="{{ $user->profile_photo_url }}" alt="Photo de profil de {{ $user->name }}" {{ $attributes->merge(['class' => $class.' rounded-full object-cover']) }}>
@else
    <span aria-label="Avatar de {{ $user->name }}" {{ $attributes->merge(['class' => $class.' grid place-items-center rounded-full bg-blue-100 font-bold text-blue-700']) }}>
        {{ $user->initials }}
    </span>
@endif
