<template>
  <div class="min-h-screen bg-surface font-body text-on-surface flex flex-col overflow-x-hidden">

    <TopNavBarClient @search="onNavSearch" @toggle-sidebar="sidebarOpen = !sidebarOpen" />

    <div class="flex flex-1 pt-16">
      <ClientSidebar 
        :activeTab="activeTab" 
        :isOpen="sidebarOpen"
        @change-tab="tab => activeTab = tab" 
        @close="sidebarOpen = false"
      />

      <main class="lg:ml-64 flex-1 p-4 md:p-8 min-h-screen bg-surface zellige-pattern transition-all duration-300">

        <!-- ── Dashboard ──────────────────────────────────────────────────── -->
        <div v-if="activeTab === 'dashboard'" class="space-y-8 pb-20">

          <!-- Greeting -->
          <header class="flex flex-col sm:flex-row justify-between items-start gap-4">
            <div class="flex-1">
              <h1 class="font-headline font-bold text-3xl md:text-4xl text-on-surface mb-1">
                Marhba <span class="text-primary">{{ store.userName }}</span> 👋
              </h1>
              <p class="text-on-surface-variant font-medium text-sm md:text-base italic">
                Transformez vos idées en succès avec des freelances expérimentés
              </p>
            </div>
            <button @click="showNewBriefModal = true"
              class="w-full sm:w-auto flex items-center justify-center gap-2 bg-primary text-white px-6 py-3 rounded-full font-bold text-sm hover:scale-105 active:scale-95 transition-all shadow-xl shadow-primary/30">
              <span class="material-symbols-outlined text-sm">add</span>Nouvelle mission
            </button>
          </header>

          <!-- Briefs Info / Summary -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="bg-white p-6 rounded-[2rem] border border-primary/5 shadow-sm hover:shadow-md transition-shadow">
              <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-2xl bg-primary/10 flex items-center justify-center text-primary">
                  <span class="material-symbols-outlined">explore</span>
                </div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Exploration</p>
              </div>
              <p class="text-3xl font-black text-on-surface">{{ briefs.length }} <span class="text-xs font-medium text-on-surface-variant uppercase ml-1">Briefs dispo.</span></p>
            </div>

            <div class="bg-white p-6 rounded-[2rem] border border-primary/5 shadow-sm hover:shadow-md transition-shadow">
              <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-2xl bg-secondary/10 flex items-center justify-center text-secondary">
                  <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1">favorite</span>
                </div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Favoris</p>
              </div>
              <p class="text-3xl font-black text-on-surface">{{ store.favoritedBriefs.length }} <span class="text-xs font-medium text-on-surface-variant uppercase ml-1">Enregistrés</span></p>
            </div>

            <div class="bg-white p-6 rounded-[2rem] border border-primary/5 shadow-sm hover:shadow-md transition-shadow hidden lg:block">
              <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-2xl bg-yellow-100 flex items-center justify-center text-yellow-700">
                  <span class="material-symbols-outlined">trending_up</span>
                </div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Tendance</p>
              </div>
              <p class="text-3xl font-black text-on-surface">Digital <span class="text-xs font-medium text-on-surface-variant uppercase ml-1">Demande forte</span></p>
            </div>
          </div>

          <!-- Brief feed -->
          <div v-if="!loading && briefs.length === 0" class="text-center py-20 bg-white rounded-[2rem] border border-primary/5">
            <span class="material-symbols-outlined text-5xl text-gray-300 block mb-3">search_off</span>
            <p class="text-gray-400 font-medium">Aucun brief ne correspond à votre recherche.</p>
            <button @click="resetFilters" class="mt-4 text-primary text-sm font-bold hover:underline">Effacer les filtres</button>
          </div>
          <div v-else class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <BriefCard v-for="brief in briefs" :key="brief.id" :brief="brief" />
          </div>
        </div>

        <!-- ── Mes Missions ───────────────────────────────────────────────── -->
        <div v-if="activeTab === 'briefs'" class="space-y-6">
          <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
              <h1 class="font-headline font-bold text-3xl md:text-4xl text-on-surface mb-1">Mes Missions</h1>
              <p class="text-on-surface-variant text-sm">Missions que vous avez publiées</p>
            </div>
            <button @click="showNewBriefModal = true"
              class="w-full sm:w-auto flex items-center justify-center gap-2 bg-primary text-white px-5 py-2.5 rounded-full font-bold text-sm hover:scale-105 transition-all shadow-lg shadow-primary/20">
              <span class="material-symbols-outlined text-sm">add</span>Nouvelle mission
            </button>
          </div>
          <div v-if="loadingMyMissions" class="flex justify-center py-16">
            <span class="material-symbols-outlined text-4xl text-primary/30 animate-spin">progress_activity</span>
          </div>
          <div v-else-if="!myMissions.length" class="text-center py-20 bg-white rounded-[2rem] border border-primary/5">
            <span class="material-symbols-outlined text-5xl text-gray-300 block mb-3">work_outline</span>
            <p class="text-gray-400 font-medium">Vous n'avez pas encore publié de missions.</p>
            <button @click="showNewBriefModal = true" class="mt-4 text-primary text-sm font-bold hover:underline">Créer votre première mission</button>
          </div>
          <div v-else class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <MyMissionCard v-for="m in myMissions" :key="m.id" :mission="m" />
          </div>
        </div>

        <!-- ── Messages ───────────────────────────────────────────────────── -->
        <div v-if="activeTab === 'messages'" class="px-0 md:px-4">
          <h1 class="font-headline font-bold text-3xl md:text-4xl text-on-surface mb-2">Messages</h1>
          <p class="text-on-surface-variant text-sm mb-6">Vos conversations avec les freelancers</p>
          <MessagingPanel :autoOpen="pendingConversation" @opened="pendingConversation = null" />
        </div>

        <!-- ── Paiements ──────────────────────────────────────────────────── -->
        <div v-if="activeTab === 'payments'" class="px-0 md:px-4">
          <h1 class="font-headline font-bold text-3xl md:text-4xl text-on-surface mb-2">Paiements</h1>
          <p class="text-on-surface-variant text-sm mb-6">Historique de vos paiements</p>
          <PaymentTab role="client" />
        </div>

        <!-- ── Contrats ───────────────────────────────────────────────────── -->
        <div v-if="activeTab === 'contracts'" class="px-0 md:px-4">
          <h1 class="font-headline font-bold text-3xl md:text-4xl text-on-surface mb-2">Contrats</h1>
          <p class="text-on-surface-variant text-sm mb-6">Gestion de vos contrats</p>
          <ContractTab role="client" />
        </div>

        <!-- ── Profil ─────────────────────────────────────────────────────── -->
        <div v-if="activeTab === 'profile'" class="max-w-2xl space-y-6 px-0 md:px-4">
          <h1 class="font-headline font-bold text-3xl md:text-4xl text-on-surface mb-2">Profil</h1>
          <div class="bg-white rounded-[2rem] border border-primary/5 p-6 md:p-8 shadow-sm space-y-6">
            <div class="flex items-center gap-5">
              <div class="w-16 h-16 md:w-20 md:h-20 rounded-full bg-primary flex items-center justify-center text-white font-bold text-xl md:text-2xl shadow-xl shadow-primary/20">
                {{ store.userInitials }}
              </div>
              <div>
                <h2 class="font-bold text-xl md:text-2xl text-on-surface">{{ store.userName }}</h2>
                <p class="text-on-surface-variant text-sm">Client · MorLancer</p>
              </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="bg-surface-container rounded-2xl p-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-1">Missions publiées</p>
                <p class="font-black text-2xl text-primary">{{ myMissions.length }}</p>
              </div>
              <div class="bg-surface-container rounded-2xl p-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-1">Briefs favoris</p>
                <p class="font-black text-2xl text-primary">{{ store.favoritedBriefs.length }}</p>
              </div>
            </div>
          </div>
        </div>

      </main>
    </div>

    <!-- + Nouveau Brief Modal -->
    <Teleport to="body">
      <div v-if="showNewBriefModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-2 md:p-4" @click.self="showNewBriefModal = false">
        <div class="bg-white rounded-[2rem] shadow-2xl max-w-lg w-full p-6 md:p-8 animate-in max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between mb-6">
            <h2 class="font-headline text-xl md:text-2xl font-bold text-on-surface">Nouvelle Mission</h2>
            <button @click="showNewBriefModal = false" class="p-2 hover:bg-primary/10 rounded-full transition-colors">
              <span class="material-symbols-outlined">close</span>
            </button>
          </div>
          <form @submit.prevent="submitBrief" class="space-y-4">
            <div>
              <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-1">Titre *</label>
              <input v-model="newBrief.title" required placeholder="Ex: Développement site e-commerce"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary transition-colors" />
            </div>
            <div>
              <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-1">Description *</label>
              <textarea v-model="newBrief.description" required rows="3" md:rows="4" placeholder="Décrivez votre besoin..."
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary transition-colors resize-none" />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-1">Budget (DH) *</label>
                <input v-model="newBrief.budget" type="number" min="0" required placeholder="5000"
                  class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary transition-colors" />
              </div>
              <div>
                <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-1">Deadline *</label>
                <input v-model="newBrief.deadline" type="date" required :min="minDate"
                  class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary transition-colors" />
              </div>
            </div>
            <div>
              <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-2">Choisir les catégories parentes *</label>
              <div class="flex flex-wrap gap-2 mb-4">
                <button type="button" v-for="c in rootCategories" :key="c.id" 
                  @click="toggleParentCategory(c.id)"
                  :class="newBrief.parent_ids.includes(c.id) ? 'bg-primary text-white shadow-lg shadow-primary/25' : 'bg-surface-container text-on-surface-variant border border-primary/10'"
                  class="px-5 py-2 rounded-full text-xs font-bold transition-all hover:scale-105 active:scale-95">
                  {{ c.name }}
                </button>
              </div>
            </div>
            <div v-if="modalSubCategories.length">
              <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-2">Choisir les compétences spécifiques (tags)</label>
              <div class="flex flex-wrap gap-2 max-h-40 overflow-y-auto p-1">
                <button type="button" v-for="c in modalSubCategories" :key="c.id" 
                  @click="toggleSubCategory(c.id)"
                  :class="newBrief.category_ids.includes(c.id) ? 'bg-primary text-white' : 'bg-surface-container text-on-surface-variant border border-primary/10'"
                  class="px-4 py-1.5 rounded-full text-xs font-bold transition-all hover:scale-105 active:scale-95">
                  {{ c.name }}
                </button>
              </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 pt-2">
              <button type="button" @click="showNewBriefModal = false"
                class="order-2 sm:order-1 px-4 py-2.5 rounded-full border border-primary text-primary font-bold text-sm hover:bg-primary/5 transition-colors">Annuler</button>
              <button type="submit" :disabled="submittingBrief"
                class="order-1 sm:order-2 px-4 py-2.5 rounded-full bg-primary text-white font-bold text-sm disabled:opacity-50 hover:scale-105 transition-transform flex items-center justify-center gap-2">
                <span v-if="submittingBrief" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                <span v-else>Publier</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import TopNavBarClient from '@/components/Common/TopNavBarClient.vue'
