# REFRIGAIR EXPO 2027 — Journal des évolutions

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