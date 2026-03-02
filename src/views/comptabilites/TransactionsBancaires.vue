<template>
  <SidebarLayout currentPage="comptabilites">
    <div>
      <header :style="headerStyle" class="fade-in">
        <div>
          <h1 :style="titleStyle">Transactions Bancaires</h1>
          <p :style="subtitleStyle">Enregistrer, filtrer et suivre les transactions (débit/crédit)</p>
        </div>
        <div :style="{display:'flex', gap:'10px', flexWrap:'wrap'}">
          <button :style="secondaryBtnStyle" @click="goTo('/comptabilites/banque')">Retour</button>
          <button :style="secondaryBtnStyle" @click="goTo('/comptabilites/banque/comptes')">Comptes</button>
          <button :style="primaryBtnStyle" @click="openModal()">Nouvelle transaction</button>
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
          <label :style="labelStyle">Compte</label>
          <select v-model="filters.id_compte" :style="inputStyle">
            <option value="">Tous</option>
            <option v-for="c in comptes" :key="c.id" :value="c.id">{{ c.nom_compte }}</option>
          </select>
        </div>
        <div :style="filterBlockStyle">
          <label :style="labelStyle">Type</label>
          <select v-model="filters.sens" :style="inputStyle">
            <option value="">Tous</option>
            <option value="DEBIT">DEBIT</option>
            <option value="CREDIT">CREDIT</option>
          </select>
        </div>
        <div :style="filterBlockStyle">
          <label :style="labelStyle">Recherche</label>
          <input v-model="filters.search" type="text" placeholder="Référence, libellé..." :style="inputStyle" />
        </div>
        <div :style="{display:'flex', alignItems:'end', gap:'10px'}">
          <button :style="secondaryBtnStyle" @click="loadTransactions">Appliquer</button>
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
                <th :style="thStyle">Date</th>
                <th :style="thStyle">Compte</th>
                <th :style="thStyle">Sens</th>
                <th :style="thStyle">Montant</th>
                <th :style="thStyle">Référence</th>
                <th :style="thStyle">Libellé</th>
                <th :style="thStyle">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="t in transactions" :key="t.id" :style="trStyle">
                <td :style="tdStyle">{{ formatDate(t.date_transaction) }}</td>
                <td :style="tdStyle">{{ t.compte_nom || '—' }}</td>
                <td :style="tdStyle">
                  <span :style="sensBadgeStyle(t.sens)">{{ t.sens }}</span>
                </td>
                <td :style="tdStyle">
                  <span :style="amountStyle(t.sens)">{{ formatMoney(t.montant || 0) }}</span>
                </td>
                <td :style="tdStyle">{{ t.reference || '—' }}</td>
                <td :style="tdStyle">{{ t.libelle || '—' }}</td>
                <td :style="tdStyle">
                  <div :style="{display:'flex', gap:'8px'}">
                    <button :style="actionBtnStyle('#3b82f6')" @click="openModal(t)">Modifier</button>
                    <button :style="actionBtnStyle('#ef4444')" @click="deleteTransaction(t)">Supprimer</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <div v-if="transactions.length === 0" :style="emptyStateStyle">
            <p :style="emptyTextStyle">Aucune transaction</p>
          </div>
        </div>
      </div>

      <Transition name="modal">
        <div v-if="showModal" :style="modalOverlayStyle" @click.self="closeModal">
          <div :style="modalStyle" class="modal-content">
            <div :style="modalHeaderStyle">
              <h2 :style="modalTitleStyle">{{ editing ? 'Modifier transaction' : 'Nouvelle transaction' }}</h2>
              <button :style="closeButtonStyle" @click="closeModal">✕</button>
            </div>
            <div :style="modalBodyStyle">
              <div :style="formGridStyle">
                <div>
                  <label :style="labelStyle">Date *</label>
                  <input v-model="form.date_transaction" type="date" :style="inputStyle" />
                </div>
                <div>
                  <label :style="labelStyle">Compte *</label>
                  <select v-model="form.id_compte" :style="inputStyle">
                    <option value="">—</option>
                    <option v-for="c in comptes" :key="c.id" :value="c.id">{{ c.nom_compte }}</option>
                  </select>
                </div>
                <div>
                  <label :style="labelStyle">Sens *</label>
                  <select v-model="form.sens" :style="inputStyle">
                    <option value="DEBIT">DEBIT</option>
                    <option value="CREDIT">CREDIT</option>
                  </select>
                </div>
                <div>
                  <label :style="labelStyle">Montant *</label>
                  <input v-model.number="form.montant" type="number" min="0" step="1" :style="inputStyle" />
                </div>
                <div>
                  <label :style="labelStyle">Référence</label>
                  <input v-model="form.reference" type="text" :style="inputStyle" placeholder="Optionnel" />
                </div>
                <div>
                  <label :style="labelStyle">Libellé</label>
                  <input v-model="form.libelle" type="text" :style="inputStyle" placeholder="Ex: Retrait, Virement, Dépôt..." />
                </div>
                <div style="grid-column: 1 / -1;">
                  <label :style="labelStyle">Note</label>
                  <textarea v-model="form.note" :style="{...inputStyle, minHeight:'90px'}"></textarea>
                </div>
              </div>
            </div>
            <div :style="modalFooterStyle">
              <button :style="secondaryBtnStyle" @click="closeModal">Annuler</button>
              <button :style="primaryBtnStyle" @click="saveTransaction" :disabled="saving">{{ saving ? 'Enregistrement...' : 'Enregistrer' }}</button>
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
  if (!iso) return '—'
  const d = new Date(iso)
  if (Number.isNaN(d.getTime())) return String(iso)
  return d.toLocaleDateString('fr-FR')
}

