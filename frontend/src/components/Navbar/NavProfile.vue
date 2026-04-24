<template>
  <div class="relative ml-2" ref="ref_">
    <button @click="open=!open" class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-bold text-xs shadow-lg shadow-primary/20 hover:scale-110 transition-transform" :title="store.userName">
      {{ store.userInitials }}
    </button>
    <Transition name="dd">
      <div v-if="open" class="absolute top-12 right-0 w-56 bg-white rounded-2xl shadow-2xl border border-primary/5 z-50 overflow-hidden">
        <div class="px-4 py-4 border-b border-primary/5 bg-primary/5 flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-bold text-xs flex-shrink-0">{{ store.userInitials }}</div>
          <div class="min-w-0">
            <p class="font-bold text-sm text-on-surface truncate">{{ store.userName }}</p>
            <p class="text-[10px] text-on-surface-variant">Freelancer</p>
          </div>
        </div>
        <div class="py-2">
          <button @click="go('profile')" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-on-surface hover:bg-primary/10 transition-colors text-left">
            <span class="material-symbols-outlined text-base text-on-surface-variant">person</span>Profil
          </button>
          <button @click="go('messages')" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-on-surface hover:bg-primary/10 transition-colors text-left">
            <span class="material-symbols-outlined text-base text-on-surface-variant">chat_bubble</span>Messages
          </button>
          <div class="border-t border-primary/5 my-1"></div>
          <button @click="logout" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-secondary hover:bg-secondary/10 transition-colors text-left">
            <span class="material-symbols-outlined text-base">logout</span>Se déconnecter
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>
<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useFreelancerStore } from '@/stores/freelancer'
const store = useFreelancerStore()
const open = ref(false), ref_ = ref(null)
const go = (tab) => {
  open.value = false
  window.dispatchEvent(new CustomEvent('freelancer-tab', { detail: tab }))
}
const logout = () => { localStorage.clear(); window.location.href = '/login' }
const out = (e) => { if (ref_.value && !ref_.value.contains(e.target)) open.value = false }
onMounted(() => document.addEventListener('click', out))
onUnmounted(() => document.removeEventListener('click', out))
</script>
<style scoped>
.dd-enter-active,.dd-leave-active{transition:opacity .15s ease,transform .15s ease}
.dd-enter-from,.dd-leave-to{opacity:0;transform:translateY(-6px)}
</style>
