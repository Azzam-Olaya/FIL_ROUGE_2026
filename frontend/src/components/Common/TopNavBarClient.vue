<template>
  <header class="bg-surface-container border-b border-primary/10 flex items-center w-full px-4 md:px-6 py-3 fixed top-0 left-0 right-0 z-50 h-16 gap-2 md:gap-4">

    <!-- Left: Hamburger + Logo -->
    <div class="flex items-center gap-2 md:gap-4 flex-shrink-0">
      <!-- Hamburger Menu for Mobile -->
      <button @click="$emit('toggle-sidebar')" class="p-2 -ml-2 text-on-surface-variant lg:hidden hover:bg-primary/10 rounded-full transition-colors">
        <span class="material-symbols-outlined">menu</span>
      </button>

      <router-link to="/" class="flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-2xl" style="font-variation-settings: 'FILL' 1">auto_awesome</span>
        <div class="flex flex-col">
          <span class="font-headline font-black italic text-lg md:text-xl text-primary tracking-tight leading-none">MorLancer</span>
          <span class="hidden sm:block text-[8px] md:text-[10px] text-on-surface-variant font-bold uppercase tracking-[0.15em] leading-none mt-0.5">Talents digitaux</span>
        </div>
      </router-link>
    </div>

    <!-- Search + Filter center (Hidden on mobile, search icon used instead) -->
    <div class="hidden md:flex flex-1 items-center gap-3 max-w-2xl mx-auto">
      <div class="relative flex-1">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
        <input v-model="searchQuery" @input="onSearch" @keyup.enter="onSearch"
          placeholder="Rechercher des briefs..."
          class="w-full bg-surface border border-primary/10 rounded-full py-2 pl-10 pr-4 text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 font-medium placeholder:text-on-surface-variant/50" />
        <button v-if="searchQuery" @click="clearSearch" class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-on-surface">
          <span class="material-symbols-outlined text-sm">close</span>
        </button>
      </div>
      <select v-model="categoryFilter" @change="onSearch"
        class="bg-surface border border-primary/10 text-on-surface rounded-full py-2 px-3 text-xs focus:outline-none font-medium min-w-[140px]">
        <option value="">Toutes catégories</option>
        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
      </select>
    </div>

    <!-- Right icons -->
    <div class="flex items-center gap-0 md:gap-1 flex-shrink-0" ref="iconsRef">
      
      <!-- Mobile Search Icon -->
      <button class="md:hidden p-2 rounded-full hover:bg-primary/10 transition-colors text-on-surface-variant">
        <span class="material-symbols-outlined">search</span>
      </button>

      <!-- Wallet Balance -->
      <div @click="showDeposit = true" class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-primary/5 rounded-full border border-primary/10 mr-2 cursor-pointer hover:bg-primary/10 transition-colors group">
        <span class="material-symbols-outlined text-primary text-sm group-hover:scale-110 transition-transform">account_balance_wallet</span>
        <span class="text-xs font-black text-primary">{{ Number(store.balance).toLocaleString() }} DH</span>
        <span class="material-symbols-outlined text-xs text-primary/50">add_circle</span>
      </div>

      <DepositModal :is-open="showDeposit" @close="showDeposit = false" @success="store.fetchBalance()" />

      <!-- Notifications -->
      <div class="relative">
        <button @click="toggleNotif" class="p-2 rounded-full hover:bg-primary/10 transition-colors relative" title="Notifications">
          <span class="material-symbols-outlined text-on-surface-variant">notifications</span>
          <span v-if="store.unreadCount > 0"
            class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] bg-secondary text-white text-[10px] font-black rounded-full flex items-center justify-center px-1 animate-pulse">
            {{ store.unreadCount > 9 ? '9+' : store.unreadCount }}
          </span>
        </button>
        <Transition name="dd">
          <div v-if="notifOpen" class="absolute top-12 right-0 w-80 bg-white rounded-2xl shadow-2xl border border-primary/5 z-50 overflow-hidden">
            <div class="flex justify-between items-center px-4 py-3 border-b border-primary/5">
              <p class="font-bold text-sm text-on-surface">Notifications</p>
              <button v-if="store.unreadCount > 0" @click.stop="store.markAllRead()"
                class="text-[10px] font-bold text-primary hover:underline uppercase tracking-widest">Tout lu</button>
            </div>
            <div class="max-h-72 overflow-y-auto divide-y divide-primary/5">
              <div v-if="loadingNotif" class="text-center py-6">
                <span class="material-symbols-outlined text-2xl text-primary/30 animate-spin block">progress_activity</span>
              </div>
              <template v-else-if="store.notifications.length">
                <div v-for="n in store.notifications" :key="n.id" @click="handleClick(n)"
                  :class="!n.read ? 'bg-primary/5 border-l-4 border-primary' : 'bg-white'"
                  class="flex gap-3 px-4 py-3 hover:bg-primary/10 cursor-pointer transition-colors">
                  <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0" :class="iconBg(n.type)">
                    <span class="material-symbols-outlined text-sm text-white" style="font-variation-settings:'FILL' 1">{{ iconName(n.type) }}</span>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="font-bold text-xs text-on-surface truncate">{{ n.title }}</p>
                    <p class="text-[11px] text-on-surface-variant mt-0.5 line-clamp-2">{{ n.message }}</p>
                    <p class="text-[10px] text-on-surface-variant/50 mt-1">{{ timeAgo(n.created_at) }}</p>
                  </div>
                  <div v-if="!n.read" class="w-2 h-2 bg-secondary rounded-full flex-shrink-0 mt-1.5"></div>
                </div>
              </template>
              <div v-else class="text-center py-8">
                <span class="material-symbols-outlined text-3xl text-on-surface-variant/30 block mb-2">notifications_off</span>
                <p class="text-xs text-on-surface-variant">Aucune notification</p>
              </div>
            </div>
          </div>
        </Transition>
      </div>

      <!-- Favorites -->
      <div class="relative">
        <button @click="favOpen = !favOpen; notifOpen = false" class="p-2 rounded-full hover:bg-primary/10 transition-colors relative" title="Favoris">
          <span class="material-symbols-outlined transition-colors"
            :class="store.favoritedBriefs.length ? 'text-primary' : 'text-on-surface-variant'"
            :style="store.favoritedBriefs.length ? `font-variation-settings:'FILL' 1` : ''">favorite</span>
          <span v-if="store.favoritedBriefs.length"
            class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] bg-secondary text-white text-[10px] font-black rounded-full flex items-center justify-center px-1">
            {{ store.favoritedBriefs.length > 9 ? '9+' : store.favoritedBriefs.length }}
          </span>
        </button>
        <Transition name="dd">
          <div v-if="favOpen" class="absolute top-12 right-0 w-80 bg-white rounded-2xl shadow-2xl border border-primary/5 z-50 overflow-hidden">
            <div class="px-4 py-3 border-b border-primary/5">
              <p class="font-bold text-sm text-on-surface">Briefs favoris <span class="text-on-surface-variant font-normal">({{ store.favoritedBriefs.length }})</span></p>
            </div>
            <div class="max-h-80 overflow-y-auto divide-y divide-primary/5">
              <div v-for="b in store.favoritedBriefs" :key="b.id" @click="goToBrief(b)" class="flex gap-3 px-4 py-3 hover:bg-primary/5 group/item cursor-pointer">
                <div class="w-9 h-9 rounded-full bg-primary flex items-center justify-center text-white font-bold text-[10px] flex-shrink-0">
                  {{ initials(b.freelancer?.name) }}
                </div>
                <div class="flex-1 min-w-0">
                  <p class="font-semibold text-xs text-on-surface truncate">{{ b.title }}</p>
                  <p class="text-[11px] text-on-surface-variant mt-0.5">{{ b.freelancer?.name || '—' }}</p>
                </div>
                <button @click.stop="store.toggleFavorite(b)" class="opacity-0 group-hover/item:opacity-100 p-1.5 rounded-full hover:bg-secondary/10 transition-all">
                  <span class="material-symbols-outlined text-secondary text-sm" style="font-variation-settings:'FILL' 1">favorite</span>
                </button>
              </div>
              <div v-if="!store.favoritedBriefs.length" class="text-center py-8">
                <span class="material-symbols-outlined text-3xl text-on-surface-variant/30 block mb-2">favorite_border</span>
                <p class="text-xs text-on-surface-variant">Aucun brief en favori</p>
              </div>
            </div>
          </div>
        </Transition>
      </div>

      <!-- Profile -->
      <div class="relative">
        <button @click="profileOpen = !profileOpen; notifOpen = false; favOpen = false"
          class="w-9 h-9 rounded-full bg-primary flex items-center justify-center text-white font-bold text-xs shadow-lg hover:scale-110 transition-transform ml-1"
          :title="store.userName">
          {{ store.userInitials }}
        </button>
        <Transition name="dd">
          <div v-if="profileOpen" class="absolute top-12 right-0 w-52 bg-white rounded-2xl shadow-2xl border border-primary/5 z-50 overflow-hidden">
            <div class="px-4 py-4 border-b border-primary/5 bg-primary/5 flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-bold text-xs flex-shrink-0">{{ store.userInitials }}</div>
              <div class="min-w-0">
                <p class="font-bold text-sm text-on-surface truncate">{{ store.userName }}</p>
                <p class="text-[10px] text-on-surface-variant">Client</p>
              </div>
            </div>
            <div class="py-2">
              <button @click="go('profile')" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-on-surface hover:bg-primary/10 transition-colors text-left">
                <span class="material-symbols-outlined text-base text-on-surface-variant">person</span>Mon Profil
              </button>
              <button @click="go('messages')" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-on-surface hover:bg-primary/10 transition-colors text-left">
                <span class="material-symbols-outlined text-base text-on-surface-variant">chat_bubble</span>Messages
              </button>
              <div class="border-t border-primary/5 my-1"></div>
              <button @click="logout" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-secondary hover:bg-secondary/10 transition-colors text-left">
                <span class="material-symbols-outlined text-base">logout</span>Déconnexion
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useClientStore } from '@/stores/client'
import { useAuthStore }   from '@/stores/auth'
import api from '@/api/axios'
import DepositModal from '@/components/Client/DepositModal.vue'

