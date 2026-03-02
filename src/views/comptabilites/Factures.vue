<template>
  <SidebarLayout currentPage="comptabilites">
    <div>
      <header :style="headerStyle" class="fade-in">
        <div>
          <h1 :style="titleStyle">Factures / Pièces</h1>
          <p :style="subtitleStyle">Toutes les pièces justificatives enregistrées sur l’application (liées aux dépenses)</p>
        </div>
        <div :style="{display:'flex', gap:'10px', flexWrap:'wrap'}">
          <button :style="secondaryBtnStyle" @click="goTo('/comptabilites/depenses')">Retour dépenses</button>
          <button :style="secondaryBtnStyle" @click="goTo('/comptabilites/motifs')">Motifs</button>
        </div>
      </header>

      <div :style="filtersStyle" class="fade-in">
        <div :style="filterBlockStyle">
          <label :style="labelStyle">Période</label>
          <div :style="{display:'flex', gap:'10px', flexWrap:'wrap'}">
            <input v-model="filters.date_debut" type="date" :style="inputStyle" />
            <input v-model="filters.date_fin" type="date" :style="inputStyle" />
          </div>
        </div>
        <div :style="filterBlockStyle">
          <label :style="labelStyle">Recherche</label>
          <input v-model="filters.search" type="text" placeholder="Nom pièce, motif, bénéficiaire..." :style="inputStyle" />
        </div>
        <div :style="{display:'flex', alignItems:'end', gap:'10px'}">
          <button :style="secondaryBtnStyle" @click="loadPieces">Appliquer</button>
          <button :style="dangerBtnStyle" @click="resetFilters">Reset</button>
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
                <th :style="thStyle">Date dépense</th>
                <th :style="thStyle">Motif</th>
                <th :style="thStyle">Bénéficiaire</th>
                <th :style="thStyle">Pièce</th>
                <th :style="thStyle">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in pieces" :key="p.id" :style="trStyle">
                <td :style="tdStyle">{{ formatDate(p.date_depense) }}</td>
                <td :style="tdStyle">
                  <span :style="motifBadgeStyle">{{ p.motif_libelle || '—' }}</span>
                </td>
                <td :style="tdStyle">{{ p.beneficiaire || '—' }}</td>
                <td :style="tdStyle">
                  <a :href="p.url_fichier" target="_blank" rel="noreferrer" :style="fileLinkStyle">{{ p.nom_original || 'Pièce' }}</a>
                  <div :style="{color:'#64748b', fontSize:'12px', marginTop:'4px'}">{{ p.type_fichier || '' }}</div>
                </td>
                <td :style="tdStyle">
                  <div :style="{display:'flex', gap:'8px'}">
                    <button :style="actionBtnStyle('#0f172a')" @click="goToDepense(p.id_depense)">Voir dépense</button>
                    <button :style="actionBtnStyle('#ef4444')" @click="deletePiece(p)">Supprimer</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <div v-if="pieces.length === 0" :style="emptyStateStyle">
            <p :style="emptyTextStyle">Aucune pièce trouvée</p>
          </div>
        </div>
      </div>
    </div>
  </SidebarLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import SidebarLayout from '../SidebarLayout.vue'
import { useAuthStore } from '../../stores/authStore'
import { getApiBaseUrl } from '../../services/api.js'

const router = useRouter()
const authStore = useAuthStore()
const API_BASE_URL = getApiBaseUrl()

const loading = ref(false)
const pieces = ref([])
const filters = ref({ date_debut: '', date_fin: '', search: '' })

