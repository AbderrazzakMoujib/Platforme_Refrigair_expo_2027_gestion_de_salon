# Invitations et activation des comptes

Les comptes Commercial, Responsable Conférences et Exposant sont créés par un utilisateur Admin. Ils sont créés actifs, mais sans mot de passe. La personne reçoit un lien unique valide 48 heures et choisit son mot de passe sur la plateforme. L'activation ne connecte pas automatiquement la personne. Les comptes Admin ne peuvent pas utiliser ce circuit.

Les jetons bruts ne sont pas enregistrés en base et ne sont jamais journalisés. La base ne conserve que leur empreinte SHA-256. Un renvoi révoque les liens précédents. La suspension d'un compte révoque également les invitations en attente. Une fois l'accès réactivé, l'Admin doit renvoyer explicitement une invitation.

## Migration à appliquer

Cette version ajoute `role` et `delivery_status` à `commercial_invitations` et rend `sent_at` nullable. La migration rétro-remplit le rôle des invitations existantes à partir de leur utilisateur. Elle ne supprime pas de ligne utilisateur ou d'invitation.

Après une sauvegarde de la base et le déploiement des fichiers, exécuter depuis le répertoire de l'application :

```sh
php artisan migrate --force
php artisan optimize:clear
php artisan route:list --path=account/activation
```

Ne pas utiliser `migrate:fresh`, `db:wipe` ou une réinitialisation des migrations. La commande `php artisan app:create-admin` sert uniquement à initialiser la première installation et refuse de créer un second Admin.

## Réglages SMTP dans cPanel

Créer ou sélectionner une boîte d'envoi sur le domaine de l'application, puis récupérer dans cPanel les valeurs exactes affichées par **Email Accounts > Connect Devices / Set Up Mail Client**. Les noms de serveur, ports, chiffrement et identifiants varient selon l'hébergeur. Ne pas reprendre les valeurs d'un autre hébergeur.

Configurer les variables d'environnement de l'application depuis le gestionnaire cPanel ou le fichier d'environnement déjà utilisé par le déploiement :

```dotenv
APP_URL=https://<domaine-public-de-la-plateforme>
MAIL_MAILER=smtp
MAIL_HOST=<serveur-smtp-fourni-par-cpanel>
MAIL_PORT=<port-fourni-par-cpanel>
MAIL_USERNAME=<adresse-complete-de-la-boite>
MAIL_PASSWORD=<mot-de-passe-de-la-boite>
MAIL_SCHEME=<smtp-ou-smtps-selon-cpanel>
MAIL_FROM_ADDRESS=<adresse-complete-de-la-boite>
MAIL_FROM_NAME="REFRIGAIR EXPO 2027"
```

Les valeurs entre chevrons sont des espaces réservés, pas des valeurs à copier littéralement. Garder le mot de passe SMTP hors du dépôt Git et des tickets. `APP_URL` doit être l'URL HTTPS publique afin que les liens d'activation ne pointent pas vers `localhost`.

Après avoir configuré les variables sur le serveur, reconstruire le cache de configuration :

```sh
php artisan config:clear
php artisan config:cache
```

Envoyer un message de vérification depuis l'environnement d'exploitation et contrôler la réception, les indésirables ainsi que les journaux du transport SMTP. L'interface Admin indique si l'envoi d'une invitation a réussi ; en cas d'échec, le compte reste en attente et l'Admin peut renvoyer l'invitation après correction de la configuration.

## File d'attente cPanel

L'envoi des invitations de cette fonctionnalité est synchrone et intervient après l'enregistrement réussi du compte et du jeton. Aucun worker Laravel n'est donc nécessaire pour les invitations.

Si d'autres notifications sont mises en file, préférer un worker persistant géré par l'hébergeur quand cPanel propose un gestionnaire de processus. À défaut, créer une tâche cron chaque minute, avec le chemin absolu de PHP et de l'application adaptés au compte :

```sh
cd <chemin-absolu-de-l-application> && /usr/local/bin/php artisan queue:work --stop-when-empty --max-time=50
```

Configurer `QUEUE_CONNECTION` et le pilote correspondant avant d'activer ce cron. Ne pas exécuter plusieurs workers cron simultanés. En cas d'échec, examiner `storage/logs/laravel.log`, l'état de la file et les journaux SMTP sans recopier de liens d'activation ou de secrets dans un ticket.
