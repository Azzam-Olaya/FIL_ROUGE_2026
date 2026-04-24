<template>
  <header class="bg-surface/80 backdrop-blur-md flex justify-between items-center w-full px-8 py-4 fixed top-0 left-0 right-0 z-50 shadow-[0px_20px_40px_rgba(30,28,13,0.06)] border-b border-primary/5">

    <!-- Logo MorLancer -->
    <router-link to="/" class="flex items-center gap-2 group flex-shrink-0">
      <span class="material-symbols-outlined text-primary text-3xl font-bold group-hover:scale-110 transition-transform">
        auto_awesome
      </span>
      <div class="flex flex-col">
        <span class="font-headline font-black italic text-2xl text-primary tracking-tight">MorLancer</span>
        <span class="text-[10px] text-primary tracking-[0.2em] font-bold uppercase">Talents & Opportunités</span>
      </div>
    </router-link>

    <!-- Badge plateforme active -->
    <div class="bg-white/80 backdrop-blur-md px-5 py-2 rounded-full flex items-center gap-2 border border-primary/5 shadow-sm">
      <span class="w-2 h-2 bg-primary rounded-full animate-pulse"></span>
      <span class="text-xs font-bold text-primary italic">Plateforme Active</span>
    </div>

    <!-- Déconnexion -->
    <button
      @click="logout"
      class="flex items-center gap-2 bg-secondary text-white px-6 py-2.5 rounded-full font-bold text-sm hover:brightness-110 active:scale-95 transition-all shadow-lg shadow-secondary/20"
    >
      <span class="material-symbols-outlined text-base">logout</span>
      Déconnexion
    </button>

  </header>
</template>

<script setup>
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

const logout = async () => {
  try { await axios.post('http://localhost:8000/api/logout') } catch {}
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  router.push('/login')
}
</script>
