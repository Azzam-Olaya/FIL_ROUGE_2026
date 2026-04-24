<template>
  <div class="space-y-6">

    <!-- Barre de recherche + filtres -->
    <div class="bg-white/80 backdrop-blur-md rounded-[2rem] shadow-sm border border-primary/10 p-6 flex flex-wrap gap-4 items-end">

      <!-- Recherche texte -->
      <div class="flex-1 min-w-[220px]">
        <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Rechercher</label>
        <div class="flex items-center border border-gray-200 rounded-xl px-4 py-2 focus-within:border-primary transition-colors gap-2">
          <span class="material-symbols-outlined text-gray-400 text-sm">search</span>
          <input
            v-model="filters.search"
            @input="debouncedSearch"
            placeholder="Titre, description..."
            class="bg-transparent border-none focus:ring-0 w-full text-sm p-0"
          />
          <button v-if="filters.search" @click="filters.search = ''; load()" class="text-gray-300 hover:text-gray-500">
            <span class="material-symbols-outlined text-sm">close</span>
          </button>
        </div>
      </div>

      <!-- Filtre catégorie principale -->
      <div class="min-w-[180px]">
        <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Catégorie</label>
        <select
          v-model="filters.category_id"
          @change="onCategoryChange"
          class="w-full border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-primary"
        >
          <option :value="null">Toutes les catégories</option>
          <option v-for="cat in rootCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
        </select>
      </div>

      <!-- Filtre sous-catégorie (affiché uniquement si une catégorie est sélectionnée) -->
      <div class="min-w-[180px]" v-if="subCategories.length">
        <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Sous-catégorie</label>
        <select
          v-model="filters.sub_category_id"
          @change="load"
          class="w-full border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-primary"
        >
          <option :value="null">Toutes</option>
          <option v-for="sub in subCategories" :key="sub.id" :value="sub.id">{{ sub.name }}</option>
        </select>
      </div>

      <!-- Budget max -->
      <div class="min-w-[160px]">
        <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Budget max (MAD)</label>
        <input
          v-model="filters.budget_max"
          @input="debouncedSearch"
          type="number" min="0" placeholder="Ex: 10000"
          class="w-full border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-primary"
        />
      </div>

      <!-- Réinitialiser -->
      <button
        @click="resetFilters"
        class="px-5 py-2 rounded-xl border border-gray-200 text-sm text-gray-500 hover:bg-gray-50 transition-colors flex items-center gap-1"
      >
        <span class="material-symbols-outlined text-sm">filter_alt_off</span>
        Réinitialiser
      </button>
    </div>

    <!-- Compteur résultats + indicateur chargement -->
    <div class="flex items-center justify-between px-1">
      <p class="text-sm text-gray-500 font-medium">
        <span class="font-bold text-primary">{{ missions.length }}</span> mission(s) trouvée(s)
      </p>
      <div v-if="loading" class="flex items-center gap-2 text-sm text-gray-400">
        <span class="material-symbols-outlined text-sm animate-spin">progress_activity</span>
        Chargement...
      </div>
    </div>

    <!-- Aucun résultat -->
    <div v-if="!loading && missions.length === 0" class="text-center py-20 bg-white/80 rounded-[2rem] border border-primary/5">
      <span class="material-symbols-outlined text-5xl text-gray-300 block mb-3">search_off</span>
      <p class="text-gray-400 font-medium">Aucune mission ne correspond à votre recherche.</p>
      <button @click="resetFilters" class="mt-4 text-primary text-sm font-bold hover:underline">Effacer les filtres</button>
    </div>

    <!-- Liste des missions -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
      <div
        v-for="mission in missions" :key="mission.id"
        class="bg-white/80 backdrop-blur-md rounded-[2rem] border border-primary/5 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all p-6 flex flex-col gap-4"
      >
        <!-- Header mission -->
        <div class="flex items-start justify-between gap-3">
          <div class="flex-1">
            <span class="text-[10px] font-bold uppercase tracking-widest text-secondary bg-secondary/10 px-3 py-1 rounded-full">
              {{ mission.category?.name || 'Non catégorisé' }}
            </span>
            <h3 class="font-bold text-lg text-gray-800 mt-2 leading-tight">{{ mission.title }}</h3>
            <p class="text-sm text-gray-400 mt-1">
              <span class="material-symbols-outlined text-xs align-middle">person</span>
              {{ mission.client?.name || '—' }}
            </p>
          </div>
          <div class="text-right flex-shrink-0">
            <p class="text-[10px] text-gray-400 uppercase tracking-widest">Budget</p>
            <p class="font-black text-2xl text-primary">{{ Number(mission.budget).toLocaleString('fr-FR') }}</p>
            <p class="text-[10px] text-primary font-bold">MAD</p>
          </div>
        </div>

        <!-- Description -->
        <p class="text-sm text-gray-500 leading-relaxed line-clamp-2">{{ mission.description }}</p>

        <!-- Footer -->
        <div class="flex items-center justify-between pt-2 border-t border-gray-50">
          <div class="flex items-center gap-1 text-xs text-gray-400">
            <span class="material-symbols-outlined text-sm">calendar_today</span>
            Deadline : {{ formatDate(mission.deadline) }}
          </div>
          <button class="bg-primary text-white px-5 py-2 rounded-full text-xs font-bold hover:brightness-110 active:scale-95 transition-all flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">send</span>
            Postuler
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const missions   = ref([])
const categories = ref([])
const loading    = ref(false)
let debounceTimer = null

const filters = ref({ search: '', category_id: null, sub_category_id: null, budget_max: '' })

const token   = () => localStorage.getItem('token')
const headers = computed(() => ({ Authorization: `Bearer ${token()}` }))

/** Catégories racines (sans parent) */
const rootCategories = computed(() => categories.value.filter(c => !c.parent_id))

/** Sous-catégories de la catégorie sélectionnée */
const subCategories = computed(() => {
  if (!filters.value.category_id) return []
  return categories.value.filter(c => c.parent_id === filters.value.category_id)
})

/** Réinitialise la sous-catégorie quand la catégorie change */
const onCategoryChange = () => {
  filters.value.sub_category_id = null
  load()
}

/** Charge les missions filtrées depuis l'API */
const load = async () => {
  loading.value = true
  try {
    const params = {}
    if (filters.value.search)           params.search      = filters.value.search
    if (filters.value.sub_category_id)  params.category_id = filters.value.sub_category_id
    else if (filters.value.category_id) params.category_id = filters.value.category_id
    if (filters.value.budget_max)       params.budget_max  = filters.value.budget_max

    const res = await axios.get('http://localhost:8000/api/freelancer/missions', { headers: headers.value, params })
    missions.value = res.data
  } finally {
    loading.value = false
  }
}

/** Charge les catégories depuis l'API */
const loadCategories = async () => {
  try {
    const res = await axios.get('http://localhost:8000/api/freelancer/categories', { headers: headers.value })
    categories.value = res.data
  } catch { /* silencieux */ }
}

/** Recherche avec délai pour éviter trop d'appels API */
const debouncedSearch = () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(load, 400)
}

/** Réinitialise tous les filtres */
const resetFilters = () => {
  filters.value = { search: '', category_id: null, sub_category_id: null, budget_max: '' }
  load()
}

const formatDate = (d) =>
  d ? new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' }) : '—'

onMounted(async () => {
  await loadCategories()
  await load()
})
</script>
