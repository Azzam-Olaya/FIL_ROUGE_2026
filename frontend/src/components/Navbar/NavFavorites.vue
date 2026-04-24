<template>
  <!--
    NavFavorites — Composant favoris de la navbar
    ───────────────────────────────────────────────
    Rôle : Afficher la liste des missions favorites du freelancer
           et permettre de les retirer directement depuis la navbar.

    Synchronisation :
      - Lit favoritedMissions depuis le store freelancer
      - Appelle store.toggleFavorite() pour retirer un favori
      - Quand le feed appelle toggleFavorite(), ce composant
        se met à jour automatiquement (réactivité Pinia)
      - Le badge reflète le nombre de favoris en temps réel
  -->
  <div class="relative">

    <!-- Bouton icône favoris -->
    <button
      @click="toggle"
      class="p-2 rounded-full hover:bg-primary/10 transition-colors relative group"
      title="Missions favorites"
    >
      <span
        class="material-symbols-outlined text-on-surface-variant group-hover:text-primary transition-colors"
        :style="store.favoritedMissions.length ? `font-variation-settings: 'FILL' 1` : ''"
        :class="store.favoritedMissions.length ? 'text-primary' : ''"
      >
        favorite
      </span>
      <!-- Badge compteur favoris -->
      <span
        v-if="store.favoritedMissions.length > 0"
        class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] bg-secondary text-white text-[10px] font-black rounded-full flex items-center justify-center px-1"
      >
        {{ store.favoritedMissions.length > 9 ? '9+' : store.favoritedMissions.length }}
      </span>
    </button>

    <!-- Dropdown favoris -->
    <Transition name="dropdown">
      <div
        v-if="open"
        v-click-outside="close"
        class="absolute top-12 right-0 w-80 bg-white rounded-2xl shadow-2xl border border-primary/5 z-50 overflow-hidden"
      >
        <!-- En-tête -->
        <div class="px-4 py-3 border-b border-primary/5">
          <p class="font-bold text-sm text-on-surface">
            Missions favorites
            <span class="text-on-surface-variant font-normal">({{ store.favoritedMissions.length }})</span>
          </p>
        </div>

        <!-- Liste des missions favorites -->
        <div class="max-h-80 overflow-y-auto divide-y divide-primary/5">
          <div
            v-for="mission in store.favoritedMissions"
            :key="mission.id"
            class="flex gap-3 px-4 py-3 hover:bg-primary/5 transition-colors group/item"
          >
            <!-- Avatar client -->
            <div class="w-9 h-9 rounded-full bg-primary flex items-center justify-center text-white font-bold text-[10px] flex-shrink-0">
              {{ getInitials(mission.clientName || mission.client?.name) }}
            </div>

            <!-- Infos mission -->
            <div class="flex-1 min-w-0">
              <p class="font-semibold text-xs text-on-surface truncate">{{ mission.title }}</p>
              <p class="text-[11px] text-on-surface-variant mt-0.5">
                {{ mission.clientName || mission.client?.name || '—' }}
              </p>
              <div class="flex gap-2 mt-1">
                <span class="text-[10px] font-bold text-primary bg-primary/10 px-2 py-0.5 rounded-full">
                  {{ mission.budget }} MAD
                </span>
                <span class="text-[10px] text-on-surface-variant bg-surface-container px-2 py-0.5 rounded-full">
                  {{ mission.category || mission.category?.name }}
                </span>
              </div>
            </div>

            <!-- Bouton retirer des favoris -->
            <button
              @click.stop="store.toggleFavorite(mission)"
              class="flex-shrink-0 p-1.5 rounded-full hover:bg-secondary/10 transition-colors opacity-0 group-hover/item:opacity-100"
              title="Retirer des favoris"
            >
              <span class="material-symbols-outlined text-secondary text-sm" style="font-variation-settings: 'FILL' 1">
                favorite
              </span>
            </button>
          </div>

          <!-- État vide -->
          <div v-if="store.favoritedMissions.length === 0" class="text-center py-8">
            <span class="material-symbols-outlined text-3xl text-on-surface-variant/30 block mb-2">favorite_border</span>
            <p class="text-xs text-on-surface-variant">Aucune mission en favori</p>
            <p class="text-[10px] text-on-surface-variant/60 mt-1">Cliquez sur ⭐ dans le feed</p>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useFreelancerStore } from '@/stores/freelancer'

const store = useFreelancerStore()
const open  = ref(false)

const toggle = () => { open.value = !open.value }
const close  = () => { open.value = false }

/** Génère les initiales d'un nom pour l'avatar */
const getInitials = (name) =>
  name?.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) || 'U'

/** Directive v-click-outside pour fermer le dropdown au clic extérieur */
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
