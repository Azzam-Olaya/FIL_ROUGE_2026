<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-xl shadow-md">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">Rejoindre MorLancer</h2>
      </div>

      <div v-if="step === 1" class="space-y-6">
        <p class="text-center text-gray-600">Choisissez votre profil (Client ou Freelance)</p>
        <div class="grid grid-cols-2 gap-4">
          <button @click="selectRole(2)" :class="form.role_id === 2 ? 'ring-2 ring-indigo-600 border-indigo-600' : 'border-gray-200'" class="p-4 border rounded-lg hover:border-indigo-400 transition flex flex-col items-center">
            <i class="fas fa-user-tie text-2xl mb-2 text-indigo-600"></i>
            <span>Client</span>
          </button>
          <button @click="selectRole(3)" :class="form.role_id === 3 ? 'ring-2 ring-indigo-600 border-indigo-600' : 'border-gray-200'" class="p-4 border rounded-lg hover:border-indigo-400 transition flex flex-col items-center">
            <i class="fas fa-laptop-code text-2xl mb-2 text-indigo-600"></i>
            <span>Freelancer</span>
          </button>
        </div>
        <button @click="step = 2" :disabled="!form.role_id" class="w-full py-2 bg-indigo-600 text-white rounded-md disabled:opacity-50">Continuer</button>
      </div>

      <form v-else class="mt-8 space-y-4" @submit.prevent="handleRegister">
        <div class="space-y-1">
          <label class="text-sm font-medium text-gray-700">Nom complet</label>
          <input v-model="form.name" type="text" required class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>
        <div class="space-y-1">
          <label class="text-sm font-medium text-gray-700">Email</label>
          <input v-model="form.email" type="email" required class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>
        <div class="space-y-1">
          <label class="text-sm font-medium text-gray-700">Mot de passe</label>
          <input v-model="form.password" type="password" required class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>
        <div class="space-y-1">
          <label class="text-sm font-medium text-gray-700">Confirmer le mot de passe</label>
          <input v-model="form.password_confirmation" type="password" required class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>

        <div class="flex items-center space-x-4 pt-4">
           <button @click="step = 1" type="button" class="flex-1 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Retour</button>
           <button type="submit" class="flex-1 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">S'inscrire</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const step = ref(1);

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role_id: null,
});

const selectRole = (id) => {
  form.role_id = id;
};

const handleRegister = () => {
  console.log('Register attempt', form);
  // Simulation success
  router.push('/login');
};
</script>
