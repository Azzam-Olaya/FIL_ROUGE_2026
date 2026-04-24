<template>
  <div class="relative ml-2" ref="containerRef">
    <button @click="open = !open"
      class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-bold text-xs shadow-lg shadow-primary/20 hover:scale-110 transition-transform"
      :title="store.userName">
      {{ store.userInitials }}
    </button>

    <Transition name="dropdown">
      <div v-if="open" class="absolute top-12 right-0 w-56 bg-white rounded-2xl shadow-2xl border border-primary/5 z-50 overflow-hidden">
        <div class="px-4 py-4 border-b border-primary/5 bg-primary/5 flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
            {{ store.userInitials }}
          </div>
          <div class="min-w-0">
            <p class="font-bold text-sm text-on-surface truncate">{{ store.userName }}</p>
            <p class="text-[10px] text-on-surface-variant">Freelancer</p>
          </div>
        </div>
        <div class="py-2">
          <button @click="navigate('/freelancer/dashboard', 'profile')"
            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-on-surface hover:bg-primary/10 transition-colors text-left">
            <span class="material-symbols-outlined text-base text-on-surface-variant">person</span>Profil
          </button>
          <button @click="navigate('/freelancer/dashboard', 'messages')"
            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-on-surface hover:bg-primary/10 transition-colors text-left">
            <span class="material-symbols-outlined text-base text-on-surface-variant">chat_bubble</span>Messages
          </button>
          <div class="border-t border-primary/5 my-1"></div>
          <button @click="logout"
            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-secondary hover:bg-secondary/10 transition-colors text-left">
            <span class="material-symbols-outlined text-base">logout</span>Se déconnecter
          </button>
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
const containerRef = ref(null)

const navigate = (path, tab) => {
  open.value = false
  if (tab) {
    router.push({ path, query: { tab } })
    window.dispatchEvent(new CustomEvent('freelancer-tab', { detail: tab }))
  } else {
    router.push(path)
  }
}

const logout = () => { localStorage.clear(); window.location.href = '/login' }

const handleOutside = (e) => { if (containerRef.value && !containerRef.value.contains(e.target)) open.value = false }
onMounted(() => document.addEventListener('click', handleOutside))
onUnmounted(() => document.removeEventListener('click', handleOutside))
</script>

<style scoped>
.dropdown-enter-active, .dropdown-leave-active { transition: opacity 0.15s ease, transform 0.15s ease; }
.dropdown-enter-from, .dropdown-leave-to { opacity: 0; transform: translateY(-6px); }
</style>
