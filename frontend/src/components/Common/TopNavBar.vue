<template>
  <header class="bg-surface-container border-b border-primary/10 flex justify-between items-center w-full px-8 py-4 sticky top-0 z-50">
    <router-link to="/" class="flex items-center gap-2 group flex-shrink-0">
      <span class="material-symbols-outlined text-primary text-3xl font-bold group-hover:scale-110 transition-transform">auto_awesome</span>
      <div class="flex flex-col">
        <span class="font-headline font-black italic text-2xl text-primary tracking-tight leading-none">MorLancer</span>
        <span class="text-[10px] text-on-surface-variant font-bold uppercase tracking-[0.15em] leading-none mt-0.5">Talents digitaux</span>
      </div>
    </router-link>

    <NavSearch />

    <div class="flex items-center gap-1">
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
</script>
