<template>
  <SidebarLayout currentPage="comptabilites">
    <div>
      <header :style="headerStyle" class="fade-in">
        <div>
          <h1 :style="titleStyle">Gestion des Dépenses</h1>
          <p :style="subtitleStyle">Dépenses, filtres par période, motifs et pièces justificatives</p>
        </div>
        <div :style="{display:'flex', gap:'10px', flexWrap:'wrap'}">
          <button :style="secondaryBtnStyle" @click="goTo('/comptabilites')">Accueil</button>
          <button :style="secondaryBtnStyle" @click="goTo('/comptabilites/motifs')">Motifs</button>
          <button :style="secondaryBtnStyle" @click="goTo('/comptabilites/factures')">Factures</button>
          <button :style="secondaryBtnStyle" @click="goTo('/comptabilites/banque')">Gestion bancaire</button>
          <button :style="primaryBtnStyle" @click="openDepenseModal()">Nouvelle dépense</button>
        </div>
      </header>

      <!-- Stats -->
      <div :style="statsGridStyle" class="fade-in">
        <div :style="statsCardStyle">
          <p :style="statsLabelStyle">Total période</p>
          <p :style="statsValueStyle">{{ formatMoney(stats.total_periode) }}</p>
        </div>
        <div :style="statsCardStyle">
          <p :style="statsLabelStyle">Nombre de dépenses</p>
          <p :style="statsValueStyle">{{ stats.nb_depenses }}</p>
        </div>
        <div :style="statsCardStyle">
          <p :style="statsLabelStyle">Motifs</p>
          <p :style="statsValueStyle">{{ motifs.length }}</p>
        </div>
      </div>

      <!-- Filters -->
      <div :style="filtersStyle" class="fade-in">
        <div :style="filterBlockStyle">
          <label :style="labelStyle">Période</label>
          <div :style="{display:'flex', gap:'10px', flexWrap:'wrap'}">
            <input v-model="filters.date_debut" type="date" :style="inputStyle" />
            <input v-model="filters.date_fin" type="date" :style="inputStyle" />
          </div>
        </div>

        <div :style="filterBlockStyle">
          <label :style="labelStyle">Motif</label>
          <select v-model="filters.motif_id" :style="inputStyle">
            <option value="">Tous</option>
            <option v-for="m in motifs" :key="m.id" :value="m.id">{{ m.libelle }}</option>
          </select>
        </div>

        <div :style="filterBlockStyle">
          <label :style="labelStyle">Recherche</label>
          <input v-model="filters.search" type="text" placeholder="Référence, description, bénéficiaire..." :style="inputStyle" />
        </div>

        <div :style="{display:'flex', alignItems:'end', gap:'10px'}">
          <button :style="secondaryBtnStyle" @click="reloadAll">Appliquer</button>
          <button :style="dangerBtnStyle" @click="resetFilters">Reset</button>
        </div>
      </div>

      <!-- Table -->
      <div :style="tableContainerStyle" class="fade-in">
        <div v-if="loading" :style="loadingContainerStyle">
          <div :style="spinnerStyle"></div>
          <p :style="loadingTextStyle">Chargement...</p>
        </div>

        <div v-if="!loading" :style="tableWrapperStyle">
          <table :style="tableStyle">
            <thead>
              <tr>
                <th :style="thStyle">Date</th>
                <th :style="thStyle">Motif</th>
                <th :style="thStyle">Bénéficiaire</th>
                <th :style="thStyle">Description</th>
                <th :style="thStyle">Montant</th>
                <th :style="thStyle">Pièces</th>
                <th :style="thStyle">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="d in depenses" :key="d.id" :style="trStyle">
                <td :style="tdStyle">{{ formatDate(d.date_depense) }}</td>
                <td :style="tdStyle">
                  <span :style="motifBadgeStyle">{{ d.motif_libelle || '—' }}</span>
                </td>
                <td :style="tdStyle">{{ d.beneficiaire || '—' }}</td>
                <td :style="tdStyle">{{ d.description || '—' }}</td>
                <td :style="tdStyle">
                  <span :style="amountStyle">{{ formatMoney(d.montant || 0) }}</span>
                </td>
                <td :style="tdStyle">
                  <button v-if="(d.nb_pieces || 0) > 0" :style="linkBtnStyle" @click="openPiecesModal(d)">
                    {{ d.nb_pieces }} pièce(s)
                  </button>
                  <span v-else :style="{color:'#94a3b8'}">0</span>
                </td>
                <td :style="tdStyle">
                  <div :style="{display:'flex', gap:'8px'}">
                    <button :style="actionBtnStyle('#3b82f6')" @click="openDepenseModal(d)">Modifier</button>
                    <button :style="actionBtnStyle('#ef4444')" @click="deleteDepense(d)">Supprimer</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <div v-if="depenses.length === 0" :style="emptyStateStyle">
            <p :style="emptyTextStyle">Aucune dépense trouvée</p>
          </div>
        </div>
      </div>

      <!-- Modal Dépense -->
      <Transition name="modal">
        <div v-if="showDepenseModal" :style="modalOverlayStyle" @click.self="closeDepenseModal">
          <div :style="modalStyle" class="modal-content">
            <div :style="modalHeaderStyle">
              <h2 :style="modalTitleStyle">{{ editingDepense ? 'Modifier dépense' : 'Nouvelle dépense' }}</h2>
              <button :style="closeButtonStyle" @click="closeDepenseModal">✕</button>
            </div>

            <div :style="modalBodyStyle">
              <div :style="formGridStyle">
                <div>
                  <label :style="labelStyle">Date</label>
                  <input v-model="form.date_depense" type="date" :style="inputStyle" />
                </div>
                <div>
                  <label :style="labelStyle">Motif</label>
                  <select v-model="form.motif_id" :style="inputStyle">
                    <option value="">—</option>
                    <option v-for="m in motifs" :key="m.id" :value="m.id">{{ m.libelle }}</option>
                  </select>
                </div>
                <div>
                  <label :style="labelStyle">Bénéficiaire</label>
                  <input v-model="form.beneficiaire" type="text" :style="inputStyle" placeholder="Ex: Fournisseur, employé..." />
                </div>
                <div>
                  <label :style="labelStyle">Montant</label>
                  <input v-model.number="form.montant" type="number" min="0" step="1" :style="inputStyle" />
                </div>
                <div style="grid-column: 1 / -1;">
                  <label :style="labelStyle">Description</label>
                  <textarea v-model="form.description" :style="{...inputStyle, minHeight:'90px'}" placeholder="Détails de la dépense..."></textarea>
                </div>
              </div>

              <div :style="piecesBoxStyle">
                <div :style="{display:'flex', alignItems:'center', justifyContent:'space-between', gap:'12px'}">
                  <div>
                    <p :style="{margin:0, fontWeight:'700', color:'#0f172a'}">Pièces justificatives</p>
                    <p :style="{margin:'4px 0 0', color:'#64748b', fontSize:'13px'}">Upload via Cloudinary (PDF/JPG/PNG) puis enregistrement.</p>
                  </div>
                  <div>
                    <input ref="pieceInput" type="file" accept=".pdf,image/*" @change="handlePieceSelected" />
                  </div>
                </div>

                <div v-if="uploadingPiece" :style="{marginTop:'10px', color:'#64748b'}">Upload en cours...</div>
                <div v-if="pieceUploadError" :style="{marginTop:'10px', color:'#ef4444'}">{{ pieceUploadError }}</div>

                <div v-if="form.pieces.length > 0" :style="{marginTop:'12px', display:'grid', gap:'8px'}">
                  <div v-for="(p, idx) in form.pieces" :key="p.url + idx" :style="pieceRowStyle">
                    <a :href="p.url" target="_blank" rel="noreferrer" :style="{color:'#3b82f6', textDecoration:'none', fontWeight:'600'}">
                      {{ p.nom_original || 'Pièce' }}
                    </a>
                    <button :style="smallDangerBtnStyle" @click="removePiece(idx)">Retirer</button>
                  </div>
                </div>
              </div>
            </div>

            <div :style="modalFooterStyle">
              <button :style="secondaryBtnStyle" @click="closeDepenseModal">Annuler</button>
              <button :style="primaryBtnStyle" @click="saveDepense" :disabled="saving">
                <span v-if="!saving">{{ editingDepense ? 'Mettre à jour' : 'Créer' }}</span>
                <span v-else>Enregistrement...</span>
              </button>
            </div>
          </div>
        </div>
      </Transition>

      <!-- Modal Pièces -->
      <Transition name="modal">
        <div v-if="showPiecesModal" :style="modalOverlayStyle" @click.self="closePiecesModal">
          <div :style="{...modalStyle, maxWidth:'900px'}" class="modal-content">
            <div :style="modalHeaderStyle">
              <h2 :style="modalTitleStyle">Pièces - {{ selectedDepense?.motif_libelle || 'Dépense' }}</h2>
              <button :style="closeButtonStyle" @click="closePiecesModal">✕</button>
            </div>
            <div :style="modalBodyStyle">
              <div v-if="piecesLoading" :style="loadingContainerStyle">
                <div :style="spinnerStyle"></div>
                <p :style="loadingTextStyle">Chargement...</p>
              </div>
              <div v-if="!piecesLoading" :style="{display:'grid', gap:'10px'}">
                <div v-for="p in pieces" :key="p.id" :style="pieceCardStyle">
                  <div>
                    <p :style="{margin:'0 0 4px', fontWeight:'700', color:'#0f172a'}">{{ p.nom_original || 'Pièce' }}</p>
                    <p :style="{margin:0, color:'#64748b', fontSize:'13px'}">{{ p.type_fichier || '' }}</p>
                  </div>
                  <div :style="{display:'flex', gap:'10px'}">
                    <a :href="p.url_fichier" target="_blank" rel="noreferrer" :style="linkBtnStyle">Ouvrir</a>
                    <button :style="smallDangerBtnStyle" @click="deletePiece(p)">Supprimer</button>
                  </div>
                </div>
                <div v-if="pieces.length === 0" :style="emptyStateStyle">
                  <p :style="emptyTextStyle">Aucune pièce</p>
                </div>
              </div>
            </div>
            <div :style="modalFooterStyle">
              <button :style="secondaryBtnStyle" @click="closePiecesModal">Fermer</button>
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
import { uploadToCloudinary } from '../../services/cloudinaryService.js'

