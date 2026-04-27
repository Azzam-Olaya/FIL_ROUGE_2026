<template>
  <div class="space-y-8">

    <!-- KPI paiements -->
    <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white rounded-2xl p-6 border border-primary/5 shadow-sm">
        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Total dépôts (tous)</p>
        <p class="text-3xl font-black text-gray-800">{{ fmt(data.total_deposits) }} <span class="text-sm font-bold text-gray-400">MAD</span></p>
      </div>
      <div class="bg-white rounded-2xl p-6 border border-primary/5 shadow-sm">
        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Dépôts ce mois</p>
        <p class="text-3xl font-black text-primary">{{ fmt(data.deposits_this_month) }} <span class="text-sm font-bold text-gray-400">MAD</span></p>
        <div class="flex items-center gap-1 mt-2 text-xs font-bold"
          :class="(data.deposits_trend ?? 0) >= 0 ? 'text-green-600' : 'text-red-500'">
          <span class="material-symbols-outlined text-xs">{{ (data.deposits_trend ?? 0) >= 0 ? 'trending_up' : 'trending_down' }}</span>
          {{ (data.deposits_trend ?? 0) >= 0 ? '+' : '' }}{{ data.deposits_trend ?? 0 }}% vs mois dernier
        </div>
      </div>
      <div class="bg-white rounded-2xl p-6 border border-primary/5 shadow-sm">
        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Revenus plateforme (5%)</p>
        <p class="text-3xl font-black text-green-600">{{ fmt(data.total_revenue) }} <span class="text-sm font-bold text-gray-400">MAD</span></p>
      </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

      <!-- Graphique dépôts par semaine -->
      <section class="bg-white p-8 rounded-2xl border border-primary/5 shadow-sm">
        <h4 class="font-bold text-lg text-gray-800 mb-1">Dépôts — 4 dernières semaines</h4>
        <p class="text-sm text-gray-400 mb-6">Montants déposés par les clients (MAD)</p>
        <div v-if="loading" class="h-40 flex items-center justify-center">
          <span class="material-symbols-outlined animate-spin text-3xl text-gray-300">progress_activity</span>
        </div>
        <div v-else class="h-40 flex items-end gap-4 border-b border-gray-100 pb-2">
          <div v-for="w in data.weekly_deposits" :key="w.label" class="flex-1 flex flex-col items-center gap-2 group">
            <span class="text-xs font-bold text-primary opacity-0 group-hover:opacity-100 transition-opacity">{{ fmt(w.amount) }}</span>
            <div class="w-full rounded-t-xl transition-all duration-700 hover:bg-primary cursor-pointer"
              :style="{ height: maxDeposit > 0 ? Math.max((w.amount / maxDeposit * 100), 4) + '%' : '4px' }"
              :class="w.amount > 0 ? 'bg-primary/30 hover:bg-primary' : 'bg-gray-100'"></div>
            <span class="text-[10px] font-bold text-gray-400 uppercase">{{ w.label }}</span>
          </div>
        </div>
      </section>

      <!-- Transactions récentes -->
      <section class="bg-white p-8 rounded-2xl border border-primary/5 shadow-sm">
        <h4 class="font-bold text-lg text-gray-800 mb-1">Transactions récentes</h4>
        <p class="text-sm text-gray-400 mb-6">10 dernières transactions complétées</p>
        <div v-if="loading" class="h-40 flex items-center justify-center">
          <span class="material-symbols-outlined animate-spin text-3xl text-gray-300">progress_activity</span>
        </div>
        <div v-else class="space-y-2 max-h-64 overflow-y-auto">
          <div v-if="!data.recent_transactions?.length" class="text-center py-8 text-gray-400 text-sm italic">
            Aucune transaction pour l'instant.
          </div>
          <div v-for="t in data.recent_transactions" :key="t.date + t.user"
            class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition-colors">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-black text-xs">
                {{ t.user?.[0]?.toUpperCase() ?? '?' }}
              </div>
              <div>
                <p class="text-sm font-bold text-gray-800">{{ t.user ?? 'Inconnu' }}</p>
                <p class="text-[10px] font-bold uppercase tracking-widest"
                  :class="t.type === 'deposit' ? 'text-green-500' : 'text-blue-500'">
                  {{ t.type === 'deposit' ? 'Dépôt' : t.type }}
                </p>
              </div>
            </div>
            <div class="text-right">
              <p class="text-sm font-black text-gray-800">{{ fmt(t.amount) }} MAD</p>
              <p class="text-[10px] text-gray-400">{{ new Date(t.date).toLocaleDateString('fr-FR') }}</p>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/api/axios'

const data    = ref({})
const loading = ref(true)

const fmt = (v) => Number(v ?? 0).toLocaleString('fr-FR')

const maxDeposit = computed(() => Math.max(...(data.value.weekly_deposits ?? []).map(w => w.amount), 1))

onMounted(async () => {
  const res = await api.get('/admin/stats')
  data.value = res.data
  loading.value = false
})
</script>
