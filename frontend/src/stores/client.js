import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../api/axios'
import { useAuthStore } from './auth'

export const useClientStore = defineStore('client', () => {
  const authStore = useAuthStore()

  const user = computed(() => authStore.user)
  const userName = computed(() => user.value?.name || 'Client')
  const userInitials = computed(() =>
    user.value?.name?.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) || 'U'
  )

  // ── Likes ──────────────────────────────────────────────────────────────────
  const likedIds = ref([])
  const isLiked = (id) => likedIds.value.includes(id)
  const toggleLike = (brief) => {
    if (isLiked(brief.id)) {
      likedIds.value = likedIds.value.filter(i => i !== brief.id)
      brief.likes_count = Math.max(0, (brief.likes_count || 1) - 1)
    } else {
      likedIds.value.push(brief.id)
      brief.likes_count = (brief.likes_count || 0) + 1
    }
  }

  // ── Favorites ──────────────────────────────────────────────────────────────
  const favoritedBriefs = ref([])
  const favoritedIds = computed(() => favoritedBriefs.value.map(b => b.id))
  const isFavorited = (id) => favoritedIds.value.includes(id)
  const toggleFavorite = (brief) => {
    if (isFavorited(brief.id)) {
      favoritedBriefs.value = favoritedBriefs.value.filter(b => b.id !== brief.id)
    } else {
      favoritedBriefs.value.push(brief)
    }
  }

  const fetchFavorites = async () => {
    if (!authStore.user) return
    try {
      const res = await api.get('/client/favorites')
      favoritedBriefs.value = res.data
    } catch { }
  }

  // ── Notifications ──────────────────────────────────────────────────────────
  const notifications = ref([])
  const unreadCount = computed(() => notifications.value.filter(n => !n.read).length)
  const markAllRead = async () => {
    notifications.value.forEach(n => { n.read = true })
    try { await api.post('/client/notifications/read', {}) } catch { }
  }
  const markRead = (id) => { const n = notifications.value.find(n => n.id === id); if (n) n.read = true }

  const fetchNotifications = async () => {
    if (!authStore.user) return
    try {
      const res = await api.get('/client/notifications')
      notifications.value = res.data
    } catch {
      if (notifications.value.length === 0) {
        notifications.value = [
          { id: 1, type: 'like', title: '❤️ Nouveau like', message: 'Yasmine B. a aimé votre mission "Rénovation Salon"', read: false, created_at: new Date().toISOString() },
          { id: 2, type: 'comment', title: '💬 Nouveau commentaire', message: 'Omar K. : "Je suis disponible pour ce projet !"', read: false, created_at: new Date(Date.now() - 300000).toISOString() },
          { id: 3, type: 'like', title: '❤️ Nouveau like', message: 'Sara M. a aimé votre mission "Site E-Commerce"', read: true, created_at: new Date(Date.now() - 3600000).toISOString() },
        ]
      }
    }
  }

  let _pollingTimer = null
  const startPolling = () => {
    if (_pollingTimer) return
    fetchNotifications()
    _pollingTimer = setInterval(fetchNotifications, 30000)
  }

  return {
    user, userName, userInitials,
    likedIds, isLiked, toggleLike,
    favoritedBriefs, favoritedIds, isFavorited, toggleFavorite, fetchFavorites,
    notifications, unreadCount, markAllRead, markRead, fetchNotifications, startPolling,
  }
})