const router = useRouter()
const authStore = useAuthStore()
const API_BASE_URL = getApiBaseUrl()

const loading = ref(false)
const saving = ref(false)
const motifs = ref([])
const depenses = ref([])
const stats = ref({ total_periode: 0, nb_depenses: 0 })

const filters = ref({
  date_debut: '',
  date_fin: '',
  motif_id: '',
  search: ''
})

const showDepenseModal = ref(false)
const editingDepense = ref(null)
const form = ref(makeEmptyForm())

const showPiecesModal = ref(false)
const selectedDepense = ref(null)
const pieces = ref([])
const piecesLoading = ref(false)

const pieceInput = ref(null)
const uploadingPiece = ref(false)
const pieceUploadError = ref('')

function makeEmptyForm() {
  const today = new Date().toISOString().slice(0, 10)
  return {
    id_depense: null,
    date_depense: today,
    motif_id: '',
    beneficiaire: '',
    description: '',
    montant: 0,
    pieces: [] // {url, nom_original, type_fichier}
  }
}

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

const formatMoney = (amount) => new Intl.NumberFormat('fr-FR', {
  style: 'currency',
  currency: 'XOF',
  maximumFractionDigits: 0
}).format(Number(amount || 0))

const formatDate = (iso) => {
  if (!iso) return '—'
  // accepte yyyy-mm-dd ou timestamp
  const d = new Date(iso)
  if (Number.isNaN(d.getTime())) return String(iso)
  return d.toLocaleDateString('fr-FR')
}

