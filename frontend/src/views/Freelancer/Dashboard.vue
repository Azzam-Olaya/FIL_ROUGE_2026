<template>
  <div class="min-h-screen bg-surface font-body text-on-surface flex flex-col overflow-x-hidden">
    <TopNavBar @toggle-sidebar="sidebarOpen = !sidebarOpen" />

    <div class="flex flex-1 overflow-x-hidden">
      <Sidebar 
        :activeTab="activeTab" 
        :isOpen="sidebarOpen"
        @change-tab="tab => activeTab = tab" 
        @close="sidebarOpen = false"
      />

      <main class="lg:ml-64 p-4 md:p-8 min-h-screen flex-1 relative bg-surface zellige-pattern transition-all duration-300">
        <!-- ── Dashboard ──────────────────────────────────────────────────── -->
        <div v-if="activeTab === 'dashboard'" class="space-y-8 pb-20">

          <header class="flex flex-col sm:flex-row justify-between items-start gap-4">
            <div class="flex-1">
              <h1 class="font-headline font-bold text-3xl md:text-5xl text-on-surface mb-2">
                Marhba {{ store.userName }}
              </h1>
              <p class="text-on-surface-variant font-medium text-base md:text-lg italic">
                Mettez en valeur votre talent et trouvez de nouvelles opportunités
              </p>
            </div>
            <button @click="showNewBriefModal = true"
              class="w-full sm:w-auto flex items-center justify-center gap-2 bg-primary text-white px-6 py-3 rounded-full font-bold text-sm hover:scale-105 active:scale-95 transition-all shadow-xl shadow-primary/30 flex-shrink-0">
              <span class="material-symbols-outlined text-sm">add</span>
              Nouveau brief
            </button>
          </header>

          <!-- Mission feed -->
          <section>
            <div class="flex flex-col md:flex-row items-baseline justify-between mb-6 gap-2">
              <div>
                <h2 class="font-headline text-2xl md:text-3xl font-bold text-on-surface">Missions des clients</h2>
                <p class="text-sm text-on-surface-variant mt-1">Trouvez votre prochain projet passionnant</p>
              </div>
              <div v-if="loading" class="flex items-center gap-2 text-on-surface-variant">
                <span class="material-symbols-outlined text-sm animate-spin">progress_activity</span>
                <span class="text-sm">Chargement...</span>
              </div>
            </div>

            <div v-if="!loading && allMissions.length === 0"
              class="bg-surface-container-low p-8 md:p-12 rounded-[2rem] md:rounded-[2.5rem] text-center border border-primary/5">
              <span class="material-symbols-outlined text-6xl md:text-8xl text-on-surface-variant/30 mb-4" style="font-variation-settings: 'wght' 200">work_off</span>
              <h3 class="font-headline text-xl md:text-2xl font-bold text-on-surface mb-2">Aucune mission disponible</h3>
            </div>

            <div v-else class="grid grid-cols-1 gap-6">
              <MissionCard
                v-for="mission in allMissions"
                :key="mission.id"
                :mission="mission"
                @mission-updated="loadMissions"
              />
            </div>
          </section>
        </div>

        <!-- ── Mes Briefs ─────────────────────────────────────────────────── -->
        <div v-if="activeTab === 'briefs'" class="space-y-6">
          <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
              <h1 class="font-headline font-bold text-3xl md:text-4xl text-on-surface mb-1">Mes Briefs</h1>
              <p class="text-on-surface-variant text-sm">Briefs que vous avez publiés</p>
            </div>
            <button @click="showNewBriefModal = true"
              class="w-full sm:w-auto flex items-center justify-center gap-2 bg-primary text-white px-5 py-2.5 rounded-full font-bold text-sm hover:scale-105 transition-all shadow-lg shadow-primary/20">
              <span class="material-symbols-outlined text-sm">add</span>Nouveau brief
            </button>
          </div>
          <div v-if="loadingBriefs" class="flex justify-center py-16">
            <span class="material-symbols-outlined text-4xl text-primary/30 animate-spin">progress_activity</span>
          </div>
          <div v-else-if="!myBriefs.length" class="text-center py-20 bg-white rounded-[2rem] border border-primary/5">
            <span class="material-symbols-outlined text-5xl text-gray-300 block mb-3">briefcase</span>
            <p class="text-gray-400 font-medium">Vous n'avez pas encore publié de briefs.</p>
            <button @click="showNewBriefModal = true" class="mt-4 text-primary text-sm font-bold hover:underline">Publier votre premier brief</button>
          </div>
          <div v-else class="grid grid-cols-1 gap-6">
            <div v-for="b in myBriefs" :key="b.id" :id="'brief-' + b.id" 
              class="bg-white rounded-[2rem] border border-primary/5 p-4 md:p-6 shadow-sm transition-all duration-500"
              :class="route.query.briefId == b.id ? 'ring-2 ring-primary bg-primary/5 scale-[1.02]' : ''">
              <div class="flex flex-col sm:flex-row items-start justify-between gap-4 mb-3">
                <div class="flex-1">
                  <div class="flex flex-wrap gap-2 mt-2">
                    <span v-for="cat in b.categories" :key="cat.id" class="text-[10px] font-bold uppercase tracking-widest text-white bg-primary px-3 py-1 rounded-full">
                      {{ cat.name }}
                    </span>
                    <span v-if="!b.categories?.length" class="text-[10px] font-bold uppercase tracking-widest text-white bg-primary px-3 py-1 rounded-full">
                      {{ b.category?.name || 'Sans catégorie' }}
                    </span>
                  </div>
                  <h3 class="font-bold text-lg text-on-surface mt-2">{{ b.title }}</h3>
                  <p class="text-sm text-on-surface-variant mt-1 line-clamp-3">{{ b.description }}</p>
                </div>
                <div class="sm:text-right flex-shrink-0">
                  <p class="font-black text-xl text-primary">{{ b.price || 0 }} DH</p>
                  <p class="text-xs text-on-surface-variant mt-0.5">{{ b.duration || 'Délai non spécifié' }}</p>
                </div>
              </div>
              <div class="flex items-center gap-4 text-xs text-on-surface-variant pt-3 border-t border-primary/5">
                <button @click="toggleLikes(b)" class="flex items-center gap-1.5 hover:text-primary transition-colors">
                  <span class="material-symbols-outlined text-sm">favorite</span>{{ b.likes_count || 0 }} j'aime
                </button>
                <button @click="toggleComments(b)" class="flex items-center gap-1.5 hover:text-primary transition-colors">
                  <span class="material-symbols-outlined text-sm">chat_bubble</span>{{ b.comments_count || 0 }} commentaires
                </button>
              </div>

              <!-- Likes List -->
              <div v-if="b.showLikes" class="mt-4 pt-4 border-t border-primary/5">
                <div v-if="b.likes?.length" class="flex flex-wrap gap-2">
                  <div v-for="like in b.likes" :key="like.id" class="flex items-center gap-1.5 bg-primary/5 px-2.5 py-1 rounded-full">
                    <div class="w-5 h-5 rounded-full bg-primary flex items-center justify-center text-white text-[8px] font-bold">
                      {{ (like.user?.name || 'U').split(' ').map(n => n[0]).join('').toUpperCase().slice(0,1) }}
                    </div>
                    <span class="text-[10px] font-medium text-on-surface">{{ like.user?.name || 'Client' }}</span>
                  </div>
                </div>
                <p v-else class="text-center text-xs text-on-surface-variant py-2">Aucun j'aime pour le moment</p>
              </div>

              <!-- Comments List -->
              <div v-if="b.showComments" class="mt-4 pt-4 border-t border-primary/5 space-y-4">
                <div v-if="b.commentsList?.length" class="space-y-4">
                  <div v-for="c in b.commentsList" :key="c.id" class="flex gap-3 items-start animate-in">
                    <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white font-bold text-[10px] flex-shrink-0">
                      {{ (c.user?.name || 'U').split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) }}
                    </div>
                    <div class="flex-1 bg-surface-container rounded-2xl px-4 py-3">
                      <p class="font-bold text-xs text-on-surface">{{ c.user?.name || 'Anonyme' }}</p>
                      <p class="text-sm text-on-surface-variant mt-0.5 leading-relaxed">{{ c.body }}</p>
                    </div>
                  </div>
                </div>
                <p v-else class="text-center text-xs text-on-surface-variant py-2">Aucun commentaire pour le moment</p>
              </div>
            </div>
          </div>
        </div>

        <!-- ── Messages ───────────────────────────────────────────────────── -->
        <div v-if="activeTab === 'messages'" class="px-0 md:px-4">
          <h1 class="font-headline font-bold text-3xl md:text-4xl text-on-surface mb-2">Messages</h1>
          <p class="text-on-surface-variant text-sm mb-6">Vos conversations avec les clients</p>
          <MessagingPanel />
        </div>

        <!-- ── Paiements ──────────────────────────────────────────────────── -->
        <div v-if="activeTab === 'payments'" class="px-0 md:px-4">
          <h1 class="font-headline font-bold text-3xl md:text-4xl text-on-surface mb-2">Paiements</h1>
          <p class="text-on-surface-variant text-sm mb-6">Historique et gestion des paiements</p>
          <PaymentTab role="freelancer" />
        </div>

        <!-- ── Contrats ───────────────────────────────────────────────────── -->
        <div v-if="activeTab === 'contracts'" class="px-0 md:px-4">
          <h1 class="font-headline font-bold text-3xl md:text-4xl text-on-surface mb-2">Contrats</h1>
          <p class="text-on-surface-variant text-sm mb-6">Gestion de vos contrats</p>
          <ContractTab role="freelancer" />
        </div>

        <!-- ── Profil ─────────────────────────────────────────────────────── -->
        <div v-if="activeTab === 'profile'" class="px-0 md:px-4 max-w-2xl">
          <h1 class="font-headline font-bold text-3xl md:text-4xl text-on-surface mb-2">Profil</h1>
          <p class="text-on-surface-variant text-sm mb-6">Configuration de votre page talent</p>
          <div class="bg-white rounded-[2rem] border border-primary/5 p-6 md:p-8 shadow-sm space-y-6">
            <div class="flex items-center gap-5">
              <div class="w-16 h-16 md:w-20 md:h-20 rounded-full bg-primary flex items-center justify-center text-white font-bold text-xl md:text-2xl shadow-xl shadow-primary/20">
                {{ store.userInitials }}
              </div>
              <div>
                <h2 class="font-bold text-xl md:text-2xl text-on-surface">{{ store.userName }}</h2>
                <p class="text-on-surface-variant text-sm">Freelancer · MorLancer</p>
              </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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

    <!-- Nouveau Brief Modal -->
    <Teleport to="body">
      <div v-if="showNewBriefModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-2 md:p-4" @click.self="showNewBriefModal = false">
        <div class="bg-white rounded-[2rem] shadow-2xl max-w-lg w-full p-6 md:p-8 animate-in max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between mb-6">
            <h2 class="font-headline text-xl md:text-2xl font-bold text-on-surface">Nouveau Brief</h2>
            <button @click="showNewBriefModal = false" class="p-2 hover:bg-primary/10 rounded-full transition-colors">
              <span class="material-symbols-outlined">close</span>
            </button>
          </div>
          <form @submit.prevent="submitBrief" class="space-y-4">
            <div>
              <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-1">Titre *</label>
              <input v-model="newBrief.title" required placeholder="Ex: Expert en design UI/UX"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary transition-colors" />
            </div>
            <div>
              <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-1">Description *</label>
              <textarea v-model="newBrief.description" required rows="4" md:rows="6" placeholder="Décrivez vos compétences et ce que vous proposez..."
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary transition-colors resize-none" />
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
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-1">Prix (DH) *</label>
                <input v-model="newBrief.price" type="number" min="0" required placeholder="5000"
                  class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary transition-colors" />
              </div>
              <div>
                <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-1">Période de réalisation *</label>
                <input v-model="newBrief.duration" required placeholder="Ex: 2 semaines"
                  class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary transition-colors" />
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
import { useRoute, useRouter } from 'vue-router'
import TopNavBar      from '@/components/Common/TopNavBar.vue'
import Sidebar        from '@/components/Freelancer/Sidebar.vue'
import MissionCard    from '@/components/Freelancer/MissionCard.vue'
import MessagingPanel from '@/components/Common/MessagingPanel.vue'
import PaymentTab     from '@/components/Common/PaymentTab.vue'
import ContractTab    from '@/components/Common/ContractTab.vue'
import { useFreelancerStore } from '@/stores/freelancer'
import api from '@/api/axios'

