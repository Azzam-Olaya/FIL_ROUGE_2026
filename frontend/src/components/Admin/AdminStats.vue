<template>
  <div class="space-y-8">

    <!-- KPI Cards -->
    <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
      <div v-for="stat in kpis" :key="stat.label"
        class="bg-white p-7 rounded-2xl shadow-sm border border-primary/5 relative overflow-hidden group hover:-translate-y-1 transition-all">
        <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-2">{{ stat.label }}</p>
        <h3 class="text-4xl font-black text-gray-800 tracking-tight">{{ stat.value }}</h3>
        <div class="flex items-center gap-1 mt-3 text-sm font-bold"
          :class="stat.trend > 0 ? 'text-green-600' : stat.trend < 0 ? 'text-red-500' : 'text-gray-400'">
          <span class="material-symbols-outlined text-sm">
            {{ stat.trend > 0 ? 'trending_up' : stat.trend < 0 ? 'trending_down' : 'trending_flat' }}
          </span>
          <span>{{ stat.trend > 0 ? '+' : '' }}{{ stat.trend }}% ce mois</span>
        </div>
        <span class="material-symbols-outlined absolute -bottom-2 -right-2 text-8xl opacity-[0.04] group-hover:opacity-[0.07] transition-opacity text-primary">
          {{ stat.icon }}
        </span>
      </div>
    </section>

    <!-- Graphique + Modération -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Graphique inscriptions réelles par semaine -->
      <section class="lg:col-span-2 bg-white p-8 rounded-2xl border border-primary/5 shadow-sm">
        <div class="mb-6">
          <h4 class="font-bold text-lg text-gray-800">Inscriptions — 4 dernières semaines</h4>
          <p class="text-sm text-gray-400">Nouveaux clients & freelancers enregistrés</p>
        </div>

        <div v-if="loading" class="h-48 flex items-center justify-center text-gray-300">
          <span class="material-symbols-outlined animate-spin text-4xl">progress_activity</span>
        </div>

        <div v-else class="h-48 flex items-end gap-4 border-b border-gray-100 pb-2">
          <div v-for="week in weeklyData" :key="week.label"
            class="flex-1 flex flex-col items-center gap-2 group">
            <span class="text-xs font-bold text-primary opacity-0 group-hover:opacity-100 transition-opacity">
              {{ week.count }}
            </span>
            <div class="w-full rounded-t-xl transition-all duration-700 hover:bg-primary cursor-pointer"
              :style="{ height: maxWeekly > 0 ? Math.max((week.count / maxWeekly * 100), 4) + '%' : '4px' }"
              :class="week.count > 0 ? 'bg-primary/30 hover:bg-primary' : 'bg-gray-100'">
            </div>
            <span class="text-[10px] font-bold text-gray-400 uppercase">{{ week.label }}</span>
          </div>
        </div>

        <!-- Répartition réelle -->
        <div class="mt-6 grid grid-cols-3 gap-4">
          <div class="text-center p-3 bg-primary/5 rounded-xl">
            <p class="text-2xl font-black text-primary">{{ data.total_clients ?? 0 }}</p>
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mt-1">Clients</p>
          </div>
          <div class="text-center p-3 bg-secondary/5 rounded-xl">
            <p class="text-2xl font-black text-secondary">{{ data.total_freelancers ?? 0 }}</p>
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mt-1">Freelancers</p>
          </div>
          <div class="text-center p-3 bg-yellow-50 rounded-xl">
            <p class="text-2xl font-black text-yellow-600">{{ data.pending_users ?? 0 }}</p>
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mt-1">En attente</p>
          </div>
        </div>
      </section>

      <!-- Modération rapide -->
      <section class="bg-primary/5 p-7 rounded-2xl border border-primary/10">
        <h4 class="font-bold text-lg mb-5">Modération Rapide</h4>
        <div class="space-y-3 max-h-72 overflow-y-auto">
          <div v-for="user in pendingUsers" :key="user.id"
            class="flex items-center gap-3 p-3 bg-white rounded-xl border-l-4 shadow-sm group hover:shadow-md transition-all"
            :class="user.role?.name === 'freelancer' ? 'border-primary' : 'border-secondary'">
            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center font-black text-primary text-sm flex-shrink-0">
              {{ user.name[0].toUpperCase() }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-bold truncate">{{ user.name }}</p>
              <p class="text-[10px] font-bold uppercase tracking-widest opacity-60"
                :class="user.role?.name === 'freelancer' ? 'text-primary' : 'text-secondary'">
                {{ user.role?.name }}
              </p>
            </div>
            <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
              <button @click="approveUser(user.id)" title="Approuver"
                class="p-1 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 transition-colors">
                <span class="material-symbols-outlined text-sm">check_circle</span>
              </button>
              <button @click="rejectUser(user.id)" title="Rejeter"
                class="p-1 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition-colors">
                <span class="material-symbols-outlined text-sm">cancel</span>
              </button>
            </div>
          </div>
          <div v-if="!loading && pendingUsers.length === 0"
            class="text-center py-10 text-gray-400 text-sm font-medium italic">
            Aucun utilisateur en attente ✨
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const data = ref({})
const pendingUsers = ref([])
const loading = ref(true)

const token = () => localStorage.getItem('token')
const headers = computed(() => ({ Authorization: `Bearer ${token()}` }))

const weeklyData = computed(() => data.value.weekly_registrations || [])
const maxWeekly = computed(() => Math.max(...weeklyData.value.map(w => w.count), 1))

const kpis = computed(() => {
  const d = data.value
  return [
    {
      label: 'Utilisateurs total',
      value: d.total_users ?? 0,
      trend: d.users_trend ?? 0,
      icon: 'people'
    },
    {
      label: 'Missions publiées',
      value: d.total_missions ?? 0,
      trend: d.missions_trend ?? 0,
      icon: 'work'
    },
    {
      label: 'Contrats signés',
      value: d.total_contracts ?? 0,
      trend: d.contracts_trend ?? 0,
      icon: 'handshake'
    },
    {
      label: 'Revenus (MAD)',
      value: Number(d.total_revenue ?? 0).toLocaleString('fr-FR'),
      trend: d.revenue_trend ?? 0,
      icon: 'payments'
    },
  ]
})

const loadStats = async () => {
  const res = await axios.get('http://localhost:8000/api/admin/stats', { headers: headers.value })
  data.value = res.data
}

const loadPending = async () => {
  const res = await axios.get('http://localhost:8000/api/admin/pending-users', { headers: headers.value })
  pendingUsers.value = res.data
}

const approveUser = async (id) => {
  await axios.post(`http://localhost:8000/api/admin/users/${id}/approve`, {}, { headers: headers.value })
  pendingUsers.value = pendingUsers.value.filter(u => u.id !== id)
  if (data.value.pending_users > 0) data.value.pending_users--
}

const rejectUser = async (id) => {
  await axios.post(`http://localhost:8000/api/admin/users/${id}/reject`, {}, { headers: headers.value })
  pendingUsers.value = pendingUsers.value.filter(u => u.id !== id)
  if (data.value.pending_users > 0) data.value.pending_users--
}

onMounted(async () => {
  await Promise.all([loadStats(), loadPending()])
  loading.value = false
})
</script>
