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

    <!-- Row 1 : Inscriptions + Répartition utilisateurs -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Bar chart : inscriptions par semaine -->
      <section class="lg:col-span-2 bg-white p-8 rounded-2xl border border-primary/5 shadow-sm">
        <h4 class="font-bold text-lg text-gray-800 mb-1">Inscriptions — 4 dernières semaines</h4>
        <p class="text-sm text-gray-400 mb-6">Nouveaux clients & freelancers</p>
        <div class="relative h-52">
          <canvas ref="barRef"></canvas>
        </div>
      </section>

      <!-- Doughnut : répartition utilisateurs -->
      <section class="bg-white p-8 rounded-2xl border border-primary/5 shadow-sm flex flex-col">
        <h4 class="font-bold text-lg text-gray-800 mb-1">Utilisateurs</h4>
        <p class="text-sm text-gray-400 mb-4">Répartition par rôle</p>
        <div class="relative h-40 flex-1">
          <canvas ref="doughnutRef"></canvas>
        </div>
        <div class="mt-4 grid grid-cols-3 gap-2 text-center">
          <div class="p-2 bg-primary/5 rounded-xl">
            <p class="text-xl font-black text-primary">{{ data.total_clients ?? 0 }}</p>
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Clients</p>
          </div>
          <div class="p-2 bg-red-50 rounded-xl">
            <p class="text-xl font-black text-red-500">{{ data.total_freelancers ?? 0 }}</p>
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Freelancers</p>
          </div>
          <div class="p-2 bg-yellow-50 rounded-xl">
            <p class="text-xl font-black text-yellow-600">{{ data.pending_users ?? 0 }}</p>
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">En attente</p>
          </div>
        </div>
      </section>
    </div>

    <!-- Row 2 : Contrats par statut + Line contrats/semaine -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Doughnut : statuts contrats -->
      <section class="bg-white p-8 rounded-2xl border border-primary/5 shadow-sm flex flex-col">
        <h4 class="font-bold text-lg text-gray-800 mb-1">Contrats</h4>
        <p class="text-sm text-gray-400 mb-4">Par statut</p>
        <div class="relative h-40">
          <canvas ref="contractDoughnutRef"></canvas>
        </div>
        <div class="mt-4 space-y-2 text-xs font-bold">
          <div v-for="s in contractStatuses" :key="s.label" class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="w-3 h-3 rounded-full" :style="{ background: s.color }"></span>
              <span class="text-gray-500 uppercase tracking-widest">{{ s.label }}</span>
            </div>
            <span class="text-gray-800">{{ s.value }}</span>
          </div>
        </div>
      </section>

      <!-- Line chart : contrats par semaine -->
      <section class="lg:col-span-2 bg-white p-8 rounded-2xl border border-primary/5 shadow-sm">
        <h4 class="font-bold text-lg text-gray-800 mb-1">Contrats — 4 dernières semaines</h4>
        <p class="text-sm text-gray-400 mb-6">Volume de contrats créés</p>
        <div class="relative h-52">
          <canvas ref="lineRef"></canvas>
        </div>
      </section>
    </div>

    <!-- Row 3 : Dépôts + Top freelancers -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Bar chart : dépôts par semaine -->
      <section class="lg:col-span-2 bg-white p-8 rounded-2xl border border-primary/5 shadow-sm">
        <h4 class="font-bold text-lg text-gray-800 mb-1">Dépôts — 4 dernières semaines</h4>
        <p class="text-sm text-gray-400 mb-6">Montants déposés (MAD)</p>
        <div class="relative h-52">
          <canvas ref="depositsRef"></canvas>
        </div>
      </section>

      <!-- Top freelancers -->
      <section class="bg-white p-8 rounded-2xl border border-primary/5 shadow-sm">
        <h4 class="font-bold text-lg text-gray-800 mb-1">Top Freelancers</h4>
        <p class="text-sm text-gray-400 mb-4">Par missions complétées</p>
        <div class="space-y-3">
          <div v-if="!data.top_freelancers?.length" class="text-center py-8 text-gray-300 text-sm">Aucune donnée</div>
          <div v-for="(f, i) in data.top_freelancers" :key="f.name" class="flex items-center gap-3">
            <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-black text-white flex-shrink-0"
              :class="i === 0 ? 'bg-yellow-400' : i === 1 ? 'bg-gray-400' : i === 2 ? 'bg-orange-400' : 'bg-primary/20 text-primary'">
              {{ i + 1 }}
            </span>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-bold text-gray-800 truncate">{{ f.name }}</p>
              <div class="w-full bg-gray-100 rounded-full h-1.5 mt-1">
                <div class="bg-primary h-1.5 rounded-full transition-all duration-700"
                  :style="{ width: maxTop > 0 ? (f.count / maxTop * 100) + '%' : '0%' }"></div>
              </div>
            </div>
            <span class="text-sm font-black text-primary flex-shrink-0">{{ f.count }}</span>
          </div>
        </div>
      </section>
    </div>

    <!-- Modération rapide -->
    <section class="bg-primary/5 p-7 rounded-2xl border border-primary/10">
      <h4 class="font-bold text-lg mb-5">Modération Rapide</h4>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
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
            <button @click="approveUser(user.id)" class="p-1 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 transition-colors">
              <span class="material-symbols-outlined text-sm">check_circle</span>
            </button>
            <button @click="rejectUser(user.id)" class="p-1 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition-colors">
              <span class="material-symbols-outlined text-sm">cancel</span>
            </button>
          </div>
        </div>
        <div v-if="!loading && pendingUsers.length === 0"
          class="col-span-full text-center py-10 text-gray-400 text-sm font-medium italic">
          Aucun utilisateur en attente ✨
        </div>
      </div>
    </section>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { Chart, BarController, LineController, DoughnutController, BarElement, LineElement, ArcElement, PointElement, CategoryScale, LinearScale, Tooltip, Legend, Filler } from 'chart.js'
