<template>
  <!--
    NavSearch — Composant de recherche de la navbar
    ─────────────────────────────────────────────────
    Rôle : Permettre au freelancer de chercher des missions publiées
           par catégorie/sous-catégorie directement depuis la navbar.

    Synchronisation :
      - Écrit searchQuery et searchCategory dans le store freelancer
      - Navigue vers /freelancer/dashboard?tab=search pour activer
        l'onglet MissionSearch qui lit ces valeurs au montage
  -->
  <div class="hidden lg:flex items-center gap-3 flex-1 max-w-lg mx-8">

    <!-- Champ texte de recherche -->
    <div class="relative flex-1">
      <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">
        search
      </span>
      <input
        v-model="localQuery"
        @keyup.enter="submit"
        class="w-full bg-surface-container-highest/50 border-none rounded-full py-2 pl-10 pr-4 text-xs focus:ring-2 focus:ring-primary/20 transition-all font-medium placeholder:text-on-surface-variant/60"
        placeholder="Chercher des missions..."
        type="text"
      />
      <!-- Bouton effacer -->
      <button
        v-if="localQuery"
        @click="clear"
        class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-on-surface transition-colors"
      >
        <span class="material-symbols-outlined text-sm">close</span>
      </button>
    </div>

    <!-- Filtre catégorie principale -->
    <select
      v-model="localCategory"
      @change="updateSearch"
      class="bg-surface-container-highest/50 border-none rounded-full py-2 px-3 text-xs focus:ring-2 focus:ring-primary/20 transition-all font-medium text-on-surface"
    >
      <option value="">Toutes catégories</option>
      <option value="design">Design</option>
      <option value="dev">Développement</option>
      <option value="marketing">Marketing</option>
      <option value="construction">Construction</option>
      <option value="other">Autres</option>
    </select>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useFreelancerStore } from '@/stores/freelancer'

const router = useRouter()
const route = useRoute()
const store = useFreelancerStore()

/** Valeurs locales avant validation (évite de polluer le store à chaque frappe) */
const localQuery    = ref(store.searchQuery)
const localCategory = ref(store.searchCategory)

/**
 * Met à jour la recherche en temps réel
 * Navigue/met à jour les query params et le store
 */
const updateSearch = () => {
  store.searchQuery    = localQuery.value
  store.searchCategory = localCategory.value
  
  router.push({
    path: '/freelancer/dashboard',
    query: { 
      tab: 'dashboard',
      search: localQuery.value, 
      category: localCategory.value 
    },
  })
}

/** Réinitialise la recherche */
const clear = () => {
  localQuery.value     = ''
  localCategory.value  = ''
  store.searchQuery    = ''
  store.searchCategory = ''
  router.push({
    path: '/freelancer/dashboard',
    query: { tab: 'dashboard' }
  })
}

// Watch store changes to sync local values
watch(() => store.searchQuery, (newVal) => {
  localQuery.value = newVal
})

watch(() => store.searchCategory, (newVal) => {
  localCategory.value = newVal
})
</script>
