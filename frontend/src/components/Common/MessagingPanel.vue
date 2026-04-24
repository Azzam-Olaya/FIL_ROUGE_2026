<template>
  <div class="flex overflow-hidden rounded-2xl border border-primary/5 shadow-sm bg-white/40" style="height:calc(100vh - 9rem)">
    <aside class="w-80 flex-shrink-0 bg-surface-container-lowest/80 flex flex-col border-r border-primary/5" :class="{'hidden md:flex':selectedUser}">
      <div class="p-5 border-b border-primary/5 flex items-center justify-between">
        <h2 class="font-headline text-xl font-bold text-primary">Messages</h2>
        <div v-if="loadingC" class="w-4 h-4 border-2 border-primary/20 border-t-primary rounded-full animate-spin"></div>
      </div>
      <div class="p-3 border-b border-primary/5 relative">
        <div class="flex items-center gap-2 bg-surface-container rounded-xl px-3 py-2">
          <span class="material-symbols-outlined text-sm text-on-surface-variant">person_search</span>
          <input v-model="sq" @input="search" placeholder="Nouvelle conversation..." class="bg-transparent border-none focus:ring-0 text-xs w-full outline-none" />
          <button v-if="sq" @click="sq='';sr=[]"><span class="material-symbols-outlined text-xs text-on-surface-variant">close</span></button>
        </div>
        <div v-if="sr.length" class="absolute left-3 right-3 top-14 bg-white rounded-xl shadow-xl border border-primary/5 z-10 overflow-hidden">
          <div v-for="u in sr" :key="u.id" @click="startConv(u)" class="flex items-center gap-3 px-4 py-3 hover:bg-primary/5 cursor-pointer transition-colors">
            <div class="w-9 h-9 rounded-full bg-primary flex items-center justify-center text-white text-xs font-bold flex-shrink-0">{{ ini(u.name) }}</div>
            <div><p class="text-sm font-bold text-on-surface">{{ u.name }}</p><p class="text-[10px] text-on-surface-variant capitalize">{{ u.role?.name||'Utilisateur' }}</p></div>
          </div>
        </div>
      </div>
      <div class="flex-1 overflow-y-auto divide-y divide-primary/5">
        <div v-if="!loadingC && !convs.length" class="text-center py-12 opacity-40">
          <span class="material-symbols-outlined text-4xl block mb-2">forum</span>
          <p class="text-xs font-medium">Aucune conversation</p>
        </div>
        <div v-for="c in convs" :key="c.user_id" @click="openConv(c)"
          :class="sel?.user_id===c.user_id?'bg-primary/5 border-l-4 border-primary':'hover:bg-surface-container-high'"
          class="p-4 cursor-pointer transition-all">
          <div class="flex gap-3 items-center">
            <div class="w-11 h-11 rounded-full bg-primary flex items-center justify-center text-white font-bold text-xs flex-shrink-0">{{ ini(c.name) }}</div>
            <div class="flex-1 min-w-0">
              <div class="flex justify-between items-baseline mb-0.5">
                <h3 class="font-bold text-sm truncate text-on-surface">{{ c.name }}</h3>
                <span class="text-[10px] text-on-surface-variant opacity-60 flex-shrink-0 ml-1">{{ c.time }}</span>
              </div>
              <p class="text-xs text-on-surface-variant truncate" :class="c.unread?'font-bold text-primary':'font-medium'">{{ c.lastMessage }}</p>
            </div>
            <div v-if="c.unread" class="w-2.5 h-2.5 bg-secondary rounded-full flex-shrink-0"></div>
          </div>
        </div>
      </div>
    </aside>

    <section v-if="sel" class="flex-1 flex flex-col min-w-0">
      <div class="p-4 flex items-center gap-3 bg-surface-container-low/50 border-b border-primary/5 flex-shrink-0">
        <button @click="sel=null" class="md:hidden p-2 rounded-full hover:bg-primary/10"><span class="material-symbols-outlined">arrow_back</span></button>
        <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-bold text-xs flex-shrink-0">{{ ini(sel.name) }}</div>
        <div><h2 class="font-bold text-sm text-on-surface">{{ sel.name }}</h2><p class="text-[10px] text-on-surface-variant capitalize">{{ sel.role||'Utilisateur' }}</p></div>
      </div>
      <div ref="msgsEl" class="flex-1 overflow-y-auto p-6 flex flex-col gap-3">
        <div v-if="loadingM" class="flex justify-center py-8"><div class="w-6 h-6 border-2 border-primary/20 border-t-primary rounded-full animate-spin"></div></div>
        <div v-else-if="!msgs.length" class="flex flex-col items-center justify-center h-full opacity-30 text-center">
          <span class="material-symbols-outlined text-5xl mb-3 text-primary">chat_bubble_outline</span>
          <p class="text-sm font-medium">Commencez la conversation</p>
        </div>
        <template v-else>
          <div v-for="m in msgs" :key="m.id" :class="m.sender==='me'?'items-end':'items-start'" class="flex flex-col gap-1 max-w-[75%]" :style="m.sender==='me'?'align-self:flex-end':'align-self:flex-start'">
            <div :class="m.sender==='me'?'bg-primary text-white rounded-tl-2xl rounded-bl-2xl rounded-br-sm':'bg-surface-container-high text-on-surface rounded-tr-2xl rounded-br-2xl rounded-bl-sm'" class="px-4 py-2.5 text-sm leading-relaxed shadow-sm">{{ m.text }}</div>
            <p class="text-[10px] text-on-surface-variant px-1">{{ m.time }}</p>
          </div>
        </template>
      </div>
      <div class="p-4 border-t border-primary/5 flex-shrink-0">
        <div class="bg-white rounded-2xl p-2 shadow-sm flex items-end gap-2 ring-1 ring-primary/5 focus-within:ring-2 focus-within:ring-primary/20 transition-all">
          <textarea v-model="nm" @keydown.enter.exact.prevent="send" class="flex-1 bg-transparent border-none focus:ring-0 text-sm py-2 px-3 resize-none max-h-28 placeholder:text-on-surface-variant/40 font-medium outline-none" placeholder="Écrivez votre message... (Entrée pour envoyer)" rows="1"></textarea>
          <button @click="send" :disabled="!nm.trim()||sending" class="w-10 h-10 rounded-xl bg-primary text-white flex items-center justify-center shadow-lg shadow-primary/20 hover:scale-105 active:scale-95 transition-all flex-shrink-0 disabled:opacity-40">
            <span v-if="sending" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
            <span v-else class="material-symbols-outlined text-sm" style="font-variation-settings:'FILL' 1">send</span>
          </button>
        </div>
      </div>
    </section>

    <section v-else class="flex-1 hidden md:flex flex-col items-center justify-center p-12 opacity-30 text-center">
      <span class="material-symbols-outlined text-8xl mb-4 text-primary" style="font-variation-settings:'wght' 100">forum</span>
      <h3 class="font-headline text-xl font-bold">Vos messages</h3>
      <p class="text-sm mt-2">Sélectionnez une conversation ou recherchez un utilisateur</p>
    </section>
  </div>
