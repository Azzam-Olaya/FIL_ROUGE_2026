<template>
  <div class="min-h-screen bg-surface font-body text-on-surface flex flex-col overflow-x-hidden">
    <!-- TopNavBar modulaire -->
    <TopNavBar />

    <div class="flex flex-1 overflow-x-hidden">
      <!-- Barre Latérale -->
      <Sidebar :activeTab="activeTab" @change-tab="tab => activeTab = tab" />

      <!-- Contenu Principal -->
      <main class="ml-64 p-8 min-h-screen flex-1 relative bg-surface zellige-pattern">
        
        <!-- ── Onglet : Dashboard (Flux de missions) ─────────────────────── -->
        <div v-if="activeTab === 'dashboard'" class="space-y-8 pb-20">
          <!-- Greeting Header -->
          <header class="flex justify-between items-start">
            <div class="flex-1">
              <h1 class="font-headline font-bold text-5xl text-on-surface mb-2">
                Marhaba, {{ store.userName || 'Freelancer' }}
              </h1>
              <p class="text-on-surface-variant font-medium text-lg italic">
                Mettez en valeur votre talent et trouvez de nouvelles opportunités
              </p>
            </div>
            <button @click="openNewBrief" class="flex items-center gap-2 bg-primary text-white px-6 py-3 rounded-full font-bold text-sm hover:scale-105 active:scale-95 transition-all shadow-xl shadow-primary/30 flex-shrink-0">
              <span class="material-symbols-outlined text-sm">add</span>
              +Nouveau brief
            </button>
          </header>

          <!-- Search & Filter Section -->
          <div class="bg-white rounded-[2rem] border border-primary/10 shadow-sm p-6 space-y-4">
            <h3 class="font-headline text-sm font-bold text-on-surface uppercase tracking-widest">Filtrer et chercher</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <!-- Search input -->
              <div>
                <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-2">Rechercher</label>
                <div class="relative">
                  <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
                  <input
                    v-model="localSearch"
                    @input="applyFilters"
                    placeholder="Titre, description..."
                    class="w-full bg-surface-container border border-primary/10 rounded-xl pl-10 pr-3 py-2 text-xs focus:border-primary focus:outline-none transition-colors"
                  />
                </div>
              </div>

              <!-- Category filter -->
              <div>
                <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-2">Catégorie</label>
                <select
                  v-model="localCategory"
                  @change="applyFilters"
                  class="w-full bg-surface-container border border-primary/10 rounded-xl px-3 py-2 text-xs focus:border-primary focus:outline-none transition-colors"
                >
                  <option value="">Toutes les catégories</option>
                  <option value="design">Design</option>
                  <option value="dev">Développement</option>
                  <option value="marketing">Marketing</option>
                  <option value="construction">Construction</option>
                  <option value="other">Autres</option>
                </select>
              </div>

              <!-- Budget filter -->
              <div>
                <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-2">Budget max (DH)</label>
                <input
                  v-model="maxBudget"
                  @input="applyFilters"
                  type="number"
                  placeholder="Ex: 10000"
                  class="w-full bg-surface-container border border-primary/10 rounded-xl px-3 py-2 text-xs focus:border-primary focus:outline-none transition-colors"
                />
              </div>

              <!-- Reset button -->
              <div class="flex items-end">
                <button
                  @click="resetFilters"
                  class="w-full px-4 py-2 rounded-xl border border-primary text-primary bg-primary/5 font-bold text-xs hover:bg-primary/10 transition-colors flex items-center justify-center gap-2"
                >
                  <span class="material-symbols-outlined text-sm">filter_alt_off</span>
                  Réinitialiser
                </button>
              </div>
            </div>
          </div>

          <!-- Missions Section -->
          <section>
            <div class="flex items-baseline justify-between mb-6">
              <div>
                <h2 class="font-headline text-3xl font-bold text-on-surface">Missions recommandées</h2>
                <p class="text-sm text-on-surface-variant mt-1">{{ filteredMissions.length }} mission{{ filteredMissions.length !== 1 ? 's' : '' }} trouvée{{ filteredMissions.length !== 1 ? 's' : '' }}</p>
              </div>
              <div v-if="loading" class="flex items-center gap-2 text-on-surface-variant">
                <span class="material-symbols-outlined text-sm animate-spin">progress_activity</span>
                <span class="text-sm">Chargement...</span>
              </div>
            </div>
            
            <!-- No results state -->
            <div v-if="!loading && filteredMissions.length === 0" class="bg-surface-container-low p-12 rounded-[2.5rem] text-center border border-primary/5">
              <span class="material-symbols-outlined text-8xl text-on-surface-variant/30 mb-4" style="font-variation-settings: 'wght' 200">search_off</span>
              <h3 class="font-headline text-2xl font-bold text-on-surface mb-2">Aucune mission trouvée</h3>
              <p class="text-on-surface-variant font-medium mb-6">Essayez d'ajuster vos critères de recherche ou de retirer les filtres.</p>
              <button @click="resetFilters" class="px-6 py-2 bg-surface text-primary rounded-full font-bold text-sm shadow-sm border border-primary/10 hover:bg-primary/5 transition-all">
                Réinitialiser les filtres
              </button>
            </div>

            <!-- Missions Grid -->
            <div v-else class="grid grid-cols-1 gap-6">
              <MissionCard 
                v-for="mission in filteredMissions" 
                :key="mission.id" 
                :mission="mission"
                @mission-updated="loadMissions"
              />
            </div>
          </section>
        </div>

        <!-- ── Onglet : Mes Briefs ─────────────────────────────────────────── -->
        <div v-if="activeTab === 'briefs'" class="px-4">
          <h1 class="font-headline font-bold text-4xl text-on-surface mb-2">Mes Briefs</h1>
          <p class="text-on-surface-variant text-sm mb-8">Briefs que vous avez publiés pour d'autres freelancers</p>
          <div class="bg-surface-container-low p-8 rounded-[2rem] text-center border border-primary/5">
            <span class="material-symbols-outlined text-5xl text-on-surface-variant/30 block mb-3">briefcase</span>
            <p class="text-on-surface-variant">Vous n'avez pas encore publié de briefs.</p>
          </div>
        </div>

        <!-- ── Onglet : Messages ───────────────────────────────────────────── -->
        <div v-if="activeTab === 'messages'" class="px-4">
          <h1 class="font-headline font-bold text-4xl text-on-surface mb-2">Messages</h1>
          <p class="text-on-surface-variant text-sm mb-8">Vos conversations avec les clients</p>
          <div class="bg-surface-container-low p-8 rounded-[2rem] text-center border border-primary/5">
            <span class="material-symbols-outlined text-5xl text-on-surface-variant/30 block mb-3">chat_bubble</span>
            <p class="text-on-surface-variant">Aucun message pour le moment.</p>
          </div>
        </div>

        <!-- ── Onglet : Paiements ──────────────────────────────────────────── -->
        <div v-if="activeTab === 'payments'" class="px-4">
          <h1 class="font-headline font-bold text-4xl text-on-surface mb-2">Paiements</h1>
          <p class="text-on-surface-variant text-sm mb-8">Historique et gestion des paiements</p>
          <div class="bg-surface-container-low p-8 rounded-[2rem] text-center border border-primary/5">
            <span class="material-symbols-outlined text-5xl text-on-surface-variant/30 block mb-3">payments</span>
            <p class="text-on-surface-variant">Aucun paiement enregistré.</p>
          </div>
        </div>

        <!-- ── Onglet : Contrats ───────────────────────────────────────────── -->
        <div v-if="activeTab === 'contracts'" class="px-4">
          <h1 class="font-headline font-bold text-4xl text-on-surface mb-2">Contrats</h1>
          <p class="text-on-surface-variant text-sm mb-8">Gestion de vos contrats en cours et passés</p>
          <div class="bg-surface-container-low p-8 rounded-[2rem] text-center border border-primary/5">
            <span class="material-symbols-outlined text-5xl text-on-surface-variant/30 block mb-3">description</span>
            <p class="text-on-surface-variant">Aucun contrat en cours.</p>
          </div>
        </div>

        <!-- ── Onglet : Profil ─────────────────────────────────────────────── -->
        <div v-if="activeTab === 'profile'" class="px-4">
          <h1 class="font-headline font-bold text-4xl text-on-surface mb-2">Profil</h1>
          <p class="text-on-surface-variant text-sm mb-8">Configuration de votre page talent</p>
          <div class="bg-surface-container-low p-8 rounded-[2rem] text-center border border-primary/5">
            <span class="material-symbols-outlined text-5xl text-on-surface-variant/30 block mb-3">person</span>
            <p class="text-on-surface-variant">Gérez votre profil professionnel.</p>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import TopNavBar from '@/components/Common/TopNavBar.vue'