const loadMotifs = async () => {
  try {
    const res = await fetch(
      `${API_BASE_URL}/api_comptabilites.php?action=list_motifs${getUserParams()}${randomParam()}`,
      { headers: getAuthHeaders() }
    )
    const data = await res.json()
    motifs.value = data.success ? (data.data || []) : []
  } catch (e) {
    console.error(e)
    motifs.value = []
  }
}

const loadDepenses = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams()
    if (filters.value.date_debut) params.set('date_debut', filters.value.date_debut)
    if (filters.value.date_fin) params.set('date_fin', filters.value.date_fin)
    if (filters.value.motif_id) params.set('motif_id', filters.value.motif_id)
    if (filters.value.search) params.set('search', filters.value.search)

    const url = `${API_BASE_URL}/api_comptabilites.php?action=list_depenses${getUserParams()}&${params.toString()}${randomParam()}`
    const res = await fetch(url, { headers: getAuthHeaders() })
    const data = await res.json()
    depenses.value = data.success ? (data.data || []) : []
  } catch (e) {
    console.error(e)
    depenses.value = []
  } finally {
    loading.value = false
  }
}

const loadStats = async () => {
  try {
    const params = new URLSearchParams()
    if (filters.value.date_debut) params.set('date_debut', filters.value.date_debut)
    if (filters.value.date_fin) params.set('date_fin', filters.value.date_fin)
    if (filters.value.motif_id) params.set('motif_id', filters.value.motif_id)

    const url = `${API_BASE_URL}/api_comptabilites.php?action=stats_depenses${getUserParams()}&${params.toString()}${randomParam()}`
    const res = await fetch(url, { headers: getAuthHeaders() })
    const data = await res.json()
    stats.value = data.success ? (data.data || { total_periode: 0, nb_depenses: 0 }) : { total_periode: 0, nb_depenses: 0 }
  } catch (e) {
    console.error(e)
    stats.value = { total_periode: 0, nb_depenses: 0 }
  }
}

