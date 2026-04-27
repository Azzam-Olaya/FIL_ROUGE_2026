<template>
  <div class="min-h-screen bg-surface font-body text-on-surface flex flex-col zellige-pattern overflow-hidden relative">
    <!-- Decorative Blur Orbs -->
    <div class="absolute top-0 left-0 w-full h-full pointer-events-none opacity-20">
      <div class="absolute top-[15%] right-[5%] w-72 h-72 bg-primary-container rounded-full blur-[120px]"></div>
      <div class="absolute bottom-[15%] left-[5%] w-96 h-96 bg-secondary-container rounded-full blur-[150px]"></div>
    </div>

    <main class="flex-grow flex items-center justify-center px-4 py-8 relative z-10">
      <div class="w-full max-w-2xl">
        <div class="bg-surface-container-lowest/80 backdrop-blur-xl p-8 md:p-12 rounded-xl shadow-[0px_20px_40px_rgba(30,28,13,0.06)] flex flex-col items-center border border-white/20">
          <div class="mb-8 text-center">
            <div class="flex items-center justify-center gap-2 mb-4">
              <router-link to="/" class="flex items-center gap-2 group">
                <span class="material-symbols-outlined text-primary text-4xl group-hover:rotate-12 transition-transform" style="font-variation-settings: 'FILL' 1;">auto_awesome</span>
                <h1 class="font-headline font-black italic text-4xl text-primary tracking-tight">MorLancer</h1>
              </router-link>
            </div>
            <h2 class="font-headline text-2xl font-bold text-on-surface mb-2">Créer mon compte</h2>
            <p class="text-on-surface-variant font-medium">Choisissez votre rôle et rejoignez MorLancer.</p>
          </div>

          <form @submit.prevent="handleRegister" class="w-full space-y-8">
            <!-- Role Selection -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div @click="form.role_name = 'client'" 
                   :class="form.role_name === 'client' ? 'border-primary bg-primary/5 ring-2 ring-primary/20' : 'border-outline-variant/20 bg-surface-container/30'" 
                   class="p-6 rounded-2xl border-2 cursor-pointer transition-all hover:border-primary/40 text-center flex flex-col items-center group">
                <span class="material-symbols-outlined text-4xl mb-2 transition-transform group-hover:scale-110" :class="form.role_name === 'client' ? 'text-primary' : 'text-on-surface-variant'">business_center</span>
                <span class="font-headline font-bold text-lg" :class="form.role_name === 'client' ? 'text-primary' : 'text-on-surface'">Client</span>
                <p class="text-[10px] uppercase font-bold tracking-widest mt-1 opacity-70">Publier des missions</p>
              </div>
              <div @click="form.role_name = 'freelancer'" 
                   :class="form.role_name === 'freelancer' ? 'border-secondary bg-secondary/5 ring-2 ring-secondary/20' : 'border-outline-variant/20 bg-surface-container/30'" 
                   class="p-6 rounded-2xl border-2 cursor-pointer transition-all hover:border-secondary/40 text-center flex flex-col items-center group">
                <span class="material-symbols-outlined text-4xl mb-2 transition-transform group-hover:scale-110" :class="form.role_name === 'freelancer' ? 'text-secondary' : 'text-on-surface-variant'">work</span>
                <span class="font-headline font-bold text-lg" :class="form.role_name === 'freelancer' ? 'text-secondary' : 'text-on-surface'">Freelancer</span>
                <p class="text-[10px] uppercase font-bold tracking-widest mt-1 opacity-70">Proposer mes services</p>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <div class="relative">
                <label class="block text-xs font-bold uppercase tracking-widest text-tertiary mb-1" for="name">Prénom & Nom</label>
                <div class="flex items-center border-b border-outline-variant/40 focus-within:border-primary transition-colors duration-300 py-2">
                  <span class="material-symbols-outlined text-on-surface-variant mr-3">person</span>
                  <input v-model="form.name" class="bg-transparent border-none focus:ring-0 w-full text-on-surface placeholder:text-on-surface-variant/40 p-0 font-medium" id="name" placeholder="MorLancer" required type="text"/>
                </div>
              </div>
              <div class="relative">
                <label class="block text-xs font-bold uppercase tracking-widest text-tertiary mb-1" for="email">Email PRO</label>
                <div class="flex items-center border-b border-outline-variant/40 focus-within:border-primary transition-colors duration-300 py-2">
                  <span class="material-symbols-outlined text-on-surface-variant mr-3">mail</span>
                  <input v-model="form.email" class="bg-transparent border-none focus:ring-0 w-full text-on-surface placeholder:text-on-surface-variant/40 p-0 font-medium" id="email" placeholder="Morlancer@gmail.com" required type="email"/>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <div class="relative">
                <label class="block text-xs font-bold uppercase tracking-widest text-tertiary mb-1" for="password">Mot de passe</label>
                <div class="flex items-center border-b focus-within:border-primary transition-colors duration-300 py-2"
                     :class="passwordMismatch ? 'border-red-400' : 'border-outline-variant/40'">
                  <span class="material-symbols-outlined text-on-surface-variant mr-3">lock</span>
                  <input v-model="form.password" class="bg-transparent border-none focus:ring-0 w-full text-on-surface placeholder:text-on-surface-variant/40 p-0 font-medium" id="password" placeholder="••••••••" required type="password" minlength="8"/>
                </div>
                <p class="text-[10px] text-on-surface-variant mt-1 italic">Minimum 8 caractères.</p>
              </div>
              <div class="relative">
                <label class="block text-xs font-bold uppercase tracking-widest text-tertiary mb-1" for="password_confirmation">Confirmer le mot de passe</label>
                <div class="flex items-center border-b focus-within:border-primary transition-colors duration-300 py-2"
                     :class="passwordMismatch ? 'border-red-400' : 'border-outline-variant/40'">
                  <span class="material-symbols-outlined mr-3" :class="passwordMismatch ? 'text-red-400' : 'text-on-surface-variant'">lock_check</span>
                  <input v-model="form.password_confirmation" class="bg-transparent border-none focus:ring-0 w-full text-on-surface placeholder:text-on-surface-variant/40 p-0 font-medium" id="password_confirmation" placeholder="••••••••" required type="password"/>
                </div>
                <p v-if="passwordMismatch" class="text-[10px] text-red-500 mt-1 font-bold">Les mots de passe ne correspondent pas.</p>
                <p v-else-if="form.password_confirmation && !passwordMismatch" class="text-[10px] text-green-600 mt-1 font-bold">✓ Mots de passe identiques.</p>
              </div>
            </div>

            <button :disabled="loading" class="w-full bg-primary text-on-primary font-black py-4 rounded-full shadow-xl hover:brightness-110 active:scale-95 transition-all flex items-center justify-center gap-2 group uppercase tracking-widest disabled:opacity-50" type="submit">
              {{ loading ? 'Création...' : 'Créer mon compte' }}
              <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform">how_to_reg</span>
            </button>

            <div v-if="errorMessage" class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
              {{ errorMessage }}
            </div>
            <ul v-if="Object.keys(formErrors).length" class="mt-4 space-y-2 text-sm text-red-700">
              <li v-for="(messages, field) in formErrors" :key="field">{{ messages[0] }}</li>
            </ul>
          </form>

          <div class="mt-8 text-center border-t border-outline-variant/10 pt-8 w-full">
            <p class="text-on-surface-variant text-sm font-medium">
              Déjà membre de l'élite ? 
              <router-link to="/login" class="text-primary font-black ml-1 hover:underline underline-offset-4">Se connecter</router-link>
            </p>
          </div>
        </div>
      </div>
    </main>

    <footer class="py-6 px-8 flex flex-col md:flex-row justify-between items-center text-[10px] uppercase tracking-[0.2em] text-on-surface-variant/40 gap-4 mt-auto">
      <p>© 2026 MorLancer. Tous droits réservés.</p>
    </footer>
  </div>
</template>

<script setup>
import { reactive, ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/api/axios';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();
const loading = ref(false);
const errorMessage = ref('');
const formErrors = ref({});
const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role_name: 'freelancer'
});

const passwordMismatch = computed(() =>
  form.password_confirmation.length > 0 && form.password !== form.password_confirmation
);

const handleRegister = async () => {
  errorMessage.value = '';
  formErrors.value = {};

  if (form.password !== form.password_confirmation) {
    errorMessage.value = 'Les mots de passe ne correspondent pas.';
    return;
  }

  loading.value = true;
  try {
    const res = await api.post('/register', form);
    authStore.setAuth(res.data.user, res.data.access_token);
    router.push('/verify-identity');
  } catch (err) {
    const response = err.response?.data;
    if (response?.errors) {
      formErrors.value = response.errors;
      errorMessage.value = response.message || 'La validation a échoué.';
    } else {
      errorMessage.value = response?.message || 'Erreur lors de l’inscription.';
    }
  } finally {
    loading.value = false;
  }
};
</script>
