<template>
  <div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
      <div class="bg-white rounded-2xl border border-primary/5 p-6 shadow-sm">
        <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-2">Total contrats</p>
        <p class="font-black text-3xl text-on-surface">{{ contracts.length }}</p>
      </div>
      <div class="bg-white rounded-2xl border border-primary/5 p-6 shadow-sm">
        <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-2">En cours</p>
        <p class="font-black text-3xl text-primary">{{ activeCount }}</p>
      </div>
      <div class="bg-white rounded-2xl border border-primary/5 p-6 shadow-sm">
        <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-2">Complétés</p>
        <p class="font-black text-3xl text-green-600">{{ completedCount }}</p>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center py-16">
      <span class="material-symbols-outlined text-4xl text-primary/30 animate-spin">progress_activity</span>
    </div>
    <div v-else-if="contracts.length === 0" class="text-center py-20 bg-white rounded-2xl border border-primary/5">
      <span class="material-symbols-outlined text-5xl text-on-surface-variant/30 block mb-3">description</span>
      <p class="text-on-surface-variant font-medium">Aucun contrat enregistré</p>
    </div>
    <div v-else class="space-y-4">
      <div v-for="c in contracts" :key="c.id" class="bg-white rounded-2xl border border-primary/5 p-6 shadow-sm hover:shadow-md transition-all">
        <div class="flex justify-between items-start gap-4">
          <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
              <span :class="statusClass(c.status)" class="text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full">{{ statusLabel(c.status) }}</span>
              <span class="text-xs text-on-surface-variant">Contrat #{{ c.id }}</span>
            </div>
            <h3 class="font-bold text-lg text-on-surface mb-1">{{ c.mission?.title || '—' }}</h3>
            <p class="text-sm text-on-surface-variant">
              {{ role === 'freelancer' ? 'Client' : 'Freelancer' }} :
              <span class="font-semibold">{{ role === 'freelancer' ? c.client?.name : c.freelancer?.name || '—' }}</span>
            </p>
          </div>
          <div class="text-right flex-shrink-0">
            <p class="font-black text-2xl text-primary">{{ Number(c.amount).toLocaleString('fr-FR') }}</p>
            <p class="text-[10px] text-primary font-bold">MAD</p>
          </div>
        </div>
        <div v-if="c.status === 'active' && role === 'client'" class="mt-4 flex gap-3 justify-end">
          <button @click="complete(c)" class="px-5 py-2 bg-primary text-white text-xs font-bold rounded-full hover:brightness-110 transition-all flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">check_circle</span>Valider la livraison
          </button>
          <button @click="refund(c)" class="px-5 py-2 bg-secondary/10 text-secondary text-xs font-bold rounded-full hover:bg-secondary hover:text-white transition-all flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">undo</span>Remboursement
          </button>
        </div>
        <p class="text-xs text-on-surface-variant mt-3">{{ formatDate(c.created_at) }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/api/axios'

const props     = defineProps({ role: { type: String, default: 'freelancer' } })
const contracts = ref([])
const loading   = ref(false)

const activeCount    = computed(() => contracts.value.filter(c => c.status === 'active').length)
const completedCount = computed(() => contracts.value.filter(c => c.status === 'completed').length)
const endpoint       = computed(() => props.role === 'freelancer' ? '/contracts/freelancer' : '/contracts/client')

const load = async () => {
  loading.value = true
  try {
    const res = await api.get(endpoint.value)
    contracts.value = res.data
  } catch {
    contracts.value = []
  } finally {
    loading.value = false
  }
}

const complete = async (c) => {
  try {
    await api.post(`/contracts/${c.id}/complete`, {})
    c.status = 'completed'
  } catch {
    alert('Erreur')
  }
}

const refund = async (c) => {
  if (!confirm('Confirmer la demande de remboursement ?')) return
  try {
    await api.post(`/contracts/${c.id}/refund`, {})
    c.status = 'refunded'
  } catch {
    alert('Erreur')
  }
}

const statusClass = (s) => ({ active: 'bg-primary/10 text-primary', completed: 'bg-green-100 text-green-700', refunded: 'bg-yellow-100 text-yellow-700' }[s] || 'bg-surface-container text-on-surface-variant')
const statusLabel = (s) => ({ active: 'En cours', completed: 'Complété', refunded: 'Remboursé' }[s] || s)
const formatDate  = (d) => d ? new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' }) : '—'

onMounted(load)
</script>
