<template>
  <!--
    NavNotifications — Composant notifications de la navbar
    ─────────────────────────────────────────────────────────
    Rôle : Afficher les notifications du freelancer en temps réel.

    Trois types de notifications :
      - 'message' : nouveau message d'un client
      - 'like'    : like reçu sur un brief du freelancer
      - 'comment' : commentaire reçu sur un brief du freelancer

    Synchronisation :
      - Lit notifications et unreadCount depuis le store freelancer
      - Appelle store.markAllRead() à l'ouverture du dropdown
      - Appelle store.markRead(id) au clic sur une notification
      - Isolation totale : une erreur ici ne casse pas la navbar
  -->
  <div class="relative">

    <!-- Bouton icône notifications -->
    <button
      @click="toggle"
      class="p-2 rounded-full hover:bg-primary/10 transition-colors relative group"
      title="Notifications"
    >
      <span class="material-symbols-outlined text-on-surface-variant group-hover:text-primary transition-colors">
        notifications
      </span>
      <!-- Badge nombre non lus -->
      <span
        v-if="store.unreadCount > 0"
        class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] bg-secondary text-white text-[10px] font-black rounded-full flex items-center justify-center px-1 animate-pulse"
      >
        {{ store.unreadCount > 9 ? '9+' : store.unreadCount }}
      </span>
    </button>

    <!-- Dropdown notifications -->
    <Transition name="dropdown">
      <div
        v-if="open"
        v-click-outside="close"
        class="absolute top-12 right-0 w-80 bg-white rounded-2xl shadow-2xl border border-primary/5 z-50 overflow-hidden"
      >
        <!-- En-tête dropdown -->
        <div class="flex justify-between items-center px-4 py-3 border-b border-primary/5">
          <p class="font-bold text-sm text-on-surface">Notifications</p>
          <button
            v-if="store.unreadCount > 0"
            @click="store.markAllRead()"
            class="text-[10px] font-bold text-primary hover:underline uppercase tracking-widest"
          >
            Tout marquer lu
          </button>
        </div>

        <!-- Liste des notifications -->
        <div class="max-h-72 overflow-y-auto divide-y divide-primary/5">
          <div
            v-for="notif in store.notifications"
            :key="notif.id"
            @click="handleClick(notif)"
            :class="!notif.read ? 'bg-primary/5' : 'bg-white'"
            class="flex gap-3 px-4 py-3 hover:bg-primary/10 transition-colors cursor-pointer"
          >
            <!-- Icône selon le type -->
            <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center" :class="iconBg(notif.type)">
              <span class="material-symbols-outlined text-sm text-white" style="font-variation-settings: 'FILL' 1">
                {{ iconName(notif.type) }}
              </span>
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-semibold text-xs text-on-surface truncate">{{ notif.title }}</p>
              <p class="text-[11px] text-on-surface-variant mt-0.5 line-clamp-2">{{ notif.message }}</p>
            </div>
            <!-- Point non lu -->
            <div v-if="!notif.read" class="w-2 h-2 bg-secondary rounded-full flex-shrink-0 mt-1"></div>
          </div>

          <!-- État vide -->
          <div v-if="store.notifications.length === 0" class="text-center py-8">
            <span class="material-symbols-outlined text-3xl text-on-surface-variant/30 block mb-2">notifications_off</span>
            <p class="text-xs text-on-surface-variant">Aucune notification</p>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useFreelancerStore } from '@/stores/freelancer'

const store  = useFreelancerStore()
const router = useRouter()
const open   = ref(false)

/** Ouvre/ferme le dropdown et marque les notifs comme lues à l'ouverture */
const toggle = () => {
  open.value = !open.value
  if (open.value) store.markAllRead()
}

const close = () => { open.value = false }

/**
 * Gère le clic sur une notification :
 *  - Marque comme lue
 *  - Redirige vers la page appropriée selon le type
 */
const handleClick = (notif) => {
  store.markRead(notif.id)
  if (notif.type === 'message') router.push('/messaging')
  close()
}

/** Couleur de fond de l'icône selon le type de notification */
const iconBg = (type) => ({
  message: 'bg-primary',
  like:    'bg-secondary',
  comment: 'bg-tertiary',
}[type] || 'bg-primary')

/** Icône Material Symbols selon le type */
const iconName = (type) => ({
  message: 'chat_bubble',
  like:    'favorite',
  comment: 'comment',
}[type] || 'notifications')

/**
 * Directive v-click-outside (inline pour éviter une dépendance externe)
 * Ferme le dropdown si le clic est en dehors du composant
 */
const vClickOutside = {
  mounted(el, binding) {
    el._clickOutside = (e) => { if (!el.contains(e.target)) binding.value() }
    document.addEventListener('click', el._clickOutside)
  },
  unmounted(el) {
    document.removeEventListener('click', el._clickOutside)
  },
}
</script>

<style scoped>
/* Animation d'apparition du dropdown */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>