import ClientSidebar   from '@/components/Client/Sidebar.vue'
import BriefCard       from '@/components/Client/BriefCard.vue'
import MyMissionCard   from '@/components/Client/MyMissionCard.vue'
import MessagingPanel  from '@/components/Common/MessagingPanel.vue'
import PaymentTab      from '@/components/Common/PaymentTab.vue'
import ContractTab     from '@/components/Common/ContractTab.vue'
import { useClientStore } from '@/stores/client'
import api from '@/api/axios'

const store        = useClientStore()
const route        = useRoute()
const activeTab    = ref(route.query.tab || 'dashboard')
const sidebarOpen  = ref(false)

// ── Brief feed ──────────────────────────────────────────────────────────────
const briefs     = ref([])
const categories = ref([])
const loading    = ref(false)
let debounceTimer = null

const filters = ref({ search: '', category_id: null, sub_category_id: null })

const rootCategories = computed(() => categories.value.filter(c => !c.parent_id))
const subCategories  = computed(() => filters.value.category_id
  ? categories.value.filter(c => c.parent_id === filters.value.category_id) : [])
const allCategories  = computed(() => categories.value)

const onCategoryChange = () => { filters.value.sub_category_id = null; loadBriefs() }

const loadBriefs = async () => {
  loading.value = true
  try {
    const params = {}
    if (filters.value.search)           params.search = filters.value.search
    if (filters.value.sub_category_id)  params.category_id = filters.value.sub_category_id
    else if (filters.value.category_id) params.category_id = filters.value.category_id
    const res = await api.get('/client/briefs', { params })
    briefs.value = res.data
  } catch {
    briefs.value = [
      { id: 1, title: 'Expert Zellige & Design Intérieur', description: 'Spécialiste en création de motifs Zellige traditionnels pour projets résidentiels et commerciaux haut de gamme.', category: { name: 'Design' }, freelancer: { id: 10, name: 'Yasmine B.' }, likes_count: 42, comments_count: 3, favorites_count: 8, price: 8000, timeline: 'Disponible immédiatement' },
      { id: 2, title: 'Développeur Full-Stack Laravel/Vue', description: 'Expert Laravel + Vue.js avec 5 ans d\'expérience. Disponible pour projets web complexes et applications SaaS.', category: { name: 'Développement' }, freelancer: { id: 11, name: 'Omar K.' }, likes_count: 65, comments_count: 5, favorites_count: 12, price: 15000, timeline: '2 semaines' },
      { id: 3, title: 'Photographe & Vidéaste Professionnel', description: 'Couverture d\'événements, shooting produits, vidéos promotionnelles. Équipement haut de gamme.', category: { name: 'Médias' }, freelancer: { id: 12, name: 'Sara M.' }, likes_count: 28, comments_count: 2, favorites_count: 5, price: 5000, timeline: 'Flexible' },
      { id: 4, title: 'Expert Marketing Digital & SEO', description: 'Stratégie digitale, gestion réseaux sociaux, SEO/SEM. Résultats mesurables garantis sous 3 mois.', category: { name: 'Marketing' }, freelancer: { id: 13, name: 'Karim A.' }, likes_count: 33, comments_count: 7, favorites_count: 9, price: 6000, timeline: 'En continu' },
    ]
  } finally { loading.value = false }
}

