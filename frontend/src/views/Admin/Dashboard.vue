<template>
  <div class="min-h-screen bg-surface font-body text-on-surface flex overflow-x-hidden">
    <!-- Barre Latérale (SideNavBar) : Navigation pour l'Administrateur -->
    <aside class="h-screen w-64 fixed left-0 top-0 bg-white border-r border-primary/5 flex flex-col gap-2 pt-24 pb-8 z-40 shadow-xl shadow-primary/5">
      <div class="px-8 mb-10">
        <h1 class="text-2xl font-black text-primary font-headline italic">MorLancer Pro</h1>
        <p class="text-[10px] opacity-60 tracking-[0.2em] uppercase mt-1 font-bold">Artisanat Digital</p>
      </div>
      <nav class="flex flex-col gap-2">
        <a @click="activeTab = 'stats'" :class="activeTab === 'stats' ? 'bg-primary text-white' : 'text-on-surface-variant hover:bg-primary/10'" class="flex items-center gap-4 rounded-full mx-4 py-3 px-6 font-bold shadow-lg shadow-primary/20 transition-all scale-98 hover:scale-100 cursor-pointer" href="#">
          <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">dashboard</span>
          <span class="text-sm">Tableau de bord</span>
        </a>
        <a @click="activeTab = 'users'" :class="activeTab === 'users' ? 'bg-primary text-white' : 'text-on-surface-variant hover:bg-primary/10'" class="flex items-center gap-4 rounded-full mx-4 py-3 px-6 font-bold shadow-lg shadow-primary/20 transition-all scale-98 hover:scale-100 cursor-pointer" href="#">
          <span class="material-symbols-outlined">group</span>
          <span class="text-sm">Utilisateurs</span>
        </a>
        <a @click="activeTab = 'contracts'" :class="activeTab === 'contracts' ? 'bg-primary text-white' : 'text-on-surface-variant hover:bg-primary/10'" class="flex items-center gap-4 rounded-full mx-4 py-3 px-6 font-bold shadow-lg shadow-primary/20 transition-all scale-98 hover:scale-100 cursor-pointer" href="#">
          <span class="material-symbols-outlined">payments</span>
          <span class="text-sm">Contrats</span>
        </a>
        <a @click="activeTab = 'messages'" :class="activeTab === 'messages' ? 'bg-primary text-white' : 'text-on-surface-variant hover:bg-primary/10'" class="flex items-center gap-4 rounded-full mx-4 py-3 px-6 font-bold shadow-lg shadow-primary/20 transition-all scale-98 hover:scale-100 cursor-pointer" href="#">
          <span class="material-symbols-outlined">chat_bubble</span>
          <span class="text-sm">Messages</span>
        </a>
        <a @click="activeTab = 'categories'" :class="activeTab === 'categories' ? 'bg-primary text-white' : 'text-on-surface-variant hover:bg-primary/10'" class="flex items-center gap-4 rounded-full mx-4 py-3 px-6 font-bold shadow-lg shadow-primary/20 transition-all scale-98 hover:scale-100 cursor-pointer" href="#">
          <span class="material-symbols-outlined">category</span>
          <span class="text-sm">Catégories</span>
        </a>
        <a @click="activeTab = 'reports'" :class="activeTab === 'reports' ? 'bg-primary text-white' : 'text-on-surface-variant hover:bg-primary/10'" class="flex items-center gap-4 rounded-full mx-4 py-3 px-6 font-bold shadow-lg shadow-primary/20 transition-all scale-98 hover:scale-100 cursor-pointer" href="#">
          <span class="material-symbols-outlined">report</span>
          <span class="text-sm">Signalements</span>
        </a>
      </nav>
    </aside>

    <!-- Contenu Principal (Dashboard Admin) -->
    <main class="ml-64 p-12 min-h-screen zellige-pattern flex-1">
      <header class="flex justify-between items-end mb-16 px-4">
        <div>
          <h2 class="font-headline text-5xl font-black text-primary tracking-tight">{{ getTabTitle() }}</h2>
          <p class="text-on-surface-variant mt-2 text-lg font-medium italic">{{ getTabDescription() }}</p>
        </div>
        <div class="flex gap-4 items-center">
          <div class="bg-white/80 backdrop-blur-md px-6 py-3 rounded-full flex items-center gap-3 border border-primary/5 shadow-sm">
            <span class="w-2.5 h-2.5 bg-primary rounded-full animate-pulse shadow-[0_0_8px_rgba(0,72,36,0.4)]"></span>
            <span class="text-sm font-bold text-primary italic">Plateforme Active</span>
          </div>
          <button @click="logout" class="bg-secondary text-white px-8 py-3 rounded-full font-bold flex items-center gap-2 hover:brightness-110 active:scale-95 transition-all shadow-lg shadow-secondary/20">
            <span class="material-symbols-outlined text-sm">logout</span>
            Déconnexion
          </button>
        </div>
      </header>

      <!-- Contenu selon l'onglet actif -->
      <div class="px-4">
        <!-- Onglet Statistiques -->
        <AdminStats v-if="activeTab === 'stats'" />

        <!-- Onglet Utilisateurs -->
        <AdminUsers v-if="activeTab === 'users'" />

        <!-- Onglet Contrats -->
        <div v-if="activeTab === 'contracts'" class="text-center py-20">
          <span class="material-symbols-outlined text-6xl text-gray-400">construction</span>
          <p class="text-gray-500 mt-4">Gestion des contrats - En développement</p>
        </div>

        <!-- Onglet Messages -->
        <div v-if="activeTab === 'messages'" class="text-center py-20">
          <span class="material-symbols-outlined text-6xl text-gray-400">construction</span>
          <p class="text-gray-500 mt-4">Contrôle des conversations - En développement</p>
        </div>

        <!-- Onglet Catégories -->
        <div v-if="activeTab === 'categories'" class="text-center py-20">
          <span class="material-symbols-outlined text-6xl text-gray-400">construction</span>
          <p class="text-gray-500 mt-4">Gestion des catégories - En développement</p>
        </div>

        <!-- Onglet Signalements -->
        <div v-if="activeTab === 'reports'" class="text-center py-20">
          <span class="material-symbols-outlined text-6xl text-gray-400">construction</span>
          <p class="text-gray-500 mt-4">Gestion des signalements - En développement</p>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import AdminStats from '@/components/Admin/AdminStats.vue'
