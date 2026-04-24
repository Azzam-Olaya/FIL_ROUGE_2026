<template>
  <div class="min-h-screen bg-surface font-body text-on-surface flex flex-col overflow-x-hidden">
    <TopNavBar />

    <div class="flex flex-1 overflow-x-hidden">
      <Sidebar :activeTab="activeTab" @change-tab="tab => activeTab = tab" />

      <main class="ml-64 p-8 min-h-screen flex-1 relative bg-surface zellige-pattern">

        <!-- ── Dashboard ──────────────────────────────────────────────────── -->
        <div v-if="activeTab === 'dashboard'" class="space-y-8 pb-20">

          <header class="flex justify-between items-start">
            <div class="flex-1">
              <h1 class="font-headline font-bold text-5xl text-on-surface mb-2">
                Marhaba, {{ store.userName }}
              </h1>
              <p class="text-on-surface-variant font-medium text-lg italic">
                Mettez en valeur votre talent et trouvez de nouvelles opportunités
              </p>
            </div>
            <button @click="openNewBrief"
              class="flex items-center gap-2 bg-primary text-white px-6 py-3 rounded-full font-bold text-sm hover:scale-105 active:scale-95 transition-all shadow-xl shadow-primary/30 flex-shrink-0">
              <span class="material-symbols-outlined text-sm">add</span>
              +Nouveau brief
            </button>
          </header>

          <!-- Filters -->
          <div class="bg-white rounded-[2rem] border border-primary/10 shadow-sm p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <div>
                <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-2">Rechercher</label>
                <div class="relative">
                  <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
                  <input v-model="localSearch" @input="applyFilters" placeholder="Titre, description..."
                    class="w-full bg-surface-container border border-primary/10 rounded-xl pl-10 pr-3 py-2 text-xs focus:border-primary focus:outline-none transition-colors" />
                </div>
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-2">Catégorie</label>
                <select v-model="localCategory" @change="applyFilters"
                  class="w-full bg-surface-container border border-primary/10 rounded-xl px-3 py-2 text-xs focus:border-primary focus:outline-none transition-colors">
                  <option value="">Toutes les catégories</option>
                  <option value="design">Design</option>
                  <option value="dev">Développement</option>
                  <option value="marketing">Marketing</option>
                  <option value="construction">Construction</option>
                  <option value="other">Autres</option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-2">Budget max (DH)</label>
                <input v-model="maxBudget" @input="applyFilters" type="number" placeholder="Ex: 10000"
                  class="w-full bg-surface-container border border-primary/10 rounded-xl px-3 py-2 text-xs focus:border-primary focus:outline-none transition-colors" />
              </div>

              <div class="flex items-end">
                <button @click="resetFilters"
                  class="w-full px-4 py-2 rounded-xl border border-primary text-primary bg-primary/5 font-bold text-xs hover:bg-primary/10 transition-colors flex items-center justify-center gap-2">
                  <span class="material-symbols-outlined text-sm">filter_alt_off</span>
                  Réinitialiser
                </button>
              </div>
            </div>
          </div>

          <!-- Mission feed -->
          <section>
            <div class="flex items-baseline justify-between mb-6">
              <div>
                <h2 class="font-headline text-3xl font-bold text-on-surface">Missions recommandées</h2>
                <p class="text-sm text-on-surface-variant mt-1">
                  {{ filteredMissions.length }} mission{{ filteredMissions.length !== 1 ? 's' : '' }} trouvée{{ filteredMissions.length !== 1 ? 's' : '' }}
                </p>
              </div>
              <div v-if="loading" class="flex items-center gap-2 text-on-surface-variant">
                <span class="material-symbols-outlined text-sm animate-spin">progress_activity</span>
                <span class="text-sm">Chargement...</span>
              </div>
            </div>

            <div v-if="!loading && filteredMissions.length === 0"
              class="bg-surface-container-low p-12 rounded-[2.5rem] text-center border border-primary/5">
              <span class="material-symbols-outlined text-8xl text-on-surface-variant/30 mb-4" style="font-variation-settings: 'wght' 200">search_off</span>
              <h3 class="font-headline text-2xl font-bold text-on-surface mb-2">Aucune mission trouvée</h3>
              <button @click="resetFilters" class="mt-4 px-6 py-2 bg-surface text-primary rounded-full font-bold text-sm border border-primary/10 hover:bg-primary/5 transition-all">
                Réinitialiser les filtres
              </button>
            </div>

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

        <!-- ── Mes Briefs ─────────────────────────────────────────────────── -->
        <div v-if="activeTab === 'briefs'" class="px-4">
          <h1 class="font-headline font-bold text-4xl text-on-surface mb-2">Mes Briefs</h1>
          <p class="text-on-surface-variant text-sm mb-8">Briefs que vous avez publiés</p>
          <div class="bg-surface-container-low p-8 rounded-[2rem] text-center border border-primary/5">
            <span class="material-symbols-outlined text-5xl text-on-surface-variant/30 block mb-3">briefcase</span>
            <p class="text-on-surface-variant">Vous n'avez pas encore publié de briefs.</p>
          </div>
        </div>

        <!-- ── Messages ───────────────────────────────────────────────────── -->
        <div v-if="activeTab === 'messages'" class="px-4">
          <h1 class="font-headline font-bold text-4xl text-on-surface mb-2">Messages</h1>
          <p class="text-on-surface-variant text-sm mb-6">Vos conversations avec les clients</p>
          <MessagingPanel />
        </div>

        <!-- ── Paiements ──────────────────────────────────────────────────── -->
        <div v-if="activeTab === 'payments'" class="px-4">
          <h1 class="font-headline font-bold text-4xl text-on-surface mb-2">Paiements</h1>
          <p class="text-on-surface-variant text-sm mb-6">Historique et gestion des paiements</p>
          <PaymentTab role="freelancer" />
        </div>

        <!-- ── Contrats ───────────────────────────────────────────────────── -->
        <div v-if="activeTab === 'contracts'" class="px-4">
          <h1 class="font-headline font-bold text-4xl text-on-surface mb-2">Contrats</h1>
          <p class="text-on-surface-variant text-sm mb-6">Gestion de vos contrats</p>
          <ContractTab role="freelancer" />
        </div>

        <!-- ── Profil ─────────────────────────────────────────────────────── -->
        <div v-if="activeTab === 'profile'" class="px-4 max-w-2xl">
          <h1 class="font-headline font-bold text-4xl text-on-surface mb-2">Profil</h1>
          <p class="text-on-surface-variant text-sm mb-6">Configuration de votre page talent</p>
          <div class="bg-white rounded-[2rem] border border-primary/5 p-8 shadow-sm space-y-6">
            <div class="flex items-center gap-5">
              <div class="w-20 h-20 rounded-full bg-primary flex items-center justify-center text-white font-bold text-2xl shadow-xl shadow-primary/20">
                {{ store.userInitials }}
              </div>
              <div>
                <h2 class="font-bold text-2xl text-on-surface">{{ store.userName }}</h2>
                <p class="text-on-surface-variant text-sm">Freelancer · MorLancer</p>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div class="bg-surface-container rounded-2xl p-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-1">Missions favorites</p>
                <p class="font-black text-2xl text-primary">{{ store.favoritedMissions.length }}</p>
              </div>
              <div class="bg-surface-container rounded-2xl p-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-1">Notifications</p>
                <p class="font-black text-2xl text-primary">{{ store.notifications.length }}</p>
              </div>
            </div>
          </div>
        </div>

      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import TopNavBar     from '@/components/Common/TopNavBar.vue'