import Sidebar from '@/components/Freelancer/Sidebar.vue'
import MissionCard from '@/components/Freelancer/MissionCard.vue'
import { useFreelancerStore } from '@/stores/freelancer'
import axios from 'axios'

const store = useFreelancerStore()
const activeTab = ref('dashboard')
const loading = ref(false)

// Local search/filter state
const localSearch = ref('')
const localCategory = ref('')
const maxBudget = ref('')

// All missions from API
const allMissions = ref([
  {
    id: 101,
    title: 'Design Logo Artisanat Moderne',
    description: 'Nous cherchons un designer pour concevoir un logo alliant les zelliges traditionnels et une typographie moderne pour notre nouvelle marque de vêtements.',
    budget: 2500,
    price: '2500',
    deadline: '10 Jours',
    categories: ['Design', 'Branding'],
    categorySlug: 'design',
    clientName: 'Boutique Atlas',
    client: { id: 1, name: 'Boutique Atlas' },
    likes: 12,
    comments: 3,
    commentsList: [
      { author: 'Yasmine B.', text: 'Est-ce que vous avez des couleurs préférées ?' }
    ]
  },
  {
    id: 102,
    title: 'Développement Site E-commerce',
    description: 'Mise en place d\'une boutique en ligne complète (frontend & backend) pour vendre de la poterie de Safi à l\'international.',
    budget: 15000,
    price: '15000',
    deadline: '1 Mois',
    categories: ['Dev', 'E-commerce'],
    categorySlug: 'dev',
    clientName: 'Poterie Safi Exp',
    client: { id: 2, name: 'Poterie Safi Exp' },
    likes: 45,
    comments: 8,
    commentsList: []
  },
  {
    id: 103,
    title: 'Campagne Marketing Réseaux Sociaux',
    description: 'Besoin d\'un expert pour gérer la promotion de nos tapis berbères sur Instagram. Création de contenu et publicités.',
    budget: 5000,
    price: '5000',
    deadline: 'En continu',
    categories: ['Marketing', 'Ads'],
    categorySlug: 'marketing',
    clientName: 'Tissages du Haut',
    client: { id: 3, name: 'Tissages du Haut' },
    likes: 22,
    comments: 1,
    commentsList: []
  },
  {
    id: 104,
    title: 'Application Mobile de Livraison',
    description: 'Nous recherchons un développeur Flutter/React Native pour une app de livraison spécialisée dans l\'artisanat lourd.',
    budget: 20000,
    price: '20000',
    deadline: '3 Mois',
    categories: ['Dev', 'Mobile'],
    categorySlug: 'dev',
    clientName: 'Atlas Deliver',
    client: { id: 4, name: 'Atlas Deliver' },
    likes: 56,
    comments: 12,
    commentsList: []
  }
])

