<template>
  <div class="relative" ref="containerRef">
    <button @click="open = !open" class="p-2 rounded-full hover:bg-primary/10 transition-colors relative group" title="Missions favorites">
      <span class="material-symbols-outlined group-hover:text-primary transition-colors"
        :class="store.favoritedMissions.length ? 'text-primary' : 'text-on-surface-variant'"
        :style="store.favoritedMissions.length ? `font-variation-settings: 'FILL' 1` : ''">favorite</span>
      <span v-if="store.favoritedMissions.length > 0"
        class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] bg-secondary text-white text-[10px] font-black rounded-full flex items-center justify-center px-1">
        {{ store.favoritedMissions.length > 9 ? '9+' : store.favoritedMissions.length }}
      </span>
    </button>

    <Transition name="dropdown">
      <div v-if="open" class="absolute top-12 right-0 w-80 bg-white rounded-2xl shadow-2xl border border-primary/5 z-50 overflow-hidden">
        <div class="px-4 py-3 border-b border-primary/5">
          <p class="font-bold text-sm text-on-surface">
            Missions favorites <span class="text-on-surface-variant font-normal">({{ store.favoritedMissions.length }})</span>
          </p>
        </div>
        <div class="max-h-80 overflow-y-auto divide-y divide-primary/5">
          <div v-for="m in store.favoritedMissions" :key="m.id"
            class="flex gap-3 px-4 py-3 hover:bg-primary/5 transition-colors group/item">
            <div class="w-9 h-9 rounded-full bg-primary flex items-center justify-center text-white font-bold text-[10px] flex-shrink-0">
              {{ initials(m.clientName || m.client?.name) }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-semibold text-xs text-on-surface truncate">{{ m.title }}</p>
              <p class="text-[11px] text-on-surface-variant mt-0.5">{{ m.clientName || m.client?.name || '—' }}</p>
              <div class="flex gap-2 mt-1">
                <span class="text-[10px] font-bold text-primary bg-primary/10 px-2 py-0.5 rounded-full">{{ m.budget || m.price }} DH</span>
                <span v-if="m.category?.name || m.categories?.[0]" class="text-[10px] text-on-surface-variant bg-surface-container px-2 py-0.5 rounded-full">
                  {{ m.category?.name || m.categories?.[0] }}
                </span>
              </div>
            </div>
            <button @click.stop="store.toggleFavorite(m)"
              class="flex-shrink-0 p-1.5 rounded-full hover:bg-secondary/10 transition-colors opacity-0 group-hover/item:opacity-100" title="Retirer">
              <span class="material-symbols-outlined text-secondary text-sm" style="font-variation-settings: 'FILL' 1">favorite</span>
            </button>
          </div>
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
import { ref, onMounted, onUnmounted } from 'vue'
import { useFreelancerStore } from '@/stores/freelancer'

const store        = useFreelancerStore()
const open         = ref(false)
const containerRef = ref(null)

const initials = (name) => name?.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) || 'U'
const handleOutside = (e) => { if (containerRef.value && !containerRef.value.contains(e.target)) open.value = false }
onMounted(() => document.addEventListener('click', handleOutside))
onUnmounted(() => document.removeEventListener('click', handleOutside))
</script>

<style scoped>
.dropdown-enter-active, .dropdown-leave-active { transition: opacity 0.15s ease, transform 0.15s ease; }
.dropdown-enter-from, .dropdown-leave-to { opacity: 0; transform: translateY(-6px); }
</style>
