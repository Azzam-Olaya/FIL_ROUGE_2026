<template>
  <div class="min-h-screen bg-surface selection:bg-primary/20 zellige-pattern overflow-x-hidden">

    <!-- ═══════════════════════════════════════════════
         NAVBAR
    ═══════════════════════════════════════════════ -->
    <header class="fixed top-0 w-full z-50 bg-surface/80 backdrop-blur-xl border-b border-primary/5 transition-all duration-300"
            :class="{ 'shadow-lg shadow-primary/5': scrolled }">
      <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <!-- Logo -->
        <router-link to="/" class="flex items-center gap-2 group">
          <span class="material-symbols-outlined text-primary text-3xl font-bold group-hover:rotate-12 transition-transform">auto_awesome</span>
          <span class="text-2xl font-serif font-bold text-primary tracking-tight">MorLancer</span>
        </router-link>

        <!-- Desktop Nav -->
        <nav class="hidden md:flex items-center gap-8">
          <a href="#about" class="nav-link">À propos</a>
          <a href="#how" class="nav-link">Comment ça marche</a>
          <a href="#contact" class="nav-link">Contact</a>
        </nav>

        <!-- Auth Buttons -->
        <div class="hidden md:flex items-center gap-4">
          <router-link to="/login" class="text-sm font-bold text-primary hover:bg-primary/5 px-5 py-2.5 rounded-xl transition-all uppercase tracking-wider">
            Connexion
          </router-link>
          <router-link to="/register" class="bg-primary text-white px-6 py-2.5 rounded-xl text-sm font-bold shadow-lg shadow-primary/20 hover:bg-primary-container hover:shadow-xl transition-all active:scale-95 uppercase tracking-wider">
            Rejoindre
          </router-link>
        </div>

        <!-- Mobile Hamburger -->
        <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden w-10 h-10 flex items-center justify-center rounded-xl hover:bg-primary/5 transition-all">
          <span class="material-symbols-outlined text-on-surface text-2xl">{{ mobileMenuOpen ? 'close' : 'menu' }}</span>
        </button>
      </div>

      <!-- Mobile Menu -->
      <transition name="slide-down">
        <div v-if="mobileMenuOpen" class="md:hidden bg-white border-t border-primary/5 px-6 py-6 space-y-4 shadow-xl">
          <a href="#about" @click="mobileMenuOpen = false" class="block text-sm font-bold text-on-surface-variant hover:text-primary uppercase tracking-widest py-2">À propos</a>
          <a href="#how" @click="mobileMenuOpen = false" class="block text-sm font-bold text-on-surface-variant hover:text-primary uppercase tracking-widest py-2">Comment ça marche</a>
          <a href="#contact" @click="mobileMenuOpen = false" class="block text-sm font-bold text-on-surface-variant hover:text-primary uppercase tracking-widest py-2">Contact</a>
          <hr class="border-primary/10">
          <router-link to="/login" class="block text-sm font-bold text-primary uppercase tracking-wider py-2">Connexion</router-link>
          <router-link to="/register" class="block bg-primary text-white text-center px-6 py-3 rounded-xl text-sm font-bold shadow-lg uppercase tracking-wider">Rejoindre</router-link>
        </div>
      </transition>
    </header>

    <main class="pt-24">

      <!-- ═══════════════════════════════════════════════
           SECTION 1 : HERO / PRÉSENTATION
      ═══════════════════════════════════════════════ -->
      <section class="relative py-24 md:py-32 overflow-hidden">
        <!-- Background decorations -->
        <div class="absolute inset-0 z-0 opacity-10 pointer-events-none zellige-pattern"></div>
        <div class="absolute top-[-15%] right-[-10%] w-[50vw] h-[50vw] bg-primary/8 rounded-full blur-[140px] pointer-events-none"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-[40vw] h-[40vw] bg-secondary/6 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="max-w-6xl mx-auto px-6 relative z-10 text-center">
          <span class="inline-block bg-secondary/10 text-secondary text-[10px] font-black uppercase tracking-[0.3em] px-5 py-2 rounded-full mb-8 animate-pulse">
            🇲🇦 Marketplace Freelance au Maroc
          </span>

          <h1 class="text-5xl md:text-7xl lg:text-8xl font-serif font-bold text-on-surface mb-8 leading-[1.1] tracking-tight">
            Connectez Clients <br class="hidden sm:block">
            <span class="text-primary italic relative">
              & Freelancers
              <svg class="absolute -bottom-2 left-0 w-full h-3 text-primary/20" viewBox="0 0 200 8" preserveAspectRatio="none">
                <path d="M0 7c50-6 100-6 200 0" stroke="currentColor" stroke-width="3" fill="none"/>
              </svg>
            </span> au Maroc
          </h1>

          <p class="text-lg md:text-xl text-on-surface-variant max-w-2xl mx-auto mb-12 font-medium leading-relaxed">
            Publiez vos missions, découvrez des talents via leurs Briefs, et collaborez en toute confiance grâce à un système de contrats et paiements sécurisés.
          </p>

          <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <router-link to="/register" class="w-full sm:w-auto bg-primary text-white px-10 py-4 rounded-2xl font-bold text-lg shadow-xl shadow-primary/30 hover:translate-y-[-3px] hover:shadow-2xl transition-all duration-300 active:scale-95">
              Créer mon compte
            </router-link>
            <a href="#how" class="w-full sm:w-auto bg-white border-2 border-primary/10 text-primary px-10 py-4 rounded-2xl font-bold text-lg hover:bg-primary/5 hover:border-primary/20 transition-all duration-300">
              Comment ça marche
            </a>
          </div>

          <!-- Stats bar -->
          <div class="mt-16 flex flex-wrap justify-center gap-8 md:gap-16">
            <div v-for="stat in stats" :key="stat.label" class="text-center">
              <p class="text-3xl md:text-4xl font-serif font-black text-primary">{{ stat.value }}</p>
              <p class="text-xs font-bold uppercase tracking-widest text-on-surface-variant/60 mt-1">{{ stat.label }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- ═══════════════════════════════════════════════
           SECTION 2 : SERVICES
      ═══════════════════════════════════════════════ -->
      <section id="services" class="py-20 md:py-28 px-6">
        <div class="max-w-6xl mx-auto">
          <div class="text-center mb-16">
            <span class="text-primary/60 text-xs font-black uppercase tracking-[0.3em] mb-4 block">Ce que vous pouvez faire</span>
            <h2 class="text-4xl md:text-5xl font-serif font-bold text-on-surface leading-tight">
              Une plateforme <span class="text-primary italic">complète</span>
            </h2>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div v-for="(service, index) in services" :key="service.title"
                 class="neo-artisan-card p-10 rounded-[2.5rem] border-l-[6px] group cursor-pointer"
                 :class="service.borderColor"
                 :style="{ animationDelay: index * 100 + 'ms' }">
              <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-6 transition-all duration-300 group-hover:scale-110"
                   :class="service.bgColor">
                <span class="material-symbols-outlined text-3xl font-bold" :class="service.iconColor">{{ service.icon }}</span>
              </div>
              <h4 class="text-2xl font-serif font-bold text-on-surface mb-4 group-hover:text-primary transition-colors">{{ service.title }}</h4>
              <p class="text-on-surface-variant text-sm leading-relaxed font-medium">{{ service.description }}</p>
              <div class="mt-6 flex items-center gap-2 text-primary text-xs font-bold uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0">
                <span>Explorer</span>
                <span class="material-symbols-outlined text-sm">arrow_forward</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ═══════════════════════════════════════════════
           SECTION 3 : À PROPOS
      ═══════════════════════════════════════════════ -->
      <section id="how" class="py-20 md:py-28 bg-surface-container relative overflow-hidden">
        <div class="absolute inset-0 opacity-5 pointer-events-none zellige-pattern"></div>
        <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-2 gap-16 items-center relative z-10">
          <div>
            <span class="text-primary font-black text-xs uppercase tracking-[0.3em] mb-4 block">Comment ça marche</span>
            <h3 class="text-4xl md:text-5xl font-serif font-bold text-on-surface mb-8 leading-tight">
              Du Brief au <span class="text-primary italic">Contrat</span> en quelques clics
            </h3>
            <p class="text-on-surface-variant mb-8 leading-relaxed text-lg">
              MorLancer centralise tout le cycle freelance : publication de missions, découverte de talents via leurs Briefs, génération automatique de contrats, messagerie en temps réel et paiements sécurisés.
            </p>
            <ul class="space-y-5">
              <li v-for="item in benefits" :key="item" class="flex items-center gap-4 group">
                <span class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center flex-shrink-0 group-hover:bg-primary group-hover:text-white transition-all">
                  <span class="material-symbols-outlined text-primary text-lg group-hover:text-white transition-colors" style="font-variation-settings: 'FILL' 1;">verified</span>
                </span>
                <span class="text-on-surface font-semibold text-base">{{ item }}</span>
              </li>
            </ul>
          </div>
          <div class="relative">
            <div class="absolute -inset-6 bg-primary/5 rounded-[4rem] rotate-3 transition-transform hover:rotate-6"></div>
            <div class="absolute -inset-6 bg-secondary/5 rounded-[4rem] -rotate-2"></div>
            <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80"
                 alt="Collaboration freelance"
                 class="relative rounded-[3rem] shadow-2xl border-4 border-white object-cover h-[450px] w-full hover:scale-[1.02] transition-transform duration-500">
          </div>
        </div>
      </section>

      <!-- ═══════════════════════════════════════════════
           SECTION 4 : CTA (Call to Action)
      ═══════════════════════════════════════════════ -->
      <section class="py-20 md:py-28 px-6">
        <div class="max-w-5xl mx-auto relative overflow-hidden bg-primary rounded-[3rem] p-12 md:p-20 text-center shadow-2xl shadow-primary/30">
          <!-- Zellige overlay -->
          <div class="absolute inset-0 opacity-10 pointer-events-none zellige-pattern"></div>
          <!-- Glow -->
          <div class="absolute top-0 right-0 w-[60%] h-[60%] bg-white/5 rounded-full blur-[100px]"></div>

          <div class="relative z-10">
            <span class="inline-block bg-white/10 backdrop-blur-sm text-white/80 text-[10px] font-black uppercase tracking-[0.3em] px-5 py-2 rounded-full mb-8 border border-white/10">
              Rejoignez la communauté
            </span>
            <h2 class="text-4xl md:text-5xl lg:text-6xl font-serif font-bold text-white mb-6 leading-tight">
              Prêt à lancer<br>votre première <span class="italic text-primary-fixed-dim">mission</span> ?
            </h2>
            <p class="text-white/70 text-lg max-w-xl mx-auto mb-10 font-medium leading-relaxed">
              Rejoignez MorLancer, créez votre profil, publiez vos Briefs ou vos missions et commencez à collaborer dès aujourd'hui.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
              <router-link to="/register" class="w-full sm:w-auto bg-white text-primary px-10 py-4 rounded-2xl font-bold text-lg shadow-xl hover:shadow-2xl hover:translate-y-[-3px] transition-all duration-300 active:scale-95">
                Créer mon compte gratuit
              </router-link>
              <router-link to="/login" class="w-full sm:w-auto border-2 border-white/20 text-white px-10 py-4 rounded-2xl font-bold text-lg hover:bg-white/10 transition-all duration-300">
                J'ai déjà un compte
              </router-link>
            </div>
          </div>
        </div>
      </section>

      <!-- ═══════════════════════════════════════════════
           SECTION 5 : CONTACT
      ═══════════════════════════════════════════════ -->
      <section id="contact" class="py-20 md:py-28 bg-surface-container relative overflow-hidden">
        <div class="absolute inset-0 opacity-5 pointer-events-none zellige-pattern"></div>
        <div class="max-w-6xl mx-auto px-6 relative z-10">
          <div class="text-center mb-16">
            <span class="text-primary/60 text-xs font-black uppercase tracking-[0.3em] mb-4 block">Parlons Ensemble</span>
            <h2 class="text-4xl md:text-5xl font-serif font-bold text-on-surface">
              Contactez-<span class="text-primary italic">nous</span>
            </h2>
          </div>

          <div class="grid md:grid-cols-2 gap-12">
            <!-- Contact Info -->
            <div class="space-y-8">
              <p class="text-on-surface-variant text-lg leading-relaxed font-medium">
                Une question, un partenariat ou du feedback ? Notre équipe est à votre écoute pour vous accompagner.
              </p>

              <div v-for="info in contactInfo" :key="info.label"
                   class="flex items-start gap-5 group">
                <div class="w-14 h-14 rounded-2xl bg-primary/5 flex items-center justify-center flex-shrink-0 group-hover:bg-primary transition-all duration-300">
                  <span class="material-symbols-outlined text-primary text-2xl group-hover:text-white transition-colors">{{ info.icon }}</span>
                </div>
                <div>
                  <h5 class="font-bold text-on-surface text-sm uppercase tracking-widest mb-1">{{ info.label }}</h5>
                  <p class="text-on-surface-variant font-medium">{{ info.value }}</p>
                </div>
              </div>
            </div>

            <!-- Contact Form -->
            <form @submit.prevent="submitContact" class="neo-artisan-card p-10 rounded-[2.5rem] space-y-6">
              <div>
                <label for="contact-name" class="block text-xs font-bold text-on-surface-variant uppercase tracking-widest mb-2">Nom complet</label>
                <input id="contact-name" v-model="contactForm.name" type="text" required
                       class="w-full px-5 py-3.5 rounded-xl border-2 border-primary/10 bg-surface focus:border-primary focus:ring-2 focus:ring-primary/10 text-on-surface font-medium transition-all outline-none"
                       placeholder="Votre nom...">
              </div>
              <div>
                <label for="contact-email" class="block text-xs font-bold text-on-surface-variant uppercase tracking-widest mb-2">Email</label>
                <input id="contact-email" v-model="contactForm.email" type="email" required
                       class="w-full px-5 py-3.5 rounded-xl border-2 border-primary/10 bg-surface focus:border-primary focus:ring-2 focus:ring-primary/10 text-on-surface font-medium transition-all outline-none"
                       placeholder="votre@email.com">
              </div>
              <div>
                <label for="contact-message" class="block text-xs font-bold text-on-surface-variant uppercase tracking-widest mb-2">Message</label>
                <textarea id="contact-message" v-model="contactForm.message" rows="4" required
                          class="w-full px-5 py-3.5 rounded-xl border-2 border-primary/10 bg-surface focus:border-primary focus:ring-2 focus:ring-primary/10 text-on-surface font-medium transition-all outline-none resize-none"
                          placeholder="Votre message..."></textarea>
              </div>
              <button type="submit"
                      class="w-full bg-primary text-white py-4 rounded-xl font-bold text-sm uppercase tracking-widest shadow-lg shadow-primary/20 hover:bg-primary-container hover:shadow-xl transition-all active:scale-[0.98]"
                      :class="{ 'opacity-70 cursor-not-allowed': contactSent }">
                <span v-if="!contactSent" class="flex items-center justify-center gap-2">
                  <span class="material-symbols-outlined text-lg">send</span>
                  Envoyer le message
                </span>
                <span v-else class="flex items-center justify-center gap-2">
                  <span class="material-symbols-outlined text-lg">check_circle</span>
                  Message envoyé !
                </span>
              </button>
            </form>
          </div>
        </div>
      </section>
    </main>

    <!-- ═══════════════════════════════════════════════
         FOOTER
    ═══════════════════════════════════════════════ -->
    <footer class="bg-on-surface text-surface-container-low py-16 px-6">
      <div class="max-w-6xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-start gap-12 mb-12">
          <div>
            <span class="text-3xl font-serif font-bold text-white mb-3 block">MorLancer</span>
            <p class="text-on-surface-variant/60 text-sm max-w-xs">La marketplace freelance marocaine — missions, contrats et paiements sécurisés en un seul endroit.</p>
          </div>
          <div class="grid grid-cols-2 gap-x-16 gap-y-3">
            <a href="#about" class="text-sm text-on-surface-variant/60 hover:text-white transition-colors">À propos</a>
            <a href="#how" class="text-sm text-on-surface-variant/60 hover:text-white transition-colors">Comment ça marche</a>
            <a href="#contact" class="text-sm text-on-surface-variant/60 hover:text-white transition-colors">Contact</a>
            <router-link to="/login" class="text-sm text-on-surface-variant/60 hover:text-white transition-colors">Connexion</router-link>
          </div>
        </div>
        <hr class="border-white/10 mb-8">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
          <p class="text-on-surface-variant/40 text-xs font-bold uppercase tracking-widest">© 2026 MorLancer. Fièrement créé au Maroc 🇲🇦</p>
          <div class="flex gap-4">
            <a href="#" class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center hover:bg-primary transition-all" aria-label="Instagram">
              <span class="material-symbols-outlined text-white/60 text-xl hover:text-white">photo_camera</span>
            </a>
            <a href="#" class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center hover:bg-primary transition-all" aria-label="LinkedIn">
              <span class="material-symbols-outlined text-white/60 text-xl hover:text-white">work</span>
            </a>
            <a href="#" class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center hover:bg-primary transition-all" aria-label="Twitter">
              <span class="material-symbols-outlined text-white/60 text-xl hover:text-white">tag</span>
            </a>
          </div>
        </div>
      </div>
    </footer>

  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

// ─── State ───
const mobileMenuOpen = ref(false);
const scrolled = ref(false);
const contactSent = ref(false);
const contactForm = ref({ name: '', email: '', message: '' });

// ─── Scroll listener for navbar shadow ───
const handleScroll = () => { scrolled.value = window.scrollY > 20; };
onMounted(() => window.addEventListener('scroll', handleScroll));
onUnmounted(() => window.removeEventListener('scroll', handleScroll));

// ─── Contact form handler ───
const submitContact = () => {
  contactSent.value = true;
  setTimeout(() => {
    contactSent.value = false;
    contactForm.value = { name: '', email: '', message: '' };
  }, 3000);
};

// ─── Data ───
const stats = [
  { value: '3 rôles', label: 'Admin · Client · Freelancer' },
  { value: '100%', label: 'Contrats automatisés' },
  { value: 'Temps réel', label: 'Messagerie intégrée' },
  { value: 'Escrow', label: 'Paiements sécurisés' }
];

const services = [
  {
    title: 'Briefs & Portfolio',
    description: 'Les freelancers publient leurs réalisations sous forme de Briefs. Les clients explorent, likent, commentent et favorisent les profils qui les inspirent.',
    icon: 'auto_awesome',
    borderColor: 'border-primary',
    bgColor: 'bg-primary/10',
    iconColor: 'text-primary'
  },
  {
    title: 'Missions & Contrats',
    description: 'Les clients publient des missions détaillées. Un contrat est généré automatiquement dès qu\'un accord est trouvé, avec suivi de statut complet.',
    icon: 'handshake',
    borderColor: 'border-secondary',
    bgColor: 'bg-secondary/10',
    iconColor: 'text-secondary'
  },
  {
    title: 'Paiements & Wallet',
    description: 'Système d\'escrow intégré avec PayPal. Les fonds sont sécurisés jusqu\'à la validation de la mission, protégeant clients et freelancers.',
    icon: 'account_balance_wallet',
    borderColor: 'border-tertiary',
    bgColor: 'bg-tertiary/10',
    iconColor: 'text-tertiary'
  }
];

const benefits = [
  'Approbation admin obligatoire pour chaque compte',
  'Contrats générés automatiquement à l\'accord',
  'Messagerie en temps réel liée aux contrats',
  'Paiements sécurisés via escrow PayPal'
];

const contactInfo = [
  { icon: 'mail', label: 'Email', value: 'contact@morlancer.ma' },
  { icon: 'location_on', label: 'Adresse', value: 'Casablanca, Maroc' },
  { icon: 'schedule', label: 'Horaires', value: 'Lun–Ven : 9h – 18h' }
];
</script>

<style scoped>
.nav-link {
  @apply text-sm font-bold text-on-surface-variant hover:text-primary transition-colors uppercase tracking-widest relative;
}
.nav-link::after {
  content: '';
  @apply absolute bottom-[-4px] left-0 w-0 h-[2px] bg-primary transition-all duration-300;
}
.nav-link:hover::after {
  @apply w-full;
}

/* Mobile menu animation */
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.3s ease;
}
.slide-down-enter-from,
.slide-down-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
