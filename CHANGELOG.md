# REFRIGAIR EXPO 2027 — Journal des évolutions

## Mise à jour : Conférences & Lounge des Technologies

**Projet :** Plateforme de gestion de salon — REFRIGAIR EXPO 2027  
**Framework :** Laravel 13  
**Branche :** `feature/conferences-lounge`  
**Objectif :** Vendre et gérer les créneaux de présentation (salle de conférence et Lounge des Technologies), suivre le chiffre d'affaires et automatiser les e-mails aux intervenants.

### Règles métier

| Espace | Jours | Créneaux | Prix |
|---|---|---|---|
| Salle de conférence (`conference`) | mer. 3 et jeu. 4 février 2027 | 1 h (45 min présentation + 10 min Q&R + 5 min changement), pause 12:30–14:00. Codes `C2-1…C2-6`, `C3-1…C3-6` | 1 500 € HT |
| Lounge des Technologies (`lounge`) | du 2 au 4 février 2027 | 2 h, en continu. Codes `L1-1…L1-3`, `L2-1…L2-4`, `L3-1…L3-4` | 1 000 € HT |

- 23 créneaux au total. Statuts : `available` (disponible), `option`, `reserved` (réservé), `paid` (payé).
- **Vendu = réservé + payé.** L'option bloque le créneau mais n'est comptée ni dans les ventes ni dans le CA.
- **CA HT** = prix des créneaux vendus ; **CA encaissé** = prix des créneaux payés.
- Un créneau a **un seul titulaire** : un exposant inscrit (`exhibitor_id`) **ou** une personne / société non exposante (`exhibitor_name` + coordonnées).
- Pas de double réservation : un créneau pris doit être repassé en « disponible » avant d'être donné à quelqu'un d'autre.
- L'exposant demande **un seul créneau** par demande.
- **Rôles :** le **commercial** gère tout le module ; l'**admin** consulte seulement (planning, CA, exports, supports) ; l'**exposant** fait ses demandes et envoie son support.

---

## 1. Base de données

**Fichiers :**
- `database/migrations/2026_11_23_100000_create_spaces_table.php` — les 2 espaces (`type` unique : conference / lounge).
- `database/migrations/2026_11_23_100001_create_slots_table.php` — les créneaux : code unique, date, horaires, prix HT, statut, titulaire, titre / résumé / intervenants, support, notes, dates d'envoi des rappels. Contrainte unique `(space_id, date, start_time)` : deux créneaux ne peuvent pas se chevaucher dans un même espace.
- `database/migrations/2026_11_23_100002_create_slot_requests_table.php` — les demandes des exposants (statut `pending` / `accepted` / `rejected`, motif de refus, créneau attribué, qui a traité). La colonne `second_choice_slot_id` existe mais n'est plus utilisée (une seule demande = un seul créneau).
- `database/migrations/2026_11_24_100000_add_contact_fields_to_slots_table.php` — `contact_name`, `contact_email`, `contact_phone` pour un titulaire non exposant.

**Modèles et enums :**
- `app/Models/Space.php`, `app/Models/Slot.php`, `app/Models/SlotRequest.php`.
- `app/Enums/SpaceType.php`, `app/Enums/SlotStatus.php` (libellés FR/EN, couleurs, `isTaken()`, `isSold()`), `app/Enums/SlotRequestStatus.php`.
- `app/Models/Exhibitor.php` — **modifié** : ajout des relations `slots()` et `slotRequests()`.

## 2. Référentiel et données initiales

- `app/Support/SlotCatalog.php` — **source unique** des horaires, codes, prix, réservations initiales et règles affichées dans le PDF. Pour changer un horaire ou un prix, c'est ici.
- `database/seeders/ConferenceLoungeSeeder.php` — crée les 2 espaces, les 23 créneaux et les 13 réservations actuelles (Media, Ebm Papst, Daikoo, Froidel, FIRST THERM, RES, AMPF). Si l'exposant existe en base (comparaison sans casse), il est relié ; sinon son nom est stocké dans `exhibitor_name`. **Relançable sans risque** : ne crée pas de doublons et n'écrase pas les modifications faites dans l'interface.
- `database/seeders/DatabaseSeeder.php` — **modifié** : appelle `ConferenceLoungeSeeder`.

