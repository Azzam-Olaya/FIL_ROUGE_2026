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
                Marhba {{ store.userName }}
              </h1>
              <p class="text-on-surface-variant font-medium text-lg italic">
                Mettez en valeur votre talent et trouvez de nouvelles opportunités
              </p>
            </div>
            <button @click="showNewBriefModal = true"
              class="flex items-center gap-2 bg-primary text-white px-6 py-3 rounded-full font-bold text-sm hover:scale-105 active:scale-95 transition-all shadow-xl shadow-primary/30 flex-shrink-0">
              <span class="material-symbols-outlined text-sm">add</span>
              Nouveau brief
            </button>
          </header>

          <!-- Mission feed -->
          <section>
            <div class="flex items-baseline justify-between mb-6">
              <div>
                <h2 class="font-headline text-3xl font-bold text-on-surface">Missions des clients</h2>
                <p class="text-sm text-on-surface-variant mt-1">
                  {{ allMissions.length }} mission{{ allMissions.length !== 1 ? 's' : '' }} disponible{{ allMissions.length !== 1 ? 's' : '' }}
                </p>
              </div>
              <div v-if="loading" class="flex items-center gap-2 text-on-surface-variant">
                <span class="material-symbols-outlined text-sm animate-spin">progress_activity</span>
                <span class="text-sm">Chargement...</span>
              </div>
            </div>

            <div v-if="!loading && allMissions.length === 0"
              class="bg-surface-container-low p-12 rounded-[2.5rem] text-center border border-primary/5">
              <span class="material-symbols-outlined text-8xl text-on-surface-variant/30 mb-4" style="font-variation-settings: 'wght' 200">work_off</span>
              <h3 class="font-headline text-2xl font-bold text-on-surface mb-2">Aucune mission disponible</h3>
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
          <div class="flex items-center justify-between">
            <div>
              <h1 class="font-headline font-bold text-4xl text-on-surface mb-1">Mes Briefs</h1>
              <p class="text-on-surface-variant text-sm">Briefs que vous avez publiés</p>
            </div>
            <button @click="showNewBriefModal = true"
              class="flex items-center gap-2 bg-primary text-white px-5 py-2.5 rounded-full font-bold text-sm hover:scale-105 transition-all shadow-lg shadow-primary/20">
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
            <div v-for="b in myBriefs" :key="b.id" class="bg-white rounded-[2rem] border border-primary/5 p-6 shadow-sm">
              <div class="flex items-start justify-between gap-4 mb-3">
                <div>
                  <span class="text-[10px] font-bold uppercase tracking-widest text-white bg-primary px-3 py-1 rounded-full">{{ b.category?.name || 'Sans catégorie' }}</span>
                  <h3 class="font-bold text-lg text-on-surface mt-2">{{ b.title }}</h3>
                  <p class="text-sm text-on-surface-variant mt-1 line-clamp-2">{{ b.description }}</p>
                </div>
                <div class="text-right flex-shrink-0">
                  <p class="font-black text-xl text-primary">{{ b.price }} DH</p>
                  <p class="text-xs text-on-surface-variant mt-0.5">{{ b.duration }}</p>
                </div>
              </div>
              <div class="flex items-center gap-4 text-xs text-on-surface-variant pt-3 border-t border-primary/5">
                <span class="flex items-center gap-1">
                  <span class="material-symbols-outlined text-sm">favorite</span>{{ b.likes_count || 0 }} j'aime
                </span>
                <span class="flex items-center gap-1">
                  <span class="material-symbols-outlined text-sm">chat_bubble</span>{{ b.comments_count || 0 }} commentaires
                </span>
              </div>
            </div>
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

    <!-- Nouveau Brief Modal -->
    <Teleport to="body">
      <div v-if="showNewBriefModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="showNewBriefModal = false">
        <div class="bg-white rounded-[2rem] shadow-2xl max-w-lg w-full p-8 animate-in">
          <div class="flex items-center justify-between mb-6">
            <h2 class="font-headline text-2xl font-bold text-on-surface">Nouveau Brief</h2>
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
              <textarea v-model="newBrief.description" required rows="6" placeholder="Décrivez vos compétences et ce que vous proposez..."
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary transition-colors resize-none" />
            </div>
            <div>
              <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-1">Catégorie *</label>
              <select v-model="newBrief.category_id" required
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary transition-colors">
                <option value="">Sélectionner une catégorie</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
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
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import TopNavBar      from '@/components/Common/TopNavBar.vue'
import Sidebar        from '@/components/Freelancer/Sidebar.vue'
import MissionCard    from '@/components/Freelancer/MissionCard.vue'
import MessagingPanel from '@/components/Common/MessagingPanel.vue'
import PaymentTab     from '@/components/Common/PaymentTab.vue'
import ContractTab    from '@/components/Common/ContractTab.vue'
import { useFreelancerStore } from '@/stores/freelancer'
import axios from 'axios'

