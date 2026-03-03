<template>
  <SidebarLayout currentPage="comptabilites">
    <div class="fade-in">
      <!-- Header -->
      <header class="page-header">
        <div>
          <h1 class="page-title">Transactions Bancaires</h1>
          <p class="page-subtitle">Enregistrer, filtrer et suivre les transactions (debit/credit)</p>
        </div>
        <div class="header-actions">
          <button class="btn btn--dark" @click="goTo('/comptabilites/banque')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
            Retour
          </button>
          <button class="btn btn--dark" @click="goTo('/comptabilites/banque/comptes')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
            Comptes
          </button>
          <button class="btn btn--primary" @click="openModal()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nouvelle transaction
          </button>
        </div>
      </header>

      <!-- Filtres -->
      <div class="filters-card">
        <div class="filter-group">
          <label class="filter-label">Periode</label>
          <div class="date-range">
            <input v-model="filters.date_debut" type="date" class="form-input" />
            <span class="date-sep">a</span>
            <input v-model="filters.date_fin" type="date" class="form-input" />
          </div>
        </div>
        <div class="filter-group">
          <label class="filter-label">Compte</label>
          <select v-model="filters.id_compte" class="form-input">
            <option value="">Tous les comptes</option>
            <option v-for="c in comptes" :key="c.id" :value="c.id">{{ c.nom_compte }}</option>
          </select>
        </div>
        <div class="filter-group">
          <label class="filter-label">Type</label>
          <select v-model="filters.sens" class="form-input">
            <option value="">Tous</option>
            <option value="DEBIT">DEBIT</option>
            <option value="CREDIT">CREDIT</option>
          </select>
        </div>
        <div class="filter-group">
          <label class="filter-label">Recherche</label>
          <div class="search-wrapper">
            <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input v-model="filters.search" type="text" placeholder="Reference, libelle..." class="form-input search-input" />
          </div>
        </div>
        <div class="filter-actions">
          <button class="btn btn--dark btn--sm" @click="loadComptesEtTransactions">Appliquer</button>
          <button class="btn btn--danger btn--sm" @click="resetFilters">Reset</button>
        </div>
      </div>

      <!-- Stats rapides -->
      <div class="stats-bar" v-if="!loading && transactions.length > 0">
        <div class="stat-chip stat-chip--total">
          <span class="stat-chip__label">Total</span>
          <span class="stat-chip__value">{{ transactions.length }}</span>
        </div>
        <div class="stat-chip stat-chip--credit">
          <span class="stat-chip__label">Credits</span>
          <span class="stat-chip__value">{{ formatMoney(totalCredit) }}</span>
        </div>
        <div class="stat-chip stat-chip--debit">
          <span class="stat-chip__label">Debits</span>
          <span class="stat-chip__value">{{ formatMoney(totalDebit) }}</span>
        </div>
      </div>

      <!-- Tableau -->
      <div class="table-card">
        <div v-if="loading" class="loading-state">
          <div class="spinner"></div>
          <p class="loading-text">Chargement des transactions...</p>
        </div>

        <div v-else-if="transactions.length === 0" class="empty-state">
          <svg class="empty-icon" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
          <p class="empty-text">Aucune transaction trouvee</p>
          <p class="empty-hint">Cliquez sur "Nouvelle transaction" pour commencer</p>
        </div>

        <div v-else class="table-wrapper">
          <table class="table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Compte</th>
                <th>Sens</th>
                <th>Montant</th>
                <th>Reference</th>
                <th>Libelle</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="t in transactions" :key="t.id" class="table-row">
                <td>{{ formatDate(t.date_transaction) }}</td>
                <td>{{ t.compte_nom || '--' }}</td>
                <td>
                  <span class="badge" :class="t.sens === 'CREDIT' ? 'badge--credit' : 'badge--debit'">
                    {{ t.sens }}
                  </span>
                </td>
                <td>
                  <span class="amount" :class="t.sens === 'CREDIT' ? 'amount--credit' : 'amount--debit'">
                    {{ formatMoney(t.montant || 0) }}
                  </span>
                </td>
                <td class="td-muted">{{ t.reference || '--' }}</td>
                <td>{{ t.libelle || '--' }}</td>
                <td>
                  <div class="row-actions">
                    <button class="btn-action btn-action--edit" @click="openModal(t)" title="Modifier">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                    </button>
                    <button class="btn-action btn-action--delete" @click="deleteTransaction(t)" title="Supprimer">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Modal -->
      <Transition name="modal">
        <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
          <div class="modal">
            <div class="modal-header">
              <h2 class="modal-title">{{ editing ? 'Modifier transaction' : 'Nouvelle transaction' }}</h2>
              <button class="modal-close" @click="closeModal">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </button>
            </div>
            <div class="modal-body">
              <div class="modal-form-grid">
                <div class="form-group">
                  <label class="form-label">Date *</label>
                  <input v-model="form.date_transaction" type="date" class="form-input" />
                </div>
                <div class="form-group">
                  <label class="form-label">Compte *</label>
                  <select v-model="form.id_compte" class="form-input">
                    <option value="">-- Selectionner --</option>
                    <option v-for="c in comptes" :key="c.id" :value="c.id">{{ c.nom_compte }}</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Sens *</label>
                  <select v-model="form.sens" class="form-input">
                    <option value="DEBIT">DEBIT</option>
                    <option value="CREDIT">CREDIT</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Montant *</label>
                  <input v-model.number="form.montant" type="number" min="0" step="1" class="form-input" />
                </div>
                <div class="form-group">
                  <label class="form-label">Reference</label>
                  <input v-model="form.reference" type="text" class="form-input" placeholder="Optionnel" />
                </div>
                <div class="form-group">
                  <label class="form-label">Libelle</label>
                  <input v-model="form.libelle" type="text" class="form-input" placeholder="Ex: Retrait, Virement..." />
                </div>
                <div class="form-group form-group--full">
                  <label class="form-label">Note</label>
                  <textarea v-model="form.note" class="form-input form-textarea" placeholder="Note optionnelle..."></textarea>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn--dark" @click="closeModal">Annuler</button>
              <button class="btn btn--primary" @click="saveTransaction" :disabled="saving">
                {{ saving ? 'Enregistrement...' : 'Enregistrer' }}
              </button>
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
const comptes = ref([])
const transactions = ref([])