const sensBadgeStyle = (sens) => ({
  background: sens === 'CREDIT' ? 'rgba(16,185,129,0.12)' : 'rgba(239,68,68,0.12)',
  color: sens === 'CREDIT' ? '#059669' : '#ef4444',
  padding: '4px 10px',
  borderRadius: '999px',
  fontWeight: '900',
  fontSize: '12px'
})

const amountStyle = (sens) => ({
  fontWeight: '900',
  color: sens === 'CREDIT' ? '#059669' : '#ef4444'
})

const loadComptes = async () => {
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
    const data = await res.json()
    transactions.value = data.success ? (data.data || []) : []
  } catch (e) {
    console.error(e)
    transactions.value = []
  } finally {
    loading.value = false
  }
}

const resetFilters = () => {
  filters.value = { date_debut: '', date_fin: '', id_compte: '', sens: '', search: '' }
  loadTransactions()
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
    const data = await res.json()
    if (!data.success) throw new Error(data.error || data.message || 'Erreur')
    closeModal()
    await loadTransactions()
    await loadComptes()
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
    const data = await res.json()
    if (!data.success) throw new Error(data.error || data.message || 'Erreur')
    await loadTransactions()
    await loadComptes()
  } catch (e) {
    console.error(e)
    alert(e.message || 'Erreur suppression')
  }
}

onMounted(async () => {
  await loadComptes()
  await loadTransactions()
})

// Styles
const headerStyle = { display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '12px', flexWrap: 'wrap', marginBottom: '18px' }
const titleStyle = { fontSize: '32px', fontWeight: '800', color: '#0f172a', margin: '0 0 4px 0' }
const subtitleStyle = { fontSize: '14px', color: '#64748b', margin: 0 }
const primaryBtnStyle = { padding: '12px 18px', background: 'linear-gradient(135deg, #10b981 0%, #059669 100%)', color: 'white', border: 'none', borderRadius: '12px', cursor: 'pointer', fontWeight: '700' }
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
const actionBtnStyle = (color) => ({ padding: '8px 10px', borderRadius: '10px', border: 'none', cursor: 'pointer', fontWeight: '800', color: 'white', background: color })
const emptyStateStyle = { padding: '18px', textAlign: 'center' }
const emptyTextStyle = { margin: 0, color: '#94a3b8', fontWeight: '700' }
const loadingContainerStyle = { textAlign: 'center', padding: '40px 16px' }
const loadingTextStyle = { marginTop: '10px', fontSize: '14px', fontWeight: '700', color: '#64748b' }
const spinnerStyle = { width: '40px', height: '40px', margin: '0 auto', border: '4px solid #f1f5f9', borderTop: '4px solid #10b981', borderRadius: '50%', animation: 'spin 1s linear infinite' }
const modalOverlayStyle = { position: 'fixed', inset: 0, background: 'rgba(15, 23, 42, 0.55)', display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '16px', zIndex: 999 }
const modalStyle = { width: '100%', maxWidth: '760px', background: 'white', borderRadius: '18px', border: '1px solid #e2e8f0', overflow: 'hidden' }
const modalHeaderStyle = { display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '14px 16px', borderBottom: '1px solid #e2e8f0' }
const modalTitleStyle = { margin: 0, fontSize: '16px', fontWeight: '900', color: '#0f172a' }
const closeButtonStyle = { border: 'none', background: 'transparent', cursor: 'pointer', fontSize: '18px', fontWeight: '900', color: '#64748b' }
const modalBodyStyle = { padding: '16px' }
const modalFooterStyle = { padding: '14px 16px', borderTop: '1px solid #e2e8f0', display: 'flex', justifyContent: 'flex-end', gap: '10px' }
const formGridStyle = { display: 'grid', gridTemplateColumns: 'repeat(2, minmax(0, 1fr))', gap: '12px' }
</script>

<style scoped>
@keyframes spin { to { transform: rotate(360deg); } }
.fade-in { animation: fadeIn .2s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
.modal-content { box-shadow: 0 20px 60px rgba(0,0,0,0.25); }
</style>

