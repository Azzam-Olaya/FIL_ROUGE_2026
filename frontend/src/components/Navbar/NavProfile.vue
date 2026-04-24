<template>
  <!--
    NavProfile — Composant profil de la navbar
    ────────────────────────────────────────────
    Rôle : Afficher l'avatar du freelancer et donner accès
           aux actions de compte (profil, paramètres, déconnexion).

    Synchronisation :
      - Lit userName et userInitials depuis le store (session)
      - Isolation totale : aucune dépendance aux autres composants navbar
      - La déconnexion vide le localStorage et redirige vers /login
  -->
  <div class="relative ml-2">

    <!-- Avatar bouton -->
    <button
      @click="goToProfile"
      class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-bold text-xs shadow-lg shadow-primary/20 hover:scale-110 transition-transform ring-2 ring-transparent hover:ring-primary/30"
      :title="store.userName"
    >
      {{ store.userInitials }}
    </button>

    <!-- Dropdown menu profil -->
    <Transition name="dropdown">
      <div
        v-if="open"
        v-click-outside="close"
        class="absolute top-12 right-0 w-56 bg-white rounded-2xl shadow-2xl border border-primary/5 z-50 overflow-hidden"
      >
        <!-- Info utilisateur -->
        <div class="px-4 py-4 border-b border-primary/5 bg-primary/5">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
              {{ store.userInitials }}
            </div>
            <div class="min-w-0">
              <p class="font-bold text-sm text-on-surface truncate">{{ store.userName }}</p>
              <p class="text-[10px] text-on-surface-variant">Freelancer</p>
            </div>
          </div>
        </div>

        <!-- Actions du menu -->
        <div class="py-2">
          <button
            @click="navigate('/profile')"
            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-on-surface hover:bg-primary/10 transition-colors text-left"
          >
            <span class="material-symbols-outlined text-base text-on-surface-variant">person</span>
            Mon Profil
          </button>

          <button
            @click="navigate('/freelancer/dashboard')"
            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-on-surface hover:bg-primary/10 transition-colors text-left"
          >
            <span class="material-symbols-outlined text-base text-on-surface-variant">dashboard</span>
            Dashboard
          </button>

          <button
            @click="navigate('/messaging')"
            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-on-surface hover:bg-primary/10 transition-colors text-left"
          >
            <span class="material-symbols-outlined text-base text-on-surface-variant">chat_bubble</span>
            Messages
          </button>

          <div class="border-t border-primary/5 my-1"></div>

          <!-- Déconnexion -->
          <button
            @click="logout"
            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-secondary hover:bg-secondary/10 transition-colors text-left"
          >
            <span class="material-symbols-outlined text-base">logout</span>
            Déconnexion
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useFreelancerStore } from '@/stores/freelancer'

const store  = useFreelancerStore()
const router = useRouter()
const open   = ref(false)

const toggle = () => { open.value = !open.value }
const close  = () => { open.value = false }

/** Clic sur l'avatar → navigue vers l'onglet profil du dashboard */
const goToProfile = () => {
  const role = JSON.parse(localStorage.getItem('user') || '{}')?.role_name
  if (role === 'client') {
    router.push({ path: '/client/dashboard', query: { tab: 'profile' } })
  } else {
    router.push({ path: '/freelancer/dashboard', query: { tab: 'profile' } })
  }
}




/** Navigue vers une route et ferme le menu */
const navigate = (path) => {
  router.push(path)
  close()
}

/**
 * Déconnexion :
 *  - Vide le localStorage (token + user)
 *  - Redirige vers la page de connexion
 */
const logout = () => {
  localStorage.clear()
  window.location.href = '/login'
}

/** Directive v-click-outside pour fermer le menu au clic extérieur */
const vClickOutside = {
  mounted(el, binding) {
    el._clickOutside = (e) => { if (!el.contains(e.target)) binding.value() }
    document.addEventListener('click', el._clickOutside)
  },
  unmounted(el) {
    document.removeEventListener('click', el._clickOutside)
  },
}
</script>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>