const filters = ref({ date_debut: '', date_fin: '', id_compte: '', sens: '', search: '' })

const showModal = ref(false)
const editing = ref(null)
const form = ref(makeEmpty())

function makeEmpty() {
  const today = new Date().toISOString().slice(0, 10)
  return { id_transaction: null, date_transaction: today, id_compte: '', sens: 'DEBIT', montant: 0, reference: '', libelle: '', note: '' }
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
  if (!iso) return '--'
  const d = new Date(iso)
  if (Number.isNaN(d.getTime())) return String(iso)
  return d.toLocaleDateString('fr-FR')
}

const totalCredit = computed(() =>
  transactions.value
    .filter(t => t.sens === 'CREDIT')
    .reduce((s, t) => s + Number(t.montant || 0), 0)
)
const totalDebit = computed(() =>
  transactions.value
    .filter(t => t.sens === 'DEBIT')
    .reduce((s, t) => s + Number(t.montant || 0), 0)
)

const loadComptes = async () => {
  try {
    const res = await fetch(
      `${API_BASE_URL}/api_gestion_bancaire.php?action=list_comptes${getUserParams()}${randomParam()}`,
      { headers: getAuthHeaders() }
    )
    if (res.status === 401) {
      localStorage.removeItem('token')
      localStorage.removeItem('isAuthenticated')
      router.push('/login')
      return
    }
    const data = await res.json()
    comptes.value = data.success ? (data.data || []) : []
  } catch (e) {
    console.error(e)
    comptes.value = []
  }
}

