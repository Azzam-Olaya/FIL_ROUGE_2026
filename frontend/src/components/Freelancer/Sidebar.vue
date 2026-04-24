<template>
  <aside class="h-screen w-64 fixed left-0 top-0 bg-surface-container border-r border-primary/10 flex flex-col gap-2 pt-24 pb-8 z-40">
    <nav class="flex-grow flex flex-col gap-2">
      <!-- Tableau de bord -->
      <a
        @click="$emit('change-tab', 'dashboard')"
        :class="activeTab === 'dashboard' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-on-surface-variant hover:bg-primary/10'"
        class="flex items-center gap-4 rounded-full mx-4 py-3 px-6 transition-all cursor-pointer group"
      >
        <span class="material-symbols-outlined" :class="{ 'scale-110': activeTab === 'dashboard' }">dashboard</span>
        <span class="text-sm" :class="activeTab === 'dashboard' ? 'font-bold' : 'font-medium group-hover:font-bold'">Tableau de bord</span>
      </a>

      <!-- Mes Briefs -->
      <a
        @click="$emit('change-tab', 'briefs')"
        :class="activeTab === 'briefs' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-on-surface-variant hover:bg-primary/10'"
        class="flex items-center gap-4 rounded-full mx-4 py-3 px-6 transition-all cursor-pointer group"
      >
        <span class="material-symbols-outlined group-hover:scale-110 transition-transform">work</span>
        <span class="text-sm" :class="activeTab === 'briefs' ? 'font-bold' : 'font-medium group-hover:font-bold'">Mes Briefs</span>
      </a>

      <!-- Messages -->
      <a
        @click="$emit('change-tab', 'messages')"
        :class="activeTab === 'messages' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-on-surface-variant hover:bg-primary/10'"
        class="flex items-center gap-4 rounded-full mx-4 py-3 px-6 transition-all cursor-pointer group"
      >
        <span class="material-symbols-outlined group-hover:scale-110 transition-transform">chat_bubble</span>
        <span class="text-sm" :class="activeTab === 'messages' ? 'font-bold' : 'font-medium group-hover:font-bold'">Messages</span>
      </a>

      <!-- Paiements -->
      <a
        @click="$emit('change-tab', 'payments')"
        :class="activeTab === 'payments' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-on-surface-variant hover:bg-primary/10'"
        class="flex items-center gap-4 rounded-full mx-4 py-3 px-6 transition-all cursor-pointer group"
      >
        <span class="material-symbols-outlined group-hover:scale-110 transition-transform">payments</span>
        <span class="text-sm" :class="activeTab === 'payments' ? 'font-bold' : 'font-medium group-hover:font-bold'">Paiements</span>
      </a>
      
      <!-- Contrats -->
      <a
        @click="$emit('change-tab', 'contracts')"
        :class="activeTab === 'contracts' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-on-surface-variant hover:bg-primary/10'"
        class="flex items-center gap-4 rounded-full mx-4 py-3 px-6 transition-all cursor-pointer group"
      >
        <span class="material-symbols-outlined group-hover:scale-110 transition-transform">description</span>
        <span class="text-sm" :class="activeTab === 'contracts' ? 'font-bold' : 'font-medium group-hover:font-bold'">Contrats</span>
      </a>

      <!-- Profil -->
      <a
        @click="$emit('change-tab', 'profile')"
        :class="activeTab === 'profile' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-on-surface-variant hover:bg-primary/10'"
        class="flex items-center gap-4 rounded-full mx-4 py-3 px-6 transition-all cursor-pointer group"
      >
        <span class="material-symbols-outlined group-hover:scale-110 transition-transform">person</span>
        <span class="text-sm" :class="activeTab === 'profile' ? 'font-bold' : 'font-medium group-hover:font-bold'">Profil</span>
      </a>
    </nav>

    <!-- Zone Déconnecter -->
    <div class="px-4 mt-auto">
      <div class="p-6 bg-surface-container-high/50 rounded-[2rem] border border-primary/5 flex flex-col gap-4 text-center">
        <button @click="logout" class="w-full bg-secondary text-white py-3 rounded-full font-bold text-xs hover:brightness-110 transition-all shadow-lg shadow-secondary/20 flex items-center justify-center gap-2">
          <span class="material-symbols-outlined text-sm">logout</span>
          Déconnecter
        </button>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { useRouter } from 'vue-router'

defineProps({
  activeTab: {
    type: String,
    required: true
  }
})

defineEmits(['change-tab'])

const router = useRouter()

const logout = () => {
    router.push('/login')
}
</script>