const emit = defineEmits(['search', 'toggle-sidebar'])
const store     = useClientStore()
const authStore = useAuthStore()
const router    = useRouter()

const notifOpen    = ref(false)
const favOpen      = ref(false)
const profileOpen  = ref(false)
const loadingNotif = ref(false)
const searchQuery   = ref('')
const categoryFilter = ref('')
const categories    = ref([])
const iconsRef      = ref(null)
const showDeposit   = ref(false)

const toggleNotif = async () => {
  notifOpen.value   = !notifOpen.value
  favOpen.value     = false
  profileOpen.value = false
  if (notifOpen.value) {
    loadingNotif.value = true
    await store.fetchNotifications()
    loadingNotif.value = false
  }
}

const handleClick = (n) => {
  store.markRead(n.id)
  notifOpen.value = false
  if (n.type === 'message') go('messages')
  else if (n.type === 'like' || n.type === 'comment') go('briefs')
  else if (n.type === 'contract') go('contracts')
  else go('dashboard')
}

const onSearch = () => emit('search', { query: searchQuery.value, category: categoryFilter.value })
const clearSearch = () => { searchQuery.value = ''; emit('search', { query: '', category: categoryFilter.value }) }

const go = (tab) => {
  profileOpen.value = false
  window.dispatchEvent(new CustomEvent('client-tab', { detail: tab }))
}

