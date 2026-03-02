<template>
  <SidebarLayout currentPage="comptabilites">
    <div class="page">
      <!-- Header -->
      <header class="header fade-in">
        <div>
          <h1 class="title">Gestion des Depenses</h1>
          <p class="subtitle">Depenses, filtres par periode, motifs et pieces justificatives</p>
        </div>
        <div class="header-actions">
          <button class="btn btn--dark" @click="goTo('/comptabilites')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Accueil
          </button>
          <button class="btn btn--dark" @click="openMotifsModal">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h16M4 18h7"/></svg>
            Motifs
          </button>
          <button class="btn btn--dark" @click="goTo('/comptabilites/factures')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            Factures
          </button>
          <button class="btn btn--dark" @click="goTo('/comptabilites/banque')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            Gestion bancaire
          </button>
          <button class="btn btn--primary" @click="openDepenseModal()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nouvelle depense
          </button>
        </div>
      </header>

      <!-- Stats -->
      <div class="stats-grid fade-in">
        <div class="stats-card">
          <div class="stats-icon stats-icon--green">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
          </div>
          <div>
            <p class="stats-label">Total periode</p>
            <p class="stats-value">{{ formatMoney(stats.total_periode) }}</p>
          </div>
        </div>
        <div class="stats-card">
          <div class="stats-icon stats-icon--blue">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
          </div>
          <div>
            <p class="stats-label">Nombre de depenses</p>
            <p class="stats-value">{{ stats.nb_depenses }}</p>
          </div>
        </div>
        <div class="stats-card">
          <div class="stats-icon stats-icon--amber">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h16M4 18h7"/></svg>
          </div>
          <div>
            <p class="stats-label">Motifs</p>
            <p class="stats-value">{{ motifs.length }}</p>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="filters fade-in">
        <div class="filter-group">
          <label class="label">Periode</label>
          <div class="filter-dates">
            <input v-model="filters.date_debut" type="date" class="input" />
            <input v-model="filters.date_fin" type="date" class="input" />
          </div>
        </div>
        <div class="filter-group">
          <label class="label">Motif</label>
          <select v-model="filters.motif_id" class="input">
            <option value="">Tous</option>
            <option v-for="m in motifs" :key="m.id" :value="m.id">{{ m.libelle }}</option>
          </select>
        </div>
        <div class="filter-group">
          <label class="label">Recherche</label>
          <input v-model="filters.search" type="text" placeholder="Reference, description, beneficiaire..." class="input" />
        </div>
        <div class="filter-actions">
          <button class="btn btn--dark" @click="reloadAll">Appliquer</button>
          <button class="btn btn--danger" @click="resetFilters">Reset</button>
        </div>
      </div>

      <!-- Table -->
      <div class="table-container fade-in">
        <div v-if="loading" class="loading-state">
          <div class="spinner"></div>
          <p class="loading-text">Chargement...</p>
        </div>

        <div v-if="!loading" class="table-wrapper">
          <table class="table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Motif</th>
                <th>Beneficiaire</th>
                <th>Description</th>
                <th>Montant</th>
                <th>Pieces</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="d in depenses" :key="d.id">
                <td>{{ formatDate(d.date_depense) }}</td>
                <td>
                  <span class="badge badge--green">{{ d.motif_libelle || '---' }}</span>
                </td>
                <td>{{ d.beneficiaire || '---' }}</td>
                <td class="td-desc">{{ d.description || '---' }}</td>
                <td>
                  <span class="amount">{{ formatMoney(d.montant || 0) }}</span>
                </td>
                <td>
                  <button v-if="(d.nb_pieces || 0) > 0" class="link-btn" @click="openPiecesModal(d)">
                    {{ d.nb_pieces }} piece(s)
                  </button>
                  <span v-else class="text-muted">0</span>
                </td>
                <td>
                  <div class="row-actions">
                    <button class="btn-action btn-action--blue" @click="openDepenseModal(d)">Modifier</button>
                    <button class="btn-action btn-action--red" @click="deleteDepense(d)">Supprimer</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <div v-if="depenses.length === 0" class="empty-state">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="8" y1="15" x2="16" y2="15"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
            <p class="empty-text">Aucune depense trouvee</p>
          </div>
        </div>
      </div>

      <!-- Modal Depense -->
      <Transition name="modal">
        <div v-if="showDepenseModal" class="modal-overlay" @click.self="closeDepenseModal">
          <div class="modal modal--large">
            <div class="modal-header">
              <h2 class="modal-title">{{ editingDepense ? 'Modifier depense' : 'Nouvelle depense' }}</h2>
              <button class="modal-close" @click="closeDepenseModal">&times;</button>
            </div>

            <div class="modal-body">
              <div class="form-grid">
                <div class="form-group">
                  <label class="label">Date</label>
                  <input v-model="form.date_depense" type="date" class="input" />
                </div>
                <div class="form-group">
                  <label class="label">Motif</label>
                  <select v-model="form.motif_id" class="input">
                    <option value="">---</option>
                    <option v-for="m in motifs" :key="m.id" :value="m.id">{{ m.libelle }}</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="label">Beneficiaire</label>
                  <input v-model="form.beneficiaire" type="text" class="input" placeholder="Ex: Fournisseur, employe..." />
                </div>
                <div class="form-group">
                  <label class="label">Montant</label>
                  <input v-model.number="form.montant" type="number" min="0" step="1" class="input" />
                </div>
                <div class="form-group form-group--full">
                  <label class="label">Description</label>
                  <textarea v-model="form.description" class="input textarea" placeholder="Details de la depense..."></textarea>
                </div>
              </div>

              <div class="pieces-box">
                <div class="pieces-header">
                  <div>
                    <p class="pieces-title">Pieces justificatives</p>
                    <p class="pieces-hint">Upload via Cloudinary (PDF/JPG/PNG) puis enregistrement.</p>
                  </div>
                  <div>
                    <input ref="pieceInput" type="file" accept=".pdf,image/*" @change="handlePieceSelected" />
                  </div>
                </div>

                <div v-if="uploadingPiece" class="pieces-status">Upload en cours...</div>
                <div v-if="pieceUploadError" class="pieces-error">{{ pieceUploadError }}</div>

                <div v-if="form.pieces.length > 0" class="pieces-list">
                  <div v-for="(p, idx) in form.pieces" :key="p.url + idx" class="piece-row">
                    <a :href="p.url" target="_blank" rel="noreferrer" class="piece-link">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                      {{ p.nom_original || 'Piece' }}
                    </a>
                    <button class="btn-sm btn-sm--danger" @click="removePiece(idx)">Retirer</button>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button class="btn btn--dark" @click="closeDepenseModal">Annuler</button>
              <button class="btn btn--primary" @click="saveDepense" :disabled="saving">
                <span v-if="!saving">{{ editingDepense ? 'Mettre a jour' : 'Creer' }}</span>
                <span v-else>Enregistrement...</span>
              </button>
            </div>
          </div>
        </div>
      </Transition>

      <!-- Modal Motifs -->
      <Transition name="modal">
        <div v-if="showMotifsModal" class="modal-overlay" @click.self="closeMotifsModal">
          <div class="modal">
            <div class="modal-header">
              <h2 class="modal-title">Motifs de depense</h2>
              <button class="modal-close" @click="closeMotifsModal">&times;</button>
            </div>
            <div class="modal-body">
              <div class="motif-add-row">
                <input v-model="newMotifLibelle" type="text" placeholder="Nouveau motif (libelle)" class="input" />
                <button class="btn btn--primary" @click="addMotif" :disabled="addingMotif">Ajouter</button>
              </div>

              <div v-if="motifs.length === 0" class="empty-state">
                <p class="empty-text">Aucun motif</p>
              </div>
              <div v-else class="motif-list">
                <div v-for="m in motifs" :key="m.id" class="motif-item">
                  <div>
                    <p class="motif-name">{{ m.libelle }}</p>
                    <p class="motif-desc">{{ m.description || '' }}</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn--dark" @click="closeMotifsModal">Fermer</button>
            </div>
          </div>
        </div>
      </Transition>

      <!-- Modal Pieces -->
      <Transition name="modal">
        <div v-if="showPiecesModal" class="modal-overlay" @click.self="closePiecesModal">
          <div class="modal modal--large">
            <div class="modal-header">
              <h2 class="modal-title">Pieces - {{ selectedDepense?.motif_libelle || 'Depense' }}</h2>
              <button class="modal-close" @click="closePiecesModal">&times;</button>
            </div>
            <div class="modal-body">
              <div v-if="piecesLoading" class="loading-state">
                <div class="spinner"></div>
                <p class="loading-text">Chargement...</p>
              </div>
              <div v-if="!piecesLoading" class="pieces-grid">
                <div v-for="p in pieces" :key="p.id" class="piece-card">
                  <div>
                    <p class="piece-card-name">{{ p.nom_original || 'Piece' }}</p>
                    <p class="piece-card-type">{{ p.type_fichier || '' }}</p>
                  </div>
                  <div class="piece-card-actions">
                    <a :href="p.url_fichier" target="_blank" rel="noreferrer" class="link-btn">Ouvrir</a>
                    <button class="btn-sm btn-sm--danger" @click="deletePiece(p)">Supprimer</button>
                  </div>
                </div>
                <div v-if="pieces.length === 0" class="empty-state">
                  <p class="empty-text">Aucune piece</p>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn--dark" @click="closePiecesModal">Fermer</button>
            </div>
          </div>
        </div>
      </Transition>
    </div>
  </SidebarLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
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

