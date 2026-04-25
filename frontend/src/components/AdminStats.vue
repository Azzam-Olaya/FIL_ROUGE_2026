<template>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <div v-for="card in cards" :key="card.label" 
      class="bg-white p-6 rounded-[2rem] shadow-xl shadow-primary/5 border border-primary/5 flex items-center gap-5 hover:scale-[1.02] transition-all group">
      <div :class="card.color" class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0 transition-transform group-hover:rotate-6">
        <span class="material-symbols-outlined text-2xl font-bold">{{ card.icon }}</span>
      </div>
      <div>
        <h3 class="text-[10px] font-bold text-on-surface-variant uppercase tracking-[0.2em] mb-1">{{ card.label }}</h3>
        <p class="text-2xl font-black text-on-surface tracking-tight">
          {{ card.value }}
          <span v-if="card.suffix" class="text-[10px] font-bold text-primary ml-1 capitalize">{{ card.suffix }}</span>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/api/axios'

const stats = ref({
  total_missions: 0,
  total_clients: 0,
  total_freelancers: 0,
  total_contracts: 0,
  sum_revenue_platform: 0,
  pending_users: 0,
  banned_users: 0
})

const cards = computed(() => [
  { label: 'Missions', value: stats.value.total_missions, icon: 'work', color: 'bg-blue-50 text-blue-500' },
  { label: 'Clients', value: stats.value.total_clients, icon: 'group', color: 'bg-green-50 text-green-500' },
  { label: 'Freelancers', value: stats.value.total_freelancers, icon: 'engineering', color: 'bg-purple-50 text-purple-500' },
  { label: 'Revenus', value: stats.value.sum_revenue_platform, icon: 'payments', color: 'bg-primary/10 text-primary', suffix: 'MAD' },
  { label: 'En attente', value: stats.value.pending_users, icon: 'pending', color: 'bg-orange-50 text-orange-500' },
  { label: 'Bannis', value: stats.value.banned_users, icon: 'block', color: 'bg-red-50 text-red-500' },
])

const loadStats = async () => {
  try {
    const res = await api.get('/admin/stats')
    stats.value = res.data
  } catch (error) {
    console.error('Erreur chargement stats')
  }
}

onMounted(loadStats)
</script>