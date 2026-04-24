<template>
  <div class="bg-white rounded-[2rem] border border-primary/10 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all p-6 flex flex-col gap-4">

    <!-- Header: category + title + price -->
    <div class="flex items-start justify-between gap-4">
      <div class="flex-1">
        <div class="flex flex-wrap items-center gap-2 mb-2">
          <span class="text-[10px] font-bold uppercase tracking-widest text-white bg-primary px-3 py-1 rounded-full">
            {{ brief.category?.name || 'Sans catégorie' }}
          </span>
        </div>
        <h3 class="font-headline text-lg font-bold text-on-surface mb-1">{{ brief.title }}</h3>
        <p class="text-on-surface-variant text-sm line-clamp-2">{{ brief.description }}</p>
      </div>
      <div v-if="brief.price || brief.budget" class="text-right flex-shrink-0">
        <p class="text-primary font-bold text-xl">{{ brief.price || brief.budget }} DH</p>
        <p class="text-on-surface-variant text-xs mt-0.5">Budget</p>
      </div>
    </div>

    <!-- Meta: timeline + freelancer -->
    <div class="flex items-center gap-5 pb-3 border-b border-primary/5 text-sm text-on-surface-variant">
      <div v-if="brief.timeline" class="flex items-center gap-1.5">
        <span class="material-symbols-outlined text-sm">schedule</span>
        <span>{{ brief.timeline }}</span>
      </div>
      <div class="flex items-center gap-1.5">
        <div class="w-6 h-6 rounded-full bg-primary flex items-center justify-center text-white text-[10px] font-bold">
          {{ initials(brief.freelancer?.name) }}
        </div>
        <span>{{ brief.freelancer?.name || 'Freelancer' }}</span>
      </div>
    </div>

    <!-- Action buttons -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <!-- Like -->
        <button @click="toggleLike"
          :class="store.isLiked(brief.id) ? 'bg-secondary/15 text-secondary' : 'bg-surface-container text-on-surface-variant hover:bg-primary/10 hover:text-primary'"
          class="flex items-center gap-1.5 px-3 py-2 rounded-full transition-all text-sm font-semibold">
          <span class="material-symbols-outlined text-base"
            :style="store.isLiked(brief.id) ? `font-variation-settings: 'FILL' 1` : ''">favorite</span>
          <span class="text-xs font-bold">{{ brief.likes_count || 0 }}</span>
        </button>

        <!-- Comment -->
        <button @click="showComments = !showComments"
          :class="showComments ? 'bg-primary text-white' : 'bg-surface-container text-on-surface-variant hover:bg-primary/10 hover:text-primary'"
          class="flex items-center gap-1.5 px-3 py-2 rounded-full transition-all text-sm font-semibold">
          <span class="material-symbols-outlined text-base">chat_bubble</span>
          <span class="text-xs font-bold">{{ brief.comments_count || 0 }}</span>
        </button>
      </div>

      <div class="flex items-center gap-2">
        <!-- Favorite -->
        <button @click="toggleFavorite"
          :class="store.isFavorited(brief.id) ? 'bg-secondary/15 text-secondary' : 'bg-surface-container text-on-surface-variant hover:bg-primary/10 hover:text-primary'"
          class="flex items-center gap-1.5 px-3 py-2 rounded-full transition-all text-sm font-semibold"
          title="Ajouter aux favoris">
          <span class="material-symbols-outlined text-base"
            :style="store.isFavorited(brief.id) ? `font-variation-settings: 'FILL' 1` : ''">star</span>
          <span class="text-xs font-bold">{{ brief.favorites_count || 0 }}</span>
        </button>

        <!-- Contact -->
        <button @click="showContactModal = true"
          class="flex items-center gap-1.5 px-5 py-2 rounded-full bg-primary text-white hover:scale-105 active:scale-95 transition-transform font-semibold text-sm shadow-lg shadow-primary/20">
          <span class="material-symbols-outlined text-base">mail</span>
          Contacter
        </button>
      </div>
    </div>

    <!-- Comments section -->
    <div v-if="showComments" class="pt-3 border-t border-primary/5 space-y-3">
      <div class="flex gap-2">
        <input v-model="newComment" @keyup.enter="addComment"
          placeholder="Ajouter un commentaire..."
          class="flex-1 bg-surface-container rounded-full px-4 py-2 text-xs border border-primary/10 focus:border-primary focus:outline-none transition-colors" />
        <button @click="addComment" :disabled="!newComment.trim()"
          class="px-4 py-2 bg-primary text-white rounded-full text-xs font-semibold disabled:opacity-40 hover:scale-105 transition-transform">
          <span class="material-symbols-outlined text-sm">send</span>
        </button>
      </div>

      <div v-if="loadingComments" class="text-center py-3">
        <span class="material-symbols-outlined text-primary/30 animate-spin">progress_activity</span>
      </div>
      <div v-else-if="commentsList.length > 0" class="space-y-2 max-h-48 overflow-y-auto">
        <div v-for="(c, i) in commentsList" :key="i"
          class="bg-surface-container-low p-3 rounded-[1.25rem] text-xs">
          <p class="font-semibold text-on-surface">{{ c.user?.name || c.author }}</p>
          <p class="text-on-surface-variant mt-0.5">{{ c.body || c.text }}</p>
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
            <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
              {{ initials(brief.freelancer?.name) }}
            </div>
            <div>
              <p class="text-sm font-bold text-on-surface">{{ brief.freelancer?.name || 'Freelancer' }}</p>
              <p class="text-xs text-on-surface-variant">{{ brief.title }}</p>
            </div>
          </div>

          <div class="space-y-3">
            <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-1">Votre message</label>
            <textarea v-model="contactMessage"
              placeholder="Exprimez votre intérêt pour ce freelancer..."
              class="w-full bg-surface-container border border-primary/10 rounded-xl px-4 py-3 text-sm focus:border-primary focus:outline-none transition-colors resize-none h-24" />
            <div class="flex gap-3">
              <button @click="showContactModal = false"
                class="flex-1 px-4 py-2 rounded-full border border-primary text-primary font-bold text-sm hover:bg-primary/5 transition-colors">
                Annuler
              </button>
              <button @click="sendContact" :disabled="!contactMessage.trim() || sending"
                class="flex-1 px-4 py-2 rounded-full bg-primary text-white font-bold text-sm disabled:opacity-50 hover:scale-105 transition-transform flex items-center justify-center gap-2">
                <span v-if="sending" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                <span v-else>Envoyer</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useClientStore } from '@/stores/client'
