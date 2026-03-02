<template>
  <SidebarLayout currentPage="comptabilites">
    <div>
      <header :style="headerStyle" class="fade-in">
        <div>
          <h1 :style="titleStyle">Motifs de Dépense</h1>
          <p :style="subtitleStyle">Créez et organisez les motifs (catégories) utilisés par les dépenses</p>
        </div>
        <div :style="{display:'flex', gap:'10px', flexWrap:'wrap'}">
          <button :style="secondaryBtnStyle" @click="goTo('/comptabilites/depenses')">Retour dépenses</button>
          <button :style="primaryBtnStyle" @click="openModal()">Nouveau motif</button>
        </div>
      </header>

      <div :style="filtersStyle" class="fade-in">
        <div :style="filterBlockStyle">
          <label :style="labelStyle">Recherche</label>
          <input v-model="search" type="text" placeholder="Libellé..." :style="inputStyle" />
        </div>
        <div :style="{display:'flex', alignItems:'end', gap:'10px'}">
          <button :style="secondaryBtnStyle" @click="loadMotifs">Actualiser</button>
        </div>
      </div>

      <div :style="tableContainerStyle" class="fade-in">
        <div v-if="loading" :style="loadingContainerStyle">
          <div :style="spinnerStyle"></div>
          <p :style="loadingTextStyle">Chargement...</p>
        </div>

        <div v-if="!loading" :style="tableWrapperStyle">
          <table :style="tableStyle">
            <thead>
              <tr>
                <th :style="thStyle">Libellé</th>
                <th :style="thStyle">Description</th>
                <th :style="thStyle">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="m in filtered" :key="m.id" :style="trStyle">
                <td :style="tdStyle"><strong>{{ m.libelle }}</strong></td>
                <td :style="tdStyle">{{ m.description || '—' }}</td>
                <td :style="tdStyle">
                  <div :style="{display:'flex', gap:'8px'}">
                    <button :style="actionBtnStyle('#3b82f6')" @click="openModal(m)">Modifier</button>
                    <button :style="actionBtnStyle('#ef4444')" @click="deleteMotif(m)">Supprimer</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <div v-if="filtered.length === 0" :style="emptyStateStyle">
            <p :style="emptyTextStyle">Aucun motif</p>
          </div>
        </div>
      </div>

      <Transition name="modal">
        <div v-if="showModal" :style="modalOverlayStyle" @click.self="closeModal">
          <div :style="modalStyle" class="modal-content">
            <div :style="modalHeaderStyle">
              <h2 :style="modalTitleStyle">{{ editing ? 'Modifier motif' : 'Nouveau motif' }}</h2>
              <button :style="closeButtonStyle" @click="closeModal">✕</button>
            </div>
            <div :style="modalBodyStyle">
              <div :style="{display:'grid', gap:'12px'}">
                <div>
                  <label :style="labelStyle">Libellé *</label>
                  <input v-model="form.libelle" type="text" :style="inputStyle" placeholder="Ex: Transport, Eau, Electricité..." />
                </div>
                <div>
                  <label :style="labelStyle">Description</label>
                  <textarea v-model="form.description" :style="{...inputStyle, minHeight:'90px'}" placeholder="Optionnel"></textarea>
                </div>
              </div>
            </div>
            <div :style="modalFooterStyle">
              <button :style="secondaryBtnStyle" @click="closeModal">Annuler</button>
              <button :style="primaryBtnStyle" @click="saveMotif" :disabled="saving">{{ saving ? 'Enregistrement...' : 'Enregistrer' }}</button>
            </div>
          </div>
        </div>
      </Transition>
    </div>
  </SidebarLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import SidebarLayout from '../SidebarLayout.vue'
import { useAuthStore } from '../../stores/authStore'
import { getApiBaseUrl } from '../../services/api.js'

const router = useRouter()
const authStore = useAuthStore()
const API_BASE_URL = getApiBaseUrl()

const loading = ref(false)
const saving = ref(false)
const motifs = ref([])
const search = ref('')

const showModal = ref(false)
const editing = ref(null)
const form = ref({ id_motif: null, libelle: '', description: '' })

const randomParam = () => `&_=${Date.now()}_${Math.random().toString(36).slice(2)}`
const getUserParams = () => {
  const userId = authStore.user?.id
  const role = authStore.isAdmin ? 'admin' : 'user'
  const id_entreprise = authStore.user?.id_entreprise
  const base = `&user_id=${userId || ''}&role=${role}`
  return id_entreprise ? `${base}&id_entreprise=${id_entreprise}` : base
}

const goTo = (path) => router.push(path)

const filtered = computed(() => {
  const q = (search.value || '').trim().toLowerCase()
  if (!q) return motifs.value
  return motifs.value.filter(m => String(m.libelle || '').toLowerCase().includes(q))
})

const loadMotifs = async () => {
  loading.value = true
  try {
    const res = await fetch(`${API_BASE_URL}/api_comptabilites.php?action=list_motifs${getUserParams()}${randomParam()}`)
    const data = await res.json()
    motifs.value = data.success ? (data.data || []) : []
  } catch (e) {
    console.error(e)
    motifs.value = []
  } finally {
    loading.value = false
  }
}

const openModal = (m = null) => {
  editing.value = m
  form.value = m
    ? { id_motif: m.id, libelle: m.libelle || '', description: m.description || '' }
    : { id_motif: null, libelle: '', description: '' }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editing.value = null
  form.value = { id_motif: null, libelle: '', description: '' }
}

