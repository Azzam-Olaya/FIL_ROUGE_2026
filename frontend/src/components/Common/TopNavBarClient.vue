<template>
  <header class="bg-surface-container border-b border-primary/10 flex items-center w-full px-6 py-3 fixed top-0 left-0 right-0 z-50 h-16 gap-4">

    <!-- Logo -->
    <router-link to="/" class="flex items-center gap-2 flex-shrink-0 mr-4">
      <span class="material-symbols-outlined text-primary text-2xl" style="font-variation-settings: 'FILL' 1">auto_awesome</span>
      <div class="flex flex-col">
        <span class="font-headline font-black italic text-xl text-primary tracking-tight leading-none">MorLancer</span>
        <span class="text-[10px] text-on-surface-variant font-bold uppercase tracking-[0.15em] leading-none mt-0.5">Talents digitaux</span>
      </div>
    </router-link>

    <!-- Search + Filter (center) -->
    <div class="flex-1 flex items-center gap-3 max-w-2xl mx-auto">
      <div class="relative flex-1">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
        <input
          v-model="searchQuery"
          @input="onSearch"
          @keyup.enter="submitSearch"
          placeholder="Rechercher des briefs..."
          class="w-full bg-surface border border-primary/10 rounded-full py-2 pl-10 pr-4 text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 font-medium placeholder:text-on-surface-variant/50"
        />
        <button v-if="searchQuery" @click="clearSearch" class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-on-surface">
          <span class="material-symbols-outlined text-sm">close</span>
        </button>
      </div>

      <select
        v-model="categoryFilter"
        @change="submitSearch"
        class="bg-surface border border-primary/10 text-on-surface rounded-full py-2 px-3 text-xs focus:outline-none font-medium min-w-[140px]"
      >
        <option value="" class="text-on-surface">Toutes catégories</option>
        <option v-for="cat in categories" :key="cat.id" :value="cat.id" class="text-on-surface">{{ cat.name }}</option>
      </select>
    </div>

    <!-- Right icons -->
    <div class="flex items-center gap-1 flex-shrink-0 ml-4">

      <!-- Notifications -->
      <div class="relative" ref="notifRef">
        <button @click="toggleNotif" class="p-2 rounded-full hover:bg-primary/10 transition-colors relative" title="Notifications">
          <span class="material-symbols-outlined text-on-surface-variant">notifications</span>
          <span v-if="store.unreadCount > 0"
            class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] bg-secondary text-white text-[10px] font-black rounded-full flex items-center justify-center px-1 animate-pulse">
            {{ store.unreadCount > 9 ? '9+' : store.unreadCount }}
          </span>
        </button>

        <Transition name="dropdown">
          <div v-if="notifOpen" class="absolute top-12 right-0 w-80 bg-white rounded-2xl shadow-2xl border border-primary/5 z-50 overflow-hidden">
            <div class="flex justify-between items-center px-4 py-3 border-b border-primary/5">
              <p class="font-bold text-sm text-on-surface">Notifications</p>
              <button v-if="store.unreadCount > 0" @click.stop="store.markAllRead()"
                class="text-[10px] font-bold text-primary hover:underline uppercase tracking-widest">Tout lu</button>
            </div>
            <div class="max-h-80 overflow-y-auto divide-y divide-primary/5">
              <div v-if="loadingNotif" class="text-center py-6">
                <span class="material-symbols-outlined text-2xl text-primary/30 animate-spin block">progress_activity</span>
              </div>
              <template v-else-if="store.notifications.length > 0">
                <div v-for="notif in store.notifications" :key="notif.id"
                  @click="handleNotifClick(notif)"
                  :class="!notif.read ? 'bg-primary/5 border-l-4 border-primary' : 'bg-white'"
                  class="flex gap-3 px-4 py-3 hover:bg-primary/10 transition-colors cursor-pointer">
                  <div class="flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center shadow-sm" :class="iconBg(notif.type)">
                    <span class="material-symbols-outlined text-sm text-white" style="font-variation-settings: 'FILL' 1">{{ iconName(notif.type) }}</span>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="font-bold text-xs text-on-surface leading-tight">{{ notif.title }}</p>
                    <p class="text-[11px] text-on-surface-variant mt-0.5">{{ notif.message }}</p>
                    <p class="text-[10px] text-on-surface-variant/50 mt-1">{{ timeAgo(notif.created_at) }}</p>
                  </div>
                  <div v-if="!notif.read" class="w-2.5 h-2.5 bg-secondary rounded-full flex-shrink-0 mt-1"></div>
                </div>
              </template>
              <div v-else class="text-center py-10">
                <span class="material-symbols-outlined text-4xl text-on-surface-variant/20 block mb-2">notifications_off</span>
                <p class="text-xs text-on-surface-variant">Aucune notification</p>
              </div>
            </div>
          </div>
        </Transition>
      </div>

      <!-- Favorites -->
      <div class="relative" ref="favRef">
        <button @click="toggleFav" class="p-2 rounded-full hover:bg-primary/10 transition-colors relative" title="Favoris">
          <span class="material-symbols-outlined text-on-surface-variant"
            :style="store.favoritedBriefs.length ? `font-variation-settings: 'FILL' 1` : ''"
            :class="store.favoritedBriefs.length ? 'text-primary' : ''">favorite</span>
          <span v-if="store.favoritedBriefs.length > 0"
            class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] bg-secondary text-white text-[10px] font-black rounded-full flex items-center justify-center px-1">
            {{ store.favoritedBriefs.length > 9 ? '9+' : store.favoritedBriefs.length }}
          </span>
        </button>

        <Transition name="dropdown">
          <div v-if="favOpen" class="absolute top-12 right-0 w-80 bg-white rounded-2xl shadow-2xl border border-primary/5 z-50 overflow-hidden">
            <div class="px-4 py-3 border-b border-primary/5">
              <p class="font-bold text-sm text-on-surface">Briefs favoris <span class="text-on-surface-variant font-normal">({{ store.favoritedBriefs.length }})</span></p>
            </div>
            <div class="max-h-80 overflow-y-auto divide-y divide-primary/5">
              <div v-for="brief in store.favoritedBriefs" :key="brief.id"
                class="flex gap-3 px-4 py-3 hover:bg-primary/5 transition-colors group/item">
                <div class="w-9 h-9 rounded-full bg-primary flex items-center justify-center text-white font-bold text-[10px] flex-shrink-0">
                  {{ initials(brief.freelancer?.name) }}
                </div>
                <div class="flex-1 min-w-0">
                  <p class="font-semibold text-xs text-on-surface truncate">{{ brief.title }}</p>
                  <p class="text-[11px] text-on-surface-variant mt-0.5">{{ brief.freelancer?.name || '—' }}</p>
                </div>
                <button @click.stop="store.toggleFavorite(brief)"
                  class="flex-shrink-0 p-1.5 rounded-full hover:bg-secondary/10 opacity-0 group-hover/item:opacity-100 transition-all">
                  <span class="material-symbols-outlined text-secondary text-sm" style="font-variation-settings: 'FILL' 1">favorite</span>
                </button>
              </div>
              <div v-if="store.favoritedBriefs.length === 0" class="text-center py-8">
                <span class="material-symbols-outlined text-3xl text-on-surface-variant/30 block mb-2">favorite_border</span>
                <p class="text-xs text-on-surface-variant">Aucun brief en favori</p>
              </div>
            </div>
          </div>
        </Transition>
      </div>

      <!-- Profile avatar -->
      <button @click="goToProfile"
        class="w-9 h-9 rounded-full bg-primary flex items-center justify-center text-white font-bold text-xs shadow-lg hover:scale-110 transition-transform ml-1"
        :title="store.userName">
        {{ store.userInitials }}
      </button>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useClientStore } from '@/stores/client'