const store     = useFreelancerStore()
const route     = useRoute()
const activeTab = ref(route.query.tab || 'dashboard')

// ── Missions (publiées par les clients) ─────────────────────────────────────
const allMissions = ref([])
const loading     = ref(false)

const loadMissions = async () => {
  loading.value = true
  try {
    const res = await axios.get('http://localhost:8000/api/freelancer/missions', { headers: store.authHeaders })
    if (res.data?.length > 0) allMissions.value = res.data
  } catch {
    allMissions.value = [
      { id: 101, title: 'Design Logo Artisanat Moderne', description: 'Nous cherchons un designer pour concevoir un logo alliant les zelliges traditionnels et une typographie moderne pour notre nouvelle marque de vêtements.', budget: 2500, deadline: '10 Jours', categories: ['Design', 'Branding'], categorySlug: 'design', client: { id: 1, name: 'Boutique Atlas' }, likes: 12, comments: 3, favorites_count: 2, commentsList: [] },
      { id: 102, title: 'Développement Site E-commerce', description: 'Mise en place d\'une boutique en ligne complète (frontend & backend) pour vendre de la poterie de Safi à l\'international.', budget: 15000, deadline: '1 Mois', categories: ['Dev', 'E-commerce'], categorySlug: 'dev', client: { id: 2, name: 'Poterie Safi Exp' }, likes: 45, comments: 8, favorites_count: 5, commentsList: [] },
      { id: 103, title: 'Campagne Marketing Réseaux Sociaux', description: 'Besoin d\'un expert pour gérer la promotion de nos tapis berbères sur Instagram. Création de contenu et publicités ciblées.', budget: 5000, deadline: 'En continu', categories: ['Marketing', 'Ads'], categorySlug: 'marketing', client: { id: 3, name: 'Tissages du Haut' }, likes: 22, comments: 1, favorites_count: 1, commentsList: [] },
      { id: 104, title: 'Application Mobile de Livraison', description: 'Nous recherchons un développeur Flutter/React Native pour une app de livraison spécialisée dans l\'artisanat lourd.', budget: 20000, deadline: '3 Mois', categories: ['Dev', 'Mobile'], categorySlug: 'dev', client: { id: 4, name: 'Atlas Deliver' }, likes: 56, comments: 12, favorites_count: 8, commentsList: [] },
    ]
  } finally { loading.value = false }
}

// ── Mes Briefs (publiés par le freelancer) ──────────────────────────────────
const myBriefs    = ref([])
const loadingBriefs = ref(false)

const loadMyBriefs = async () => {
  loadingBriefs.value = true
  try {
    const res = await axios.get('http://localhost:8000/api/freelancer/briefs/mine', { headers: store.authHeaders })
    myBriefs.value = res.data
  } catch { myBriefs.value = [] } finally { loadingBriefs.value = false }
}

// ── Nouveau Brief Modal ─────────────────────────────────────────────────────
const showNewBriefModal = ref(false)
const submittingBrief   = ref(false)
const categories        = ref([])
const newBrief = ref({ title: '', description: '', category_id: '', price: '', duration: '' })

const loadCategories = async () => {
  try {
    const res = await axios.get('http://localhost:8000/api/freelancer/categories', { headers: store.authHeaders })
    categories.value = res.data.filter(c => !c.parent_id)
  } catch { categories.value = [] }
}

const submitBrief = async () => {
  submittingBrief.value = true
  try {
    await axios.post('http://localhost:8000/api/freelancer/briefs', newBrief.value, { headers: store.authHeaders })
    showNewBriefModal.value = false
    newBrief.value = { title: '', description: '', category_id: '', price: '', duration: '' }
    activeTab.value = 'briefs'
    loadMyBriefs()
  } catch (e) {
    alert(e.response?.data?.message || 'Erreur lors de la publication')
  } finally { submittingBrief.value = false }
}

// Listen for tab events from NavProfile
const handleTabEvent = (e) => { activeTab.value = e.detail }

watch(() => route.query.tab, (t) => { if (t) activeTab.value = t })
watch(activeTab, (t) => { if (t === 'briefs') loadMyBriefs() })

onMounted(() => {
  store.startPolling()
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
