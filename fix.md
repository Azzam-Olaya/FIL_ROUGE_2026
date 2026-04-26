# Fichier de suivi des bugs et problèmes (Fix)

Ce fichier liste les bugs identifiés, les problèmes en cours de résolution et l'historique des correctifs. Mis à jour le **27/04/2026** après audit complet du code source.

---

## 🐛 Bugs Ouverts / Problèmes Actifs

### 🔴 Priorité Critique

1. **`complete()` et `refund()` sans vérification d'autorisation**
   - **Fichier :** `ContractController.php` → `complete()`, `refund()`
   - **Problème :** Aucune vérification que l'utilisateur appelant est le client propriétaire du contrat. N'importe quel utilisateur authentifié peut compléter ou rembourser le contrat d'un autre.
   - **Impact :** Faille de sécurité critique — manipulation des fonds d'autrui.
   - **Fix :** Ajouter `if ($contract->client_id !== auth()->id()) abort(403);` en début de chaque méthode.

2. **Routes `/client/*` et `/freelancer/*` non protégées par rôle**
   - **Fichier :** `routes/api.php`
   - **Problème :** Les groupes `/client/*` et `/freelancer/*` n'ont que `auth:sanctum`. Un freelancer peut appeler `POST /client/missions` et vice versa.
   - **Impact :** Violation du RBAC — faille de sécurité.
   - **Fix :** Enregistrer l'alias `'role'` dans `bootstrap/app.php` puis appliquer `->middleware('role:client')` et `->middleware('role:freelancer')`.

3. **`acceptApplication` instancie directement un contrôleur**
   - **Fichier :** `ClientController.php` → `acceptApplication()`
   - **Problème :** `new \App\Http\Controllers\Api\ContractController()` est instancié manuellement sans passer le `Request` utilisateur correctement. `$contractData->user()` retourne `null`, provoquant une erreur 500 lors du solde insuffisant.
   - **Impact :** Création de contrat impossible via le flux candidature → acceptation.
   - **Fix :** Refactoriser la logique de création de contrat en service ou en méthode privée partagée.

4. **Contrat : pas de vérification du propriétaire de la mission**
   - **Fichier :** `ContractController.php` → `store()`
   - **Problème :** Le client qui crée un contrat n'est pas vérifié comme propriétaire de la mission. N'importe quel utilisateur peut créer un contrat sur la mission d'un autre.
   - **Impact :** Faille de sécurité.
   - **Fix :** Ajouter `if ($request->mission_id) { $m = Mission::findOrFail($request->mission_id); abort_if($m->client_id !== $client->id, 403); }`.

5. **`CheckRole.php` dans le mauvais namespace et non enregistré**
   - **Fichier :** `app/Http/CheckRole.php`
   - **Problème :** Le fichier est dans le namespace `App\Http` au lieu de `App\Http\Middleware`, et son alias `'role'` n'est pas enregistré dans `bootstrap/app.php`. Écrire `->middleware('role:client')` génère une erreur.
   - **Impact :** Le RBAC sur les routes client/freelancer est totalement inopérant.
   - **Fix :**
     1. Déplacer vers `app/Http/Middleware/CheckRole.php` et corriger le namespace.
     2. Ajouter dans `bootstrap/app.php` : `$middleware->alias(['role' => \App\Http\Middleware\CheckRole::class]);`

6. **Route `GET /api/debug-logs` exposée publiquement sans authentification**
   - **Fichier :** `routes/api.php`
   - **Problème :** La route retourne les 200 dernières lignes du log Laravel sans aucune protection.
   - **Impact :** Fuite d'informations sensibles (stack traces, données internes).
   - **Fix :** Supprimer cette route ou la protéger avec `auth:sanctum` + middleware admin.

---

### 🟠 Priorité Haute

7. **Anti-pattern `ensureTablesExist()` dans les contrôleurs**
   - **Fichiers :** `FreelancerController.php`, `ClientController.php`
   - **Problème :** Ces méthodes vérifient l'existence de tables et les créent manuellement via SQL brut, contournant les migrations. Exécutées à chaque requête.
   - **Impact :** Performance dégradée + architecture fragile + SQL non portable.
   - **Fix :** Supprimer ces méthodes et s'assurer que toutes les migrations sont correctement exécutées.

