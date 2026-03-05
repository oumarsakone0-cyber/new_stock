<template>
  <SidebarLayout currentPage="comptabilites">
    <div class="bancaire-page">
      <!-- Header -->
      <header class="bancaire-header fade-in">
        <div>
          <h1 class="bancaire-title">Comptes Bancaires</h1>
          <p class="bancaire-subtitle">Gerez vos comptes et suivez les soldes (calcules a partir des transactions)</p>
        </div>
        <div class="header-actions">
          <button class="btn btn--dark" @click="goTo('/comptabilites/banque')">Retour</button>
          <button class="btn btn--dark" @click="goTo('/comptabilites/banque/transactions')">Transactions</button>
          <button class="btn btn--primary" @click="openModal()">Nouveau compte</button>
        </div>
      </header>

      <!-- Table -->
      <div class="table-container fade-in">
        <!-- Loading -->
        <div v-if="loading" class="loading-state">
          <div class="spinner"></div>
          <p class="loading-text">Chargement...</p>
        </div>

        <!-- Content -->
        <div v-if="!loading" class="table-wrapper">
          <table class="data-table">
            <thead>
              <tr>
                <th>Banque</th>
                <th>Nom du compte</th>
                <th>N&deg; Compte</th>
                <th>Devise</th>
                <th>Solde initial</th>
                <th>Solde actuel</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="c in comptes" :key="c.id">
                <td>{{ c.banque || '—' }}</td>
                <td><strong>{{ c.nom_compte || '—' }}</strong></td>
                <td>{{ c.numero_compte || '—' }}</td>
                <td>{{ c.devise || 'XOF' }}</td>
                <td>{{ formatMoney(c.solde_initial || 0) }}</td>
                <td>
                  <span :class="['solde', Number(c.solde_actuel || 0) < 0 ? 'solde--negatif' : 'solde--positif']">
                    {{ formatMoney(c.solde_actuel || 0) }}
                  </span>
                </td>
                <td>
                  <div class="row-actions">
                    <button class="btn-action btn-action--edit" @click="openModal(c)">Modifier</button>
                    <button class="btn-action btn-action--delete" @click="deleteCompte(c)">Supprimer</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Empty -->
          <div v-if="comptes.length === 0" class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="empty-icon">
              <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
            <p class="empty-text">Aucun compte bancaire</p>
            <p class="empty-hint">Cliquez sur "Nouveau compte" pour commencer</p>
          </div>
        </div>
      </div>

      <!-- Modal -->
      <Transition name="modal">
        <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
          <div class="modal">
            <div class="modal-header">
              <h2 class="modal-title">{{ editing ? 'Modifier compte' : 'Nouveau compte' }}</h2>
              <button class="modal-close" @click="closeModal">&times;</button>
            </div>
            <div class="modal-body">
              <div class="form-grid">
                <div class="form-group">
                  <label class="form-label">Banque</label>
                  <input v-model="form.banque" type="text" class="form-input" placeholder="Ex: SGBCI" />
                </div>
                <div class="form-group">
                  <label class="form-label">Nom du compte *</label>
                  <input v-model="form.nom_compte" type="text" class="form-input" placeholder="Ex: Compte principal" />
                </div>
                <div class="form-group">
                  <label class="form-label">Numero de compte</label>
                  <input v-model="form.numero_compte" type="text" class="form-input" placeholder="Optionnel" />
                </div>
                <div class="form-group">
                  <label class="form-label">Devise</label>
                  <select v-model="form.devise" class="form-input">
                    <option value="XOF">XOF</option>
                    <option value="EUR">EUR</option>
                    <option value="USD">USD</option>
                  </select>
                </div>
                <div class="form-group form-group--full">
                  <label class="form-label">Solde initial</label>
                  <input v-model.number="form.solde_initial" type="number" step="1" class="form-input" />
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn--dark" @click="closeModal">Annuler</button>
              <button class="btn btn--primary" @click="saveCompte" :disabled="saving">
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
import { ref, onMounted } from 'vue'
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

const showModal = ref(false)
const editing = ref(null)
const form = ref(makeEmpty())

