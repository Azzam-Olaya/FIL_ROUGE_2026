<template>
  <div class="space-y-6">

    <!-- Formulaire ajout/édition -->
    <div class="bg-white rounded-[2rem] shadow-xl shadow-primary/5 border border-primary/5 p-6 md:p-8">
      <h3 class="font-bold text-xl text-primary mb-6 flex items-center gap-2">
        <span class="material-symbols-outlined">{{ editingId ? 'edit_square' : 'add_circle' }}</span>
        {{ editingId ? 'Modifier la catégorie' : 'Nouvelle catégorie' }}
      </h3>
      <form @submit.prevent="save" class="flex flex-col md:flex-row gap-6 items-end">
        <div class="w-full md:flex-1">
          <label class="block text-[10px] font-bold uppercase tracking-[0.2em] text-on-surface-variant mb-2">Nom de la catégorie</label>
          <input v-model="form.name" required placeholder="Ex: Design graphique"
            class="w-full border border-gray-200 rounded-2xl px-5 py-3 focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/5 text-sm transition-all" />
        </div>
        <div class="w-full md:flex-1">
          <label class="block text-[10px] font-bold uppercase tracking-[0.2em] text-on-surface-variant mb-2">Catégorie parente</label>
          <select v-model="form.parent_id"
            class="w-full border border-gray-200 rounded-2xl px-5 py-3 focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/5 text-sm transition-all appearance-none bg-no-repeat bg-[right_1.25rem_center] bg-[length:1em_1em]">
            <option :value="null">— Catégorie principale —</option>
            <option v-for="cat in rootCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
        </div>
        <div class="flex gap-3 w-full md:w-auto">
          <button type="submit"
            class="flex-1 md:flex-none bg-primary text-white px-8 py-3 rounded-2xl font-bold text-sm hover:scale-105 active:scale-95 transition-all flex items-center justify-center gap-2 shadow-lg shadow-primary/20">
            <span class="material-symbols-outlined text-sm">{{ editingId ? 'save' : 'add' }}</span>
            {{ editingId ? 'Enregistrer' : 'Ajouter' }}
          </button>
          <button v-if="editingId" type="button" @click="cancelEdit"
            class="flex-1 md:flex-none bg-surface-container text-on-surface-variant px-8 py-3 rounded-2xl font-bold text-sm hover:bg-gray-200 transition-all">
            Annuler
          </button>
        </div>
      </form>
      <p v-if="error" class="mt-4 text-red-600 text-xs font-bold bg-red-50 p-3 rounded-xl border border-red-100 flex items-center gap-2">
        <span class="material-symbols-outlined text-sm">error</span> {{ error }}
      </p>
    </div>

    <!-- Liste des catégories -->
    <div class="bg-white rounded-[2.5rem] shadow-xl shadow-primary/5 border border-primary/5 overflow-hidden">
      <div class="px-8 py-6 border-b border-gray-50 flex items-center justify-between bg-gray-50/30">
        <div class="flex items-center gap-3">
          <span class="material-symbols-outlined text-primary">category</span>
          <span class="font-bold text-on-surface">{{ totalCount }} catégorie(s)</span>
        </div>
      </div>

      <div v-if="categories.length === 0" class="py-24 text-center text-on-surface-variant/40">
        <span class="material-symbols-outlined text-6xl block mb-4 animate-pulse">category</span>
        <p class="font-medium">Aucune catégorie. Commencez par en créer une.</p>
      </div>

      <!-- Liste Recursion -->
      <div class="divide-y divide-gray-50">
        <div v-for="cat in categories" :key="cat.id" class="group">
          <!-- Ligne catégorie principale -->
          <div class="flex items-center gap-4 px-8 py-5 hover:bg-primary/[0.02] transition-colors">
            <button @click="toggleExpand(cat.id)" class="text-on-surface-variant hover:text-primary transition-colors">
              <span class="material-symbols-outlined text-lg">
                {{ expanded.includes(cat.id) ? 'expand_less' : 'expand_more' }}
              </span>
            </button>
            <span class="material-symbols-outlined text-primary/40">folder</span>
            <div class="flex-1">
              <span class="font-bold text-on-surface tracking-tight">{{ cat.name }}</span>
              <span class="block text-[10px] text-on-surface-variant font-bold uppercase tracking-widest mt-0.5">
                {{ cat.children?.length || 0 }} sous-catégories
              </span>
            </div>
            
            <div class="flex gap-2 lg:opacity-0 group-hover:opacity-100 transition-all transform lg:translate-x-2 group-hover:translate-x-0">
              <button @click="startEdit(cat)"
                class="p-2.5 rounded-xl bg-primary/5 text-primary hover:bg-primary hover:text-white transition-all shadow-sm">
                <span class="material-symbols-outlined text-sm font-bold">edit</span>
              </button>
              <button @click="confirmDelete(cat)"
                class="p-2.5 rounded-xl bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-all shadow-sm">
                <span class="material-symbols-outlined text-sm font-bold">delete</span>
              </button>
            </div>
          </div>

          <!-- Sous-catégories -->
          <div v-if="expanded.includes(cat.id) && cat.children?.length" class="bg-surface-container/30 border-y border-gray-50">
            <div v-for="sub in cat.children" :key="sub.id"
              class="flex items-center gap-4 px-8 py-4 pl-16 hover:bg-primary/[0.04] transition-colors group/sub">
              <span class="material-symbols-outlined text-secondary/30 text-sm">subdirectory_arrow_right</span>
              <span class="text-on-surface font-medium flex-1 text-sm">{{ sub.name }}</span>
              
              <div class="flex gap-2 lg:opacity-0 group-hover/sub:opacity-100 transition-all">
                <button @click="startEdit(sub)"
                  class="p-2 rounded-xl bg-primary/5 text-primary hover:bg-primary hover:text-white transition-all">
                  <span class="material-symbols-outlined text-xs">edit</span>
                </button>
                <button @click="confirmDelete(sub)"
                  class="p-2 rounded-xl bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-all">
                  <span class="material-symbols-outlined text-xs">delete</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal confirmation suppression -->
    <Teleport to="body">
      <div v-if="deleteTarget" class="fixed inset-0 bg-on-surface/40 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-[2.5rem] p-8 shadow-2xl max-w-sm w-full animate-in zoom-in-95 duration-200">
          <div class="w-16 h-16 rounded-2xl bg-red-50 flex items-center justify-center mb-6">
            <span class="material-symbols-outlined text-red-500 text-3xl">warning</span>
          </div>
          <h3 class="font-bold text-2xl text-on-surface mb-2 tracking-tight">Supprimer ?</h3>
          <p class="text-on-surface-variant text-sm mb-8 leading-relaxed">
            Voulez-vous vraiment supprimer <span class="text-on-surface font-bold">"{{ deleteTarget.name }}"</span> ?
            Les sous-catégories seront rattachées au niveau supérieur.
          </p>
          <div class="flex gap-4">
            <button @click="deleteCategory"
              class="flex-1 bg-red-500 text-white py-3.5 rounded-2xl font-bold hover:bg-red-600 transition-all shadow-lg shadow-red-500/20 active:scale-95">
              Supprimer
            </button>
            <button @click="deleteTarget = null"
              class="flex-1 bg-surface-container text-on-surface py-3.5 rounded-2xl font-bold hover:bg-gray-200 transition-all active:scale-95">
              Annuler
            </button>
          </div>
        </div>
      </div>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/api/axios'

