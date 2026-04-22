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
        <option value="rejected">Rejeté</option>
        <option value="banned">Banni</option>
      </select>
    </div>

    <!-- Liste des utilisateurs -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
      <table class="min-w-full">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rôle</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° CIN/Passeport</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lieu de naissance</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Document</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="user in users" :key="user.id">
            <td class="px-4 py-4 whitespace-nowrap">{{ user.name }}</td>
            <td class="px-4 py-4 whitespace-nowrap">{{ user.email }}</td>
            <td class="px-4 py-4 whitespace-nowrap">{{ user.role_name }}</td>
            <td class="px-4 py-4 whitespace-nowrap">{{ user.id_number || '—' }}</td>
            <td class="px-4 py-4 whitespace-nowrap">{{ user.birthplace || '—' }}</td>
            <td class="px-4 py-4 whitespace-nowrap">
              <a v-if="user.id_document_path"
                 :href="`http://localhost:8000/storage/${user.id_document_path}`"
                 target="_blank"
                 class="text-blue-600 underline text-sm">Voir document</a>
              <span v-else class="text-gray-400 text-sm">Aucun</span>
            </td>
            <td class="px-4 py-4 whitespace-nowrap">
              <span :class="getStatusClass(user.verification_status)" class="px-2 py-1 rounded-full text-xs font-bold">
                {{ getStatusText(user.verification_status) }}
              </span>
            </td>
            <td class="px-4 py-4 whitespace-nowrap space-x-2">
              <button v-if="user.verification_status === 'pending'"
                      @click="approveUser(user.id)"
                      class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600">
                Approuver
              </button>
              <button v-if="user.verification_status === 'pending'"
                      @click="rejectUser(user.id)"
                      class="bg-orange-500 text-white px-3 py-1 rounded text-sm hover:bg-orange-600">
                Rejeter
              </button>
              <button v-if="user.verification_status !== 'banned'"
                      @click="banUser(user.id)"
                      class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600">
                Bannir
              </button>
            </td>
          </tr>
          <tr v-if="users.length === 0">
            <td colspan="8" class="px-4 py-8 text-center text-gray-400">Aucun utilisateur trouvé.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const users = ref([]);
const filters = ref({ role: '', status: '' });

const getToken = () => localStorage.getItem('token');

const loadUsers = async () => {
  try {
    const params = {};
    if (filters.value.role) params.role = filters.value.role;
    if (filters.value.status) params.status = filters.value.status;

    const response = await axios.get('http://localhost:8000/api/admin/users', {
      headers: { Authorization: `Bearer ${getToken()}` },
      params,
    });
    users.value = response.data;
  } catch {
    alert('Erreur lors du chargement des utilisateurs');
  }
};

const approveUser = async (id) => {
  await axios.post(`http://localhost:8000/api/admin/users/${id}/approve`, {}, {
    headers: { Authorization: `Bearer ${getToken()}` },
  });
  loadUsers();
};

const rejectUser = async (id) => {
  await axios.post(`http://localhost:8000/api/admin/users/${id}/reject`, {}, {
    headers: { Authorization: `Bearer ${getToken()}` },
  });
  loadUsers();
};

const banUser = async (id) => {
  await axios.post(`http://localhost:8000/api/admin/users/${id}/ban`, {}, {
    headers: { Authorization: `Bearer ${getToken()}` },
  });
  loadUsers();
};

const getStatusClass = (status) => ({
  pending: 'bg-yellow-100 text-yellow-800',
  verified: 'bg-green-100 text-green-800',
  rejected: 'bg-orange-100 text-orange-800',
  banned: 'bg-red-100 text-red-800',
}[status] || 'bg-gray-100 text-gray-800');

const getStatusText = (status) => ({
  pending: 'En attente',
  verified: 'Vérifié',
  rejected: 'Rejeté',
  banned: 'Banni',
}[status] || status);

onMounted(loadUsers);
</script>