const store        = useFreelancerStore()
const route        = useRoute()
const router       = useRouter()
const activeTab    = ref(route.query.tab || 'dashboard')
const sidebarOpen  = ref(false)

// ── Missions (publiées par les clients) ─────────────────────────────────────
const allMissions = ref([])
const loading     = ref(false)
const debounceTimer = ref(null)

const loadMissions = async () => {
  loading.value = true
  try {
    const params = {}
    if (store.searchQuery)    params.search = store.searchQuery
    if (store.searchCategory) params.category_id = store.searchCategory
    
    const res = await api.get('/freelancer/missions', { params })
    if (res.data) allMissions.value = res.data
  } catch {
    allMissions.value = []
  } finally { loading.value = false }
}

watch(() => store.searchQuery, () => {
  clearTimeout(debounceTimer.value)
  debounceTimer.value = setTimeout(loadMissions, 400)
})
watch(() => store.searchCategory, loadMissions)

// ── Mes Briefs (publiés par le freelancer) ──────────────────────────────────
const myBriefs    = ref([])
const loadingBriefs = ref(false)

const loadMyBriefs = async () => {
  loadingBriefs.value = true
  try {
    const res = await api.get('/freelancer/briefs/mine')
    myBriefs.value = res.data
  } catch { myBriefs.value = [] } finally { loadingBriefs.value = false }
}

