<template>
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[100] flex items-center justify-center p-4" @click.self="$emit('close')">
      <div class="bg-white rounded-[2.5rem] shadow-2xl max-w-lg w-full flex flex-col max-h-[90vh] animate-in overflow-hidden">
        
        <!-- Header -->
        <div class="p-8 pb-4 flex items-center justify-between">
          <div>
            <h2 class="font-headline text-2xl font-bold text-on-surface">Proposer un contrat</h2>
            <p class="text-xs text-on-surface-variant font-medium mt-1">À destination de <span class="text-primary font-bold">{{ freelancerName }}</span></p>
          </div>
          <button @click="$emit('close')" class="w-10 h-10 rounded-full hover:bg-primary/10 transition-colors flex items-center justify-center">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>

        <!-- Form Content -->
        <div class="flex-1 overflow-y-auto p-8 pt-4 space-y-6 custom-scrollbar">
          
          <!-- Price -->
            <div class="flex justify-between items-center mb-3">
              <label class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">payments</span>
                Budget Total (DH)
              </label>
              <div @click="showDeposit = true" class="flex items-center gap-1 px-2 py-1 bg-primary/5 rounded-lg border border-primary/10 cursor-pointer hover:bg-primary/10 transition-colors">
                <span class="text-[9px] font-black text-primary uppercase">Solde : {{ Number(store.balance).toLocaleString() }} DH</span>
                <span class="material-symbols-outlined text-[10px] text-primary">add_circle</span>
              </div>
            </div>
            <div class="relative">
              <input v-model="form.amount" type="number" placeholder="Ex: 5000"
                class="w-full bg-surface-container-high/50 border border-primary/5 rounded-2xl pl-14 pr-6 py-4 text-sm font-bold focus:border-primary focus:outline-none transition-all" />
              <span class="absolute left-6 top-1/2 -translate-y-1/2 font-black text-primary/40 text-lg">DH</span>
            </div>

          <!-- Deadline -->
          <div>
            <label class="block text-[10px] font-black uppercase tracking-widest text-on-surface-variant mb-3 flex items-center gap-2">
              <span class="material-symbols-outlined text-sm">event</span>
              Échéance (Deadline)
            </label>
            <input v-model="form.deadline" type="date"
              class="w-full bg-surface-container-high/50 border border-primary/5 rounded-2xl px-6 py-4 text-sm font-bold focus:border-primary focus:outline-none transition-all" />
          </div>

          <!-- Technologies -->
          <div>
            <label class="block text-[10px] font-black uppercase tracking-widest text-on-surface-variant mb-3 flex items-center gap-2">
              <span class="material-symbols-outlined text-sm">terminal</span>
              Langages & Technologies
            </label>
            <input v-model="form.technologies" placeholder="Ex: Vue.js, Laravel, Tailwind..."
              class="w-full bg-surface-container-high/50 border border-primary/5 rounded-2xl px-6 py-4 text-sm focus:border-primary focus:outline-none transition-all" />
          </div>

          <!-- Specifications (CDC) -->
          <div>
            <label class="block text-[10px] font-black uppercase tracking-widest text-on-surface-variant mb-3 flex items-center gap-2">
              <span class="material-symbols-outlined text-sm">description</span>
              Cahier des charges (Détails)
            </label>
            <textarea v-model="form.specifications" rows="5" placeholder="Décrivez précisément les livrables attendus..."
              class="w-full bg-surface-container-high/50 border border-primary/5 rounded-3xl px-6 py-5 text-sm focus:border-primary focus:outline-none resize-none transition-all" />
          </div>

          <!-- Note -->
          <div class="bg-primary/5 p-5 rounded-3xl border border-primary/10">
            <div class="flex gap-3 text-primary">
              <span class="material-symbols-outlined">info</span>
              <p class="text-[11px] font-medium leading-relaxed">
                Les fonds seront immédiatement bloqués sur votre compte MorLancer (<span class="font-bold">Escrow</span>). 
                Le projet commencera dès que le freelancer aura validé vos termes.
              </p>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="p-8 bg-surface-container-low/50 flex gap-4">
          <button @click="$emit('close')"
            class="flex-1 py-4 px-6 rounded-full border border-primary text-primary font-bold text-sm hover:bg-primary/5 transition-all">
            Annuler
          </button>
          <button @click="submit" :disabled="loading || !isValid"
            class="flex-2 py-4 px-8 rounded-full bg-primary text-white font-bold text-sm hover:scale-[1.02] active:scale-95 transition-all disabled:opacity-50 disabled:scale-100 flex items-center justify-center gap-3 shadow-xl shadow-primary/20">
            <span v-if="loading" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
            <span v-else class="flex items-center gap-2">
              Envoyer la proposition
              <span class="material-symbols-outlined text-sm">send</span>
            </span>
          </button>
        </div>
      </div>
    </div>

    <DepositModal :is-open="showDeposit" @close="showDeposit = false" @success="store.fetchBalance()" />
  </Teleport>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/api/axios'
import { useClientStore } from '@/stores/client'
import DepositModal from '@/components/Client/DepositModal.vue'

const props = defineProps({
  show: Boolean,
  freelancerId: [Number, String],
  freelancerName: String
})

const emit = defineEmits(['close', 'success'])

const store     = useClientStore()
const loading   = ref(false)
const showDeposit = ref(false)
const form = ref({
  amount: '',
  deadline: '',
  technologies: '',
  specifications: '',
  freelancer_id: props.freelancerId
})

onMounted(() => {
  store.fetchBalance()
})

const isValid = computed(() => {
  return form.value.amount > 0 && 
         form.value.deadline && 
         form.value.technologies.trim() && 
         form.value.specifications.trim()
})

const submit = async () => {
  if (!isValid.value) return
  loading.value = true
  try {
    const res = await api.post('/contracts', {
      ...form.value,
      freelancer_id: props.freelancerId
    })
    alert('Proposition envoyée avec succès ! Fonds bloqués en escrow.')
    emit('success', res.data.contract)
    emit('close')
  } catch (err) {
    const errorData = err.response?.data
    let msg = errorData?.message || 'Une erreur est survenue.'
    if (errorData?.errors) {
      msg = Object.values(errorData.errors).flat().join('\n')
    }
    alert(msg)
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
