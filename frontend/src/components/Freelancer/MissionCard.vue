<template>
  <div class="bg-white rounded-[2.5rem] border border-primary/10 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all p-6 flex flex-col gap-5">

    <!-- Header -->
    <div class="flex items-start justify-between gap-4">
      <div class="flex-1">
        <div class="flex flex-wrap gap-2 mb-3">
          <span class="text-[10px] font-bold uppercase tracking-widest text-white bg-primary px-3 py-1 rounded-full">
            {{ mission.category?.name || mission.categories?.[0] || 'Sans catégorie' }}
          </span>
        </div>
        <h3 class="font-headline text-xl font-bold text-on-surface mb-2">{{ mission.title }}</h3>
        <p class="text-on-surface-variant text-sm line-clamp-2">{{ mission.description }}</p>
      </div>
      <div class="text-right flex-shrink-0">
        <p class="text-primary font-bold text-2xl">{{ mission.budget || mission.price }} DH</p>
        <p class="text-on-surface-variant text-xs mt-1">Budget</p>
      </div>
    </div>

    <!-- Meta -->
    <div class="flex items-center gap-6 pb-4 border-b border-primary/5">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-on-surface-variant text-sm">schedule</span>
        <span class="text-sm text-on-surface-variant">{{ mission.deadline }}</span>
      </div>
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-on-surface-variant text-sm">person</span>
        <span class="text-sm text-on-surface-variant">{{ mission.client?.name || mission.clientName || 'Client' }}</span>
      </div>
    </div>

    <!-- Categories pills -->
    <div v-if="mission.categories?.length" class="flex flex-wrap gap-2">
      <span v-for="(cat, i) in mission.categories" :key="i"
        class="text-[10px] font-semibold text-primary bg-primary/10 px-3 py-1 rounded-full">{{ cat }}</span>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-between pt-4 border-t border-primary/5">
      <div class="flex items-center gap-3">
        <!-- Like -->
        <button @click="toggleLike"
          :class="store.isLiked(mission.id) ? 'bg-secondary/20 text-secondary' : 'bg-surface-container text-on-surface-variant hover:bg-primary/10 hover:text-primary'"
          class="flex items-center gap-2 px-4 py-2 rounded-full transition-all text-sm font-semibold">
          <span class="material-symbols-outlined text-base"
            :style="store.isLiked(mission.id) ? `font-variation-settings: 'FILL' 1` : ''">favorite</span>
          <span class="text-xs font-bold">{{ mission.likes || 0 }}</span>
        </button>

        <!-- Comment -->
        <button @click="showComments = !showComments"
          :class="showComments ? 'bg-primary text-white' : 'bg-surface-container text-on-surface-variant hover:bg-primary/10 hover:text-primary'"
          class="flex items-center gap-2 px-4 py-2 rounded-full transition-all text-sm font-semibold">
          <span class="material-symbols-outlined text-base">chat_bubble</span>
          <span class="text-xs font-bold">{{ mission.comments || 0 }}</span>
        </button>
      </div>

      <div class="flex items-center gap-3">
        <!-- Favorite -->
        <button @click="toggleFavorite"
          :class="store.isFavorited(mission.id) ? 'bg-secondary/20 text-secondary' : 'bg-surface-container text-on-surface-variant hover:bg-primary/10 hover:text-primary'"
          class="flex items-center gap-2 px-4 py-2 rounded-full transition-all text-sm font-semibold"
          title="Ajouter aux favoris">
          <span class="material-symbols-outlined text-base"
            :style="store.isFavorited(mission.id) ? `font-variation-settings: 'FILL' 1` : ''">star</span>
          <span class="text-xs font-bold">{{ mission.favorites_count ?? mission.favorites ?? 0 }}</span>
        </button>

        <!-- Contact -->
        <button @click="showContactModal = true"
          class="flex items-center gap-2 px-6 py-2 rounded-full bg-primary text-white hover:scale-105 transition-transform font-semibold text-sm shadow-lg shadow-primary/20">
          <span class="material-symbols-outlined text-base">mail</span>
          Contacter
        </button>
      </div>
    </div>

    <!-- Comments section -->
    <div v-if="showComments" class="mt-2 pt-4 border-t border-primary/5 space-y-3">
      <div class="flex gap-3">
        <input v-model="newComment" @keyup.enter="addComment" placeholder="Ajouter un commentaire..."
          class="flex-1 bg-surface-container rounded-full px-4 py-2 text-xs border border-primary/10 focus:border-primary focus:outline-none transition-colors" />
        <button @click="addComment" :disabled="!newComment.trim()"
          class="px-4 py-2 bg-primary text-white rounded-full text-xs font-semibold disabled:opacity-50 hover:scale-105 transition-transform">
          <span class="material-symbols-outlined text-sm">send</span>
        </button>
      </div>
      <div v-if="mission.commentsList?.length" class="space-y-2 max-h-48 overflow-y-auto">
        <div v-for="(c, i) in mission.commentsList" :key="i"
          class="bg-surface-container-low p-3 rounded-[1.25rem] text-xs">
          <p class="font-semibold text-on-surface">{{ c.author }}</p>
          <p class="text-on-surface-variant mt-1">{{ c.text }}</p>
        </div>
      </div>
      <p v-else class="text-center py-3 text-on-surface-variant text-xs">Aucun commentaire pour le moment</p>
    </div>

    <!-- Contact Modal -->
    <Teleport to="body">
      <div v-if="showContactModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="showContactModal = false">
        <div class="bg-white rounded-[2.5rem] shadow-2xl max-w-md w-full p-6 animate-in">
          <div class="flex items-center justify-between mb-4">
            <h2 class="font-headline text-xl font-bold text-on-surface">Contacter le client</h2>
            <button @click="showContactModal = false" class="p-2 hover:bg-primary/10 rounded-full transition-colors">
              <span class="material-symbols-outlined">close</span>
            </button>
          </div>
          <div class="bg-primary/5 rounded-2xl p-4 mb-4 border border-primary/10 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
              {{ initials(mission.client?.name || mission.clientName) }}
            </div>
            <div>
              <p class="text-sm font-bold text-on-surface">{{ mission.client?.name || mission.clientName || 'Client' }}</p>
              <p class="text-xs text-on-surface-variant">{{ mission.title }}</p>
            </div>
          </div>
          <textarea v-model="contactMessage" rows="3" placeholder="Exprimez votre intérêt pour cette mission..."
            class="w-full bg-surface-container border border-primary/10 rounded-xl px-4 py-3 text-sm focus:border-primary focus:outline-none resize-none mb-3" />
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
    </Teleport>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useFreelancerStore } from '@/stores/freelancer'