8. **`getActiveMissions` inclut les contrats annulés et refusés**
   - **Fichier :** `FreelancerController.php` → `getActiveMissions()`
   - **Problème :** Le filtre est `status != 'completed'`, donc les contrats `cancelled`, `refunded`, `pending_freelancer` sont inclus dans les "missions actives".
   - **Impact :** Missions erronées affichées dans le dashboard freelancer.
   - **Fix :** Changer le filtre en `->whereIn('status', ['active', 'pending_freelancer'])`.

9. **`AdminController` utilise des `role_id` hardcodés**
   - **Fichier :** `AdminController.php`
   - **Problème :** Les filtres utilisent `whereIn('role_id', [2,3])`, `where('role_id', 2)`, `where('role_id', 3)`. Si le seeder est relancé ou réordonné, les stats seront fausses.
   - **Impact :** Statistiques incorrectes en cas de réinitialisation de la base.
   - **Fix :** Utiliser `whereHas('role', fn($q) => $q->where('name', 'client'))`.

10. **Données de notification fictives en fallback dans les stores Pinia**
    - **Fichiers :** `stores/freelancer.js`, `stores/client.js`
    - **Problème :** En cas d'échec de l'API `/notifications`, des données fictives sont injectées. L'utilisateur voit de fausses notifications.
    - **Impact :** Confusion utilisateur, données non fiables.
    - **Fix :** Afficher un état vide ou un message d'erreur au lieu des données fictives.

11. **`stopPolling` manquant dans les stores — fuite mémoire**
    - **Fichiers :** `stores/freelancer.js`, `stores/client.js`
    - **Problème :** `startPolling` lance un `setInterval` mais aucune fonction `stopPolling` n'est exportée pour l'arrêter lors de la déconnexion.
    - **Impact :** Fuite mémoire — les requêtes continuent après déconnexion avec un token invalide.
    - **Fix :** Exporter `stopPolling() { clearInterval(_pollingTimer); _pollingTimer = null; }` et l'appeler dans `clearAuth()`.

12. **`getSuggestedFreelancers` renvoie des utilisateurs de n'importe quel rôle**
    - **Fichier :** `FreelancerController.php` → `getSuggestedFreelancers()`
    - **Problème :** Le filtre est `role_id != $userRoleId`, ce qui peut retourner des admins. Les avatars sont des URLs Unsplash hardcodées.
    - **Impact :** Données incorrectes affichées aux utilisateurs.
    - **Fix :** Filtrer explicitement `where('role_id', Role::where('name','freelancer')->value('id'))` et utiliser les vrais avatars.

13. **`WalletController::capturePayPalOrder` désactive la vérification SSL**
    - **Fichier :** `WalletController.php`
    - **Problème :** `curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false)` rend la connexion PayPal vulnérable aux attaques Man-in-the-Middle.
    - **Impact :** Risque de sécurité en production.
    - **Fix :** Supprimer cette ligne ou utiliser le SDK PayPal officiel.

---

### 🟡 Priorité Moyenne

14. **Info `debug` exposée dans la réponse `ContractController`**
    - **Fichier :** `ContractController.php`
    - **Problème :** En cas d'erreur, la réponse retourne `'debug' => 'Vérifiez si mission_id est obligatoire... Détail: ' . $e->getMessage()`.
    - **Fix :** Supprimer la clé `debug` et retourner un message générique en production.

15. **`mission_id` nullable avec fallback `Mission::first()` dans `ContractController`**
    - **Fichier :** `ContractController.php`
    - **Problème :** Si `mission_id` est null, le code récupère n'importe quelle première mission comme fallback (commenté "Debug").
    - **Impact :** Contrats associés à de mauvaises missions en base.
    - **Fix :** Supprimer ce bloc de debug et gérer proprement le cas `mission_id = null`.