const showMotifsModal = ref(false)
const newMotifLibelle = ref('')
const addingMotif = ref(false)

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
    pieces: []
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
  if (!iso) return '---'
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
      pieces: []
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
  if (!confirm('Supprimer cette depense ?')) return
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

const openMotifsModal = async () => {
  showMotifsModal.value = true
  await loadMotifs()
}

const closeMotifsModal = () => {
  showMotifsModal.value = false
  newMotifLibelle.value = ''
}

const addMotif = async () => {
  const lib = (newMotifLibelle.value || '').trim()
  if (!lib) return alert('Libelle requis')
  addingMotif.value = true
  try {
    const res = await fetch(`${API_BASE_URL}/api_comptabilites.php?action=add_motif`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', ...getAuthHeaders() },
      body: JSON.stringify({ libelle: lib, description: '', id_entreprise: authStore.user?.id_entreprise })
    })
    const data = await res.json()
    if (!data.success) throw new Error(data.error || data.message || 'Erreur')
    newMotifLibelle.value = ''
    await loadMotifs()
  } catch (e) {
    console.error(e)
    alert(e.message || 'Erreur ajout motif')
  } finally {
    addingMotif.value = false
  }
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
  if (!confirm('Supprimer cette piece ?')) return
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
</script>

<style scoped>
/* ========== Page ========== */
.page {
  padding: 0;
}

