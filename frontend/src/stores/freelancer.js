/**
 * Store Pinia — État global du freelancer
 *
 * Ce store est la source unique de vérité pour :
 *  - Les missions favorites (synchronisées entre le feed et NavFavorites)
 *  - Les missions likées (synchronisées dans le feed)
 *  - Les notifications (messages, likes sur briefs, commentaires sur briefs)
 *  - La session utilisateur (lecture seule depuis localStorage)
 *
 * Aucune logique métier backend n'est modifiée ici.
 * Les composants navbar et le dashboard consomment ce store
 * pour rester synchronisés sans couplage direct.
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useFreelancerStore = defineStore('freelancer', () => {

  // ─── Session ────────────────────────────────────────────────────────────────

  /** Données de l'utilisateur connecté (depuis localStorage) */
  const user = ref(JSON.parse(localStorage.getItem('user') || '{}'))

  /** Nom affiché dans la navbar et le dashboard */
  const userName = computed(() => user.value?.name || 'Freelancer')

  /** Initiales pour l'avatar */
  const userInitials = computed(() =>
    user.value?.name?.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) || 'U'
  )

  /** En-têtes HTTP avec le token JWT */
  const authHeaders = computed(() => ({
    Authorization: `Bearer ${localStorage.getItem('token')}`,
  }))

  // ─── Favoris ────────────────────────────────────────────────────────────────

  /**
   * Liste des missions complètes marquées comme favorites.
   * Stockée ici pour que NavFavorites puisse les afficher
   * sans refaire un appel API séparé.
   */
  const favoritedMissions = ref([])

  /** IDs des missions favorites (pour vérification rapide dans le feed) */
  const favoritedIds = computed(() => favoritedMissions.value.map(m => m.id))

  /** Vérifie si une mission est dans les favoris */
  const isFavorited = (missionId) => favoritedIds.value.includes(missionId)

  /**
   * Bascule le statut favori d'une mission.
   * Met à jour la liste complète pour que NavFavorites soit synchronisé.
   * @param {Object} mission — objet mission complet du feed
   */
  const toggleFavorite = (mission) => {
    if (isFavorited(mission.id)) {
      favoritedMissions.value = favoritedMissions.value.filter(m => m.id !== mission.id)
    } else {
      favoritedMissions.value.push(mission)
    }
  }

  // ─── Likes ──────────────────────────────────────────────────────────────────

  /** IDs des missions likées par le freelancer */
  const likedIds = ref([])

  /** Vérifie si une mission est likée */
  const isLiked = (missionId) => likedIds.value.includes(missionId)

  /**
   * Bascule le like d'une mission et met à jour le compteur local.
   * @param {Object} mission — objet mission du feed (modifié en place)
   */
  const toggleLike = (mission) => {
    if (isLiked(mission.id)) {
      likedIds.value = likedIds.value.filter(id => id !== mission.id)
      mission.likes = Math.max(0, (mission.likes || 1) - 1)
    } else {
      likedIds.value.push(mission.id)
      mission.likes = (mission.likes || 0) + 1
    }
  }

  // ─── Notifications ──────────────────────────────────────────────────────────

  /**
   * Liste des notifications.
   * Trois types supportés : 'message' | 'like' | 'comment'
   * Chaque notification : { id, type, title, message, read, createdAt }
   */
  const notifications = ref([])

  /** Nombre de notifications non lues (badge navbar) */
  const unreadCount = computed(() => notifications.value.filter(n => !n.read).length)

  /** Marque toutes les notifications comme lues */
  const markAllRead = () => {
    notifications.value.forEach(n => { n.read = true })
  }

  /** Marque une notification spécifique comme lue */
  const markRead = (notifId) => {
    const n = notifications.value.find(n => n.id === notifId)
    if (n) n.read = true
  }

  /**
   * Charge les notifications depuis l'API.
   * Fallback sur des données fictives si l'API est indisponible.
   */
  const fetchNotifications = async () => {
    try {
      const res = await axios.get('http://localhost:8000/api/freelancer/notifications', {
        headers: authHeaders.value,
      })
      notifications.value = res.data
    } catch {
      // Données fictives de démonstration
      notifications.value = [
        { id: 1, type: 'message', title: 'Nouveau message', message: 'Mehdi El Fassi : "Êtes-vous disponible ?"', read: false, createdAt: new Date().toISOString() },
        { id: 2, type: 'like',    title: '❤️ Like sur votre brief', message: 'Expert Zellige — 5 nouveaux likes', read: false, createdAt: new Date().toISOString() },
        { id: 3, type: 'comment', title: '💬 Commentaire sur votre brief', message: '"Superbe travail, très intéressé..."', read: false, createdAt: new Date().toISOString() },
      ]
    }
  }

  // ─── Recherche (état partagé avec MissionSearch) ────────────────────────────

  /**
   * Terme de recherche actif.
   * Écrit par NavSearch, lu par le Dashboard pour pré-remplir MissionSearch.
   */
  const searchQuery = ref('')

  /** Catégorie sélectionnée dans la navbar */
  const searchCategory = ref('')

  return {
    // Session
    user, userName, userInitials, authHeaders,
    // Favoris
    favoritedMissions, favoritedIds, isFavorited, toggleFavorite,
    // Likes
    likedIds, isLiked, toggleLike,
    // Notifications
    notifications, unreadCount, markAllRead, markRead, fetchNotifications,
    // Recherche
    searchQuery, searchCategory,
  }
})
