<template>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <!-- Carte Missions -->
    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-500">
      <div class="flex items-center">
        <div class="flex-shrink-0">
          <span class="material-symbols-outlined text-blue-500 text-3xl">work</span>
        </div>
        <div class="ml-4">
          <h3 class="text-lg font-semibold text-gray-900">Total Missions</h3>
          <p class="text-2xl font-bold text-blue-600">{{ stats.total_missions }}</p>
        </div>
      </div>
    </div>

    <!-- Carte Clients -->
    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-500">
      <div class="flex items-center">
        <div class="flex-shrink-0">
          <span class="material-symbols-outlined text-green-500 text-3xl">group</span>
        </div>
        <div class="ml-4">
          <h3 class="text-lg font-semibold text-gray-900">Clients</h3>
          <p class="text-2xl font-bold text-green-600">{{ stats.total_clients }}</p>
        </div>
      </div>
    </div>

    <!-- Carte Freelancers -->
    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-purple-500">
      <div class="flex items-center">
        <div class="flex-shrink-0">
          <span class="material-symbols-outlined text-purple-500 text-3xl">engineering</span>
        </div>
        <div class="ml-4">
          <h3 class="text-lg font-semibold text-gray-900">Freelancers</h3>
          <p class="text-2xl font-bold text-purple-600">{{ stats.total_freelancers }}</p>
        </div>
      </div>
    </div>

    <!-- Carte Revenus -->
    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-yellow-500">
      <div class="flex items-center">
        <div class="flex-shrink-0">
          <span class="material-symbols-outlined text-yellow-500 text-3xl">attach_money</span>
        </div>
        <div class="ml-4">
          <h3 class="text-lg font-semibold text-gray-900">Revenus Platform</h3>
          <p class="text-2xl font-bold text-yellow-600">{{ stats.sum_revenue_platform }} MAD</p>
        </div>
      </div>
    </div>

    <!-- Carte Utilisateurs en attente -->
    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-orange-500">
      <div class="flex items-center">
        <div class="flex-shrink-0">
          <span class="material-symbols-outlined text-orange-500 text-3xl">pending</span>
        </div>
        <div class="ml-4">
          <h3 class="text-lg font-semibold text-gray-900">En Attente</h3>
          <p class="text-2xl font-bold text-orange-600">{{ stats.pending_users }}</p>
        </div>
      </div>
    </div>

    <!-- Carte Utilisateurs bannis -->
    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-red-500">
      <div class="flex items-center">
        <div class="flex-shrink-0">
          <span class="material-symbols-outlined text-red-500 text-3xl">block</span>
        </div>
        <div class="ml-4">
          <h3 class="text-lg font-semibold text-gray-900">Bannis</h3>
          <p class="text-2xl font-bold text-red-600">{{ stats.banned_users }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

// État réactif pour les statistiques
const stats = ref({
  total_missions: 0,
  total_clients: 0,
  total_freelancers: 0,
  total_contracts: 0,
  sum_revenue_platform: 0,
  pending_users: 0,
  banned_users: 0
});

// Fonction pour charger les statistiques
const loadStats = async () => {
  try {
    // Récupération du token depuis le localStorage (ou Pinia store)
    const token = localStorage.getItem('auth_token');

    const response = await axios.get('http://localhost:8000/api/admin/stats', {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });

    // Mise à jour des statistiques
    stats.value = response.data;
  } catch (error) {
    console.error('Erreur lors du chargement des statistiques:', error);
    alert('Erreur lors du chargement des statistiques');
  }
};

// Chargement automatique au montage du composant
onMounted(() => {
  loadStats();
});
</script>