/* ========== Animations ========== */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}
.fade-in {
  animation: fadeIn 0.25s ease-out;
}
@keyframes spin {
  to { transform: rotate(360deg); }
}

/* ========== Header ========== */
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
  margin-bottom: 20px;
}
.title {
  font-size: 28px;
  font-weight: 800;
  color: #0f172a;
  margin: 0 0 4px 0;
}
.subtitle {
  font-size: 14px;
  color: #64748b;
  margin: 0;
}
.header-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

/* ========== Buttons ========== */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 18px;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 700;
  font-size: 14px;
  transition: transform 0.12s ease, box-shadow 0.15s ease;
}
.btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
.btn:active {
  transform: scale(0.97);
}
.btn--primary {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: #ffffff;
}
.btn--dark {
  background: #0f172a;
  color: #ffffff;
}
.btn--danger {
  background: #ef4444;
  color: #ffffff;
}

/* ========== Stats ========== */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 14px;
  margin-bottom: 18px;
}
.stats-card {
  display: flex;
  align-items: center;
  gap: 14px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 18px;
  transition: box-shadow 0.2s ease;
}
.stats-card:hover {
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
}
.stats-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.stats-icon--green {
  background: rgba(16, 185, 129, 0.12);
  color: #059669;
}
.stats-icon--blue {
  background: rgba(59, 130, 246, 0.12);
  color: #3b82f6;
}
.stats-icon--amber {
  background: rgba(245, 158, 11, 0.12);
  color: #d97706;
}
.stats-label {
  margin: 0;
  color: #64748b;
  font-size: 13px;
  font-weight: 700;
}
.stats-value {
  margin: 4px 0 0;
  color: #0f172a;
  font-size: 22px;
  font-weight: 900;
}

