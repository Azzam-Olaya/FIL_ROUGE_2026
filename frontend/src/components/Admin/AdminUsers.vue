<template>
  <div class="space-y-8">
    <!-- Filtres et Recherche -->
    <section class="bg-white/80 backdrop-blur-md p-8 rounded-3xl border border-primary/5 shadow-xl shadow-primary/5">
      <div class="flex flex-col lg:flex-row gap-6 items-start lg:items-center justify-between mb-8">
        <div>
          <h3 class="font-headline text-2xl font-bold text-on-surface mb-2">Gestion des Utilisateurs</h3>
          <p class="text-on-surface-variant text-sm font-medium">Validation, modération et supervision des comptes</p>
        </div>
        <div class="flex gap-4 items-center">
          <!-- Filtre par rôle -->
          <select v-model="filterRole" @change="loadUsers"
                  class="px-4 py-2 bg-white border border-primary/10 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-primary/20">
            <option value="">Tous les rôles</option>
            <option value="freelancer">Artisans</option>
            <option value="client">Clients</option>
            <option value="admin">Administrateurs</option>
          </select>
          <!-- Filtre par statut -->
          <select v-model="filterStatus" @change="loadUsers"
                  class="px-4 py-2 bg-white border border-primary/10 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-primary/20">
            <option value="">Tous les statuts</option>
            <option value="pending">En attente</option>
            <option value="approved">Approuvé</option>
            <option value="banned">Banni</option>
          </select>
        </div>
      </div>

      <!-- Barre de recherche -->
      <div class="relative mb-6">
        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-primary/60">search</span>
        <input v-model="searchQuery" @input="debouncedSearch"
               type="text" placeholder="Rechercher par nom ou email..."
               class="w-full pl-12 pr-4 py-3 bg-white border border-primary/10 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
      </div>

      <div v-if="pendingCount > 0" class="mb-6 rounded-3xl border border-yellow-200 bg-yellow-50 p-5 text-yellow-900">
        <div class="flex items-start gap-3">
          <span class="material-symbols-outlined text-3xl">notifications</span>
          <div>
            <p class="font-bold text-sm uppercase tracking-[0.24em] mb-1">Validation requise</p>
            <p class="text-sm leading-6">Vous avez {{ pendingCount }} utilisateur(s) en attente. Vérifiez les pièces justificatives, puis validez ou rejetez les comptes.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Liste des Utilisateurs -->
    <section class="bg-white/80 backdrop-blur-md p-8 rounded-3xl border border-primary/5 shadow-xl shadow-primary/5">
      <div v-if="loading" class="text-center py-20">
        <div class="animate-spin w-8 h-8 border-4 border-primary border-t-transparent rounded-full mx-auto mb-4"></div>
        <p class="text-on-surface-variant">Chargement des utilisateurs...</p>
      </div>

      <div v-else-if="error" class="text-center py-20">
        <span class="material-symbols-outlined text-6xl text-secondary mb-4">error</span>
        <p class="text-secondary font-bold">{{ error }}</p>
      </div>

      <div v-else-if="users.length === 0" class="text-center py-20">
        <span class="material-symbols-outlined text-6xl text-gray-400 mb-4">people</span>
        <p class="text-gray-500 font-bold">Aucun utilisateur trouvé</p>
      </div>

      <div v-else class="space-y-4">
        <div v-for="user in users" :key="user.id"
             class="flex gap-6 items-center p-6 bg-white rounded-2xl border border-primary/5 shadow-sm hover:shadow-md transition-all group">
          <!-- Avatar -->
          <div class="w-16 h-16 rounded-full bg-surface-container flex items-center justify-center font-bold text-xl text-primary overflow-hidden">
            {{ user.name ? user.name[0].toUpperCase() : '?' }}
          </div>

          <!-- Informations utilisateur -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 mb-2">
              <h4 class="font-bold text-lg truncate">{{ user.name }}</h4>
              <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest"
                    :class="getRoleBadgeClass(user.role?.name)">
                {{ user.role?.name || 'N/A' }}
              </span>
              <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest"
                    :class="getStatusBadgeClass(user.status)">
                {{ getStatusLabel(user.status) }}
              </span>
            </div>
            <p class="text-sm text-on-surface-variant mb-1">{{ user.email }}</p>
            <p class="text-xs text-on-surface-variant/60">
              Inscrit le {{ formatDate(user.created_at) }}
              <span v-if="user.last_login_at">• Dernière connexion: {{ formatDate(user.last_login_at) }}</span>
            </p>
          </div>

          <!-- Actions -->
          <div class="flex gap-2">
            <button v-if="user.status === 'pending'"
                    @click="approveUser(user.id)"
                    class="px-4 py-2 bg-primary text-white rounded-xl text-sm font-bold hover:brightness-110 active:scale-95 transition-all flex items-center gap-2">
              <span class="material-symbols-outlined text-sm">check_circle</span>
              Approuver
            </button>
                        <button v-if="user.status === 'pending'"
                    @click="rejectUser(user.id)"
                    class="px-4 py-2 bg-secondary text-white rounded-xl text-sm font-bold hover:brightness-110 active:scale-95 transition-all flex items-center gap-2">
              <span class="material-symbols-outlined text-sm">block</span>
              Rejeter
            </button>
            <button v-else-if="user.status === 'approved'"
                    @click="rejectUser(user.id)"
                    class="px-4 py-2 bg-secondary text-white rounded-xl text-sm font-bold hover:brightness-110 active:scale-95 transition-all flex items-center gap-2">
              <span class="material-symbols-outlined text-sm">block</span>
              Bannir
            </button>
            <button @click="viewUserDetails(user.id)"
                    class="px-4 py-2 border border-primary/20 text-primary rounded-xl text-sm font-bold hover:bg-primary hover:text-white active:scale-95 transition-all flex items-center gap-2">
              <span class="material-symbols-outlined text-sm">visibility</span>
              Détails
            </button>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="users.length > 0 && totalPages > 1" class="flex justify-center items-center gap-4 mt-8">
        <button @click="changePage(currentPage - 1)"
                :disabled="currentPage === 1"
                class="px-4 py-2 border border-primary/20 rounded-xl text-sm font-bold disabled:opacity-50 disabled:cursor-not-allowed hover:bg-primary hover:text-white transition-all">
          Précédent
        </button>
        <span class="text-sm font-medium">Page {{ currentPage }} sur {{ totalPages }}</span>
        <button @click="changePage(currentPage + 1)"
                :disabled="currentPage === totalPages"
                class="px-4 py-2 border border-primary/20 rounded-xl text-sm font-bold disabled:opacity-50 disabled:cursor-not-allowed hover:bg-primary hover:text-white transition-all">
          Suivant
        </button>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const users = ref([])