</template>
<script setup>
import { ref, nextTick, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
const B = 'http://localhost:8000/api'
const h = () => ({ Authorization: `Bearer ${localStorage.getItem('token')}` })
const convs=ref([]),msgs=ref([]),sel=ref(null),nm=ref(''),sq=ref(''),sr=ref([])
const loadingC=ref(false),loadingM=ref(false),sending=ref(false),msgsEl=ref(null)
let pt=null,st=null
const loadConvs = async () => { loadingC.value=true; try { const r=await axios.get(`${B}/conversations`,{headers:h()}); convs.value=r.data } catch { convs.value=[] } finally { loadingC.value=false } }
const openConv = async (c) => { sel.value=c; sq.value=''; sr.value=[]; loadingM.value=true; try { const r=await axios.get(`${B}/conversations/${c.user_id}`,{headers:h()}); msgs.value=r.data; await sb() } catch { msgs.value=[] } finally { loadingM.value=false } }
const startConv = (u) => { sel.value={user_id:u.id,name:u.name,role:u.role?.name}; sq.value=''; sr.value=[]; msgs.value=[] }
const send = async () => {
  if (!nm.value.trim()||!sel.value||sending.value) return
  const t=nm.value.trim(); nm.value=''; sending.value=true
  const tid=Date.now(); msgs.value.push({id:tid,sender:'me',text:t,time:new Date().toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'})})
  await sb()
  try {
    const r=await axios.post(`${B}/messages`,{receiver_id:sel.value.user_id,content:t},{headers:h()})
    const i=msgs.value.findIndex(m=>m.id===tid)
    if(i!==-1) msgs.value[i]=r.data
    // Force reload conversations immediately to show new conversation
    await loadConvs()
  } catch {} finally { sending.value=false }
}
const search = () => {
  clearTimeout(st)
  if (sq.value.trim().length<2) { sr.value=[]; return }
  st=setTimeout(async()=>{ try { const r=await axios.get(`${B}/users/search?q=${encodeURIComponent(sq.value)}`,{headers:h()}); sr.value=r.data } catch { sr.value=[] } },300)
}
const poll = async () => {
  if (sel.value) { try { const r=await axios.get(`${B}/conversations/${sel.value.user_id}`,{headers:h()}); if(r.data.length!==msgs.value.length){msgs.value=r.data;await sb()} } catch {} }
  loadConvs()
}
const sb = async () => { await nextTick(); if(msgsEl.value) msgsEl.value.scrollTop=msgsEl.value.scrollHeight }
const ini = (n) => n?.split(' ').map(x=>x[0]).join('').toUpperCase().slice(0,2)||'U'

// Auto-open conversation when triggered from contact button
const handleOpenConversation = async (e) => {
  const { userId, name } = e.detail
  if (!userId) return
  // Reload conversations first
  await loadConvs()
  const existing = convs.value.find(c => c.user_id === userId)
  if (existing) {
    await openConv(existing)
  } else {
    // Add optimistic conversation entry to list
    const newConv = { user_id: userId, name, lastMessage: '...', time: '', unread: false }
    convs.value.unshift(newConv)
    sel.value = { user_id: userId, name, role: 'Client' }
    msgs.value = []
    // Try to load any existing messages
    try {
      const r = await axios.get(`${B}/conversations/${userId}`, { headers: h() })
      msgs.value = r.data
      await sb()
    } catch {}
  }
}

onMounted(() => { loadConvs(); pt=setInterval(poll, 3000); window.addEventListener('open-conversation', handleOpenConversation) })
onUnmounted(() => { clearInterval(pt); window.removeEventListener('open-conversation', handleOpenConversation) })
</script>
