<template>
  <div class="bg-white rounded-[2rem] border border-primary/10 shadow-sm hover:shadow-md transition-all">
    
    <!-- Header: Avatar + Name + Category + Date -->
    <div class="flex items-start justify-between gap-3 p-6 pb-4">
      <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-full bg-primary flex items-center justify-center text-white font-bold text-sm flex-shrink-0 shadow-md shadow-primary/20">
          {{ store.userInitials }}
        </div>
        <div>
          <div class="flex items-center gap-2">
            <p class="font-bold text-sm text-on-surface">{{ store.userName }}</p>
            <span :class="statusClass(mission.status)" class="text-[9px] font-black uppercase px-2 py-0.5 rounded-full tracking-tighter">
              {{ statusLabel(mission.status) }}
            </span>
          </div>
          <div class="flex items-center gap-2 mt-0.5">
            <span class="text-[10px] font-bold text-white bg-primary px-2.5 py-0.5 rounded-full">
              {{ mission.category?.name || (mission.categories?.[0]?.name) || 'Mission' }}
            </span>
            <span class="text-[10px] text-on-surface-variant flex items-center gap-1">
              <span class="material-symbols-outlined text-[12px]">calendar_today</span>
              {{ new Date(mission.created_at).toLocaleDateString('fr-FR') }}
            </span>
          </div>
        </div>
      </div>
      <div class="text-right flex-shrink-0">
        <p class="text-primary font-black text-xl">{{ mission.budget }} DH</p>
        <p class="text-[10px] text-on-surface-variant font-medium">Budget</p>
      </div>
    </div>

    <!-- Body: Title + Description -->
    <div class="px-6 pb-4">
      <h3 class="font-headline text-lg font-bold text-on-surface mb-2">{{ mission.title }}</h3>
      <p class="text-on-surface-variant text-sm leading-relaxed" :class="expanded ? '' : 'line-clamp-3'">
        {{ mission.description }}
      </p>
      <button v-if="mission.description?.length > 180" @click="expanded = !expanded"
        class="text-primary text-xs font-bold mt-1 hover:underline">
        {{ expanded ? 'Voir moins' : 'Voir plus' }}
      </button>
    </div>

    <!-- Skills tags -->
    <div v-if="mission.categories?.length > 1" class="px-6 pb-3 flex flex-wrap gap-2">
      <template v-for="cat in mission.categories" :key="cat.id">
        <span v-if="cat.id !== mission.category_id"
          class="text-[10px] font-bold text-secondary bg-secondary/10 px-3 py-1.5 rounded-lg flex items-center gap-1">
          <span class="material-symbols-outlined" style="font-size: 12px">terminal</span>
          {{ cat.name }}
        </span>
      </template>
    </div>

    <!-- Meta: deadline -->
    <div class="px-6 pb-3 opacity-70">
      <p class="text-[11px] text-on-surface-variant font-bold flex items-center gap-1.5 uppercase tracking-wider">
        <span class="material-symbols-outlined text-sm">event</span>
        Échéance : {{ mission.deadline ? new Date(mission.deadline).toLocaleDateString('fr-FR') : '—' }}
      </p>
    </div>

    <!-- Counters bar -->
    <div class="px-6 py-2 flex items-center justify-between border-t border-b border-primary/5 text-[11px] font-bold text-on-surface-variant/70">
      <button @click="toggleLikes" class="flex items-center gap-1.5 hover:text-secondary transition-colors group">
        <span class="w-5 h-5 rounded-full bg-secondary/10 flex items-center justify-center group-hover:bg-secondary group-hover:text-white transition-colors">
          <span class="material-symbols-outlined text-[10px] font-black" style="font-variation-settings:'FILL' 1">favorite</span>
        </span>
        {{ mission.likes_count || 0 }} j'aime
      </button>
      <button @click="toggleComments" class="flex items-center gap-1.5 hover:text-primary transition-colors group">
        <span class="w-5 h-5 rounded-full bg-primary/10 flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-colors">
          <span class="material-symbols-outlined text-[10px] font-black">chat_bubble</span>
        </span>
        {{ mission.comments_count || 0 }} commentaire{{ (mission.comments_count || 0) !== 1 ? 's' : '' }}
      </button>
    </div>

    <!-- Action buttons Row -->
    <div class="px-4 py-2 flex items-center border-b border-primary/5">
      <button @click="toggleComments"
        :class="showComments ? 'text-primary bg-primary/10' : 'text-on-surface-variant hover:bg-surface-container hover:text-primary'"
        class="flex-1 flex items-center justify-center gap-2 py-2 rounded-xl transition-all font-bold text-xs uppercase tracking-widest">
        <span class="material-symbols-outlined text-base">chat_bubble</span>
        Messages
      </button>
      <button @click="toggleApplications"
        :class="showApplications ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-on-surface-variant hover:bg-surface-container hover:text-primary'"
        class="flex-1 flex items-center justify-center gap-2 py-2 rounded-xl transition-all font-bold text-xs uppercase tracking-widest border border-transparent">
        <span class="material-symbols-outlined text-base">group</span>
        Candidatures
      </button>
    </div>

    <!-- Likes List Section -->
    <div v-if="showLikes" class="px-6 py-4 bg-surface-container/20 border-b border-primary/5 animate-in">
      <p class="text-[9px] font-black uppercase tracking-widest text-on-surface-variant/60 mb-4 flex items-center gap-2">
        <span class="material-symbols-outlined text-xs font-black">favorite</span>
        Freelancers ayant aimé
      </p>
      <div v-if="loadingLikes" class="flex justify-center py-4">
        <span class="material-symbols-outlined text-primary/30 animate-spin">progress_activity</span>
      </div>
      <div v-else-if="likesList.length" class="flex flex-wrap gap-2">
        <div v-for="user in likesList" :key="user.id" 
             class="flex items-center gap-2 bg-white border border-primary/5 pl-1 pr-3 py-1 rounded-full shadow-sm hover:border-primary/20 transition-all cursor-default">
          <div class="w-6 h-6 rounded-full bg-secondary flex items-center justify-center text-white font-bold text-[8px] shadow-sm">
            {{ initials(user.name) }}
          </div>
          <span class="text-[11px] font-bold text-on-surface">{{ user.name }}</span>
        </div>
      </div>
      <p v-else class="text-center text-xs text-on-surface-variant py-4 italic">Aucun like pour le moment</p>
    </div>

    <!-- Comments section -->
    <div v-if="showComments" class="px-6 py-4 space-y-4 bg-surface-container/10 border-b border-primary/5 animate-in">
      <p class="text-[9px] font-black uppercase tracking-widest text-on-surface-variant/60 mb-2 flex items-center gap-2">
        <span class="material-symbols-outlined text-xs font-black">chat_bubble</span>
        Discussions
      </p>
      <div v-if="loadingComments" class="flex justify-center py-3">
        <span class="material-symbols-outlined text-primary/30 animate-spin">progress_activity</span>
      </div>
      <div v-else-if="commentsList.length" class="space-y-4">
        <div v-for="(c, i) in commentsList" :key="i" class="group flex gap-3 items-start">
          <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center text-white font-bold text-xs flex-shrink-0 shadow-lg shadow-primary/10">
            {{ initials(c.user?.name || c.author) }}
          </div>
          <div class="flex-1">
            <div class="bg-white border border-primary/5 rounded-2xl px-5 py-4 shadow-sm group-hover:border-primary/20 transition-all">
              <div class="flex items-center justify-between gap-2 mb-2">
                <p class="font-bold text-xs text-on-surface">{{ c.user?.name || c.author }}</p>
                <button @click="contactFreelancer(c)"
                  class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/10 text-primary text-[10px] font-black uppercase tracking-tighter hover:bg-primary hover:text-white transition-all">
                  <span class="material-symbols-outlined" style="font-size:12px">mail</span>
                  Contacter
                </button>
              </div>
              <p class="text-sm text-on-surface-variant leading-relaxed">{{ c.body || c.text }}</p>
            </div>
          </div>
        </div>
      </div>
      <p v-else class="text-center text-xs text-on-surface-variant py-3 italic">Aucun commentaire pour le moment</p>
    </div>

    <!-- Applications Section -->
    <div v-if="showApplications" class="px-6 py-6 space-y-4 bg-primary/5 border-b border-primary/5 animate-in">
      <p class="text-[10px] font-black uppercase tracking-widest text-primary mb-4 flex items-center gap-2">
        <span class="material-symbols-outlined text-sm font-black">group</span>
        Freelancers ayant postulé
      </p>

      <div v-if="loadingApps" class="flex justify-center py-6">
        <span class="material-symbols-outlined text-primary/30 animate-spin">progress_activity</span>
      </div>

      <div v-else-if="appsList.length" class="space-y-4">
        <div v-for="app in appsList" :key="app.id" class="bg-white border border-primary/10 rounded-3xl p-5 shadow-sm hover:border-primary/30 transition-all">
          <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-2xl bg-primary flex items-center justify-center text-white font-bold text-sm shadow-lg shadow-primary/10">
                {{ initials(app.freelancer?.name) }}
              </div>
              <div>
                <p class="font-bold text-on-surface">{{ app.freelancer?.name }}</p>
                <p class="text-primary font-black text-xs">{{ Number(app.price).toLocaleString() }} DH</p>
              </div>
            </div>
            
            <div v-if="app.status === 'pending'" class="flex gap-2 w-full sm:w-auto">
              <button @click="acceptApp(app)" :disabled="processingApp"
                class="flex-1 sm:flex-none px-6 py-2 bg-primary text-white rounded-full text-[10px] font-black uppercase tracking-widest hover:scale-105 transition-all shadow-md shadow-primary/20">
                Accepter
              </button>
              <button class="flex-1 sm:flex-none px-6 py-2 border border-primary/20 text-on-surface-variant rounded-full text-[10px] font-black uppercase tracking-widest hover:bg-red-50 hover:text-red-600 hover:border-red-600 transition-all">
                Refuser
              </button>
            </div>
            <div v-else class="flex items-center gap-2 text-green-600 bg-green-50 px-3 py-1 rounded-full border border-green-100">
               <span class="material-symbols-outlined text-sm font-black">check_circle</span>
               <span class="text-[10px] font-black uppercase tracking-widest">{{ app.status }}</span>
            </div>
          </div>
          <div class="mt-4 p-4 bg-surface-container/50 rounded-2xl border border-primary/5 italic text-sm text-on-surface-variant">
            "{{ app.proposal }}"
          </div>
        </div>
      </div>
      <p v-else class="text-center text-xs text-on-surface-variant py-6 italic bg-white/50 rounded-3xl border border-dashed border-primary/10">
        Personne n'a encore postulé à cette mission.
      </p>
    </div>

    <!-- Contact Modal -->
    <Teleport to="body">
      <div v-if="showContactModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="showContactModal = false">
        <div class="bg-white rounded-[2rem] shadow-2xl max-w-md w-full p-6 animate-in">
          <div class="flex items-center justify-between mb-4">
            <h2 class="font-headline text-xl font-bold text-on-surface">Contacter le freelancer</h2>
            <button @click="showContactModal = false" class="p-2 hover:bg-primary/10 rounded-full transition-colors">
              <span class="material-symbols-outlined">close</span>
            </button>
          </div>
          <div class="bg-primary/5 rounded-2xl p-4 mb-4 border border-primary/10 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
              {{ initials(contactTarget?.user?.name) }}
            </div>
            <div>
              <p class="font-bold text-on-surface">{{ contactTarget?.user?.name }}</p>
              <p class="text-xs text-on-surface-variant mt-0.5">A commenté : {{ mission.title }}</p>
            </div>
          </div>
          <textarea v-model="contactMessage" rows="4" placeholder="Bonjour, votre profil m'intéresse pour cette mission..."
            class="w-full bg-surface-container border border-primary/10 rounded-xl px-4 py-3 text-sm focus:border-primary focus:outline-none resize-none mb-3" />
          <div class="flex gap-3">
            <button @click="showContactModal = false"
              class="flex-1 px-4 py-2.5 rounded-full border border-primary text-primary font-bold text-sm hover:bg-primary/5 transition-colors">Annuler</button>
            <button @click="sendContact" :disabled="!contactMessage.trim() || sending"
              class="flex-1 px-4 py-2.5 rounded-full bg-primary text-white font-bold text-sm disabled:opacity-50 hover:scale-105 transition-transform flex items-center justify-center gap-2">
              <span v-if="sending" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
              <span v-else>Envoyer</span>
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useClientStore } from '@/stores/client'
import api from '@/api/axios'

