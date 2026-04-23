<template>
  <div class="min-h-screen bg-surface font-body text-on-surface flex flex-col overflow-x-hidden">
    <!-- TopNavBar Réutilisable -->
    <TopNavBar />
    <div class="flex flex-1 overflow-x-hidden">
    <!-- Barre Latérale (SideNavBar) : Navigation principale pour le Freelance -->
    <aside class="h-screen w-64 fixed left-0 top-0 bg-white border-r border-primary/5 flex flex-col gap-2 pt-24 pb-8 z-40 shadow-xl shadow-primary/5">
      <div class="px-8 mb-12">
        <h2 class="font-headline italic font-black text-primary text-2xl">MorLancer</h2>
        <p class="text-[10px] text-on-surface-variant tracking-[0.2em] font-bold uppercase mt-1">Talents Digitaux</p>
      </div>
      <nav class="flex-grow flex flex-col gap-2">
        <a @click="activeTab = 'dashboard'" :class="activeTab === 'dashboard' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-on-surface-variant hover:bg-primary/10'" class="flex items-center gap-4 rounded-full mx-4 py-3 px-6 transition-all cursor-pointer">
          <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">dashboard</span>
          <span class="text-sm font-bold">Tableau de bord</span>
        </a>
        <a @click="activeTab = 'briefs'" :class="activeTab === 'briefs' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-on-surface-variant hover:bg-primary/10'" class="flex items-center gap-4 rounded-full mx-4 py-3 px-6 transition-all cursor-pointer group">
          <span class="material-symbols-outlined group-hover:scale-110 transition-transform">work</span>
          <span class="text-sm font-medium">Mes Briefs</span>
        </a>
        <a @click="activeTab = 'payments'" :class="activeTab === 'payments' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-on-surface-variant hover:bg-primary/10'" class="flex items-center gap-4 rounded-full mx-4 py-3 px-6 transition-all cursor-pointer group">
          <span class="material-symbols-outlined group-hover:scale-110 transition-transform">payments</span>
          <span class="text-sm font-medium">Paiements</span>
        </a>
        <router-link to="/messaging" class="flex items-center gap-4 text-on-surface-variant mx-4 py-3 px-6 hover:bg-primary/10 rounded-full transition-all group">
          <span class="material-symbols-outlined group-hover:scale-110 transition-transform">chat_bubble</span>
          <span class="text-sm font-medium">Messages</span>
        </router-link>
        <a @click="activeTab = 'profile'" :class="activeTab === 'profile' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-on-surface-variant hover:bg-primary/10'" class="flex items-center gap-4 rounded-full mx-4 py-3 px-6 transition-all cursor-pointer group">
          <span class="material-symbols-outlined group-hover:scale-110 transition-transform">person</span>
          <span class="text-sm font-medium">Profil</span>
        </a>
      </nav>
      <!-- Zone Promotionnelle / Upgrade -->
      <div class="px-4 mt-auto">
        <div class="p-6 bg-surface-container-high/50 rounded-[2rem] border border-primary/5 flex flex-col gap-4 text-center">
          <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Besoin de plus de visibilité ?</p>
          <button class="w-full bg-secondary text-white py-3 rounded-full font-bold text-xs hover:brightness-110 transition-all shadow-lg shadow-secondary/10">Devenir Premium</button>
        </div>
      </div>
    </aside>

    <!-- Contenu Principal (Main Content) -->
    <main class="ml-64 flex-1 p-12 zellige-pattern bg-white/30">
      <!-- En-tête commun -->
      <header class="flex justify-between items-start mb-10 px-4">
        <div>
          <h1 class="font-headline font-bold text-5xl text-on-surface mb-2">Marhaba, {{ userName }}</h1>
          <p class="text-on-surface-variant font-medium text-lg italic">{{ greeting }}</p>
        </div>
        <div class="flex items-center gap-4">
          <button @click="activeTab = 'missions'" class="flex items-center gap-2 bg-primary text-white px-6 py-3 rounded-full font-bold text-sm hover:scale-105 active:scale-95 transition-all shadow-xl shadow-primary/30">
            <span class="material-symbols-outlined text-sm">search</span>
            Chercher une mission
          </button>
        </div>
      </header>

      <!-- Onglet Missions Search -->
      <div v-if="activeTab === 'missions'" class="px-4">
        <MissionSearch />
      </div>

      <!-- Onglet Dashboard (contenu existant) -->
      <div v-if="activeTab === 'dashboard'">

      <!-- Grille Bento : Organisation visuelle moderne -->
      <div class="grid grid-cols-12 gap-8 px-4">
        
        <!-- Section Financière (Gauche) -->
        <section class="col-span-12 lg:col-span-4 flex flex-col gap-8">
          <div class="bg-white/80 backdrop-blur-md p-8 rounded-[2.5rem] border border-primary/5 flex flex-col justify-between h-64 relative overflow-hidden group shadow-2xl shadow-primary/5">
            <div class="absolute -top-6 -right-6 w-32 h-32 bg-primary/5 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
            <div>
              <p class="text-on-surface-variant text-[10px] font-bold uppercase tracking-[0.2em] mb-2 opacity-60">Revenus Disponibles</p>
              <h3 class="font-headline text-5xl font-bold text-on-surface tracking-tighter">{{ balance }} <span class="text-xl font-bold text-primary italic">MAD</span></h3>
            </div>
            <div class="mt-8 flex items-center gap-4">
              <div class="flex -space-x-3">
                <div v-for="i in 3" :key="i" class="w-10 h-10 rounded-full border-2 border-white bg-surface-container flex items-center justify-center overflow-hidden">
                   <span class="material-symbols-outlined text-sm opacity-30">person</span>
                </div>
                <div class="w-10 h-10 rounded-full border-2 border-white bg-primary text-white flex items-center justify-center text-[10px] font-bold shadow-lg shadow-primary/20">+12</div>
              </div>
              <p class="text-xs text-on-surface-variant font-bold italic opacity-70">Collaborations en cours</p>
            </div>
          </div>

          <!-- Liste des talents suggérés (Reseautage) -->
          <div class="bg-surface-container-low/40 backdrop-blur-md p-8 rounded-[2.5rem] border border-primary/5 flex-1 shadow-inner">
            <div class="flex justify-between items-center mb-8">
              <h4 class="font-headline text-xl font-bold text-primary italic">Nouveaux talents</h4>
              <a class="text-on-surface-variant text-xs font-bold uppercase tracking-widest hover:text-primary transition-colors" href="#">Voir tout</a>
            </div>
            <div class="flex flex-col gap-4">
              <div v-for="talent in talents" :key="talent.name" class="flex items-center gap-4 p-4 rounded-2xl bg-white/50 border border-transparent hover:border-primary/10 hover:bg-white transition-all shadow-sm group">
                <img :alt="talent.name" class="w-12 h-12 rounded-full object-cover shadow-sm group-hover:scale-110 transition-transform" :src="talent.avatar">
                <div class="flex-grow min-w-0">
                   <p class="text-sm font-bold truncate">{{ talent.name }}</p>
                   <p class="text-[10px] text-on-surface-variant font-bold uppercase tracking-widest opacity-60">{{ talent.skill }}</p>
                </div>
                <div class="bg-tertiary-container/30 p-2 rounded-xl">
                   <span class="material-symbols-outlined text-tertiary text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Liste des missions actives (Droite) -->
        <section class="col-span-12 lg:col-span-8 flex flex-col gap-8">
          <div class="flex items-baseline justify-between mb-2">
            <h2 class="font-headline text-3xl font-bold text-on-surface">Missions en cours</h2>
            <p class="text-on-surface-variant text-sm font-bold italic opacity-60">Projets que vous réalisez actuellement</p>
          </div>

          <!-- Carte de Mission (Design Premium) -->
          <div v-for="mission in activeMissions" :key="mission.id" 
               class="bg-white rounded-[2.5rem] overflow-hidden shadow-2xl shadow-primary/5 flex flex-col md:flex-row border border-primary/5 group transition-all hover:translate-y-[-4px]">
            <div class="w-full md:w-60 h-60 md:h-auto overflow-hidden relative">
              <img class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000" :src="mission.image">
              <div class="absolute top-4 left-4" v-if="mission.urgent">
                <span class="bg-secondary text-white text-[10px] px-4 py-1.5 rounded-full font-black uppercase tracking-widest shadow-lg shadow-secondary/30">Livrable proche</span>
              </div>
            </div>
            <div class="flex-grow p-10 flex flex-col justify-between">
              <div>
                <div class="flex justify-between items-start mb-6">
                  <div>
                    <h3 class="font-headline text-2xl font-bold mb-1 text-on-surface group-hover:text-primary transition-colors">{{ mission.title }}</h3>
                    <p class="text-on-surface-variant text-sm font-bold opacity-60 italic">Client: {{ mission.client }}</p>
                  </div>
                  <div class="text-right">
                    <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant mb-1">Gains prévus</p>
                    <p class="font-bold text-2xl text-on-surface">{{ mission.budget }} <span class="text-xs text-primary font-black">MAD</span></p>
                  </div>
                </div>
                <!-- Barre de progression de la mission -->
                <div class="mt-8">
                  <div class="flex justify-between text-[10px] font-black uppercase tracking-widest mb-2 text-primary">
                    <span>Avancement du projet</span>
                    <span>{{ mission.progress }}%</span>
                  </div>
                  <div class="h-2 w-full bg-surface-container rounded-full overflow-hidden shadow-inner">
                    <div class="h-full bg-primary rounded-full transition-all duration-1000" :style="{ width: mission.progress + '%' }"></div>
                  </div>
                </div>
              </div>
              <div class="mt-10 flex justify-end gap-4">
                <button class="px-8 py-3 text-primary text-sm font-black uppercase tracking-widest hover:underline transition-all">Détails</button>
                <button class="px-8 py-3 bg-surface-container text-on-surface rounded-full text-xs font-black uppercase tracking-widest hover:bg-primary hover:text-white transition-all shadow-sm">Déposer livrable</button>
              </div>
            </div>
          </div>
        </section>
      </div>

      <!-- Rubrique Conciergerie / Aide (En bas) -->
      <section class="mt-16 mx-4 bg-primary text-white p-16 rounded-[3rem] shadow-2xl relative overflow-hidden group">
        <div class="absolute inset-0 zellige-pattern opacity-10 group-hover:scale-110 transition-transform duration-[20s]"></div>
        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-12">
          <div class="max-w-2xl text-center md:text-left">
            <h2 class="font-headline text-4xl font-bold mb-6 italic leading-tight">Gérez votre fiscalité avec MorLancer Pay</h2>
            <p class="text-white/80 font-medium text-lg leading-relaxed">Récupérez vos gains en toute sécurité via PayPal ou Payoneer, avec une gestion automatisée de vos factures.</p>
          </div>
          <button class="bg-secondary text-white px-12 py-5 rounded-full font-black text-sm uppercase tracking-[0.2em] shadow-2xl hover:scale-105 active:scale-95 transition-all flex-shrink-0">
            Configurer mes paiements
          </button>
        </div>
      </section>
      </div> <!-- fin onglet dashboard -->
    </main>
    </div>
  </div>
