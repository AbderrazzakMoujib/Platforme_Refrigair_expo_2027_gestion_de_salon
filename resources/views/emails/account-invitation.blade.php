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
    $logoUrl = rtrim((string) config('app.url'), '/').'/assets/images/Refrigairexpo-February-2-4-2027-OFEC-Casablanca-Morocco-logo-8.png';
@endphp
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ $roleLabel }} — REFRIGAIR EXPO 2027</title>
</head>
<body style="margin:0;padding:24px 12px;background:#F5F7FB;font-family:Arial,Helvetica,sans-serif;color:#24324a;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
        <tr>
            <td align="center" style="padding:24px;background:#0F2347;">
                <img src="{{ $logoUrl }}" width="240" alt="REFRIGAIR EXPO 2027" style="display:block;width:100%;max-width:240px;height:auto;border:0;">
            </td>
        </tr>
        <tr>
            <td style="padding:32px 28px 12px;">
                <p style="margin:0 0 18px;font-size:16px;line-height:1.6;">Bonjour {{ $user->name }},</p>
                @if ($role === 'exposant')
                    <h1 style="margin:0 0 16px;color:#0F2347;font-size:24px;line-height:1.3;">Votre espace exposant est prêt</h1>
                    <p style="margin:0 0 16px;font-size:15px;line-height:1.7;">Votre espace exposant pour <strong>REFRIGAIR EXPO 2027</strong>, organisé du <strong>2 au 4 février 2027 à l'OFEC de Casablanca</strong>, a été créé. Cette plateforme vous permet de préparer et de suivre votre participation depuis un espace personnel sécurisé.</p>
                    <h2 style="margin:24px 0 10px;color:#0F2347;font-size:17px;">Découvrez votre espace</h2>
                    <p style="margin:0 0 12px;font-size:14px;line-height:1.65;"><strong>Entreprise et stand.</strong> Consultez les informations de votre entreprise et de votre participation.</p>
                    <p style="margin:0 0 12px;font-size:14px;line-height:1.65;"><strong>Badges.</strong> Enregistrez votre équipe et suivez le quota attribué.</p>
                    <p style="margin:0 0 12px;font-size:14px;line-height:1.65;"><strong>Équipements.</strong> Consultez le catalogue, soumettez vos demandes et suivez leur traitement.</p>
                    <p style="margin:0 0 12px;font-size:14px;line-height:1.65;"><strong>Conférences &amp; Lounge.</strong> Consultez le programme, demandez une réservation et, après confirmation, transmettez votre support et échangez avec le Responsable Conférences.</p>
                    <p style="margin:0 0 12px;font-size:14px;line-height:1.65;"><strong>Demandes et notifications.</strong> Suivez les demandes liées à votre participation et leurs mises à jour.</p>
                    <p style="margin:0 0 12px;font-size:14px;line-height:1.65;"><strong>Assistance.</strong> Contactez votre Commercial référent et échangez avec lui depuis la plateforme.</p>
                @elseif ($role === 'commercial')
                    <h1 style="margin:0 0 16px;color:#0F2347;font-size:24px;line-height:1.3;">Activez votre espace Commercial</h1>
                    <p style="margin:0 0 18px;font-size:15px;line-height:1.7;">Votre accès à la plateforme REFRIGAIR EXPO 2027 a été créé. Votre espace vous permet de gérer vos entreprises exposantes et leurs accès, suivre les badges, gérer le catalogue partagé et les demandes d'équipements, traiter l'assistance et suivre les réservations Conférences &amp; Lounge selon vos permissions.</p>
                @else
                    <h1 style="margin:0 0 16px;color:#0F2347;font-size:24px;line-height:1.3;">Activez votre espace Responsable Conférences</h1>
                    <p style="margin:0 0 18px;font-size:15px;line-height:1.7;">Votre accès à la plateforme REFRIGAIR EXPO 2027 a été créé. Vous pourrez consulter le planning, traiter les demandes de réservation, suivre les créneaux confirmés, consulter les supports, valider leur contenu ou demander une révision, et échanger avec les exposants depuis l'espace Responsable Conférences.</p>
                @endif

                <div style="margin:26px 0 20px;padding:16px;border:1px solid #dbeafe;border-radius:8px;background:#eff6ff;">
                    <p style="margin:0 0 5px;font-size:12px;color:#475569;">Adresse email de connexion</p>
                    <p style="margin:0;font-size:15px;font-weight:bold;color:#0F2347;word-break:break-word;">{{ $user->email }}</p>
                </div>

                <p style="margin:0 0 18px;font-size:15px;line-height:1.7;">Pour accéder à votre espace personnel, définissez votre mot de passe à l'aide du bouton ci-dessous.</p>
                <p style="margin:24px 0 28px;text-align:center;">
                    <a href="{{ $activationUrl }}" style="display:inline-block;padding:14px 22px;border-radius:8px;background:#2563EB;color:#ffffff;text-decoration:none;font-size:15px;font-weight:bold;">{{ $buttonLabel }}</a>
                </p>
                <p style="margin:0 0 12px;font-size:13px;line-height:1.6;color:#475569;">Ce lien personnel expire dans 48 heures et ne peut être utilisé qu'une seule fois. Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :</p>
                <p style="margin:0 0 24px;font-size:12px;line-height:1.6;word-break:break-all;"><a href="{{ $activationUrl }}" style="color:#2563EB;">{{ $activationUrl }}</a></p>
                <p style="margin:0 0 8px;font-size:14px;line-height:1.6;">Nous restons à votre disposition pour vous accompagner dans la préparation de votre participation.</p>
                <p style="margin:0 0 20px;font-size:14px;line-height:1.6;">Cordialement,<br><strong>L'équipe REFRIGAIR EXPO 2027</strong></p>
            </td>
        </tr>
        <tr>
            <td align="center" style="padding:18px 24px;background:#f8fafc;border-top:1px solid #e2e8f0;font-size:12px;line-height:1.6;color:#64748b;">
                REFRIGAIR EXPO 2027 · OFEC Casablanca<br>
                <a href="https://refrigairexpo.com" style="color:#2563EB;text-decoration:none;">Site officiel</a><br>
                Si vous n'attendiez pas cette invitation, ignorez cet email. Aucun mot de passe n'est envoyé par email.
            </td>
        </tr>
    </table>
</body>
</html>
