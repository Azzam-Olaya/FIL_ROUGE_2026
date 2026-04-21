<template>
  <div class="min-h-screen bg-surface selection:bg-primary/20">
    <!-- Barre de Navigation (TopAppBar) -->
    <header class="fixed top-0 w-full z-50 bg-surface/80 backdrop-blur-md flex justify-between items-center px-6 py-4 border-b border-primary/5">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-primary font-bold">auto_awesome</span>
        <span class="text-2xl font-serif font-bold text-primary tracking-tight">MorLancer</span>
      </div>
      <!-- Menu de navigation (Visible sur Desktop) -->
      <div class="hidden md:flex items-center gap-8">
        <a href="#about" class="text-sm font-medium text-on-surface-variant hover:text-primary transition-colors uppercase tracking-widest">À propos</a>
        <a href="#services" class="text-sm font-medium text-on-surface-variant hover:text-primary transition-colors uppercase tracking-widest">Services</a>
        <a href="#contact" class="text-sm font-medium text-on-surface-variant hover:text-primary transition-colors uppercase tracking-widest">Contact</a>
      </div>
      <!-- Boutons d'action : Connexion et Inscription -->
      <div class="flex items-center gap-4">
        <router-link to="/login" class="text-sm font-bold text-primary hover:bg-primary/5 px-4 py-2 rounded-xl transition-all uppercase tracking-wider">Connexion</router-link>
        <router-link to="/register" class="bg-primary text-white px-6 py-2.5 rounded-xl text-sm font-bold shadow-lg shadow-primary/20 hover:bg-primary-container transition-all active:scale-95 uppercase tracking-wider">Rejoindre</router-link>
      </div>
    </header>

    <main class="pt-24 min-h-screen">
      <!-- Section Hero : Présentation accrocheuse -->
      <section class="relative py-20 overflow-hidden">
        <!-- Motif Zellige décoratif en arrière-plan -->
        <div class="absolute inset-0 z-0 opacity-10 pointer-events-none zellige-pattern"></div>
        <div class="max-w-5xl mx-auto px-6 relative z-10 text-center">
          <span class="inline-block bg-secondary/10 text-secondary text-[10px] font-bold uppercase tracking-[0.3em] px-4 py-1.5 rounded-full mb-6">Plateforme n°1 au Maroc</span>
          <h1 class="text-5xl md:text-7xl font-serif font-bold text-on-surface mb-8 leading-tight tracking-tight">
            L'Élite de <span class="text-primary italic">l'Artisanat</span> Digital
          </h1>
          <p class="text-lg text-on-surface-variant max-w-2xl mx-auto mb-10 font-medium leading-relaxed">
            Connectez-vous avec les meilleurs talents marocains. Des projets d'exception, réalisés par des mains expertes.
          </p>
          <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <router-link to="/register" class="w-full sm:w-auto bg-primary text-white px-10 py-4 rounded-2xl font-bold text-lg shadow-xl shadow-primary/30 hover:translate-y-[-2px] transition-all">
              Commencer l'expérience
            </router-link>
            <a href="#services" class="w-full sm:w-auto bg-white border border-primary/10 text-primary px-10 py-4 rounded-2xl font-bold text-lg hover:bg-primary/5 transition-all">
              Voir nos services
            </a>
          </div>
        </div>
      </section>

      <!-- Section Services : Liste des domaines d'expertise -->
      <section id="services" class="py-20 px-6 max-w-5xl mx-auto">
        <h2 class="text-primary/60 text-xs font-bold uppercase tracking-[0.2em] mb-10 text-center">Savoir-faire Exceptionnel</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <!-- Boucle Vue.js : On affiche chaque service de la liste -->
          <div v-for="service in services" :key="service.title" class="neo-artisan-card p-10 rounded-[3rem] border-l-[8px] transition-all" :class="service.borderColor">
             <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-6" :class="service.bgColor">
                <span class="material-symbols-outlined text-2xl font-bold" :class="service.iconColor">{{ service.icon }}</span>
             </div>
             <h4 class="text-2xl font-serif font-bold text-on-surface mb-4">{{ service.title }}</h4>
             <p class="text-on-surface-variant text-sm leading-relaxed font-medium">{{ service.description }}</p>
          </div>
        </div>
      </section>

      <!-- Section À Propos : Philosophie de la plateforme -->
      <section id="about" class="py-20 bg-surface-container relative overflow-hidden">
        <div class="max-w-5xl mx-auto px-6 grid md:grid-cols-2 gap-12 items-center">
          <div>
            <span class="text-primary font-bold text-xs uppercase tracking-widest mb-4 block">Notre Philosophie</span>
            <h3 class="text-4xl font-serif font-bold text-on-surface mb-6 leading-tight">Fusion entre Héritage et Vision Digitale</h3>
            <p class="text-on-surface-variant mb-6 leading-relaxed text-lg">
              MorLancer n'est pas seulement une plateforme, c'est une vitrine pour l'artisanat du futur. Nous valorisons la qualité, la rigueur et le talent marocain à l'échelle globale.
            </p>
            <ul class="space-y-4">
              <li v-for="item in benefits" :key="item" class="flex items-center gap-3 text-on-surface font-semibold">
                <span class="material-symbols-outlined text-primary font-bold text-xl">verified</span>
                {{ item }}
              </li>
            </ul>
          </div>
          <div class="relative">
            <div class="absolute -inset-4 bg-primary/5 rounded-[4rem] rotate-3"></div>
            <img src="https://images.unsplash.com/photo-1541746972996-4e0b0f43e02a?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Work" class="relative rounded-[3.5rem] shadow-2xl border-4 border-white object-cover h-[400px] w-full">
          </div>
        </div>
      </section>
    </main>

    <!-- Pied de Page (Footer) -->
    <footer class="bg-on-surface text-surface-container-low py-16 px-6">
      <div class="max-w-5xl mx-auto flex flex-col md:flex-row justify-between items-center gap-8">
        <div>
          <span class="text-3xl font-serif font-bold text-white mb-2 block">MorLancer</span>
          <p class="text-on-surface-variant text-sm">© 2026 MorLancer. Fièrement développé au Maroc.</p>
        </div>
        <!-- Réseaux Sociaux -->
        <div class="flex gap-6">
          <a href="#" class="hover:text-primary transition-colors"><i class="fab fa-instagram text-xl"></i></a>
          <a href="#" class="hover:text-primary transition-colors"><i class="fab fa-linkedin text-xl"></i></a>
          <a href="#" class="hover:text-primary transition-colors"><i class="fab fa-twitter text-xl"></i></a>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