import axios from 'axios'

const emit = defineEmits(['search'])

const store  = useClientStore()
const router = useRouter()

const notifOpen   = ref(false)
const favOpen     = ref(false)
const loadingNotif = ref(false)
const notifRef    = ref(null)
const favRef      = ref(null)
const searchQuery = ref('')
const categoryFilter = ref('')
const categories  = ref([])

const toggleNotif = async () => {
  notifOpen.value = !notifOpen.value
  favOpen.value   = false
  if (notifOpen.value) {
    loadingNotif.value = true
    await store.fetchNotifications()
    loadingNotif.value = false
  }
}

const toggleFav = () => {
  favOpen.value   = !favOpen.value
  notifOpen.value = false
}

const handleOutsideClick = (e) => {
  if (notifRef.value && !notifRef.value.contains(e.target)) notifOpen.value = false
  if (favRef.value   && !favRef.value.contains(e.target))   favOpen.value   = false
}

const handleNotifClick = async (notif) => {
  store.markRead(notif.id)
  try { await axios.patch(`http://localhost:8000/api/client/notifications/${notif.id}/read`, {}, { headers: store.authHeaders }) } catch {}
  notifOpen.value = false
}

const onSearch = () => {
  emit('search', { query: searchQuery.value, category: categoryFilter.value })
}

const submitSearch = () => {
  emit('search', { query: searchQuery.value, category: categoryFilter.value })
}

const clearSearch = () => {
  searchQuery.value = ''
  emit('search', { query: '', category: categoryFilter.value })
}

const goToProfile = () => router.push({ path: '/client/dashboard', query: { tab: 'profile' } })

const timeAgo = (date) => {
  if (!date) return ''
  const diff = Date.now() - new Date(date)
  if (diff < 60000)    return 'À l\'instant'
  if (diff < 3600000)  return Math.floor(diff / 60000) + ' min'
  if (diff < 86400000) return Math.floor(diff / 3600000) + 'h'
  return Math.floor(diff / 86400000) + 'j'
}

const iconBg   = (t) => ({ like: 'bg-secondary', comment: 'bg-primary', message: 'bg-tertiary' }[t] || 'bg-primary')
const iconName = (t) => ({ like: 'favorite', comment: 'comment', message: 'chat_bubble' }[t] || 'notifications')
const initials = (name) => name?.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) || 'U'

const loadCategories = async () => {
  try {
    const res = await axios.get('http://localhost:8000/api/freelancer/categories', { headers: store.authHeaders })
    categories.value = res.data.filter(c => !c.parent_id)
  } catch { categories.value = [] }
}

onMounted(() => {
  store.startPolling()
  loadCategories()
  document.addEventListener('click', handleOutsideClick)
})

onUnmounted(() => document.removeEventListener('click', handleOutsideClick))
</script>

<style scoped>
.dropdown-enter-active, .dropdown-leave-active { transition: opacity 0.15s ease, transform 0.15s ease; }
.dropdown-enter-from, .dropdown-leave-to { opacity: 0; transform: translateY(-6px); }
</style>
