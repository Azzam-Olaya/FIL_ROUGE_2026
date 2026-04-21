<template>
  <div class="min-h-screen bg-surface font-body text-on-surface flex overflow-x-hidden">
    <!-- Barre Latérale (SideNavBar) : Navigation pour l'Administrateur -->
    <aside class="h-screen w-64 fixed left-0 top-0 bg-white border-r border-primary/5 flex flex-col gap-2 pt-24 pb-8 z-40 shadow-xl shadow-primary/5">
      <div class="px-8 mb-10">
        <h1 class="text-2xl font-black text-primary font-headline italic">MorLancer Pro</h1>
        <p class="text-[10px] opacity-60 tracking-[0.2em] uppercase mt-1 font-bold">Artisanat Digital</p>
      </div>
      <nav class="flex flex-col gap-2">
        <a class="flex items-center gap-4 bg-primary text-white rounded-full mx-4 py-3 px-6 font-bold shadow-lg shadow-primary/20 transition-all scale-98 hover:scale-100" href="#">
          <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">dashboard</span>
          <span class="text-sm">Tableau de bord</span>
        </a>
        <a class="flex items-center gap-4 text-on-surface-variant mx-4 py-3 px-6 hover:bg-primary/10 rounded-full transition-all group" href="#">
          <span class="material-symbols-outlined group-hover:scale-110 transition-transform">work</span>
          <span class="text-sm font-medium">Missions</span>
        </a>
        <a class="flex items-center gap-4 text-on-surface-variant mx-4 py-3 px-6 hover:bg-primary/10 rounded-full transition-all group" href="#">
          <span class="material-symbols-outlined group-hover:scale-110 transition-transform">payments</span>
          <span class="text-sm font-medium">Paiements</span>
        </a>
        <a class="flex items-center gap-4 text-on-surface-variant mx-4 py-3 px-6 hover:bg-primary/10 rounded-full transition-all group" href="#">
          <span class="material-symbols-outlined group-hover:scale-110 transition-transform">chat_bubble</span>
          <span class="text-sm font-medium">Messages</span>
        </a>
      </nav>
      <!-- Zone Statut Admin -->
      <div class="mt-auto px-4">
        <div class="bg-primary-container text-white p-6 rounded-3xl relative overflow-hidden shadow-xl">
          <div class="relative z-10">
            <p class="text-[10px] font-bold uppercase tracking-widest mb-2 opacity-80">Statut Admin</p>
            <p class="font-headline text-lg font-bold leading-tight italic">Accès Premium Illimité</p>
          </div>
          <div class="absolute -right-4 -bottom-4 opacity-10">
            <span class="material-symbols-outlined text-8xl" style="font-variation-settings: 'FILL' 1;">verified_user</span>
          </div>
        </div>
      </div>
    </aside>

    <!-- Contenu Principal (Dashboard Admin) -->
    <main class="ml-64 p-12 min-h-screen zellige-pattern flex-1">
      <header class="flex justify-between items-end mb-16 px-4">
        <div>
          <h2 class="font-headline text-5xl font-black text-primary tracking-tight">Vue d'ensemble</h2>
          <p class="text-on-surface-variant mt-2 text-lg font-medium italic">Suivi de l'écosystème artisanal digital</p>
        </div>
        <div class="flex gap-4 items-center">
          <div class="bg-white/80 backdrop-blur-md px-6 py-3 rounded-full flex items-center gap-3 border border-primary/5 shadow-sm">
            <span class="w-2.5 h-2.5 bg-primary rounded-full animate-pulse shadow-[0_0_8px_rgba(0,72,36,0.4)]"></span>
            <span class="text-sm font-bold text-primary italic">124 Artisans en ligne</span>
          </div>
          <button @click="logout" class="bg-secondary text-white px-8 py-3 rounded-full font-bold flex items-center gap-2 hover:brightness-110 active:scale-95 transition-all shadow-lg shadow-secondary/20">
            <span class="material-symbols-outlined text-sm">logout</span>
            Déconnexion
          </button>
        </div>
      </header>

      <!-- Grille de Statistiques (Données Globales) -->
      <section class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-16 px-4">
        <div v-for="stat in adminStats" :key="stat.label" class="bg-white p-8 rounded-2xl shadow-xl shadow-primary/5 relative overflow-hidden group border border-primary/5 transition-all hover:translate-y-[-4px]">
          <div class="relative z-10">
            <p class="text-on-surface-variant text-xs font-bold uppercase tracking-widest mb-2 opacity-60">{{ stat.label }}</p>
            <h3 class="text-4xl font-headline font-bold text-on-surface tracking-tighter">{{ stat.value }}</h3>
            <div class="flex items-center gap-1 mt-4 text-sm font-bold" :class="stat.trendColor || 'text-primary'">
              <span class="material-symbols-outlined text-sm">{{ stat.trendIcon || 'trending_up' }}</span>
              <span>{{ stat.trend }}</span>
            </div>
          </div>
          <div class="absolute top-0 right-0 p-4 opacity-[0.03] group-hover:opacity-[0.08] transition-opacity">
            <span class="material-symbols-outlined text-7xl">{{ stat.icon }}</span>
          </div>
        </div>
      </section>

      <!-- Graphiques et Modération -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 px-4">
        <!-- Section Graphique (Evolution) -->
        <section class="lg:col-span-2 bg-white/80 backdrop-blur-md p-10 rounded-3xl border border-primary/5 shadow-xl shadow-primary/5">
          <div class="flex justify-between items-start mb-10">
            <div>
              <h4 class="font-headline text-2xl font-bold text-on-surface">Performance de Croissance</h4>
              <p class="text-on-surface-variant text-sm font-medium">Revenus vs Inscriptions (30 derniers jours)</p>
            </div>
          </div>
          <!-- Barres de graphique simulées en CSS/Tailwind -->
          <div class="h-64 flex items-end justify-between gap-3 px-2 border-b border-primary/5 pb-2">
             <div v-for="h in [40, 60, 55, 80, 65, 90, 75, 45, 85, 95]" :key="h" 
                  :style="{ height: h + '%' }" 
                  class="w-full bg-primary/20 rounded-t-xl relative group hover:bg-primary transition-all cursor-crosshair">
                <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-primary text-white text-[10px] font-bold px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap shadow-xl">
                  {{ h }}k MAD
                </div>
             </div>
          </div>
          <div class="flex justify-between mt-6 text-[10px] text-on-surface-variant font-black tracking-widest px-2">
            <span>SEMAINE 1</span> <span>SEMAINE 2</span> <span>SEMAINE 3</span> <span>SEMAINE 4</span>
          </div>
        </section>

        <!-- Section Modération (Validation des utilisateurs) -->
        <section class="bg-primary/5 backdrop-blur-md p-10 rounded-3xl border border-primary/10">
          <h4 class="font-headline text-2xl font-bold mb-8">Modération Rapide</h4>
          <div class="space-y-4">
            <div v-for="user in pendingUsers" :key="user.id" 
                 class="flex gap-4 items-center p-4 bg-white rounded-2xl border-l-[6px] shadow-sm group hover:shadow-md transition-all"
                 :class="user.role?.name === 'freelancer' ? 'border-primary' : 'border-secondary'">
              <div class="w-12 h-12 rounded-full bg-surface-container flex items-center justify-center font-bold text-lg text-primary overflow-hidden">
                {{ user.name[0] }}
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-bold truncate">{{ user.name }}</p>
                <p class="text-[11px] font-bold uppercase tracking-widest opacity-60" :class="user.role?.name === 'freelancer' ? 'text-primary' : 'text-secondary'">
                   {{ user.role?.name }}
                </p>
              </div>
              <!-- Actions rapides de validation / bannissement -->
              <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                 <button @click="approveUser(user.id)" class="text-primary hover:scale-110 active:scale-95"><span class="material-symbols-outlined">check_circle</span></button>
                 <button @click="banUser(user.id)" class="text-secondary hover:scale-110 active:scale-95"><span class="material-symbols-outlined">cancel</span></button>
              </div>
            </div>
            <!-- Message si aucune tâche en attente -->
            <div v-if="pendingUsers.length === 0" class="text-center py-10 opacity-40 font-bold italic text-sm">
              Rien à modérer ! ✨
            </div>
          </div>
          <button class="w-full mt-8 py-4 border border-primary/10 rounded-full text-xs font-black uppercase tracking-widest text-primary hover:bg-primary hover:text-white transition-all shadow-sm">
            Tous les signalements
          </button>
        </section>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue';