const loadCategories = async () => {
  try {
    const res = await api.get('/client/categories')
    categories.value = res.data
  } catch { categories.value = [] }
}

const debouncedLoad = () => { clearTimeout(debounceTimer); debounceTimer = setTimeout(loadBriefs, 400) }
const resetFilters  = () => { filters.value = { search: '', category_id: null, sub_category_id: null }; loadBriefs() }

const onNavSearch = ({ query, category }) => {
  filters.value.search      = query
  filters.value.category_id = category || null
  loadBriefs()
}

// ── My Missions ─────────────────────────────────────────────────────────────
const myMissions        = ref([])
const loadingMyMissions = ref(false)

const loadMyMissions = async () => {
  loadingMyMissions.value = true
  try {
    const res = await api.get('/client/missions')
    myMissions.value = res.data
  } catch { myMissions.value = [] } finally { loadingMyMissions.value = false }
}

// ── New Brief Modal ──────────────────────────────────────────────────────────
const showNewBriefModal = ref(false)
const submittingBrief   = ref(false)
const newBrief = ref({ title: '', description: '', budget: '', deadline: '', parent_ids: [], category_ids: [] })
const minDate  = new Date(Date.now() + 86400000).toISOString().split('T')[0]

const modalSubCategories  = computed(() => {
  if (!newBrief.value.parent_ids.length) return []
  return categories.value.filter(c => newBrief.value.parent_ids.includes(c.parent_id))
})