const toggleComments = async (brief) => {
  brief.showComments = !brief.showComments
  if (brief.showComments && !brief.commentsList) {
    try {
      const res = await api.get(`/freelancer/briefs/${brief.id}/comments`)
      brief.commentsList = res.data
    } catch {}
  }
}

const toggleLikes = (brief) => {
  brief.showLikes = !brief.showLikes
}

// ── Nouveau Brief Modal ─────────────────────────────────────────────────────
const showNewBriefModal = ref(false)
const submittingBrief   = ref(false)
const categories        = ref([])
const newBrief = ref({ title: '', description: '', parent_ids: [], category_ids: [], price: '', duration: '' })

const rootCategories = computed(() => categories.value.filter(c => !c.parent_id))
const modalSubCategories = computed(() => {
  if (!newBrief.value.parent_ids.length) return []
  return categories.value.filter(c => newBrief.value.parent_ids.includes(c.parent_id))
})

const toggleParentCategory = (id) => {
  const idx = newBrief.value.parent_ids.indexOf(id)
  if (idx > -1) {
    newBrief.value.parent_ids.splice(idx, 1)
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

const loadCategories = async () => {
  try {
    const res = await api.get('/freelancer/categories')
    categories.value = res.data
  } catch { categories.value = [] }
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
      category_id: newBrief.value.parent_ids[0],
      category_ids: [...newBrief.value.parent_ids, ...newBrief.value.category_ids]
    }
    await api.post('/freelancer/briefs', payload)
    showNewBriefModal.value = false
    newBrief.value = { title: '', description: '', parent_ids: [], category_ids: [], price: '', duration: '' }
    activeTab.value = 'briefs'
    loadMyBriefs()
  } catch (e) {
    alert(e.response?.data?.message || 'Erreur lors de la publication')
  } finally { submittingBrief.value = false }
}

// Listen for tab events from NavProfile
const handleTabEvent = (e) => { activeTab.value = e.detail }

watch(() => route.query.tab, (t) => { if (t) activeTab.value = t })
watch(activeTab, (t) => {
  if (t === 'briefs') loadMyBriefs()
  // Sync URL with tab and clear briefId
  router.push({ query: { tab: t } })
})

watch(() => route.query.briefId, (id) => {
  if (id && activeTab.value === 'briefs') {
    setTimeout(() => {
      const el = document.getElementById(`brief-${id}`)
      if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' })
    }, 800)
  }
}, { immediate: true })

onMounted(() => {
  store.startPolling()
  store.fetchFavorites()
  loadMissions()
  loadCategories()
  window.addEventListener('freelancer-tab', handleTabEvent)
})
onUnmounted(() => window.removeEventListener('freelancer-tab', handleTabEvent))
</script>

<style scoped>
@keyframes slideIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.animate-in { animation: slideIn 0.25s ease-out; }
.zellige-pattern {
  background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M30 0l5 25h25l-20 15 8 20-18-12-18 12 8-20L0 25h25z' fill='%23006233' fill-opacity='0.03' fill-rule='evenodd'/%3E%3C/svg%3E");
}
</style>