const categories = ref([])
const expanded = ref([])
const editingId = ref(null)
const deleteTarget = ref(null)
const error = ref('')
const form = ref({ name: '', parent_id: null })

const rootCategories = computed(() => categories.value)
const totalCount = computed(() =>
  categories.value.reduce((acc, c) => acc + 1 + (c.children?.length || 0), 0)
)

const load = async () => {
  try {
    const res = await api.get('/admin/categories')
    categories.value = res.data
  } catch {
    console.error('Erreur chargement catégories')
  }
}

const save = async () => {
  error.value = ''
  try {
    if (editingId.value) {
      await api.put(`/admin/categories/${editingId.value}`, form.value)
    } else {
      await api.post('/admin/categories', form.value)
    }
    cancelEdit()
    await load()
  } catch (e) {
    error.value = e.response?.data?.message || Object.values(e.response?.data?.errors || {})[0]?.[0] || 'Erreur'
  }
}

const startEdit = (cat) => {
  editingId.value = cat.id
  form.value = { name: cat.name, parent_id: cat.parent_id ?? null }
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const cancelEdit = () => {
  editingId.value = null
  form.value = { name: '', parent_id: null }
  error.value = ''
}

const confirmDelete = (cat) => { deleteTarget.value = cat }

const deleteCategory = async () => {
  try {
    await api.delete(`/admin/categories/${deleteTarget.value.id}`)
    deleteTarget.value = null
    await load()
  } catch (e) {
    alert(e.response?.data?.message || 'Erreur')
  }
}

const toggleExpand = (id) => {
  expanded.value.includes(id)
    ? expanded.value = expanded.value.filter(i => i !== id)
    : expanded.value.push(id)
}

onMounted(load)
</script>
