<template>
  <div class="bg-white rounded-[2rem] border border-primary/10 shadow-sm hover:shadow-md transition-all">

    <!-- Header: category + status + budget -->
    <div class="flex items-start justify-between gap-4 p-6 pb-4">
      <div class="flex-1">
        <div class="flex items-center gap-2 mb-2">
          <span class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant bg-surface-container px-3 py-1 rounded-full flex items-center gap-1">
            <span class="material-symbols-outlined text-xs" style="font-size: 14px">schedule</span>
            {{ new Date(mission.created_at).toLocaleDateString('fr-FR') }}
          </span>
          <span :class="statusClass(mission.status)" class="text-[10px] font-bold uppercase px-2.5 py-0.5 rounded-full">
            {{ statusLabel(mission.status) }}
          </span>
        </div>
        <h3 class="font-headline text-lg font-bold text-on-surface mb-1">{{ mission.title }}</h3>
        <p class="text-on-surface-variant text-sm leading-relaxed" :class="expanded ? '' : 'line-clamp-2'">
          {{ mission.description }}
        </p>
        <button v-if="mission.description?.length > 150" @click="expanded = !expanded"
          class="text-primary text-xs font-bold mt-1 hover:underline">
          {{ expanded ? 'Voir moins' : 'Voir plus' }}
        </button>
      </div>
      <div class="text-right flex-shrink-0">
        <p class="font-black text-xl text-primary">{{ mission.budget }} DH</p>
        <p class="text-[10px] text-on-surface-variant font-medium">Budget</p>
      </div>
    </div>

    <!-- Categories pills (Languages/Skills) -->
    <div v-if="mission.categories?.length" class="px-6 pb-3 flex flex-wrap gap-2">
      <span v-for="cat in mission.categories" :key="cat.id"
        class="text-[10px] font-bold text-secondary bg-secondary/10 px-3 py-1.5 rounded-lg flex items-center gap-1">
        <span class="material-symbols-outlined" style="font-size: 12px">terminal</span>
        {{ cat.name }}
      </span>
    </div>

    <!-- Meta: deadline -->
    <div class="px-6 pb-3 border-b border-primary/5">
      <p class="text-xs text-on-surface-variant flex items-center gap-1.5">
        <span class="material-symbols-outlined text-sm">calendar_today</span>
        Deadline : {{ mission.deadline ? new Date(mission.deadline).toLocaleDateString('fr-FR') : '—' }}
      </p>
    </div>

    <!-- Counters bar -->
    <div class="px-6 py-2 flex items-center justify-between border-b border-primary/5 text-xs text-on-surface-variant">
      <div class="flex items-center gap-3">
        <button @click="toggleLikes" class="flex items-center gap-1 hover:text-primary transition-colors">
          <span class="w-4 h-4 rounded-full bg-secondary flex items-center justify-center">
            <span class="material-symbols-outlined text-white" style="font-size:10px;font-variation-settings:'FILL' 1">favorite</span>
          </span>
          {{ mission.likes_count || 0 }} j'aime
        </button>
      </div>
      <div class="flex items-center gap-3">
        <button @click="toggleComments" class="hover:text-primary hover:underline transition-colors">
          {{ mission.comments_count || 0 }} commentaire{{ (mission.comments_count || 0) !== 1 ? 's' : '' }}
        </button>
      </div>
    </div>

    <!-- Action buttons -->
    <div class="px-4 py-2 flex items-center gap-2 border-b border-primary/5">
      <button @click="toggleComments"
        :class="showComments ? 'text-primary bg-primary/10' : 'text-on-surface-variant hover:bg-surface-container hover:text-primary'"
        class="flex-1 flex items-center justify-center gap-2 py-2 rounded-xl transition-all font-semibold text-sm">
        <span class="material-symbols-outlined text-base">chat_bubble</span>
        Commentaires
      </button>
      <button @click="toggleLikes"
        :class="showLikes ? 'text-secondary bg-secondary/10' : 'text-on-surface-variant hover:bg-surface-container hover:text-secondary'"
        class="flex-1 flex items-center justify-center gap-2 py-2 rounded-xl transition-all font-semibold text-sm">
        <span class="material-symbols-outlined text-base">favorite</span>
        Favoris
      </button>
    </div>

    <!-- Likes List Section -->
    <div v-if="showLikes" class="px-6 py-4 bg-surface-container/30 border-b border-primary/5 animate-in">
      <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-3 flex items-center gap-2">
        <span class="material-symbols-outlined text-sm font-black">favorite</span>
        Freelancers ayant aimé
      </p>
      <div v-if="loadingLikes" class="flex justify-center py-4">
        <span class="material-symbols-outlined text-primary/30 animate-spin">progress_activity</span>
      </div>
      <div v-else-if="likesList.length" class="flex flex-wrap gap-2">
        <div v-for="user in likesList" :key="user.id" 
             class="flex items-center gap-2 bg-white border border-primary/10 pl-1 pr-3 py-1 rounded-full shadow-sm hover:border-primary/30 transition-colors">
          <div class="w-6 h-6 rounded-full bg-secondary flex items-center justify-center text-white font-bold text-[9px]">
            {{ initials(user.name) }}
          </div>
          <span class="text-xs font-bold text-on-surface">{{ user.name }}</span>
        </div>
      </div>
      <p v-else class="text-center text-xs text-on-surface-variant py-4 italic">Aucun like pour le moment</p>
    </div>

    <!-- Comments section -->
    <div v-if="showComments" class="px-6 py-4 space-y-4">
      <!-- Loading -->
      <div v-if="loadingComments" class="flex justify-center py-3">
        <span class="material-symbols-outlined text-primary/30 animate-spin">progress_activity</span>
      </div>

      <!-- Comments list -->
      <div v-else-if="commentsList.length" class="space-y-3">
        <div v-for="(c, i) in commentsList" :key="i" class="flex gap-3 items-start">
          <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white font-bold text-[10px] flex-shrink-0">
            {{ initials(c.user?.name || c.author) }}
          </div>
          <div class="flex-1 bg-surface-container rounded-2xl px-4 py-3">
            <div class="flex items-center justify-between gap-2 mb-1">
              <p class="font-bold text-xs text-on-surface">{{ c.user?.name || c.author }}</p>
              <button @click="contactFreelancer(c)"
                class="flex items-center gap-1 px-3 py-1 rounded-full bg-primary text-white text-[10px] font-bold hover:scale-105 transition-transform flex-shrink-0">
                <span class="material-symbols-outlined" style="font-size:12px">mail</span>
                Contacter
              </button>
            </div>
            <p class="text-sm text-on-surface-variant leading-relaxed">{{ c.body || c.text }}</p>
          </div>
        </div>
      </div>
      <p v-else class="text-center text-xs text-on-surface-variant py-3">Aucun commentaire pour le moment</p>
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
const showContactModal = ref(false)
const expanded         = ref(false)
const commentsList     = ref([])
const likesList        = ref([])
const loadingComments  = ref(false)
const loadingLikes     = ref(false)
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
