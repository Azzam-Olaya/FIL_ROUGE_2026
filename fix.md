# Fichier de suivi des bugs et problèmes (Fix)

Ce fichier liste les bugs identifiés, les problèmes en cours de résolution et l'historique des correctifs. Mis à jour le **25/04/2026** après audit complet du code source.

---

## 🐛 Bugs Ouverts / Problèmes Actifs

### Priorité Haute

1. **Routes Client/Freelancer non protégées par rôle**
   - **Fichier :** `routes/api.php` (lignes 33-73)
   - **Problème :** Les groupes de routes `/client/*` et `/freelancer/*` n'ont aucun middleware de rôle. Seul `auth:sanctum` est appliqué. Un freelancer authentifié peut appeler les endpoints client (`POST /client/missions`, etc.) et vice versa.
   - **Impact :** Faille de sécurité — violation du RBAC.
   - **Fix suggéré :** Créer un middleware `CheckRole` générique et l'appliquer : `->middleware('role:client')` et `->middleware('role:freelancer')`.

2. **Anti-pattern `ensureTablesExist()` dans les contrôleurs**
   - **Fichiers :** `FreelancerController.php` (L358-374), `ClientController.php` (L181-197)
   - **Problème :** Ces méthodes vérifient l'existence de tables en base à chaque requête via `Schema::hasTable()`. C'est un anti-pattern qui génère des requêtes SQL inutiles et contourne le système de migrations de Laravel.
   - **Impact :** Performance dégradée + architecture fragile.
   - **Fix suggéré :** Supprimer ces méthodes et s'assurer que les migrations sont correctement exécutées.

3. **Données de notification hardcodées dans les stores Pinia**
   - **Fichiers :** `stores/freelancer.js` (L52-57), `stores/client.js` (L55-59)
   - **Problème :** En cas d'échec de l'API `/notifications`, des données fictives sont injectées dans le state. L'utilisateur voit de fausses notifications.
   - **Impact :** Confusion utilisateur, données non fiables.
   - **Fix suggéré :** Afficher un message d'erreur ou un état vide au lieu de données fictives.

### Priorité Moyenne

4. **Axios baseURL codée en dur (localhost)**
   - **Fichier :** `frontend/src/api/axios.js` (ligne 6)
   - **Problème :** `baseURL: 'http://localhost:8000/api'` est hardcodé. Cela empêche le déploiement en production ou staging sans modifier le code source.
   - **Fix suggéré :** Utiliser une variable d'environnement Vite : `import.meta.env.VITE_API_URL`.

5. **Pas de gestion proactive de l'expiration du token Sanctum**
   - **Fichier :** `frontend/src/api/axios.js`
   - **Problème :** Le token expiré est détecté uniquement par l'intercepteur 401 (réactif). Il n'y a pas de mécanisme de refresh token ni de vérification proactive de l'expiration.
   - **Impact :** L'utilisateur est déconnecté abruptement sans avertissement.
   - **Fix suggéré :** Implémenter un mécanisme de token refresh ou au minimum afficher un modal d'avertissement avant expiration.

6. **Contrat : pas de vérification du propriétaire de la mission**
   - **Fichier :** `ContractController.php` → `store()`
   - **Problème :** Le client qui crée un contrat n'est pas vérifié comme étant le propriétaire de la mission (`mission.client_id === auth()->id`). N'importe quel utilisateur authentifié peut créer un contrat sur la mission d'un autre.
   - **Impact :** Faille de sécurité — un utilisateur peut générer des contrats non autorisés.
   - **Fix suggéré :** Ajouter `$mission = Mission::findOrFail($request->mission_id); if ($mission->client_id !== $client->id) abort(403);`.

7. **Contrat : `complete()` et `refund()` sans vérification d'autorisation**
   - **Fichier :** `ContractController.php` → `complete()`, `refund()`
   - **Problème :** Aucune vérification que l'utilisateur qui finalise ou rembourse le contrat est bien le client propriétaire du contrat.
   - **Impact :** N'importe quel utilisateur authentifié peut compléter ou rembourser le contrat d'un autre.
   - **Fix suggéré :** Ajouter `if ($contract->client_id !== auth()->id) abort(403);`.

8. **Incohérence `id_document_path` exposé dans la réponse API**
   - **Fichier :** `User.php` → `$hidden`
   - **Problème :** Le champ `id_document_path` (chemin vers le document d'identité CIN/Passeport) n'est pas dans `$hidden`. Ce chemin sensible est retourné dans toutes les réponses JSON contenant un user.
   - **Fix suggéré :** Ajouter `'id_document_path', 'id_number', 'birthplace'` au tableau `$hidden`.

### Priorité Basse

9. **HTTP Polling pour les notifications (toutes les 30s)**
   - **Fichiers :** `stores/freelancer.js` (L62-66), `stores/client.js` (L64-68)
   - **Problème :** Charge réseau inutile car chaque utilisateur connecté envoie une requête HTTP toutes les 30 secondes.
   - **Impact :** Scalabilité limitée avec beaucoup d'utilisateurs simultanés.
   - **Fix suggéré :** À remplacer par WebSockets (Laravel Reverb) quand l'intégration sera faite.

10. **Pas de validation `password_confirmation` à l'inscription**
    - **Fichier :** `AuthController.php` → `register()`
    - **Problème :** Le champ `password` est validé avec `'required|string|min:8'` mais il n'y a pas de champ `password_confirmation` pour éviter les erreurs de saisie.
    - **Fix suggéré :** Ajouter `'password' => 'required|string|min:8|confirmed'`.

11. **`docker-compose.yml` utilise la version dépréciée**
    - **Fichier :** `docker-compose.yml` (ligne 1)
    - **Problème :** `version: '3.8'` est déprécié dans les versions récentes de Docker Compose.
    - **Fix suggéré :** Supprimer la ligne `version` (Docker Compose v2+ l'ignore).

12. **`SearchUsers` mal placé dans `MessageController`**
    - **Fichier :** `MessageController.php`
    - **Problème :** La recherche d'utilisateurs est un endpoint générique qui n'a rien à voir spécifiquement avec la messagerie. Violation du principe de responsabilité unique.
    - **Fix suggéré :** Déplacer vers un `UserController` dédié.

---

## ✅ Bugs Résolus (Historique)

- **Problème d'Authentification (Résolu)** : Résolution du problème de redirection empêchant le chargement du dashboard admin après une connexion réussie.
- **Routage Freelance (Résolu)** : Unification du parcours Freelance sur une seule page dynamique.
- **500 Notifications Freelancer (Résolu)** : Correction de l'erreur 500 sur l'endpoint `/freelancer/notifications`.
- **Barre de recherche manquante (Résolu)** : Restauration de la fonctionnalité de recherche dans le dashboard freelancer.
