# Fichier de suivi des manquements (Missing)

Ce fichier répertorie toutes les fonctionnalités, configurations et éléments qui ne sont pas encore complets ou qui manquent dans le projet actuel. Mis à jour le **25/04/2026** après audit complet du code source vs le SRS.

---

## 🚧 Fonctionnalités Manquantes / Incomplètes

### 1. Intégration de Paiement (SRS §3.3 / §6 Phase 2)
Pas encore de passerelle de paiement réelle (Stripe ou PayPal). Le `ContractController` utilise un système de `balance` interne sur le modèle `User` avec `increment`/`decrement`, mais aucun appel à une API de paiement externe n'est implémenté. La logique d'escrow (fonds bloqués) est simulée en mémoire.

### 2. WebSockets / Temps Réel (SRS §3.4)
- Aucune intégration de **Laravel Reverb** ou Pusher n'est en place.
- Le module de messagerie (`MessageController`) fonctionne en HTTP classique (requête/réponse).
- Les notifications (`freelancer.js`, `client.js`) utilisent du **HTTP Polling** (`setInterval` toutes les 30 secondes) au lieu de WebSockets.
- Les stores Pinia contiennent des **données de notification codées en dur** (fallback hardcoded) en cas d'échec de l'API.
- Le service **Redis** est absent du `docker-compose.yml`, il est nécessaire pour Laravel Reverb/Broadcasting.

### 3. Tests Automatisés (SRS §6 Phase 3)
- **Backend :** Seuls les fichiers par défaut de Laravel existent (`ExampleTest.php` dans `Feature/` et `Unit/`). Aucun test fonctionnel réel (Auth, Contracts, Missions, Messages, Admin).
- **Frontend :** Aucun framework de test E2E (Cypress ou Playwright) configuré. Aucun test unitaire de composants.
- **Couverture actuelle :** ~0% (objectif SRS : >80%).

### 4. RBAC : Policies & Gates Laravel (SRS §3.1)
- Aucun fichier `Policy` n'existe dans `/app/Policies/`.
- Seul le middleware `CheckAdminRole` protège les routes admin.
- **Les routes Client (`/client/*`) et Freelancer (`/freelancer/*`) ne sont protégées par aucun middleware de rôle.** Un freelancer authentifié peut théoriquement accéder aux endpoints client et vice versa.
- Le SRS exige « Implementation of Laravel gates/policies to restrict dashboard access based on user type ».

### 5. Candidature / Application aux Missions (SRS §3.3)
Le cycle de vie Mission → Application → Contrat décrit dans le SRS n'est pas implémenté :
- Pas de système de **candidature** (un freelancer qui postule à une mission).
- Le contrat est créé directement par le client sans flux d'application/sélection.
- Pas de statut `In Review` dans le workflow du contrat (le SRS prévoit `Draft → Active → In Review → Completed → Paid`).

### 6. Résolution de Litiges / Disputes (SRS §3.3)
- Le `refund()` dans `ContractController` rembourse directement sans intervention admin.
- Le SRS exige : « Admin intervention logic for refunds or mediation ».
- Pas de module de dispute ou d'arbitrage.

### 7. Profil Utilisateur Complet (SRS §3.1)
- Le modèle `User` ne contient pas de champs pour **avatar**, **bio**, **skill tags**, ou **liens sociaux** (exigés par le SRS).
- Le `updateProfile` du `FreelancerController` ne gère que `name` et `email`.
- Pas de page profil public pour la découverte de freelancers.

### 8. Validation de Fichiers Avancée
- La vérification d'identité (`verifyIdentity`) valide uniquement les extensions (`mimes:pdf,jpg,jpeg,png`) et la taille (`max:4096`).
- Pas de scan antivirus ni de validation approfondie du contenu des fichiers uploadés (briefs, pièces jointes messages).

### 9. SEO & Meta (Welcome Page)
- La page `Welcome.vue` n'a pas de balises meta de référencement optimisées.
- SSR non mis en place (SPA pure).
- Pas de `sitemap.xml` ni de `robots.txt` configuré pour le frontend.

### 10. Email / Notifications Système
- Pas de système d'envoi d'emails (confirmation d'inscription, notification de validation de compte, reset de mot de passe).
- Le modèle `User` utilise `Notifiable` mais aucune classe `Notification` Laravel n'est implémentée.

### 11. CI/CD & Automatisation (SRS §6 Phase 3)
- Pas de pipeline **GitHub Actions** pour les tests automatisés, le linting ou le déploiement.
- Pas de configuration ESLint/Prettier pour le frontend ni de PHP-CS-Fixer pour le backend.

### 12. Recherche & Filtres Avancés (SRS §3.2)
- La recherche de freelancers (`searchUsers` dans `MessageController`) est basique (LIKE sur le nom uniquement).
- Pas de recherche/filtre par compétences, catégorie, localisation, ou évaluation de briefs comme décrit dans le SRS.

### 13. Pinia Store Admin Manquant
- Il n'existe pas de store Pinia `admin.js` pour le frontend Admin.
- Le dashboard Admin fait ses appels API directement sans state management centralisé.

### 14. Seeders / Données de Test
- Le répertoire `database/seeders/` contient 3 fichiers mais l'état de leurs données n'est pas vérifié pour un environnement de démo complet.
- Pas de `UserFactory` personnalisé au-delà du défaut Laravel.

### 15. Documentation API
- Pas de documentation API (Swagger/OpenAPI ou Postman collection) pour les développeurs.