const props = defineProps({ mission: { type: Object, required: true } })
const store = useClientStore()

const showComments     = ref(false)
const showLikes        = ref(false)
const showApplications = ref(false)
const showContactModal = ref(false)
const expanded         = ref(false)
const commentsList     = ref([])
const likesList        = ref([])
const appsList         = ref([])
const loadingComments  = ref(false)
const loadingLikes     = ref(false)
const loadingApps      = ref(false)
const processingApp    = ref(false)
const contactTarget    = ref(null)
const contactMessage   = ref('')
const sending          = ref(false)

const initials = (n) => n?.split(' ').map(x => x[0]).join('').toUpperCase().slice(0, 2) || 'U'

const toggleComments = async () => {
  showComments.value = !showComments.value
  showLikes.value = false
  if (showComments.value && !commentsList.value.length) {
    loadingComments.value = true
    try {
      const res = await api.get(`/client/missions/${props.mission.id}/comments`)
      commentsList.value = res.data
    } catch {} finally { loadingComments.value = false }
  }
}

const toggleLikes = async () => {
  showLikes.value = !showLikes.value
  showComments.value = false
  if (showLikes.value && !likesList.value.length) {
    loadingLikes.value = true
    try {
      const res = await api.get(`/client/missions/${props.mission.id}/likes`)
      likesList.value = res.data
    } catch {} finally { loadingLikes.value = false }
  }
}

