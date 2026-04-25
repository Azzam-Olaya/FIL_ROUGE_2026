<template>
  <div class="relative" ref="containerRef">
    <button @click="toggle" class="p-2 rounded-full hover:bg-primary/10 transition-colors relative group" title="Notifications">
      <span class="material-symbols-outlined text-on-surface-variant group-hover:text-primary transition-colors">notifications</span>
      <span v-if="store.unreadCount > 0"
        class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] bg-secondary text-white text-[10px] font-black rounded-full flex items-center justify-center px-1 animate-pulse">
        {{ store.unreadCount > 9 ? '9+' : store.unreadCount }}
      </span>
    </button>

    <Transition name="dropdown">
      <div v-if="open" class="absolute top-12 right-0 w-80 bg-white rounded-2xl shadow-2xl border border-primary/5 z-50 overflow-hidden">
        <div class="flex justify-between items-center px-4 py-3 border-b border-primary/5">
          <p class="font-bold text-sm text-on-surface">
            Notifications
            <span v-if="store.unreadCount > 0" class="ml-1 text-[10px] bg-secondary text-white px-2 py-0.5 rounded-full font-bold">{{ store.unreadCount }}</span>
          </p>
          <button v-if="store.unreadCount > 0" @click.stop="store.markAllRead()"
            class="text-[10px] font-bold text-primary hover:underline uppercase tracking-widest">Tout lu</button>
        </div>

        <div class="max-h-72 overflow-y-auto divide-y divide-primary/5">
          <div v-if="loading" class="text-center py-6">
            <span class="material-symbols-outlined text-2xl text-primary/30 animate-spin block">progress_activity</span>
          </div>
          <template v-else-if="store.notifications.length > 0">
            <button v-for="notif in store.notifications" :key="notif.id"
              @click="handleClick(notif)"
              :class="!notif.read ? 'bg-primary/5 border-l-4 border-primary' : 'bg-white'"
              class="w-full text-left flex gap-3 px-4 py-3 hover:bg-primary/10 transition-colors cursor-pointer">
              <div class="flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center" :class="iconBg(notif.type)">
                <span class="material-symbols-outlined text-sm text-white" style="font-variation-settings: 'FILL' 1">{{ iconName(notif.type) }}</span>
              </div>
              <div class="flex-1 min-w-0">
                <p class="font-semibold text-xs text-on-surface truncate">{{ notif.title }}</p>
                <p class="text-[11px] text-on-surface-variant mt-0.5 line-clamp-2">{{ notif.message }}</p>
                <p class="text-[10px] text-on-surface-variant/50 mt-1">{{ timeAgo(notif.created_at) }}</p>
              </div>
              <div v-if="!notif.read" class="w-2 h-2 bg-secondary rounded-full flex-shrink-0 mt-1.5"></div>
            </button>
          </template>
          <div v-else class="text-center py-8">
            <span class="material-symbols-outlined text-3xl text-on-surface-variant/30 block mb-2">notifications_off</span>
            <p class="text-xs text-on-surface-variant">Aucune notification</p>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useFreelancerStore } from '@/stores/freelancer'

const store        = useFreelancerStore()
const router       = useRouter()
const open         = ref(false)
const loading      = ref(false)
const containerRef = ref(null)

const toggle = async () => {
  open.value = !open.value
  if (open.value) { loading.value = true; await store.fetchNotifications(); loading.value = false }
}

const handleClick = (notif) => {
  store.markRead(notif.id)
  open.value = false
  if (notif.type === 'message') {
    router.push({ path: '/freelancer/dashboard', query: { tab: 'messages' } })
  } else if (notif.type === 'like' || notif.type === 'comment') {
    router.push({ path: '/freelancer/dashboard', query: { tab: 'briefs', briefId: notif.portfolio_id } })
  }
}

const timeAgo = (date) => {
  if (!date) return ''
  const d = Date.now() - new Date(date)
  if (d < 60000)    return 'À l\'instant'
  if (d < 3600000)  return Math.floor(d / 60000) + ' min'
  if (d < 86400000) return Math.floor(d / 3600000) + 'h'
  return Math.floor(d / 86400000) + 'j'
}

const iconBg   = (t) => ({ message: 'bg-primary', like: 'bg-secondary', comment: 'bg-tertiary' }[t] || 'bg-primary')
const iconName = (t) => ({ message: 'chat_bubble', like: 'favorite', comment: 'comment' }[t] || 'notifications')

const handleOutside = (e) => { if (containerRef.value && !containerRef.value.contains(e.target)) open.value = false }
onMounted(() => document.addEventListener('click', handleOutside))
onUnmounted(() => document.removeEventListener('click', handleOutside))
</script>

<style scoped>
.dropdown-enter-active, .dropdown-leave-active { transition: opacity 0.15s ease, transform 0.15s ease; }
.dropdown-enter-from, .dropdown-leave-to { opacity: 0; transform: translateY(-6px); }
</style>
