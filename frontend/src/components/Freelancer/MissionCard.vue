<template>
  <div class="bg-white rounded-[2.5rem] border border-primary/10 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all p-6 flex flex-col gap-5">
    
    <!-- Header: Client Info + Budget -->
    <div class="flex items-start justify-between gap-4">
      <div class="flex-1">
        <div class="flex items-center gap-2 mb-3">
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

    <!-- Metadata: Deadline + Client Name -->
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

    <!-- Categories display -->
    <div v-if="mission.categories && mission.categories.length > 0" class="flex flex-wrap gap-2">
      <span 
        v-for="(cat, idx) in mission.categories" 
        :key="idx"
        class="text-[10px] font-semibold text-primary bg-primary/10 px-3 py-1 rounded-full"
      >
        {{ cat }}
      </span>
    </div>

    <!-- Action buttons row -->
    <div class="flex items-center justify-between pt-4 border-t border-primary/5">
      <!-- Left: Like + Comment buttons -->
      <div class="flex items-center gap-3">
        <!-- Like Button -->
        <button
          @click="toggleLike"
          :class="store.isLiked(mission.id) ? 'bg-secondary/20 text-secondary' : 'bg-surface-container text-on-surface-variant hover:bg-primary/10 hover:text-primary'"
          class="flex items-center gap-2 px-4 py-2 rounded-full transition-all font-semibold text-sm"
        >
          <span class="material-symbols-outlined text-base" :style="store.isLiked(mission.id) ? `font-variation-settings: 'FILL' 1` : ''">favorite</span>
          <span class="text-xs font-bold">{{ mission.likes || 0 }}</span>
        </button>

        <!-- Comment Button -->
        <button
          @click="toggleComments"
          :class="showComments ? 'bg-primary text-white' : 'bg-surface-container text-on-surface-variant hover:bg-primary/10 hover:text-primary'"
          class="flex items-center gap-2 px-4 py-2 rounded-full transition-all font-semibold text-sm"
        >
          <span class="material-symbols-outlined text-base">chat_bubble</span>
          <span class="text-xs font-bold">{{ mission.comments || 0 }}</span>
        </button>
      </div>

      <!-- Right: Favorite + Contact buttons -->
      <div class="flex items-center gap-3">
        <!-- Favorite Button -->
        <button
          @click="toggleFavorite"
          :class="store.isFavorited(mission.id) ? 'bg-secondary/20 text-secondary' : 'bg-surface-container text-on-surface-variant hover:bg-primary/10 hover:text-primary'"
          class="flex items-center gap-2 px-4 py-2 rounded-full transition-all font-semibold text-sm"
          title="Ajouter aux favoris"
        >
          <span class="material-symbols-outlined text-base" :style="store.isFavorited(mission.id) ? `font-variation-settings: 'FILL' 1` : ''">star</span>
        </button>

        <!-- Contact Button -->
        <button
          @click="openContact"
          class="flex items-center gap-2 px-6 py-2 rounded-full bg-primary text-white hover:scale-105 transition-transform font-semibold text-sm shadow-lg shadow-primary/20"
        >
          <span class="material-symbols-outlined text-base">mail</span>
          Contacter
        </button>
      </div>
    </div>

    <!-- Comments Section -->
    <div v-if="showComments" class="mt-4 pt-4 border-t border-primary/5 space-y-3">
      <!-- Add comment form -->
      <div class="flex gap-3">
        <input
          v-model="newComment"
          placeholder="Ajouter un commentaire..."
          class="flex-1 bg-surface-container rounded-full px-4 py-2 text-xs border border-primary/10 focus:border-primary focus:outline-none transition-colors"
        />
        <button
          @click="addComment"
          :disabled="!newComment.trim()"
          class="px-4 py-2 bg-primary text-white rounded-full font-semibold text-xs disabled:opacity-50 hover:scale-105 transition-transform"
        >
          <span class="material-symbols-outlined text-sm">send</span>
        </button>
      </div>

      <!-- Comments list -->
      <div v-if="mission.commentsList && mission.commentsList.length > 0" class="space-y-2 max-h-48 overflow-y-auto">
        <div
          v-for="(comment, idx) in mission.commentsList"
          :key="idx"
          class="bg-surface-container-low p-3 rounded-[1.25rem] text-xs"
        >
          <p class="font-semibold text-on-surface">{{ comment.author }}</p>
          <p class="text-on-surface-variant mt-1">{{ comment.text }}</p>
        </div>
      </div>

      <!-- No comments state -->
      <div v-else class="text-center py-4 text-on-surface-variant text-xs">
        Aucun commentaire pour le moment
      </div>
    </div>

    <!-- Contact Modal -->
    <Teleport to="body">
      <div v-if="showContactModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-[2.5rem] shadow-2xl max-w-md w-full p-6 animate-in">
          <!-- Modal Header -->
          <div class="flex items-center justify-between mb-4">
            <h2 class="font-headline text-xl font-bold text-on-surface">Contacter le client</h2>
            <button
              @click="showContactModal = false"
              class="p-2 hover:bg-primary/10 rounded-full transition-colors"
            >
              <span class="material-symbols-outlined">close</span>
            </button>
          </div>

          <!-- Client Info -->
          <div class="bg-primary/5 rounded-2xl p-4 mb-4 border border-primary/10">
            <p class="text-sm font-bold text-on-surface">
              {{ mission.client?.name || mission.clientName || 'Client' }}
            </p>
            <p class="text-xs text-on-surface-variant mt-1">Détails: {{ mission.title }}</p>
          </div>

          <!-- Message Form -->
          <div class="space-y-3">
            <div>
              <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-2">
                Votre message
              </label>
              <textarea
                v-model="contactMessage"
                placeholder="Exprimez votre intérêt pour cette mission..."
                class="w-full bg-surface-container border border-primary/10 rounded-xl px-4 py-3 text-xs focus:border-primary focus:outline-none transition-colors resize-none h-24"
              />
            </div>

            <!-- Action buttons -->
            <div class="flex gap-3">
              <button
                @click="showContactModal = false"
                class="flex-1 px-4 py-2 rounded-full border border-primary text-primary font-bold text-sm hover:bg-primary/5 transition-colors"
              >
                Annuler
              </button>
              <button
                @click="sendContact"
                :disabled="!contactMessage.trim()"
                class="flex-1 px-4 py-2 rounded-full bg-primary text-white font-bold text-sm disabled:opacity-50 hover:scale-105 transition-transform"
              >
                Envoyer
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
import { useFreelancerStore } from '@/stores/freelancer'
import axios from 'axios'

