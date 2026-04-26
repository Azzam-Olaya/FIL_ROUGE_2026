<template>
  <div class="wallet-container p-6 space-y-8 animate-fade-in">
    <!-- Balance Card -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="balance-card bg-gradient-to-br from-indigo-600 to-blue-700 p-8 rounded-3xl text-white shadow-xl relative overflow-hidden group">
        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
          <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24"><path d="M21 18v1c0 1.1-.9 2-2 2H5c-1.11 0-2-.9-2-2V5c0-1.1.89-2 2-2h14c1.1 0 2 .9 2 2v1h-9c-1.11 0-2 .9-2 2v8c0 1.1.89 2 2 2h9zm-9-2h10V8H12v8zm4-2.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>
        </div>
        <h3 class="text-indigo-100 text-sm font-medium mb-1">Solde disponible</h3>
        <div class="text-4xl font-bold mb-4">{{ formatCurrency(store.wallet.balance) }}</div>
        <button @click="showDeposit = true" class="bg-white/20 hover:bg-white/30 backdrop-blur-md px-6 py-2 rounded-xl text-sm font-semibold transition-all">
          Ajouter des fonds
        </button>
      </div>

      <div class="stat-card bg-white p-6 rounded-3xl border border-gray-100 shadow-sm flex items-center space-x-4">
        <div class="w-12 h-12 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
        </div>
        <div>
          <div class="text-gray-500 text-xs font-medium">{{ isClient ? 'Total dépensé' : 'Total gagné' }}</div>
          <div class="text-xl font-bold">{{ formatCurrency(isClient ? store.wallet.total_spent : store.wallet.total_earned) }}</div>
        </div>
      </div>

      <div class="stat-card bg-white p-6 rounded-3xl border border-gray-100 shadow-sm flex items-center space-x-4">
        <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
          <div class="text-gray-500 text-xs font-medium">Transactions ({{ store.transactions?.length || 0 }})</div>
          <div class="text-xl font-bold">Activité récente</div>
        </div>
      </div>
    </div>

    <!-- History -->
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-50 flex justify-between items-center">
        <h2 class="font-bold text-gray-800">Historique des transactions</h2>
      </div>
      <div class="divide-y divide-gray-50">
        <div v-if="!store.transactions?.length" class="p-12 text-center text-gray-400 italic">
          Aucune transaction enregistrée.
        </div>
        <div v-for="tx in store.transactions" :key="tx.id" class="p-4 hover:bg-gray-50 transition-colors flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <div :class="[
              'w-10 h-10 rounded-full flex items-center justify-center',
              tx.type === 'escrow_in' || tx.type === 'withdrawal' ? 'bg-red-50 text-red-500' : 'bg-green-50 text-green-500'
            ]">
              <svg v-if="tx.type === 'escrow_in'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
              <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
            </div>
            <div>
              <div class="font-semibold text-gray-800 text-sm italic">{{ tx.description }}</div>
              <div class="text-xs text-gray-400 italic">{{ formatDate(tx.created_at) }}</div>
            </div>
          </div>
          <div :class="['font-bold', tx.type === 'escrow_in' || tx.type === 'withdrawal' ? 'text-red-600' : 'text-green-600']">
            {{ tx.type === 'escrow_in' || tx.type === 'withdrawal' ? '-' : '+' }}{{ formatCurrency(tx.amount) }}
          </div>
        </div>
      </div>
    </div>

    <!-- Deposit Mock Modal -->
    <div v-if="showDeposit" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-3xl w-full max-w-md p-8 shadow-2xl animate-scale-up">
        <h3 class="text-2xl font-bold mb-6">Ajouter des fonds</h3>
        <p class="text-gray-500 text-sm mb-6 italic">Simulation d'intégration de paiement (Stripe/PayPal non configuré).</p>
        <div class="space-y-4">
          <input v-model="depositAmount" type="number" placeholder="Montant (min 50 DH)" class="w-full px-5 py-4 bg-gray-50 rounded-2xl border-0 focus:ring-2 focus:ring-indigo-500 transition-all font-bold text-xl">
          <button @click="handleDeposit" :disabled="depositAmount < 50" class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-bold py-4 rounded-2xl shadow-lg transition-all">
            Confirmer le dépôt
          </button>
          <button @click="showDeposit = false" class="w-full py-2 text-gray-400 hover:text-gray-600 text-sm transition-all">Annuler</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({
  store: { type: Object, required: true },
  isClient: { type: Boolean, default: false }
})

const showDeposit = ref(false)
const depositAmount = ref(100)

const formatCurrency = (val) => new Intl.NumberFormat('fr-MA', { style: 'currency', currency: 'MAD' }).format(val)
const formatDate = (date) => new Date(date).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })

const handleDeposit = async () => {
  try {
    const res = await props.store.deposit(depositAmount.value)
    if (res) {
      showDeposit.value = false
      await props.store.fetchWallet()
      await props.store.fetchTransactions()
    }
  } catch(e) {}
}

onMounted(() => {
  props.store.fetchWallet()
  props.store.fetchTransactions()
})
</script>

<style scoped>
.animate-fade-in { animation: fadeIn 0.4s ease-out; }
.animate-scale-up { animation: scaleUp 0.3s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
@keyframes scaleUp { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
</style>
