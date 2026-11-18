
@php
    $notificationsRoute = $isExhibitor
        ? 'exhibitor.notifications'
        : 'commercial.notifications';
@endphp

@if ($isExhibitor)

    <x-exhibitor.layout :user="$user" :exhibitor="$exhibitor">

        @include('account.notifications.partials.content')

    </x-exhibitor.layout>

@else

    <x-commercial.layout
        :commercial="$user"
        :commercial-initials="$user->initials">

        @include('account.notifications.partials.content')

    </x-commercial.layout>

@endif
