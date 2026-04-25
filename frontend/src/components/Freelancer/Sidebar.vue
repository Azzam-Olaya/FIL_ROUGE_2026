<template>
  <div>
    <!-- Mobile Overlay -->
    <div v-if="isOpen" @click="$emit('close')" class="fixed inset-0 bg-black/50 z-30 lg:hidden"></div>

    <aside :class="isOpen ? 'translate-x-0' : '-translate-x-full'"
      class="h-screen w-64 fixed left-0 top-0 bg-surface-container border-r border-primary/10 flex flex-col gap-2 pt-24 pb-8 z-40 transition-transform duration-300 lg:translate-x-0">
      <nav class="flex-grow flex flex-col gap-2">
        <a v-for="item in navItems" :key="item.tab"
          @click="$emit('change-tab', item.tab); $emit('close')"
          :class="activeTab === item.tab ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-on-surface-variant hover:bg-primary/10'"
          class="flex items-center gap-4 rounded-full mx-4 py-3 px-6 transition-all cursor-pointer group">
          <span class="material-symbols-outlined group-hover:scale-110 transition-transform"
            :style="activeTab === item.tab ? `font-variation-settings: 'FILL' 1` : ''">{{ item.icon }}</span>
          <span class="text-sm" :class="activeTab === item.tab ? 'font-bold' : 'font-medium'">{{ item.label }}</span>
        </a>
      </nav>

      <div class="px-4 mt-auto">
        <div class="p-4 bg-surface-container-high/50 rounded-[2rem] border border-primary/5">
          <button @click="handleLogout"
            class="w-full bg-secondary text-white py-3 rounded-full font-bold text-xs hover:brightness-110 transition-all shadow-lg shadow-secondary/20 flex items-center justify-center gap-2">
            <span class="material-symbols-outlined text-sm">logout</span>
            Se déconnecter
          </button>
        </div>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

defineProps({ 
  activeTab: { type: String, required: true },
  isOpen: { type: Boolean, default: false }
})
defineEmits(['change-tab', 'close'])

const router = useRouter()
const authStore = useAuthStore()

const navItems = [
  { tab: 'dashboard', icon: 'dashboard',  label: 'Dashboard'   },
  { tab: 'briefs',    icon: 'work',        label: 'Mes Briefs'  },
  { tab: 'messages',  icon: 'chat_bubble', label: 'Messages'    },
  { tab: 'payments',  icon: 'payments',    label: 'Paiements'   },
  { tab: 'contracts', icon: 'description', label: 'Contrats'    },
  { tab: 'profile',   icon: 'person',      label: 'Profil'      },
]

const handleLogout = () => {
  authStore.clearAuth()
  router.push('/login')
}
</script>