import Sidebar       from '@/components/Freelancer/Sidebar.vue'
import MissionCard   from '@/components/Freelancer/MissionCard.vue'
import MessagingPanel from '@/components/Common/MessagingPanel.vue'
import PaymentTab    from '@/components/Common/PaymentTab.vue'
import ContractTab   from '@/components/Common/ContractTab.vue'
import { useFreelancerStore } from '@/stores/freelancer'
import axios from 'axios'

const store     = useFreelancerStore()
const activeTab = ref('dashboard')
const loading   = ref(false)

const localSearch   = ref('')
const localCategory = ref('')
const maxBudget     = ref('')

const allMissions = ref([
  { id: 101, title: 'Design Logo Artisanat Moderne', description: 'Nous cherchons un designer pour concevoir un logo alliant les zelliges traditionnels et une typographie moderne.', budget: 2500, price: '2500', deadline: '10 Jours', categories: ['Design', 'Branding'], categorySlug: 'design', clientName: 'Boutique Atlas', client: { id: 1, name: 'Boutique Atlas' }, likes: 12, comments: 3, favorites_count: 2, commentsList: [] },
  { id: 102, title: 'Développement Site E-commerce', description: 'Mise en place d\'une boutique en ligne complète (frontend & backend) pour vendre de la poterie de Safi.', budget: 15000, price: '15000', deadline: '1 Mois', categories: ['Dev', 'E-commerce'], categorySlug: 'dev', clientName: 'Poterie Safi Exp', client: { id: 2, name: 'Poterie Safi Exp' }, likes: 45, comments: 8, favorites_count: 5, commentsList: [] },
  { id: 103, title: 'Campagne Marketing Réseaux Sociaux', description: 'Besoin d\'un expert pour gérer la promotion de nos tapis berbères sur Instagram.', budget: 5000, price: '5000', deadline: 'En continu', categories: ['Marketing', 'Ads'], categorySlug: 'marketing', clientName: 'Tissages du Haut', client: { id: 3, name: 'Tissages du Haut' }, likes: 22, comments: 1, favorites_count: 1, commentsList: [] },
  { id: 104, title: 'Application Mobile de Livraison', description: 'Nous recherchons un développeur Flutter/React Native pour une app de livraison spécialisée.', budget: 20000, price: '20000', deadline: '3 Mois', categories: ['Dev', 'Mobile'], categorySlug: 'dev', clientName: 'Atlas Deliver', client: { id: 4, name: 'Atlas Deliver' }, likes: 56, comments: 12, favorites_count: 8, commentsList: [] },
])

