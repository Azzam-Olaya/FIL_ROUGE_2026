<template>
  <div class="space-y-6">
    <div v-if="loading" class="flex justify-center py-16">
      <span class="material-symbols-outlined text-4xl text-primary/30 animate-spin">progress_activity</span>
    </div>

    <div v-else-if="!reports.length" class="text-center py-20 bg-white rounded-[2rem] border border-primary/5">
      <span class="material-symbols-outlined text-5xl text-gray-300 block mb-3">flag</span>
      <p class="text-gray-400 font-medium">Aucun signalement en attente.</p>
    </div>

    <div v-else class="grid grid-cols-1 gap-4">
      <div v-for="r in reports" :key="r.id"
        class="bg-white rounded-[2rem] border border-primary/5 p-6 shadow-sm flex flex-col md:flex-row md:items-center gap-4">

        <!-- Badge count -->
        <div class="flex-shrink-0 w-14 h-14 rounded-2xl flex flex-col items-center justify-center shadow-inner"
          :class="r.report_count >= 10 ? 'bg-red-100' : r.report_count >= 5 ? 'bg-orange-100' : 'bg-yellow-50'">
          <span class="font-black text-xl" :class="r.report_count >= 10 ? 'text-red-600' : r.report_count >= 5 ? 'text-orange-500' : 'text-yellow-600'">
            {{ r.report_count }}
          </span>
          <span class="text-[8px] font-bold uppercase tracking-wider text-on-surface-variant">signaux</span>
        </div>

        <!-- Info -->
        <div class="flex-1 min-w-0">
          <div class="flex flex-wrap items-center gap-2 mb-1">
            <span class="font-bold text-on-surface">{{ r.reported?.name }}</span>
            <span class="text-[10px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-full"
              :class="r.status === 'banned' ? 'bg-red-100 text-red-600' : r.status === 'dismissed' ? 'bg-gray-100 text-gray-500' : 'bg-yellow-100 text-yellow-700'">
              {{ r.status === 'banned' ? 'Banni' : r.status === 'dismissed' ? 'Ignoré' : 'En attente' }}
            </span>
            <span v-if="r.report_count >= 10" class="text-[10px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-full bg-red-500 text-white animate-pulse">
              Auto-banni
            </span>
          </div>
          <p class="text-sm text-on-surface-variant">
            Signalé par <span class="font-semibold text-on-surface">{{ r.reporter?.name }}</span>
            · <span class="font-medium text-primary">{{ r.reason }}</span>
            · {{ r.created_at }}
          </p>
          <p v-if="r.details" class="text-xs text-on-surface-variant mt-1 italic">« {{ r.details }} »</p>
        </div>

        <!-- Actions -->
        <div v-if="r.status === 'pending'" class="flex gap-2 flex-shrink-0">
          <button @click="dismiss(r.id)"
            class="px-4 py-2 rounded-full border border-primary/20 text-on-surface-variant font-bold text-xs hover:bg-primary/5 transition-all">
            Ignorer
          </button>
          <button @click="ban(r.id)"
            class="px-4 py-2 rounded-full bg-red-500 text-white font-bold text-xs hover:scale-105 active:scale-95 transition-all shadow-lg shadow-red-500/20">
            Bannir
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api/axios'

const reports = ref([])
const loading = ref(false)

const load = async () => {
  loading.value = true
  try {
    const res = await api.get('/admin/reports')
    reports.value = res.data
  } catch { reports.value = [] }
  finally { loading.value = false }
}

const dismiss = async (id) => {
  await api.post(`/admin/reports/${id}/dismiss`)
  const r = reports.value.find(r => r.id === id)
  if (r) r.status = 'dismissed'
}

const ban = async (id) => {
  await api.post(`/admin/reports/${id}/ban`)
  reports.value = reports.value.map(r =>
    r.reported?.id === reports.value.find(x => x.id === id)?.reported?.id
      ? { ...r, status: 'banned' }
      : r
  )
}

onMounted(load)
</script>