16. **Axios `baseURL` codée en dur**
    - **Fichier :** `frontend/src/api/axios.js`
    - **Problème :** `baseURL: 'http://localhost:8000/api'` empêche tout déploiement en staging/production.
    - **Fix :** Utiliser `import.meta.env.VITE_API_URL`.

17. **Champs sensibles exposés dans l'API (`id_document_path`, `id_number`, `birthplace`)**
    - **Fichier :** `User.php` → `$hidden`
    - **Problème :** Ces champs ne sont pas dans `$hidden` et sont exposés dans toutes les réponses JSON contenant un user.
    - **Fix :** Ajouter ces champs au tableau `$hidden`.

18. **Pas de gestion proactive de l'expiration du token Sanctum**
    - **Fichier :** `frontend/src/api/axios.js`
    - **Problème :** L'intercepteur 401 est réactif uniquement. Pas de mécanisme de refresh token ni d'avertissement avant expiration.
    - **Impact :** Déconnexion abrupte sans avertissement.
    - **Fix :** Implémenter un avertissement ou un mécanisme de refresh (identifié dans `context.md` §6).

19. **Pas de validation `password_confirmation` à l'inscription**
    - **Fichier :** `AuthController.php` → `register()`
    - **Problème :** Pas de champ `password_confirmation` pour éviter les erreurs de saisie.
    - **Fix :** Ajouter `'password' => 'required|string|min:8|confirmed'`.

20. **Page de connexion : erreurs affichées avec `alert()` natif**
    - **Fichier :** `frontend/src/views/Auth/Login.vue`
    - **Problème :** Les erreurs d'authentification sont affichées via `alert()` — expérience utilisateur dégradée, sans distinction visuelle entre "mauvais mot de passe" et "compte en attente".
    - **Fix :** Remplacer `alert()` par des messages d'erreur inline stylisés.

21. **`WalletController::getSummary` — logique `escrow_in`/`escrow_out` inversée**
    - **Fichier :** `WalletController.php`
    - **Problème :** `total_earned` est calculé avec `type = 'escrow_out'` et `total_spent` avec `type = 'escrow_in'`. La sémantique semble inversée.
    - **Fix :** Vérifier et aligner la nomenclature avec les `Transaction::create()` effectués dans le code.

---

### 🟢 Priorité Basse

22. **HTTP Polling pour les notifications (toutes les 30s)**
    - **Fichiers :** `stores/freelancer.js`, `stores/client.js`
    - **Problème :** Charge réseau inutile — chaque utilisateur envoie 2 req/min.
    - **Fix :** Remplacer par WebSockets (Laravel Reverb) — prévu dans `context.md` §6.

23. **`docker-compose.yml` utilise la clé `version` dépréciée**
    - **Fichier :** `docker-compose.yml`
    - **Problème :** `version: '3.8'` est déprécié dans Docker Compose v2+.
    - **Fix :** Supprimer la ligne `version`.

24. **`searchUsers` mal placé dans `MessageController`**
    - **Fichier :** `MessageController.php`
    - **Problème :** La recherche d'utilisateurs est dans un contrôleur de messagerie — violation du principe de responsabilité unique.
    - **Fix :** Déplacer vers un `UserController` dédié.

25. **Composants Vue dupliqués**
    - **Fichiers :** `components/AdminStats.vue`, `components/AdminUsers.vue` (doublons de `components/Admin/`)
    - **Problème :** Ces composants existent en double, ce qui peut créer de la confusion lors des imports.
    - **Fix :** Supprimer les doublons à la racine de `components/`.

---

## ✅ Bugs Résolus (Historique)

- **Problème d'authentification (Résolu)** : Résolution du problème de redirection empêchant le chargement du dashboard admin après une connexion réussie.
- **Routage Freelance (Résolu)** : Unification du parcours Freelance sur une seule page dynamique.
- **500 Notifications Freelancer (Résolu)** : Correction de l'erreur 500 sur l'endpoint `/freelancer/notifications`.
- **Barre de recherche manquante (Résolu)** : Restauration de la fonctionnalité de recherche dans le dashboard freelancer.