const loadTransactions = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams()
    if (filters.value.date_debut) params.set('date_debut', filters.value.date_debut)
    if (filters.value.date_fin) params.set('date_fin', filters.value.date_fin)
    if (filters.value.id_compte) params.set('id_compte', filters.value.id_compte)
    if (filters.value.sens) params.set('sens', filters.value.sens)
    if (filters.value.search) params.set('search', filters.value.search)

    const url = `${API_BASE_URL}/api_gestion_bancaire.php?action=list_transactions${getUserParams()}&${params.toString()}${randomParam()}`
    const res = await fetch(url, { headers: getAuthHeaders() })
    if (res.status === 401) {
      localStorage.removeItem('token')
      localStorage.removeItem('isAuthenticated')
      router.push('/login')
      return
    }
    const data = await res.json()
    const raw = data.success ? (data.data || []) : []
    const isTransactions = raw.length === 0 || ('date_transaction' in (raw[0] || {}) && !('banque' in (raw[0] || {})))
    transactions.value = isTransactions ? raw : []
  } catch (e) {
    console.error(e)
    transactions.value = []
  } finally {
    loading.value = false
  }
}

// Charge comptes + transactions. Si transactions vide, rappelle list_transactions (serveur peut avoir ancienne API).
const loadComptesEtTransactions = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams()
    if (filters.value.date_debut) params.set('date_debut', filters.value.date_debut)
    if (filters.value.date_fin) params.set('date_fin', filters.value.date_fin)
    if (filters.value.id_compte) params.set('id_compte', filters.value.id_compte)
    if (filters.value.sens) params.set('sens', filters.value.sens)
    if (filters.value.search) params.set('search', filters.value.search)

    const baseParams = `${getUserParams()}&${params.toString()}${randomParam()}`

    // 1) Appel combine (comptes + transactions)
    const urlCombined = `${API_BASE_URL}/api_gestion_bancaire.php?action=list_comptes_et_transactions${baseParams}`
    const resCombined = await fetch(urlCombined, { headers: getAuthHeaders() })
    if (resCombined.status === 401) {
      localStorage.removeItem('token')
      localStorage.removeItem('isAuthenticated')
      router.push('/login')
      return
    }
    const dataCombined = await resCombined.json()

    if (dataCombined.success && dataCombined.hasOwnProperty('comptes')) {
      comptes.value = Array.isArray(dataCombined.comptes) ? dataCombined.comptes : []
    }
    const txFromCombined = Array.isArray(dataCombined?.transactions) ? dataCombined.transactions : []

    // 2) Si pas de transactions dans la reponse combinee, appeler list_transactions (requete visible dans Network)
    if (txFromCombined.length > 0) {
      transactions.value = txFromCombined
    } else {
      const urlTx = `${API_BASE_URL}/api_gestion_bancaire.php?action=list_transactions${baseParams}`
      const resTx = await fetch(urlTx, { headers: getAuthHeaders() })
      if (resTx.status === 401) {
        localStorage.removeItem('token')
        localStorage.removeItem('isAuthenticated')
        router.push('/login')
        return
      }
      const dataTx = await resTx.json()
      const txList = dataTx.success && Array.isArray(dataTx.data) ? dataTx.data : []
      const isTx = txList.length === 0 || ('date_transaction' in (txList[0] || {}) && !('banque' in (txList[0] || {})))
      transactions.value = isTx ? txList : []
    }
  } catch (e) {
    console.error(e)
    await loadComptes()
    await loadTransactions()
  } finally {
    loading.value = false
  }
}

const resetFilters = () => {
  filters.value = { date_debut: '', date_fin: '', id_compte: '', sens: '', search: '' }
  loadComptesEtTransactions()
}

