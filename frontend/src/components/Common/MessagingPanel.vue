<template>
  <div class="flex overflow-hidden rounded-2xl border border-primary/5 shadow-sm bg-white/40" style="height: calc(100vh - 9rem)">

    <!-- Conversations list -->
    <aside class="w-80 flex-shrink-0 bg-surface-container-lowest/80 flex flex-col border-r border-primary/5" :class="{ 'hidden md:flex': selectedUser }">
      <div class="p-5 border-b border-primary/5 flex items-center justify-between">
        <h2 class="font-headline text-xl font-bold text-primary">Messages</h2>
        <div v-if="loadingConvs" class="w-4 h-4 border-2 border-primary/20 border-t-primary rounded-full animate-spin"></div>
      </div>

      <div class="p-3 border-b border-primary/5 relative">
        <div class="flex items-center gap-2 bg-surface-container rounded-xl px-3 py-2">
          <span class="material-symbols-outlined text-sm text-on-surface-variant">person_search</span>
          <input v-model="searchQuery" @input="onSearch" placeholder="Nouvelle conversation..."
            class="bg-transparent border-none focus:ring-0 text-xs w-full outline-none" />
          <button v-if="searchQuery" @click="searchQuery = ''; searchResults = []">
            <span class="material-symbols-outlined text-xs text-on-surface-variant">close</span>
          </button>
        </div>
        <div v-if="searchResults.length" class="absolute left-3 right-3 top-14 bg-white rounded-xl shadow-xl border border-primary/5 z-10 overflow-hidden">
          <div v-for="u in searchResults" :key="u.id" @click="startConversation(u)"
            class="flex items-center gap-3 px-4 py-3 hover:bg-primary/5 cursor-pointer transition-colors">
            <div class="w-9 h-9 rounded-full bg-primary flex items-center justify-center text-white text-xs font-bold flex-shrink-0">{{ initials(u.name) }}</div>
            <div>
              <p class="text-sm font-bold text-on-surface">{{ u.name }}</p>
              <p class="text-[10px] text-on-surface-variant capitalize">{{ u.role?.name || 'Utilisateur' }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="flex-1 overflow-y-auto divide-y divide-primary/5">
        <div v-if="!loadingConvs && conversations.length === 0" class="text-center py-12 opacity-40">
          <span class="material-symbols-outlined text-4xl block mb-2">forum</span>
          <p class="text-xs font-medium">Aucune conversation</p>
        </div>
        <div v-for="conv in conversations" :key="conv.user_id" @click="openConversation(conv)"
          :class="selectedUser?.user_id === conv.user_id ? 'bg-primary/5 border-l-4 border-primary' : 'hover:bg-surface-container-high'"
          class="p-4 cursor-pointer transition-all">
          <div class="flex gap-3 items-center">
            <div class="w-11 h-11 rounded-full bg-primary flex items-center justify-center text-white font-bold text-xs flex-shrink-0">{{ initials(conv.name) }}</div>
            <div class="flex-1 min-w-0">
              <div class="flex justify-between items-baseline mb-0.5">
                <h3 class="font-bold text-sm truncate text-on-surface">{{ conv.name }}</h3>
                <span class="text-[10px] text-on-surface-variant opacity-60 flex-shrink-0 ml-1">{{ conv.time }}</span>
              </div>
              <p class="text-xs text-on-surface-variant truncate" :class="conv.unread ? 'font-bold text-primary' : 'font-medium'">{{ conv.lastMessage }}</p>
            </div>
            <div v-if="conv.unread" class="w-2.5 h-2.5 bg-secondary rounded-full flex-shrink-0"></div>
          </div>
        </div>
      </div>
    </aside>

    <!-- Chat window -->
    <section v-if="selectedUser" class="flex-1 flex flex-col min-w-0">
      <div class="p-4 flex items-center gap-3 bg-surface-container-low/50 border-b border-primary/5 flex-shrink-0">
        <button @click="selectedUser = null" class="md:hidden p-2 rounded-full hover:bg-primary/10">
          <span class="material-symbols-outlined">arrow_back</span>
        </button>
        <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-bold text-xs flex-shrink-0">{{ initials(selectedUser.name) }}</div>
        <div>
          <h2 class="font-bold text-sm text-on-surface">{{ selectedUser.name }}</h2>
          <p class="text-[10px] text-on-surface-variant capitalize">{{ selectedUser.role || 'Utilisateur' }}</p>
        </div>
      </div>

      <div ref="messagesEl" class="flex-1 overflow-y-auto p-6 flex flex-col gap-3">
        <div v-if="loadingMsgs" class="flex justify-center py-8">
          <div class="w-6 h-6 border-2 border-primary/20 border-t-primary rounded-full animate-spin"></div>
        </div>
        <div v-else-if="messages.length === 0" class="flex flex-col items-center justify-center h-full opacity-30 text-center">
          <span class="material-symbols-outlined text-5xl mb-3 text-primary">chat_bubble_outline</span>
          <p class="text-sm font-medium">Commencez la conversation</p>
        </div>
        <template v-else>
          <div v-for="msg in messages" :key="msg.id"
            :class="msg.sender === 'me' ? 'items-end' : 'items-start'"
            class="flex flex-col gap-1 max-w-[75%]"
            :style="msg.sender === 'me' ? 'align-self: flex-end' : 'align-self: flex-start'">
            <div :class="msg.sender === 'me' ? 'bg-primary text-white rounded-tl-2xl rounded-bl-2xl rounded-br-sm' : 'bg-surface-container-high text-on-surface rounded-tr-2xl rounded-br-2xl rounded-bl-sm'"
              class="px-4 py-2.5 text-sm leading-relaxed shadow-sm">{{ msg.text }}</div>
            <p class="text-[10px] text-on-surface-variant px-1">{{ msg.time }}</p>
          </div>
        </template>
      </div>

      <div class="p-4 border-t border-primary/5 flex-shrink-0">
        <div class="bg-white rounded-2xl p-2 shadow-sm flex items-end gap-2 ring-1 ring-primary/5 focus-within:ring-2 focus-within:ring-primary/20 transition-all">
          <textarea v-model="newMessage" @keydown.enter.exact.prevent="sendMessage"
            class="flex-1 bg-transparent border-none focus:ring-0 text-sm py-2 px-3 resize-none max-h-28 placeholder:text-on-surface-variant/40 font-medium outline-none"
            placeholder="Écrivez votre message... (Entrée pour envoyer)" rows="1"></textarea>
          <button @click="sendMessage" :disabled="!newMessage.trim() || sending"
            class="w-10 h-10 rounded-xl bg-primary text-white flex items-center justify-center shadow-lg shadow-primary/20 hover:scale-105 active:scale-95 transition-all flex-shrink-0 disabled:opacity-40">
            <span v-if="sending" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
            <span v-else class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1">send</span>
          </button>
        </div>
      </div>
    </section>

    <section v-else class="flex-1 hidden md:flex flex-col items-center justify-center p-12 opacity-30 text-center">
      <span class="material-symbols-outlined text-8xl mb-4 text-primary" style="font-variation-settings: 'wght' 100">forum</span>
      <h3 class="font-headline text-xl font-bold">Vos messages</h3>
      <p class="text-sm mt-2">Sélectionnez une conversation ou recherchez un utilisateur</p>
    </section>
  </div>
</template>

<script setup>
import { ref, nextTick, onMounted, onUnmounted } from 'vue'
import axios from 'axios'

const props = defineProps({ autoOpen: { type: Object, default: null } })
const emit  = defineEmits(['opened'])

const BASE = 'http://localhost:8000/api'
const h    = () => ({ Authorization: `Bearer ${localStorage.getItem('token')}` })
const me   = () => JSON.parse(localStorage.getItem('user') || '{}')?.id

const conversations = ref([])
const messages      = ref([])
const selectedUser  = ref(null)
const newMessage    = ref('')
const searchQuery   = ref('')
const searchResults = ref([])
const loadingConvs  = ref(false)
const loadingMsgs   = ref(false)
const sending       = ref(false)
const messagesEl    = ref(null)
let pollingTimer = null, searchTimer = null

// ── Conversations ──────────────────────────────────────────────────────────
const loadConversations = async () => {
  loadingConvs.value = true
  try {
    const res = await axios.get(`${BASE}/conversations`, { headers: h() })
    conversations.value = res.data
  } catch { conversations.value = [] }
  finally { loadingConvs.value = false }
}

// ── Open a conversation ────────────────────────────────────────────────────
const openConversation = async (conv) => {
  selectedUser.value  = conv
  searchQuery.value   = ''
  searchResults.value = []
  loadingMsgs.value   = true
  messages.value      = []
  try {
    const res = await axios.get(`${BASE}/conversations/${conv.user_id}`, { headers: h() })
    // Ensure we have an array
    messages.value = Array.isArray(res.data) ? res.data : []
    await scrollBottom()
    // Mark conversation as read in list
    const c = conversations.value.find(c => c.user_id === conv.user_id)
    if (c) c.unread = false
  } catch (e) {
    console.error('openConversation error:', e?.response?.data || e)
    messages.value = []
  } finally { loadingMsgs.value = false }
}

// ── Start new conversation from search ────────────────────────────────────
const startConversation = async (user) => {
  searchQuery.value   = ''
  searchResults.value = []
  const existing = conversations.value.find(c => c.user_id === user.id)
  if (existing) {
    await openConversation(existing)
  } else {
    selectedUser.value = { user_id: user.id, name: user.name, role: user.role?.name }
    messages.value = []
    // Try to load existing messages
    try {
      const res = await axios.get(`${BASE}/conversations/${user.id}`, { headers: h() })
      messages.value = res.data
      await scrollBottom()
    } catch {}
  }
}

// ── Send message ───────────────────────────────────────────────────────────
const sendMessage = async () => {
  if (!newMessage.value.trim() || !selectedUser.value || sending.value) return
  const text = newMessage.value.trim()
  newMessage.value = ''
  sending.value    = true

  // Optimistic message
  const tempId = `tmp_${Date.now()}`
  messages.value.push({
    id: tempId, sender: 'me', text,
    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
  })
  await scrollBottom()

  try {
    await axios.post(`${BASE}/messages`, {
      receiver_id: selectedUser.value.user_id,
      content: text
    }, { headers: h() })

    // Reload real messages from server
    const res = await axios.get(`${BASE}/conversations/${selectedUser.value.user_id}`, { headers: h() })
    messages.value = Array.isArray(res.data) ? res.data : messages.value
    await scrollBottom()

    // Refresh conversation list
    const convRes = await axios.get(`${BASE}/conversations`, { headers: h() })
    conversations.value = convRes.data
  } catch (e) {
    console.error('sendMessage error:', e?.response?.data || e)
    // Remove temp message on error
    messages.value = messages.value.filter(m => m.id !== tempId)
  } finally { sending.value = false }
}

// ── Search users ───────────────────────────────────────────────────────────
const onSearch = () => {
  clearTimeout(searchTimer)
  if (searchQuery.value.trim().length < 2) { searchResults.value = []; return }
  searchTimer = setTimeout(async () => {
    try {
      const res = await axios.get(`${BASE}/users/search?q=${encodeURIComponent(searchQuery.value)}`, { headers: h() })
      searchResults.value = res.data
    } catch { searchResults.value = [] }
  }, 300)
}

// ── Polling: check for new messages every 3s ───────────────────────────────
const poll = async () => {
  // Refresh conversation list for all users
  try {
    const res = await axios.get(`${BASE}/conversations`, { headers: h() })
    conversations.value = res.data
  } catch {}

  // Refresh messages in open conversation
  if (selectedUser.value) {
    try {
      const res = await axios.get(`${BASE}/conversations/${selectedUser.value.user_id}`, { headers: h() })
      const newMsgs = res.data
      // Only update if there are new messages (compare last id)
      const lastOld = messages.value.filter(m => !String(m.id).startsWith('tmp_')).at(-1)?.id
      const lastNew = newMsgs.at(-1)?.id
      if (lastNew && lastNew !== lastOld) {
        messages.value = newMsgs
        await scrollBottom()
      }
    } catch {}
  }
}

// ── Auto-open conversation (from contact button) ───────────────────────────
const openAutoConv = async ({ userId, name }) => {
  if (!userId) return
  await loadConversations()
  const existing = conversations.value.find(c => c.user_id === userId)
  if (existing) {
    await openConversation(existing)
  } else {
    selectedUser.value  = { user_id: userId, name, role: 'Utilisateur' }
    searchQuery.value   = ''
    searchResults.value = []
    loadingMsgs.value   = true
    try {
      const res = await axios.get(`${BASE}/conversations/${userId}`, { headers: h() })
      messages.value = res.data
      await scrollBottom()
    } catch { messages.value = [] }
    finally { loadingMsgs.value = false }
    // Add to conversation list if not present
    if (!conversations.value.find(c => c.user_id === userId)) {
      conversations.value.unshift({ user_id: userId, name, lastMessage: '...', time: '', unread: false })
    }
  }
  emit('opened')
}

const scrollBottom = async () => {
  await nextTick()
  if (messagesEl.value) messagesEl.value.scrollTop = messagesEl.value.scrollHeight
}
const initials = (name) => name?.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) || 'U'

// Handle event-based open (from freelancer MissionCard)
const handleOpenConv = (e) => { openAutoConv(e.detail) }

onMounted(async () => {
  await loadConversations()
  if (props.autoOpen?.userId) await openAutoConv(props.autoOpen)
  pollingTimer = setInterval(poll, 3000)
  window.addEventListener('open-conversation', handleOpenConv)
})
onUnmounted(() => {
  clearInterval(pollingTimer)
  window.removeEventListener('open-conversation', handleOpenConv)
})
</script>
