<template>
  <div class="min-h-screen bg-surface font-body text-on-surface flex flex-col overflow-x-hidden">
    <!-- TopNavBar Réutilisable -->
    <TopNavBar />
    <div class="flex flex-1 overflow-x-hidden">
    <!-- Barre Latérale (SideNavBar) : Accès aux jetons et missions du Client -->
    <aside class="h-screen w-64 fixed left-0 top-0 bg-white border-r border-primary/5 flex flex-col gap-2 pt-24 pb-8 z-40 shadow-xl shadow-primary/5">
      <div class="px-8 mb-12">
        <h2 class="font-headline italic font-black text-primary text-2xl">MorLancer Pro</h2>
        <p class="text-[10px] text-on-surface-variant tracking-[0.2em] font-bold uppercase mt-1">Artisanat Digital</p>
      </div>
      <nav class="flex-grow flex flex-col gap-2">
        <!-- Lien actif : Achat de jetons (Monétisation) -->
        <a class="flex items-center gap-4 bg-primary text-white rounded-full mx-4 py-3 px-6 shadow-lg shadow-primary/20 scale-98 transition-all" href="#">
          <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">stars</span>
          <span class="text-sm font-bold">Pack de Jetons</span>
        </a>
        <a class="flex items-center gap-4 text-on-surface-variant mx-4 py-3 px-6 hover:bg-primary/10 rounded-full transition-all group" href="#">
          <span class="material-symbols-outlined group-hover:scale-110 transition-transform">list_alt</span>
          <span class="text-sm font-medium">Mes Missions</span>
        </a>
        <router-link to="/messaging" class="flex items-center gap-4 text-on-surface-variant mx-4 py-3 px-6 hover:bg-primary/10 rounded-full transition-all group">
          <span class="material-symbols-outlined group-hover:scale-110 transition-transform">chat_bubble</span>
          <span class="text-sm font-medium">Messages</span>
        </router-link>
        <a class="flex items-center gap-4 text-on-surface-variant mx-4 py-3 px-6 hover:bg-primary/10 rounded-full transition-all group" href="#">
          <span class="material-symbols-outlined group-hover:scale-110 transition-transform">receipt_long</span>
          <span class="text-sm font-medium">Historique</span>
        </a>
      </nav>
      <!-- Zone Solde : Affiche le nombre de jetons restants -->
      <div class="px-4 mt-auto">
        <div class="p-6 bg-surface-container-high/50 rounded-[2rem] border border-primary/5 flex flex-col gap-4 text-center">
          <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Solde Actuel</p>
          <div class="flex items-center justify-center gap-2">
             <span class="material-symbols-outlined text-primary font-bold">token</span>
             <span class="text-2xl font-black text-on-surface tracking-tighter">450</span>
          </div>
          <p class="text-[10px] font-medium text-on-surface-variant opacity-60">Consommation : 12/mois</p>
        </div>
      </div>
    </aside>

    <!-- Contenu Principal -->
    <main class="ml-64 p-12 min-h-screen zellige-pattern flex-1">
      <header class="flex justify-between items-start mb-16 px-4">
        <div>
          <h1 class="font-headline font-black text-5xl text-primary tracking-tight">Pack de Jetons</h1>
          <p class="text-on-surface-variant mt-2 text-lg font-medium italic">Alimentez vos projets avec l'excellence digitale.</p>
        </div>
        <div class="flex items-center gap-4">
           <!-- Badge promotionnel dynamique -->
           <div class="bg-white/80 backdrop-blur-md px-6 py-3 rounded-full flex items-center gap-3 border border-primary/5 shadow-sm">
             <span class="w-2.5 h-2.5 bg-secondary rounded-full animate-pulse shadow-[0_0_8px_rgba(183,31,39,0.4)]"></span>
             <span class="text-sm font-bold text-secondary italic">Offre de Printemps -20%</span>
           </div>
           <button @click="logout" class="bg-surface-container text-on-surface px-6 py-3 rounded-full font-bold text-xs uppercase tracking-widest hover:bg-secondary hover:text-white transition-all shadow-sm">Déconnexion</button>
        </div>
      </header>

      <!-- Grille de sélection des packs (Bento design) -->
      <section class="grid grid-cols-1 md:grid-cols-3 gap-8 px-4">
        <div v-for="pack in tokenPacks" :key="pack.name" 
             class="bg-white/80 backdrop-blur-md p-10 rounded-[3rem] border border-primary/5 flex flex-col items-center text-center relative overflow-hidden group shadow-2xl transition-all hover:translate-y-[-8px]">
          <div class="absolute inset-0 zellige-pattern opacity-5 group-hover:opacity-10 transition-opacity"></div>
          
          <div :class="pack.bgColor" class="w-24 h-24 rounded-full flex items-center justify-center mb-8 shadow-xl shadow-primary/5 group-hover:scale-110 transition-transform">
             <span class="material-symbols-outlined text-4xl text-white" style="font-variation-settings: 'FILL' 1;">{{ pack.icon }}</span>
          </div>

          <h3 class="font-headline text-3xl font-black text-on-surface mb-2 tracking-tight">{{ pack.name }}</h3>
          <p class="text-on-surface-variant font-medium text-sm mb-6">{{ pack.description }}</p>

          <!-- Affichage du prix premium -->
          <div class="flex items-baseline gap-1 mb-8">
             <span class="text-5xl font-black text-primary tracking-tighter">{{ pack.price }}</span>
             <span class="text-lg font-bold text-primary italic">MAD</span>
          </div>

          <!-- Liste des avantages inclus dans le pack -->
          <div class="bg-surface-container-high/50 w-full p-6 rounded-2xl mb-8 flex flex-col gap-3">
             <div v-for="feature in pack.features" :key="feature" class="flex items-center gap-2 text-xs font-bold text-on-surface text-left">
                <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                {{ feature }}
             </div>
          </div>

          <button class="w-full py-4 rounded-full bg-primary text-white font-black uppercase tracking-[0.2em] shadow-xl shadow-primary/20 hover:brightness-110 active:scale-95 transition-all">
            Sélectionner
          </button>

          <div class="absolute -top-10 -right-10 w-24 h-24 bg-primary/5 rounded-full blur-2xl group-hover:bg-primary/20 transition-all duration-700"></div>
        </div>
      </section>

      <!-- Indicateurs de confiance (Footer de Dashboard) -->
      <section class="mt-16 grid grid-cols-1 md:grid-cols-4 gap-8 px-4 opacity-70">
        <div class="flex flex-col items-center text-center p-6 bg-white/40 rounded-3xl backdrop-blur-sm border border-primary/5">
           <span class="material-symbols-outlined text-primary mb-3 text-3xl">lock</span>
           <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-on-surface">Paiement Sécurisé</h4>
        </div>
        <div class="flex flex-col items-center text-center p-6 bg-white/40 rounded-3xl backdrop-blur-sm border border-primary/5">
           <span class="material-symbols-outlined text-primary mb-3 text-3xl">history</span>
           <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-on-surface">Validité Illimitée</h4>
        </div>
        <div class="flex flex-col items-center text-center p-6 bg-white/40 rounded-3xl backdrop-blur-sm border border-primary/5">
           <span class="material-symbols-outlined text-primary mb-3 text-3xl">support_agent</span>
           <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-on-surface">Assistance 24/7</h4>
        </div>
        <div class="flex flex-col items-center text-center p-6 bg-white/40 rounded-3xl backdrop-blur-sm border border-primary/5">
           <span class="material-symbols-outlined text-primary mb-3 text-3xl">receipt</span>
           <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-on-surface">Facture instantanée</h4>
        </div>
      </section>
    </main>
    </div>
  </div>