function makeEmpty() {
  return { id_compte: null, banque: '', nom_compte: '', numero_compte: '', devise: 'XOF', solde_initial: 0 }
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

const loadComptes = async () => {
  loading.value = true
  try {
    const res = await fetch(
      `${API_BASE_URL}/api_gestion_bancaire.php?action=list_comptes${getUserParams()}${randomParam()}`,
      { headers: getAuthHeaders() }
    )
    const data = await res.json()
    comptes.value = data.success ? (data.data || []) : []
  } catch (e) {
    console.error(e)
    comptes.value = []
  } finally {
    loading.value = false
  }
}

const openModal = (c = null) => {
  editing.value = c
  form.value = c
    ? { id_compte: c.id, banque: c.banque || '', nom_compte: c.nom_compte || '', numero_compte: c.numero_compte || '', devise: c.devise || 'XOF', solde_initial: Number(c.solde_initial || 0) }
    : makeEmpty()
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editing.value = null
  form.value = makeEmpty()
}

const saveCompte = async () => {
  if (!form.value.nom_compte?.trim()) {
    alert('Nom du compte requis')
    return
  }
  saving.value = true
  try {
    const action = editing.value ? 'update_compte' : 'add_compte'
    const payload = { ...form.value, user_id: authStore.user?.id, id_entreprise: authStore.user?.id_entreprise }
    const res = await fetch(`${API_BASE_URL}/api_gestion_bancaire.php?action=${action}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', ...getAuthHeaders() },
      body: JSON.stringify(payload)
    })
    const data = await res.json()
    if (!data.success) throw new Error(data.error || data.message || 'Erreur')
    closeModal()
    await loadComptes()
  } catch (e) {
    console.error(e)
    alert(e.message || 'Erreur sauvegarde')
  } finally {
    saving.value = false
  }
}

const deleteCompte = async (c) => {
  if (!confirm('Supprimer ce compte ?')) return
  try {
    const res = await fetch(`${API_BASE_URL}/api_gestion_bancaire.php?action=delete_compte`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', ...getAuthHeaders() },
      body: JSON.stringify({ id_compte: c.id, user_id: authStore.user?.id, id_entreprise: authStore.user?.id_entreprise })
    })
    const data = await res.json()
    if (!data.success) throw new Error(data.error || data.message || 'Erreur')
    await loadComptes()
  } catch (e) {
    console.error(e)
    alert(e.message || 'Erreur suppression')
  }
}

onMounted(loadComptes)
</script>

<style scoped>
/* ========== Page ========== */
.bancaire-page {
  padding: 0;
}

/* ========== Header ========== */
.bancaire-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
  margin-bottom: 20px;
}
.bancaire-title {
  font-size: 28px;
  font-weight: 800;
  color: #0f172a;
  margin: 0 0 4px 0;
  letter-spacing: -0.5px;
}
.bancaire-subtitle {
  font-size: 14px;
  color: #64748b;
  margin: 0;
  font-weight: 500;
}
.header-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

/* ========== Buttons ========== */
.btn {
  padding: 10px 18px;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 700;
  font-size: 13px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.btn:hover {
  opacity: 0.9;
  transform: translateY(-1px);
}
.btn:active {
  transform: translateY(0);
}
.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}
.btn--primary {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: #ffffff;
}
.btn--dark {
  background: #0f172a;
  color: #ffffff;
}

/* ========== Table Container ========== */
.table-container {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  overflow: hidden;
}
.table-wrapper {
  overflow-x: auto;
}

/* ========== Table ========== */
.data-table {
  width: 100%;
  border-collapse: collapse;
}
.data-table thead th {
  text-align: left;
  padding: 14px 16px;
  font-size: 12px;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
  white-space: nowrap;
}
.data-table tbody tr {
  transition: background 0.15s ease;
}
.data-table tbody tr:hover {
  background: #f8fafc;
}
.data-table tbody td {
  padding: 14px 16px;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
  font-size: 14px;
  color: #334155;
}

/* ========== Solde ========== */
.solde {
  font-weight: 900;
  font-size: 14px;
}
.solde--positif {
  color: #059669;
}
.solde--negatif {
  color: #ef4444;
}

/* ========== Row Actions ========== */
.row-actions {
  display: flex;
  gap: 8px;
}
.btn-action {
  padding: 7px 12px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  font-weight: 700;
  font-size: 12px;
  color: #ffffff;
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.btn-action:hover {
  opacity: 0.85;
  transform: translateY(-1px);
}
.btn-action--edit {
  background: #3b82f6;
}
.btn-action--delete {
  background: #ef4444;
}

/* ========== Empty State ========== */
.empty-state {
  padding: 48px 16px;
  text-align: center;
}
.empty-icon {
  color: #cbd5e1;
  margin-bottom: 12px;
}
.empty-text {
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
.form-label {
  font-size: 12px;
  font-weight: 700;
  color: #334155;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}
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
}
.form-input:focus {
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
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
  .bancaire-header {
    flex-direction: column;
    align-items: flex-start;
  }
  .bancaire-title {
    font-size: 22px;
  }
  .form-grid {
    grid-template-columns: 1fr;
  }
  .modal {
    max-width: 100%;
  }
}
</style>
