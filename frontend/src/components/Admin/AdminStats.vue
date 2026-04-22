<template>
  <div class="space-y-8">
    <!-- Grille de Statistiques -->
    <section class="grid grid-cols-1 md:grid-cols-4 gap-8">
      <div v-for="stat in stats" :key="stat.label"
           class="bg-white p-8 rounded-2xl shadow-xl shadow-primary/5 relative overflow-hidden group border border-primary/5 transition-all hover:translate-y-[-4px]">
        <div class="relative z-10">
          <p class="text-on-surface-variant text-xs font-bold uppercase tracking-widest mb-2 opacity-60">{{ stat.label }}</p>
          <h3 class="text-4xl font-headline font-bold text-on-surface tracking-tighter">{{ stat.value }}</h3>
          <div class="flex items-center gap-1 mt-4 text-sm font-bold" :class="stat.trendColor || 'text-primary'">
            <span class="material-symbols-outlined text-sm">{{ stat.trendIcon || 'trending_up' }}</span>
            <span>{{ stat.trend }}</span>
          </div>
        </div>
        <div class="absolute top-0 right-0 p-4 opacity-[0.03] group-hover:opacity-[0.08] transition-opacity">
          <span class="material-symbols-outlined text-7xl">{{ stat.icon }}</span>
        </div>
      </div>
    </section>

    <!-- Graphiques et Modération -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Section Graphique (Evolution) -->
      <section class="lg:col-span-2 bg-white/80 backdrop-blur-md p-10 rounded-3xl border border-primary/5 shadow-xl shadow-primary/5">
        <div class="flex justify-between items-start mb-10">
          <div>
            <h4 class="font-headline text-2xl font-bold text-on-surface">Performance de Croissance</h4>
            <p class="text-on-surface-variant text-sm font-medium">Revenus vs Inscriptions (30 derniers jours)</p>
          </div>
        </div>
        <!-- Barres de graphique simulées en CSS/Tailwind -->
        <div class="h-64 flex items-end justify-between gap-3 px-2 border-b border-primary/5 pb-2">
           <div v-for="h in [40, 60, 55, 80, 65, 90, 75, 45, 85, 95]" :key="h"
                :style="{ height: h + '%' }"
                class="w-full bg-primary/20 rounded-t-xl relative group hover:bg-primary transition-all cursor-crosshair">
              <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-primary text-white text-[10px] font-bold px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap shadow-xl">
                {{ h }}k MAD
              </div>
           </div>
        </div>
        <div class="flex justify-between mt-6 text-[10px] text-on-surface-variant font-black tracking-widest px-2">
          <span>SEMAINE 1</span> <span>SEMAINE 2</span> <span>SEMAINE 3</span> <span>SEMAINE 4</span>
        </div>
      </section>

      <!-- Section Modération (Validation des utilisateurs) -->
      <section class="bg-primary/5 backdrop-blur-md p-10 rounded-3xl border border-primary/10">
        <h4 class="font-headline text-2xl font-bold mb-8">Modération Rapide</h4>
        <div class="space-y-4">
          <div v-for="user in pendingUsers" :key="user.id"
               class="flex gap-4 items-center p-4 bg-white rounded-2xl border-l-[6px] shadow-sm group hover:shadow-md transition-all"
               :class="user.role?.name === 'freelancer' ? 'border-primary' : 'border-secondary'">
            <div class="w-12 h-12 rounded-full bg-surface-container flex items-center justify-center font-bold text-lg text-primary overflow-hidden">
              {{ user.name[0] }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-bold truncate">{{ user.name }}</p>
              <p class="text-[11px] font-bold uppercase tracking-widest opacity-60" :class="user.role?.name === 'freelancer' ? 'text-primary' : 'text-secondary'">
                 {{ user.role?.name }}
              </p>
            </div>
            <!-- Actions rapides de validation / bannissement -->
            <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
               <button @click="approveUser(user.id)" class="text-primary hover:scale-110 active:scale-95"><span class="material-symbols-outlined">check_circle</span></button>
               <button @click="banUser(user.id)" class="text-secondary hover:scale-110 active:scale-95"><span class="material-symbols-outlined">cancel</span></button>
            </div>
          </div>
          <!-- Message si aucune tâche en attente -->
          <div v-if="pendingUsers.length === 0" class="text-center py-10 opacity-40 font-bold italic text-sm">
            Rien à modérer ! ✨
          </div>
        </div>
        <button class="w-full mt-8 py-4 border border-primary/10 rounded-full text-xs font-black uppercase tracking-widest text-primary hover:bg-primary hover:text-white transition-all shadow-sm">
          Tous les signalements
        </button>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

// Données réactives
const stats = ref([])
const pendingUsers = ref([])
const loading = ref(true)
const error = ref(null)

// Charger les statistiques
const loadStats = async () => {
  try {
    const response = await axios.get('/api/admin/stats')
    stats.value = [
      { label: 'Utilisateurs actifs', value: response.data.total_users || '0', icon: 'people', trend: '+12%', trendColor: 'text-primary', trendIcon: 'trending_up' },
      { label: 'Contrats signés', value: response.data.total_contracts || '0', icon: 'contract', trend: '+8%', trendColor: 'text-primary', trendIcon: 'trending_up' },
      { label: 'Revenus générés', value: `${response.data.total_revenue || 0} MAD`, icon: 'payments', trend: '+15%', trendColor: 'text-primary', trendIcon: 'trending_up' },
      { label: 'Signalements', value: response.data.pending_reports || '0', icon: 'report', trend: '-3%', trendColor: 'text-secondary', trendIcon: 'trending_down' }
    ]
  } catch (err) {
    console.error('Erreur lors du chargement des statistiques:', err)
    error.value = 'Erreur lors du chargement des statistiques'
    // Données par défaut en cas d'erreur
    stats.value = [
      { label: 'Utilisateurs actifs', value: '0', icon: 'people', trend: 'N/A', trendColor: 'text-gray-500', trendIcon: 'error' },
      { label: 'Contrats signés', value: '0', icon: 'contract', trend: 'N/A', trendColor: 'text-gray-500', trendIcon: 'error' },
      { label: 'Revenus générés', value: '0 MAD', icon: 'payments', trend: 'N/A', trendColor: 'text-gray-500', trendIcon: 'error' },
      { label: 'Signalements', value: '0', icon: 'report', trend: 'N/A', trendColor: 'text-gray-500', trendIcon: 'error' }
    ]
  }
}

// Charger les utilisateurs en attente
const loadPendingUsers = async () => {
  try {
    const response = await axios.get('/api/admin/users/pending')
    pendingUsers.value = response.data.users || []
  } catch (err) {
    console.error('Erreur lors du chargement des utilisateurs en attente:', err)
    pendingUsers.value = []
  }
}

// Méthodes de modération
const approveUser = async (userId) => {
  try {
    await axios.post(`/api/admin/users/${userId}/approve`)
    // Retirer l'utilisateur de la liste des en attente
    pendingUsers.value = pendingUsers.value.filter(user => user.id !== userId)
  } catch (error) {
    console.error('Erreur lors de l\'approbation:', error)
  }
}

const banUser = async (userId) => {
  try {
    await axios.post(`/api/admin/users/${userId}/ban`)
    // Retirer l'utilisateur de la liste des en attente
    pendingUsers.value = pendingUsers.value.filter(user => user.id !== userId)
  } catch (error) {
    console.error('Erreur lors du bannissement:', error)
  }
}

// Chargement initial
onMounted(async () => {
  await Promise.all([loadStats(), loadPendingUsers()])
  loading.value = false
})
</script>