## 3. Logique métier

- `app/Services/SlotService.php` — le cœur du module, utilisé par tous les contrôleurs, le tableau de bord et les tests :
  - `update()` : attribution / changement de statut dans une transaction avec `lockForUpdate` (empêche deux réservations simultanées). Si le titulaire change ou si le créneau est libéré, le support et l'historique des rappels de l'ancien titulaire sont effacés (fichier compris).
  - `accept()` / `reject()` : traitement des demandes des exposants.
  - `stats()` : créneaux vendus, options, CA HT, CA encaissé et taux de remplissage, au total, par espace et par jour.
  - `storeSupport()` : enregistrement du support (disque privé `local`, dossier `slot-supports/`).
  - `notifyHolder()` : prévient le titulaire (compte exposant, sinon e-mail de la fiche exposant, sinon e-mail du contact externe).
- `app/Policies/SlotPolicy.php` — `manage` (commercial), `view` (admin + commercial), `uploadSupport` (titulaire exposant ou commercial, créneau réservé / payé), `downloadSupport`.
- `app/Policies/SlotRequestPolicy.php` — `create` (exposant), `process` (commercial, demande en attente).
- `app/Http/Requests/Slots/` — validation par Form Requests :
  - `UpdateSlotForm` — planning commercial. Gère « Réservé pour : exposant inscrit / autre personne ». Sur un créneau libre, si un titulaire est saisi alors que le statut est resté sur « Disponible », le créneau passe automatiquement en « Réservé ».
  - `SubmitSlotRequestForm` — demande de l'exposant : créneau libre, du bon espace, pas de doublon en attente.
  - `AcceptSlotRequestForm`, `RejectSlotRequestForm` (motif obligatoire), `UploadSlotSupportForm` (PPT / PPTX / PDF, 20 Mo max).
- `app/Rules/MaxLines.php` — règle « 5 lignes maximum » pour le résumé.

## 4. Espace Commercial

**Pages :** `/commercial/conferences` (planning) et `/commercial/conferences/demandes`

**Fichiers :**
- `app/Http/Controllers/CommercialSlotController.php` + `resources/views/commercial/slots/index.blade.php` — planning visuel par jour et par espace, une couleur par statut. Un clic ouvre une fenêtre pour :
  - réserver pour **un exposant inscrit** (par exemple s'il n'a pas réussi à remplir le formulaire) ou pour **une autre personne non exposante** (nom / société, contact, e-mail, téléphone) ;
  - changer le statut (option, réservé, payé, disponible) ;
  - saisir titre, résumé, intervenants et notes internes ;
  - **déposer le support à la place du titulaire**.
- `app/Http/Controllers/CommercialSlotRequestController.php` + `resources/views/commercial/slots/requests.blade.php` — liste des demandes avec filtres ; accepter (attribue le créneau demandé) ou refuser avec motif.
- `resources/views/components/commercial/layout.blade.php` — **modifié** : entrée de menu « Conférences & Lounge ».

## 5. Tableau de bord commercial

- `app/Http/Controllers/CommercialDashboardController.php` — **modifié** : ajout de `slotStats` (une ligne).
- `resources/views/commercial/dashboard.blade.php` — **modifié** : nouvelle section « Conférences & Lounge des Technologies » (vendus / total par espace, CA HT, CA encaissé, remplissage par jour). Les autres sections sont inchangées. Les chiffres sont **globaux** au salon.

## 6. Espace Administration (consultation)

**Page :** `/admin/conferences`

- `app/Http/Controllers/Admin/SlotController.php` + `resources/views/admin/slots/index.blade.php` — indicateurs, ventes par espace, remplissage par jour, tableau **« Qui a réservé ? »** (par titulaire : codes, CA, encaissé), détail des créneaux avec filtres (recherche, espace, jour, statut), coordonnées des titulaires non exposants, téléchargement des supports. **Aucune modification possible** depuis l'admin.
- `resources/views/components/admin/layout.blade.php` — **modifié** : entrée de menu.

