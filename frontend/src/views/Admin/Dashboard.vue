<template>
  <div class="min-h-screen bg-gray-100">
    <div class="flex">
      <!-- Sidebar -->
      <aside class="w-64 bg-indigo-900 min-h-screen text-white p-6">
        <h1 class="text-2xl font-bold italic mb-10">MorLancer Admin</h1>
        <nav class="space-y-4">
          <a href="#" class="block py-2.5 px-4 rounded transition duration-200 bg-indigo-800">Tableau de bord</a>
          <a href="#" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-indigo-800">Utilisateurs</a>
          <a href="#" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-indigo-800">Contrats</a>
          <a href="#" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-indigo-800">Catégories</a>
        </nav>
      </aside>

      <!-- Main Content -->
      <main class="flex-1 p-10">
        <header class="flex justify-between items-center mb-10">
          <h2 class="text-3xl font-semibold text-gray-800">Statistiques Globales</h2>
          <button @click="logout" class="text-gray-600 hover:text-indigo-600">Déconnexion</button>
        </header>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
          <div v-for="(val, label) in stats" :key="label" class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <p class="text-sm font-medium text-gray-500 uppercase">{{ label }}</p>
            <p class="text-2xl font-bold text-gray-900">{{ val }}</p>
          </div>
        </div>

        <!-- Pending Users Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="p-6 border-b border-gray-100">
            <h3 class="text-xl font-bold text-gray-800">Utilisateurs en attente de validation</h3>
          </div>
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rôle</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="user in pendingUsers" :key="user.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ user.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ user.email }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ user.role?.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button @click="approveUser(user.id)" class="text-green-600 hover:text-green-900 mr-4">Approuver</button>
                  <button @click="banUser(user.id)" class="text-red-600 hover:text-red-900">Bannir</button>
                </td>
              </tr>
              <tr v-if="pendingUsers.length === 0">
                <td colspan="4" class="px-6 py-10 text-center text-gray-500 italic">Aucun utilisateur en attente de validation.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const stats = ref({
  'Missions': 0,
  'Clients': 0,
  'Freelancers': 0,
  'Contrats': 0
});

const pendingUsers = ref([]);

const fetchStats = async () => {
  // Simulated fetch
  stats.value = {
    'Missions': 12,
    'Clients': 45,
    'Freelancers': 32,
    'Contrats': 8
  };
};

const fetchPendingUsers = async () => {
  // Simulated fetch
  pendingUsers.value = [
    { id: 1, name: 'Ahmed El Alami', email: 'ahmed@example.com', role: { name: 'freelancer' } },
    { id: 2, name: 'Sara Bennani', email: 'sara@example.com', role: { name: 'client' } }
  ];
};

const approveUser = (id) => {
  pendingUsers.value = pendingUsers.value.filter(u => u.id !== id);
  console.log('Approved user', id);
};

const banUser = (id) => {
  pendingUsers.value = pendingUsers.value.filter(u => u.id !== id);
  console.log('Banned user', id);
};

onMounted(() => {
  fetchStats();
  fetchPendingUsers();
});

const logout = () => {
  localStorage.removeItem('token');
  window.location.href = '/login';
};
</script>
