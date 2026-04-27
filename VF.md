# Cahier des Charges — MorLancer
**Version :** 2.0 — Fonctionnalités actives  
**Date :** Juin 2026  
**Statut :** En développement actif

---

## 1. Présentation Générale

**MorLancer** est une marketplace freelance marocaine qui met en relation des **Clients** (entreprises ou particuliers ayant des besoins) et des **Freelancers** (professionnels indépendants proposant leurs services).

La plateforme repose sur une architecture découplée :
- **Backend** : API RESTful — Laravel 13 (PHP 8.3), PostgreSQL 15, Laravel Sanctum, Docker
- **Frontend** : SPA — Vue.js 3 (Composition API), Pinia, Vue Router, Tailwind CSS, Vite, Chart.js

---

## 2. Rôles Utilisateurs

| Rôle | Description |
|------|-------------|
| **Admin** | Gère la plateforme : validation des comptes, statistiques, catégories, signalements |
| **Client** | Publie des missions, explore les Briefs, gère contrats et paiements |
| **Freelancer** | Publie des Briefs, explore les missions, gère ses contrats et son wallet |

---

## 3. Authentification & Onboarding

### 3.1 Inscription
- Formulaire avec choix du rôle : **Client** ou **Freelancer**
- Champs : nom, email, mot de passe (min. 8 caractères, confirmation)
- Validation côté backend (unicité email/nom, confirmation mot de passe)
- Compte créé avec statut `pending` — en attente d'approbation admin

### 3.2 Vérification d'identité
- Upload obligatoire d'un document (CIN ou Passeport) après inscription
- Saisie du numéro de document et lieu de naissance
- Stockage sécurisé du fichier (`storage/app/public/id_documents`)
- Statut passe à `pending` jusqu'à validation manuelle par l'admin

### 3.3 Connexion
- Authentification par email/mot de passe via Laravel Sanctum (token Bearer)
- Redirection automatique selon le rôle : `/admin/dashboard`, `/client/dashboard`, `/freelancer/dashboard`
- Blocage si compte non vérifié (`pending`, `rejected`, `banned`)

### 3.4 Déconnexion
- Suppression du token Sanctum côté serveur
- Redirection vers `/login`

---

## 4. Dashboard Admin

### 4.1 Statistiques (Tableau de bord)
Données réelles affichées via graphes Chart.js :
- **KPI Cards** : total utilisateurs, missions publiées, contrats signés, revenus (MAD) avec tendance mensuelle
- **Bar chart** : inscriptions par semaine (4 dernières semaines)
- **Doughnut** : répartition Clients / Freelancers / En attente
- **Doughnut** : contrats par statut (Actifs, En attente, Complétés, Remboursés, Annulés)
- **Line chart** : contrats créés par semaine
- **Bar chart** : dépôts en MAD par semaine
- **Top Freelancers** : classement par missions complétées avec barre de progression
- **Modération rapide** : liste des comptes en attente avec actions directes

### 4.2 Gestion des utilisateurs
- Liste de tous les utilisateurs avec filtres (rôle, statut)
- Colonnes : Nom, Compte, N° CIN, Né(e) à, Document, Date, Statut, Actions
- Actions : **Approuver**, **Rejeter**, **Bannir**, **Débannir**

### 4.3 Gestion des catégories
- CRUD complet des catégories métiers (parent + sous-catégories)
- Utilisées pour taguer missions et Briefs

### 4.4 Gestion des contrats
- Visualisation de tous les contrats de la plateforme

### 4.5 Gestion des paiements
- Visualisation de toutes les transactions

### 4.6 Signalements
- Liste de tous les signalements avec badge coloré (jaune / orange / rouge selon le nombre)
- Informations : utilisateur signalé, signaleur, raison, détails, date, nombre total de signalements
- Actions admin : **Ignorer** ou **Bannir** l'utilisateur signalé
- **Auto-ban automatique** : si un utilisateur cumule ≥ 10 signalements, il est banni automatiquement

---

## 5. Dashboard Client

### 5.1 Feed des Briefs
- Affichage des Briefs publiés par les freelancers
- Recherche par mot-clé et filtres par catégorie / sous-catégorie
- Interactions sur chaque Brief : **Like**, **Commentaire**, **Favori**, **Contacter**
- Clic sur un favori depuis la navbar → scroll automatique vers le Brief dans le feed

