# Fichier de suivi des manquements (Missing)

Ce fichier répertorie toutes les fonctionnalités, configurations et éléments qui ne sont pas encore complets ou qui manquent dans le projet actuel. Mis à jour le **27/04/2026** après audit complet du code source vs `context.md`.

---

## 🚧 Fonctionnalités Manquantes / Incomplètes

### 1. Intégration de Paiement réelle (context.md §5 — Financials)
- **Stripe** non intégré. `WalletController::addFunds()` est une simulation directe (commenté "to be replaced by Stripe/PayPal").
- **PayPal** : `capturePayPalOrder()` fonctionne uniquement en sandbox avec `curl` brut — pas de SDK officiel, pas de gestion des webhooks PayPal (ex : `PAYMENT.CAPTURE.DENIED`).
- La logique d'escrow est simulée via le champ `balance` sur le modèle `User` — pas de compte escrow dédié ni de traçabilité réelle des fonds bloqués par contrat.
- Route `/client/test-credit` exposée dans un groupe authentifié — à supprimer avant la mise en production.
- `VITE_PAYPAL_CLIENT_ID` non définie dans les variables d'environnement frontend.

### 2. WebSockets / Messagerie Temps Réel (context.md §3 — Laravel Reverb)
- Aucune intégration de **Laravel Reverb** ou Pusher n'est en place.
- `MessageController` fonctionne en HTTP classique uniquement.
- Les notifications utilisent du **HTTP Polling** (`setInterval` 30s) — pas de WebSockets.
- **Redis** absent du `docker-compose.yml` — requis pour Reverb/Broadcasting (context.md §3 mentionne Redis dans l'environnement Docker).
- La page `Messaging.vue` existe mais ne dispose pas de mise à jour en temps réel.

### 3. Tests Automatisés (context.md §6 — QA)
- **Backend :** Seuls les fichiers par défaut Laravel (`ExampleTest.php`). Pas de tests pour Auth, Contrats, Missions, Wallet, Admin.
- **Frontend :** Aucun framework E2E (Cypress/Playwright). Pas de tests unitaires de composants (Vitest).
- **Couverture actuelle : ~0%**.

### 4. RBAC Complet : Policies & Gates Laravel (context.md §4 — Roles)
- Aucun fichier `Policy` dans `/app/Policies/`.
- Seul le middleware `CheckAdminRole` protège les routes admin.
- **Routes `/client/*` et `/freelancer/*` sans middleware de rôle** (voir `fix.md` bug #2 et #5).
- `app/Http/CheckRole.php` existe mais est dans le mauvais namespace et non enregistré dans `bootstrap/app.php`.

### 5. Candidature aux Missions — Frontend absent (context.md §5.3)
- Le système de candidature (`MissionApplication`) est implémenté côté backend (`applyToMission`, `getApplications`, `acceptApplication`) mais **absent du frontend** — aucun composant Vue pour postuler à une mission.
- Pas de vue "Mes candidatures" pour le freelancer.
- Pas de statut `in_review` dans le workflow du contrat (context.md prévoit : `Draft → Active → Complete / Refund`).

### 6. Livraisons (Deliverables) — Backend existant, Frontend absent
- La migration `create_deliverables_table` et `DeliverableController.php` existent côté backend.
- Aucun composant frontend pour soumettre ou valider des livrables dans le dashboard.
- La notion de "livraison validée" pour déclencher le paiement n'est pas connectée à `complete()`.

### 7. Résolution de Litiges / Disputes (context.md §5.5)
- Migration `create_disputes_table` et `DisputeController.php` existent côté backend.
- `refund()` dans `ContractController` rembourse directement **sans intervention admin**.
- Pas de vue frontend pour ouvrir une dispute.
- Pas de workflow admin pour arbitrer un litige.

### 8. Profil Utilisateur Complet (context.md §4 — Freelancer)
- Pas de champs **avatar**, **bio**, **compétences (tags)**, **liens sociaux** dans le modèle `User` ou les migrations.
- `updateProfile` du `FreelancerController` ne gère que `name`, `payment_method`, `payment_account`.
- Pas de page profil public pour la découverte de freelancers.
- `getSuggestedFreelancers` retourne des faux avatars Unsplash hardcodés.

### 9. Store Pinia Admin Manquant
- Pas de store `admin.js` pour le frontend admin.
- Le dashboard admin fait ses appels API directement depuis les composants sans state management centralisé.

### 10. Emails / Notifications Système (context.md §5.1 — Onboarding)
- Pas d'envoi d'emails (confirmation d'inscription, validation de compte, reset mot de passe).
- `User` utilise `Notifiable` mais aucune classe `Notification` Laravel n'est implémentée.
- Pas de route `POST /forgot-password` ou `POST /reset-password`.

### 11. SEO & Meta (context.md §6 — SEO)
- `Welcome.vue` sans balises meta SEO optimisées.
- Pas de `sitemap.xml` ni `robots.txt` pour le frontend.
- SSR non mis en place (SPA pure — non indexable nativement par les moteurs de recherche).

### 12. CI/CD & Automatisation
- Pas de pipeline **GitHub Actions** (tests, lint, déploiement).
- Pas de configuration **ESLint/Prettier** pour Vue.js.
- Pas de **PHP-CS-Fixer** pour Laravel.

### 13. Recherche & Filtres Avancés (context.md §5.2 — Briefs)
- Recherche des briefs et missions limitée au champ `title`/`description` avec LIKE.
- Pas de filtre par **compétences**, **localisation**, **note**, ou **disponibilité**.
- `searchUsers` dans `MessageController` : LIKE sur `name` uniquement.

### 14. Seeders / Données de Démonstration
- Pas de seeder de données de démo (missions, briefs, contrats, utilisateurs) pour tester le flux complet.
- Pas de `UserFactory` personnalisé avec des données réalistes.
- Le seeder admin crée un utilisateur avec `'password' => 'password'` en clair — vérifier que le cast `hashed` fonctionne bien.

### 15. Documentation API
- Pas de documentation **Swagger/OpenAPI** ni de collection **Postman**.
- 34+ endpoints documentés uniquement par des commentaires PHP dans le code.

### 16. Variables d'Environnement Frontend
- `VITE_API_URL` non définie dans `.env` du frontend (baseURL hardcodée dans `axios.js`).
- `VITE_PAYPAL_CLIENT_ID` non définie.
- Pas de fichier `.env.example` dans le dossier `frontend/`.

### 17. Gestion des Images / Médias
- Les images uploadées (briefs, documents identité) sont stockées dans `storage/public` mais aucun lien symbolique `php artisan storage:link` n'est documenté ou automatisé dans le Dockerfile.
- Pas de système de compression ou de validation approfondie des images uploadées.

### 18. Middleware `CheckRole` non fonctionnel
- `app/Http/CheckRole.php` est dans le mauvais namespace (`App\Http` au lieu de `App\Http\Middleware`).
- L'alias `'role'` n'est pas enregistré dans `bootstrap/app.php`.
- Résultat : même si on écrit `->middleware('role:client')` dans les routes, cela génère une erreur 500.
- **Correction requise :**
  1. Déplacer vers `app/Http/Middleware/CheckRole.php`
  2. Corriger le namespace en `App\Http\Middleware`
  3. Ajouter dans `bootstrap/app.php` : `$middleware->alias(['role' => \App\Http\Middleware\CheckRole::class]);`