</template>

<script setup>
import TopNavBar from '@/components/Common/TopNavBar.vue';
import { ref } from 'vue';

/**
 * Packs de jetons disponibles à l'achat.
 * Chaque pack a ses propres caractéristiques et prix.
 */
const tokenPacks = ref([
  { 
    name: 'Essentiel', 
    price: '190', 
    icon: 'token', 
    description: 'Parfait pour démarrer un petit projet artisanal.',
    bgColor: 'bg-primary/60',
    features: ['10 Jetons Inclus', 'Accès Standard aux Artisans', 'Support Email']
  },
  { 
    name: 'Artisan', 
    price: '450', 
    icon: 'stars', 
    description: 'La solution idéale pour vos chantiers réguliers.',
    bgColor: 'bg-primary',
    features: ['30 Jetons Inclus', 'Accès Prioritaire', 'Support 24/7', 'Badge Client Vérifié']
  },
  { 
    name: 'Luxe', 
    price: '990', 
    icon: 'diamond', 
    description: 'Pour l\'excellence et le prestige sans limites.',
    bgColor: 'bg-secondary',
    features: ['80 Jetons Inclus', 'Conciergerie Dédiée', 'Zéro Frais de Commission', 'Contrat sur-mesure']
  }
]);

/**
 * Fonction de déconnexion simple.
 * Supprime le jeton du localStorage et redirige vers le login.
 */
const logout = () => {
 localStorage.removeItem('token');
 window.location.href = '/login';
};
</script>

<style scoped>
/**
 * Motif Zellige spécifique au Dashboard Client (Variante d'étoiles).
 */
.zellige-pattern {
  background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M30 0l5 25h25l-20 15 8 20-18-12-18 12 8-20L0 25h25z' fill='%23006233' fill-opacity='0.03' fill-rule='evenodd'/%3E%3C/svg%3E");
}
</style>
