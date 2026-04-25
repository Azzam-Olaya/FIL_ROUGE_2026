# Fichier de suivi des manquements (Missing)

Ce fichier répertorie toutes les fonctionnalités, configurations et éléments qui ne sont pas encore complets ou qui manquent dans le projet actuel.

## 🚧 Fonctionnalités Manquantes / Incomplètes
1. **Intégration de Paiement :** Pas encore de passerelle de paiement (ex: Stripe ou PayPal) pour le traitement réel des `Contracts`.
2. **WebSockets (Temps réel) :** Le module de messagerie (`/messages`) et les alertes (`Notification`) doivent être connectés à un système temps réel comme Laravel Reverb ou Pusher pour éviter le polling HTTP.
3. **Tests Automatisés :** 
   - Tests unitaires/fonctionnels Backend (PHPUnit / Pest) à étoffer.
   - Tests E2E Frontend (Cypress ou Playwright) manquants.
4. **Validation de fichiers :** Manque d'assurance sur le scan antivirus / validation approfondie des fichiers uploadés (briefs, pièces jointes messages).
5. **SEO & Meta (Welcome page) :** La page `Welcome.vue` n'a pas encore toutes les balises meta de référencement optimisées (SSR non mis en place, car c'est une SPA).
