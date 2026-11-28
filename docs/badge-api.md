# API des badges — v1

Endpoint de lecture destiné aux clients serveur et aux kiosques autorisés :

```text
POST https://platforme.refrigairexpo.com/api/v1/badges/lookup
Authorization: Bearer <jeton-client>
Accept: application/json
Content-Type: application/json
```

Corps :

```json
{"qr_token":"50bcd6e2-f912-4505-8516-54c7167294d8"}
```

Le QR imprimé contient le `qr_token` UUID seul. Il ne contient ni URL ni identifiant numérique.

## Réponses

Un badge actif renvoie son prénom, nom, fonction, société et stand. L’email, le téléphone et l’identifiant numérique de la base ne sont pas exposés.

```json
{
  "success": true,
  "data": {
    "qr_token": "50bcd6e2-f912-4505-8516-54c7167294d8",
    "status": "active",
    "active": true,
    "first_name": "Sara",
    "last_name": "Amrani",
    "function": "Technicienne",
    "company": {"name": "Atlas Froid", "stand_number": "A12"}
  }
}
```

Un badge désactivé renvoie `status: inactive` et `active: false`, sans les informations personnelles. Un UUID valide inconnu ou archivé renvoie `404 badge_not_found`; un UUID invalide renvoie `422`; l’absence d’un jeton valide, un jeton expiré ou révoqué renvoie `401`; une requête sans HTTPS hors environnements `local` et `testing` renvoie `400`; la limite est de 300 requêtes par minute et par adresse IP (`429` au-delà).

## Jetons client

Après application de la migration, créez un jeton sur le serveur avec :

```bash
php artisan badge-api:client:create "BadgeKiosk-OFEC-01"
```

Le jeton aléatoire n’est affiché qu’une fois; seule son empreinte SHA-256 est stockée en base. Par défaut, il expire après un an. Créez un jeton séparé par service backend ou par appareil, conservez-le dans un coffre de secrets et révoquez-le avec :

```bash
php artisan badge-api:client:revoke <identifiant-client>
```

Ne placez jamais ce jeton dans le JavaScript public, une page HTML, un dépôt de code ou une ressource embarquée dans l’APK. Pour Android, provisionnez un jeton propre à l’appareil et conservez-le dans Android Keystore; une application mobile distribuée ne peut pas protéger un secret commun compilé dans son binaire.

L’endpoint n’utilise pas de session web et n’est pas public. L’ancienne route web `/admin/badges/verify/{token}` reste disponible avec ses contrôles `auth` et de rôle pour les QR historiques contenant une URL.