const filteredMissions = computed(() => {
  let result = allMissions.value
  const query = localSearch.value?.toLowerCase().trim()
  if (query) result = result.filter(m => m.title.toLowerCase().includes(query) || m.description.toLowerCase().includes(query))
  if (localCategory.value) result = result.filter(m => m.categorySlug === localCategory.value)
  if (maxBudget.value) result = result.filter(m => m.budget <= parseInt(maxBudget.value))
  return result
})

const applyFilters = () => {
  store.searchQuery    = localSearch.value
  store.searchCategory = localCategory.value
}

const resetFilters = () => {
  localSearch.value   = ''
  localCategory.value = ''
  maxBudget.value     = ''
  store.searchQuery    = ''
  store.searchCategory = ''
}

const loadMissions = async () => {
  loading.value = true
  try {
    const res = await axios.get('http://localhost:8000/api/freelancer/missions', { headers: store.authHeaders })
    if (res.data?.length > 0) allMissions.value = res.data
  } catch {
    // keep mock data
  } finally {
    loading.value = false
  }
}

const openNewBrief = () => {
  alert('Fonctionnalité de création de brief bientôt disponible')
}

onMounted(() => {
  store.startPolling()
  loadMissions()
})
</script>

<style scoped>
.zellige-pattern {
  background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M30 0l5 25h25l-20 15 8 20-18-12-18 12 8-20L0 25h25z' fill='%23006233' fill-opacity='0.03' fill-rule='evenodd'/%3E%3C/svg%3E");
}
</style>