const contactFreelancer = (comment) => {
  contactTarget.value    = comment
  contactMessage.value   = ''
  showContactModal.value = true
}

const toggleApplications = async () => {
  showApplications.value = !showApplications.value
  showComments.value = false
  showLikes.value = false
  if (showApplications.value && !appsList.value.length) {
    loadingApps.value = true
    try {
      const res = await api.get(`/client/missions/${props.mission.id}/applications`)
      appsList.value = res.data
    } catch {} finally { loadingApps.value = false }
  }
}

const acceptApp = async (app) => {
  if (!confirm(`Voulez-vous lancer ce projet avec ${app.freelancer?.name} ? Un montant de ${Number(app.price).toLocaleString()} DH sera bloqué en escrow.`)) return
  
  processingApp.value = true
  try {
    const res = await api.post(`/client/applications/${app.id}/accept`)
    alert('Succès : Contrat créé et projet lancé !')
    // Refresh mission status locally
    props.mission.status = 'in_progress'
    showApplications.value = false
    // Redirect to contracts
    window.dispatchEvent(new CustomEvent('client-tab', { detail: 'contracts' }))
  } catch (e) {
    alert(e.response?.data?.message || 'Erreur lors de l’acceptation')
  } finally { processingApp.value = false }
}

const sendContact = async () => {
  if (!contactMessage.value.trim() || !contactTarget.value?.user?.id) return
  sending.value = true
  try {
    await api.post('/messages', {
      receiver_id: contactTarget.value.user.id,
      content: contactMessage.value,
    })
    showContactModal.value = false
    contactMessage.value   = ''
    window.dispatchEvent(new CustomEvent('client-tab', { detail: 'messages' }))
  } catch {} finally { sending.value = false }
}

const statusClass = (s) => ({
  open: 'bg-primary/10 text-primary',
  in_progress: 'bg-yellow-100 text-yellow-700',
  completed: 'bg-green-100 text-green-700',
  cancelled: 'bg-red-100 text-red-600'
}[s] || 'bg-surface-container text-on-surface-variant')

const statusLabel = (s) => ({
  open: 'Ouvert',
  in_progress: 'En cours',
  completed: 'Complété',
  cancelled: 'Annulé'
}[s] || s)
</script>

<style scoped>
@keyframes slideIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.animate-in { animation: slideIn 0.25s ease-out; }
</style>
