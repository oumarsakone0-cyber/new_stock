<template>
  <SidebarLayout currentPage="produits" :magasinId="magasinId">
    <!-- Loading State -->
    <div v-if="loading" :style="loadingStyle">
      <div :style="spinnerStyle"></div>
      <p>Chargement des produits...</p>
    </div>

    <!-- Error State -->
    <div v-if="error && !loading" :style="errorStyle">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      <p>{{ error }}</p>
      <button :style="retryButtonStyle" @click="loadProduits">Réessayer</button>
    </div>

    <!-- Content -->
    <div v-if="!loading">
      <header :style="headerStyle">
        <div :style="headerLeftStyle">
          <button :style="backButtonStyle" @click="goBack">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            Retour
          </button>
          <div>
            <h1 :style="titleStyle">Produits</h1>
            <p :style="subtitleStyle">{{ magasin.nom }}</p>
          </div>
        </div>
        <button :style="addButtonStyle" @click="showModal = true">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 5v14M5 12h14"/>
          </svg>
          Ajouter un produit
        </button>
      </header>

      <!-- Search and Filter -->
      <div :style="searchContainerStyle">
        <div :style="searchInputContainer">
          <svg :style="searchIconStyle" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
          </svg>
          <input v-model="searchQuery" :style="searchInputStyle" placeholder="Rechercher un produit..." />
        </div>
        <select v-model="filterCategory" :style="selectStyle">
          <option value="">Toutes catégories</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.nom">{{ cat.nom }}</option>
        </select>
      </div>

      <!-- Products Table -->
      <div :style="tableContainerStyle">
        <div :style="tableWrapperStyle">
          <table :style="tableStyle">
            <thead>
              <tr>
                <th :style="thStyle">Produit</th>
                <th :style="thStyle">Catégorie</th>
                <th :style="thStyle">Prix de vente</th>
                <th :style="thStyle">Stock</th>
                <th :style="thStyle">Statut</th>
                <th :style="thStyle">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="produit in filteredProduits" :key="produit.id" :style="trStyle">
                <td :style="tdStyle">
                  <div :style="productInfoStyle">
                    <div :style="productImageStyle">
                      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2">
                        <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                      </svg>
                    </div>
                    <div>
                      <p :style="productNameStyle">{{ produit.nom }}</p>
                      <p :style="productSkuStyle">SKU: {{ produit.sku || 'N/A' }}</p>
                    </div>
                  </div>
                </td>
                <td :style="tdStyle">
                  <span :style="categoryBadgeStyle">{{ produit.categorie || 'Non classé' }}</span>
                </td>
                <td :style="tdStyle">{{ formatMoney(produit.prix_vente) }}</td>
                <td :style="tdStyle">
                  <span :style="getStockStyle(produit.stock_actuel)">{{ produit.stock_actuel }} unités</span>
                </td>
                <td :style="tdStyle">
                  <span :style="getStatusBadgeStyle(produit.stock_actuel)">
                    {{ produit.stock_actuel > 10 ? 'En stock' : produit.stock_actuel > 0 ? 'Stock bas' : 'Rupture' }}
                  </span>
                </td>
                <td :style="tdStyle">
                  <div :style="actionsStyle">
                    <button :style="actionBtnStyle" @click="editProduit(produit)" title="Modifier">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                      </svg>
                    </button>
                    <button :style="deleteBtnStyle" @click="deleteProduit(produit.id)" title="Supprimer">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-if="filteredProduits.length === 0 && !loading" :style="emptyStateStyle">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2">
            <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
          </svg>
          <p :style="emptyTextStyle">Aucun produit trouvé</p>
        </div>
      </div>
    </div>

    <!-- Modal Ajout/Modification -->
    <div v-if="showModal" :style="modalOverlayStyle" @click.self="closeModal">
      <div :style="modalStyle">
        <div :style="modalHeaderStyle">
          <h2 :style="modalTitleStyle">{{ editingProduit ? 'Modifier le produit' : 'Nouveau produit' }}</h2>
          <button :style="closeButtonStyle" @click="closeModal">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
          </button>
        </div>
        
        <form @submit.prevent="saveProduit" :style="formStyle">
          <div :style="formRowStyle">
            <div :style="formGroupStyle">
              <label :style="labelStyle">Nom du produit *</label>
              <input v-model="formData.nom" type="text" :style="inputStyle" required />
            </div>
            <div :style="formGroupStyle">
              <label :style="labelStyle">SKU (auto-generee)</label>
              <input v-model="formData.sku" type="text" :style="{ ...inputStyle, backgroundColor: '#f8fafc', color: '#64748b' }" readonly />
            </div>
          </div>
          
          <div :style="formRowStyle">
            <div :style="formGroupStyle">
              <label :style="labelStyle">Catégorie</label>
              <div :style="categorySelectRowStyle">
                <select v-model="formData.categorie" :style="{ ...inputStyle, flex: '1' }">
                  <option value="">Sélectionner</option>
                  <option v-for="cat in categories" :key="cat.id" :value="cat.nom">{{ cat.nom }}</option>
                </select>
                <button type="button" :style="addCategoryBtnStyle" @click="showCategoryModal = true" title="Ajouter une catégorie">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14M5 12h14"/>
                  </svg>
                </button>
              </div>
            </div>
            <div :style="formGroupStyle">
              <label :style="labelStyle">Prix de vente *</label>
              <input v-model.number="formData.prix_vente" type="number" step="0.01" :style="inputStyle" required />
            </div>
          </div>

          <div :style="formRowStyle">
            <div :style="formGroupStyle">
              <label :style="labelStyle">Prix d'achat</label>
              <input v-model.number="formData.prix_achat" type="number" step="0.01" :style="inputStyle" />
            </div>
            <div :style="formGroupStyle">
              <label :style="labelStyle">Stock initial</label>
              <input v-model.number="formData.stock_actuel" type="number" :style="inputStyle" :disabled="!!editingProduit" />
              <p v-if="editingProduit" :style="hintStyle">Utilisez la gestion du stock pour modifier</p>
            </div>
          </div>

          <div :style="formGroupStyle">
            <label :style="labelStyle">Seuil d'alerte</label>
            <input v-model.number="formData.seuil_alerte" type="number" :style="inputStyle" />
          </div>
          
          <div :style="formGroupStyle">
            <label :style="labelStyle">Description</label>
            <textarea v-model="formData.description" :style="textareaStyle" rows="3"></textarea>
          </div>
          
          <div :style="formActionsStyle">
            <button type="button" :style="cancelButtonStyle" @click="closeModal">Annuler</button>
            <button type="submit" :style="submitButtonStyle" :disabled="saving">
              {{ saving ? 'Enregistrement...' : (editingProduit ? 'Enregistrer' : 'Créer le produit') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Ajout Catégorie -->
    <div v-if="showCategoryModal" :style="modalOverlayStyle" @click.self="closeCategoryModal">
      <div :style="{ ...modalStyle, maxWidth: '440px' }">
        <div :style="modalHeaderStyle">
          <h2 :style="modalTitleStyle">Nouvelle catégorie</h2>
          <button :style="closeButtonStyle" @click="closeCategoryModal">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
          </button>
        </div>
        
        <form @submit.prevent="saveCategorie" :style="formStyle">
          <div :style="formGroupStyle">
            <label :style="labelStyle">Nom de la catégorie *</label>
            <input v-model="categoryFormData.nom" type="text" :style="inputStyle" placeholder="Ex: Boissons" required />
          </div>
          
          <div :style="formGroupStyle">
            <label :style="labelStyle">Description</label>
            <textarea v-model="categoryFormData.description" :style="textareaStyle" rows="3" placeholder="Ex: Sodas, jus, eaux"></textarea>
          </div>
          
          <div :style="formActionsStyle">
            <button type="button" :style="cancelButtonStyle" @click="closeCategoryModal">Annuler</button>
            <button type="submit" :style="submitButtonStyle" :disabled="savingCategory">
              {{ savingCategory ? 'Enregistrement...' : 'Créer la catégorie' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </SidebarLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import SidebarLayout from '../SidebarLayout.vue'

const router = useRouter()
const route = useRoute()

const API_BASE_URL = 'https://sogetrag.com/apistok'

const magasinId = computed(() => route.params.id)
const magasin = ref({ id: magasinId.value, nom: 'Chargement...' })

const goBack = () => router.push(`/magasins/${magasinId.value}`)

const searchQuery = ref('')
const filterCategory = ref('')
const showModal = ref(false)
const editingProduit = ref(null)
const loading = ref(false)
const saving = ref(false)
const error = ref(null)

const categories = ref([])
const showCategoryModal = ref(false)
const savingCategory = ref(false)

const categoryFormData = reactive({
  nom: '',
  description: ''
})

const produits = ref([])

const formData = reactive({
  nom: '',
  sku: '',
  categorie: '',
  prix_vente: 0,
  prix_achat: 0,
  stock_actuel: 0,
  seuil_alerte: 10,
  description: ''
})

const filteredProduits = computed(() => {
  return produits.value.filter(p => {
    const matchSearch = p.nom.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                       (p.sku && p.sku.toLowerCase().includes(searchQuery.value.toLowerCase()))
    const matchCategory = !filterCategory.value || p.categorie === filterCategory.value
    return matchSearch && matchCategory
  })
})

const formatMoney = (amount) => new Intl.NumberFormat('fr-FR', { style: 'decimal', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(amount || 0) + ' FCFA'

// Génération automatique du SKU : 2 premières lettres + dernière lettre + 5 chiffres aléatoires
const generateSku = (nom) => {
  if (!nom || nom.trim().length < 2) return ''
  const clean = nom.trim().toUpperCase().replace(/[^A-Z0-9]/g, '')
  if (clean.length < 2) return ''
  const prefix = clean.substring(0, 2)
  const last = clean.charAt(clean.length - 1)
  const random = Math.floor(10000 + Math.random() * 90000) // 5 chiffres (10000-99999)
  return `${prefix}${last}${random}`
}

// Auto-générer le SKU quand le nom change (seulement en mode création)
watch(() => formData.nom, (newNom) => {
  if (!editingProduit.value) {
    formData.sku = generateSku(newNom)
  }
})

// API Functions
const loadMagasinInfo = async () => {
  try {
    const response = await fetch(`${API_BASE_URL}/api_magasins.php?action=details&id=${magasinId.value}`)
    const data = await response.json()
    if (data.success) {
      magasin.value = data.data
      // Recharger les catégories avec le bon user_id
      loadCategories()
    }
  } catch (err) {
    console.error('Erreur chargement magasin:', err)
  }
}

const loadProduits = async () => {
  loading.value = true
  error.value = null
  
  try {
    const cacheBuster = Date.now() // ou Math.random()
    const response = await fetch(
      `${API_BASE_URL}/api_produits.php?action=list&magasin_id=${magasinId.value}&_=${cacheBuster}`
    )
    const data = await response.json()
    
    if (data.success) {
      produits.value = data.data
    } else {
      error.value = data.error || 'Erreur lors du chargement des produits'
    }
  } catch (err) {
    error.value = 'Impossible de se connecter au serveur'
    console.error('Erreur:', err)
  } finally {
    loading.value = false
  }
}

// Catégories API
const loadCategories = async () => {
  try {
    const userId = magasin.value.user_id || 3
    const cacheBuster = Date.now()
    const response = await fetch(
      `${API_BASE_URL}/api_categories.php?action=list&user_id=${userId}&_=${cacheBuster}`
    )
    const data = await response.json()
    
    if (data.success) {
      categories.value = data.data
    }
  } catch (err) {
    console.error('Erreur chargement catégories:', err)
  }
}

const saveCategorie = async () => {
  savingCategory.value = true
  
  try {
    const userId = magasin.value.user_id || 3
    const payload = {
      user_id: userId,
      magasin_id: magasinId.value,
      nom: categoryFormData.nom,
      description: categoryFormData.description
    }
    
    const response = await fetch(`${API_BASE_URL}/api_categories.php?action=add`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })
    
    const data = await response.json()
    
    if (data.success) {
      await loadCategories()
      // Sélectionner automatiquement la nouvelle catégorie dans le formulaire produit
      formData.categorie = categoryFormData.nom
      closeCategoryModal()
    } else {
      alert(data.error || 'Erreur lors de la création de la catégorie')
    }
  } catch (err) {
    alert('Erreur de connexion au serveur')
    console.error('Erreur:', err)
  } finally {
    savingCategory.value = false
  }
}

const closeCategoryModal = () => {
  showCategoryModal.value = false
  Object.assign(categoryFormData, {
    nom: '',
    description: ''
  })
}

const saveProduit = async () => {
  saving.value = true
  
  try {
    const url = editingProduit.value
      ? `${API_BASE_URL}/api_produits.php?action=update`
      : `${API_BASE_URL}/api_produits.php?action=add`
    
    const payload = {
      ...formData,
      magasin_id: magasinId.value
    }
    
    if (editingProduit.value) {
      payload.id = editingProduit.value.id
      // Ne pas envoyer stock_actuel lors de la modification
      delete payload.stock_actuel
    }
    
    const response = await fetch(url, {
      method: editingProduit.value ? 'PUT' : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })
    
    const data = await response.json()
    
    if (data.success) {
      await loadProduits()
      closeModal()
    } else {
      alert(data.error || 'Erreur lors de l\'enregistrement')
    }
  } catch (err) {
    alert('Erreur de connexion au serveur')
    console.error('Erreur:', err)
  } finally {
    saving.value = false
  }
}

const editProduit = (produit) => {
  editingProduit.value = produit
  Object.assign(formData, {
    nom: produit.nom,
    sku: produit.sku || '',
    categorie: produit.categorie || '',
    prix_vente: produit.prix_vente,
    prix_achat: produit.prix_achat || 0,
    stock_actuel: produit.stock_actuel,
    seuil_alerte: produit.seuil_alerte || 10,
    description: produit.description || ''
  })
  showModal.value = true
}

const deleteProduit = async (id) => {
  if (!confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')) return
  
  try {
    const response = await fetch(`${API_BASE_URL}/api_produits.php?action=delete&id=${id}`, {
      method: 'DELETE'
    })
    
    const data = await response.json()
    
    if (data.success) {
      await loadProduits()
    } else {
      alert(data.error || 'Erreur lors de la suppression')
    }
  } catch (err) {
    alert('Erreur de connexion au serveur')
    console.error('Erreur:', err)
  }
}

const closeModal = () => {
  showModal.value = false
  editingProduit.value = null
  Object.assign(formData, {
    nom: '',
    sku: '',
    categorie: '',
    prix_vente: 0,
    prix_achat: 0,
    stock_actuel: 0,
    seuil_alerte: 10,
    description: ''
  })
}

// Load data on mount
onMounted(() => {
  loadMagasinInfo()
  loadProduits()
  loadCategories()
})

// Styles
const loadingStyle = {
  textAlign: 'center',
  padding: '60px 20px',
  color: '#64748b'
}

const spinnerStyle = {
  width: '48px',
  height: '48px',
  margin: '0 auto 16px',
  border: '4px solid #f1f5f9',
  borderTop: '4px solid #3b82f6',
  borderRadius: '50%',
  animation: 'spin 1s linear infinite'
}

const errorStyle = {
  textAlign: 'center',
  padding: '60px 20px',
  color: '#ef4444'
}

const retryButtonStyle = {
  marginTop: '16px',
  padding: '12px 24px',
  background: '#3b82f6',
  color: 'white',
  border: 'none',
  borderRadius: '8px',
  cursor: 'pointer',
  fontSize: '14px',
  fontWeight: '600'
}

const headerStyle = { display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '24px', flexWrap: 'wrap', gap: '16px' }
const headerLeftStyle = { display: 'flex', alignItems: 'center', gap: '16px', flexWrap: 'wrap' }
const backButtonStyle = { display: 'flex', alignItems: 'center', gap: '8px', padding: '10px 16px', backgroundColor: '#fff', border: '1px solid #e2e8f0', borderRadius: '8px', cursor: 'pointer', fontSize: '14px', color: '#475569' }
const titleStyle = { fontSize: '28px', fontWeight: '700', color: '#1e293b', margin: '0' }
const subtitleStyle = { fontSize: '14px', color: '#64748b', margin: '4px 0 0 0' }
const addButtonStyle = { display: 'flex', alignItems: 'center', gap: '8px', padding: '12px 20px', backgroundColor: '#3b82f6', color: 'white', border: 'none', borderRadius: '10px', cursor: 'pointer', fontSize: '14px', fontWeight: '600' }
const searchContainerStyle = { display: 'flex', gap: '12px', marginBottom: '24px', flexWrap: 'wrap' }
const searchInputContainer = { position: 'relative', flex: '1', minWidth: '200px' }
const searchIconStyle = { position: 'absolute', left: '12px', top: '50%', transform: 'translateY(-50%)', color: '#94a3b8' }
const searchInputStyle = { width: '100%', padding: '12px 12px 12px 44px', border: '1px solid #e2e8f0', borderRadius: '10px', fontSize: '14px', backgroundColor: 'white', boxSizing: 'border-box' }
const selectStyle = { padding: '12px 16px', border: '1px solid #e2e8f0', borderRadius: '10px', fontSize: '14px', backgroundColor: 'white', minWidth: '180px' }
const tableContainerStyle = { backgroundColor: 'white', borderRadius: '16px', overflow: 'hidden', boxShadow: '0 1px 3px rgba(0,0,0,0.1)', border: '1px solid #e2e8f0' }
const tableWrapperStyle = { overflowX: 'auto' }
const tableStyle = { width: '100%', borderCollapse: 'collapse', minWidth: '700px' }
const thStyle = { textAlign: 'left', padding: '16px', fontSize: '12px', fontWeight: '600', color: '#64748b', textTransform: 'uppercase', backgroundColor: '#f8fafc', borderBottom: '1px solid #e2e8f0', whiteSpace: 'nowrap' }
const trStyle = { borderBottom: '1px solid #f1f5f9' }
const tdStyle = { padding: '16px', fontSize: '14px', color: '#1e293b' }
const productInfoStyle = { display: 'flex', alignItems: 'center', gap: '12px' }
const productImageStyle = { width: '40px', height: '40px', borderRadius: '8px', backgroundColor: '#f1f5f9', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: '0' }
const productNameStyle = { margin: '0', fontWeight: '500' }
const productSkuStyle = { margin: '2px 0 0 0', fontSize: '12px', color: '#94a3b8' }
const categoryBadgeStyle = { padding: '4px 10px', borderRadius: '6px', fontSize: '12px', backgroundColor: '#f1f5f9', color: '#475569', whiteSpace: 'nowrap' }
const getStockStyle = (stock) => ({ fontWeight: '600', color: stock > 10 ? '#1e293b' : stock > 0 ? '#f59e0b' : '#ef4444' })
const getStatusBadgeStyle = (stock) => ({ padding: '4px 10px', borderRadius: '20px', fontSize: '12px', fontWeight: '500', backgroundColor: stock > 10 ? '#dcfce7' : stock > 0 ? '#fef3c7' : '#fee2e2', color: stock > 10 ? '#166534' : stock > 0 ? '#92400e' : '#991b1b', whiteSpace: 'nowrap' })
const actionsStyle = { display: 'flex', gap: '8px' }
const actionBtnStyle = { width: '32px', height: '32px', borderRadius: '8px', border: '1px solid #e2e8f0', backgroundColor: 'white', cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#64748b' }
const deleteBtnStyle = { ...actionBtnStyle, color: '#ef4444' }
const emptyStateStyle = { padding: '60px 20px', textAlign: 'center' }
const emptyTextStyle = { marginTop: '16px', color: '#94a3b8', fontSize: '14px' }
const modalOverlayStyle = { position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, backgroundColor: 'rgba(0,0,0,0.5)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 1000, padding: '20px' }
const modalStyle = { backgroundColor: 'white', borderRadius: '20px', width: '100%', maxWidth: '560px', maxHeight: '90vh', overflow: 'auto' }
const modalHeaderStyle = { display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '24px 24px 0' }
const modalTitleStyle = { fontSize: '20px', fontWeight: '600', color: '#1e293b', margin: 0 }
const closeButtonStyle = { background: 'none', border: 'none', cursor: 'pointer', color: '#64748b' }
const formStyle = { padding: '24px' }
const formRowStyle = { display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '16px' }
const formGroupStyle = { marginBottom: '16px' }
const labelStyle = { display: 'block', fontSize: '14px', fontWeight: '500', color: '#374151', marginBottom: '8px' }
const inputStyle = { width: '100%', padding: '12px', border: '1px solid #d1d5db', borderRadius: '10px', fontSize: '14px', boxSizing: 'border-box' }
const textareaStyle = { ...inputStyle, resize: 'vertical' }
const hintStyle = { fontSize: '12px', color: '#94a3b8', marginTop: '4px' }
const formActionsStyle = { display: 'flex', gap: '12px', marginTop: '24px' }
const cancelButtonStyle = { flex: 1, padding: '12px', backgroundColor: '#f1f5f9', border: 'none', borderRadius: '10px', fontSize: '14px', fontWeight: '500', color: '#64748b', cursor: 'pointer' }
const submitButtonStyle = { flex: 1, padding: '12px', backgroundColor: '#3b82f6', border: 'none', borderRadius: '10px', fontSize: '14px', fontWeight: '600', color: 'white', cursor: 'pointer' }
const categorySelectRowStyle = { display: 'flex', gap: '8px', alignItems: 'center' }
const addCategoryBtnStyle = { width: '42px', height: '42px', borderRadius: '10px', border: '1px solid #3b82f6', backgroundColor: '#eff6ff', cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#3b82f6', flexShrink: '0', transition: 'all 0.2s' }
</script>

<style>
@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
