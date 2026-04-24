<template>
  <div class="min-h-screen bg-surface font-body text-on-surface flex flex-col overflow-x-hidden">

    <TopNavBarClient @search="onNavSearch" />

    <div class="flex flex-1 pt-16">
      <ClientSidebar :activeTab="activeTab" @change-tab="tab => activeTab = tab" />

      <main class="ml-64 flex-1 p-8 min-h-screen bg-surface zellige-pattern">

        <!-- ── Dashboard ──────────────────────────────────────────────────── -->
        <div v-if="activeTab === 'dashboard'" class="space-y-8 pb-20">

          <!-- Greeting -->
          <header>
            <h1 class="font-headline font-bold text-4xl text-on-surface mb-1">
              Marhba <span class="text-primary">{{ store.userName }}</span> 👋
            </h1>
            <p class="text-on-surface-variant font-medium text-base italic">
              Transformez vos idées en succès avec des freelances expérimentés
            </p>
          </header>

          <!-- + Nouveau brief + count -->
          <div class="flex items-center justify-between">
            <button @click="showNewBriefModal = true"
              class="flex items-center gap-2 bg-primary text-white px-6 py-3 rounded-full font-bold text-sm hover:scale-105 active:scale-95 transition-all shadow-xl shadow-primary/30">
              <span class="material-symbols-outlined text-sm">add</span>Nouvelle mission
            </button>
            <div class="flex items-center gap-3">
              <p class="text-sm text-gray-500"><span class="font-bold text-primary">{{ briefs.length }}</span> mission(s)</p>
              <span v-if="loading" class="material-symbols-outlined text-sm text-gray-400 animate-spin">progress_activity</span>
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
          <div class="flex items-center justify-between">
            <div>
              <h1 class="font-headline font-bold text-4xl text-on-surface mb-1">Mes Missions</h1>
              <p class="text-on-surface-variant text-sm">Missions que vous avez publiées</p>
            </div>
            <button @click="showNewBriefModal = true"
              class="flex items-center gap-2 bg-primary text-white px-5 py-2.5 rounded-full font-bold text-sm hover:scale-105 transition-all shadow-lg shadow-primary/20">
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
        <div v-if="activeTab === 'messages'">
          <h1 class="font-headline font-bold text-4xl text-on-surface mb-2">Messages</h1>
          <p class="text-on-surface-variant text-sm mb-6">Vos conversations avec les freelancers</p>
          <MessagingPanel />
        </div>

        <!-- ── Paiements ──────────────────────────────────────────────────── -->
        <div v-if="activeTab === 'payments'">
          <h1 class="font-headline font-bold text-4xl text-on-surface mb-2">Paiements</h1>
          <p class="text-on-surface-variant text-sm mb-6">Historique de vos paiements</p>
          <PaymentTab role="client" />
        </div>

        <!-- ── Contrats ───────────────────────────────────────────────────── -->
        <div v-if="activeTab === 'contracts'">
          <h1 class="font-headline font-bold text-4xl text-on-surface mb-2">Contrats</h1>
          <p class="text-on-surface-variant text-sm mb-6">Gestion de vos contrats</p>
          <ContractTab role="client" />
        </div>

        <!-- ── Profil ─────────────────────────────────────────────────────── -->
        <div v-if="activeTab === 'profile'" class="max-w-2xl space-y-6">
          <h1 class="font-headline font-bold text-4xl text-on-surface mb-2">Profil</h1>
          <div class="bg-white rounded-[2rem] border border-primary/5 p-8 shadow-sm space-y-6">
            <div class="flex items-center gap-5">
              <div class="w-20 h-20 rounded-full bg-primary flex items-center justify-center text-white font-bold text-2xl shadow-xl shadow-primary/20">
                {{ store.userInitials }}
              </div>
              <div>
                <h2 class="font-bold text-2xl text-on-surface">{{ store.userName }}</h2>
                <p class="text-on-surface-variant text-sm">Client · MorLancer</p>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
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
      <div v-if="showNewBriefModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="showNewBriefModal = false">
        <div class="bg-white rounded-[2rem] shadow-2xl max-w-lg w-full p-8 animate-in">
          <div class="flex items-center justify-between mb-6">
            <h2 class="font-headline text-2xl font-bold text-on-surface">Nouvelle Mission</h2>
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
              <textarea v-model="newBrief.description" required rows="3" placeholder="Décrivez votre besoin..."
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary transition-colors resize-none" />
            </div>
            <div class="grid grid-cols-2 gap-4">
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
              <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-1">Catégorie *</label>
              <select v-model="newBrief.category_id" required @change="onModalCategoryChange"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary transition-colors">
                <option value="">Sélectionner une catégorie</option>
                <option v-for="c in rootCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
            <div v-if="modalSubCategories.length">
              <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-1">Sous-catégorie</label>
              <select v-model="newBrief.sub_category_id"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary transition-colors">
                <option value="">Toutes les sous-catégories</option>
                <option v-for="c in modalSubCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
            <div class="flex gap-3 pt-2">
              <button type="button" @click="showNewBriefModal = false"
                class="flex-1 px-4 py-2.5 rounded-full border border-primary text-primary font-bold text-sm hover:bg-primary/5 transition-colors">Annuler</button>
              <button type="submit" :disabled="submittingBrief"
                class="flex-1 px-4 py-2.5 rounded-full bg-primary text-white font-bold text-sm disabled:opacity-50 hover:scale-105 transition-transform flex items-center justify-center gap-2">
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
import axios from 'axios'

