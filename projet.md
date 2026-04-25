# Cahier des Charges : Projet MorLancer

## 1. Présentation du Projet
**Nom du projet :** MorLancer
**Type d'application :** Plateforme de Freelance en ligne (Mise en relation entre clients et freelances).
**Architecture Globale :** Application Web divisée en une API Backend et un Frontend "Single Page Application" (SPA) orienté Dashboard.

---

## 2. Acteurs du Système (Rôles)
Le système gère trois types d'utilisateurs distincts dont les accès et les interfaces diffèrent :
- **Administrateur (Admin) :** Gère la plateforme, valide les utilisateurs, modère le contenu et suit les statistiques globales.
- **Client :** Publie des missions, consulte les briefs des freelances, interagit avec eux, communique avec les freelances et gère les contrats et les paiements.
- **Freelance :** Publie des briefs,consulte les missions des clients, interagit avec eux, communique avec les clients et suit ses encaissements.

---

## 3. Spécifications Techniques

### 3.1. Partie Backend (API RESTful)
**Stack technologique :**
- **Framework :** Laravel 13 (PHP 8.3)
- **Base de données :** PostgreSQL (via Laravel Eloquent ORM)
- **Authentification :** Laravel Sanctum (Token-based API Authentication)
- **Outils :** PHPUnit pour les tests.

**Entités Principales (Base de données) :**
- `User` & `Role` : Gestion des utilisateurs et de leurs permissions.
- `Category` : Catégorisation des missions et des compétences.
- `Mission` & `MissionLike` : Demandes postées par les clients et l'engagement.
- `Briefs`& `BriefLike`, `BriefComment`, `BriefFavorite` : Projets réalisés par les freelances, avec un système d'interaction social (likes, commentaires, mises en favoris).
- `Message` : Messagerie interne.
- `Contract` : Contrats entre client et freelance.
- `Notification` : Alertes en temps réel.

**Modules de l'API (`routes/api.php`) :**
1. **Authentification (`/auth`) :** Inscription, connexion, déconnexion et vérification d'identité.
2. **Administration (`/admin`) :**
   - Validation, suspension et bannissement d'utilisateurs.
   - Statistiques globales de la plateforme.
   - Gestion CRUD des catégories métiers.
3. **Clients (`/client`) :**
   - Création et suivi de missions.
   - Suivi financier (paiements).
   - Découverte et interaction (like, commentaire, favori) avec les portfolios des freelances (`briefs`).
   - Recherche et interaction avec les briefs.
4. **Freelances (`/freelancer`) :**
   - Gestion du profil, du solde financier et des statistiques personnelles.
   - Gestion des briefs.
   - Recherche et interaction avec les missions actives.
5. **Messagerie (`/messages`) :** Communication directe et contextuelle liée aux contrats.
6. **Contrats (`/contracts`) :** Création, validation (complete), et remboursement (refund) des contrats.

---

### 3.2. Partie Frontend (Client Web)
**Stack technologique :**
- **Framework :** Vue.js 3 (Composition API) avec Vite.
- **State Management :** Pinia.
- **Routage :** Vue Router (Orienté SPA robuste).
- **Stylisation :** Tailwind CSS.
- **Requêtes HTTP :** Axios.

**Organisation de l'Interface (`src/views`) :**
- **Approche unifiée :** L'application a vocation à fonctionner comme un Dashboard dynamique. Au lieu de multiples pages rechargées, les vues métiers (Feed des missions, Briefs, Messagerie, Contrats) sont souvent encapsulées en onglets sur une seule vue Dashboard en fonction du rôle.
- **Dossiers par Rôle :**
  - `/Admin` : Interface de modération, tables d'utilisateurs, gestion des catégories et dashboards de statistiques.
  - `/Client` : Interface d'ajout de mission, creer et suivi des contrats, page de gestion des freelances favoris.
  - `/Freelancer` : Affichage et édition du profil, ajout de projets au briefs, board pour explorer les offres de missions.
  - `/Auth` : Formulaires spécialisés de login et d'enregistrement.
- **Composants Transversaux :**
  - **Welcome.vue :** Page de destination commerciale / Landing Page pour les visiteurs non connectés.
  - **Messaging.vue :** Interface de chat centralisée pour gérer les conversations avec les autres membres.

---

## 4. Fonctionnalités Clés et Workflow

1. **Onboarding :** Les utilisateurs s'inscrivent (Auth), l'Admin doit approuver les profils des freelances/clients (`/admin/users/{id}/approve`).
2. **Publication et briefs :** Le freelance expose ses compétences en publiant des `briefs`. Les clients consultent, aiment, mettent en favori et s'abonnent / commentent ces projets.
3. **Missions :** Le client dépose un besoin via les missions. Le freelance explore `missions/active` de son côté pour trouver du travail.
4. **Contractualisation & Discussion :** En cas d'accord, un `Contract` est généré, un fil de discussion `Message` exclusif au contrat est mis en place jusqu'à l'achèvement (`/complete`) ou annulation (`/refund`).
5. **Tableaux de bord (Dashboards) :** Tous les metrics (solde financier pour le freelance, dépenses pour le client, trafic pour l'admin) sont récupérés de façon granulaire par l'API et rafraîchis nativement sur l'interface VueJS, offrant une expérience fluide de type application mobile.
