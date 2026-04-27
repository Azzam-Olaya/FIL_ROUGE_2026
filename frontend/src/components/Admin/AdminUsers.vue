<template>
  <div class="space-y-6">
    <!-- Filtres -->
    <div class="flex flex-col sm:flex-row gap-4">
      <select v-model="filters.role" @change="loadUsers" class="border p-2 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 outline-none">
        <option value="">Tous les rôles</option>
        <option value="client">Client</option>
        <option value="freelancer">Freelancer</option>
      </select>
      <select v-model="filters.status" @change="loadUsers" class="border p-2 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 outline-none">
        <option value="">Tous les statuts</option>
        <option value="pending">En attente</option>
        <option value="verified">Vérifié</option>
        <option value="rejected">Rejeté</option>
        <option value="banned">Banni</option>
      </select>
    </div>

    <!-- Liste des utilisateurs -->
    <div class="bg-white shadow-xl shadow-primary/5 rounded-[2rem] border border-primary/5 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Nom</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Compte</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">N° CIN</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Né(e) à</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Document</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Date</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Statut</th>
              <th class="px-6 py-4 text-right text-xs font-bold text-gray-400 uppercase tracking-widest">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50/50 transition-colors">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs uppercase">
                    {{ user.name.charAt(0) }}
                  </div>
                  <span class="font-bold text-sm text-on-surface">{{ user.name }}</span>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="flex flex-col">
                  <span class="text-sm text-on-surface-variant">{{ user.email }}</span>
                  <span class="text-[10px] font-bold uppercase tracking-wider text-primary">{{ user.role_name }}</span>
                </div>
              </td>
              <td class="px-6 py-4">
                <span class="text-sm font-bold text-on-surface">{{ user.id_number || '—' }}</span>
              </td>
              <td class="px-6 py-4">
                <span class="text-sm text-on-surface-variant">{{ user.birthplace || '—' }}</span>
              </td>
              <td class="px-6 py-4">
                <a v-if="user.id_document_path"
                   :href="`${BASE_URL}/storage/${user.id_document_path}`"
                   target="_blank"
                   class="text-primary hover:underline text-xs font-bold flex items-center gap-1">
                  <span class="material-symbols-outlined text-sm">file_open</span> Voir
                </a>
                <span v-else class="text-xs text-gray-400 italic">Aucun</span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-600">
                <div class="flex flex-col">
                  <span>{{ formatDate(user.created_at) }}</span>
                  <span v-if="isNew(user.created_at)" class="text-[8px] text-primary font-black uppercase tracking-widest mt-1">Nouveau</span>
                </div>
              </td>
              <td class="px-6 py-4">
                <span :class="getStatusClass(user.verification_status)" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">
                  {{ getStatusText(user.verification_status) }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <div v-if="user.id !== currentUserId" class="flex justify-end gap-2">
                  <button v-if="user.verification_status === 'pending'"
                          @click="approveUser(user.id)"
                          title="Approuver"
                          class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors">
                    <span class="material-symbols-outlined">check_circle</span>
                  </button>
                  <button v-if="user.verification_status === 'pending'"
                          @click="rejectUser(user.id)"
                          title="Rejeter"
                          class="p-2 text-orange-600 hover:bg-orange-50 rounded-lg transition-colors">
                    <span class="material-symbols-outlined">cancel</span>
                  </button>
                  <button v-if="user.verification_status !== 'banned'"
                          @click="banUser(user.id)"
                          title="Bannir"
                          class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                    <span class="material-symbols-outlined">block</span>
                  </button>
                  <button v-if="user.verification_status === 'banned'"
                          @click="unbanUser(user.id)"
                          title="Débannir"
                          class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors">
                    <span class="material-symbols-outlined">lock_open</span>
                  </button>
                </div>
                <span v-else class="text-xs text-gray-400 italic">Vous</span>
              </td>
            </tr>
            <tr v-if="users.length === 0">
              <td colspan="8" class="px-6 py-12 text-center text-gray-400 italic text-sm">
                Aucun utilisateur trouvé.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '@/api/axios'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const currentUserId = computed(() => authStore.user?.id)

const users = ref([])
const filters = ref({ role: '', status: '' })
const BASE_URL = 'http://localhost:8000' // Keeping it as it's used for links

const loadUsers = async () => {
  try {
    const params = {}
    if (filters.value.role)   params.role   = filters.value.role
    if (filters.value.status) params.status = filters.value.status

    const response = await api.get('/admin/users', { params })
    users.value = response.data
  } catch {
    console.error('Erreur lors du chargement des utilisateurs')
  }
}

const approveUser = async (id) => {
  try {
    await api.post(`/admin/users/${id}/approve`)
    loadUsers()
  } catch (e) { alert(e.response?.data?.message || 'Erreur') }
}

const rejectUser = async (id) => {
  try {
    await api.post(`/admin/users/${id}/reject`)
    loadUsers()
  } catch (e) { alert(e.response?.data?.message || 'Erreur') }
}

const banUser = async (id) => {
  try {
    if (confirm('Voulez-vous vraiment bannir cet utilisateur ?')) {
      await api.post(`/admin/users/${id}/ban`)
      loadUsers()
    }
  } catch (e) { alert(e.response?.data?.message || 'Erreur') }
}

const unbanUser = async (id) => {
  try {
    await api.post(`/admin/users/${id}/approve`)
    loadUsers()
  } catch (e) { alert(e.response?.data?.message || 'Erreur') }
}

const getStatusClass = (status) => ({
  pending:  'bg-yellow-100 text-yellow-800',
  verified: 'bg-green-100 text-green-800',
  rejected: 'bg-orange-100 text-orange-800',
  banned:   'bg-red-100 text-red-800',
}[status] || 'bg-gray-100 text-gray-800')

const getStatusText = (status) => ({
  pending:  'En attente',
  verified: 'Vérifié',
  rejected: 'Rejeté',
  banned:   'Banni',
}[status] || status)

const formatDate = (date) => new Date(date).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' })
const isNew = (date) => (Date.now() - new Date(date).getTime()) < 48 * 60 * 60 * 1000

onMounted(loadUsers)
</script>