const store     = useClientStore()
const route     = useRoute()
const activeTab = ref(route.query.tab || 'dashboard')

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
    const res = await axios.get('http://localhost:8000/api/client/briefs', { headers: store.authHeaders, params })
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
    const res = await axios.get('http://localhost:8000/api/freelancer/categories', { headers: store.authHeaders })
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
    const res = await axios.get('http://localhost:8000/api/client/missions', { headers: store.authHeaders })
    myMissions.value = res.data
  } catch { myMissions.value = [] } finally { loadingMyMissions.value = false }
}

// ── New Brief Modal ──────────────────────────────────────────────────────────
const showNewBriefModal = ref(false)
const submittingBrief   = ref(false)
const newBrief = ref({ title: '', description: '', budget: '', deadline: '', category_id: '', sub_category_id: '' })
const minDate  = new Date(Date.now() + 86400000).toISOString().split('T')[0]

const modalSubCategories  = computed(() => newBrief.value.category_id
  ? categories.value.filter(c => c.parent_id === newBrief.value.category_id) : [])
const onModalCategoryChange = () => { newBrief.value.sub_category_id = '' }

const submitBrief = async () => {
  submittingBrief.value = true
  try {
    await axios.post('http://localhost:8000/api/client/missions', newBrief.value, { headers: store.authHeaders })
    showNewBriefModal.value = false
    newBrief.value = { title: '', description: '', budget: '', deadline: '', category_id: '', sub_category_id: '' }
    loadMyMissions()
    activeTab.value = 'briefs'
  } catch (e) {
    alert(e.response?.data?.message || 'Erreur lors de la publication')
  } finally { submittingBrief.value = false }
}

// Listen for tab changes from navbar profile menu or BriefCard contact button
const handleTabEvent = (e) => { activeTab.value = e.detail }

watch(() => route.query.tab, (t) => { if (t) activeTab.value = t })
watch(activeTab, (t) => { if (t === 'briefs') loadMyMissions() })

onMounted(async () => {
  await loadCategories()
  await loadBriefs()
  loadMyMissions()
  window.addEventListener('client-tab', handleTabEvent)
})
onUnmounted(() => window.removeEventListener('client-tab', handleTabEvent))
</script>

<style scoped>
@keyframes slideIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.animate-in { animation: slideIn 0.25s ease-out; }
.zellige-pattern {
  background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M30 0l5 25h25l-20 15 8 20-18-12-18 12 8-20L0 25h25z' fill='%23006233' fill-opacity='0.03' fill-rule='evenodd'/%3E%3C/svg%3E");
}
</style>
