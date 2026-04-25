<template>
  <div class="bg-white rounded-[2rem] border border-primary/10 shadow-sm hover:shadow-md transition-all">

    <!-- Post Header: avatar + name + date + category -->
    <div class="flex items-start justify-between gap-3 p-6 pb-4">
      <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-full bg-primary flex items-center justify-center text-white font-bold text-sm flex-shrink-0 shadow-md shadow-primary/20">
          {{ initials(brief.freelancerName) }}
        </div>
        <div>
          <p class="font-bold text-sm text-on-surface">{{ brief.freelancerName || 'Freelancer' }}</p>
          <div class="flex items-center gap-2 mt-0.5">
            <span class="text-[10px] font-bold text-white bg-primary px-2.5 py-0.5 rounded-full">
              {{ brief.category || 'Sans catégorie' }}
            </span>
            <span class="text-[10px] text-on-surface-variant flex items-center gap-1">
              <span class="material-symbols-outlined text-[11px]">calendar_today</span>
              {{ new Date(brief.createdAt || brief.created_at).toLocaleDateString('fr-FR') }}
            </span>
            <span v-if="brief.timeline" class="text-[10px] text-on-surface-variant flex items-center gap-1 border-l border-primary/10 pl-2">
              <span class="material-symbols-outlined text-[11px]">schedule</span>{{ brief.timeline }}
            </span>
          </div>
        </div>
      </div>
      <div class="text-right flex-shrink-0">
        <p class="text-primary font-black text-xl">{{ brief.price || brief.budget }} DH</p>
        <p class="text-[10px] text-on-surface-variant font-medium">Budget</p>
      </div>
    </div>

    <!-- Post Body: title + full description -->
    <div class="px-6 pb-4">
      <h3 class="font-headline text-lg font-bold text-on-surface mb-2">{{ brief.title }}</h3>
      <p class="text-on-surface-variant text-sm leading-relaxed" :class="expanded ? '' : 'line-clamp-3'">
        {{ brief.description }}
      </p>
      <button v-if="brief.description?.length > 180" @click="expanded = !expanded"
        class="text-primary text-xs font-bold mt-1 hover:underline">
        {{ expanded ? 'Voir moins' : 'Voir plus' }}
      </button>
    </div>

    <!-- Counters bar -->
    <div class="px-6 py-2 flex items-center justify-between border-t border-b border-primary/5 text-xs text-on-surface-variant">
      <div class="flex items-center gap-3">
        <span class="flex items-center gap-1">
          <span class="w-4 h-4 rounded-full bg-secondary flex items-center justify-center">
            <span class="material-symbols-outlined text-white" style="font-size:10px;font-variation-settings:'FILL' 1">favorite</span>
          </span>
          {{ brief.likes || 0 }} j'aime
        </span>
      </div>
      <div class="flex items-center gap-3">
        <button @click="toggleComments" class="hover:text-primary hover:underline transition-colors">
          {{ brief.comments || 0 }} commentaire{{ (brief.comments || 0) !== 1 ? 's' : '' }}
        </button>
        <span>·</span>
        <span>{{ brief.favorites_count || 0 }} favori{{ (brief.favorites_count || 0) !== 1 ? 's' : '' }}</span>
      </div>
    </div>

    <!-- Action buttons row -->
    <div class="px-4 py-2 flex items-center gap-1 border-b border-primary/5">
      <!-- Like -->
      <button @click="toggleLike"
        :class="store.isLiked(brief.id) ? 'text-secondary bg-secondary/10' : 'text-on-surface-variant hover:bg-surface-container hover:text-secondary'"
        class="flex-1 flex items-center justify-center gap-2 py-2 rounded-xl transition-all font-semibold text-sm">
        <span class="material-symbols-outlined text-base"
          :style="store.isLiked(brief.id) ? `font-variation-settings:'FILL' 1` : ''">favorite</span>
        J'aime
      </button>
      <!-- Comment -->
      <button @click="toggleComments"
        :class="showComments ? 'text-primary bg-primary/10' : 'text-on-surface-variant hover:bg-surface-container hover:text-primary'"
        class="flex-1 flex items-center justify-center gap-2 py-2 rounded-xl transition-all font-semibold text-sm">
        <span class="material-symbols-outlined text-base">chat_bubble</span>
        Commenter
      </button>
      <!-- Favorite -->
      <button @click="toggleFavorite"
        :class="store.isFavorited(brief.id) ? 'text-primary bg-primary/10' : 'text-on-surface-variant hover:bg-surface-container hover:text-primary'"
        class="flex-1 flex items-center justify-center gap-2 py-2 rounded-xl transition-all font-semibold text-sm">
        <span class="material-symbols-outlined text-base"
          :style="store.isFavorited(brief.id) ? `font-variation-settings:'FILL' 1` : ''">star</span>
        Favori
      </button>
      <!-- Contact -->
      <button @click="showContactModal = true"
        class="flex-1 flex items-center justify-center gap-2 py-2 rounded-xl bg-primary text-white hover:brightness-110 transition-all font-semibold text-sm">
        <span class="material-symbols-outlined text-base">mail</span>
        Contacter
      </button>
    </div>

    <!-- Comments section -->
    <div v-if="showComments" class="px-6 py-4 space-y-4">
      <!-- Add comment -->
      <div class="flex gap-3 items-start">
        <div class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center text-primary font-bold text-[10px] flex-shrink-0">
          {{ store.userInitials }}
        </div>
        <div class="flex-1 flex gap-2">
          <input v-model="newComment" @keyup.enter="addComment" placeholder="Ajouter un commentaire..."
            class="flex-1 bg-surface-container rounded-full px-4 py-2 text-xs border border-primary/10 focus:border-primary focus:outline-none transition-colors" />
          <button @click="addComment" :disabled="!newComment.trim()"
            class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center disabled:opacity-40 hover:scale-105 transition-transform flex-shrink-0">
            <span class="material-symbols-outlined text-sm">send</span>
          </button>
        </div>
      </div>

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
            <p class="font-bold text-xs text-on-surface">{{ c.user?.name || c.author }}</p>
            <p class="text-sm text-on-surface-variant mt-0.5 leading-relaxed">{{ c.body || c.text }}</p>
          </div>
        </div>
      </div>
      <p v-else class="text-center text-xs text-on-surface-variant py-2">Soyez le premier à commenter</p>
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
            <div class="w-12 h-12 rounded-full bg-primary flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
              {{ initials(brief.freelancerName) }}
            </div>
            <div>
              <p class="font-bold text-on-surface">{{ brief.freelancerName || 'Freelancer' }}</p>
              <p class="text-xs text-on-surface-variant mt-0.5">{{ brief.title }}</p>
            </div>
          </div>
          <textarea v-model="contactMessage" rows="4" placeholder="Bonjour, je suis intéressé par votre profil..."
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

