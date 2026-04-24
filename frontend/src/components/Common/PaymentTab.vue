<template>
  <div class="space-y-6">

    <!-- KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
      <div class="bg-white rounded-2xl border border-primary/5 p-6 shadow-sm flex flex-col gap-2">
        <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Total {{ role === 'freelancer' ? 'Gagné' : 'Dépensé' }}</p>
        <p class="font-black text-3xl text-on-surface">{{ totalAmount.toLocaleString('fr-FR') }} <span class="text-sm text-primary font-bold">MAD</span></p>
      </div>
      <div class="bg-white rounded-2xl border border-primary/5 p-6 shadow-sm flex flex-col gap-2">
        <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Contrats</p>
        <p class="font-black text-3xl text-on-surface">{{ payments.length }}</p>
      </div>
      <div class="bg-white rounded-2xl border border-primary/5 p-6 shadow-sm flex flex-col gap-2">
        <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Complétés</p>
        <p class="font-black text-3xl text-primary">{{ completedCount }}</p>
      </div>
    </div>

    <!-- Loader -->
    <div v-if="loading" class="flex justify-center py-16">
      <span class="material-symbols-outlined text-4xl text-primary/30 animate-spin">progress_activity</span>
    </div>

    <!-- Vide -->
    <div v-else-if="payments.length === 0" class="text-center py-20 bg-white rounded-2xl border border-primary/5">
      <span class="material-symbols-outlined text-5xl text-on-surface-variant/30 block mb-3">receipt_long</span>
      <p class="text-on-surface-variant font-medium">Aucun paiement enregistré</p>
    </div>

    <!-- Liste des paiements -->
    <div v-else class="bg-white rounded-2xl border border-primary/5 shadow-sm overflow-hidden">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-primary/5 bg-surface-container/40">
            <th class="text-left px-6 py-4 text-[10px] font-black uppercase tracking-widest text-on-surface-variant">Mission</th>
            <th class="text-left px-6 py-4 text-[10px] font-black uppercase tracking-widest text-on-surface-variant">{{ role === 'freelancer' ? 'Client' : 'Freelancer' }}</th>
            <th class="text-left px-6 py-4 text-[10px] font-black uppercase tracking-widest text-on-surface-variant">Montant</th>
            <th class="text-left px-6 py-4 text-[10px] font-black uppercase tracking-widest text-on-surface-variant">Statut</th>
            <th class="text-left px-6 py-4 text-[10px] font-black uppercase tracking-widest text-on-surface-variant">Date</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-primary/5">
          <tr v-for="p in payments" :key="p.id" class="hover:bg-surface-container/20 transition-colors">
            <td class="px-6 py-4 font-semibold text-on-surface">{{ p.mission?.title || '—' }}</td>
            <td class="px-6 py-4 text-on-surface-variant">{{ role === 'freelancer' ? p.mission?.client?.name : p.freelancer?.name || '—' }}</td>
            <td class="px-6 py-4 font-black text-primary">{{ Number(p.amount).toLocaleString('fr-FR') }} MAD</td>
            <td class="px-6 py-4">
              <span :class="statusClass(p.status)" class="text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full">
                {{ statusLabel(p.status) }}
              </span>
            </td>
            <td class="px-6 py-4 text-on-surface-variant text-xs">{{ formatDate(p.created_at) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  role: { type: String, default: 'freelancer' } // 'freelancer' | 'client'
})

const payments = ref([])
const loading  = ref(false)

const token   = () => localStorage.getItem('token')
const headers = computed(() => ({ Authorization: `Bearer ${token()}` }))

const totalAmount   = computed(() => payments.value.reduce((s, p) => s + Number(p.amount || 0), 0))
const completedCount = computed(() => payments.value.filter(p => p.status === 'completed').length)

const endpoint = computed(() =>
  props.role === 'freelancer'
    ? 'http://localhost:8000/api/freelancer/payments'
    : 'http://localhost:8000/api/client/payments'
)

const load = async () => {
  loading.value = true
  try {
    const res = await axios.get(endpoint.value, { headers: headers.value })
    payments.value = res.data
  } catch {
    payments.value = defaultPayments()
  } finally {
    loading.value = false
  }
}

const statusClass = (s) => ({
  completed: 'bg-green-100 text-green-700',
  pending:   'bg-yellow-100 text-yellow-700',
  active:    'bg-primary/10 text-primary',
}[s] || 'bg-surface-container text-on-surface-variant')

const statusLabel = (s) => ({
  completed: 'Complété',
  pending:   'En attente',
  active:    'En cours',
}[s] || s)

const formatDate = (d) => d ? new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' }) : '—'

const defaultPayments = () => props.role === 'freelancer'
  ? [
      { id: 1, mission: { title: 'Rénovation Salon Riadh', client: { name: 'Mehdi El Fassi' } }, amount: 8500, status: 'completed', created_at: new Date().toISOString() },
      { id: 2, mission: { title: 'Site web E-Commerce', client: { name: 'Sarah M.' } }, amount: 12000, status: 'active', created_at: new Date().toISOString() },
    ]
  : [
      { id: 1, mission: { title: 'Rénovation Salon Riadh' }, freelancer: { name: 'Yasmine B.' }, amount: 8500, status: 'completed', created_at: new Date().toISOString() },
      { id: 2, mission: { title: 'Identité Visuelle' }, freelancer: { name: 'Omar K.' }, amount: 5500, status: 'pending', created_at: new Date().toISOString() },
    ]

onMounted(load)
</script>
