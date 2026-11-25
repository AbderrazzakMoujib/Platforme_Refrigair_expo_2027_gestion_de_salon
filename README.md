# REFRIGAIR EXPO 2027

Plateforme de gestion du salon REFRIGAIR EXPO 2027, développée avec Laravel 13, PHP 8.3+, Blade, Tailwind CSS, Vite et MariaDB/MySQL.

## Modules et rôles

- **Administrateur** : comptes, commerciaux, exposants, badges et quotas, catalogue et demandes d'équipements, salons, paramètres et supervision.
- **Commercial** : ses exposants, leurs badges, équipements et réservations Conférences & Lounge. Les données liées aux autres commerciaux sont filtrées côté serveur.
- **Exposant** : son profil, ses badges, demandes d'équipements, réservations et supports.
- **Responsable Conférences** (`responsable_conferences`) : planning, demandes, décisions, historique et profil Conférences & Lounge.

Les accès sont protégés par `auth`, le middleware de rôle et, pour les ressources propriétaires, des requêtes limitées au propriétaire. Les QR de badges demandent une session Admin ou celle du Commercial responsable de l'exposant.

## Architecture applicative

- Routes web : `routes/web.php`; tâches planifiées : `routes/console.php`.
- Modèles et règles : `app/Models`, `app/Policies`, `app/Services`.
- Notifications : `app/Notifications`; emails : `resources/views/emails`.
- Fichiers privés : disque `local`; photos, logos et images publiques : disque `public` via `storage:link`.
- Vues Blade : `resources/views`; assets Vite : `resources/js`, `resources/css`.
- Migrations et seeders : `database/migrations`, `database/seeders`.

## Installation locale

Prérequis : PHP 8.3+, Composer, Node.js/npm et MariaDB/MySQL ou SQLite.

```bash
composer install
copy .env.example .env   # PowerShell; sous Unix : cp .env.example .env
php artisan key:generate
```

Configurez ensuite `.env` pour votre environnement local (connexion DB, URL, mail), puis :

```bash
php artisan migrate
php artisan db:seed
php artisan storage:link
npm install
npm run build
php artisan serve
```

En développement/local, le seeder principal conserve l'utilisateur de démonstration `test@example.com` avec le mot de passe de factory. **Il est omis en production**. N'utilisez pas cette identité en production et créez les comptes réels depuis les parcours d'administration. `ConferenceLoungeSeeder` installe les espaces et 23 créneaux de façon idempotente sans écraser les créneaux existants. Vérifiez les données métier avant toute exécution de seeders sur une base déjà utilisée.

Ne lancez jamais `migrate:fresh`, `db:wipe` ou une commande de reset sur une base contenant des données à préserver.

## Tests et frontend

```bash
php artisan test
npm run build
```

Les tests utilisent SQLite en mémoire (voir `phpunit.xml`) et ne doivent pas se connecter à la base configurée dans `.env`.

## Notifications et tâches planifiées

Certaines notifications de créneaux implémentent `ShouldQueue`. En production, utiliser un backend de queue durable (par défaut Laravel `database`) et faire tourner un worker supervisé :

```bash
php artisan queue:work --sleep=3 --tries=3 --timeout=60
```

Le scheduler lance `slots:send-reminders` chaque jour à 09:00 dans `Africa/Casablanca` : rappel du support à partir de J-7 jusqu'à la veille de l'ouverture du 2 février 2027, et rappel de veille pour les créneaux vendus du lendemain. Les colonnes d'envoi évitent les renvois après mise en queue. Les workers réessaient les notifications; si elles finissent dans `failed_jobs`, l'administrateur doit les inspecter et utiliser `php artisan queue:retry <id>` après correction de la cause.

## Production

Consultez [la procédure de déploiement](docs/DEPLOIEMENT_PRODUCTION.md). Elle couvre les variables, le serveur web/HTTPS, les migrations, le cache, les queues, le cron, les fichiers privés et les sauvegardes. Cette documentation ne remplace pas la configuration réelle du serveur, le test SMTP, la restauration d'une sauvegarde et le contrôle d'accès avant ouverture au public.
