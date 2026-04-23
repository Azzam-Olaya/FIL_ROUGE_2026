<template>
  <header class="bg-surface/80 backdrop-blur-md flex justify-between items-center w-full px-8 py-4 sticky top-0 z-50 shadow-[0px_20px_40px_rgba(30,28,13,0.06)] border-b border-primary/5">
    <div class="flex items-center gap-8">
      <router-link to="/" class="flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-3xl font-bold" style="font-variation-settings: 'FILL' 1;">auto_awesome</span>
        <span class="font-headline font-black italic text-2xl text-primary tracking-tight">MorLancer</span>
      </router-link>
      <div class="relative hidden lg:block">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
        <input v-model="searchQuery" @keyup.enter="onSearch"
          class="bg-surface-container-highest/50 border-none rounded-full py-2 pl-10 pr-4 text-xs w-64 focus:ring-2 focus:ring-primary/20 transition-all font-medium"
          placeholder="Rechercher une mission..." type="text"/>
      </div>
    </div>

    <div class="flex items-center gap-4">
      <!-- Nouveau Brief (freelancer seulement) -->
      <button v-if="role === 'freelancer'" @click="$emit('new-brief')"
        class="hidden md:flex items-center gap-2 bg-primary text-white px-5 py-2 rounded-full font-bold text-xs hover:brightness-110 active:scale-95 transition-all shadow-lg shadow-primary/20">
        <span class="material-symbols-outlined text-sm">add</span>
        Nouveau Brief
      </button>

      <!-- Favoris -->
      <button @click="$emit('open-favorites')"
        class="p-2 rounded-full hover:bg-primary/10 transition-colors relative">
        <span class="material-symbols-outlined text-on-surface-variant">favorite</span>
      </button>

      <!-- Notifications -->
      <button @click="showNotifs = !showNotifs"
        class="p-2 rounded-full hover:bg-primary/10 transition-colors relative">
        <span class="material-symbols-outlined text-on-surface-variant">notifications</span>
        <span v-if="notifCount > 0"
          class="absolute top-1 right-1 w-4 h-4 bg-secondary rounded-full text-white text-[9px] font-bold flex items-center justify-center">
          {{ notifCount > 9 ? '9+' : notifCount }}
        </span>
      </button>

      <!-- Avatar + nom -->
      <div class="flex items-center gap-2 cursor-pointer group" @click="$emit('open-profile')">
        <div class="w-9 h-9 rounded-full bg-primary flex items-center justify-center text-white font-bold text-sm shadow-lg shadow-primary/20">
          {{ initials }}
        </div>
        <span class="hidden md:block text-sm font-bold text-on-surface group-hover:text-primary transition-colors">{{ userName }}</span>
      </div>
    </div>

    <!-- Dropdown notifications -->
    <div v-if="showNotifs"
      class="absolute top-16 right-8 w-80 bg-white rounded-2xl shadow-2xl border border-primary/5 z-50 overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <span class="font-bold text-sm">Notifications</span>
        <span class="text-xs text-gray-400">{{ notifCount }} nouvelles</span>
      </div>
      <div v-if="notifications.length === 0" class="px-5 py-8 text-center text-gray-400 text-sm">
        Aucune notification
      </div>
      <div v-for="n in notifications" :key="n.at" class="px-5 py-3 hover:bg-gray-50 border-b border-gray-50 last:border-0">
        <div class="flex items-start gap-3">
          <span class="material-symbols-outlined text-sm mt-0.5" :class="n.type === 'like' ? 'text-red-400' : 'text-primary'">
            {{ n.type === 'like' ? 'favorite' : 'chat_bubble' }}
          </span>
          <div>
            <p class="text-xs font-bold text-gray-700">{{ n.user }}
              <span class="font-normal text-gray-500">{{ n.type === 'like' ? 'a aimé' : 'a commenté' }}</span>
              votre brief
            </p>
            <p class="text-xs text-primary font-medium truncate">{{ n.brief }}</p>
            <p v-if="n.body" class="text-xs text-gray-400 italic truncate">"{{ n.body }}"</p>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  role: { type: String, default: '' }
})

const emit = defineEmits(['new-brief', 'open-favorites', 'open-profile', 'search'])

const searchQuery = ref('')
const showNotifs = ref(false)
const notifications = ref([])

const user = JSON.parse(localStorage.getItem('user') || '{}')
const userName = computed(() => user?.name || '')
const initials = computed(() => userName.value.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2))
const notifCount = computed(() => notifications.value.length)

const onSearch = () => emit('search', searchQuery.value)

const loadNotifications = async () => {
  if (props.role !== 'freelancer') return
  try {
    const res = await axios.get('http://localhost:8000/api/freelancer/notifications', {
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
    })
    notifications.value = res.data.slice(0, 10)
  } catch {}
}

const closeOnOutside = (e) => {
  if (showNotifs.value && !e.target.closest('header')) showNotifs.value = false
}

onMounted(() => {
  loadNotifications()
  document.addEventListener('click', closeOnOutside)
})
onUnmounted(() => document.removeEventListener('click', closeOnOutside))
</script>
