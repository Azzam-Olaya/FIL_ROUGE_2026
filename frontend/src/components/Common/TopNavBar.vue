<template>
  <header class="bg-surface/80 backdrop-blur-md flex justify-between items-center w-full px-8 py-4 sticky top-0 z-50 shadow-[0px_20px_40px_rgba(30,28,13,0.06)] border-b border-primary/5">
    <!-- Côté gauche: Logo et recherche -->
    <div class="flex items-center gap-8">
      <router-link to="/" class="flex items-center gap-2 group">
        <span class="material-symbols-outlined text-primary text-3xl font-bold group-hover:scale-110 transition-transform">auto_awesome</span>
        <span class="font-headline font-black italic text-2xl text-primary tracking-tight">MorLancer</span>
      </router-link>
      
      <!-- Barre de recherche (visible en lg et +) -->
      <div class="relative hidden lg:block">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
        <input 
          v-model="searchQuery"
          @keyup.enter="handleSearch"
          class="bg-surface-container-highest/50 border-none rounded-full py-2 pl-10 pr-4 text-xs w-64 focus:ring-2 focus:ring-primary/20 transition-all font-medium placeholder:text-on-surface-variant/60" 
          placeholder="Rechercher une mission..." 
          type="text"
        />
      </div>
    </div>

    <!-- Côté droit: Icônes et profil -->
    <div class="flex items-center gap-6">
      <div class="hidden md:flex gap-4">
        <!-- Favoris -->
        <button 
          @click="handleFavorites"
          class="p-2 rounded-full hover:bg-primary/10 transition-colors group"
          title="Favoris"
        >
          <span class="material-symbols-outlined text-on-surface-variant group-hover:text-primary">favorite</span>
        </button>

        <!-- Notifications -->
        <button 
          @click="handleNotifications"
          class="p-2 rounded-full hover:bg-primary/10 transition-colors relative group"
          title="Notifications"
        >
          <span class="material-symbols-outlined text-on-surface-variant group-hover:text-primary">notifications</span>
          <span v-if="notificationCount > 0" class="absolute top-2 right-2 w-2 h-2 bg-secondary rounded-full animate-pulse"></span>
        </button>
      </div>

      <!-- Profil -->
      <div 
        @click="handleProfile"
        class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-bold text-xs shadow-lg shadow-primary/20 cursor-pointer hover:scale-110 transition-transform"
        :title="`${userName}`"
      >
        {{ userInitials }}
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const searchQuery = ref('');
const notificationCount = ref(0);

// Récupérer les données de l'utilisateur
const user = JSON.parse(localStorage.getItem('user') || '{}');
const userName = computed(() => user.name || 'Utilisateur');
const userInitials = computed(() => {
  return user.name
    ?.split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2) || 'U';
});

// Gestionnaires d'événements
const handleSearch = () => {
  if (searchQuery.value.trim()) {
    // Rediriger vers la page de recherche ou émettre un événement
    console.log('Recherche:', searchQuery.value);
    // router.push(`/search?q=${searchQuery.value}`);
  }
};

const handleFavorites = () => {
  router.push('/favorites');
};

const handleNotifications = () => {
  console.log('Ouvrir notifications');
};

const handleProfile = () => {
  router.push('/profile');
};
</script>

<style scoped>
/* Animations si nécessaire */
</style>
