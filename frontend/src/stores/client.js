import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useClientStore = defineStore('client', () => {
  const user         = ref(JSON.parse(localStorage.getItem('user') || '{}'))
  const userName     = computed(() => user.value?.name || 'Client')
  const userInitials = computed(() => user.value?.name?.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) || 'U')
  const authHeaders  = computed(() => ({ Authorization: `Bearer ${localStorage.getItem('token')}` }))

  const likedIds   = ref([])
  const isLiked    = (id) => likedIds.value.includes(id)
  const toggleLike = (b) => {
    if (isLiked(b.id)) { likedIds.value = likedIds.value.filter(i => i !== b.id); b.likes_count = Math.max(0, (b.likes_count || 1) - 1) }
    else { likedIds.value.push(b.id); b.likes_count = (b.likes_count || 0) + 1 }
  }

  const favoritedBriefs = ref([])
  const favoritedIds    = computed(() => favoritedBriefs.value.map(b => b.id))
  const isFavorited     = (id) => favoritedIds.value.includes(id)
  const toggleFavorite  = (b) => {
    if (isFavorited(b.id)) favoritedBriefs.value = favoritedBriefs.value.filter(x => x.id !== b.id)
    else favoritedBriefs.value.push(b)
  }

  const notifications = ref([])
  const unreadCount   = computed(() => notifications.value.filter(n => !n.read).length)
  const markAllRead   = async () => {
    notifications.value.forEach(n => { n.read = true })
    try { await axios.post('http://localhost:8000/api/client/notifications/read', {}, { headers: authHeaders.value }) } catch {}
  }
  const markRead = (id) => { const n = notifications.value.find(n => n.id === id); if (n) n.read = true }

  const fetchNotifications = async () => {
    try {
      const res = await axios.get('http://localhost:8000/api/client/notifications', { headers: authHeaders.value })
      notifications.value = res.data
    } catch {
      if (!notifications.value.length) notifications.value = [
        { id: 1, type: 'like',    title: '❤️ Nouveau like',        message: 'Yasmine B. a aimé votre mission', read: false, created_at: new Date().toISOString() },
        { id: 2, type: 'comment', title: '💬 Nouveau commentaire', message: 'Omar K. : "Je suis disponible !"', read: false, created_at: new Date(Date.now()-300000).toISOString() },
      ]
    }
  }

  let _t = null
  const startPolling = () => {
    if (_t) return
    fetchNotifications()
    _t = setInterval(fetchNotifications, 30000)
  }

  return { user, userName, userInitials, authHeaders, likedIds, isLiked, toggleLike, favoritedBriefs, favoritedIds, isFavorited, toggleFavorite, notifications, unreadCount, markAllRead, markRead, fetchNotifications, startPolling }
})
