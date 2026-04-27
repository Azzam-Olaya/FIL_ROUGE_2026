# Software Requirements Specification (SRS) — MorLancer
**Version :** 2.0  
**Date :** Juin 2026  
**Statut :** En développement actif

---

## 1. Introduction

### 1.1 Objectif
Ce document définit les exigences fonctionnelles et non-fonctionnelles de la plateforme **MorLancer**. Il sert de référence principale pour les développeurs et les parties prenantes.

### 1.2 Périmètre
MorLancer est une marketplace freelance marocaine connectant des **Freelancers** (professionnels indépendants) et des **Clients** (entreprises ou particuliers). La plateforme s'appuie sur un backend Laravel 13 et un frontend Vue.js 3 SPA pour gérer les missions, les Briefs, la messagerie, les contrats et les paiements sécurisés.

---

## 2. Architecture & Stack Technique

| Composant | Technologie | Description |
|-----------|-------------|-------------|
| **API Layer** | Laravel 13 (PHP 8.3) | API RESTful avec logique métier et RBAC |
| **Client Layer** | Vue.js 3 (Pinia, Vue Router 4) | SPA réactive avec Composition API |
| **Data Store** | PostgreSQL 15 | Base de données relationnelle |
| **Auth** | Laravel Sanctum | Authentification par token Bearer |
| **Temps réel** | Polling HTTP (3–30s) | Remplacement temporaire de WebSockets |
| **Graphes** | Chart.js | Visualisation des statistiques admin |
| **Styles** | Tailwind CSS | Framework CSS utilitaire |
| **Build** | Vite | Bundler frontend |
| **DevOps** | Docker | PHP-FPM, Nginx, PostgreSQL, Redis |

---

## 3. Rôles Utilisateurs

| Rôle | Responsabilités |
|------|----------------|
| **Admin** | Validation des comptes, statistiques plateforme, gestion des catégories, traitement des signalements |
| **Client** | Publication de missions, exploration des Briefs, gestion des contrats et paiements |
| **Freelancer** | Publication de Briefs, exploration des missions, gestion des contrats et wallet |

---

## 4. Exigences Fonctionnelles

### 4.1 Authentification & Onboarding

| ID | Exigence |
|----|----------|
| F-AUTH-01 | Inscription avec choix du rôle (Client ou Freelancer), validation unicité email/nom |
| F-AUTH-02 | Upload obligatoire d'un document d'identité (CIN/Passeport) + numéro + lieu de naissance |
| F-AUTH-03 | Compte créé avec statut `pending` — approbation manuelle par l'admin requise |
| F-AUTH-04 | Connexion par email/mot de passe, token Bearer Sanctum, redirection selon rôle |
| F-AUTH-05 | Blocage de connexion si statut `pending`, `rejected` ou `banned` |
| F-AUTH-06 | Déconnexion avec suppression du token côté serveur |

### 4.2 Dashboard Admin

| ID | Exigence |
|----|----------|
| F-ADM-01 | Tableau de bord avec KPI réels (utilisateurs, missions, contrats, revenus) et tendances mensuelles |
| F-ADM-02 | Graphes Chart.js : bar inscriptions/semaine, doughnut utilisateurs, doughnut contrats, line contrats/semaine, bar dépôts/semaine |
| F-ADM-03 | Top Freelancers par missions complétées |
| F-ADM-04 | Liste des utilisateurs avec filtres (rôle, statut) et colonnes : Nom, Email, N° CIN, Né(e) à, Document, Date, Statut |
| F-ADM-05 | Actions sur utilisateurs : Approuver, Rejeter, Bannir, Débannir |
| F-ADM-06 | CRUD complet des catégories métiers (parent + sous-catégories) |
| F-ADM-07 | Visualisation de tous les contrats de la plateforme |
| F-ADM-08 | Visualisation de toutes les transactions |
| F-ADM-09 | Liste des signalements avec badge coloré, actions Ignorer / Bannir |
| F-ADM-10 | Auto-ban automatique si un utilisateur cumule ≥ 10 signalements |

### 4.3 Dashboard Client