## 7. Espace Exposant

**Page :** `/exposant/conferences`

- `app/Http/Controllers/ExhibitorSlotController.php` + `resources/views/exhibitor/slots/index.blade.php` — présentation des deux offres (déroulé de l'heure, ce qui est inclus au lounge, horaires, prix), « Comment ça marche ? » en 5 étapes, **Mes créneaux** (suivi Confirmé → Support → Jour J, date limite d'envoi, envoi du support, heure d'arrivée), **Mes demandes**, et réservation guidée avec planning cliquable.
- **Confidentialité :** l'exposant voit « Réservé » mais **jamais le nom** des autres exposants (les noms ne sont pas envoyés à la vue).
- `resources/views/components/exhibitor/layout.blade.php` — **modifié** : entrée de menu + icône `calendar`.

## 8. Exports

- `app/Http/Controllers/SlotExportController.php` + `resources/views/exports/slots-program.blade.php`
  - **Programme PDF FR / EN** (statuts « Réservé / Disponible » et « Booked / Available », prix HT, règles de réservation). Utilise `barryvdh/laravel-dompdf` **s'il est installé** ; sinon, page HTML imprimable avec le même contenu.
  - **CSV** compatible Excel (BOM UTF-8, séparateur `;`), avec protection contre l'injection de formules (`=`, `+`, `-`, `@`).
- Accessibles au commercial (`/commercial/conferences/export/...`) et à l'admin (`/admin/conferences/export/...`).

## 9. Notifications et rappels

- `app/Notifications/SlotNotification.php` — base commune : canaux `mail` + `database` (cloche), **en file d'attente** (`ShouldQueue`). Pour un titulaire non exposant : e-mail seul, sans lien vers l'espace exposant.
- `SlotConfirmedNotification` (créneau confirmé), `SlotSupportReminderNotification` (rappel J-7 si le support n'est pas envoyé), `SlotEveReminderNotification` (la veille : venir 15 min avant pour le test technique), `SlotRequestRejectedNotification` (demande refusée + motif).
- `app/Console/Commands/SendSlotReminders.php` — commande `slots:send-reminders`. Chaque rappel n'est envoyé qu'**une fois** (`support_reminder_sent_at`, `eve_reminder_sent_at`).
- `routes/console.php` — **modifié** : la commande est planifiée tous les jours à 09:00 (heure de Casablanca).
- `app/Http/Controllers/AccountNotificationController.php` — **modifié** : un clic sur une notification de type `conference` ouvre la page Conférences & Lounge de l'exposant.

## 10. Routes

- `routes/web.php` — **modifié** : routes `commercial.slots.*`, `commercial.slot-requests.*`, `admin.slots.*` et `exhibitor.slots.*`. Ouvrir directement l'URL du formulaire exposant (`GET /exposant/conferences/demandes`) redirige vers la page au lieu d'une erreur 405.

## 11. Tests

`tests/Concerns/SlotFixtures.php` (helpers partagés) et 7 fichiers dans `tests/Feature/` — **44 tests** (suite complète : 122 tests au vert) :

- `SlotGenerationTest` — génération des créneaux, horaires, prix, réservations initiales, seeder relançable.
- `SlotDoubleBookingTest` — impossibilité de double réservation (planning, demandes, contraintes en base).
- `SlotRevenueTest` — calcul du CA (option exclue, payé = encaissé), remplissage par jour, tableau de bord.
- `SlotWorkflowTest` — parcours exposant → commercial, rôles, support, rappels, exports.
- `SlotSafeguardsTest` — changement de titulaire, doublons, CSV, ouverture des notifications.
- `SlotBookingOnBehalfTest` — réservation par le commercial pour un exposant ou une personne non exposante.
- `AdminSlotsTest` — consultation admin et lecture seule.

### Mise en route

```bash
php artisan migrate
php artisan db:seed --class=ConferenceLoungeSeeder
npm run build
composer require barryvdh/laravel-dompdf   # facultatif : vrai PDF
```

### Points d'attention

- **Lancer les migrations avant d'ouvrir le tableau de bord commercial** : il lit la table `slots`.
- Les e-mails sont **en file d'attente** : il faut `php artisan queue:work` (déjà inclus dans `composer dev`).
- Les rappels nécessitent le planificateur en production : `php artisan schedule:run` chaque minute (cron).
- `config/app.php` est en `UTC` : les heures affichées ont 1 h de décalage avec Casablanca (réglage global non modifié).

---

## Mise à jour : Gestion des équipements, demandes et notifications

**Projet :** Plateforme de gestion de salon — REFRIGAIR EXPO 2027  
**Framework :** Laravel 13  
**Commit :** `b5d1f8b`  
**Objectif :** Centraliser le catalogue des équipements, transférer la validation des demandes aux commerciaux responsables et améliorer le suivi des commandes.

---

## 1. Espace Commercial — Catalogue des équipements

**Page :** `/commercial/equipements`

**Fichiers :**
- `app/Http/Controllers/CommercialEquipmentController.php`
- `resources/views/commercial/equipment/index.blade.php`

### Modifications

- Transformation du catalogue privé en **catalogue partagé**.
- Tous les commerciaux consultent désormais le même catalogue d'équipements.
- Un commercial peut ajouter, modifier, activer ou désactiver un équipement du catalogue commun.
- Les équipements créés par l'administration sont également visibles.
- Conservation de l'identifiant du commercial créateur pour assurer la traçabilité.
- Mise à jour des statistiques globales : équipements actifs, inactifs, références et stock disponible.
- Conservation des filtres par catégorie, recherche, statut et pagination.
- Harmonisation des textes et de l'interface pour refléter le fonctionnement partagé.

**Règle métier :** Le catalogue des équipements appartient à la plateforme, et non à un commercial particulier.

## 2. Espace Administration — Catalogue des équipements

**Page :** `/admin/equipements`

**Fichier :**
- `resources/views/admin/equipment/index.blade.php`

### Fonctionnalités

- Consultation de l'ensemble des équipements créés par l'administration et les commerciaux.
- Identification du commercial associé à chaque équipement via la colonne **Ajouté par**.
- Maintien des fonctionnalités de gestion du catalogue.
- Suivi centralisé des équipements proposés aux exposants.

**Objectif :** Permettre à l'administration d'identifier l'origine des produits et de superviser le catalogue commun.

## 3. Espace Exposant — Options du stand

**Page :** `/exposant/options-du-stand`

**Fichier :**
- `resources/views/exhibitor/stand-options/index.blade.php`

### Modifications de l'interface

- Amélioration de la présentation des cartes produits.
- Séparation de la photographie et des informations du produit.
- Affichage structuré de la catégorie, du nom, de la description, du prix et du stock.
- Amélioration de l'alignement des éléments et du bouton Ajouter.
- Présentation plus adaptée aux différentes tailles d'écran.

### Gestion du panier

- Ajout d'une icône de suppression pour retirer un équipement sélectionné.
- Possibilité de supprimer un article ajouté par erreur avant l'envoi.
- Mise à jour automatique des quantités.
- Recalcul automatique du montant total estimé.
- Désactivation du bouton de soumission lorsque le panier est vide.

**Important :** La suppression depuis le panier ne supprime pas l'équipement du catalogue général.

## 4. Espace Exposant — Soumission d'une demande

**Fonctionnement métier**

Lorsqu'un exposant sélectionne des équipements puis soumet sa demande :

1. La commande est enregistrée avec le statut `pending`.
2. Les articles, quantités et prix sont conservés.
3. Une notification est destinée au commercial responsable.
4. Une notification est également destinée à l'administration.
5. La demande apparaît dans l'historique de l'exposant.

**Règle importante :** L'envoi d'une demande ne déduit pas immédiatement les quantités du stock.

## 5. Espace Commercial — Demandes d'équipements

**Pages :**
- `/commercial/demandes-equipements`
- `/commercial/demandes-equipements/{id}`

**Fichiers :**
- `app/Http/Controllers/CommercialEquipmentRequestController.php`
- `resources/views/commercial/equipment-requests/show.blade.php`

### Gestion des demandes

Le commercial responsable peut :

- Consulter les demandes provenant de ses propres exposants.
- Voir les équipements, les quantités et le montant total.
- Consulter les commentaires de l'exposant.
- Suivre l'état de chaque demande.
- **Approuver une demande.**
- **Refuser une demande avec un motif obligatoire.**

### Validation d'une demande

La validation comprend :

- Vérification que la demande appartient à un exposant du commercial connecté.
- Vérification que la demande n'a pas déjà été traitée.
- Vérification de la disponibilité des équipements.
- Contrôle du stock avant validation.
- Déduction des quantités approuvées.
- Passage du statut à `approved`.
- Enregistrement du commercial responsable et de la date de traitement.

### Refus d'une demande

- Motif de refus obligatoire.
- Passage du statut à `rejected`.
- Conservation de l'historique de décision.
- Aucune déduction du stock.

### Sécurité

Un commercial ne peut pas consulter, approuver ou refuser une demande appartenant à un autre commercial.

Les opérations de validation utilisent des transactions et des verrouillages de données afin de limiter les problèmes de traitement simultané.

## 6. Espace Commercial — Détail d'une demande

**Fichier :**
- `resources/views/commercial/equipment-requests/show.blade.php`

### Modifications UI/UX

- Ajout des sections **Approuver la demande** et **Refuser la demande**.
- Mise en page sur deux colonnes.
- Bouton de validation vert.
- Bouton de refus rouge.
- Champ de saisie du motif de refus.
- Positionnement des deux blocs dans le contenu principal, avant le footer.
- Actualisation du texte de gestion administrative en gestion commerciale.

Les actions de validation sont présentées pour les demandes ayant encore le statut `pending`.

## 7. Espace Administration — Liste des demandes

**Page :** `/admin/demandes`

**Fichier :**
- `resources/views/admin/requests/index.blade.php`

### Modifications

- Passage à un fonctionnement **lecture seule**.
- Suppression des boutons de validation et de refus.
- Conservation de la consultation des commandes.
- Affichage des exposants et des commerciaux responsables.
- Conservation des statistiques et filtres.
- Consultation des statuts : En attente, Approuvée, Refusée.
- Accès aux détails de chaque demande.

**Règle métier :** L'administration supervise toutes les demandes mais ne décide plus de leur approbation.

## 8. Espace Administration — Détail d'une demande

**Page :** `/admin/demandes/{id}`

**Fichier :**
- `resources/views/admin/requests/show.blade.php`

### Modifications

- Suppression des formulaires d'approbation et de refus.
- Présentation du détail des équipements demandés.
- Affichage des quantités, prix unitaires et sous-totaux.
- Affichage du montant total.
- Informations sur l'exposant et son commercial référent.
- Affichage du commentaire de l'exposant.
- Suivi du statut de la demande.
- Affichage du nom de la personne ayant traité la demande et de la date.
- Affichage du motif lorsqu'une demande est refusée.
- Ajout d'une indication claire précisant que l'administration dispose d'un accès en consultation.

## 9. Notifications — Nouvelle demande d'équipements

**Fichiers :**
- `app/Models/EquipmentRequest.php`
- `app/Notifications/NewEquipmentRequestNotification.php`
- `app/Http/Controllers/AccountNotificationController.php`

### Évolutions

- Extension des notifications de nouvelles demandes au commercial responsable.
- Conservation des notifications destinées à l'administration.
- Identification de la demande dans les données de notification.
- Redirection vers l'espace approprié selon le rôle.
- Vérification des autorisations avant d'ouvrir une commande.

### Destinataires

**Commercial :**
- Notification d'une nouvelle demande de son exposant.
- Accès direct au détail de la commande.
- Possibilité de traiter la demande.

**Administration :**
- Notification de la nouvelle demande.
- Accès au détail en consultation uniquement.

Les notifications utilisent le système de notifications de Laravel.

## 10. Notifications — Décision sur une demande

**Nouveau fichier :**
- `app/Notifications/EquipmentRequestStatusNotification.php`

### Fonctionnement

Après validation ou refus :

- Génération d'une notification indiquant le résultat.
- Information de l'exposant concerné.
- Information de l'administration.
- Identification de la commande.
- Conservation du nouveau statut dans la base de données.

Les notifications permettent de suivre les changements d'état sans devoir consulter systématiquement la liste des commandes.

## 11. Routes et autorisations

**Fichier :**
- `routes/web.php`

### Modifications

Ajout des routes de traitement réservées au Commercial :

- `commercial.equipment-requests.approve`
- `commercial.equipment-requests.reject`

Suppression des anciennes routes de validation Admin :

- `admin.demandes.approve`
- `admin.demandes.reject`

Conservation des routes de consultation côté Administration.

**Règle :** Les permissions sont contrôlées côté serveur, et pas uniquement en masquant les boutons dans les interfaces.

## 12. Tests automatisés

**Fichiers :**
- `tests/Feature/AuthenticationTest.php`
- `tests/Feature/SharedEquipmentCatalogTest.php`

### Scénarios couverts

- Consultation du catalogue partagé.
- Accès aux équipements créés par différents commerciaux.
- Consultation des équipements créés par l'administration.
- Approbation d'une commande par son commercial responsable.
- Vérification de la déduction du stock.
- Refus avec motif obligatoire.
- Vérification que le refus ne modifie pas le stock.
- Protection contre le traitement multiple.
- Contrôle des permissions entre commerciaux.
- Conservation de l'accès en consultation de l'administration.

Les anciens tests de validation Admin ont été adaptés à la nouvelle répartition des rôles.

**Note :** L'exécution complète des tests doit être vérifiée avant la mise en production.

---

## 13. Récapitulatif des permissions

| Fonctionnalité | Administrateur | Commercial | Exposant |
|---|---|---|---|
| Consulter le catalogue | Oui | Oui | Produits actifs |
| Ajouter un équipement | Oui | Oui | Non |
| Modifier un équipement | Oui | Oui | Non |
| Soumettre une demande | Non | Pour ses exposants | Oui |
| Consulter les demandes | Toutes | Ses exposants | Les siennes |
| Approuver une demande | Non | Oui | Non |
| Refuser une demande | Non | Oui | Non |
| Suivre les statuts | Oui | Oui | Oui |
| Recevoir les notifications | Oui | Oui | Oui, selon événement |

## 14. Assistance exposants — Fonctionnalité distincte

Les demandes d'équipements et les demandes d'assistance sont deux modules indépendants.

**Demandes d'équipements :** gestion des commandes d'articles et de leur validation commerciale.

**Assistance exposants :** création de tickets de support pour les questions concernant les badges, stands, équipements, comptes et autres problèmes.

Un exposant qui soumet une commande d'équipements ne crée donc pas automatiquement un ticket d'assistance.

---

## 15. Mise à jour précédente — Profil Commercial

**Commit :** `ffda75f`

**Fichiers principaux :**
- `resources/views/commercial/profile.blade.php`
- `resources/views/components/commercial/layout.blade.php`
- `resources/views/components/exhibitor/layout.blade.php`
- `resources/views/components/layouts/guest.blade.php`

### Améliorations

- Refonte visuelle de la page Mon profil.
- Intégration au layout de l'espace Commercial.
- Présentation de l'identité et des informations du compte.
- Affichage de l'avatar ou des initiales.
- Formulaire de changement du mot de passe.
- Vérification de l'ancien mot de passe.
- Contrôles de visibilité des champs de mot de passe.
- Harmonisation des layouts de la plateforme.

---

## Architecture fonctionnelle retenue

**Exposant → Commercial → Administration**

- **Exposant :** sélectionne des équipements, envoie ses demandes et consulte leur évolution.
- **Commercial :** gère ses exposants, reçoit leurs demandes et prend les décisions de validation.
- **Administration :** supervise l'ensemble de l'activité, consulte les demandes et centralise le catalogue d'équipements.

Cette organisation permet une séparation des responsabilités tout en conservant une vision globale de l'activité du salon.