<template>
  <div class="hidden md:flex items-center gap-3 flex-1 max-w-lg mx-8">
    <div class="relative flex-1">
      <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
      <input v-model="localQuery" @input="updateSearch" @keyup.enter="updateSearch"
        class="w-full bg-surface-container-highest/50 border-none rounded-full py-2 pl-10 pr-4 text-xs focus:ring-2 focus:ring-primary/20 transition-all font-medium placeholder:text-on-surface-variant/60"
        placeholder="Chercher des missions..." type="text" />
      <button v-if="localQuery" @click="clear" class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-on-surface transition-colors">
        <span class="material-symbols-outlined text-sm">close</span>
      </button>
    </div>
    <select v-model="localCategory" @change="updateSearch"
      class="bg-surface-container-highest/50 border-none rounded-full py-2 px-3 text-xs focus:ring-2 focus:ring-primary/20 transition-all font-medium text-on-surface max-w-[150px]">
      <option value="">Toutes catégories</option>
      <option v-for="c in rootCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
    </select>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, computed } from 'vue'
import { useFreelancerStore } from '@/stores/freelancer'
import api from '@/api/axios'

const store         = useFreelancerStore()
const localQuery    = ref(store.searchQuery)
const localCategory = ref(store.searchCategory)
const categories    = ref([])

const rootCategories = computed(() => categories.value.filter(c => !c.parent_id))

const loadCategories = async () => {
  try {
    const res = await api.get('/freelancer/categories')
    categories.value = res.data
  } catch { categories.value = [] }
}

const updateSearch = () => {
  store.searchQuery    = localQuery.value
  store.searchCategory = localCategory.value
}

const clear = () => {
  localQuery.value     = ''
  localCategory.value  = ''
  store.searchQuery    = ''
  store.searchCategory = ''
}

onMounted(loadCategories)

watch(() => store.searchQuery,    v => { localQuery.value    = v })
watch(() => store.searchCategory, v => { localCategory.value = v })
</script>
