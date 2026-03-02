<template>
  <SidebarLayout currentPage="comptabilites">
    <div>
      <header :style="headerStyle" class="fade-in">
        <div>
          <h1 :style="titleStyle">Comptes Bancaires</h1>
          <p :style="subtitleStyle">Gérez vos comptes et suivez les soldes (calculés à partir des transactions)</p>
        </div>
        <div :style="{display:'flex', gap:'10px', flexWrap:'wrap'}">
          <button :style="secondaryBtnStyle" @click="goTo('/comptabilites/banque')">Retour</button>
          <button :style="secondaryBtnStyle" @click="goTo('/comptabilites/banque/transactions')">Transactions</button>
          <button :style="primaryBtnStyle" @click="openModal()">Nouveau compte</button>
        </div>
      </header>

      <div :style="tableContainerStyle" class="fade-in">
        <div v-if="loading" :style="loadingContainerStyle">
          <div :style="spinnerStyle"></div>
          <p :style="loadingTextStyle">Chargement...</p>
        </div>

        <div v-if="!loading" :style="tableWrapperStyle">
          <table :style="tableStyle">
            <thead>
              <tr>
                <th :style="thStyle">Banque</th>
                <th :style="thStyle">Nom du compte</th>
                <th :style="thStyle">N° Compte</th>
                <th :style="thStyle">Devise</th>
                <th :style="thStyle">Solde initial</th>
                <th :style="thStyle">Solde actuel</th>
                <th :style="thStyle">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="c in comptes" :key="c.id" :style="trStyle">
                <td :style="tdStyle">{{ c.banque || '—' }}</td>
                <td :style="tdStyle"><strong>{{ c.nom_compte || '—' }}</strong></td>
                <td :style="tdStyle">{{ c.numero_compte || '—' }}</td>
                <td :style="tdStyle">{{ c.devise || 'XOF' }}</td>
                <td :style="tdStyle">{{ formatMoney(c.solde_initial || 0) }}</td>
                <td :style="tdStyle">
                  <span :style="balanceStyle(c.solde_actuel)">{{ formatMoney(c.solde_actuel || 0) }}</span>
                </td>
                <td :style="tdStyle">
                  <div :style="{display:'flex', gap:'8px'}">
                    <button :style="actionBtnStyle('#3b82f6')" @click="openModal(c)">Modifier</button>
                    <button :style="actionBtnStyle('#ef4444')" @click="deleteCompte(c)">Supprimer</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <div v-if="comptes.length === 0" :style="emptyStateStyle">
            <p :style="emptyTextStyle">Aucun compte</p>
          </div>
        </div>
      </div>

      <Transition name="modal">
        <div v-if="showModal" :style="modalOverlayStyle" @click.self="closeModal">
          <div :style="modalStyle" class="modal-content">
            <div :style="modalHeaderStyle">
              <h2 :style="modalTitleStyle">{{ editing ? 'Modifier compte' : 'Nouveau compte' }}</h2>
              <button :style="closeButtonStyle" @click="closeModal">✕</button>
            </div>
            <div :style="modalBodyStyle">
              <div :style="formGridStyle">
                <div>
                  <label :style="labelStyle">Banque</label>
                  <input v-model="form.banque" type="text" :style="inputStyle" placeholder="Ex: SGBCI" />
                </div>
                <div>
                  <label :style="labelStyle">Nom du compte *</label>
                  <input v-model="form.nom_compte" type="text" :style="inputStyle" placeholder="Ex: Compte principal" />
                </div>
                <div>
                  <label :style="labelStyle">Numéro de compte</label>
                  <input v-model="form.numero_compte" type="text" :style="inputStyle" placeholder="Optionnel" />
                </div>
                <div>
                  <label :style="labelStyle">Devise</label>
                  <select v-model="form.devise" :style="inputStyle">
                    <option value="XOF">XOF</option>
                    <option value="EUR">EUR</option>
                    <option value="USD">USD</option>
                  </select>
                </div>
                <div style="grid-column: 1 / -1;">
                  <label :style="labelStyle">Solde initial</label>
                  <input v-model.number="form.solde_initial" type="number" step="1" :style="inputStyle" />
                </div>
              </div>
            </div>
            <div :style="modalFooterStyle">
              <button :style="secondaryBtnStyle" @click="closeModal">Annuler</button>
              <button :style="primaryBtnStyle" @click="saveCompte" :disabled="saving">{{ saving ? 'Enregistrement...' : 'Enregistrer' }}</button>
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

const balanceStyle = (amount) => ({
  fontWeight: '900',
  color: Number(amount || 0) < 0 ? '#ef4444' : '#059669'
})

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

// Styles
const headerStyle = { display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '12px', flexWrap: 'wrap', marginBottom: '18px' }
const titleStyle = { fontSize: '32px', fontWeight: '800', color: '#0f172a', margin: '0 0 4px 0' }
const subtitleStyle = { fontSize: '14px', color: '#64748b', margin: 0 }
const primaryBtnStyle = { padding: '12px 18px', background: 'linear-gradient(135deg, #10b981 0%, #059669 100%)', color: 'white', border: 'none', borderRadius: '12px', cursor: 'pointer', fontWeight: '700' }
const secondaryBtnStyle = { padding: '12px 18px', background: '#0f172a', color: 'white', border: 'none', borderRadius: '12px', cursor: 'pointer', fontWeight: '700' }
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
const labelStyle = { fontSize: '12px', fontWeight: '800', color: '#0f172a' }
const inputStyle = { width: '100%', padding: '10px 12px', borderRadius: '12px', border: '1px solid #e2e8f0', outline: 'none', background: '#fff' }
const formGridStyle = { display: 'grid', gridTemplateColumns: 'repeat(2, minmax(0, 1fr))', gap: '12px' }
</script>

<style scoped>
@keyframes spin { to { transform: rotate(360deg); } }
.fade-in { animation: fadeIn .2s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
.modal-content { box-shadow: 0 20px 60px rgba(0,0,0,0.25); }
</style>