const openModal = (t = null) => {
  editing.value = t
  if (!t) {
    form.value = makeEmpty()
  } else {
    form.value = {
      id_transaction: t.id,
      date_transaction: t.date_transaction?.slice(0, 10) || new Date().toISOString().slice(0, 10),
      id_compte: t.id_compte || '',
      sens: t.sens || 'DEBIT',
      montant: Number(t.montant || 0),
      reference: t.reference || '',
      libelle: t.libelle || '',
      note: t.note || ''
    }
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editing.value = null
  form.value = makeEmpty()
}

const saveTransaction = async () => {
  if (!form.value.date_transaction || !form.value.id_compte || !form.value.sens || !form.value.montant) {
    alert('Date, compte, sens et montant requis')
    return
  }
  saving.value = true
  try {
    const action = editing.value ? 'update_transaction' : 'add_transaction'
    const payload = { ...form.value, user_id: authStore.user?.id, id_entreprise: authStore.user?.id_entreprise }
    const res = await fetch(`${API_BASE_URL}/api_gestion_bancaire.php?action=${action}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', ...getAuthHeaders() },
      body: JSON.stringify(payload)
    })
    if (res.status === 401) {
      localStorage.removeItem('token')
      localStorage.removeItem('isAuthenticated')
      router.push('/login')
      return
    }
    const data = await res.json()
    if (!data.success) throw new Error(data.error || data.message || 'Erreur')
    closeModal()
    await loadComptesEtTransactions()
  } catch (e) {
    console.error(e)
    alert(e.message || 'Erreur sauvegarde')
  } finally {
    saving.value = false
  }
}

const deleteTransaction = async (t) => {
  if (!confirm('Supprimer cette transaction ?')) return
  try {
    const res = await fetch(`${API_BASE_URL}/api_gestion_bancaire.php?action=delete_transaction`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', ...getAuthHeaders() },
      body: JSON.stringify({ id_transaction: t.id, user_id: authStore.user?.id, id_entreprise: authStore.user?.id_entreprise })
    })
    if (res.status === 401) {
      localStorage.removeItem('token')
      localStorage.removeItem('isAuthenticated')
      router.push('/login')
      return
    }
    const data = await res.json()
    if (!data.success) throw new Error(data.error || data.message || 'Erreur')
    await loadComptesEtTransactions()
  } catch (e) {
    console.error(e)
    alert(e.message || 'Erreur suppression')
  }
}

onMounted(() => {
  // Une seule requete list_comptes_et_transactions : comptes + transactions (reponse garantie avec les deux)
  loadComptesEtTransactions()
})
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
  font-size: 28px;
  font-weight: 800;
  color: #0f172a;
  margin: 0 0 4px 0;
}
.page-subtitle {
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
  gap: 7px;
  padding: 11px 18px;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 700;
  font-size: 13px;
  transition: opacity 0.15s ease, transform 0.1s ease;
}
.btn:hover { opacity: 0.88; }
.btn:active { transform: scale(0.97); }
.btn--primary {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: #fff;
}
.btn--dark {
  background: #0f172a;
  color: #fff;
}
.btn--danger {
  background: #ef4444;
  color: #fff;
}
.btn--sm {
  padding: 9px 14px;
  font-size: 12px;
}
.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* ========== Filters ========== */
.filters-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 16px 20px;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 14px;
  align-items: end;
  margin-bottom: 16px;
}
.filter-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.filter-label {
  font-size: 11px;
  font-weight: 800;
  color: #334155;
  text-transform: uppercase;
  letter-spacing: 0.4px;
}
.date-range {
  display: flex;
  align-items: center;
  gap: 8px;
}
.date-sep {
  font-size: 12px;
  color: #94a3b8;
  font-weight: 600;
}
.search-wrapper {
  position: relative;
}
.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  pointer-events: none;
}
.search-input {
  padding-left: 36px !important;
}
.filter-actions {
  display: flex;
  align-items: end;
  gap: 8px;
}

/* ========== Stats ========== */
.stats-bar {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  margin-bottom: 16px;
}
.stat-chip {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  background: #fff;
}
.stat-chip__label {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  color: #64748b;
}
.stat-chip__value {
  font-size: 14px;
  font-weight: 800;
}
.stat-chip--total .stat-chip__value { color: #0f172a; }
.stat-chip--credit .stat-chip__value { color: #059669; }
.stat-chip--debit .stat-chip__value { color: #ef4444; }

/* ========== Table ========== */
.table-card {
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
  padding: 14px 16px;
  font-size: 11px;
  font-weight: 800;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  border-bottom: 2px solid #e2e8f0;
  white-space: nowrap;
  background: #f8fafc;
}
.table tbody td {
  padding: 14px 16px;
  border-bottom: 1px solid #f1f5f9;
  font-size: 14px;
  color: #0f172a;
  vertical-align: middle;
}
.table-row {
  transition: background 0.12s ease;
}
.table-row:hover {
  background: #f8fafc;
}
.td-muted {
  color: #94a3b8 !important;
}

/* ========== Badge ========== */
.badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.3px;
}
.badge--credit {
  background: rgba(16, 185, 129, 0.1);
  color: #059669;
}
.badge--debit {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}

/* ========== Amount ========== */
.amount {
  font-weight: 800;
  font-size: 14px;
}
.amount--credit { color: #059669; }
.amount--debit { color: #ef4444; }

/* ========== Row Actions ========== */
.row-actions {
  display: flex;
  gap: 6px;
}
.btn-action {
  width: 34px;
  height: 34px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  color: #fff;
  transition: opacity 0.15s ease, transform 0.1s ease;
}
.btn-action:hover { opacity: 0.85; transform: translateY(-1px); }
.btn-action--edit { background: #3b82f6; }
.btn-action--delete { background: #ef4444; }

/* ========== Empty ========== */
.empty-state {
  padding: 56px 16px;
  text-align: center;
}
.empty-icon {
  color: #cbd5e1;
  margin-bottom: 14px;
}
.empty-text {
  margin: 0 0 6px 0;
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
  padding: 56px 16px;
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
  margin-top: 14px;
  font-size: 14px;
  font-weight: 700;
  color: #64748b;
}

/* ========== Form ========== */
.form-input {
  width: 100%;
  padding: 11px 14px;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  outline: none;
  background: #ffffff;
  font-size: 14px;
  color: #0f172a;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
  box-sizing: border-box;
}
.form-input:focus {
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}
.form-label {
  font-size: 12px;
  font-weight: 700;
  color: #334155;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}
.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.form-group--full {
  grid-column: 1 / -1;
}
.form-textarea {
  min-height: 90px;
  resize: vertical;
}

/* ========== Modal ========== */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  z-index: 999;
}
.modal {
  width: 100%;
  max-width: 720px;
  background: #ffffff;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
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
  color: #94a3b8;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: 8px;
  transition: background 0.15s ease, color 0.15s ease;
}
.modal-close:hover {
  background: #fef2f2;
  color: #ef4444;
}
.modal-body {
  padding: 20px;
  max-height: 60vh;
  overflow-y: auto;
}
.modal-form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 14px;
}
.modal-footer {
  padding: 14px 20px;
  border-top: 1px solid #e2e8f0;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
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

/* Modal transition */
.modal-enter-active { transition: opacity 0.2s ease; }
.modal-enter-active .modal { transition: transform 0.2s ease; }
.modal-leave-active { transition: opacity 0.15s ease; }
.modal-leave-active .modal { transition: transform 0.15s ease; }
.modal-enter-from { opacity: 0; }
.modal-enter-from .modal { transform: scale(0.95); }
.modal-leave-to { opacity: 0; }
.modal-leave-to .modal { transform: scale(0.95); }

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
  .date-range {
    flex-direction: column;
    align-items: stretch;
  }
  .date-sep { display: none; }
  .stats-bar {
    flex-direction: column;
  }
  .modal-form-grid {
    grid-template-columns: 1fr;
  }
  .modal {
    max-width: 100%;
  }
}
</style>