/* ========== Filters ========== */
.filters {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 16px;
  display: grid;
  gap: 14px;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  margin-bottom: 18px;
}
.filter-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.filter-dates {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}
.filter-actions {
  display: flex;
  align-items: end;
  gap: 10px;
}

/* ========== Inputs ========== */
.label {
  font-size: 12px;
  font-weight: 800;
  color: #0f172a;
  text-transform: uppercase;
  letter-spacing: 0.02em;
}
.input {
  width: 100%;
  padding: 10px 14px;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  outline: none;
  background: #ffffff;
  font-size: 14px;
  color: #0f172a;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
  box-sizing: border-box;
}
.input:focus {
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
}
.textarea {
  min-height: 90px;
  resize: vertical;
}

/* ========== Table ========== */
.table-container {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  overflow: hidden;
}
.table-wrapper {
  overflow-x: auto;
}
.table {
  width: 100%;
  border-collapse: collapse;
}
.table thead th {
  text-align: left;
  padding: 14px 14px;
  font-size: 12px;
  font-weight: 800;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  border-bottom: 1px solid #e2e8f0;
  white-space: nowrap;
  background: #f8fafc;
}
.table tbody tr {
  transition: background 0.12s ease;
}
.table tbody tr:hover {
  background: #f8fafc;
}
.table tbody td {
  padding: 12px 14px;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: top;
  font-size: 14px;
  color: #334155;
}
.td-desc {
  max-width: 200px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.amount {
  font-weight: 900;
  color: #0f172a;
}
.badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 999px;
  font-weight: 800;
  font-size: 12px;
}
.badge--green {
  background: rgba(16, 185, 129, 0.12);
  color: #059669;
}
.link-btn {
  background: transparent;
  border: none;
  color: #3b82f6;
  cursor: pointer;
  font-weight: 700;
  padding: 0;
  font-size: 13px;
  text-decoration: none;
}
.link-btn:hover {
  text-decoration: underline;
}
.text-muted {
  color: #94a3b8;
}
.row-actions {
  display: flex;
  gap: 8px;
}
.btn-action {
  padding: 6px 12px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  font-weight: 700;
  font-size: 12px;
  color: #ffffff;
  transition: opacity 0.12s ease;
}
.btn-action:hover {
  opacity: 0.85;
}
.btn-action--blue {
  background: #3b82f6;
}
.btn-action--red {
  background: #ef4444;
}

/* ========== Empty State ========== */
.empty-state {
  padding: 40px 16px;
  text-align: center;
}
.empty-text {
  margin: 12px 0 0;
  color: #94a3b8;
  font-weight: 700;
  font-size: 15px;
}