const saveMotif = async () => {
  if (!form.value.libelle?.trim()) {
    alert('Libellé requis')
    return
  }
  saving.value = true
  try {
    const action = editing.value ? 'update_motif' : 'add_motif'
    const payload = {
      ...form.value,
      user_id: authStore.user?.id,
      id_entreprise: authStore.user?.id_entreprise
    }
    const res = await fetch(`${API_BASE_URL}/api_comptabilites.php?action=${action}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })
    const data = await res.json()
    if (!data.success) throw new Error(data.error || data.message || 'Erreur')
    closeModal()
    await loadMotifs()
  } catch (e) {
    console.error(e)
    alert(e.message || 'Erreur sauvegarde')
  } finally {
    saving.value = false
  }
}

const deleteMotif = async (m) => {
  if (!confirm('Supprimer ce motif ?')) return
  try {
    const res = await fetch(`${API_BASE_URL}/api_comptabilites.php?action=delete_motif`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id_motif: m.id, user_id: authStore.user?.id, id_entreprise: authStore.user?.id_entreprise })
    })
    const data = await res.json()
    if (!data.success) throw new Error(data.error || data.message || 'Erreur')
    await loadMotifs()
  } catch (e) {
    console.error(e)
    alert(e.message || 'Erreur suppression')
  }
}

onMounted(loadMotifs)

// Styles
const headerStyle = { display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '12px', flexWrap: 'wrap', marginBottom: '18px' }
const titleStyle = { fontSize: '32px', fontWeight: '800', color: '#0f172a', margin: '0 0 4px 0' }
const subtitleStyle = { fontSize: '14px', color: '#64748b', margin: 0 }
const primaryBtnStyle = { padding: '12px 18px', background: 'linear-gradient(135deg, #10b981 0%, #059669 100%)', color: 'white', border: 'none', borderRadius: '12px', cursor: 'pointer', fontWeight: '700' }
const secondaryBtnStyle = { padding: '12px 18px', background: '#0f172a', color: 'white', border: 'none', borderRadius: '12px', cursor: 'pointer', fontWeight: '700' }
const filtersStyle = { background: 'white', border: '1px solid #e2e8f0', borderRadius: '16px', padding: '14px', display: 'grid', gap: '12px', gridTemplateColumns: 'repeat(auto-fit, minmax(220px, 1fr))', marginBottom: '16px' }
const filterBlockStyle = { display: 'grid', gap: '6px' }
const labelStyle = { fontSize: '12px', fontWeight: '800', color: '#0f172a' }
const inputStyle = { width: '100%', padding: '10px 12px', borderRadius: '12px', border: '1px solid #e2e8f0', outline: 'none', background: '#fff' }
const tableContainerStyle = { background: 'white', border: '1px solid #e2e8f0', borderRadius: '16px', overflow: 'hidden' }
const tableWrapperStyle = { overflowX: 'auto' }
const tableStyle = { width: '100%', borderCollapse: 'collapse' }
const thStyle = { textAlign: 'left', padding: '12px', fontSize: '12px', color: '#64748b', borderBottom: '1px solid #e2e8f0', whiteSpace: 'nowrap' }
const tdStyle = { padding: '12px', borderBottom: '1px solid #f1f5f9', verticalAlign: 'top' }
const trStyle = { background: 'white' }
const actionBtnStyle = (color) => ({ padding: '8px 10px', borderRadius: '10px', border: 'none', cursor: 'pointer', fontWeight: '800', color: 'white', background: color })
const emptyStateStyle = { padding: '18px', textAlign: 'center' }
const emptyTextStyle = { margin: 0, color: '#94a3b8', fontWeight: '700' }
const loadingContainerStyle = { textAlign: 'center', padding: '40px 16px' }
const loadingTextStyle = { marginTop: '10px', fontSize: '14px', fontWeight: '700', color: '#64748b' }
const spinnerStyle = { width: '40px', height: '40px', margin: '0 auto', border: '4px solid #f1f5f9', borderTop: '4px solid #10b981', borderRadius: '50%', animation: 'spin 1s linear infinite' }
const modalOverlayStyle = { position: 'fixed', inset: 0, background: 'rgba(15, 23, 42, 0.55)', display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '16px', zIndex: 999 }
const modalStyle = { width: '100%', maxWidth: '680px', background: 'white', borderRadius: '18px', border: '1px solid #e2e8f0', overflow: 'hidden' }
const modalHeaderStyle = { display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '14px 16px', borderBottom: '1px solid #e2e8f0' }
const modalTitleStyle = { margin: 0, fontSize: '16px', fontWeight: '900', color: '#0f172a' }
const closeButtonStyle = { border: 'none', background: 'transparent', cursor: 'pointer', fontSize: '18px', fontWeight: '900', color: '#64748b' }
const modalBodyStyle = { padding: '16px' }
const modalFooterStyle = { padding: '14px 16px', borderTop: '1px solid #e2e8f0', display: 'flex', justifyContent: 'flex-end', gap: '10px' }
</script>

<style scoped>
@keyframes spin { to { transform: rotate(360deg); } }
.fade-in { animation: fadeIn .2s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
.modal-content { box-shadow: 0 20px 60px rgba(0,0,0,0.25); }
</style>

