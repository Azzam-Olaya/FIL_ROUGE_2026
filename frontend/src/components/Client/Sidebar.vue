<template>
  <aside class="h-screen w-64 fixed left-0 top-0 bg-surface-container border-r border-primary/10 flex flex-col pt-20 pb-6 z-40">
    <div class="px-8 mb-6">
      <p class="text-[10px] text-on-surface-variant/60 tracking-[0.2em] uppercase font-bold">Menu principal</p>
    </div>
    <nav class="flex-grow flex flex-col gap-1 px-3">
      <button v-for="item in navItems" :key="item.tab" @click="$emit('change-tab', item.tab)"
        :class="activeTab === item.tab ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-on-surface-variant hover:bg-primary/10'"
        class="flex items-center gap-3 rounded-full py-3 px-5 transition-all w-full text-left">
        <span class="material-symbols-outlined text-xl"
          :style="activeTab === item.tab ? `font-variation-settings:'FILL' 1` : ''">{{ item.icon }}</span>
        <span class="text-sm" :class="activeTab === item.tab ? 'font-bold' : 'font-medium'">{{ item.label }}</span>
      </button>
    </nav>
    <div class="px-4 mt-auto">
      <div class="p-4 bg-surface-container-high/50 rounded-[2rem] border border-primary/5">
        <button @click="logout"
          class="w-full flex items-center justify-center gap-2 bg-secondary text-white py-3 rounded-full font-bold text-xs hover:brightness-110 transition-all shadow-lg shadow-secondary/20">
          <span class="material-symbols-outlined text-sm">logout</span>
          Se déconnecter
        </button>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { useRouter } from 'vue-router'
defineProps({ activeTab: { type: String, required: true } })
defineEmits(['change-tab'])
const router = useRouter()
const navItems = [
  { tab: 'dashboard', icon: 'dashboard',  label: 'Dashboard'    },
  { tab: 'briefs',    icon: 'work',        label: 'Mes missions' },
  { tab: 'messages',  icon: 'chat_bubble', label: 'Messages'     },
  { tab: 'payments',  icon: 'payments',    label: 'Paiement'     },
  { tab: 'contracts', icon: 'description', label: 'Contrat'      },
  { tab: 'profile',   icon: 'person',      label: 'Profil'       },
]
const logout = () => { localStorage.clear(); router.push('/login') }
</script>