| ID | Exigence |
|----|----------|
| F-CLI-01 | Feed des Briefs avec recherche par mot-clé et filtres catégorie/sous-catégorie |
| F-CLI-02 | Interactions sur les Briefs : Like, Commentaire, Favori, Contacter |
| F-CLI-03 | Clic sur un Brief favori depuis la navbar → scroll automatique vers le Brief dans le feed |
| F-CLI-04 | Création de mission : titre, description, budget (DH), deadline, catégories |
| F-CLI-05 | Liste des missions publiées avec statut et onglet commentaires |
| F-CLI-06 | Messagerie avec les freelancers : envoi de messages, polling 3s |
| F-CLI-07 | Lancement de contrat depuis la messagerie via modal |
| F-CLI-08 | Signalement d'un interlocuteur depuis la messagerie |
| F-CLI-09 | Gestion des contrats : Compléter, Rembourser, suivi des statuts |
| F-CLI-10 | Wallet : solde disponible, total déposé/dépensé, escrow bloqué, dépôt PayPal |

### 4.4 Dashboard Freelancer

| ID | Exigence |
|----|----------|
| F-FRL-01 | Feed des missions avec recherche par mot-clé et catégorie |
| F-FRL-02 | Interactions sur les missions : Like, Commenter, Favori, Contacter |
| F-FRL-03 | Création de Brief : titre, description, catégories, prix (DH), délai |
| F-FRL-04 | Liste des Briefs publiés avec likes et commentaires reçus |
| F-FRL-05 | Alerte visuelle pour les propositions de contrat en attente |
| F-FRL-06 | Modal de révision de contrat : Accepter ou Refuser |
| F-FRL-07 | Messagerie avec les clients : envoi de messages, polling 3s |
| F-FRL-08 | Signalement d'un interlocuteur depuis la messagerie |
| F-FRL-09 | Gestion des contrats actifs et historique |
| F-FRL-10 | Wallet : solde disponible, revenus, escrow |

### 4.5 Système de Signalement

| ID | Exigence |
|----|----------|
| F-REP-01 | Bouton 🚩 dans chaque conversation (Client et Freelancer) |
| F-REP-02 | Formulaire : raison parmi 6 choix + détails optionnels |
| F-REP-03 | Impossible de se signaler soi-même |
| F-REP-04 | Un seul signalement `pending` par paire reporter/reporté |
| F-REP-05 | Auto-ban automatique à ≥ 10 signalements cumulés |

### 4.6 Système de Contrats

| Statut | Description |
|--------|-------------|
| `pending_freelancer` | Proposition envoyée, en attente d'acceptation |
| `active` | Contrat accepté, mission en cours |
| `completed` | Mission validée, paiement libéré |
| `refunded` | Remboursement effectué |
| `cancelled` | Contrat annulé |

### 4.7 Messagerie

| ID | Exigence |
|----|----------|
| F-MSG-01 | Conversations directes entre Client et Freelancer |
| F-MSG-02 | Polling toutes les 3 secondes pour les nouveaux messages |
| F-MSG-03 | Ouverture automatique depuis un Brief (bouton Contacter) |
| F-MSG-04 | Lancement de contrat intégré côté client |
| F-MSG-05 | Signalement de l'interlocuteur intégré |

### 4.8 Notifications

| ID | Exigence |
|----|----------|
| F-NOT-01 | Notifications en base de données |
| F-NOT-02 | Polling côté frontend via stores Pinia |
| F-NOT-03 | Déclenchées par : propositions de contrat, mises à jour de statut |

---

## 5. Exigences Non-Fonctionnelles

### 5.1 Performance
- Navigation SPA instantanée via Vue Router et cache Pinia
- Réponse API < 200ms pour les opérations CRUD standard
- Polling messagerie toutes les 3s, notifications toutes les 30s

### 5.2 Sécurité
- Token Bearer Sanctum sur toutes les routes protégées
- RBAC : middleware `admin` sur `/admin/*`
- Validation des inputs côté backend (Laravel Request)
- Vérification d'identité obligatoire avant accès au dashboard
- Données sensibles (CIN, lieu de naissance, document) exposées uniquement aux admins
- Auto-ban des utilisateurs abusifs (≥ 10 signalements)

