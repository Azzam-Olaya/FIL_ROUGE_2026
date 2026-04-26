<template>
  <Transition name="fade">
    <div v-if="isOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
      <div class="bg-surface-container w-full max-w-md rounded-[32px] shadow-2xl overflow-hidden border border-primary/10 animate-scale-in">
        
        <!-- Header -->
        <div class="px-8 pt-8 pb-4 flex justify-between items-start">
          <div>
            <h3 class="text-2xl font-headline font-black text-on-surface">Recharger</h3>
            <p class="text-on-surface-variant text-sm font-medium mt-1">Alimentez votre portefeuille via PayPal</p>
          </div>
          <button @click="$emit('close')" class="p-2 hover:bg-primary/10 rounded-full transition-colors text-on-surface-variant">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>

        <div class="px-8 py-4">
          <!-- Current Balance Info -->
          <div class="bg-primary/5 rounded-2xl p-4 flex items-center justify-between border border-primary/10 mb-6">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white">
                <span class="material-symbols-outlined text-xl">account_balance_wallet</span>
              </div>
              <div>
                <p class="text-[10px] uppercase tracking-widest font-black text-primary/60">Solde actuel</p>
                <p class="text-lg font-black text-on-surface">{{ Number(store.balance).toLocaleString() }} DH</p>
              </div>
            </div>
          </div>

          <!-- Amount Selection -->
          <div class="space-y-4">
            <label for="deposit_amount" class="block text-xs font-black uppercase tracking-widest text-on-surface-variant px-1">Montant à ajouter</label>
            <div class="grid grid-cols-3 gap-3">
              <button v-for="amt in [500, 1000, 5000]" :key="amt"
                @click="amount = amt"
                :class="amount === amt ? 'bg-primary text-white scale-105 shadow-lg shadow-primary/30' : 'bg-surface border border-primary/10 text-on-surface hover:bg-primary/5'"
                class="py-3 rounded-2xl font-black text-sm transition-all"
                type="button"
                :aria-label="`Sélectionner ${amt} Dirhams`">
                {{ amt }} DH
              </button>
            </div>
            
            <div class="relative mt-4">
              <span class="absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant font-bold">DH</span>
              <input id="deposit_amount" name="deposit_amount" v-model.number="amount" type="number" step="100"
                class="w-full bg-surface border-2 border-primary/10 rounded-2xl py-4 pl-12 pr-4 text-lg font-black focus:outline-none focus:border-primary/40 focus:ring-4 focus:ring-primary/5 transition-all"
                placeholder="Montant libre" />
            </div>
          </div>

          <!-- PayPal Button Container -->
          <div class="mt-8 min-h-[150px]">
             <div v-if="loadingSDK" class="flex flex-col items-center justify-center py-8">
               <div class="w-8 h-8 border-4 border-primary/10 border-t-primary rounded-full animate-spin"></div>
               <p class="text-xs font-bold text-on-surface-variant mt-3 uppercase tracking-widest">Préparation PayPal...</p>
             </div>
             <div id="paypal-button-container" :class="{ 'opacity-50 pointer-events-none': amount <= 0 }"></div>
          </div>
          
        <p class="text-[10px] text-center text-on-surface-variant/60 font-medium px-4 mt-4 uppercase tracking-tighter">
            Transactions sécurisées par cryptage SSL. Les fonds sont crédités instantanément après confirmation.
          </p>
        </div>

      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useClientStore } from '@/stores/client'

const props = defineProps({
  isOpen: Boolean
})
const emit = defineEmits(['close', 'success'])
const store = useClientStore()
const amount = ref(1000)
const loadingSDK = ref(true)

const loadPayPalSDK = () => {
  // Check if SDK already loaded
  if (window.paypal) {
    loadingSDK.value = false
    renderButtons()
    return
  }

  const script = document.createElement('script')
  const clientId = 'sb' // User can change this
  script.src = `https://www.paypal.com/sdk/js?client-id=${clientId}&currency=USD&components=buttons&disable-funding=credit,card,paylater`
  script.addEventListener('load', () => {
    loadingSDK.value = false
    renderButtons()
  })
  document.body.appendChild(script)
}

const renderButtons = () => {
  if (!window.paypal) return

  window.paypal.Buttons({
    createOrder: (data, actions) => {
      // In a real app, currency would match your DH, here we use USD for sandbox simplicity or conversion logic
      return actions.order.create({
        purchase_units: [{
          amount: {
            value: amount.value.toString()
          },
          description: "Rechargement de portefeuille MorLancer"
        }]
      })
    },
    onApprove: async (data, actions) => {
      try {
        await store.capturePayPalPayment(data.orderID)
        emit('success')
        emit('close')
        alert('Votre compte a été crédité avec succès !')
      } catch (e) {
        alert('Erreur lors de la validation du paiement PayPal.')
      }
    },
    onError: (err) => {
      console.error('PayPal Error:', err)
      alert('Une erreur est survenue avec PayPal.')
    }
  }).render('#paypal-button-container')
}

const simulateDeposit = async () => {
  if (amount.value <= 0) return
  try {
     // We use the simulated endpoint or a direct call if we have one
     // For now, let's use the addFunds endpoint I created earlier (Step 1965/1966)
     await api.post('/wallet/deposit', { amount: amount.value })
     emit('success')
     emit('close')
     alert(`Simulation réussie ! ${amount.value} DH ajoutés à votre compte.`)
  } catch (e) {
     const serverError = e.response?.data?.error || e.response?.data?.message || 'Erreur lors de la simulation.'
     alert(`Échec : ${serverError}`)
  }
}

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    setTimeout(loadPayPalSDK, 100)
  }
})

onMounted(() => {
  if (props.isOpen) loadPayPalSDK()
})
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.animate-scale-in { animation: scaleIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
@keyframes scaleIn {
  from { transform: scale(0.95); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}

/* Hide arrow on number input */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
</style>