### 5.2 Mes Missions
- Création d'une mission : titre, description, budget (DH), deadline, catégories
- Liste des missions publiées par le client avec statut
- Onglet **Messages** par mission pour voir les commentaires des freelancers

### 5.3 Messagerie
- Conversations avec les freelancers
- Envoi de messages texte (polling 3s)
- Bouton **Lancer Contrat** directement depuis la conversation
- Bouton **Signaler** l'interlocuteur avec formulaire (raison + détails)

### 5.4 Contrats
- Liste des contrats du client (tous statuts)
- Actions : **Compléter** (valider la livraison), **Rembourser**
- Suivi des statuts : `pending_freelancer` → `active` → `completed` / `refunded`

### 5.5 Paiements & Wallet
- Résumé du wallet : solde disponible, total déposé, total dépensé, escrow bloqué
- Dépôt de fonds via PayPal sandbox
- Bouton de crédit test (environnement développement)

### 5.6 Profil
- Statistiques : missions, contrats, total déposé, total dépensé, escrow bloqué

---

## 6. Dashboard Freelancer

### 6.1 Feed des Missions
- Affichage des missions publiées par les clients
- Recherche par mot-clé et catégorie
- Interactions : **Like**, **Commenter**, **Favori**, **Contacter**

### 6.2 Mes Briefs
- Création d'un Brief : titre, description, catégories (parent + sous), prix (DH), délai
- Liste des Briefs publiés avec likes et commentaires reçus

### 6.3 Propositions de contrats
- Alerte visuelle si un client a envoyé une proposition de contrat
- Modal de révision : **Accepter** ou **Refuser** la proposition

### 6.4 Messagerie
- Conversations avec les clients
- Envoi de messages texte (polling 3s)
- Bouton **Signaler** l'interlocuteur avec formulaire (raison + détails)

### 6.5 Contrats
- Liste des contrats actifs et historique
- Acceptation / refus d'une proposition de contrat

### 6.6 Paiements & Wallet
- Résumé du wallet : solde disponible, revenus, escrow

### 6.7 Profil
- Statistiques : missions favorites, notifications

---

## 7. Système de Signalement

- Accessible depuis la messagerie (Client et Freelancer)
- Icône 🚩 à côté du nom de l'interlocuteur dans chaque conversation
- Formulaire : choix de la raison (Comportement abusif, Spam, Arnaque/Fraude, Contenu inapproprié, Faux profil, Autre) + détails optionnels
- Un utilisateur ne peut pas se signaler lui-même
- Pas de doublon : un signalement `pending` par paire reporter/reporté
- Réception et traitement par l'admin dans l'onglet **Signalements**
- Auto-ban automatique à ≥ 10 signalements cumulés

---

## 8. Système de Contrats

| Statut | Description |
|--------|-------------|
| `pending_freelancer` | Proposition envoyée, en attente d'acceptation du freelancer |
| `active` | Contrat accepté, mission en cours |
| `completed` | Mission validée par le client, paiement libéré |
| `refunded` | Remboursement effectué |
| `cancelled` | Contrat annulé |

- Génération depuis la messagerie (côté client) via modal
- Acceptation / refus par le freelancer via modal dédié

---

## 9. Messagerie

- Conversations entre Client et Freelancer
- Polling toutes les 3 secondes pour les nouveaux messages
- Historique complet des échanges
- Ouverture automatique d'une conversation depuis un Brief (bouton "Contacter")
- Lancement de contrat intégré (côté client)
- Signalement de l'interlocuteur intégré (les deux rôles)

---

## 10. Notifications

- Système de notifications en base de données
- Polling côté frontend (stores Pinia)
- Déclenchées par : nouvelles propositions de contrat, mises à jour de statut

---

## 11. Stack Technique

### Backend
| Élément | Détail |
|---------|--------|
| Framework | Laravel 13 (PHP 8.3) |
| Base de données | PostgreSQL 15 |
| Authentification | Laravel Sanctum (token Bearer) |
| Temps réel | Polling HTTP (3–30s) |
| Stockage fichiers | Laravel Storage (local/public) |
| Conteneurisation | Docker (PHP-FPM, Nginx, PostgreSQL, Redis) |