const reloadAll = async () => {
  await Promise.all([loadMotifs(), loadDepenses(), loadStats()])
}

const resetFilters = () => {
  filters.value = { date_debut: '', date_fin: '', motif_id: '', search: '' }
  reloadAll()
}

const openDepenseModal = (depense = null) => {
  editingDepense.value = depense
  if (!depense) {
    form.value = makeEmptyForm()
  } else {
    form.value = {
      id_depense: depense.id,
      date_depense: depense.date_depense?.slice(0, 10) || new Date().toISOString().slice(0, 10),
      motif_id: depense.motif_id || '',
      beneficiaire: depense.beneficiaire || '',
      description: depense.description || '',
      montant: Number(depense.montant || 0),
      pieces: [] // on ne charge pas ici; via modal pièces
    }
  }
  pieceUploadError.value = ''
  showDepenseModal.value = true
}

const closeDepenseModal = () => {
  showDepenseModal.value = false
  editingDepense.value = null
  form.value = makeEmptyForm()
  pieceUploadError.value = ''
  uploadingPiece.value = false
  if (pieceInput.value) pieceInput.value.value = ''
}

const saveDepense = async () => {
  if (!form.value.date_depense || !form.value.montant) {
    alert('Date et montant requis')
    return
  }

  saving.value = true
  try {
    const action = editingDepense.value ? 'update_depense' : 'add_depense'
    const payload = {
      ...form.value,
      user_id: authStore.user?.id,
      id_entreprise: authStore.user?.id_entreprise
    }
    const res = await fetch(`${API_BASE_URL}/api_comptabilites.php?action=${action}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', ...getAuthHeaders() },
      body: JSON.stringify(payload)
    })
    const data = await res.json()
    if (!data.success) throw new Error(data.error || data.message || 'Erreur')

    // si pièces ajoutées dans le form (nouvelle dépense), enregistrer après création
    const depenseId = data.data?.id_depense || payload.id_depense
    if (depenseId && Array.isArray(form.value.pieces) && form.value.pieces.length > 0) {
      await fetch(`${API_BASE_URL}/api_comptabilites.php?action=add_pieces`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          id_depense: depenseId,
          pieces: form.value.pieces,
          user_id: authStore.user?.id,
          id_entreprise: authStore.user?.id_entreprise
        })
      })
    }

    closeDepenseModal()
    await reloadAll()
  } catch (e) {
    console.error(e)
    alert(e.message || 'Erreur de sauvegarde')
  } finally {
    saving.value = false
  }
}

const deleteDepense = async (d) => {
  if (!confirm('Supprimer cette dépense ?')) return
  try {
    const res = await fetch(`${API_BASE_URL}/api_comptabilites.php?action=delete_depense`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', ...getAuthHeaders() },
      body: JSON.stringify({ id_depense: d.id, user_id: authStore.user?.id, id_entreprise: authStore.user?.id_entreprise })
    })
    const data = await res.json()
    if (!data.success) throw new Error(data.error || data.message || 'Erreur')
    await reloadAll()
  } catch (e) {
    console.error(e)
    alert(e.message || 'Erreur suppression')
  }
}

const openPiecesModal = async (d) => {
  selectedDepense.value = d
  showPiecesModal.value = true
  await loadPieces(d.id)
}

const closePiecesModal = () => {
  showPiecesModal.value = false
  selectedDepense.value = null
  pieces.value = []
  piecesLoading.value = false
}

const loadPieces = async (id_depense) => {
  piecesLoading.value = true
  try {
    const res = await fetch(
      `${API_BASE_URL}/api_comptabilites.php?action=list_pieces&id_depense=${id_depense}${getUserParams()}${randomParam()}`,
      { headers: getAuthHeaders() }
    )
    const data = await res.json()
    pieces.value = data.success ? (data.data || []) : []
  } catch (e) {
    console.error(e)
    pieces.value = []
  } finally {
    piecesLoading.value = false
  }
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
    if (selectedDepense.value?.id) await loadPieces(selectedDepense.value.id)
    await reloadAll()
  } catch (e) {
    console.error(e)
    alert(e.message || 'Erreur suppression')
  }
}

const handlePieceSelected = async (evt) => {
  const file = evt?.target?.files?.[0]
  if (!file) return
  uploadingPiece.value = true
  pieceUploadError.value = ''
  try {
    const url = await uploadToCloudinary(file)
    form.value.pieces.push({
      url,
      nom_original: file.name,
      type_fichier: file.type
    })
  } catch (e) {
    console.error(e)
    pieceUploadError.value = e.message || 'Erreur upload'
  } finally {
    uploadingPiece.value = false
    if (pieceInput.value) pieceInput.value.value = ''
  }
}

const removePiece = (idx) => {
  form.value.pieces.splice(idx, 1)
}

onMounted(async () => {
  await reloadAll()
})

// Styles (inspirés des autres pages)
const headerStyle = { display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '12px', flexWrap: 'wrap', marginBottom: '18px' }
const titleStyle = { fontSize: '32px', fontWeight: '800', color: '#0f172a', margin: '0 0 4px 0' }
const subtitleStyle = { fontSize: '14px', color: '#64748b', margin: 0 }

const primaryBtnStyle = { padding: '12px 18px', background: 'linear-gradient(135deg, #10b981 0%, #059669 100%)', color: 'white', border: 'none', borderRadius: '12px', cursor: 'pointer', fontWeight: '700' }
const secondaryBtnStyle = { padding: '12px 18px', background: '#0f172a', color: 'white', border: 'none', borderRadius: '12px', cursor: 'pointer', fontWeight: '700' }
const dangerBtnStyle = { padding: '12px 18px', background: '#ef4444', color: 'white', border: 'none', borderRadius: '12px', cursor: 'pointer', fontWeight: '700' }
const linkBtnStyle = { background: 'transparent', border: 'none', color: '#3b82f6', cursor: 'pointer', fontWeight: '700', padding: 0 }

const statsGridStyle = { display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(220px, 1fr))', gap: '12px', marginBottom: '16px' }
const statsCardStyle = { background: 'white', border: '1px solid #e2e8f0', borderRadius: '16px', padding: '16px' }
const statsLabelStyle = { margin: 0, color: '#64748b', fontSize: '13px', fontWeight: '700' }
const statsValueStyle = { margin: '8px 0 0', color: '#0f172a', fontSize: '20px', fontWeight: '900' }

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
const amountStyle = { fontWeight: '900', color: '#0f172a' }
const motifBadgeStyle = { background: 'rgba(16,185,129,0.12)', color: '#059669', padding: '4px 10px', borderRadius: '999px', fontWeight: '800', fontSize: '12px' }
const actionBtnStyle = (color) => ({ padding: '8px 10px', borderRadius: '10px', border: 'none', cursor: 'pointer', fontWeight: '800', color: 'white', background: color })

const emptyStateStyle = { padding: '18px', textAlign: 'center' }
const emptyTextStyle = { margin: 0, color: '#94a3b8', fontWeight: '700' }

const loadingContainerStyle = { textAlign: 'center', padding: '40px 16px' }
const loadingTextStyle = { marginTop: '10px', fontSize: '14px', fontWeight: '700', color: '#64748b' }
const spinnerStyle = { width: '40px', height: '40px', margin: '0 auto', border: '4px solid #f1f5f9', borderTop: '4px solid #10b981', borderRadius: '50%', animation: 'spin 1s linear infinite' }

const modalOverlayStyle = { position: 'fixed', inset: 0, background: 'rgba(15, 23, 42, 0.55)', display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '16px', zIndex: 999 }
const modalStyle = { width: '100%', maxWidth: '780px', background: 'white', borderRadius: '18px', border: '1px solid #e2e8f0', overflow: 'hidden' }
const modalHeaderStyle = { display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '14px 16px', borderBottom: '1px solid #e2e8f0' }
const modalTitleStyle = { margin: 0, fontSize: '16px', fontWeight: '900', color: '#0f172a' }
const closeButtonStyle = { border: 'none', background: 'transparent', cursor: 'pointer', fontSize: '18px', fontWeight: '900', color: '#64748b' }
const modalBodyStyle = { padding: '16px' }
const modalFooterStyle = { padding: '14px 16px', borderTop: '1px solid #e2e8f0', display: 'flex', justifyContent: 'flex-end', gap: '10px' }
const formGridStyle = { display: 'grid', gridTemplateColumns: 'repeat(2, minmax(0, 1fr))', gap: '12px' }

const piecesBoxStyle = { marginTop: '14px', padding: '12px', border: '1px dashed #cbd5e1', borderRadius: '14px', background: '#f8fafc' }
const pieceRowStyle = { display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: '10px', padding: '10px', background: 'white', borderRadius: '12px', border: '1px solid #e2e8f0' }
const pieceCardStyle = { display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: '12px', padding: '12px', borderRadius: '14px', border: '1px solid #e2e8f0', background: '#fff' }
const smallDangerBtnStyle = { padding: '8px 10px', background: '#ef4444', color: 'white', border: 'none', borderRadius: '10px', cursor: 'pointer', fontWeight: '900' }
</script>

<style scoped>
@keyframes spin { to { transform: rotate(360deg); } }
.fade-in { animation: fadeIn .2s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
.modal-content { box-shadow: 0 20px 60px rgba(0,0,0,0.25); }
</style>

