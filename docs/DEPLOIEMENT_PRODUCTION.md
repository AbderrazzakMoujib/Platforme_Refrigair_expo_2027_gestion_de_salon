# Déploiement en production

Cette procédure décrit un déploiement Laravel classique sur Linux avec Apache/Nginx, PHP-FPM et MariaDB/MySQL. Elle ne déclenche aucun déploiement. Adaptez les chemins et le gestionnaire de processus à l'hébergeur.

## 1. Prérequis et préparation

- PHP 8.3 ou supérieur avec les extensions exigées par Laravel et le driver PDO choisi.
- Composer, Node.js/npm pour compiler les assets, serveur web TLS, MariaDB/MySQL.
- Un utilisateur système dédié à l'application et un compte DB dédié, limité à cette base.
- Déployer une version revue du dépôt. Ne jamais déposer `.env`, `vendor`, les journaux ou les fichiers de sauvegarde dans le document root.
- Le document root du vhost doit être **uniquement** le dossier `public/` du projet. Le `.htaccess` livré ne remplace pas une configuration TLS ni le durcissement du serveur.

## 2. Variables d'environnement

Créer le `.env` sur le serveur hors du dépôt, avec permissions restreintes. Ne pas copier des secrets dans Git ni dans cette documentation. Valeurs à fournir par l'administrateur d'infrastructure :

```dotenv
APP_NAME="REFRIGAIR EXPO 2027"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.example
APP_KEY=<clé unique générée pour cette installation>

DB_CONNECTION=mysql
DB_HOST=<hôte DB privé>
DB_PORT=3306
DB_DATABASE=<base dédiée>
DB_USERNAME=<compte DB dédié>
DB_PASSWORD=<secret fourni par le gestionnaire de secrets>

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
FILESYSTEM_DISK=public
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=<serveur SMTP>
MAIL_PORT=<port TLS du fournisseur>
MAIL_USERNAME=<identifiant SMTP>
MAIL_PASSWORD=<secret SMTP>
MAIL_SCHEME=tls
MAIL_FROM_ADDRESS=<adresse d'envoi vérifiée>
MAIL_FROM_NAME="REFRIGAIR EXPO 2027"
```

`APP_KEY` doit être créée une seule fois pour l'installation et conservée dans le gestionnaire de secrets; ne pas la régénérer lors d'une mise à jour, car cela invaliderait les données chiffrées et les sessions. L'exemple `.env.example` est destiné au développement et active `APP_DEBUG`; ne le recopiez pas tel quel en production. Le domaine de session doit rester vide si l'application n'a pas besoin de partager la session avec des sous-domaines.

## 3. Déploiement applicatif

Exemple d'ordre d'opérations depuis la racine du projet (les chemins sont à adapter) :

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan down
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

Effectuer d'abord une sauvegarde et vérifier la compatibilité des migrations avec la version déployée. `migrate --force` applique les migrations en attente sans réinitialiser les tables; ne pas utiliser `migrate:fresh`, `db:wipe` ou `migrate:reset`. Il n'est pas nécessaire de lancer `db:seed` lors d'un déploiement ordinaire. Si le catalogue Conférences & Lounge doit être initialisé, le seeder est relançable sans écraser les créneaux déjà modifiés; valider au préalable qu'il s'agit bien de l'action souhaitée. En production, le seeder principal ne crée pas le compte de démonstration.

Après chaque déploiement, vérifier le résultat de `php artisan migrate:status`, `php artisan route:list`, les journaux, le login, les quatre espaces selon des comptes de test contrôlés et les pages de santé métier. Ne pas exposer un compte de démonstration.

## 4. Accès disque, fichiers et sauvegardes

- Rendre `storage/` et `bootstrap/cache/` inscriptibles uniquement par l'utilisateur de PHP-FPM/CLI requis. Le code et `public/` doivent rester non inscriptibles par le serveur web lorsque possible.
- Exécuter `php artisan storage:link` pour les images publiques. Les supports de conférence sont stockés sur le disque `local` et servis par des contrôleurs qui vérifient les permissions; ne pas les déplacer vers un répertoire accessible directement par HTTP.
- Sauvegarder de manière chiffrée la base relationnelle **et** `storage/app` (dont fichiers privés), ainsi que toute configuration externe utile. Stocker les sauvegardes hors serveur, avec accès restreint et rétention conforme à la politique de l'organisation.
- Définir une rétention (quotidienne courte, périodique plus longue selon exigences métier), surveiller les échecs et effectuer régulièrement une restauration de test sur un environnement isolé. Une sauvegarde non restaurée n'est pas validée.
- Ne jamais inclure `.env`, secrets SMTP ou clés dans les archives publiques ou les journaux. Le dépôt ignore `.env`; vérifier aussi les règles du fournisseur web et les ACL réseau.

