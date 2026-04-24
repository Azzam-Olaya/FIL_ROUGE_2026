<template>
  <div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
      <div class="bg-white rounded-2xl border border-primary/5 p-6 shadow-sm"><p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-2">Total {{ role==='freelancer'?'Gagné':'Dépensé' }}</p><p class="font-black text-3xl text-on-surface">{{ total.toLocaleString('fr-FR') }} <span class="text-sm text-primary font-bold">MAD</span></p></div>
      <div class="bg-white rounded-2xl border border-primary/5 p-6 shadow-sm"><p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-2">Contrats</p><p class="font-black text-3xl text-on-surface">{{ payments.length }}</p></div>
      <div class="bg-white rounded-2xl border border-primary/5 p-6 shadow-sm"><p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-2">Complétés</p><p class="font-black text-3xl text-primary">{{ payments.filter(p=>p.status==='completed').length }}</p></div>
    </div>
    <div v-if="loading" class="flex justify-center py-16"><span class="material-symbols-outlined text-4xl text-primary/30 animate-spin">progress_activity</span></div>
    <div v-else-if="!payments.length" class="text-center py-20 bg-white rounded-2xl border border-primary/5">
      <span class="material-symbols-outlined text-5xl text-on-surface-variant/30 block mb-3">receipt_long</span>
      <p class="text-on-surface-variant font-medium">Aucun paiement enregistré</p>
    </div>
    <div v-else class="bg-white rounded-2xl border border-primary/5 shadow-sm overflow-hidden">
      <table class="w-full text-sm">
        <thead><tr class="border-b border-primary/5 bg-surface-container/40">
          <th class="text-left px-6 py-4 text-[10px] font-black uppercase tracking-widest text-on-surface-variant">Mission</th>
          <th class="text-left px-6 py-4 text-[10px] font-black uppercase tracking-widest text-on-surface-variant">{{ role==='freelancer'?'Client':'Freelancer' }}</th>
          <th class="text-left px-6 py-4 text-[10px] font-black uppercase tracking-widest text-on-surface-variant">Montant</th>
          <th class="text-left px-6 py-4 text-[10px] font-black uppercase tracking-widest text-on-surface-variant">Statut</th>
          <th class="text-left px-6 py-4 text-[10px] font-black uppercase tracking-widest text-on-surface-variant">Date</th>
        </tr></thead>
        <tbody class="divide-y divide-primary/5">
          <tr v-for="p in payments" :key="p.id" class="hover:bg-surface-container/20 transition-colors">
            <td class="px-6 py-4 font-semibold text-on-surface">{{ p.mission?.title||'—' }}</td>
            <td class="px-6 py-4 text-on-surface-variant">{{ role==='freelancer'?p.mission?.client?.name:p.freelancer?.name||'—' }}</td>
            <td class="px-6 py-4 font-black text-primary">{{ Number(p.amount).toLocaleString('fr-FR') }} MAD</td>
            <td class="px-6 py-4"><span :class="sc(p.status)" class="text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full">{{ sl(p.status) }}</span></td>
            <td class="px-6 py-4 text-on-surface-variant text-xs">{{ fd(p.created_at) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
const props = defineProps({ role: { type: String, default: 'freelancer' } })
const payments=ref([]),loading=ref(false)
const h = computed(()=>({Authorization:`Bearer ${localStorage.getItem('token')}`}))
const total = computed(()=>payments.value.reduce((s,p)=>s+Number(p.amount||0),0))
const ep = computed(()=>props.role==='freelancer'?'http://localhost:8000/api/freelancer/payments':'http://localhost:8000/api/client/payments')
const load = async()=>{ loading.value=true; try { const r=await axios.get(ep.value,{headers:h.value}); payments.value=r.data } catch { payments.value=[] } finally { loading.value=false } }
const sc=(s)=>({completed:'bg-green-100 text-green-700',pending:'bg-yellow-100 text-yellow-700',active:'bg-primary/10 text-primary'}[s]||'bg-surface-container text-on-surface-variant')
const sl=(s)=>({completed:'Complété',pending:'En attente',active:'En cours'}[s]||s)
const fd=(d)=>d?new Date(d).toLocaleDateString('fr-FR',{day:'2-digit',month:'short',year:'numeric'}):'—'
onMounted(load)
</script>
