<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Freelancer Sidebar -->
    <aside class="w-64 bg-slate-900 min-h-screen p-6 text-white">
      <h1 class="text-2xl font-bold text-indigo-400 mb-8 italic">MorLancer</h1>
      <nav class="space-y-4">
        <a href="#" class="block px-4 py-2 bg-indigo-600 rounded-lg font-medium">Tableau de bord</a>
        <a href="#" class="block px-4 py-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition">Missions disponibles</a>
        <a href="#" class="block px-4 py-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition">Mon Portfolio</a>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8">
      <div class="flex justify-between items-center mb-8">
        <h2 class="text-2xl font-bold text-gray-800 text-3xl">Espace Freelance</h2>
        <div class="flex items-center space-x-6">
          <div class="text-right">
             <p class="text-xs text-gray-500 uppercase font-semibold">Gains cumulés</p>
             <p class="text-xl font-bold text-gray-900">{{ balance }} MAD</p>
          </div>
          <button class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl shadow-lg hover:shadow-indigo-200 hover:translate-y-[-2px] transition-all font-bold">Rechercher des missions</button>
        </div>
      </div>

      <!-- Overview Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
        <div v-for="card in summaryCards" :key="card.label" class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
           <div :class="card.color" class="mb-2"><i :class="card.icon" class="text-2xl"></i></div>
           <p class="text-gray-500 text-sm font-medium">{{ card.label }}</p>
           <p class="text-2xl font-bold text-gray-900">{{ card.value }}</p>
        </div>
      </div>

      <!-- Active Work -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-10 overflow-hidden">
        <div class="p-6 border-b flex justify-between items-center">
          <h3 class="font-bold text-gray-800 text-lg">Travail en cours</h3>
        </div>
        <div class="p-0">
           <div v-for="contract in activeContracts" :key="contract.id" class="p-6 border-b last:border-0 hover:bg-gray-50 transition flex justify-between items-center">
              <div>
                 <h4 class="font-bold text-indigo-700 text-lg">{{ contract.title }}</h4>
                 <p class="text-sm text-gray-600">Client: {{ contract.client }} • Échéance: {{ contract.deadline }}</p>
              </div>
              <div class="flex items-center space-x-6">
                 <span class="text-xl font-bold text-gray-900">{{ contract.amount }} MAD</span>
                 <button class="bg-indigo-50 border border-indigo-200 text-indigo-700 px-6 py-2 rounded-xl hover:bg-indigo-600 hover:text-white transition text-sm font-bold">Gérer le projet</button>
              </div>
           </div>
        </div>
      </div>

      <!-- Available Missions Promo -->
      <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 rounded-2xl p-8 text-center text-white relative overflow-hidden shadow-xl">
         <div class="relative z-10">
            <h4 class="text-2xl font-bold mb-2">Nouvelles opportunités disponibles !</h4>
            <p class="text-indigo-100 mb-6 font-medium">Il y a actuellement 15 missions correspondant à vos compétences (PHP, Vue.js, Tailwind).</p>
            <button class="bg-white text-indigo-600 px-10 py-3 rounded-xl font-bold shadow-lg hover:bg-gray-100 transition inline-flex items-center">
               Explorer les missions <i class="fas fa-arrow-right ml-2"></i>
            </button>
         </div>
         <div class="absolute top-0 right-0 -m-8 w-64 h-64 bg-white opacity-5 rounded-full"></div>
         <div class="absolute bottom-0 left-0 -mb-12 -ml-12 w-48 h-48 bg-white opacity-5 rounded-full"></div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const balance = ref(12450);

const summaryCards = [
  { label: 'Contrats en cours', value: 2, icon: 'fas fa-briefcase', color: 'text-indigo-600' },
  { label: 'Contrats achevés', value: 8, icon: 'fas fa-check-circle', color: 'text-green-600' },
  { label: 'Note moyenne', value: '4.9/5', icon: 'fas fa-star', color: 'text-amber-500' },
  { label: 'Vues profil (7j)', value: 124, icon: 'fas fa-eye', color: 'text-blue-500' },
];

const activeContracts = ref([
  { id: 101, title: 'Refonte Site E-commerce', client: 'Maroc Store', deadline: '24/04/2026', amount: 5000 },
  { id: 102, title: 'Audit Sécurité Laravel', client: 'CyberSec Ltd', deadline: '30/04/2026', amount: 2500 },
]);
</script>
