<template>
  <div class="min-h-screen bg-surface font-body text-on-surface flex flex-col zellige-pattern overflow-hidden relative">
    <!-- Decorative Blur Orbs -->
    <div class="absolute top-0 left-0 w-full h-full pointer-events-none opacity-20">
      <div class="absolute top-[10%] left-[5%] w-64 h-64 bg-primary-container rounded-full blur-[120px]"></div>
      <div class="absolute bottom-[10%] right-[5%] w-80 h-80 bg-secondary-container rounded-full blur-[150px]"></div>
    </div>

    <!-- Background Images (Floating Effect) -->
    <div class="hidden lg:block absolute bottom-12 left-12 w-48 h-48 rounded-xl overflow-hidden border-4 border-surface-container-lowest rotate-[-6deg] shadow-2xl z-0">
      <img alt="Artisanat" class="w-full h-full object-cover" src="https://images.unsplash.com/photo-1590523277543-a94d2e4eb00b?auto=format&fit=crop&w=400&q=80">
    </div>
    <div class="hidden lg:block absolute top-24 right-12 w-56 h-72 rounded-xl overflow-hidden border-4 border-surface-container-lowest rotate-[8deg] shadow-2xl z-0">
      <img alt="Digital Workspace" class="w-full h-full object-cover" src="https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&w=400&q=80">
    </div>

    <main class="flex-grow flex items-center justify-center px-4 py-12 relative z-10">
      <div class="w-full max-w-xl">
        <div class="bg-surface-container-lowest/80 backdrop-blur-xl p-8 md:p-12 rounded-xl shadow-[0px_20px_40px_rgba(30,28,13,0.06)] flex flex-col items-center">
          <div class="mb-10 text-center">
            <div class="flex items-center justify-center gap-2 mb-4">
              <router-link to="/" class="flex items-center gap-2 group">
                <span class="material-symbols-outlined text-primary text-4xl group-hover:rotate-12 transition-transform" style="font-variation-settings: 'FILL' 1;">star</span>
                <h1 class="font-headline font-black italic text-4xl text-primary tracking-tight">MorLancer</h1>
              </router-link>
            </div>
            <p class="font-headline text-on-surface-variant text-lg leading-relaxed">Connectez-vous à votre espace.</p>
          </div>

          <form @submit.prevent="handleLogin" class="w-full space-y-8">
            <div class="space-y-6">
              <div class="relative">
                <label class="block text-xs font-medium text-tertiary mb-1 uppercase tracking-widest" for="email">Adresse Email</label>
                <div class="flex items-center border-b border-outline-variant/40 focus-within:border-primary transition-colors duration-300 py-2">
                  <span class="material-symbols-outlined text-on-surface-variant mr-3">mail</span>
                  <input v-model="form.email" class="bg-transparent border-none focus:ring-0 w-full text-on-surface placeholder:text-on-surface-variant/40 p-0 font-medium" id="email" placeholder="votre@email.com" required type="email"/>
                </div>
              </div>

              <div class="relative">
                <div class="flex justify-between items-end mb-1">
                  <label class="block text-xs font-medium text-tertiary uppercase tracking-widest" for="password">Mot de passe</label>
                  <a class="text-xs text-secondary-fixed-dim hover:text-secondary transition-colors font-bold" href="#">Oublié ?</a>
                </div>
                <div class="flex items-center border-b border-outline-variant/40 focus-within:border-primary transition-colors duration-300 py-2">
                  <span class="material-symbols-outlined text-on-surface-variant mr-3">lock</span>
                  <input v-model="form.password" class="bg-transparent border-none focus:ring-0 w-full text-on-surface placeholder:text-on-surface-variant/40 p-0 font-medium" id="password" placeholder="••••••••" required type="password"/>
                </div>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <input class="w-5 h-5 rounded border-outline-variant/40 bg-surface-container text-primary focus:ring-primary focus:ring-offset-surface cursor-pointer" id="remember" type="checkbox"/>
              <label class="text-sm text-on-surface-variant select-none cursor-pointer font-medium" for="remember">Se souvenir de moi</label>
            </div>

            <button :disabled="loading" class="w-full bg-primary text-on-primary font-bold py-4 rounded-full shadow-lg hover:brightness-110 active:scale-95 transition-all flex items-center justify-center gap-2 group uppercase tracking-widest disabled:opacity-50" type="submit">
              {{ loading ? 'Connexion...' : 'Connexion' }}
              <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform">arrow_forward</span>
            </button>
          </form>

          <div class="mt-10 flex flex-col items-center gap-6 w-full">
            <div class="text-center mt-4">
              <p class="text-on-surface-variant text-sm font-medium">
                Nouveau sur MorLancer ? 
                <router-link to="/register" class="text-secondary font-black ml-1 hover:underline underline-offset-4 decoration-secondary-container">Créer un compte</router-link>
              </p>
            </div>
          </div>
        </div>

        <!-- Trust Badges -->
        <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6 opacity-60">
          <div class="flex flex-col items-center text-center p-4">
            <span class="material-symbols-outlined text-tertiary mb-2 text-3xl">verified_user</span>
            <h3 class="text-xs font-bold uppercase tracking-[0.2em] text-on-surface">Sécurisé</h3>
            <p class="text-[10px] text-on-surface-variant mt-1 font-medium italic">Paiements escrow protégés</p>
          </div>
          <div class="flex flex-col items-center text-center p-4">
            <span class="material-symbols-outlined text-tertiary mb-2 text-3xl">handshake</span>
            <h3 class="text-xs font-bold uppercase tracking-[0.2em] text-on-surface">Contrats auto</h3>
            <p class="text-[10px] text-on-surface-variant mt-1 font-medium italic">Générés à l'accord</p>
          </div>
          <div class="flex flex-col items-center text-center p-4">
            <span class="material-symbols-outlined text-tertiary mb-2 text-3xl">support_agent</span>
            <h3 class="text-xs font-bold uppercase tracking-[0.2em] text-on-surface">Support</h3>
            <p class="text-[10px] text-on-surface-variant mt-1 font-medium italic">Équipe à votre écoute</p>
          </div>
        </div>
      </div>
    </main>

    <footer class="py-8 px-8 flex flex-col md:flex-row justify-between items-center text-[10px] uppercase tracking-[0.3em] text-on-surface-variant/40 gap-4 mt-auto">
      <p>© 2026 MorLancer. Tous droits réservés.</p>
      <div class="flex gap-8 font-bold">
        <a class="hover:text-on-surface transition-colors" href="#">Confidentialité</a>
        <a class="hover:text-on-surface transition-colors" href="#">Conditions</a>
        <a class="hover:text-on-surface transition-colors" href="#">Aide</a>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../api/axios';
import { useAuthStore } from '../../stores/auth';

const router = useRouter();
const authStore = useAuthStore();
const loading = ref(false);
const form = reactive({
  email: '',
  password: ''
});

const handleLogin = async () => {
  loading.value = true;
  try {
    const res = await api.post('/login', form);
    
    // Use the auth store to save user and token
    authStore.setAuth(res.data.user, res.data.access_token);
    
    // Redirection according to role
    const roleName = authStore.userRole;
    if (roleName === 'admin') {
      router.push('/admin/dashboard');
    } else if (roleName === 'client') {
      router.push('/client/dashboard');
    } else {
      router.push('/freelancer/dashboard');
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Identifiants invalides');
  } finally {
    loading.value = false;
  }
};
</script>
