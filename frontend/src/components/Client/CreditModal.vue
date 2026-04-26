<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="$emit('close')"></div>
    <div class="relative bg-surface rounded-[2rem] w-full max-w-md shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
      <div class="p-6 border-b border-primary/10 flex items-center justify-between bg-error/5">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-error/20 flex items-center justify-center">
             <span class="material-symbols-outlined text-error font-medium">toll</span>
          </div>
          <div>
            <h2 class="font-black text-xl text-on-surface">Créditer le compte (Test)</h2>
            <p class="text-xs font-bold text-on-surface-variant uppercase tracking-widest mt-1">Simulation de solde</p>
          </div>
        </div>
        <button @click="$emit('close')" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-black/5 transition-colors">
          <span class="material-symbols-outlined text-on-surface-variant">close</span>
        </button>
      </div>

      <div class="p-6 space-y-6 overflow-y-auto">
        <p class="text-sm text-on-surface-variant font-medium">
          Ajoutez des fonds virtuels à votre portefeuille pour tester le lancement de contrats.
        </p>

        <div class="space-y-4">
          <label class="block text-xs font-black uppercase tracking-widest text-on-surface-variant px-1" for="credit_amount">Montant (DH)</label>
          <div class="grid grid-cols-3 gap-3">
            <button v-for="amt in [100, 500, 1000]" :key="amt"
              @click="amount = amt"
              :class="amount === amt ? 'bg-error text-white scale-105 shadow-lg shadow-error/30' : 'bg-surface border border-error/20 text-on-surface hover:bg-error/5'"
              class="py-3 rounded-2xl font-black text-sm transition-all" type="button">
              {{ amt }} DH
            </button>
          </div>
          
          <div class="relative mt-4">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant font-bold">DH</span>
            <input id="credit_amount" v-model.number="amount" type="number" step="100"
              class="w-full bg-surface border-2 border-error/20 rounded-2xl py-4 pl-12 pr-4 text-lg font-black focus:outline-none focus:border-error focus:ring-4 focus:ring-error/10 transition-all"
              placeholder="Montant libre" />
          </div>
        </div>

        <button @click="submitCredit" :disabled="loading || amount <= 0"
          class="w-full py-4 rounded-full font-black text-sm transition-all shadow-xl shadow-error/30 flex justify-center items-center gap-2"
          :class="loading || amount <= 0 ? 'bg-surface-container text-on-surface/50 cursor-not-allowed' : 'bg-error text-white hover:scale-[1.02] active:scale-[0.98]'">
          <span v-if="loading" class="material-symbols-outlined animate-spin text-sm">progress_activity</span>
          <span v-else class="material-symbols-outlined text-sm">add_circle</span>
          Confirmer le crédit
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import api from '@/api/axios'

const props = defineProps({ isOpen: Boolean })
const emit = defineEmits(['close', 'success'])

const amount = ref(100)
const loading = ref(false)

const submitCredit = async () => {
  if (amount.value <= 0) return
  loading.value = true
  try {
    const res = await api.post('/client/test-credit', { amount: amount.value })
    alert(`Succès! ${amount.value} DH ajoutés à votre solde virtuel.\nCeci est affiché dans votre barre de menu.`)
    emit('success')
    emit('close')
  } catch (error) {
    const msg = error.response?.data?.error || error.response?.data?.message || 'Erreur inconnue'
    alert('Erreur: ' + msg)
  } finally {
    loading.value = false
  }
}
</script>
