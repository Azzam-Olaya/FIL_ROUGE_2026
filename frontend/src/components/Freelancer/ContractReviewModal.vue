<template>
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[100] flex items-center justify-center p-4" @click.self="$emit('close')">
      <div class="bg-white rounded-[2.5rem] shadow-2xl max-w-lg w-full flex flex-col max-h-[90vh] animate-in overflow-hidden">
        
        <!-- Header -->
        <div class="p-8 pb-4 border-b border-primary/5">
          <div class="flex items-center justify-between">
            <h2 class="font-headline text-2xl font-bold text-on-surface">Proposition de Contrat</h2>
            <button @click="$emit('close')" class="w-10 h-10 rounded-full hover:bg-primary/10 transition-colors flex items-center justify-center">
              <span class="material-symbols-outlined">close</span>
            </button>
          </div>
          <p class="text-xs text-on-surface-variant font-medium mt-1">Client : <span class="text-primary font-bold">{{ contract.client?.name }}</span></p>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-8 space-y-6 custom-scrollbar">
          
          <!-- Summary Cards -->
          <div class="grid grid-cols-2 gap-4">
            <div class="bg-primary/5 p-4 rounded-2xl border border-primary/10">
              <p class="text-[9px] font-black uppercase tracking-widest text-primary mb-1">Budget Proposé</p>
              <p class="font-black text-xl text-on-surface">{{ Number(contract.amount).toLocaleString() }} DH</p>
            </div>
            <div class="bg-secondary/5 p-4 rounded-2xl border border-secondary/10">
              <p class="text-[9px] font-black uppercase tracking-widest text-secondary mb-1">Échéance</p>
              <p class="font-bold text-sm text-on-surface">{{ new Date(contract.deadline).toLocaleDateString('fr-FR') }}</p>
            </div>
          </div>

          <!-- Technologies -->
          <div>
            <label class="block text-[10px] font-black uppercase tracking-widest text-on-surface-variant mb-2">Technologies Demandées</label>
            <div class="flex flex-wrap gap-2">
              <span v-for="tech in contract.technologies.split(',')" :key="tech"
                class="bg-surface-container-high px-3 py-1 rounded-full text-[11px] font-bold text-on-surface">
                {{ tech.trim() }}
              </span>
            </div>
          </div>

          <!-- Specifications -->
          <div>
            <label class="block text-[10px] font-black uppercase tracking-widest text-on-surface-variant mb-2">Cahier des charges</label>
            <div class="bg-surface-container-low p-6 rounded-3xl border border-primary/5 text-sm text-on-surface-variant leading-relaxed">
              {{ contract.specifications }}
            </div>
          </div>

          <!-- Note on Escrow -->
          <div class="bg-green-50 p-5 rounded-3xl border border-green-100 flex gap-4">
            <span class="material-symbols-outlined text-green-600">shield_check</span>
            <p class="text-[11px] font-medium text-green-800 leading-relaxed">
              Ce client a déjà <span class="font-bold">provisionné les fonds</span>. 
              Dès que vous acceptez, l'argent est sécurisé par MorLancer et sera libéré à la validation de vos livrables.
            </p>
          </div>
        </div>

        <!-- Footer Actions -->
        <div class="p-8 bg-surface-container-low/50 flex gap-4">
          <button @click="reject" :disabled="loading"
            class="flex-1 py-4 px-6 rounded-full border border-red-200 text-red-600 font-bold text-sm hover:bg-red-50 transition-all flex items-center justify-center gap-2">
             <span class="material-symbols-outlined text-sm">close</span>
             Refuser
          </button>
          
          <button @click="accept" :disabled="loading"
            class="flex-2 py-4 px-8 rounded-full bg-primary text-white font-bold text-sm hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-3 shadow-xl shadow-primary/20">
            <span v-if="loading" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
            <span v-else class="flex items-center gap-2">
              Accepter & Commencer
              <span class="material-symbols-outlined text-sm">rocket_launch</span>
            </span>
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue'
import api from '@/api/axios'

const props = defineProps({
  show: Boolean,
  contract: Object
})

const emit = defineEmits(['close', 'updated'])
const loading = ref(false)

const accept = async () => {
  if (!confirm('Accepter ce contrat lancera officiellement le projet. Continuer ?')) return
  loading.value = true
  try {
    await api.post(`/contracts/${props.contract.id}/accept`)
    alert('Contrat accepté ! Retrouvez-le dans l’onglet Contrats.')
    emit('updated')
    emit('close')
  } catch (err) {
    alert(err.response?.data?.message || 'Erreur lors de l’acceptation.')
  } finally {
    loading.value = false
  }
}

const reject = async () => {
  if (!confirm('Voulez-vous vraiment refuser ce contrat ? Le client sera immédiatement remboursé.')) return
  loading.value = true
  try {
    await api.post(`/contracts/${props.contract.id}/reject`)
    alert('Contrat refusé.')
    emit('updated')
    emit('close')
  } catch (err) {
    alert(err.response?.data?.message || 'Erreur lors du refus.')
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
@keyframes slideIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
.animate-in { animation: slideIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0, 72, 36, 0.1); border-radius: 10px; }
.flex-2 { flex: 2; }
</style>