const props = defineProps({ brief: { type: Object, required: true } })
const store = useClientStore()

const showComments     = ref(false)
const showContactModal = ref(false)
const expanded         = ref(false)
const newComment       = ref('')
const contactMessage   = ref('')
const commentsList     = ref(props.brief.commentsList || [])
const loadingComments  = ref(false)
const sending          = ref(false)

const initials = (n) => n?.split(' ').map(x => x[0]).join('').toUpperCase().slice(0, 2) || 'U'

const toggleLike = async () => {
  store.toggleLike(props.brief)
  try { await api.post(`/client/briefs/${props.brief.id}/like`) } catch {}
}

const toggleFavorite = async () => {
  store.toggleFavorite(props.brief)
  try { await api.post(`/client/briefs/${props.brief.id}/favorite`) } catch {}
}

const toggleComments = async () => {
  showComments.value = !showComments.value
  if (showComments.value && !commentsList.value.length) {
    loadingComments.value = true
    try {
      const res = await api.get(`/client/briefs/${props.brief.id}/comments`)
      commentsList.value = res.data
    } catch {} finally { loadingComments.value = false }
  }
}

const addComment = async () => {
  if (!newComment.value.trim()) return
  const text = newComment.value.trim()
  newComment.value = ''
  commentsList.value.push({ user: { name: store.userName }, body: text })
  props.brief.comments = (props.brief.comments || 0) + 1
  try { await api.post(`/client/briefs/${props.brief.id}/comment`, { body: text }) } catch {}
}

const sendContact = async () => {
  if (!contactMessage.value.trim()) return
  sending.value = true
  try {
    const receiverId = props.brief.freelancerId
    if (receiverId) {
      await api.post('/messages', {
        receiver_id: receiverId,
        content: contactMessage.value,
      })
    }
  } catch {}
  showContactModal.value = false
  contactMessage.value   = ''
  window.dispatchEvent(new CustomEvent('client-tab', { detail: 'messages' }))
  setTimeout(() => {
    window.dispatchEvent(new CustomEvent('open-conversation', {
      detail: {
        userId: props.brief.freelancerId,
        name:   props.brief.freelancerName || 'Freelancer',
      }
    }))
  }, 150)
  sending.value = false
}
</script>

<style scoped>
@keyframes slideIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.animate-in { animation: slideIn 0.25s ease-out; }
</style>