/**
 * Données brutes pour les services de la page d'accueil.
 * En utilisant une boucle (v-for) dans le template, on garde un code propre.
 */
const services = [
  { 
    title: 'Code & Dev', 
    description: 'Applications robustes et performantes, sculptées avec les meilleures technologies du marché.',
    icon: 'architecture',
    borderColor: 'border-primary',
    bgColor: 'bg-primary/5',
    iconColor: 'text-primary'
  },
  { 
    title: 'Art Visuel', 
    description: 'Identités visuelles d\'exception qui capturent l\'essence de votre marque avec élégance.',
    icon: 'palette',
    borderColor: 'border-secondary',
    bgColor: 'bg-secondary/5',
    iconColor: 'text-secondary'
  },
  { 
    title: 'Stratégie', 
    description: 'Conseil et marketing d\'élite pour propulser votre activité vers de nouveaux sommets.',
    icon: 'auto_graph',
    borderColor: 'border-tertiary',
    bgColor: 'bg-tertiary/5',
    iconColor: 'text-tertiary'
  }
];

/**
 * Liste des avantages affichés dans la section "À Propos".
 */
const benefits = [
  'Validation rigoureuse des profils',
  'Système de paiement sécurisé',
  'Accompagnement premium 24/7',
  'Réseau exclusif de talents'
];
</script>