### Frontend
| Élément | Détail |
|---------|--------|
| Framework | Vue.js 3 (Composition API) |
| State Management | Pinia |
| Routage | Vue Router 4 |
| Styles | Tailwind CSS |
| Build | Vite |
| HTTP | Axios (instance configurée avec token) |
| Graphes | Chart.js |

---

## 12. Base de Données — Tables Actives (21 tables)

| Table | Rôle |
|-------|------|
| `users` | Comptes utilisateurs (tous rôles) |
| `roles` | Définition des rôles (admin, client, freelancer) |
| `categories` | Catégories métiers (parent/enfant) |
| `missions` | Missions publiées par les clients |
| `briefs` | Briefs publiés par les freelancers |
| `brief_likes` | Likes sur les Briefs |
| `brief_comments` | Commentaires sur les Briefs |
| `brief_favorites` | Favoris sur les Briefs |
| `brief_categories` | Liaison Brief ↔ Catégorie |
| `mission_likes` | Likes sur les missions |
| `mission_comments` | Commentaires sur les missions |
| `mission_favorites` | Favoris sur les missions |
| `mission_applications` | Candidatures des freelancers |
| `mission_category` | Liaison Mission ↔ Catégorie |
| `contracts` | Contrats Client ↔ Freelancer |
| `messages` | Messages entre utilisateurs |
| `notifications` | Notifications en base |
| `transactions` | Historique des paiements |
| `reports` | Signalements entre utilisateurs |
| `personal_access_tokens` | Tokens Sanctum |
| `migrations` | Historique des migrations Laravel |

---

## 13. Routes API Actives (62 routes)

### Auth
- `POST /register`, `POST /login`, `POST /logout`, `POST /user/verify`, `GET /user`

### Admin
- `GET /admin/stats`, `GET /admin/users`, `GET /admin/pending-users`
- `POST /admin/users/{id}/approve|reject|ban`
- `GET|POST /admin/categories`, `PUT|DELETE /admin/categories/{id}`
- `GET /admin/reports`, `POST /admin/reports/{id}/dismiss|ban`

### Client
- `GET /client/briefs`, `GET /client/categories`, `GET /client/favorites`
- `POST|GET /client/missions`, `GET /client/stats`
- `POST /client/briefs/{id}/like|comment|favorite`, `GET /client/briefs/{id}/comments`
- `GET /client/notifications`
- `GET /client/missions/{id}/applications`, `POST /client/applications/{id}/accept`
- `POST /client/test-credit`

### Freelancer
- `GET|POST /freelancer/briefs`, `GET /freelancer/briefs/mine`
- `POST /freelancer/briefs/{id}/like|comment|favorite`, `GET /freelancer/briefs/{id}/comments`
- `GET /freelancer/missions`, `GET /freelancer/missions/favorites`
- `POST /freelancer/missions/{id}/like|favorite|comment|apply`
- `GET /freelancer/missions/{id}/comments`
- `GET /freelancer/favorites`, `GET /freelancer/notifications`, `GET /freelancer/categories`

### Messagerie
- `GET /conversations`, `GET /conversations/{userId}`, `POST /messages`

### Contrats
- `POST /contracts`, `GET /contracts/client`, `GET /contracts/freelancer`
- `POST /contracts/{id}/complete|refund|accept|reject`

### Wallet
- `GET /wallet/summary`, `POST /wallet/paypal/capture`

### Signalements
- `POST /reports`

---

## 14. Sécurité

- Authentification par token Bearer (Sanctum) sur toutes les routes protégées
- RBAC : middleware `admin` sur les routes `/admin/*`
- Validation des inputs côté backend (Laravel Request)
- Vérification d'identité obligatoire avant accès au dashboard
- Auto-ban des utilisateurs abusifs (≥ 10 signalements)
- Données sensibles (CIN, lieu de naissance, document) exposées uniquement aux admins

---

## 15. Ce qui n'est PAS encore actif

- Temps réel WebSocket (Laravel Reverb) — actuellement remplacé par polling
- Refresh token / gestion proactive expiration Sanctum
- Tests automatisés (PHPUnit / Pest)
- Déploiement production / CI-CD
