<template>
  <div class="bg-white rounded-[2rem] border border-primary/10 shadow-sm hover:shadow-md transition-all">
    <div class="flex items-start justify-between gap-3 p-6 pb-4">
      <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-full bg-primary flex items-center justify-center text-white font-bold text-sm flex-shrink-0 shadow-md shadow-primary/20">
          {{ initials(mission.client?.name || mission.clientName) }}
        </div>
        <div>
          <p class="font-bold text-sm text-on-surface">{{ mission.client?.name || mission.clientName || 'Client' }}</p>
          <div class="flex items-center gap-2 mt-0.5">
            <span class="text-[10px] font-bold text-white bg-primary px-2.5 py-0.5 rounded-full">
              {{ mission.category?.name || mission.categories?.[0] || 'Sans catégorie' }}
            </span>
            <span v-if="mission.deadline" class="text-[10px] text-on-surface-variant flex items-center gap-1">
              <span class="material-symbols-outlined text-[11px]">schedule</span>{{ mission.deadline }}
            </span>
          </div>
        </div>
      </div>
      <div class="text-right flex-shrink-0">
        <p class="text-primary font-black text-xl">{{ mission.budget || mission.price }} DH</p>
        <p class="text-[10px] text-on-surface-variant font-medium">Budget</p>
      </div>
    </div>

    <div class="px-6 pb-4">
      <h3 class="font-headline text-lg font-bold text-on-surface mb-2">{{ mission.title }}</h3>
      <p class="text-on-surface-variant text-sm leading-relaxed" :class="expanded ? '' : 'line-clamp-3'">{{ mission.description }}</p>
      <button v-if="mission.description?.length > 180" @click="expanded = !expanded" class="text-primary text-xs font-bold mt-1 hover:underline">
        {{ expanded ? 'Voir moins' : 'Voir plus' }}
      </button>
    </div>

    <div v-if="mission.categories?.length" class="px-6 pb-3 flex flex-wrap gap-2">
      <span v-for="(cat, i) in mission.categories" :key="i" class="text-[10px] font-semibold text-primary bg-primary/10 px-3 py-1 rounded-full">{{ cat }}</span>
    </div>

    <div class="px-6 py-2 flex items-center justify-between border-t border-b border-primary/5 text-xs text-on-surface-variant">
      <span class="flex items-center gap-1">
        <span class="w-4 h-4 rounded-full bg-secondary flex items-center justify-center">
          <span class="material-symbols-outlined text-white" style="font-size:10px;font-variation-settings:'FILL' 1">favorite</span>
        </span>
        {{ mission.likes || 0 }} j'aime
      </span>
      <button @click="toggleComments" class="hover:text-primary hover:underline transition-colors">
        {{ mission.comments || 0 }} commentaire{{ (mission.comments || 0) !== 1 ? 's' : '' }}
      </button>
    </div>

    <div class="px-4 py-2 flex items-center gap-1 border-b border-primary/5">
      <button @click="toggleLike"
        :class="store.isLiked(mission.id) ? 'text-secondary bg-secondary/10' : 'text-on-surface-variant hover:bg-surface-container hover:text-secondary'"
        class="flex-1 flex items-center justify-center gap-2 py-2 rounded-xl transition-all font-semibold text-sm">
        <span class="material-symbols-outlined text-base" :style="store.isLiked(mission.id) ? `font-variation-settings:'FILL' 1` : ''">favorite</span>J'aime
      </button>
      <button @click="toggleComments"
        :class="showComments ? 'text-primary bg-primary/10' : 'text-on-surface-variant hover:bg-surface-container hover:text-primary'"
        class="flex-1 flex items-center justify-center gap-2 py-2 rounded-xl transition-all font-semibold text-sm">
        <span class="material-symbols-outlined text-base">chat_bubble</span>Commenter
      </button>
      <button @click="toggleFavorite"
        :class="store.isFavorited(mission.id) ? 'text-primary bg-primary/10' : 'text-on-surface-variant hover:bg-surface-container hover:text-primary'"
        class="flex-1 flex items-center justify-center gap-2 py-2 rounded-xl transition-all font-semibold text-sm">
        <span class="material-symbols-outlined text-base" :style="store.isFavorited(mission.id) ? `font-variation-settings:'FILL' 1` : ''">star</span>Favori
      </button>
      <button @click="showContactModal = true"
        class="flex-1 flex items-center justify-center gap-2 py-2 rounded-xl bg-primary text-white hover:brightness-110 transition-all font-semibold text-sm">
        <span class="material-symbols-outlined text-base">mail</span>Contacter
      </button>
    </div>

    <div v-if="showComments" class="px-6 py-4 space-y-4">
      <div class="flex gap-3 items-start">
        <div class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center text-primary font-bold text-[10px] flex-shrink-0">{{ store.userInitials }}</div>
        <div class="flex-1 flex gap-2">
          <input v-model="newComment" @keyup.enter="addComment" placeholder="Ajouter un commentaire..."
            class="flex-1 bg-surface-container rounded-full px-4 py-2 text-xs border border-primary/10 focus:border-primary focus:outline-none transition-colors" />
          <button @click="addComment" :disabled="!newComment.trim()"
            class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center disabled:opacity-40 hover:scale-105 transition-transform flex-shrink-0">
            <span class="material-symbols-outlined text-sm">send</span>
          </button>
        </div>
      </div>
      <div v-if="commentsList.length" class="space-y-3">
        <div v-for="(c, i) in commentsList" :key="i" class="flex gap-3 items-start">
          <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white font-bold text-[10px] flex-shrink-0">{{ initials(c.author || c.user?.name) }}</div>
          <div class="flex-1 bg-surface-container rounded-2xl px-4 py-3">
            <p class="font-bold text-xs text-on-surface">{{ c.author || c.user?.name }}</p>
            <p class="text-sm text-on-surface-variant mt-0.5 leading-relaxed">{{ c.text || c.body }}</p>
          </div>
        </div>
      </div>
      <p v-else class="text-center text-xs text-on-surface-variant py-2">Soyez le premier à commenter</p>
    </div>

    <Teleport to="body">
      <div v-if="showContactModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="showContactModal = false">
        <div class="bg-white rounded-[2.5rem] shadow-2xl max-w-md w-full p-6 animate-in">
          <div class="flex items-center justify-between mb-4">
            <h2 class="font-headline text-xl font-bold text-on-surface">Contacter le client</h2>
            <button @click="showContactModal = false" class="p-2 hover:bg-primary/10 rounded-full transition-colors"><span class="material-symbols-outlined">close</span></button>
          </div>
          <div class="bg-primary/5 rounded-2xl p-4 mb-4 border border-primary/10 flex items-center gap-3">
            <div class="w-12 h-12 rounded-full bg-primary flex items-center justify-center text-white font-bold text-sm flex-shrink-0">{{ initials(mission.client?.name || mission.clientName) }}</div>
            <div>
              <p class="font-bold text-on-surface">{{ mission.client?.name || mission.clientName || 'Client' }}</p>
              <p class="text-xs text-on-surface-variant mt-0.5">{{ mission.title }}</p>
            </div>
          </div>
          <textarea v-model="contactMessage" rows="4" placeholder="Bonjour, je suis intéressé par votre mission..."
            class="w-full bg-surface-container border border-primary/10 rounded-xl px-4 py-3 text-sm focus:border-primary focus:outline-none resize-none mb-3" />
          <div class="flex gap-3">
            <button @click="showContactModal = false" class="flex-1 px-4 py-2.5 rounded-full border border-primary text-primary font-bold text-sm hover:bg-primary/5 transition-colors">Annuler</button>
            <button @click="sendContact" :disabled="!contactMessage.trim() || sending"
              class="flex-1 px-4 py-2.5 rounded-full bg-primary text-white font-bold text-sm disabled:opacity-50 active:scale-95 transition-all flex items-center justify-center gap-2">
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
defineEmits(['mission-updated'])
const store = useFreelancerStore()

