<template>
  <div class="space-y-6">

    <!-- Formulaire ajout/édition -->
    <div class="bg-white rounded-2xl shadow-sm border border-primary/10 p-6">
      <h3 class="font-bold text-lg text-primary mb-4">
        {{ editingId ? 'Modifier la catégorie' : 'Nouvelle catégorie' }}
      </h3>
      <form @submit.prevent="save" class="flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-[200px]">
          <label class="block text-xs font-bold uppercase tracking-widest text-gray-500 mb-1">Nom</label>
          <input v-model="form.name" required placeholder="Ex: Design graphique"
            class="w-full border border-gray-200 rounded-xl px-4 py-2 focus:outline-none focus:border-primary text-sm" />
        </div>
        <div class="flex-1 min-w-[200px]">
          <label class="block text-xs font-bold uppercase tracking-widest text-gray-500 mb-1">Catégorie parente (optionnel)</label>
          <select v-model="form.parent_id"
            class="w-full border border-gray-200 rounded-xl px-4 py-2 focus:outline-none focus:border-primary text-sm">
            <option :value="null">— Aucune (catégorie principale) —</option>
            <option v-for="cat in rootCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
        </div>
        <div class="flex gap-2">
          <button type="submit"
            class="bg-primary text-white px-6 py-2 rounded-xl font-bold text-sm hover:brightness-110 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-sm">{{ editingId ? 'save' : 'add' }}</span>
            {{ editingId ? 'Enregistrer' : 'Ajouter' }}
          </button>
          <button v-if="editingId" type="button" @click="cancelEdit"
            class="bg-gray-100 text-gray-600 px-6 py-2 rounded-xl font-bold text-sm hover:bg-gray-200 transition-all">
            Annuler
          </button>
        </div>
      </form>
      <p v-if="error" class="mt-3 text-red-600 text-sm">{{ error }}</p>
    </div>

    <!-- Liste des catégories -->
    <div class="bg-white rounded-2xl shadow-sm border border-primary/10 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <span class="font-bold text-gray-700">{{ totalCount }} catégorie(s) au total</span>
      </div>

      <div v-if="categories.length === 0" class="py-16 text-center text-gray-400">
        <span class="material-symbols-outlined text-5xl block mb-2">category</span>
        Aucune catégorie. Ajoutez-en une ci-dessus.
      </div>

      <!-- Catégories principales -->
      <div v-for="cat in categories" :key="cat.id" class="border-b border-gray-50 last:border-0">
        <!-- Ligne catégorie principale -->
        <div class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 transition-colors group">
          <button @click="toggleExpand(cat.id)" class="text-gray-400 hover:text-primary transition-colors">
            <span class="material-symbols-outlined text-sm">
              {{ expanded.includes(cat.id) ? 'expand_less' : 'expand_more' }}
            </span>
          </button>
          <span class="material-symbols-outlined text-primary">folder</span>
          <span class="font-bold text-gray-800 flex-1">{{ cat.name }}</span>
          <span class="text-xs text-gray-400 font-medium">{{ cat.children?.length || 0 }} sous-catégorie(s)</span>
          <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
            <button @click="startEdit(cat)"
              class="p-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
              <span class="material-symbols-outlined text-sm">edit</span>
            </button>
            <button @click="confirmDelete(cat)"
              class="p-1.5 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition-colors">
              <span class="material-symbols-outlined text-sm">delete</span>
            </button>
          </div>
        </div>

        <!-- Sous-catégories -->
        <div v-if="expanded.includes(cat.id) && cat.children?.length" class="bg-gray-50/50">
          <div v-for="sub in cat.children" :key="sub.id"
            class="flex items-center gap-4 px-6 py-3 pl-14 border-t border-gray-100 hover:bg-gray-100/50 transition-colors group">
            <span class="material-symbols-outlined text-secondary text-sm">subdirectory_arrow_right</span>
            <span class="text-gray-700 flex-1 text-sm">{{ sub.name }}</span>
            <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
              <button @click="startEdit(sub)"
                class="p-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                <span class="material-symbols-outlined text-sm">edit</span>
              </button>
              <button @click="confirmDelete(sub)"
                class="p-1.5 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition-colors">
                <span class="material-symbols-outlined text-sm">delete</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal confirmation suppression -->
    <div v-if="deleteTarget" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-8 shadow-2xl max-w-sm w-full mx-4">
        <span class="material-symbols-outlined text-red-500 text-4xl block mb-3">warning</span>
        <h3 class="font-bold text-lg mb-2">Supprimer "{{ deleteTarget.name }}" ?</h3>
        <p class="text-gray-500 text-sm mb-6">
          Les sous-catégories seront remontées au niveau supérieur. Cette action est irréversible.
        </p>
        <div class="flex gap-3">
          <button @click="deleteCategory"
            class="flex-1 bg-red-500 text-white py-2 rounded-xl font-bold hover:bg-red-600 transition-colors">
            Supprimer
          </button>
          <button @click="deleteTarget = null"
            class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-xl font-bold hover:bg-gray-200 transition-colors">
            Annuler
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const categories = ref([])
const expanded = ref([])
const editingId = ref(null)
const deleteTarget = ref(null)
const error = ref('')
const form = ref({ name: '', parent_id: null })

const token = () => localStorage.getItem('token')
const headers = computed(() => ({ Authorization: `Bearer ${token()}` }))

const rootCategories = computed(() => categories.value)
const totalCount = computed(() =>
  categories.value.reduce((acc, c) => acc + 1 + (c.children?.length || 0), 0)
)

const load = async () => {
  const res = await axios.get('http://localhost:8000/api/admin/categories', { headers: headers.value })
  categories.value = res.data
}

const save = async () => {
  error.value = ''
  try {
    if (editingId.value) {
      await axios.put(`http://localhost:8000/api/admin/categories/${editingId.value}`, form.value, { headers: headers.value })
    } else {
      await axios.post('http://localhost:8000/api/admin/categories', form.value, { headers: headers.value })
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
  await axios.delete(`http://localhost:8000/api/admin/categories/${deleteTarget.value.id}`, { headers: headers.value })
  deleteTarget.value = null
  await load()
}

const toggleExpand = (id) => {
  expanded.value.includes(id)
    ? expanded.value = expanded.value.filter(i => i !== id)
    : expanded.value.push(id)
}

onMounted(load)
</script>
