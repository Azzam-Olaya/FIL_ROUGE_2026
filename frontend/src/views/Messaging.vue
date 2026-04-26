<template>
  <div class="h-screen flex flex-col bg-surface font-body text-on-surface zellige-pattern overflow-hidden relative">
    <!-- TopNavBar Réutilisable -->
    <TopNavBar />

    <div class="flex-1 flex overflow-hidden zellige-pattern relative">
      <!-- Sidebar / Conversations List -->
      <aside class="w-full md:w-96 bg-surface-container-lowest/80 backdrop-blur-xl flex flex-col border-r border-outline-variant/15 z-10 transition-all" :class="{ 'hidden md:flex': selectedChat }">
        <div class="p-6">
          <div class="flex items-center justify-between mb-6">
            <h2 class="font-headline text-2xl font-bold text-primary">Messages</h2>
            <button class="p-2 rounded-xl bg-primary/5 text-primary hover:bg-primary hover:text-white transition-all">
              <span class="material-symbols-outlined text-sm font-bold">edit_square</span>
            </button>
          </div>
          
          <div class="space-y-2 overflow-y-auto max-h-[calc(100vh-220px)] pr-2 custom-scrollbar">
            <div v-for="chat in chats" :key="chat.id" 
                 @click="selectedChat = chat"
                 :class="selectedChat?.id === chat.id ? 'bg-primary/5 border-l-4 border-primary shadow-sm' : 'hover:bg-surface-container-high'"
                 class="p-4 rounded-xl cursor-pointer transition-all group border-b border-primary/5">
              <div class="flex gap-4">
                <div class="relative flex-shrink-0">
                  <img :alt="chat.name" class="w-12 h-12 rounded-full object-cover shadow-sm group-hover:scale-105 transition-transform" :src="chat.avatar">
                  <span v-if="chat.online" class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-primary rounded-full border-2 border-white"></span>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex justify-between items-baseline mb-1">
                    <h3 class="font-bold text-sm truncate text-on-surface">{{ chat.name }}</h3>
                    <span class="text-[10px] text-on-surface-variant font-bold opacity-60">{{ chat.time }}</span>
                  </div>
                  <p class="text-xs text-on-surface-variant truncate font-medium" :class="{ 'text-primary font-bold': chat.unread }">
                    {{ chat.lastMessage }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </aside>

      <!-- Chat Window -->
      <section class="flex-1 flex flex-col bg-white/40 backdrop-blur-xl relative" v-if="selectedChat">
        <!-- Chat Header -->
        <div class="p-6 flex items-center justify-between bg-surface-container-low/50 border-b border-outline-variant/15">
          <div class="flex items-center gap-4">
             <button @click="selectedChat = null" class="md:hidden p-2 rounded-full hover:bg-primary/10">
               <span class="material-symbols-outlined">arrow_back</span>
             </button>
             <div class="relative">
               <img :alt="selectedChat.name" class="w-12 h-12 rounded-full object-cover shadow-md" :src="selectedChat.avatar">
               <span class="absolute bottom-0 right-0 w-3 h-3 bg-primary rounded-full border-2 border-white"></span>
             </div>
             <div>
               <div class="flex items-center gap-2">
                 <h2 class="font-headline font-bold text-lg text-on-surface">{{ selectedChat.name }}</h2>
                 <span class="bg-tertiary/10 text-tertiary text-[10px] px-2 py-0.5 rounded-full flex items-center gap-1 font-bold uppercase tracking-wider">
                   <span class="material-symbols-outlined text-[12px]" style="font-variation-settings: 'FILL' 1;">stars</span>
                   Vérifié
                 </span>
               </div>
               <p class="text-xs text-primary font-bold italic">En ligne</p>
             </div>
          </div>
          <div class="flex items-center gap-3">
            <!-- New: Lancer Contrat Button (Client Only) -->
            <button v-if="isClient" @click="showContractModal = true"
              class="px-5 py-2.5 rounded-xl bg-primary text-white font-bold text-xs uppercase tracking-widest flex items-center gap-2 hover:scale-105 active:scale-95 transition-all shadow-lg shadow-primary/20">
              <span class="material-symbols-outlined text-sm">assignment</span>
              <span>Lancer Contrat</span>
            </button>
            <div class="flex gap-2">
              <button class="w-10 h-10 rounded-full bg-white/50 flex items-center justify-center hover:bg-primary/10 transition-colors text-on-surface-variant"><span class="material-symbols-outlined">call</span></button>
              <button class="w-10 h-10 rounded-full bg-white/50 flex items-center justify-center hover:bg-primary/10 transition-colors text-on-surface-variant"><span class="material-symbols-outlined">video_call</span></button>
              <button class="w-10 h-10 rounded-full bg-white/50 flex items-center justify-center hover:bg-primary/10 transition-colors text-on-surface-variant"><span class="material-symbols-outlined">more_vert</span></button>
            </div>
          </div>
        </div>

        <!-- Messages -->
        <div class="flex-1 overflow-y-auto p-8 flex flex-col gap-6 custom-scrollbar scroll-smooth">
          <div v-for="(msg, idx) in messages" :key="idx" 
               :class="msg.sender === 'me' ? 'self-end flex-row-reverse' : 'self-start'" 
               class="flex items-start gap-4 max-w-[85%] group">
            <img v-if="msg.sender !== 'me'" class="w-8 h-8 rounded-full object-cover shadow-sm bg-surface-container" :src="selectedChat.avatar">
            <div :class="msg.sender === 'me' ? 'items-end' : 'items-start'" class="flex flex-col space-y-1">
              <div :class="msg.sender === 'me' ? 'bg-primary text-white rounded-tl-2xl rounded-b-2xl shadow-lg shadow-primary/10' : 'bg-surface-container-high text-on-surface rounded-tr-2xl rounded-b-2xl shadow-sm'"
                   class="p-4 text-sm leading-relaxed font-medium">
                {{ msg.text }}
                <div v-if="msg.image" class="mt-3 rounded-xl overflow-hidden shadow-md">
                   <img :src="msg.image" class="w-full max-h-60 object-cover cursor-zoom-in">
                </div>
              </div>
              <div class="flex items-center gap-1.5 px-1">
                 <span class="text-[10px] font-bold opacity-40 uppercase">{{ msg.time }}</span>
                 <span v-if="msg.sender === 'me'" class="material-symbols-outlined text-[14px] text-primary" style="font-variation-settings: 'FILL' 1;">done_all</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Input -->
        <div class="p-6 bg-surface-container-low/30 backdrop-blur-md">
          <div class="bg-white rounded-3xl p-3 shadow-2xl flex items-end gap-3 ring-1 ring-primary/5 focus-within:ring-primary/20 transition-all border border-primary/5">
             <div class="flex gap-1 pb-1">
                <button class="w-10 h-10 flex items-center justify-center text-on-surface-variant hover:text-primary transition-all active:scale-90"><span class="material-symbols-outlined">add_circle</span></button>
                <button class="w-10 h-10 flex items-center justify-center text-on-surface-variant hover:text-primary transition-all active:scale-90"><span class="material-symbols-outlined">image</span></button>
             </div>
             <textarea v-model="newMessage" @keydown.enter.prevent="sendMessage" class="flex-1 bg-transparent border-none focus:ring-0 text-sm py-3 px-2 resize-none max-h-32 placeholder:text-on-surface-variant/40 font-medium" placeholder="Écrivez votre message artisanal..." rows="1"></textarea>
             <div class="pb-1 pr-1 flex gap-2">
                <button class="w-10 h-10 flex items-center justify-center text-on-surface-variant hover:text-tertiary transition-all active:scale-90"><span class="material-symbols-outlined">mood</span></button>
                <button @click="sendMessage" class="w-11 h-11 rounded-2xl bg-primary text-white flex items-center justify-center shadow-xl shadow-primary/30 hover:scale-105 active:scale-95 transition-all">
                  <span class="material-symbols-outlined font-bold" style="font-variation-settings: 'FILL' 1;">send</span>
                </button>
             </div>
          </div>
        </div>
      </section>

      <!-- Empty State -->
      <section v-else class="flex-1 hidden md:flex flex-col items-center justify-center p-12 opacity-40 text-center">
        <span class="material-symbols-outlined text-8xl mb-4 text-primary" style="font-variation-settings: 'wght' 200;">chat_bubble_outline</span>
        <h3 class="font-headline text-2xl font-bold">Sélectionnez une conversation</h3>
        <p class="max-w-xs mx-auto mt-2 font-medium">L'excellence commence par un échange de qualité.</p>
      </section>
    </div>

    <!-- Modals -->
    <ContractModal 
      v-if="selectedChat"
      :show="showContractModal" 
      :freelancer-id="selectedChat.id" 
      :freelancer-name="selectedChat.name"
      @close="showContractModal = false"
      @success="onContractCreated"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import TopNavBar from '@/components/Common/TopNavBar.vue';
import ContractModal from '@/components/Client/ContractModal.vue';

const authStore = useAuthStore();
const isClient = computed(() => authStore.userRole === 'client');

const selectedChat = ref(null);
const newMessage = ref('');
const showContractModal = ref(false);

const chats = ref([
  { id: 1, name: 'Fatima Zohra', avatar: 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=150&q=80', lastMessage: "Le motif Zellige pour le salon est prêt. J'attends votre...", time: '14:20', online: true, unread: true },
  { id: 2, name: 'Youssef El Amrani', avatar: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=150&q=80', lastMessage: "Merci pour les dimensions. Je commence la menuiserie demain.", time: 'Hier', online: false, unread: false },
  { id: 3, name: 'Sara Benjelloun', avatar: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=150&q=80', lastMessage: "La proposition de logo a été envoyée par email. Qu'en dites-vous?", time: 'Lun', online: true, unread: false }
]);

const messages = ref([
  { sender: 'other', text: "Bonjour ! J'ai bien reçu les fichiers pour la rénovation de la cuisine. Le style néo-mauresque est une excellente idée.", time: '10:45' },
  { sender: 'me', text: "Super, je suis ravi que ça vous plaise. Pensez-vous qu'on puisse intégrer des azulejos sur le plan de travail ?", time: '10:48' },
  { sender: 'other', text: "Absolument. Voici une planche de tendance que j'ai préparée ce matin.", image: 'https://images.unsplash.com/photo-1517430816045-df4b7de11d1d?auto=format&fit=crop&w=400&q=80', time: '14:20' }
]);

const sendMessage = () => {
  if (!newMessage.value.trim()) return;
  messages.value.push({
    sender: 'me',
    text: newMessage.value,
    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
  });
  newMessage.value = '';
};

const onContractCreated = (contract) => {
  // Optionnel: Ajouter un message système dans le chat pour dire que le contrat a été envoyé
  messages.value.push({
    sender: 'me',
    text: `📜 Contrat proposé : ${contract.amount} DH. En attente de votre validation.`,
    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
    isSystem: true
  });
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(0, 72, 36, 0.1);
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 72, 36, 0.2);
}
</style>