const loading = ref(false)
const error = ref(null)
const searchQuery = ref('')
const filterRole = ref('')
const filterStatus = ref('')
const currentPage = ref(1)
const totalPages = ref(1)

const pendingCount = computed(() => users.value.filter((user) => user.status === 'pending').length)

const debounce = (func, delay) => {
  let timeoutId
  return (...args) => {
    clearTimeout(timeoutId)
    timeoutId = setTimeout(() => func.apply(null, args), delay)
  }
}

const debouncedSearch = debounce(() => {
  currentPage.value = 1
  loadUsers()
}, 300)

const loadUsers = async () => {
  loading.value = true
  error.value = null

  try {
    const params = {
      page: currentPage.value,
      search: searchQuery.value,
      role: filterRole.value,
      status: filterStatus.value
    }

    const response = await axios.get('/api/admin/users', { params })
    users.value = response.data.users || []
    totalPages.value = response.data.total_pages || 1
  } catch (err) {
    console.error('Erreur lors du chargement des utilisateurs:', err)
    error.value = 'Erreur lors du chargement des utilisateurs'
    users.value = []
  } finally {
    loading.value = false
  }
}

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    loadUsers()
  }
}

const approveUser = async (userId) => {
  try {
    await axios.post(`/api/admin/users/${userId}/approve`)
    await loadUsers()
  } catch (error) {
    console.error("Erreur lors de l'approbation:", error)
  }
}

const rejectUser = async (userId) => {
  try {
    await axios.post(`/api/admin/users/${userId}/ban`)
    await loadUsers()
  } catch (error) {
    console.error('Erreur lors du rejet:', error)
  }
}

const viewUserDetails = (userId) => {
  console.log('Voir détails utilisateur:', userId)
}

const getRoleBadgeClass = (role) => {
  const classes = {
    freelancer: 'bg-primary/10 text-primary',
    client: 'bg-secondary/10 text-secondary',
    admin: 'bg-tertiary/10 text-tertiary'
  }
  return classes[role] || 'bg-gray-100 text-gray-600'
}

const getStatusBadgeClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    banned: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-600'
}

const getStatusLabel = (status) => {
  const labels = {
    pending: 'En attente',
    approved: 'Approuvé',
    banned: 'Banni'
  }
  return labels[status] || status
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

onMounted(() => {
  loadUsers()
})
</script>