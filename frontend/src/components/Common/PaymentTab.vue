<template>
  <div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
      <div class="bg-white rounded-2xl border border-primary/5 p-6 shadow-sm">
        <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-2">Total {{ role === 'freelancer' ? 'Gagné' : 'Dépensé' }}</p>
        <p class="font-black text-3xl text-on-surface">{{ totalAmount.toLocaleString('fr-FR') }} <span class="text-sm text-primary font-bold">MAD</span></p>
      </div>
      <div class="bg-white rounded-2xl border border-primary/5 p-6 shadow-sm">
        <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-2">Contrats</p>
        <p class="font-black text-3xl text-on-surface">{{ payments.length }}</p>
      </div>
      <div class="bg-white rounded-2xl border border-primary/5 p-6 shadow-sm">
        <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-2">Complétés</p>
        <p class="font-black text-3xl text-primary">{{ completedCount }}</p>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center py-16">
      <span class="material-symbols-outlined text-4xl text-primary/30 animate-spin">progress_activity</span>
    </div>
    <div v-else-if="payments.length === 0" class="text-center py-20 bg-white rounded-2xl border border-primary/5">
      <span class="material-symbols-outlined text-5xl text-on-surface-variant/30 block mb-3">receipt_long</span>
      <p class="text-on-surface-variant font-medium">Aucun paiement enregistré</p>
    </div>
    <div v-else class="bg-white rounded-2xl border border-primary/5 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-primary/5 bg-surface-container/40">
              <th class="text-left px-6 py-4 text-[10px] font-black uppercase tracking-widest text-on-surface-variant whitespace-nowrap">Type</th>
              <th class="text-left px-6 py-4 text-[10px] font-black uppercase tracking-widest text-on-surface-variant whitespace-nowrap">Description</th>
              <th class="text-left px-6 py-4 text-[10px] font-black uppercase tracking-widest text-on-surface-variant whitespace-nowrap">{{ role === 'freelancer' ? 'Partie' : 'Tiers' }}</th>
              <th class="text-left px-6 py-4 text-[10px] font-black uppercase tracking-widest text-on-surface-variant whitespace-nowrap">Montant</th>
              <th class="text-left px-6 py-4 text-[10px] font-black uppercase tracking-widest text-on-surface-variant whitespace-nowrap">Statut</th>
              <th class="text-left px-6 py-4 text-[10px] font-black uppercase tracking-widest text-on-surface-variant whitespace-nowrap">Date</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-primary/5">
            <tr v-for="p in payments" :key="p.id" class="hover:bg-surface-container/20 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center gap-2">
                  <span class="material-symbols-outlined text-sm" :class="typeIcon(p.type).color">{{ typeIcon(p.type).icon }}</span>
                  <span class="text-[10px] font-black uppercase tracking-tighter opacity-60">{{ typeLabel(p.type) }}</span>
                </div>
              </td>
              <td class="px-6 py-4 font-semibold text-on-surface whitespace-nowrap">{{ p.title || '—' }}</td>
              <td class="px-6 py-4 text-on-surface-variant whitespace-nowrap">{{ p.party || '—' }}</td>
              <td class="px-6 py-4 font-black whitespace-nowrap" :class="p.type === 'deposit' ? 'text-green-600' : 'text-primary'">
                {{ p.type === 'deposit' ? '+' : '-' }} {{ Number(p.amount).toLocaleString('fr-FR') }} DH
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="statusClass(p.status)" class="text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full">
                  {{ statusLabel(p.status) }}
                </span>
              </td>
              <td class="px-6 py-4 text-on-surface-variant text-xs whitespace-nowrap">{{ formatDate(p.date) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/api/axios'

const props    = defineProps({ role: { type: String, default: 'freelancer' } })
const payments = ref([])
const loading  = ref(false)

const totalAmount    = computed(() => payments.value.reduce((s, p) => s + Number(p.amount || 0), 0))
const completedCount = computed(() => payments.value.filter(p => p.status === 'completed').length)
const endpoint       = computed(() => props.role === 'freelancer' ? '/freelancer/payments' : '/client/payments')

const load = async () => {
  loading.value = true
  try {
    const res = await api.get(endpoint.value)
    payments.value = res.data
  } catch {
    payments.value = props.role === 'freelancer'
      ? [{ id: 1, mission: { title: 'Rénovation Salon Riadh', client: { name: 'Mehdi El Fassi' } }, amount: 8500, status: 'completed', created_at: new Date().toISOString() }]
      : [{ id: 1, mission: { title: 'Rénovation Salon Riadh' }, freelancer: { name: 'Yasmine B.' }, amount: 8500, status: 'completed', created_at: new Date().toISOString() }]
  } finally {
    loading.value = false
  }
}

const statusClass = (s) => ({
  completed: 'bg-green-100 text-green-700',
  pending: 'bg-yellow-100 text-yellow-700',
  active: 'bg-primary/10 text-primary',
  pending_freelancer: 'bg-orange-100 text-orange-700'
}[s] || 'bg-surface-container text-on-surface-variant')

const statusLabel = (s) => ({
  completed: 'Succès',
  pending: 'En attente',
  active: 'Bloqué Escrow',
  pending_freelancer: 'Proposition'
}[s] || s)

const typeIcon = (t) => ({
  deposit: { icon: 'add_circle', color: 'text-green-500' },
  withdrawal: { icon: 'remove_circle', color: 'text-red-500' },
  contract_payment: { icon: 'handshake', color: 'text-blue-500' }
}[t] || { icon: 'receipt_long', color: 'text-gray-400' })

const typeLabel = (t) => ({
  deposit: 'Dépôt',
  withdrawal: 'Retrait',
  contract_payment: 'Contrat'
}[t] || t)

const formatDate  = (d) => d ? new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' }) : '—'

onMounted(load)
</script>