const props = defineProps({
  mission: {
    type: Object,
    required: true
  }
})

const store = useFreelancerStore()

// Local state
const showComments = ref(false)
const showContactModal = ref(false)
const newComment = ref('')
const contactMessage = ref('')

const toggleLike = async () => {
  store.toggleLike(props.mission)
  try {
    await axios.post(
      `http://localhost:8000/api/freelancer/missions/${props.mission.id}/like`,
      {},
      { headers: store.authHeaders }
    )
  } catch (error) {
    console.error('Erreur lors du like:', error)
  }
}

const toggleFavorite = async () => {
  store.toggleFavorite(props.mission)
  try {
    await axios.post(
      `http://localhost:8000/api/freelancer/missions/${props.mission.id}/favorite`,
      {},
      { headers: store.authHeaders }
    )
  } catch (error) {
    console.error('Erreur lors du favori:', error)
  }
}

const toggleComments = () => {
  showComments.value = !showComments.value
}

const addComment = async () => {
  if (!newComment.value.trim()) return

  try {
    const response = await axios.post(
      `http://localhost:8000/api/freelancer/missions/${props.mission.id}/comment`,
      { text: newComment.value },
      { headers: store.authHeaders }
    )

    // Add comment to local list
    if (!props.mission.commentsList) {
      props.mission.commentsList = []
    }
    props.mission.commentsList.push({
      author: store.userName,
      text: newComment.value
    })

    // Increment comments count
    props.mission.comments = (props.mission.comments || 0) + 1

    newComment.value = ''
  } catch (error) {
    console.error('Erreur lors du commentaire:', error)
  }
}

const openContact = () => {
  showContactModal.value = true
  contactMessage.value = ''
}

const sendContact = async () => {
  if (!contactMessage.value.trim()) return

  try {
    await axios.post(
      `http://localhost:8000/api/messages`,
      {
        receiver_id: props.mission.client?.id,
        content: contactMessage.value
      },
      { headers: store.authHeaders }
    )

    showContactModal.value = false
    contactMessage.value = ''
    
    // Show success feedback
    alert('Message envoyé avec succès!')
  } catch (error) {
    console.error('Erreur lors de l\'envoi du message:', error)
    alert('Erreur lors de l\'envoi du message')
  }
}
</script>

<style scoped>
@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-in {
  animation: slideIn 0.3s ease-out;
}

::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}
</style>