import AdminUsers from '@/components/Admin/AdminUsers.vue'

// Onglet actif
const activeTab = ref('stats')

// Router
const router = useRouter()

// Méthodes pour les onglets
const setActiveTab = (tab) => {
  activeTab.value = tab
}

const getTabTitle = () => {
  const titles = {
    stats: 'Vue d\'ensemble',
    users: 'Gestion des Utilisateurs',
    contracts: 'Gestion des Contrats',
    messages: 'Contrôle des Conversations',
    categories: 'Gestion des Catégories',
    reports: 'Gestion des Signalements'
  }
  return titles[activeTab.value] || 'Vue d\'ensemble'
}

const getTabDescription = () => {
  const descriptions = {
    stats: 'Suivi de l\'écosystème artisanal digital',
    users: 'Validation et gestion des comptes utilisateurs',
    contracts: 'Supervision des contrats et transactions',
    messages: 'Modération des conversations et échanges',
    categories: 'Organisation des catégories de services',
    reports: 'Traitement des signalements et litiges'
  }
  return descriptions[activeTab.value] || 'Suivi de l\'écosystème artisanal digital'
}

// Déconnexion
const logout = async () => {
  try {
    await axios.post('/api/logout')
    localStorage.removeItem('token')
    router.push('/login')
  } catch (error) {
    console.error('Erreur lors de la déconnexion:', error)
    // Force logout même en cas d'erreur
    localStorage.removeItem('token')
    router.push('/login')
  }
}

// Configuration axios pour inclure le token
axios.defaults.headers.common['Authorization'] = `Bearer ${localStorage.getItem('token')}`
</script>

<style scoped>
/**
 * Motif Zellige (Fleurs d'étoiles) pour le Dashboard Admin.
 */
.zellige-pattern {
  background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M30 0l5 25h25l-20 15 8 20-18-12-18 12 8-20L0 25h25z' fill='%23006233' fill-opacity='0.03' fill-rule='evenodd'/%3E%3C/svg%3E");
}
</style>