### 5.3 Scalabilité
- Architecture découplée : frontend déployable via CDN, API scalable horizontalement
- Conteneurisation Docker pour tous les services

---

## 6. Schéma Base de Données (21 tables actives)

| Table | Relations clés |
|-------|---------------|
| `users` | BelongsTo Role, HasMany Briefs, Missions, Contracts, Messages |
| `roles` | HasMany Users |
| `categories` | HasMany Children (self), BelongsToMany Briefs, Missions |
| `briefs` | BelongsTo User, HasMany BriefLikes, BriefComments, BriefFavorites |
| `brief_likes` | BelongsTo Brief, User |
| `brief_comments` | BelongsTo Brief, User |
| `brief_favorites` | BelongsTo Brief, User |
| `brief_categories` | Pivot Brief ↔ Category |
| `missions` | BelongsTo User (client), HasMany MissionLikes, MissionComments, Contracts |
| `mission_likes` | BelongsTo Mission, User |
| `mission_comments` | BelongsTo Mission, User |
| `mission_favorites` | BelongsTo Mission, User |
| `mission_applications` | BelongsTo Mission, User (freelancer) |
| `mission_category` | Pivot Mission ↔ Category |
| `contracts` | BelongsTo Mission, Client, Freelancer |
| `messages` | BelongsTo Sender, Receiver |
| `notifications` | BelongsTo User |
| `transactions` | BelongsTo User |
| `reports` | BelongsTo Reporter, Reported |
| `personal_access_tokens` | Sanctum tokens |
| `migrations` | Historique Laravel |

---

## 7. Routes API (62 routes actives)

### Auth (5)
`POST /register` · `POST /login` · `POST /logout` · `POST /user/verify` · `GET /user`

### Admin (13)
`GET /admin/stats` · `GET /admin/users` · `GET /admin/pending-users`  
`POST /admin/users/{id}/approve|reject|ban`  
`GET|POST /admin/categories` · `PUT|DELETE /admin/categories/{id}`  
`GET /admin/reports` · `POST /admin/reports/{id}/dismiss|ban`

### Client (14)
`GET /client/briefs` · `GET /client/categories` · `GET /client/favorites` · `GET /client/stats`  
`POST|GET /client/missions`  
`POST /client/briefs/{id}/like|comment|favorite` · `GET /client/briefs/{id}/comments`  
`GET /client/notifications`  
`GET /client/missions/{id}/applications` · `POST /client/applications/{id}/accept`  
`POST /client/test-credit`

### Freelancer (18)
`GET|POST /freelancer/briefs` · `GET /freelancer/briefs/mine`  
`POST /freelancer/briefs/{id}/like|comment|favorite` · `GET /freelancer/briefs/{id}/comments`  
`GET /freelancer/missions` · `GET /freelancer/missions/favorites`  
`POST /freelancer/missions/{id}/like|favorite|comment|apply`  
`GET /freelancer/missions/{id}/comments`  
`GET /freelancer/favorites` · `GET /freelancer/notifications` · `GET /freelancer/categories`

### Messagerie (3)
`GET /conversations` · `GET /conversations/{userId}` · `POST /messages`

### Contrats (7)
`POST /contracts` · `GET /contracts/client` · `GET /contracts/freelancer`  
`POST /contracts/{id}/complete|refund|accept|reject`

### Wallet (2)
`GET /wallet/summary` · `POST /wallet/paypal/capture`

### Signalements (1)
`POST /reports`

---

## 8. Roadmap

### Complété ✅
- Architecture découplée Backend/Frontend
- Authentification complète avec RBAC et vérification d'identité
- Dashboard Admin avec statistiques réelles (Chart.js)
- Dashboard Client : Briefs, Missions, Messagerie, Contrats, Wallet
- Dashboard Freelancer : Missions, Briefs, Messagerie, Contrats, Wallet
- Système de signalement avec auto-ban
- Paiements PayPal sandbox (escrow)
- Nettoyage complet du code mort (routes, tables, composants)

### Non encore actif 🚧
- Temps réel WebSocket (Laravel Reverb) — actuellement polling
- Refresh token / gestion proactive expiration Sanctum
- Tests automatisés (PHPUnit / Pest)
- Déploiement production / CI-CD
