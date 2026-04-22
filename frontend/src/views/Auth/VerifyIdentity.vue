<template>
  <div class="min-h-screen bg-surface font-body text-on-surface flex flex-col zellige-pattern overflow-hidden relative">
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
                <span class="material-symbols-outlined text-primary text-4xl group-hover:rotate-12 transition-transform" style="font-variation-settings: 'FILL' 1;">verified_user</span>
                <h1 class="font-headline font-black italic text-4xl text-primary tracking-tight">Vérification</h1>
              </router-link>
            </div>
            <h2 class="font-headline text-2xl font-bold text-on-surface mb-2">Vérification d'Identité</h2>
            <p class="text-on-surface-variant font-medium">Envoyez une copie de votre CIN ou Passeport pour finaliser votre inscription.</p>
          </div>

          <form @submit.prevent="handleVerification" class="w-full space-y-8">
            <!-- Document Upload -->
            <div class="relative">
              <label class="block text-xs font-bold uppercase tracking-widest text-tertiary mb-4">Document d'Identité (CIN / Passeport)</label>
              
              <!-- Drag & Drop Zone -->
              <div @dragover.prevent="isDragging = true"
                   @dragleave.prevent="isDragging = false"
                   @drop.prevent="handleDrop"
                   :class="isDragging ? 'border-primary bg-primary/5' : 'border-outline-variant/40 hover:border-primary/40'"
                   class="border-2 border-dashed rounded-2xl p-8 text-center transition-all cursor-pointer">
                <span class="material-symbols-outlined text-5xl text-primary/60 mb-4 block">cloud_upload</span>
                <p class="text-sm font-bold text-on-surface mb-2">Glissez-déposez votre document ici</p>
                <p class="text-xs text-on-surface-variant mb-4">ou</p>
                <input type="file" ref="fileInput" @change="handleFileSelect" accept=".pdf,.jpg,.jpeg,.png" class="hidden">
                <button @click.prevent="fileInput?.click()" class="px-6 py-2 bg-primary text-on-primary rounded-full text-sm font-bold hover:brightness-110 transition-all">
                  Parcourir les fichiers
                </button>
                <p class="text-[10px] text-on-surface-variant mt-4">PDF, JPG, PNG • Max 4 MB</p>
              </div>

              <!-- Selected File Display -->
              <div v-if="selectedFile" class="mt-4 p-4 bg-primary/10 rounded-xl border border-primary/20">
                <div class="flex items-center gap-3">
                  <span class="material-symbols-outlined text-primary">check_circle</span>
                  <div class="flex-1">
                    <p class="text-sm font-bold">{{ selectedFile.name }}</p>
                    <p class="text-xs text-on-surface-variant">{{ (selectedFile.size / 1024 / 1024).toFixed(2) }} MB</p>
                  </div>
                  <button @click.prevent="selectedFile = null" class="text-secondary hover:scale-110">
                    <span class="material-symbols-outlined">close</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- Information Fields -->
            <div class="bg-surface-container/30 p-6 rounded-2xl border border-primary/5">
              <h3 class="font-bold text-sm mb-4 uppercase tracking-widest">Informations Complémentaires</h3>
              <div class="space-y-4">
                <div class="relative">
                  <label class="block text-xs font-bold uppercase tracking-widest text-tertiary mb-1">Numéro de CIN / Passeport</label>
                  <div class="flex items-center border-b border-outline-variant/40 focus-within:border-primary transition-colors py-2">
                    <span class="material-symbols-outlined text-on-surface-variant mr-3">badge</span>
                    <input v-model="form.id_number" class="bg-transparent border-none focus:ring-0 w-full text-on-surface placeholder:text-on-surface-variant/40 p-0 font-medium" placeholder="AB123456" type="text" required/>
                  </div>
                </div>
                <div class="relative">
                  <label class="block text-xs font-bold uppercase tracking-widest text-tertiary mb-1">Lieu de Naissance</label>
                  <div class="flex items-center border-b border-outline-variant/40 focus-within:border-primary transition-colors py-2">
                    <span class="material-symbols-outlined text-on-surface-variant mr-3">location_on</span>
                    <input v-model="form.birthplace" class="bg-transparent border-none focus:ring-0 w-full text-on-surface placeholder:text-on-surface-variant/40 p-0 font-medium" placeholder="Casablanca, Maroc" type="text" required/>
                  </div>
                </div>
              </div>
            </div>

            <button :disabled="loading || !selectedFile" class="w-full bg-primary text-on-primary font-black py-4 rounded-full shadow-xl hover:brightness-110 active:scale-95 transition-all flex items-center justify-center gap-2 group uppercase tracking-widest disabled:opacity-50" type="submit">
              {{ loading ? 'Envoi en cours...' : 'Envoyer pour Validation' }}
              <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform">send</span>
            </button>

            <div v-if="errorMessage" class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
              {{ errorMessage }}
            </div>
            <div v-if="successMessage" class="mt-4 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">
              {{ successMessage }}
            </div>
          </form>

          <div class="mt-8 text-center border-t border-outline-variant/10 pt-8 w-full">
            <p class="text-on-surface-variant text-sm font-medium">
              <router-link to="/login" class="text-primary font-black hover:underline underline-offset-4">Retourner à la connexion</router-link>
            </p>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const route = useRoute()
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const isDragging = ref(false)
const selectedFile = ref(null)
const fileInput = ref(null)

const form = reactive({
  id_number: '',
  birthplace: ''
})

const handleFileSelect = (event) => {
  const file = event.target.files?.[0]
  if (file) {
    if (file.size > 4 * 1024 * 1024) {
      errorMessage.value = 'Le fichier dépasse 4 MB'
      return
    }
    selectedFile.value = file
    errorMessage.value = ''
  }
}

const handleDrop = (event) => {
  isDragging.value = false
  const file = event.dataTransfer?.files?.[0]
  if (file) {
    if (file.size > 4 * 1024 * 1024) {
      errorMessage.value = 'Le fichier dépasse 4 MB'
      return
    }
    selectedFile.value = file
    errorMessage.value = ''
  }
}

const handleVerification = async () => {
  if (!selectedFile.value) {
    errorMessage.value = 'Veuillez sélectionner un document'
    return
  }

  loading.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const formData = new FormData()
    formData.append('document', selectedFile.value)
    formData.append('id_number', form.id_number)
    formData.append('birthplace', form.birthplace)

    const token = localStorage.getItem('token')
    const response = await axios.post('http://localhost:8000/api/user/verify', formData, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'multipart/form-data'
      }
    })

    successMessage.value = 'Document envoyé avec succès ! En attente de validation par un administrateur.'
    setTimeout(() => {
      router.push('/login')
    }, 2000)
  } catch (err) {
    const response = err.response?.data
    errorMessage.value = response?.message || 'Erreur lors de l\'envoi du document'
  } finally {
    loading.value = false
  }
}
</script>
