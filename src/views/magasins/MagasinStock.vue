<template>
  <SidebarLayout currentPage="stock" :magasinId="magasinId">
    <!-- Header -->
    <header :style="headerStyle">
      <div :style="headerLeftStyle">
        <button :style="backButtonStyle" @click="goBack">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
          </svg>
          Retour
        </button>
        <div>
          <h1 :style="titleStyle">Gestion du Stock</h1>
          <p :style="subtitleStyle">{{ magasin.nom }}</p>
        </div>
      </div>
      <div :style="headerActionsStyle">
        <button :style="entryButtonStyle" @click="openModal('entree')" :disabled="loading">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 5v14M5 12h14"/>
          </svg>
          Entrée stock
        </button>
        <button :style="exitButtonStyle" @click="openModal('sortie')" :disabled="loading">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M5 12h14"/>
          </svg>
          Sortie stock
        </button>
      </div>
    </header>

    <!-- Loading State -->
    <div v-if="loading" :style="loadingContainerStyle">
      <div :style="spinnerStyle"></div>
      <p :style="loadingTextStyle">Chargement des données...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" :style="errorContainerStyle">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      <p :style="errorTextStyle">{{ error }}</p>
      <button :style="retryButtonStyle" @click="loadAllData">Réessayer</button>
    </div>

    <!-- Content -->
    <template v-else>
      <!-- Rapport de période -->
      <StockRapportPeriode 
        :magasinId="magasinId"
        :apiBaseUrl="API_BASE_URL"
        @rapport-generated="handleRapportGenerated"
      />

      <!-- Summary Cards -->
      <div :style="summaryGridStyle">
        <div :style="summaryCardStyle">
          <div :style="getSummaryIconStyle('#3b82f6')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
              <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
            </svg>
          </div>
          <div>
            <p :style="summaryLabelStyle">Total produits</p>
            <p :style="summaryValueStyle">{{ stats.stock_total || 0 }}</p>
          </div>
        </div>
        
        <div :style="summaryCardStyle">
          <div :style="getSummaryIconStyle('#10b981')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
              <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>
            </svg>
          </div>
          <div>
            <p :style="summaryLabelStyle">Entrées ce mois</p>
            <p :style="summaryValueStyle">{{ stats.entrees_mois || 0 }}</p>
          </div>
        </div>
        
        <div :style="summaryCardStyle">
          <div :style="getSummaryIconStyle('#f59e0b')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
              <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/>
            </svg>
          </div>
          <div>
            <p :style="summaryLabelStyle">Sorties ce mois</p>
            <p :style="summaryValueStyle">{{ stats.sorties_mois || 0 }}</p>
          </div>
        </div>
        
        <div :style="summaryCardStyle">
          <div :style="getSummaryIconStyle('#ef4444')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
              <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
              <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </div>
          <div>
            <p :style="summaryLabelStyle">Stock bas</p>
            <p :style="summaryValueStyle">{{ stats.stock_bas || 0 }}</p>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div :style="tabsContainerStyle">
        <button 
          v-for="tab in tabs" 
          :key="tab.id"
          :style="getTabStyle(tab.id)"
          @click="activeTab = tab.id"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Stock actuel -->
      <div v-if="activeTab === 'stock'" :style="tableContainerStyle">
        <div v-if="produits.length === 0" :style="emptyStateStyle">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2">
            <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
          </svg>
          <p :style="emptyTextStyle">Aucun produit en stock</p>
        </div>
        
        <div v-else :style="tableWrapperStyle">
          <table :style="tableStyle">
            <thead>
              <tr>
                <th :style="thStyle">Produit</th>
                <th :style="thStyle">Stock actuel</th>
                <th :style="thStyle">Seuil alerte</th>
                <th :style="thStyle">Statut</th>
                <th :style="thStyle">Dernière MAJ</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="produit in produits" :key="produit.id" :style="trStyle">
                <td :style="tdStyle">
                  <p :style="productNameStyle">{{ produit.nom }}</p>
                  <p :style="productSkuStyle">{{ produit.sku }}</p>
                </td>
                <td :style="tdStyle">
                  <span :style="stockValueStyle">{{ produit.stock_actuel }}</span> unités
                </td>
                <td :style="tdStyle">{{ produit.seuil_alerte }} unités</td>
                <td :style="tdStyle">
                  <span :style="getStatusBadgeStyle(produit.statut)">
                    {{ produit.statut }}
                  </span>
                </td>
                <td :style="tdStyle">{{ formatDate(produit.derniere_maj) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Historique des mouvements -->
      <div v-if="activeTab === 'historique'" :style="tableContainerStyle">
        <div v-if="mouvements.length === 0" :style="emptyStateStyle">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2">
            <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <p :style="emptyTextStyle">Aucun mouvement de stock</p>
        </div>
        
        <div v-else :style="tableWrapperStyle">
          <table :style="tableStyle">
            <thead>
              <tr>
                <th :style="thStyle">Date</th>
                <th :style="thStyle">Type</th>
                <th :style="thStyle">Produit</th>
                <th :style="thStyle">Quantité</th>
                <th :style="thStyle">Motif</th>
                <th :style="thStyle">Par</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="mvt in mouvements" :key="mvt.id" :style="trStyle">
                <td :style="tdStyle">{{ formatDate(mvt.date_mouvement) }}</td>
                <td :style="tdStyle">
                  <span :style="getTypeBadgeStyle(mvt.type_mouvement)">
                    {{ mvt.type_mouvement === 'entree' ? 'Entrée' : 'Sortie' }}
                  </span>
                </td>
                <td :style="tdStyle">{{ mvt.produit_nom }}</td>
                <td :style="tdStyle">
                  <span :style="getQuantityStyle(mvt.type_mouvement)">
                    {{ mvt.type_mouvement === 'entree' ? '+' : '-' }}{{ mvt.quantite }}
                  </span>
                </td>
                <td :style="tdStyle">{{ mvt.motif || 'N/A' }}</td>
                <td :style="tdStyle">{{ mvt.utilisateur || 'Système' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>

    <!-- Modal Entrée/Sortie Stock -->
    <div v-if="showModal" :style="modalOverlayStyle" @click.self="closeModal">
      <div :style="modalStyle">
        <div :style="modalHeaderStyle">
          <h2 :style="modalTitleStyle">
            {{ modalType === 'entree' ? 'Entrée de stock' : 'Sortie de stock' }}
          </h2>
          <button :style="closeButtonStyle" @click="closeModal">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
          </button>
        </div>
        
        <form @submit.prevent="saveMovement" :style="formStyle">
          <div :style="formGroupStyle">
            <label :style="labelStyle">Produit *</label>
            <select v-model="formData.produit_id" :style="inputStyle" required>
              <option value="">Sélectionner un produit</option>
              <option v-for="p in produits" :key="p.id" :value="p.id">
                {{ p.nom }} (Stock: {{ p.stock_actuel }})
              </option>
            </select>
          </div>
          
          <div :style="formGroupStyle">
            <label :style="labelStyle">Quantité *</label>
            <input 
              v-model.number="formData.quantite" 
              type="number" 
              min="1" 
              :style="inputStyle" 
              required 
            />
          </div>
          
          <div :style="formGroupStyle">
            <label :style="labelStyle">Motif</label>
            <select v-model="formData.motif" :style="inputStyle">
              <option v-if="modalType === 'entree'" value="Achat fournisseur">Achat fournisseur</option>
              <option v-if="modalType === 'entree'" value="Retour client">Retour client</option>
              <option v-if="modalType === 'entree'" value="Transfert entrepôt">Transfert entrepôt</option>
              <option v-if="modalType === 'sortie'" value="Vente">Vente</option>
              <option v-if="modalType === 'sortie'" value="Perte/Casse">Perte/Casse</option>
              <option v-if="modalType === 'sortie'" value="Transfert">Transfert</option>
              <option value="Autre">Autre</option>
            </select>
          </div>
          
          <div :style="formGroupStyle">
            <label :style="labelStyle">Notes</label>
            <textarea v-model="formData.notes" :style="textareaStyle" rows="2"></textarea>
          </div>
          
          <div :style="formActionsStyle">
            <button type="button" :style="cancelButtonStyle" @click="closeModal">
              Annuler
            </button>
            <button type="submit" :style="getSubmitButtonStyle()" :disabled="saving">
              <div v-if="saving" :style="smallSpinnerStyle"></div>
              <span v-else>
                {{ modalType === 'entree' ? 'Valider entrée' : 'Valider sortie' }}
              </span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </SidebarLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import SidebarLayout from '../SidebarLayout.vue'
import StockRapportPeriode from './StockRapportPeriode.vue'

const router = useRouter()
const route = useRoute()

// API Configuration
const API_BASE_URL = 'https://sogetrag.com/apistok'

// State
const magasinId = computed(() => route.params.id)
const magasin = ref({ id: magasinId.value, nom: '' })
const activeTab = ref('stock')
const showModal = ref(false)
const modalType = ref('entree')
const loading = ref(false)
const saving = ref(false)
const error = ref(null)

const tabs = [
  { id: 'stock', label: 'Stock actuel' },
  { id: 'historique', label: 'Historique mouvements' }
]

const produits = ref([])
const mouvements = ref([])
const stats = ref({
  total_produits: 0,
  stock_total: 0,
  entrees_mois: 0,
  sorties_mois: 0,
  stock_bas: 0
})

const formData = reactive({
  produit_id: '',
  quantite: 1,
  motif: '',
  notes: ''
})

// Methods
const goBack = () => router.push(`/magasins/${magasinId.value}`)

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit'
  })
}

const loadMagasinInfo = async () => {
  try {
    const response = await fetch(`${API_BASE_URL}/api_magasins.php?action=details&id=${magasinId.value}`)
    const data = await response.json()
    
    if (data.success && data.data) {
      magasin.value = data.data
    }
  } catch (err) {
    console.error('Erreur chargement magasin:', err)
  }
}

const loadEtatStock = async () => {
  try {
    const response = await fetch(`${API_BASE_URL}/api_stock.php?action=etat&magasin_id=${magasinId.value}`)
    const data = await response.json()
    
    if (data.success) {
      produits.value = data.data || []
    } else {
      throw new Error(data.error || 'Erreur chargement stock')
    }
  } catch (err) {
    console.error('Erreur chargement stock:', err)
    throw err
  }
}

const loadMouvements = async () => {
  try {
    const response = await fetch(`${API_BASE_URL}/api_stock.php?action=mouvements&magasin_id=${magasinId.value}`)
    const data = await response.json()
    
    if (data.success) {
      mouvements.value = data.data || []
    } else {
      throw new Error(data.error || 'Erreur chargement mouvements')
    }
  } catch (err) {
    console.error('Erreur chargement mouvements:', err)
    throw err
  }
}

const loadStats = async () => {
  try {
    const response = await fetch(`${API_BASE_URL}/api_stock.php?action=stats&magasin_id=${magasinId.value}`)
    const data = await response.json()
    
    if (data.success) {
      stats.value = data.data || stats.value
    } else {
      throw new Error(data.error || 'Erreur chargement statistiques')
    }
  } catch (err) {
    console.error('Erreur chargement stats:', err)
    throw err
  }
}

const loadAllData = async () => {
  loading.value = true
  error.value = null
  
  try {
    await Promise.all([
      loadMagasinInfo(),
      loadEtatStock(),
      loadMouvements(),
      loadStats()
    ])
  } catch (err) {
    error.value = err.message || 'Erreur de chargement des données'
  } finally {
    loading.value = false
  }
}

const openModal = (type) => {
  modalType.value = type
  formData.motif = type === 'entree' ? 'Achat fournisseur' : 'Vente'
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  Object.assign(formData, {
    produit_id: '',
    quantite: 1,
    motif: '',
    notes: ''
  })
}

const saveMovement = async () => {
  saving.value = true
  
  try {
    const action = modalType.value === 'entree' ? 'entree' : 'sortie'
    const response = await fetch(`${API_BASE_URL}/api_stock.php?action=${action}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        produit_id: formData.produit_id,
        magasin_id: magasinId.value,
        quantite: formData.quantite,
        motif: formData.motif,
        notes: formData.notes,
        utilisateur: 'Admin'
      })
    })
    
    const data = await response.json()
    
    if (data.success) {
      alert(data.message || 'Mouvement enregistré avec succès')
      closeModal()
      await loadAllData()
    } else {
      alert(data.error || 'Erreur lors de l\'enregistrement')
    }
  } catch (err) {
    console.error('Erreur sauvegarde mouvement:', err)
    alert('Erreur de connexion au serveur')
  } finally {
    saving.value = false
  }
}

const getStatusText = (stock, seuil) => {
  if (stock === 0) return 'Rupture'
  if (stock <= seuil) return 'Stock bas'
  return 'OK'
}

const handleRapportGenerated = (rapportData) => {
  console.log('Rapport généré:', rapportData)
}

// Lifecycle
onMounted(() => {
  loadAllData()
})

// Styles
const headerStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'flex-start',
  marginBottom: '24px',
  flexWrap: 'wrap',
  gap: '16px'
}

const headerLeftStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '16px',
  flexWrap: 'wrap'
}

const headerActionsStyle = {
  display: 'flex',
  gap: '12px',
  flexWrap: 'wrap'
}

const backButtonStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '8px',
  padding: '10px 16px',
  backgroundColor: '#fff',
  border: '1px solid #e2e8f0',
  borderRadius: '8px',
  cursor: 'pointer',
  fontSize: '14px',
  color: '#475569',
  transition: 'all 0.2s'
}

const titleStyle = {
  fontSize: '28px',
  fontWeight: '700',
  color: '#1e293b',
  margin: '0'
}

const subtitleStyle = {
  fontSize: '14px',
  color: '#64748b',
  margin: '4px 0 0 0'
}

const entryButtonStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '8px',
  padding: '12px 20px',
  backgroundColor: '#10b981',
  color: 'white',
  border: 'none',
  borderRadius: '10px',
  cursor: 'pointer',
  fontSize: '14px',
  fontWeight: '600',
  transition: 'all 0.2s'
}

const exitButtonStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '8px',
  padding: '12px 20px',
  backgroundColor: '#f59e0b',
  color: 'white',
  border: 'none',
  borderRadius: '10px',
  cursor: 'pointer',
  fontSize: '14px',
  fontWeight: '600',
  transition: 'all 0.2s'
}

const loadingContainerStyle = {
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  justifyContent: 'center',
  padding: '60px 20px',
  backgroundColor: 'white',
  borderRadius: '16px',
  boxShadow: '0 1px 3px rgba(0,0,0,0.1)'
}

const spinnerStyle = {
  width: '48px',
  height: '48px',
  border: '4px solid #f1f5f9',
  borderTop: '4px solid #3b82f6',
  borderRadius: '50%',
  animation: 'spin 1s linear infinite'
}

const loadingTextStyle = {
  marginTop: '16px',
  fontSize: '14px',
  color: '#64748b'
}

const errorContainerStyle = {
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  justifyContent: 'center',
  padding: '60px 20px',
  backgroundColor: 'white',
  borderRadius: '16px',
  boxShadow: '0 1px 3px rgba(0,0,0,0.1)',
  gap: '16px'
}

const errorTextStyle = {
  fontSize: '16px',
  color: '#ef4444',
  fontWeight: '500'
}

const retryButtonStyle = {
  padding: '10px 20px',
  backgroundColor: '#3b82f6',
  color: 'white',
  border: 'none',
  borderRadius: '8px',
  cursor: 'pointer',
  fontSize: '14px',
  fontWeight: '600'
}

const summaryGridStyle = {
  display: 'grid',
  gridTemplateColumns: 'repeat(auto-fit, minmax(180px, 1fr))',
  gap: '16px',
  marginBottom: '24px'
}

const summaryCardStyle = {
  backgroundColor: 'white',
  borderRadius: '12px',
  padding: '20px',
  display: 'flex',
  alignItems: 'center',
  gap: '16px',
  boxShadow: '0 1px 3px rgba(0,0,0,0.1)',
  border: '1px solid #e2e8f0'
}

const getSummaryIconStyle = (color) => ({
  width: '48px',
  height: '48px',
  borderRadius: '12px',
  backgroundColor: color,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  flexShrink: '0'
})

const summaryLabelStyle = {
  fontSize: '13px',
  color: '#64748b',
  margin: '0 0 4px 0'
}

const summaryValueStyle = {
  fontSize: '24px',
  fontWeight: '700',
  color: '#1e293b',
  margin: '0'
}

const tabsContainerStyle = {
  display: 'flex',
  gap: '8px',
  marginBottom: '16px',
  backgroundColor: '#e2e8f0',
  padding: '4px',
  borderRadius: '10px',
  width: 'fit-content',
  flexWrap: 'wrap'
}

const getTabStyle = (tabId) => ({
  padding: '10px 20px',
  border: 'none',
  borderRadius: '8px',
  fontSize: '14px',
  fontWeight: '500',
  cursor: 'pointer',
  backgroundColor: activeTab.value === tabId ? 'white' : 'transparent',
  color: activeTab.value === tabId ? '#1e293b' : '#64748b',
  boxShadow: activeTab.value === tabId ? '0 1px 3px rgba(0,0,0,0.1)' : 'none',
  transition: 'all 0.2s'
})

const tableContainerStyle = {
  backgroundColor: 'white',
  borderRadius: '16px',
  overflow: 'hidden',
  boxShadow: '0 1px 3px rgba(0,0,0,0.1)',
  border: '1px solid #e2e8f0'
}

const tableWrapperStyle = {
  overflowX: 'auto'
}

const tableStyle = {
  width: '100%',
  borderCollapse: 'collapse',
  minWidth: '600px'
}

const thStyle = {
  textAlign: 'left',
  padding: '16px',
  fontSize: '12px',
  fontWeight: '600',
  color: '#64748b',
  textTransform: 'uppercase',
  backgroundColor: '#f8fafc',
  borderBottom: '1px solid #e2e8f0',
  whiteSpace: 'nowrap'
}

const trStyle = {
  borderBottom: '1px solid #f1f5f9',
  transition: 'background-color 0.2s'
}

const tdStyle = {
  padding: '16px',
  fontSize: '14px',
  color: '#1e293b'
}

const productNameStyle = {
  margin: '0',
  fontWeight: '500'
}

const productSkuStyle = {
  margin: '2px 0 0 0',
  fontSize: '12px',
  color: '#94a3b8'
}

const stockValueStyle = {
  fontWeight: '700',
  fontSize: '16px'
}

const getStatusBadgeStyle = (statut) => ({
  padding: '4px 10px',
  borderRadius: '20px',
  fontSize: '12px',
  fontWeight: '500',
  backgroundColor: statut === 'Rupture' ? '#fee2e2' : statut === 'Stock bas' ? '#fef3c7' : '#dcfce7',
  color: statut === 'Rupture' ? '#991b1b' : statut === 'Stock bas' ? '#92400e' : '#166534',
  whiteSpace: 'nowrap'
})

const getTypeBadgeStyle = (type) => ({
  padding: '4px 10px',
  borderRadius: '20px',
  fontSize: '12px',
  fontWeight: '500',
  backgroundColor: type === 'entree' ? '#dcfce7' : '#fef3c7',
  color: type === 'entree' ? '#166534' : '#92400e'
})

const getQuantityStyle = (type) => ({
  fontWeight: '600',
  color: type === 'entree' ? '#10b981' : '#f59e0b'
})

const emptyStateStyle = {
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  justifyContent: 'center',
  padding: '60px 20px',
  gap: '16px'
}

const emptyTextStyle = {
  fontSize: '16px',
  color: '#94a3b8',
  fontWeight: '500'
}

const modalOverlayStyle = {
  position: 'fixed',
  top: 0,
  left: 0,
  right: 0,
  bottom: 0,
  backgroundColor: 'rgba(0,0,0,0.5)',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  zIndex: 1000,
  padding: '20px'
}

const modalStyle = {
  backgroundColor: 'white',
  borderRadius: '20px',
  width: '100%',
  maxWidth: '480px',
  maxHeight: '90vh',
  overflow: 'auto'
}

const modalHeaderStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  padding: '24px 24px 0'
}

const modalTitleStyle = {
  fontSize: '20px',
  fontWeight: '600',
  color: '#1e293b',
  margin: 0
}

const closeButtonStyle = {
  background: 'none',
  border: 'none',
  cursor: 'pointer',
  color: '#64748b'
}

const formStyle = {
  padding: '24px'
}

const formGroupStyle = {
  marginBottom: '16px'
}

const labelStyle = {
  display: 'block',
  fontSize: '14px',
  fontWeight: '500',
  color: '#374151',
  marginBottom: '8px'
}

const inputStyle = {
  width: '100%',
  padding: '12px',
  border: '1px solid #d1d5db',
  borderRadius: '10px',
  fontSize: '14px',
  boxSizing: 'border-box'
}

const textareaStyle = {
  ...inputStyle,
  resize: 'vertical'
}

const formActionsStyle = {
  display: 'flex',
  gap: '12px',
  marginTop: '24px'
}

const cancelButtonStyle = {
  flex: 1,
  padding: '12px',
  backgroundColor: '#f1f5f9',
  border: 'none',
  borderRadius: '10px',
  fontSize: '14px',
  fontWeight: '500',
  color: '#64748b',
  cursor: 'pointer'
}

const getSubmitButtonStyle = () => ({
  flex: 1,
  padding: '12px',
  backgroundColor: modalType.value === 'entree' ? '#10b981' : '#f59e0b',
  border: 'none',
  borderRadius: '10px',
  fontSize: '14px',
  fontWeight: '600',
  color: 'white',
  cursor: saving.value ? 'not-allowed' : 'pointer',
  opacity: saving.value ? 0.7 : 1,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  gap: '8px'
})

const smallSpinnerStyle = {
  width: '16px',
  height: '16px',
  border: '2px solid rgba(255, 255, 255, 0.3)',
  borderTop: '2px solid white',
  borderRadius: '50%',
  animation: 'spin 1s linear infinite'
}
</script>

<style scoped>
@keyframes spin {
  to { transform: rotate(360deg); }
}

tr:hover {
  background-color: #f8fafc;
}

button:hover:not(:disabled) {
  opacity: 0.9;
  transform: translateY(-1px);
}

button:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}
</style>