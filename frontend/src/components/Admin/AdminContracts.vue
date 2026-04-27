<template>
  <div class="space-y-8">

    <!-- KPI statuts -->
    <section class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">
      <div v-for="s in statusCards" :key="s.label"
        class="bg-white rounded-2xl p-5 border border-primary/5 shadow-sm flex flex-col gap-2">
        <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400">{{ s.label }}</span>
        <span class="text-3xl font-black" :class="s.color">{{ s.value }}</span>
        <span class="text-xs text-gray-400 font-medium">contrats</span>
      </div>
    </section>

    <!-- Volumes financiers -->
    <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white rounded-2xl p-6 border border-primary/5 shadow-sm">
        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Volume total engagé</p>
        <p class="text-3xl font-black text-gray-800">{{ fmt(data.volume_total) }} <span class="text-sm font-bold text-gray-400">MAD</span></p>
      </div>
      <div class="bg-white rounded-2xl p-6 border border-primary/5 shadow-sm">
        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Volume complété (payé)</p>
        <p class="text-3xl font-black text-green-600">{{ fmt(data.volume_completed) }} <span class="text-sm font-bold text-gray-400">MAD</span></p>
      </div>
      <div class="bg-white rounded-2xl p-6 border border-primary/5 shadow-sm">
        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">En escrow (bloqué)</p>
        <p class="text-3xl font-black text-orange-500">{{ fmt(data.volume_escrow) }} <span class="text-sm font-bold text-gray-400">MAD</span></p>
      </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

      <!-- Graphique contrats par semaine -->
      <section class="bg-white p-8 rounded-2xl border border-primary/5 shadow-sm">
        <h4 class="font-bold text-lg text-gray-800 mb-1">Contrats créés — 4 dernières semaines</h4>
        <p class="text-sm text-gray-400 mb-6">Nombre de nouveaux contrats par semaine</p>
        <div v-if="loading" class="h-40 flex items-center justify-center">
          <span class="material-symbols-outlined animate-spin text-3xl text-gray-300">progress_activity</span>
        </div>
        <div v-else class="h-40 flex items-end gap-4 border-b border-gray-100 pb-2">
          <div v-for="w in data.weekly_contracts" :key="w.label" class="flex-1 flex flex-col items-center gap-2 group">
            <span class="text-xs font-bold text-primary opacity-0 group-hover:opacity-100 transition-opacity">{{ w.count }}</span>
            <div class="w-full rounded-t-xl transition-all duration-700 hover:bg-primary cursor-pointer"
              :style="{ height: maxWeekly > 0 ? Math.max((w.count / maxWeekly * 100), 4) + '%' : '4px' }"
              :class="w.count > 0 ? 'bg-primary/30 hover:bg-primary' : 'bg-gray-100'"></div>
            <span class="text-[10px] font-bold text-gray-400 uppercase">{{ w.label }}</span>
          </div>
        </div>
      </section>

      <!-- Top freelancers -->
      <section class="bg-white p-8 rounded-2xl border border-primary/5 shadow-sm">
        <h4 class="font-bold text-lg text-gray-800 mb-1">Top Freelancers</h4>
        <p class="text-sm text-gray-400 mb-6">Par nombre de contrats complétés</p>
        <div v-if="loading" class="h-40 flex items-center justify-center">
          <span class="material-symbols-outlined animate-spin text-3xl text-gray-300">progress_activity</span>
        </div>
        <div v-else class="space-y-3">
          <div v-if="!data.top_freelancers?.length" class="text-center py-8 text-gray-400 text-sm italic">
            Aucun contrat complété pour l'instant.
          </div>
          <div v-for="(f, i) in data.top_freelancers" :key="f.name"
            class="flex items-center gap-4 p-3 rounded-xl hover:bg-gray-50 transition-colors">
            <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-black"
              :class="i === 0 ? 'bg-yellow-100 text-yellow-600' : i === 1 ? 'bg-gray-100 text-gray-500' : 'bg-orange-50 text-orange-400'">
              {{ i + 1 }}
            </span>
            <div class="flex-1">
              <p class="text-sm font-bold text-gray-800">{{ f.name }}</p>
              <div class="h-1.5 bg-gray-100 rounded-full mt-1 overflow-hidden">
                <div class="h-full bg-primary rounded-full transition-all duration-700"
                  :style="{ width: maxCompleted > 0 ? (f.count / maxCompleted * 100) + '%' : '0%' }"></div>
              </div>
            </div>
            <span class="text-sm font-black text-primary">{{ f.count }}</span>
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

const statusCards = computed(() => [
  { label: 'En attente',  value: data.value.contracts_pending   ?? 0, color: 'text-yellow-500' },
  { label: 'Actifs',      value: data.value.contracts_active    ?? 0, color: 'text-blue-500'   },
  { label: 'Complétés',   value: data.value.contracts_completed ?? 0, color: 'text-green-600'  },
  { label: 'Remboursés',  value: data.value.contracts_refunded  ?? 0, color: 'text-orange-500' },
  { label: 'Annulés',     value: data.value.contracts_cancelled ?? 0, color: 'text-red-500'    },
])

const maxWeekly   = computed(() => Math.max(...(data.value.weekly_contracts ?? []).map(w => w.count), 1))
const maxCompleted = computed(() => Math.max(...(data.value.top_freelancers ?? []).map(f => f.count), 1))

onMounted(async () => {
  const res = await api.get('/admin/stats')
  data.value = res.data
  loading.value = false
})
</script>