import api from '@/api/axios'

Chart.register(BarController, LineController, DoughnutController, BarElement, LineElement, ArcElement, PointElement, CategoryScale, LinearScale, Tooltip, Legend, Filler)

const data         = ref({})
const pendingUsers = ref([])
const loading      = ref(true)

// canvas refs
const barRef              = ref(null)
const doughnutRef         = ref(null)
const contractDoughnutRef = ref(null)
const lineRef             = ref(null)
const depositsRef         = ref(null)

const PRIMARY   = '#006233'
const SECONDARY = '#8B5E3C'
const COLORS    = ['#006233', '#8B5E3C', '#F59E0B', '#EF4444', '#6B7280']

const kpis = computed(() => {
  const d = data.value
  return [
    { label: 'Utilisateurs total',  value: d.total_users ?? 0,                                    trend: d.users_trend ?? 0,     icon: 'people'    },
    { label: 'Missions publiées',   value: d.total_missions ?? 0,                                 trend: d.missions_trend ?? 0,  icon: 'work'      },
    { label: 'Contrats signés',     value: d.total_contracts ?? 0,                                trend: d.contracts_trend ?? 0, icon: 'handshake' },
    { label: 'Revenus (MAD)',       value: Number(d.total_revenue ?? 0).toLocaleString('fr-FR'),  trend: d.revenue_trend ?? 0,   icon: 'payments'  },
  ]
})

const contractStatuses = computed(() => {
  const d = data.value
  return [
    { label: 'Actifs',     value: d.contracts_active    ?? 0, color: PRIMARY   },
    { label: 'En attente', value: d.contracts_pending   ?? 0, color: '#F59E0B' },
    { label: 'Complétés',  value: d.contracts_completed ?? 0, color: '#10B981' },
    { label: 'Remboursés', value: d.contracts_refunded  ?? 0, color: SECONDARY },
    { label: 'Annulés',    value: d.contracts_cancelled ?? 0, color: '#EF4444' },
  ]
})

const maxTop = computed(() => Math.max(...(data.value.top_freelancers ?? []).map(f => f.count), 1))

const buildCharts = () => {
  const d = data.value
  const weeks = (d.weekly_registrations ?? []).map(w => w.label)

  // Bar — inscriptions
  new Chart(barRef.value, {
    type: 'bar',
    data: {
      labels: weeks,
      datasets: [{
        label: 'Inscriptions',
        data: (d.weekly_registrations ?? []).map(w => w.count),
        backgroundColor: PRIMARY + '33',
        borderColor: PRIMARY,
        borderWidth: 2,
        borderRadius: 8,
      }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
  })

  // Doughnut — utilisateurs
  new Chart(doughnutRef.value, {
    type: 'doughnut',
    data: {
      labels: ['Clients', 'Freelancers', 'En attente'],
      datasets: [{ data: [d.total_clients ?? 0, d.total_freelancers ?? 0, d.pending_users ?? 0], backgroundColor: [PRIMARY, '#EF4444', '#F59E0B'], borderWidth: 0 }]
    },
    options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } } } }
  })

  // Doughnut — contrats par statut
  const cs = contractStatuses.value
  new Chart(contractDoughnutRef.value, {
    type: 'doughnut',
    data: {
      labels: cs.map(s => s.label),
      datasets: [{ data: cs.map(s => s.value), backgroundColor: cs.map(s => s.color), borderWidth: 0 }]
    },
    options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { display: false } } }
  })

  // Line — contrats par semaine
  new Chart(lineRef.value, {
    type: 'line',
    data: {
      labels: (d.weekly_contracts ?? []).map(w => w.label),
      datasets: [{
        label: 'Contrats',
        data: (d.weekly_contracts ?? []).map(w => w.count),
        borderColor: PRIMARY,
        backgroundColor: PRIMARY + '15',
        borderWidth: 2,
        pointBackgroundColor: PRIMARY,
        pointRadius: 5,
        fill: true,
        tension: 0.4,
      }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
  })

  // Bar — dépôts par semaine
  new Chart(depositsRef.value, {
    type: 'bar',
    data: {
      labels: (d.weekly_deposits ?? []).map(w => w.label),
      datasets: [{
        label: 'Dépôts (MAD)',
        data: (d.weekly_deposits ?? []).map(w => w.amount),
        backgroundColor: SECONDARY + '33',
        borderColor: SECONDARY,
        borderWidth: 2,
        borderRadius: 8,
      }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
  })
}

const loadStats   = async () => { const res = await api.get('/admin/stats');         data.value = res.data }
const loadPending = async () => { const res = await api.get('/admin/pending-users'); pendingUsers.value = res.data }

const approveUser = async (id) => {
  await api.post(`/admin/users/${id}/approve`)
  pendingUsers.value = pendingUsers.value.filter(u => u.id !== id)
  if (data.value.pending_users > 0) data.value.pending_users--
}
const rejectUser = async (id) => {
  await api.post(`/admin/users/${id}/reject`)
  pendingUsers.value = pendingUsers.value.filter(u => u.id !== id)
  if (data.value.pending_users > 0) data.value.pending_users--
}

onMounted(async () => {
  await Promise.all([loadStats(), loadPending()])
  loading.value = false
  await nextTick()
  buildCharts()
})
</script>