import axios from 'axios'

const props = defineProps({ brief: { type: Object, required: true } })

const store = useClientStore()

const showComments    = ref(false)
const showContactModal = ref(false)
const newComment      = ref('')
const contactMessage  = ref('')
const commentsList    = ref(props.brief.commentsList || [])
const loadingComments = ref(false)
const sending         = ref(false)

const initials = (name) => name?.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) || 'U'

const toggleLike = async () => {
  store.toggleLike(props.brief)
  try {
    await axios.post(`http://localhost:8000/api/client/briefs/${props.brief.id}/like`, {}, { headers: store.authHeaders })
  } catch {}
}

const toggleFavorite = async () => {
  store.toggleFavorite(props.brief)
  try {
    await axios.post(`http://localhost:8000/api/client/briefs/${props.brief.id}/favorite`, {}, { headers: store.authHeaders })
  } catch {}
}

const loadComments = async () => {
  if (commentsList.value.length > 0) return
  loadingComments.value = true
  try {
    const res = await axios.get(`http://localhost:8000/api/client/briefs/${props.brief.id}/comments`, { headers: store.authHeaders })
    commentsList.value = res.data
  } catch {} finally {
    loadingComments.value = false
  }
}

const addComment = async () => {
  if (!newComment.value.trim()) return
  const text = newComment.value.trim()
  newComment.value = ''
  commentsList.value.push({ user: { name: store.userName }, body: text })
  props.brief.comments_count = (props.brief.comments_count || 0) + 1
  try {
    await axios.post(`http://localhost:8000/api/client/briefs/${props.brief.id}/comment`, { text }, { headers: store.authHeaders })
  } catch {}
}

const sendContact = async () => {
  if (!contactMessage.value.trim() || !props.brief.freelancer?.id) return
  sending.value = true
  try {
    await axios.post('http://localhost:8000/api/messages', {
      receiver_id: props.brief.freelancer.id,
      content: contactMessage.value,
    }, { headers: store.authHeaders })
    showContactModal.value = false
    contactMessage.value   = ''
  } catch {
    // Still close modal on error — message may have been sent
    showContactModal.value = false
  } finally {
    sending.value = false
  }
}

// Load comments when section opens
const _origToggle = () => {
  showComments.value = !showComments.value
  if (showComments.value) loadComments()
}
</script>

<style scoped>
@keyframes slideIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.animate-in { animation: slideIn 0.25s ease-out; }
</style>
