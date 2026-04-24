<template>
  <div class="space-y-6">

    <!-- Filtres -->
    <div class="bg-white rounded-[2rem] shadow-sm border border-primary/10 p-6 flex flex-wrap gap-4 items-end">
      <div class="flex-1 min-w-[220px]">
        <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Rechercher</label>
        <div class="flex items-center border border-gray-200 rounded-xl px-4 py-2 focus-within:border-primary gap-2">
          <span class="material-symbols-outlined text-gray-400 text-sm">search</span>
          <input v-model="filters.search" @input="debouncedSearch" placeholder="Titre, description..."
            class="bg-transparent border-none focus:ring-0 w-full text-sm p-0" />
          <button v-if="filters.search" @click="filters.search = ''; load()">
            <span class="material-symbols-outlined text-sm text-gray-300">close</span>
          </button>
        </div>
      </div>

      <div class="min-w-[180px]">
        <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Catégorie</label>
        <select v-model="filters.category_id" @change="onCategoryChange"
          class="w-full border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-primary">
          <option :value="null">Toutes les catégories</option>
          <option v-for="cat in rootCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
        </select>
      </div>

      <div class="min-w-[180px]" v-if="subCategories.length">
        <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Sous-catégorie</label>
        <select v-model="filters.sub_category_id" @change="load"
          class="w-full border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-primary">
          <option :value="null">Toutes</option>
          <option v-for="sub in subCategories" :key="sub.id" :value="sub.id">{{ sub.name }}</option>
        </select>
      </div>

      <button @click="resetFilters"
        class="px-5 py-2 rounded-xl border border-gray-200 text-sm text-gray-500 hover:bg-gray-50 flex items-center gap-1">
        <span class="material-symbols-outlined text-sm">filter_alt_off</span>
        Réinitialiser
      </button>
    </div>

    <!-- Compteur -->
    <div class="flex items-center justify-between px-1">
      <p class="text-sm text-gray-500 font-medium">
        <span class="font-bold text-primary">{{ briefs.length }}</span> brief(s) trouvé(s)
      </p>
      <div v-if="loading" class="flex items-center gap-2 text-sm text-gray-400">
        <span class="material-symbols-outlined text-sm animate-spin">progress_activity</span>
        Chargement...
      </div>
    </div>

    <!-- Vide -->
    <div v-if="!loading && briefs.length === 0" class="text-center py-20 bg-white rounded-[2rem] border border-primary/5">
      <span class="material-symbols-outlined text-5xl text-gray-300 block mb-3">search_off</span>
      <p class="text-gray-400 font-medium">Aucun brief ne correspond à votre recherche.</p>
      <button @click="resetFilters" class="mt-4 text-primary text-sm font-bold hover:underline">Effacer les filtres</button>
    </div>

    <!-- Liste -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
      <div v-for="brief in briefs" :key="brief.id"
        class="bg-white rounded-[2rem] border border-primary/5 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all p-6 flex flex-col gap-4">
        <div class="flex items-start gap-3">
          <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
            {{ getInitials(brief.freelancer?.name) }}
          </div>
          <div class="flex-1">
            <span class="text-[10px] font-bold uppercase tracking-widest text-secondary bg-secondary/10 px-3 py-1 rounded-full">
              {{ brief.category?.name || 'Non catégorisé' }}
            </span>
            <h3 class="font-bold text-lg text-gray-800 mt-1">{{ brief.title }}</h3>
            <p class="text-sm text-gray-400">{{ brief.freelancer?.name || '—' }}</p>
          </div>
        </div>
        <p class="text-sm text-gray-500 leading-relaxed line-clamp-2">{{ brief.description }}</p>
        <div class="flex items-center justify-between pt-2 border-t border-gray-50">
          <div class="flex gap-3 text-xs text-gray-400">
            <span>{{ brief.likes_count || 0 }} likes</span>
            <span>•</span>
            <span>{{ brief.comments_count || 0 }} commentaires</span>
          </div>
          <button class="bg-primary text-white px-5 py-2 rounded-full text-xs font-bold hover:brightness-110 active:scale-95 transition-all flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">chat_bubble</span>
            Contacter
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const briefs     = ref([])
const categories = ref([])
const loading    = ref(false)
let debounceTimer = null

const filters  = ref({ search: '', category_id: null, sub_category_id: null })
const headers  = computed(() => ({ Authorization: `Bearer ${localStorage.getItem('token')}` }))

const rootCategories = computed(() => categories.value.filter(c => !c.parent_id))
const subCategories  = computed(() => {
  if (!filters.value.category_id) return []
  return categories.value.filter(c => c.parent_id === filters.value.category_id)
})

const onCategoryChange = () => { filters.value.sub_category_id = null; load() }

const load = async () => {
  loading.value = true
  try {
    const params = {}
    if (filters.value.search)           params.search = filters.value.search
    if (filters.value.sub_category_id)  params.category_id = filters.value.sub_category_id
    else if (filters.value.category_id) params.category_id = filters.value.category_id
    const res = await axios.get('http://localhost:8000/api/freelancer/briefs', { headers: headers.value, params })
    briefs.value = res.data
  } catch {
    briefs.value = [
      { id: 1, title: 'Expert Zellige', description: 'Design et création de motifs Zellige.', category: { name: 'Design' }, freelancer: { name: 'Yasmine B.' }, likes_count: 42, comments_count: 3 },
      { id: 2, title: 'Développeur Full-Stack', description: 'Laravel + Vue.js, disponible immédiatement.', category: { name: 'Développement' }, freelancer: { name: 'Omar K.' }, likes_count: 65, comments_count: 5 },
    ]
  } finally {
    loading.value = false
  }
}

const loadCategories = async () => {
  try {
    const res = await axios.get('http://localhost:8000/api/freelancer/categories', { headers: headers.value })
    categories.value = res.data
  } catch { categories.value = [] }
}

const debouncedSearch = () => { clearTimeout(debounceTimer); debounceTimer = setTimeout(load, 400) }
const resetFilters    = () => { filters.value = { search: '', category_id: null, sub_category_id: null }; load() }
const getInitials     = (name) => name?.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) || 'U'

onMounted(async () => { await loadCategories(); await load() })
</script>
