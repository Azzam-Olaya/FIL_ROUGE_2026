<template>
  <div class="min-h-screen bg-surface font-body text-on-surface flex flex-col overflow-x-hidden">
    
    <!-- Top Bar for Admin -->
    <header class="bg-white border-b border-primary/10 flex justify-between items-center w-full px-4 md:px-8 py-4 sticky top-0 z-50 h-16">
      <div class="flex items-center gap-4">
        <button @click="sidebarOpen = !sidebarOpen" class="p-2 -ml-2 text-on-surface-variant lg:hidden hover:bg-primary/10 rounded-full transition-colors">
          <span class="material-symbols-outlined">menu</span>
        </button>
        <router-link to="/" class="flex items-center gap-2 group">
          <span class="material-symbols-outlined text-primary text-2xl font-bold group-hover:scale-110 transition-transform">auto_awesome</span>
          <div class="flex flex-col">
            <span class="font-headline font-black italic text-lg md:text-xl text-primary tracking-tight leading-none">MorLancer Pro</span>
            <span class="hidden sm:block text-[8px] md:text-[10px] opacity-60 font-bold uppercase tracking-[0.2em] leading-none mt-0.5">Administration</span>
          </div>
        </router-link>
      </div>

      <div class="flex gap-4 items-center">
        <div class="hidden sm:flex bg-primary/5 px-4 py-2 rounded-full items-center gap-2 border border-primary/5">
          <span class="w-2 h-2 bg-primary rounded-full animate-pulse"></span>
          <span class="text-[10px] font-bold text-primary uppercase tracking-widest">Plateforme Active</span>
        </div>
        <button @click="handleLogout" class="bg-secondary text-white p-2 sm:px-6 sm:py-2.5 rounded-full font-bold flex items-center gap-2 hover:brightness-110 transition-all shadow-lg shadow-secondary/20">
          <span class="material-symbols-outlined text-sm">logout</span>
          <span class="hidden sm:inline text-xs">Déconnexion</span>
        </button>
      </div>
    </header>

    <div class="flex flex-1 overflow-x-hidden">
      <!-- Mobile Overlay -->
      <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/50 z-30 lg:hidden"></div>

      <!-- Barre Latérale (Admin) -->
      <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="h-screen w-64 fixed left-0 top-0 bg-white border-r border-primary/5 flex flex-col gap-2 pt-24 pb-8 z-40 shadow-xl shadow-primary/5 transition-transform duration-300 lg:translate-x-0">
        <nav class="flex flex-col gap-2">
          <a v-for="item in navItems" :key="item.tab"
            @click="activeTab = item.tab; sidebarOpen = false"
            :class="activeTab === item.tab ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-on-surface-variant hover:bg-primary/10'"
            class="flex items-center gap-4 rounded-full mx-4 py-3 px-6 font-bold transition-all cursor-pointer">
            <span class="material-symbols-outlined" :style="activeTab === item.tab ? `font-variation-settings: 'FILL' 1` : ''">{{ item.icon }}</span>
            <span class="text-sm">{{ item.label }}</span>
          </a>
        </nav>
      </aside>

      <!-- Contenu Principal -->
      <main class="lg:ml-64 p-4 md:p-8 min-h-screen zellige-pattern flex-1 transition-all duration-300">
        <header class="mb-8 md:mb-12">
          <h2 class="font-headline text-3xl md:text-5xl font-black text-primary tracking-tight">{{ getTabTitle() }}</h2>
          <p class="text-on-surface-variant mt-2 text-base md:text-lg font-medium italic">{{ getTabDescription() }}</p>
        </header>

        <!-- Contenu selon l'onglet actif -->
        <div class="space-y-6">
          <AdminStats v-if="activeTab === 'stats'" />
          <AdminUsers v-if="activeTab === 'users'" />
          <AdminCategories v-if="activeTab === 'categories'" />
          <AdminContracts v-if="activeTab === 'contracts'" />
          <AdminPayments v-if="activeTab === 'payments'" />
          <AdminReports v-if="activeTab === 'reports'" />

          <div v-if="['reports'].includes(activeTab) && false" class="text-center py-20 bg-white/50 backdrop-blur-sm rounded-[2.5rem] border border-primary/5">
            <span class="material-symbols-outlined text-6xl text-on-surface-variant/30 mb-4 animate-bounce">construction</span>
            <p class="text-on-surface-variant font-medium">Module {{ activeTab }} - En développement</p>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AdminStats      from '@/components/Admin/AdminStats.vue'
import AdminUsers      from '@/components/Admin/AdminUsers.vue'
import AdminCategories from '@/components/Admin/AdminCategories.vue'
import AdminContracts  from '@/components/Admin/AdminContracts.vue'
import AdminPayments   from '@/components/Admin/AdminPayments.vue'
import AdminReports    from '@/components/Admin/AdminReports.vue'

const activeTab   = ref('stats')
const sidebarOpen = ref(false)
const router      = useRouter()
const authStore   = useAuthStore()

const navItems = [
  { tab: 'stats',      icon: 'dashboard',   label: 'Tableau de bord' },
  { tab: 'users',      icon: 'group',       label: 'Utilisateurs'    },
  { tab: 'contracts',  icon: 'handshake',   label: 'Contrats'        },
  { tab: 'payments',   icon: 'payments',    label: 'Paiements'       },
  { tab: 'categories', icon: 'category',    label: 'Catégories'      },
  { tab: 'reports',    icon: 'report',      label: 'Signalements'    },
]

const getTabTitle = () => {
  const titles = {
    stats:      'Vue d\'ensemble',
    users:      'Gestion des Utilisateurs',
    contracts:  'Statistiques Contrats',
    payments:   'Statistiques Paiements',
    categories: 'Gestion des Catégories',
    reports:    'Gestion des Signalements'
  }
  return titles[activeTab.value] || 'Vue d\'ensemble'
}

const getTabDescription = () => {
  const descriptions = {
    stats:      'Suivi de l\'écosystème MorLancer',
    users:      'Validation et gestion des comptes utilisateurs',
    contracts:  'Statuts, volumes et top freelancers',
    payments:   'Dépôts, transactions et revenus plateforme',
    categories: 'Organisation des catégories de services',
    reports:    'Traitement des signalements et litiges'
  }
  return descriptions[activeTab.value] || 'Suivi de l\'écosystème MorLancer'
}

const handleLogout = () => {
  authStore.clearAuth()
  router.push('/login')
}
</script>

<style scoped>
.zellige-pattern {
  background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M30 0l5 25h25l-20 15 8 20-18-12-18 12 8-20L0 25h25z' fill='%23006233' fill-opacity='0.03' fill-rule='evenodd'/%3E%3C/svg%3E");
}
</style>

<style scoped>
/**
 * Motif Zellige (Fleurs d'étoiles) pour le Dashboard Admin.
 */
.zellige-pattern {
  background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M30 0l5 25h25l-20 15 8 20-18-12-18 12 8-20L0 25h25z' fill='%23006233' fill-opacity='0.03' fill-rule='evenodd'/%3E%3C/svg%3E");
}
</style>
