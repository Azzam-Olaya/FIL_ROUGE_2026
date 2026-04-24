<template>
  <div class="hidden lg:flex items-center gap-3 flex-1 max-w-lg mx-8">
    <div class="relative flex-1">
      <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
      <input v-model="q" @input="update" @keyup.enter="update" placeholder="Chercher des missions..."
        class="w-full bg-surface-container-highest/50 border-none rounded-full py-2 pl-10 pr-4 text-xs focus:ring-2 focus:ring-primary/20 transition-all font-medium placeholder:text-on-surface-variant/60" />
      <button v-if="q" @click="clear" class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-on-surface transition-colors">
        <span class="material-symbols-outlined text-sm">close</span>
      </button>
    </div>
    <select v-model="cat" @change="update"
      class="bg-surface-container-highest/50 border-none rounded-full py-2 px-3 text-xs focus:ring-2 focus:ring-primary/20 transition-all font-medium text-on-surface">
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
import { useFreelancerStore } from '@/stores/freelancer'
const store = useFreelancerStore()
const q = ref(store.searchQuery), cat = ref(store.searchCategory)
const update = () => { store.searchQuery = q.value; store.searchCategory = cat.value }
const clear = () => { q.value = ''; cat.value = ''; store.searchQuery = ''; store.searchCategory = '' }
watch(() => store.searchQuery, v => { q.value = v })
watch(() => store.searchCategory, v => { cat.value = v })
</script>