/* ========== Loading ========== */
.loading-state {
  text-align: center;
  padding: 48px 16px;
}
.spinner {
  width: 40px;
  height: 40px;
  margin: 0 auto;
  border: 4px solid #f1f5f9;
  border-top: 4px solid #10b981;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
.loading-text {
  margin-top: 12px;
  font-size: 14px;
  font-weight: 700;
  color: #64748b;
}

/* ========== Modal ========== */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  z-index: 999;
}
.modal {
  width: 100%;
  max-width: 640px;
  background: #ffffff;
  border-radius: 18px;
  border: 1px solid #e2e8f0;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
}
.modal--large {
  max-width: 900px;
}
.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid #e2e8f0;
}
.modal-title {
  margin: 0;
  font-size: 17px;
  font-weight: 800;
  color: #0f172a;
}
.modal-close {
  border: none;
  background: transparent;
  cursor: pointer;
  font-size: 22px;
  font-weight: 700;
  color: #94a3b8;
  line-height: 1;
  transition: color 0.15s ease;
}
.modal-close:hover {
  color: #ef4444;
}
.modal-body {
  padding: 20px;
  max-height: 60vh;
  overflow-y: auto;
}
.modal-footer {
  padding: 14px 20px;
  border-top: 1px solid #e2e8f0;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

/* Modal transitions */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease;
}
.modal-enter-active .modal,
.modal-leave-active .modal {
  transition: transform 0.2s ease;
}
.modal-enter-from {
  opacity: 0;
}
.modal-enter-from .modal {
  transform: scale(0.95) translateY(10px);
}
.modal-leave-to {
  opacity: 0;
}
.modal-leave-to .modal {
  transform: scale(0.95) translateY(10px);
}

/* ========== Form ========== */
.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 14px;
}
.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.form-group--full {
  grid-column: 1 / -1;
}

/* ========== Pieces Box ========== */
.pieces-box {
  margin-top: 16px;
  padding: 16px;
  border: 1px dashed #cbd5e1;
  border-radius: 14px;
  background: #f8fafc;
}
.pieces-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}
.pieces-title {
  margin: 0;
  font-weight: 700;
  color: #0f172a;
}
.pieces-hint {
  margin: 4px 0 0;
  color: #64748b;
  font-size: 13px;
}
.pieces-status {
  margin-top: 10px;
  color: #64748b;
  font-size: 13px;
}
.pieces-error {
  margin-top: 10px;
  color: #ef4444;
  font-size: 13px;
  font-weight: 600;
}
.pieces-list {
  margin-top: 12px;
  display: grid;
  gap: 8px;
}
.piece-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  padding: 10px 12px;
  background: #ffffff;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  flex-wrap: wrap;
}
.piece-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: #3b82f6;
  text-decoration: none;
  font-weight: 600;
  font-size: 13px;
}
.piece-link:hover {
  text-decoration: underline;
}
.pieces-grid {
  display: grid;
  gap: 10px;
}
.piece-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 12px 14px;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  background: #ffffff;
  flex-wrap: wrap;
}
.piece-card-name {
  margin: 0 0 2px;
  font-weight: 700;
  color: #0f172a;
  font-size: 14px;
}
.piece-card-type {
  margin: 0;
  color: #64748b;
  font-size: 13px;
}
.piece-card-actions {
  display: flex;
  gap: 10px;
  align-items: center;
}

/* ========== Small buttons ========== */
.btn-sm {
  padding: 6px 12px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 700;
  font-size: 12px;
  transition: opacity 0.12s ease;
}
.btn-sm:hover {
  opacity: 0.85;
}
.btn-sm--danger {
  background: #ef4444;
  color: #ffffff;
}

/* ========== Motifs Modal ========== */
.motif-add-row {
  display: flex;
  gap: 10px;
  margin-bottom: 14px;
}
.motif-list {
  display: grid;
  gap: 8px;
}
.motif-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 14px;
  border-radius: 12px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  transition: background 0.12s ease;
}
.motif-item:hover {
  background: #f8fafc;
}
.motif-name {
  margin: 0;
  font-weight: 800;
  color: #0f172a;
  font-size: 14px;
}
.motif-desc {
  margin: 2px 0 0;
  color: #64748b;
  font-size: 13px;
}

/* ========== Responsive ========== */
@media (max-width: 768px) {
  .header {
    flex-direction: column;
    align-items: flex-start;
  }
  .title {
    font-size: 22px;
  }
  .stats-grid {
    grid-template-columns: 1fr;
  }
  .filters {
    grid-template-columns: 1fr;
  }
  .form-grid {
    grid-template-columns: 1fr;
  }
  .modal--large {
    max-width: 100%;
  }
  .table tbody td {
    font-size: 13px;
    padding: 10px;
  }
}
</style>