</template>

<script setup>
import TopNavBar from '@/components/Common/TopNavBar.vue';
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import MissionSearch from '@/components/Freelancer/MissionSearch.vue';

const userName = ref(JSON.parse(localStorage.getItem('user') || '{}')?.name || '');
const userInitials = computed(() => {
  return userName.value.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
});

const activeTab = ref('dashboard');
const router = useRouter();
const balance = ref('0.00');
const searchQuery = ref('');
const showNotifications = ref(false);
const unreadNotifications = ref(0);

// Données dynamiques
const talents = ref([]);
const activeMissions = ref([]);
const favorites = ref([]);
const notifications = ref([]);

const greeting = computed(() => {
  const h = new Date().getHours();
  if (h < 12) return 'Bonjour, bonne matinée !';
  if (h < 18) return 'Bon après-midi, gérez vos projets.';
  return 'Bonsoir, continuez sur votre lancée !';
});

// Récupérer les données de l'API
const fetchDashboardData = async () => {
  try {
    const token = localStorage.getItem('token');
    const headers = { Authorization: `Bearer ${token}` };

    // Récupérer les missions en cours
    const missionsRes = await axios.get('http://localhost:8000/api/freelancer/missions/active', { headers });
    activeMissions.value = missionsRes.data.data || [];

    // Récupérer le solde financier
    const balanceRes = await axios.get('http://localhost:8000/api/freelancer/balance', { headers });
    balance.value = balanceRes.data.balance || '0.00';

    // Récupérer les talents suggérés
    const talentsRes = await axios.get('http://localhost:8000/api/freelancer/suggested', { headers });
    talents.value = talentsRes.data.data || [];

    // Récupérer les favoris
    const favoritesRes = await axios.get('http://localhost:8000/api/freelancer/favorites', { headers });
    favorites.value = favoritesRes.data.data || [];

    // Récupérer les notifications
    const notificationsRes = await axios.get('http://localhost:8000/api/freelancer/notifications', { headers });
    notifications.value = notificationsRes.data.data || [];
    unreadNotifications.value = notifications.value.filter(n => !n.read).length;

  } catch (error) {
    console.error('Erreur lors du chargement des données:', error);
    // Utiliser des données par défaut en cas d'erreur
    setDefaultData();
  }
};

