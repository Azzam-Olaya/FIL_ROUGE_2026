<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h2 class="text-2xl font-black text-on-surface">Gestion des Litiges</h2>
      <div class="flex gap-2">
        <span class="px-3 py-1 bg-amber-100 text-amber-700 text-[10px] font-black uppercase rounded-full">
            {{ openCount }} Ouverts
        </span>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center py-12">
      <span class="material-symbols-outlined text-4xl text-primary animate-spin">progress_activity</span>
    </div>

    <div v-else-if="disputes.length === 0" class="bg-white rounded-3xl p-12 text-center border border-primary/5">
      <span class="material-symbols-outlined text-5xl text-on-surface-variant/20 block mb-4">gavel</span>
      <p class="text-on-surface-variant font-medium">Aucun litige enregistré</p>
    </div>

    <div v-else class="grid grid-cols-1 gap-4">
      <div v-for="d in disputes" :key="d.id" class="bg-white rounded-3xl border border-primary/5 p-6 shadow-sm hover:shadow-md transition-all">
        <div class="flex justify-between items-start mb-4">
          <div class="flex items-center gap-3">
             <span :class="statusClass(d.status)" class="text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full">
                {{ d.status === 'open' ? 'En attente' : 'RÉSOLU' }}
             </span>
             <span class="text-xs text-on-surface-variant">Litige #{{ d.id }}</span>
          </div>
          <p class="text-xs text-on-surface-variant">{{ formatDate(d.created_at) }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-4">
            <div>
              <h3 class="font-bold text-lg text-on-surface mb-1">{{ d.title }}</h3>
              <p class="text-sm text-on-surface-variant italic bg-surface-container p-4 rounded-2xl border-l-4 border-red-500">
                "{{ d.reason }}"
              </p>
              <p class="text-[10px] font-bold text-primary mt-2">OUVERT PAR : {{ d.user?.name }} ({{ d.user?.id === d.contract?.client_id ? 'Client' : 'Freelancer' }})</p>
            </div>

            <div class="p-4 rounded-2xl bg-primary/5 border border-primary/10">
                <p class="text-[10px] font-black uppercase tracking-widest text-primary mb-2">Détails du contrat</p>
                <p class="text-sm font-bold">{{ d.contract?.mission?.title }}</p>
                <div class="flex justify-between mt-2">
                    <span class="text-xs">Client: <b>{{ d.contract?.client?.name }}</b></span>
                    <span class="text-xs">Freelancer: <b>{{ d.contract?.freelancer?.name }}</b></span>
                </div>
                <p class="text-sm font-black text-primary mt-2">{{ Number(d.contract?.amount).toLocaleString() }} MAD</p>
            </div>
          </div>

          <div class="flex flex-col justify-center gap-3">
            <div v-if="d.status === 'open'" class="space-y-3">
                <p class="text-xs font-bold text-on-surface">Décision de l'administrateur :</p>
                <textarea v-model="resolutionFeedback[d.id]" placeholder="Justification de la décision..." class="w-full p-4 bg-surface-container rounded-2xl text-sm border-0 focus:ring-2 focus:ring-primary"></textarea>
                
                <div class="grid grid-cols-3 gap-2">
                    <button @click="resolve(d, 'refund')" class="py-2 bg-red-600 text-white text-[10px] font-black uppercase rounded-xl hover:brightness-110">Rembourser</button>
                    <button @click="resolve(d, 'release')" class="py-2 bg-green-600 text-white text-[10px] font-black uppercase rounded-xl hover:brightness-110">Libérer</button>
                    <button @click="resolve(d, 'dismiss')" class="py-2 bg-gray-600 text-white text-[10px] font-black uppercase rounded-xl hover:brightness-110">Ignorer</button>
                </div>
            </div>
            <div v-else class="p-4 bg-green-50 rounded-2xl border border-green-100">
                <p class="text-[10px] font-black uppercase text-green-700 mb-1">RÉSOLUTION : {{ d.resolution_type }}</p>
                <p class="text-xs italic text-on-surface-variant">{{ d.admin_feedback }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/api/axios'

const disputes = ref([])
const loading = ref(false)
const resolutionFeedback = ref({})

const openCount = computed(() => disputes.value.filter(d => d.status === 'open').length)

const load = async () => {
    loading.value = true
    try {
        const res = await api.get('/admin/disputes')
        disputes.value = res.data
    } catch (e) {
        console.error(e)
    } finally {
        loading.value = false
    }
}

const resolve = async (d, type) => {
    const feedback = resolutionFeedback.value[d.id]
    if (!feedback) {
        alert('Veuillez fournir un feedback pour justifier votre décision.')
        return
    }

    try {
        await api.post(`/admin/disputes/${d.id}/resolve`, {
            resolution_type: type,
            admin_feedback: feedback
        })
        load()
        alert('Litige résolu.')
    } catch (e) {
        alert(e.response?.data?.message || 'Erreur lors de la résolution.')
    }
}

const statusClass = (s) => s === 'open' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700'
const formatDate = (d) => new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })

onMounted(load)
</script>