const randomParam = () => `&_=${Date.now()}_${Math.random().toString(36).slice(2)}`
const getUserParams = () => {
  const userId = authStore.user?.id
  const role = authStore.isAdmin ? 'admin' : 'user'
  const id_entreprise = authStore.user?.id_entreprise
  const base = `&user_id=${userId || ''}&role=${role}`
  return id_entreprise ? `${base}&id_entreprise=${id_entreprise}` : base
}
const getAuthHeaders = () => {
  const token = localStorage.getItem('token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

const goTo = (path) => router.push(path)

const formatDate = (iso) => {
  if (!iso) return '—'
  const d = new Date(iso)
  if (Number.isNaN(d.getTime())) return String(iso)
  return d.toLocaleDateString('fr-FR')
}

const loadPieces = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams()
    if (filters.value.date_debut) params.set('date_debut', filters.value.date_debut)
    if (filters.value.date_fin) params.set('date_fin', filters.value.date_fin)
    if (filters.value.search) params.set('search', filters.value.search)
    const url = `${API_BASE_URL}/api_comptabilites.php?action=list_pieces_global${getUserParams()}&${params.toString()}${randomParam()}`
    const res = await fetch(url, { headers: getAuthHeaders() })
    const data = await res.json()
    pieces.value = data.success ? (data.data || []) : []
  } catch (e) {
    console.error(e)
    pieces.value = []
  } finally {
    loading.value = false
  }
}

const resetFilters = () => {
  filters.value = { date_debut: '', date_fin: '', search: '' }
  loadPieces()
}

const deletePiece = async (p) => {
  if (!confirm('Supprimer cette pièce ?')) return
  try {
    const res = await fetch(`${API_BASE_URL}/api_comptabilites.php?action=delete_piece`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', ...getAuthHeaders() },
      body: JSON.stringify({ id_piece: p.id, user_id: authStore.user?.id, id_entreprise: authStore.user?.id_entreprise })
    })
    const data = await res.json()
    if (!data.success) throw new Error(data.error || data.message || 'Erreur')
    await loadPieces()
  } catch (e) {
    console.error(e)
    alert(e.message || 'Erreur suppression')
  }
}

const goToDepense = () => {
  // Pour l’instant, on renvoie vers la liste (amélioration: deep-link + highlight)
  router.push('/comptabilites/depenses')
}

onMounted(loadPieces)

// Styles
const headerStyle = { display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '12px', flexWrap: 'wrap', marginBottom: '18px' }
const titleStyle = { fontSize: '32px', fontWeight: '800', color: '#0f172a', margin: '0 0 4px 0' }
const subtitleStyle = { fontSize: '14px', color: '#64748b', margin: 0 }
const secondaryBtnStyle = { padding: '12px 18px', background: '#0f172a', color: 'white', border: 'none', borderRadius: '12px', cursor: 'pointer', fontWeight: '700' }
const dangerBtnStyle = { padding: '12px 18px', background: '#ef4444', color: 'white', border: 'none', borderRadius: '12px', cursor: 'pointer', fontWeight: '700' }
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
const fileLinkStyle = { color: '#3b82f6', fontWeight: '800', textDecoration: 'none' }
const motifBadgeStyle = { background: 'rgba(16,185,129,0.12)', color: '#059669', padding: '4px 10px', borderRadius: '999px', fontWeight: '800', fontSize: '12px' }
const actionBtnStyle = (color) => ({ padding: '8px 10px', borderRadius: '10px', border: 'none', cursor: 'pointer', fontWeight: '800', color: 'white', background: color })
const emptyStateStyle = { padding: '18px', textAlign: 'center' }
const emptyTextStyle = { margin: 0, color: '#94a3b8', fontWeight: '700' }
const loadingContainerStyle = { textAlign: 'center', padding: '40px 16px' }
const loadingTextStyle = { marginTop: '10px', fontSize: '14px', fontWeight: '700', color: '#64748b' }
const spinnerStyle = { width: '40px', height: '40px', margin: '0 auto', border: '4px solid #f1f5f9', borderTop: '4px solid #10b981', borderRadius: '50%', animation: 'spin 1s linear infinite' }
</script>

<style scoped>
@keyframes spin { to { transform: rotate(360deg); } }
.fade-in { animation: fadeIn .2s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
</style>