const toggleParentCategory = (id) => {
  const idx = newBrief.value.parent_ids.indexOf(id)
  if (idx > -1) {
    newBrief.value.parent_ids.splice(idx, 1)
    // Also remove children of this parent from category_ids
    const childrenIds = categories.value.filter(c => c.parent_id === id).map(c => c.id)
    newBrief.value.category_ids = newBrief.value.category_ids.filter(cid => !childrenIds.includes(cid))
  } else {
    newBrief.value.parent_ids.push(id)
  }
}

const toggleSubCategory = (id) => {
  const idx = newBrief.value.category_ids.indexOf(id)
  if (idx > -1) newBrief.value.category_ids.splice(idx, 1)
  else newBrief.value.category_ids.push(id)
}

const submitBrief = async () => {
  if (!newBrief.value.parent_ids.length) {
    alert('Veuillez sélectionner au moins une catégorie parente')
    return
  }
  submittingBrief.value = true
  try {
    const payload = {
      ...newBrief.value,
      category_id: newBrief.value.parent_ids[0], // Primary category
      // Combine parents and children for the sync
      category_ids: [...newBrief.value.parent_ids, ...newBrief.value.category_ids]
    }
    await api.post('/client/missions', payload)
    showNewBriefModal.value = false
    newBrief.value = { title: '', description: '', budget: '', deadline: '', parent_ids: [], category_ids: [] }
    loadMyMissions()
    activeTab.value = 'briefs'
  } catch (e) {
    alert(e.response?.data?.message || 'Erreur lors de la publication')
  } finally { submittingBrief.value = false }
}

// Listen for tab changes from navbar profile menu or BriefCard contact button
const pendingConversation = ref(null)
const handleTabEvent = (e) => { activeTab.value = e.detail }
const handleOpenConv  = (e) => { pendingConversation.value = e.detail; activeTab.value = 'messages' }

watch(() => route.query.tab, (t) => { if (t) activeTab.value = t })
watch(activeTab, (t) => { if (t === 'briefs') loadMyMissions() })

onMounted(async () => {
  await loadCategories()
  await loadBriefs()
  loadMyMissions()
  window.addEventListener('client-tab', handleTabEvent)
  window.addEventListener('open-conversation', handleOpenConv)
})
onUnmounted(() => {
  window.removeEventListener('client-tab', handleTabEvent)
  window.removeEventListener('open-conversation', handleOpenConv)
})
</script>

<style scoped>
@keyframes slideIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.animate-in { animation: slideIn 0.25s ease-out; }
.zellige-pattern {
  background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M30 0l5 25h25l-20 15 8 20-18-12-18 12 8-20L0 25h25z' fill='%23006233' fill-opacity='0.03' fill-rule='evenodd'/%3E%3C/svg%3E");
}
</style>