import axios from 'axios'

const props = defineProps({ mission: { type: Object, required: true } })
const store = useFreelancerStore()

const showComments     = ref(false)
const showContactModal = ref(false)
const newComment       = ref('')
const contactMessage   = ref('')
const sending          = ref(false)

const initials = (name) => name?.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) || 'U'

const toggleLike = async () => {
  store.toggleLike(props.mission)
  try { await axios.post(`http://localhost:8000/api/freelancer/missions/${props.mission.id}/like`, {}, { headers: store.authHeaders }) } catch {}
}

const toggleFavorite = async () => {
  const was = store.isFavorited(props.mission.id)
  store.toggleFavorite(props.mission)
  const field = 'favorites_count' in props.mission ? 'favorites_count' : 'favorites'
  props.mission[field] = Math.max(0, (props.mission[field] || 0) + (was ? -1 : 1))
  try { await axios.post(`http://localhost:8000/api/freelancer/missions/${props.mission.id}/favorite`, {}, { headers: store.authHeaders }) } catch {}
}

const addComment = async () => {
  if (!newComment.value.trim()) return
  const text = newComment.value.trim()
  newComment.value = ''
  if (!props.mission.commentsList) props.mission.commentsList = []
  props.mission.commentsList.push({ author: store.userName, text })
  props.mission.comments = (props.mission.comments || 0) + 1
  try { await axios.post(`http://localhost:8000/api/freelancer/missions/${props.mission.id}/comment`, { text }, { headers: store.authHeaders }) } catch {}
}

const sendContact = async () => {
  if (!contactMessage.value.trim()) return
  sending.value = true
  try {
    await axios.post('http://localhost:8000/api/messages', {
      receiver_id: props.mission.client?.id,
      content: contactMessage.value,
    }, { headers: store.authHeaders })
    showContactModal.value = false
    contactMessage.value   = ''
  } catch {} finally {
    sending.value = false
  }
}
</script>

<style scoped>
@keyframes slideIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.animate-in { animation: slideIn 0.25s ease-out; }
</style>