## 5. Queue et notifications

Les notifications qui implémentent `ShouldQueue` nécessitent un worker actif. Pour la connexion database configurée par défaut :

```bash
php artisan queue:work database --sleep=3 --tries=3 --timeout=60
```

Gérer le worker par Supervisor, systemd ou l'outil équivalent afin qu'il redémarre après erreur et au déploiement (`php artisan queue:restart`). Le timeout de 60 secondes reste inférieur au `retry_after` de 90 secondes configuré par défaut. Surveiller `php artisan queue:failed`; les rappels sont marqués envoyés lors de leur mise en queue, et une notification définitivement échouée est conservée dans `failed_jobs` pour inspection puis retry contrôlé (`php artisan queue:retry <id>`). Tester le SMTP réellement depuis le serveur avant ouverture du service. Ne pas utiliser `MAIL_MAILER=log` en production si les messages doivent partir.

## 6. Scheduler Conférences & Lounge

La commande planifiée est configurée dans `routes/console.php`, tous les jours à 09:00 `Africa/Casablanca`, avec `withoutOverlapping()`. Installer un cron exécutant Laravel **chaque minute**, depuis la racine du projet et avec le PHP CLI utilisé par l'hébergement :

```cron
* * * * * cd /chemin/absolu/vers/projet && /chemin/absolu/vers/php artisan schedule:run >> /dev/null 2>&1
```

Tester auparavant la commande métier avec `php artisan slots:send-reminders` sur un environnement non productif, et surveiller les logs/queues. Les rappels utilisent la date d'ouverture codée au 2 février 2027; toute modification de calendrier doit être répercutée explicitement et vérifiée avec l'équipe métier.

## 7. Contrôles d'exploitation et sécurité

- Forcer HTTPS au niveau du vhost/load balancer; configurer les proxies de confiance si TLS est terminé en amont.
- `APP_DEBUG=false`, clé unique, TLS, secrets non versionnés, compte DB à privilèges minimaux, sauvegardes vérifiées.
- `SESSION_SECURE_COOKIE=true`; utiliser une URL et un nom de cookie appropriés au domaine; conserver `SESSION_HTTP_ONLY=true` et `SESSION_SAME_SITE=lax` sauf besoin démontré.
- Vérifier les permissions `auth`/rôle, les comptes désactivés et les téléchargements privés avec des comptes de chaque rôle. Le QR badge autorise Admin et Commercial connecté uniquement pour les badges de ses exposants; aucun accès anonyme n'est prévu.
- Surveiller les logs applicatifs et serveur, erreurs SMTP, queue échouée, espace disque, expirations TLS et alertes de sauvegarde. Limiter l'accès aux logs qui peuvent contenir des données métier.
- Mettre à jour PHP et les dépendances selon une procédure de changement testée; exécuter les tests du dépôt et compiler les assets avant chaque release.

## 8. Vérifications avant ouverture

1. Confirmer le domaine, HTTPS, `APP_ENV`, `APP_DEBUG`, `APP_KEY`, DB, SMTP, `SESSION_SECURE_COOKIE` et stockage.
2. Sauvegarder DB et fichiers puis vérifier une restauration hors production.
3. Exécuter les migrations prévues, contrôler `migrate:status`, comptes/rôles et les 23 créneaux.
4. Démarrer et surveiller le worker; installer le cron minute et vérifier `schedule:list`.
5. Tester les connexions, permissions, emails, uploads et téléchargements privés avec des identités dédiées non prédictibles.
6. Vérifier logs, files échouées et procédures de restauration/retour arrière.

La plateforme ne peut être déclarée prête à servir de vrais clients qu'après ces contrôles sur le serveur cible et validation opérationnelle des sauvegardes, emails et autorisations.
