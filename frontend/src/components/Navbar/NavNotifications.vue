<template>
  <div class="relative" ref="ref">
    <button @click="toggle" class="p-2 rounded-full hover:bg-primary/10 transition-colors relative group" title="Notifications">
      <span class="material-symbols-outlined text-on-surface-variant group-hover:text-primary">notifications</span>
      <span v-if="store.unreadCount > 0" class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] bg-secondary text-white text-[10px] font-black rounded-full flex items-center justify-center px-1 animate-pulse">
        {{ store.unreadCount > 9 ? '9+' : store.unreadCount }}
      </span>
    </button>
    <Transition name="dd">
      <div v-if="open" class="absolute top-12 right-0 w-80 bg-white rounded-2xl shadow-2xl border border-primary/5 z-50 overflow-hidden">
        <div class="flex justify-between items-center px-4 py-3 border-b border-primary/5">
          <p class="font-bold text-sm text-on-surface">Notifications <span v-if="store.unreadCount" class="ml-1 text-[10px] bg-secondary text-white px-2 py-0.5 rounded-full font-bold">{{ store.unreadCount }}</span></p>
          <button v-if="store.unreadCount" @click.stop="store.markAllRead()" class="text-[10px] font-bold text-primary hover:underline uppercase tracking-widest">Tout lu</button>
        </div>
        <div class="max-h-72 overflow-y-auto divide-y divide-primary/5">
          <div v-if="loading" class="text-center py-6"><span class="material-symbols-outlined text-2xl text-primary/30 animate-spin block">progress_activity</span></div>
          <template v-else-if="store.notifications.length">
            <div v-for="n in store.notifications" :key="n.id" @click="click(n)"
              :class="!n.read ? 'bg-primary/5 border-l-4 border-primary' : 'bg-white'"
              class="flex gap-3 px-4 py-3 hover:bg-primary/10 cursor-pointer transition-colors">
              <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0" :class="bg(n.type)">
                <span class="material-symbols-outlined text-sm text-white" style="font-variation-settings:'FILL' 1">{{ icon(n.type) }}</span>
              </div>
              <div class="flex-1 min-w-0">
                <p class="font-semibold text-xs text-on-surface truncate">{{ n.title }}</p>
                <p class="text-[11px] text-on-surface-variant mt-0.5 line-clamp-2">{{ n.message }}</p>
                <p class="text-[10px] text-on-surface-variant/50 mt-1">{{ ago(n.created_at) }}</p>
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
</template>
<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useFreelancerStore } from '@/stores/freelancer'
const store = useFreelancerStore(), router = useRouter()
const open = ref(false), loading = ref(false), ref_ = ref(null)
const toggle = async () => {
  open.value = !open.value
  if (open.value) { loading.value = true; await store.fetchNotifications(); loading.value = false }
}
const click = (n) => {
  store.markRead(n.id); open.value = false
  if (n.type === 'message') window.dispatchEvent(new CustomEvent('freelancer-tab', { detail: 'messages' }))
}
const ago = (d) => { if (!d) return ''; const x = Date.now() - new Date(d); if (x < 60000) return 'À l\'instant'; if (x < 3600000) return Math.floor(x/60000)+'min'; if (x < 86400000) return Math.floor(x/3600000)+'h'; return Math.floor(x/86400000)+'j' }
const bg   = (t) => ({ message:'bg-primary', like:'bg-secondary', comment:'bg-tertiary' }[t]||'bg-primary')
const icon = (t) => ({ message:'chat_bubble', like:'favorite', comment:'comment' }[t]||'notifications')
const out  = (e) => { if (ref_.value && !ref_.value.contains(e.target)) open.value = false }
onMounted(() => document.addEventListener('click', out))
onUnmounted(() => document.removeEventListener('click', out))
</script>
<style scoped>
.dd-enter-active,.dd-leave-active{transition:opacity .15s ease,transform .15s ease}
.dd-enter-from,.dd-leave-to{opacity:0;transform:translateY(-6px)}
</style>