// Filtered missions based on search/filters
const filteredMissions = computed(() => {
  let result = allMissions.value

  const query = localSearch.value?.toLowerCase().trim()
  if (query) {
    result = result.filter(m => 
      m.title.toLowerCase().includes(query) || 
      m.description.toLowerCase().includes(query)
    )
  }

  const category = localCategory.value
  if (category) {
    result = result.filter(m => m.categorySlug === category)
  }

  if (maxBudget.value) {
    const max = parseInt(maxBudget.value)
    result = result.filter(m => m.budget <= max)
  }

  return result
})

// Apply filters
const applyFilters = () => {
  store.searchQuery = localSearch.value
  store.searchCategory = localCategory.value
}

// Reset all filters
const resetFilters = () => {
  localSearch.value = ''
  localCategory.value = ''
  maxBudget.value = ''
  store.searchQuery = ''
  store.searchCategory = ''
}

// Load missions from API
const loadMissions = async () => {
  loading.value = true
  try {
    const response = await axios.get(
      'http://localhost:8000/api/freelancer/missions',
      { headers: store.authHeaders }
    )
    // If API returns data, use it; otherwise use mock data
    if (response.data && response.data.length > 0) {
      allMissions.value = response.data
    }
  } catch (error) {
    console.log('Using mock missions (API not available)')
  } finally {
    loading.value = false
  }
}

// Open new brief modal
const openNewBrief = () => {
  alert('Nouvelle fonctionnalité de création de brief non encore disponible')
}

// Load missions on mount
onMounted(() => {
  loadMissions()
})
</script>

<style scoped>
.zellige-pattern {
  background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M30 0l5 25h25l-20 15 8 20-18-12-18 12 8-20L0 25h25z' fill='%23006233' fill-opacity='0.03' fill-rule='evenodd'/%3E%3C/svg%3E");
}
</style>