const logout = () => { 
  authStore.clearAuth()
  router.push('/login')
}

const timeAgo = (d) => {
  if (!d) return ''
  const diff = Date.now() - new Date(d)
  if (diff < 60000)    return 'À l\'instant'
  if (diff < 3600000)  return Math.floor(diff / 60000) + ' min'
  if (diff < 86400000) return Math.floor(diff / 3600000) + 'h'
  return Math.floor(diff / 86400000) + 'j'
}

const iconBg   = (t) => ({ like: 'bg-secondary', comment: 'bg-primary', message: 'bg-tertiary' }[t] || 'bg-primary')
const iconName = (t) => ({ like: 'favorite', comment: 'comment', message: 'chat_bubble' }[t] || 'notifications')
const initials = (n) => n?.split(' ').map(x => x[0]).join('').toUpperCase().slice(0, 2) || 'U'

const goToBrief = (brief) => {
  showFavorites.value = false
  router.push({ path: '/client/dashboard', query: { tab: 'dashboard', briefId: brief.id } })
}

const handleOutside = (e) => {
  if (iconsRef.value && !iconsRef.value.contains(e.target)) {
    notifOpen.value = false; favOpen.value = false; profileOpen.value = false
  }
}

const loadCategories = async () => {
  try {
    const res = await api.get('/freelancer/categories')
    categories.value = res.data.filter(c => !c.parent_id)
  } catch { categories.value = [] }
}

onMounted(() => {
  store.startPolling()
  store.fetchBalance()
  loadCategories()
  document.addEventListener('click', handleOutside)
})
onUnmounted(() => document.removeEventListener('click', handleOutside))
</script>

<style scoped>
.dd-enter-active, .dd-leave-active { transition: opacity 0.15s ease, transform 0.15s ease; }
.dd-enter-from, .dd-leave-to { opacity: 0; transform: translateY(-6px); }
</style>