/**
 * Statistiques d'administration.
 * Ces données devraient idéalement provenir de l'API Laravel.
 */
const adminStats = ref([
 { label: 'Utilisateurs Totaux', value: '12,840', icon: 'groups', trend: '+12% ce mois', trendColor: 'text-primary', trendIcon: 'trending_up' },
 { label: 'Revenus Platforme', value: '45,200 DH', icon: 'payments', trend: 'Volume: 452K DH', trendColor: 'text-primary', trendIcon: 'account_balance_wallet' },
 { label: 'Missions Actives', value: '312', icon: 'sync', trend: 'En cours d\'exécution', trendColor: 'text-on-surface-variant', trendIcon: 'hourglass_empty' },
 { label: 'Signalements', value: '14', icon: 'report', trend: 'Action urgente', trendColor: 'text-secondary font-black', trendIcon: 'warning', iconColor: 'text-secondary' }
]);

/**
 * Utilisateurs en attente de vérification d'identité.
 */
const pendingUsers = ref([
 { id: 1, name: 'Ahmed El Alami', email: 'ahmed@artisan.ma', role: { name: 'freelancer' } },
 { id: 2, name: 'Sara Bennani', email: 'sara@hotel-atlas.com', role: { name: 'client' } }
]);

/**
 * Logique de validation (Simulée côté Front)
 */
const approveUser = (id) => {
 pendingUsers.value = pendingUsers.value.filter(u => u.id !== id);
};

const banUser = (id) => {
 pendingUsers.value = pendingUsers.value.filter(u => u.id !== id);
};

const logout = () => {
 localStorage.removeItem('token');
 window.location.href = '/login';
};
</script>

<style scoped>
/**
 * Motif Zellige (Fleurs d'étoiles) pour le Dashboard Admin.
 */
.zellige-pattern {
  background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M30 0l5 25h25l-20 15 8 20-18-12-18 12 8-20L0 25h25z' fill='%23006233' fill-opacity='0.03' fill-rule='evenodd'/%3E%3C/svg%3E");
}
</style>