const showComments = ref(false), showContactModal = ref(false), expanded = ref(false)
const newComment = ref(''), contactMessage = ref(''), sending = ref(false)
const commentsList = ref(props.mission.commentsList || [])

const initials = (n) => n?.split(' ').map(x => x[0]).join('').toUpperCase().slice(0, 2) || 'U'

const toggleLike = async () => {
  store.toggleLike(props.mission)
  if (props.mission.id > 0) try { await axios.post(`http://localhost:8000/api/freelancer/missions/${props.mission.id}/like`, {}, { headers: store.authHeaders }) } catch {}
}
const toggleFavorite = async () => {
  const was = store.isFavorited(props.mission.id); store.toggleFavorite(props.mission)
  const f = 'favorites_count' in props.mission ? 'favorites_count' : 'favorites'
  props.mission[f] = Math.max(0, (props.mission[f] || 0) + (was ? -1 : 1))
  if (props.mission.id > 0) try { await axios.post(`http://localhost:8000/api/freelancer/missions/${props.mission.id}/favorite`, {}, { headers: store.authHeaders }) } catch {}
}
const toggleComments = () => { showComments.value = !showComments.value }
const addComment = async () => {
  if (!newComment.value.trim()) return
  const text = newComment.value.trim(); newComment.value = ''
  commentsList.value.push({ author: store.userName, text })
  props.mission.comments = (props.mission.comments || 0) + 1
  if (props.mission.id > 0) try { await axios.post(`http://localhost:8000/api/freelancer/missions/${props.mission.id}/comment`, { text }, { headers: store.authHeaders }) } catch {}
}
const sendContact = async () => {
  if (!contactMessage.value.trim()) return
  sending.value = true
  try {
    const receiverId = props.mission.client?.id || props.mission.client_id
    if (receiverId) {
      await axios.post('http://localhost:8000/api/messages', {
        receiver_id: receiverId,
        content: contactMessage.value
      }, { headers: store.authHeaders })
    }
    // Always close modal and navigate regardless of API result
    showContactModal.value = false
    contactMessage.value   = ''
    window.dispatchEvent(new CustomEvent('freelancer-tab', { detail: 'messages' }))
    setTimeout(() => {
      window.dispatchEvent(new CustomEvent('open-conversation', {
        detail: {
          userId: receiverId,
          name: props.mission.client?.name || props.mission.clientName || 'Client'
        }
      }))
    }, 150)
  } catch (e) {
    // Even on error, close modal and go to messages
    showContactModal.value = false
    contactMessage.value   = ''
    window.dispatchEvent(new CustomEvent('freelancer-tab', { detail: 'messages' }))
  } finally {
    sending.value = false
  }
}
</script>
<style scoped>
@keyframes slideIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.animate-in { animation: slideIn 0.25s ease-out; }
</style>
