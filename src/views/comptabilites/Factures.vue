<template>
  <SidebarLayout currentPage="comptabilites">
    <div>
      <!-- Header -->
      <header class="page-header fade-in">
        <div>
          <h1 class="page-title">Factures / Pieces</h1>
          <p class="page-subtitle">Toutes les pieces justificatives enregistrees sur l'application (liees aux depenses)</p>
        </div>
        <div class="header-actions">
          <button class="btn btn--dark" @click="goTo('/comptabilites/depenses')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
            Retour depenses
          </button>
          <button class="btn btn--outline" @click="goTo('/comptabilites/motifs')">Motifs</button>
        </div>
      </header>

      <!-- Filtres -->
      <section class="filters-card fade-in">
        <div class="filter-group">
          <label class="form-label">Periode</label>
          <div class="filter-dates">
            <input v-model="filters.date_debut" type="date" class="form-input" />
            <input v-model="filters.date_fin" type="date" class="form-input" />
          </div>
        </div>
        <div class="filter-group">
          <label class="form-label">Recherche</label>
          <input v-model="filters.search" type="text" placeholder="Nom piece, motif, beneficiaire..." class="form-input" />
        </div>
        <div class="filter-actions">
          <button class="btn btn--primary" @click="loadPieces">Appliquer</button>
          <button class="btn btn--danger-outline" @click="resetFilters">Reset</button>
        </div>
      </section>

      <!-- Table -->
      <section class="table-card fade-in">
        <!-- Loading -->
        <div v-if="loading" class="loading-state">
          <div class="spinner"></div>
          <p class="loading-text">Chargement...</p>
        </div>

        <!-- Contenu -->
        <div v-if="!loading" class="table-wrapper">
          <table class="data-table">
            <thead>
              <tr>
                <th>Date depense</th>
                <th>Motif</th>
                <th>Beneficiaire</th>
                <th>Piece</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in pieces" :key="p.id">
                <td>{{ formatDate(p.date_depense) }}</td>
                <td>
                  <span class="badge badge--green">{{ p.motif_libelle || '--' }}</span>
                </td>
                <td>{{ p.beneficiaire || '--' }}</td>
                <td>
                  <a :href="p.url_fichier" target="_blank" rel="noreferrer" class="file-link">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    {{ p.nom_original || 'Piece' }}
                  </a>
                  <div class="file-type">{{ p.type_fichier || '' }}</div>
                </td>
                <td>
                  <div class="row-actions">
                    <button class="btn btn--sm btn--dark" @click="goToDepense(p.id_depense)">Voir depense</button>
                    <button class="btn btn--sm btn--danger" @click="deletePiece(p)">Supprimer</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Empty state -->
          <div v-if="pieces.length === 0" class="empty-state">
            <div class="empty-icon">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            </div>
            <p class="empty-title">Aucune piece trouvee</p>
            <p class="empty-hint">Ajoutez des pieces justificatives depuis les depenses</p>
          </div>
        </div>
      </section>
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
  if (!iso) return '--'
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
  if (!confirm('Supprimer cette piece ?')) return
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
  router.push('/comptabilites/depenses')
}

onMounted(loadPieces)
</script>

<style scoped>
/* ========== Header ========== */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
  margin-bottom: 20px;
}
.page-title {
  margin: 0 0 4px 0;
  font-size: 28px;
  font-weight: 800;
  color: #0f172a;
}
.page-subtitle {
  margin: 0;
  font-size: 14px;
  color: #64748b;
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
  gap: 6px;
  padding: 11px 18px;
  border: none;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.15s ease, transform 0.1s ease, box-shadow 0.15s ease;
}
.btn:active {
  transform: scale(0.97);
}
.btn--primary {
  background: #10b981;
  color: #ffffff;
}
.btn--primary:hover {
  background: #059669;
}
.btn--dark {
  background: #0f172a;
  color: #ffffff;
}
.btn--dark:hover {
  background: #1e293b;
}
.btn--outline {
  background: #ffffff;
  color: #0f172a;
  border: 1px solid #e2e8f0;
}
.btn--outline:hover {
  background: #f8fafc;
}
.btn--danger {
  background: #ef4444;
  color: #ffffff;
}
.btn--danger:hover {
  background: #dc2626;
}
.btn--danger-outline {
  background: #ffffff;
  color: #ef4444;
  border: 1px solid #fecaca;
}
.btn--danger-outline:hover {
  background: #fef2f2;
}
.btn--sm {
  padding: 7px 12px;
  font-size: 12px;
  border-radius: 8px;
}

/* ========== Filters ========== */
.filters-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 16px;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 14px;
  align-items: end;
  margin-bottom: 18px;
}
.filter-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.filter-dates {
  display: flex;
  gap: 8px;
}
.filter-actions {
  display: flex;
  align-items: flex-end;
  gap: 8px;
}

/* ========== Form ========== */
.form-label {
  font-size: 12px;
  font-weight: 700;
  color: #334155;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}
.form-input {
  width: 100%;
  padding: 10px 12px;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  outline: none;
  background: #ffffff;
  font-size: 14px;
  color: #0f172a;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.form-input:focus {
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

/* ========== Table ========== */
.table-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  overflow: hidden;
}
.table-wrapper {
  overflow-x: auto;
}
.data-table {
  width: 100%;
  border-collapse: collapse;
}
.data-table thead th {
  text-align: left;
  padding: 14px 16px;
  font-size: 11px;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 1px solid #e2e8f0;
  white-space: nowrap;
  background: #f8fafc;
}
.data-table tbody td {
  padding: 14px 16px;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
  font-size: 14px;
  color: #334155;
}
.data-table tbody tr {
  transition: background 0.12s ease;
}
.data-table tbody tr:hover {
  background: #f8fafc;
}
.row-actions {
  display: flex;
  gap: 6px;
}

/* ========== Badge ========== */
.badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
}
.badge--green {
  background: rgba(16, 185, 129, 0.1);
  color: #059669;
}

/* ========== File link ========== */
.file-link {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  color: #3b82f6;
  font-weight: 700;
  text-decoration: none;
  font-size: 14px;
  transition: color 0.15s ease;
}
.file-link:hover {
  color: #2563eb;
  text-decoration: underline;
}
.file-type {
  color: #94a3b8;
  font-size: 12px;
  margin-top: 2px;
}

/* ========== Empty state ========== */
.empty-state {
  padding: 48px 16px;
  text-align: center;
}
.empty-icon {
  margin-bottom: 12px;
}
.empty-title {
  margin: 0 0 4px 0;
  color: #64748b;
  font-weight: 700;
  font-size: 16px;
}
.empty-hint {
  margin: 0;
  color: #94a3b8;
  font-size: 13px;
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

/* ========== Animations ========== */
.fade-in {
  animation: fadeIn 0.25s ease-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}
@keyframes spin {
  to { transform: rotate(360deg); }
}

/* ========== Responsive ========== */
@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
  }
  .page-title {
    font-size: 22px;
  }
  .filters-card {
    grid-template-columns: 1fr;
  }
  .filter-dates {
    flex-direction: column;
  }
}
</style>
