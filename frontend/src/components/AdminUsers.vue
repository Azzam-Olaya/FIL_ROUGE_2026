<template>
  <div class="space-y-6">
    <!-- Filtres -->
    <div class="flex gap-4">
      <select v-model="filters.role" @change="loadUsers" class="border p-2 rounded">
        <option value="">Tous les rôles</option>
        <option value="client">Client</option>
        <option value="freelancer">Freelancer</option>
      </select>
      <select v-model="filters.status" @change="loadUsers" class="border p-2 rounded">
        <option value="">Tous les statuts</option>
        <option value="pending">En attente</option>
        <option value="verified">Vérifié</option>
        <option value="banned">Banni</option>
      </select>
    </div>

    <!-- Liste des utilisateurs -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
      <table class="min-w-full">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rôle</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="user in users.data" :key="user.id">
            <td class="px-6 py-4 whitespace-nowrap">{{ user.name }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ user.email }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ user.role_name }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="getStatusClass(user.verification_status)">
                {{ getStatusText(user.verification_status) }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap space-x-2">
              <button v-if="user.verification_status === 'pending'"
                      @click="approveUser(user.id)"
                      class="bg-green-500 text-white px-3 py-1 rounded text-sm">
                Approuver
              </button>
              <button v-if="user.verification_status === 'pending'"
                      @click="rejectUser(user.id)"
                      class="bg-red-500 text-white px-3 py-1 rounded text-sm">
                Rejeter
              </button>
              <button @click="banUser(user.id)"
                      class="bg-gray-500 text-white px-3 py-1 rounded text-sm">
                Bannir
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const users = ref({ data: [] });
const filters = ref({ role: '', status: '' });

const loadUsers = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    const params = {};
    if (filters.value.role) params.role = filters.value.role;
    if (filters.value.status) params.status = filters.value.status;

    const response = await axios.get('http://localhost:8000/api/admin/users', {
      headers: { 'Authorization': `Bearer ${token}` },
      params
    });
    users.value = response.data;
  } catch (error) {
    alert('Erreur lors du chargement des utilisateurs');
  }
};

const approveUser = async (id) => {
  try {
    const token = localStorage.getItem('auth_token');
    await axios.post(`http://localhost:8000/api/admin/users/${id}/approve`, {}, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    alert('Utilisateur approuvé');
    loadUsers();
  } catch (error) {
    alert('Erreur lors de l\'approbation');
  }
};

const rejectUser = async (id) => {
  try {
    const token = localStorage.getItem('auth_token');
    await axios.post(`http://localhost:8000/api/admin/users/${id}/reject`, {}, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    alert('Utilisateur rejeté');
    loadUsers();
  } catch (error) {
    alert('Erreur lors du rejet');
  }
};

const banUser = async (id) => {
  try {
    const token = localStorage.getItem('auth_token');
    await axios.post(`http://localhost:8000/api/admin/users/${id}/ban`, {}, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    alert('Utilisateur banni');
    loadUsers();
  } catch (error) {
    alert('Erreur lors du bannissement');
  }
};

const getStatusClass = (status) => {
  const classes = {
    'pending': 'bg-yellow-100 text-yellow-800',
    'verified': 'bg-green-100 text-green-800',
    'banned': 'bg-red-100 text-red-800',
    'rejected': 'bg-gray-100 text-gray-800'
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const getStatusText = (status) => {
  const texts = {
    'pending': 'En attente',
    'verified': 'Vérifié',
    'banned': 'Banni',
    'rejected': 'Rejeté'
  };
  return texts[status] || status;
};

onMounted(() => {
  loadUsers();
});
</script>