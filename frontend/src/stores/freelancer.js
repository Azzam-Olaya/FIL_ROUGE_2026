import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../api/axios'
import { useAuthStore } from './auth'

export const useFreelancerStore = defineStore('freelancer', () => {
  const authStore = useAuthStore()

  const user = computed(() => authStore.user)
  const userName = computed(() => user.value?.name || 'Freelancer')
  const userInitials = computed(() =>
    user.value?.name?.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) || 'U'
  )

  // ── Favorites ──────────────────────────────────────────────────────────────
  const favoritedMissions = ref([])
  const favoritedIds = computed(() => favoritedMissions.value.map(m => m.id))
  const isFavorited = (id) => favoritedIds.value.includes(id)
  const toggleFavorite = (mission) => {
    if (isFavorited(mission.id)) {
      favoritedMissions.value = favoritedMissions.value.filter(m => m.id !== mission.id)
    } else {
      favoritedMissions.value.push(mission)
    }
  }

  const favoritedBriefs = ref([])
  const isBriefFavorited = (id) => favoritedBriefs.value.some(b => b.id === id)
  const toggleBriefFavorite = (brief) => {
    if (isBriefFavorited(brief.id)) {
      favoritedBriefs.value = favoritedBriefs.value.filter(b => b.id !== brief.id)
    } else {
      favoritedBriefs.value.push(brief)
    }
  }

  // ── Likes ──────────────────────────────────────────────────────────────────
  const likedIds = ref([])
  const isLiked = (id) => likedIds.value.includes(id)
  const toggleLike = (mission) => {
    if (isLiked(mission.id)) {
      likedIds.value = likedIds.value.filter(id => id !== mission.id)
      mission.likes = Math.max(0, (mission.likes || 1) - 1)
    } else {
      likedIds.value.push(mission.id)
      mission.likes = (mission.likes || 0) + 1
    }
  }

  // ── Notifications ──────────────────────────────────────────────────────────
  const notifications = ref([])
  const unreadCount = computed(() => notifications.value.filter(n => !n.read).length)
  const markAllRead = () => { notifications.value.forEach(n => { n.read = true }) }
  const markRead = (id) => { const n = notifications.value.find(n => n.id === id); if (n) n.read = true }

  const fetchNotifications = async () => {
    if (!authStore.user) return
    try {
      const res = await api.get('/freelancer/notifications')
      notifications.value = res.data
    } catch {
      if (notifications.value.length === 0) {
        notifications.value = [
          { id: 1, type: 'message', title: 'Nouveau message', message: 'Mehdi El Fassi : "Êtes-vous disponible ?"', read: false, created_at: new Date().toISOString() },
          { id: 2, type: 'like', title: '❤️ Like sur votre brief', message: 'Expert Zellige — 5 nouveaux likes', read: false, created_at: new Date(Date.now() - 300000).toISOString() },
          { id: 3, type: 'comment', title: '💬 Commentaire sur votre brief', message: '"Superbe travail, très intéressé..."', read: false, created_at: new Date(Date.now() - 3600000).toISOString() },
        ]
      }
    }
  }

  const fetchFavorites = async () => {
    if (!authStore.user) return
    try {
      const res_m = await api.get('/freelancer/missions/favorites')
      favoritedMissions.value = res_m.data
      const res_b = await api.get('/freelancer/favorites')
      favoritedBriefs.value = res_b.data
    } catch (e) { console.error("Error fetching favorites", e) }
  }

  let _pollingTimer = null
  const startPolling = () => {
    if (_pollingTimer) return
    fetchNotifications()
    _pollingTimer = setInterval(fetchNotifications, 30000)
  }

  // ── Search ─────────────────────────────────────────────────────────────────
  const searchQuery = ref('')
  const searchCategory = ref('')

  return {
    user, userName, userInitials,
    favoritedMissions, favoritedIds, isFavorited, toggleFavorite,
    likedIds, isLiked, toggleLike,
    notifications, unreadCount, markAllRead, markRead, fetchNotifications, startPolling,
    searchQuery, searchCategory,
    favoritedBriefs, isBriefFavorited, toggleBriefFavorite, fetchFavorites
  }
})
