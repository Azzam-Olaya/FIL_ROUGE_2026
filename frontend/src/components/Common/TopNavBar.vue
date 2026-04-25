<template>
  <header class="bg-surface-container border-b border-primary/10 flex justify-between items-center w-full px-4 md:px-8 py-4 sticky top-0 z-50">
    <div class="flex items-center gap-4">
      <!-- Hamburger Menu for Mobile -->
      <button @click="$emit('toggle-sidebar')" class="p-2 -ml-2 text-on-surface-variant lg:hidden hover:bg-primary/10 rounded-full transition-colors">
        <span class="material-symbols-outlined">menu</span>
      </button>

      <router-link to="/" class="flex items-center gap-2 group flex-shrink-0">
        <span class="material-symbols-outlined text-primary text-3xl font-bold group-hover:scale-110 transition-transform">auto_awesome</span>
        <div class="flex flex-col">
          <span class="font-headline font-black italic text-xl md:text-2xl text-primary tracking-tight leading-none">MorLancer</span>
          <span class="hidden sm:block text-[8px] md:text-[10px] text-on-surface-variant font-bold uppercase tracking-[0.15em] leading-none mt-0.5">Talents digitaux</span>
        </div>
      </router-link>
    </div>

    <div class="hidden md:block flex-1 max-w-md mx-8">
      <NavSearch />
    </div>

    <div class="flex items-center gap-1 md:gap-2">
      <div class="md:hidden">
        <!-- Search icon only on mobile if hidden from center -->
        <button class="p-2 text-on-surface-variant hover:bg-primary/10 rounded-full transition-colors">
          <span class="material-symbols-outlined">search</span>
        </button>
      </div>
      <NavNotifications />
      <NavFavorites />
      <NavProfile />
    </div>
  </header>
</template>

<script setup>
import NavSearch        from '@/components/Navbar/NavSearch.vue'
import NavNotifications from '@/components/Navbar/NavNotifications.vue'
import NavFavorites     from '@/components/Navbar/NavFavorites.vue'
import NavProfile       from '@/components/Navbar/NavProfile.vue'

defineEmits(['toggle-sidebar'])
</script>