// Données par défaut si l'API n'est pas disponible
const setDefaultData = () => {
  balance.value = '14,250.00';
  activeMissions.value = [
    { 
      id: 1, 
      title: 'Rénovation Salon Riadh', 
      client: 'Mehdi El Fassi', 
      budget: '8,500', 
      progress: 75, 
      urgent: true,
      image: 'https://images.unsplash.com/photo-1590483736622-39da8af7541c?auto=format&fit=crop&w=400&q=80'
    },
    { 
      id: 2, 
      title: 'Mobilier sur mesure - Noyer', 
      client: 'Sarah M.', 
      budget: '12,000', 
      progress: 32, 
      urgent: false,
      image: 'https://images.unsplash.com/photo-1533090161767-e6ffed986c88?auto=format&fit=crop&w=400&q=80'
    }
  ];
  talents.value = [
    { id: 1, name: 'Yasmine B.', skill: 'Design Zellige', avatar: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=150&q=80' },
    { id: 2, name: 'Omar K.', skill: 'Architecture Int.', avatar: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=150&q=80' }
  ];
  notifications.value = [
    { id: 1, title: 'Nouveau message', message: 'Mehdi El Fassi vous a envoyé un message', read: false },
    { id: 2, title: 'Paiement reçu', message: '5,000 MAD reçu pour la mission "Rénovation Salon"', read: true }
  ];
  unreadNotifications.value = 1;
  favorites.value = [
    {
      id: 1,
      title: 'Projet Web Moderne',
      client: 'Client A',
      description: 'Création d\'un site web moderne et responsive',
      budget: '5,000'
    }
  ];
};

const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value;
};

const openNewBriefModal = () => {
  // TODO: Ouvrir un modal pour créer un nouveau brief
  console.log('Ouvrir modal nouveau brief');
};

// Charger les données au montage du composant
onMounted(() => {
  fetchDashboardData();
});
</script>

<style scoped>
/**
 * Styles locaux au composant. 
 * .zellige-pattern : Applique le motif géométrique marocain discret.
 */
.zellige-pattern {
  background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M30 0l15 15-15 15-15-15zM0 30l15 15-15 15-15-15zM60 30l15 15-15 15-15-15zM30 60l15-15-15-15-15 15z' fill='%23006233' fill-opacity='0.03'/%3E%3C/svg%3E");
}
</style>
