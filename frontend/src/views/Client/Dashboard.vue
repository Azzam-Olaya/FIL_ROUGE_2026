<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Main Sidebar -->
    <aside class="w-64 bg-white border-r min-h-screen p-6">
      <h1 class="text-2xl font-bold text-indigo-600 mb-8 italic">MorLancer</h1>
      <nav class="space-y-4">
        <a href="#" class="block px-4 py-2 text-indigo-600 bg-indigo-50 rounded-lg font-medium">Dashboard</a>
        <a href="#" class="block px-4 py-2 text-gray-600 hover:bg-gray-50 rounded-lg">Mes Missions</a>
        <a href="#" class="block px-4 py-2 text-gray-600 hover:bg-gray-50 rounded-lg">Paiements</a>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8">
      <div class="flex justify-between items-center mb-8">
        <h2 class="text-2xl font-bold text-gray-800">Espace Client</h2>
        <div class="flex items-center space-x-4">
          <span class="text-sm font-medium text-gray-600">Solde: {{ balance }} MAD</span>
          <button @click="showPostMission = true" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition shadow-sm">Publier une mission</button>
        </div>
      </div>

      <!-- Quick Actions / Stats -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
          <p class="text-gray-500 text-sm">Missions actives</p>
          <p class="text-3xl font-bold text-gray-900">3</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
          <p class="text-gray-500 text-sm">Contrats terminés</p>
          <p class="text-3xl font-bold text-gray-900">12</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
          <p class="text-gray-500 text-sm">Dépenses totales</p>
          <p class="text-3xl font-bold text-gray-900">4,500 MAD</p>
        </div>
      </div>

      <!-- Recent Missions -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b flex justify-between items-center">
          <h3 class="font-bold text-gray-800">Mes dernières missions</h3>
          <a href="#" class="text-indigo-600 text-sm hover:underline">Voir tout</a>
        </div>
        <div class="p-6 space-y-4">
          <div v-for="mission in missions" :key="mission.id" class="flex justify-between items-center p-4 border rounded-lg hover:bg-gray-50 transition">
             <div>
                <h4 class="font-semibold text-gray-900">{{ mission.title }}</h4>
                <p class="text-sm text-gray-500">{{ mission.category?.name }} • {{ mission.budget }} MAD</p>
             </div>
             <span :class="getStatusClass(mission.status)" class="px-3 py-1 rounded-full text-xs font-medium uppercase">
                {{ mission.status }}
             </span>
          </div>
        </div>
      </div>
    </main>

    <!-- Post Mission Modal -->
    <div v-if="showPostMission" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
       <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-8 animate-in fade-in zoom-in duration-300">
          <h3 class="text-xl font-bold mb-6 text-gray-900">Publier une nouvelle mission</h3>
          <form @submit.prevent="postMission" class="space-y-4">
             <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Titre du projet</label>
                <input v-model="newMission.title" class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
             </div>
             <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea v-model="newMission.description" class="w-full p-2 border rounded-lg h-32 focus:ring-2 focus:ring-indigo-500 outline-none"></textarea>
             </div>
             <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Budget (MAD)</label>
                <input v-model="newMission.budget" type="number" class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
             </div>
             <div class="flex justify-end space-x-4 pt-4">
                <button type="button" @click="showPostMission = false" class="text-gray-500 hover:text-gray-700">Annuler</button>
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition">Publier</button>
             </div>
          </form>
       </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const balance = ref(2500);
const showPostMission = ref(false);
const newMission = ref({ title: '', description: '', budget: '' });

const missions = ref([
  { id: 1, title: 'Création Logo Bio', category: { name: 'Design Graphique' }, budget: 500, status: 'open' },
  { id: 2, title: 'Application Mobile React', category: { name: 'Développement Web' }, budget: 3000, status: 'active' },
]);

const postMission = () => {
  console.log('Posting mission', newMission.value);
  showPostMission.value = false;
};

const getStatusClass = (status) => {
  switch(status) {
    case 'open': return 'bg-green-100 text-green-800';
    case 'active': return 'bg-blue-100 text-blue-800';
    default: return 'bg-gray-100 text-gray-800';
  }
};
</script>
