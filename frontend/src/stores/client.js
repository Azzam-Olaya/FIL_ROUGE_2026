import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useClientStore = defineStore('client', () => {

  const user         = ref(JSON.parse(localStorage.getItem('user') || '{}'))
  const userName     = computed(() => user.value?.name || 'Client')
  const userInitials = computed(() =>
    user.value?.name?.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) || 'U'
  )
  const authHeaders = computed(() => ({
    Authorization: `Bearer ${localStorage.getItem('token')}`,
  }))

  // ─── Likes ────────────────────────────────────────────────────────────────
  const likedIds = ref([])
  const isLiked  = (id) => likedIds.value.includes(id)

  const toggleLike = (brief) => {
    if (isLiked(brief.id)) {
      likedIds.value = likedIds.value.filter(i => i !== brief.id)
      brief.likes_count = Math.max(0, (brief.likes_count || 1) - 1)
    } else {
      likedIds.value.push(brief.id)
      brief.likes_count = (brief.likes_count || 0) + 1
    }
  }

  // ─── Favorites ────────────────────────────────────────────────────────────
  const favoritedBriefs = ref([])
  const favoritedIds    = computed(() => favoritedBriefs.value.map(b => b.id))
  const isFavorited     = (id) => favoritedIds.value.includes(id)

  const toggleFavorite = (brief) => {
    if (isFavorited(brief.id)) {
      favoritedBriefs.value = favoritedBriefs.value.filter(b => b.id !== brief.id)
    } else {
      favoritedBriefs.value.push(brief)
    }
  }

  // ─── Notifications ────────────────────────────────────────────────────────
  const notifications = ref([])
  const unreadCount   = computed(() => notifications.value.filter(n => !n.read).length)

  const markAllRead = async () => {
    notifications.value.forEach(n => { n.read = true })
    try {
      await axios.post('http://localhost:8000/api/client/notifications/read', {}, { headers: authHeaders.value })
    } catch {}
  }

  const markRead = async (id) => {
    const n = notifications.value.find(n => n.id === id)
    if (n) n.read = true
  }

  const fetchNotifications = async () => {
    try {
      const res = await axios.get('http://localhost:8000/api/client/notifications', { headers: authHeaders.value })
      notifications.value = res.data
    } catch {
      if (notifications.value.length === 0) {
        notifications.value = [
          { id: 1, type: 'like',    title: '❤️ Nouveau like',        message: 'Yasmine B. a aimé votre mission "Rénovation Salon"', read: false, created_at: new Date().toISOString() },
          { id: 2, type: 'comment', title: '💬 Nouveau commentaire', message: 'Omar K. : "Je suis disponible pour ce projet !"',     read: false, created_at: new Date(Date.now() - 300000).toISOString() },
          { id: 3, type: 'like',    title: '❤️ Nouveau like',        message: 'Sara M. a aimé votre mission "Site E-Commerce"',     read: true,  created_at: new Date(Date.now() - 3600000).toISOString() },
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
    user, userName, userInitials, authHeaders,
    likedIds, isLiked, toggleLike,
    favoritedBriefs, favoritedIds, isFavorited, toggleFavorite,
    notifications, unreadCount, markAllRead, markRead, fetchNotifications, startPolling,
  }
})
