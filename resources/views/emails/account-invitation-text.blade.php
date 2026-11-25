@php
    $roleLabel = match ($role) {
        'commercial' => 'Commercial',
        'responsable_conferences' => 'Responsable Conférences',
        default => 'Exposant',
    };
    $buttonLabel = match ($role) {
        'commercial' => 'Activer mon compte Commercial',
        'responsable_conferences' => 'Activer mon espace',
        default => 'Activer mon espace exposant',
    };
@endphp
REFRIGAIR EXPO 2027 | Activation de votre espace {{ $roleLabel }}

Bonjour {{ $user->name }},

Votre accès à la plateforme REFRIGAIR EXPO 2027 a été créé.
Adresse email de connexion : {{ $user->email }}

@if ($role === 'exposant')
Votre espace vous permet de consulter les informations de votre entreprise et de votre stand, gérer les badges, soumettre des demandes d'équipements, suivre vos demandes et notifications, demander un créneau Conférences & Lounge et contacter votre Commercial référent. Le salon se déroule du 2 au 4 février 2027 à l'OFEC de Casablanca.
@elseif ($role === 'commercial')
Votre espace vous permet de gérer vos entreprises exposantes et leurs accès, suivre les badges, gérer le catalogue partagé et les demandes d'équipements, traiter l'assistance et suivre les réservations Conférences & Lounge selon vos permissions.
@else
Votre espace vous permet de consulter le planning, traiter les demandes de réservation, suivre les créneaux confirmés, consulter les supports, demander une révision et échanger avec les exposants.
@endif

{{ $buttonLabel }} : {{ $activationUrl }}

Ce lien personnel expire dans 48 heures et ne peut être utilisé qu'une seule fois. Définissez votre mot de passe sur cette page. Aucun mot de passe n'est envoyé par email.

Cordialement,
L'équipe REFRIGAIR EXPO 2027
https://refrigairexpo.com

Si vous n'attendiez pas cette invitation, ignorez cet email.
