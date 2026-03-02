<template>
  <SidebarLayout currentPage="magasins">
    <!-- Elegant Header with Floating Elements -->
    <header :style="headerStyle" class="fade-in">
      <div :style="headerContentStyle">
        <div :style="breadcrumbStyle">
          <span :style="breadcrumbItemStyle">Tableau de bord</span>
          <svg :style="breadcrumbArrowStyle" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
          <span :style="breadcrumbActiveStyle">Magasins</span>
        </div>
        <h1 :style="titleStyle">Mes Magasins</h1>
        <p :style="subtitleStyle">
          <svg :style="subtitleIconStyle" viewBox="0 0 24 24" fill="currentColor">
            <path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
          </svg>
          {{ magasins.length }} points de vente actifs
        </p>
      </div>
      <button 
        v-if="authStore.isAdmin"
        :style="addButtonStyle" 
        @click="showCreateModal = true" 
        @mouseenter="addHovered = true" 
        @mouseleave="addHovered = false"
        class="fade-in"
        style="animation-delay: 0.2s"
      >
        <div :style="addButtonIconStyle">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
          </svg>
        </div>
        <span>Nouveau Magasin</span>
      </button>
    </header>

    <!-- Stats Overview -->
    <div :style="statsOverviewStyle" class="fade-in" style="animation-delay: 0.1s">
      <div :style="statCardStyle">
        <div :style="statIconWrapperStyle('#10b981')">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 2L2 7l10 5 10-5-10-5z"/>
            <path d="M2 17l10 5 10-5"/>
            <path d="M2 12l10 5 10-5"/>
          </svg>
        </div>
        <div>
          <div :style="statValueStyle">{{ totalProduits }}</div>
          <div :style="statLabelStyle">Total Produits</div>
        </div>
      </div>
      
      <div :style="statCardStyle">
        <div :style="statIconWrapperStyle('#3b82f6')">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 3h18v18H3z"/>
            <path d="M3 9h18"/>
            <path d="M9 21V9"/>
          </svg>
        </div>
        <div>
          <div :style="statValueStyle">{{ totalVentes }}</div>
          <div :style="statLabelStyle">Ventes Totales</div>
        </div>
      </div>
      
      <div :style="statCardStyle">
        <div :style="statIconWrapperStyle('#f59e0b')">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
          </svg>
        </div>
        <div>
          <div :style="statValueStyle">{{ formatMoney(totalCA) }}</div>
          <div :style="statLabelStyle">Chiffre d'Affaires</div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" :style="loadingStyle">
      <div :style="spinnerStyle"></div>
      <p>Chargement des magasins...</p>
    </div>

    <!-- Error State -->
    <div v-if="error" :style="errorStyle">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      <p>{{ error }}</p>
      <button :style="retryButtonStyle" @click="loadMagasins">Réessayer</button>
    </div>

    <!-- Refined Grid -->
    <div v-if="!loading && !error" :style="gridStyle">
      <div 
        v-for="(magasin, index) in magasins" 
        :key="magasin.id"
        :style="getCardStyle(index)"
        @mouseenter="hoveredCard = index"
        @mouseleave="hoveredCard = null"
        @click="selectMagasin(magasin)"
        class="card fade-in"
        :class="'delay-' + (index + 2)"
      >
        <!-- Card Glow Effect -->
        <div :style="getCardGlowStyle(index, magasin.couleur)"></div>
        
        <div :style="cardHeaderStyle">
          <div :style="getIconContainerStyle(index, magasin.couleur)">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
              <polyline stroke-linecap="round" stroke-linejoin="round" points="9 22 9 12 15 12 15 22"/>
            </svg>
          </div>
          <div v-if="authStore.isAdmin" :style="cardActionsStyle">
            <button 
              :style="actionBtnStyle" 
              @click.stop="editMagasin(magasin)" 
              @mouseenter="hoveredAction = 'edit-' + index"
              @mouseleave="hoveredAction = null"
              title="Modifier"
            >
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2H14a2 2 0 002-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
              </svg>
            </button>
            <button 
              :style="getDeleteBtnStyle(index)" 
              @click.stop="deleteMagasin(magasin.id)" 
              @mouseenter="hoveredAction = 'delete-' + index"
              @mouseleave="hoveredAction = null"
              title="Supprimer"
            >
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="3 6 5 6 21 6"/>
                <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
              </svg>
            </button>
          </div>
        </div>
        
        <h3 :style="cardTitleStyle">{{ magasin.nom }}</h3>
        <p :style="cardDescStyle">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
            <circle cx="12" cy="10" r="3"/>
          </svg>
          {{ magasin.adresse }}
        </p>
        
        <div :style="statsContainerStyle">
          <div :style="statItemStyle">
            <div :style="statCircleStyle(magasin.couleur)">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
              </svg>
            </div>
            <div>
              <span :style="statValueInlineStyle">{{ magasin.total_produits || 0 }}</span>
              <span :style="statLabelInlineStyle">Produits</span>
            </div>
          </div>
          
          <div :style="statItemStyle">
            <div :style="statCircleStyle(magasin.couleur)">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="9" cy="21" r="1"/>
                <circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
              </svg>
            </div>
            <div>
              <span :style="statValueInlineStyle">{{ magasin.total_ventes || 0 }}</span>
              <span :style="statLabelInlineStyle">Ventes</span>
            </div>
          </div>
          
          <div :style="statItemStyle">
            <div :style="statCircleStyle(magasin.couleur)">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/>
                <path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/>
              </svg>
            </div>
            <div>
              <span :style="statValueInlineStyle">{{ formatMoney(magasin.chiffre_affaires || 0) }}</span>
              <span :style="statLabelInlineStyle">CA</span>
            </div>
          </div>
        </div>

        <div :style="cardFooterStyle">
          <span :style="getStatusStyle(magasin.actif)">
            <span :style="statusDotStyle(magasin.actif)"></span>
            {{ magasin.actif ? 'Actif' : 'Inactif' }}
          </span>
          <span :style="getViewDetailStyle(index)">
            Voir détails
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
          </span>
        </div>
      </div>
    </div>

    <!-- Premium Modal (admin uniquement) -->
    <Transition name="modal">
      <div v-if="showCreateModal && authStore.isAdmin" :style="modalOverlayStyle" @click.self="closeModal">
        <div :style="modalStyle" class="modal-content">
          <!-- Modal Header -->
          <div :style="modalHeaderStyle">
            <div>
              <div :style="modalBadgeStyle">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                </svg>
              </div>
              <h2 :style="modalTitleStyle">{{ editingMagasin ? 'Modifier le Magasin' : 'Nouveau Magasin' }}</h2>
              <p :style="modalSubtitleStyle">{{ editingMagasin ? 'Mettez à jour les informations' : 'Créez un nouveau point de vente' }}</p>
            </div>
            <button :style="closeButtonStyle" @click="closeModal">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 6L6 18M6 6l12 12"/>
              </svg>
            </button>
          </div>
          
          <!-- Form -->
          <form @submit.prevent="saveMagasin" :style="formStyle">
            <div :style="formGroupStyle">
              <label :style="labelStyle">
                Nom du magasin <span :style="requiredStyle">*</span>
              </label>
              <input 
                v-model="formData.nom" 
                type="text" 
                :style="inputStyle"
                placeholder="Ex: Boutique Centre-Ville"
                required
              />
            </div>
            
            <div :style="formRowStyle">
              <div :style="formGroupStyle">
                <label :style="labelStyle">Adresse</label>
                <input 
                  v-model="formData.adresse" 
                  type="text" 
                  :style="inputStyle"
                  placeholder="123 Rue du Commerce"
                />
              </div>
              
              <div :style="formGroupStyle">
                <label :style="labelStyle">Téléphone</label>
                <input 
                  v-model="formData.telephone" 
                  type="tel" 
                  :style="inputStyle"
                  placeholder="+33 1 23 45 67 89"
                />
              </div>
            </div>
            
            <div :style="formGroupStyle">
              <label :style="labelStyle">Couleur du magasin</label>
              <div :style="colorPickerStyle">
                <button 
                  v-for="color in colors" 
                  :key="color"
                  type="button"
                  :style="getColorOptionStyle(color)"
                  @click="formData.couleur = color"
                >
                  <svg v-if="formData.couleur === color" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                </button>
              </div>
            </div>
            
            <div :style="formGroupStyle">
              <label :style="checkboxLabelStyle">
                <input type="checkbox" v-model="formData.actif" :style="checkboxStyle" />
                <span :style="checkboxTextStyle">Magasin actif</span>
              </label>
            </div>
            
            <div :style="formActionsStyle">
              <button type="button" :style="cancelButtonStyle" @click="closeModal">
                Annuler
              </button>
              <button type="submit" :style="submitButtonStyle" :disabled="saving">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                  <polyline points="17 21 17 13 7 13 7 21"/>
                  <polyline points="7 3 7 8 15 8"/>
                </svg>
                {{ saving ? 'Enregistrement...' : (editingMagasin ? 'Enregistrer les modifications' : 'Créer le magasin') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    
  </SidebarLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import SidebarLayout from '../SidebarLayout.vue'
import { useAuthStore } from '../../stores/authStore'

const router = useRouter()
const authStore = useAuthStore()

const API_BASE_URL = 'https://sogetrag.com/apistok'

const hoveredCard = ref(null)
const hoveredAction = ref(null)
const addHovered = ref(false)
const showCreateModal = ref(false)
const editingMagasin = ref(null)
const loading = ref(false)
const saving = ref(false)
const error = ref(null)

const colors = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899']

const magasins = ref([])

const formData = reactive({
  nom: '',
  adresse: '',
  telephone: '',
  couleur: '#10b981',
  actif: true
})

const totalProduits = computed(() => magasins.value.reduce((sum, m) => sum + (m.total_produits || 0), 0))
const totalVentes = computed(() => magasins.value.reduce((sum, m) => sum + (m.total_ventes || 0), 0))
const totalCA = computed(() => magasins.value.reduce((sum, m) => sum + (parseFloat(m.chiffre_affaires) || 0), 0))

const formatMoney = (amount) => {
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF', minimumFractionDigits: 0 }).format(amount)
}

// API Functions
const loadMagasins = async () => {
  loading.value = true
  error.value = null
  try {
    // Génère un nombre aléatoire pour éviter le cache
    const cacheBuster = Math.random()  
    const response = await fetch(`${API_BASE_URL}/api_magasins.php?action=list&_=${cacheBuster}`)
    const data = await response.json()
    
    if (data.success) {
      const allMagasins = data.data
      // Filtrer selon les accès de l'utilisateur connecté
      const acces = authStore.accessibleMagasins
      if (acces === 'all' || authStore.isAdmin) {
        magasins.value = allMagasins
      } else if (Array.isArray(acces) && acces.length > 0) {
        magasins.value = allMagasins.filter(m => acces.includes(parseInt(m.id)))
      } else {
        magasins.value = []
      }
    } else {
      error.value = data.error || 'Erreur lors du chargement des magasins'
    }
  } catch (err) {
    error.value = 'Impossible de se connecter au serveur'
    console.error('Erreur:', err)
  } finally {
    loading.value = false
  }
}

const saveMagasin = async () => {
  saving.value = true
  try {
    const url = editingMagasin.value 
      ? `${API_BASE_URL}/api_magasins.php?action=update`
      : `${API_BASE_URL}/api_magasins.php?action=add`
    
    const payload = editingMagasin.value
      ? { ...formData, id: editingMagasin.value.id }
      : { ...formData }

    const response = await fetch(url, {
      method: editingMagasin.value ? 'PUT' : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })
    
    const data = await response.json()
    
    if (data.success) {
      await loadMagasins()
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

const deleteMagasin = async (id) => {
  if (!confirm('Êtes-vous sûr de vouloir supprimer ce magasin ?')) return
  
  try {
    const response = await fetch(`${API_BASE_URL}/api_magasins.php?action=delete&id=${id}`, {
      method: 'DELETE'
    })
    
    const data = await response.json()
    
    if (data.success) {
      await loadMagasins()
    } else {
      alert(data.error || 'Erreur lors de la suppression')
    }
  } catch (err) {
    alert('Erreur de connexion au serveur')
    console.error('Erreur:', err)
  }
}

const selectMagasin = (magasin) => {
  router.push(`/magasins/${magasin.id}`)
}

const editMagasin = (magasin) => {
  editingMagasin.value = magasin
  formData.nom = magasin.nom
  formData.adresse = magasin.adresse
  formData.telephone = magasin.telephone
  formData.couleur = magasin.couleur
  formData.actif = Boolean(magasin.actif)
  showCreateModal.value = true
}

const closeModal = () => {
  showCreateModal.value = false
  editingMagasin.value = null
  formData.nom = ''
  formData.adresse = ''
  formData.telephone = ''
  formData.couleur = '#10b981'
  formData.actif = true
}

// Load data on mount
onMounted(() => {
  loadMagasins()
})

// Styles (keeping all existing styles)
const headerStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'flex-start',
  marginBottom: '32px',
  flexWrap: 'wrap',
  gap: '20px'
}

const headerContentStyle = {
  display: 'flex',
  flexDirection: 'column',
  gap: '8px'
}

const breadcrumbStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '6px',
  marginBottom: '4px'
}

const breadcrumbItemStyle = {
  fontSize: '13px',
  color: '#94a3b8',
  fontWeight: '500'
}

const breadcrumbArrowStyle = {
  width: '14px',
  height: '14px',
  color: '#cbd5e1',
  strokeWidth: '2'
}

const breadcrumbActiveStyle = {
  fontSize: '13px',
  color: '#1e293b',
  fontWeight: '600'
}

const titleStyle = {
  fontSize: '32px',
  fontWeight: '700',
  color: '#0f172a',
  margin: '0',
  letterSpacing: '-0.02em'
}

const subtitleStyle = {
  fontSize: '14px',
  color: '#64748b',
  margin: '0',
  display: 'flex',
  alignItems: 'center',
  gap: '6px',
  fontWeight: '500'
}

const subtitleIconStyle = {
  width: '16px',
  height: '16px'
}

const addButtonStyle = computed(() => ({
  display: 'flex',
  alignItems: 'center',
  gap: '10px',
  padding: '14px 24px',
  background: addHovered.value 
    ? 'linear-gradient(135deg, #059669 0%, #047857 100%)'
    : 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
  color: 'white',
  border: 'none',
  borderRadius: '12px',
  cursor: 'pointer',
  fontSize: '14px',
  fontWeight: '600',
  transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
  boxShadow: addHovered.value 
    ? '0 12px 28px rgba(16, 185, 129, 0.35)'
    : '0 4px 12px rgba(16, 185, 129, 0.25)',
  transform: addHovered.value ? 'translateY(-2px)' : 'translateY(0)',
  letterSpacing: '0.01em'
}))

const addButtonIconStyle = {
  width: '32px',
  height: '32px',
  background: 'rgba(255, 255, 255, 0.2)',
  borderRadius: '8px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center'
}

const statsOverviewStyle = {
  display: 'grid',
  gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))',
  gap: '16px',
  marginBottom: '32px'
}

const statCardStyle = {
  background: 'white',
  borderRadius: '16px',
  padding: '20px',
  display: 'flex',
  alignItems: 'center',
  gap: '16px',
  boxShadow: '0 1px 3px rgba(0, 0, 0, 0.05)',
  border: '1px solid #f1f5f9'
}

const statIconWrapperStyle = (color) => ({
  width: '48px',
  height: '48px',
  background: `${color}15`,
  borderRadius: '12px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  color: color,
  flexShrink: 0
})

const statValueStyle = {
  fontSize: '24px',
  fontWeight: '700',
  color: '#0f172a',
  lineHeight: '1.2',
  letterSpacing: '-0.01em'
}

const statLabelStyle = {
  fontSize: '13px',
  color: '#64748b',
  fontWeight: '500',
  marginTop: '2px'
}

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
  borderTop: '4px solid #10b981',
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
  background: '#10b981',
  color: 'white',
  border: 'none',
  borderRadius: '8px',
  cursor: 'pointer',
  fontSize: '14px',
  fontWeight: '600'
}

const gridStyle = {
  display: 'grid',
  gridTemplateColumns: 'repeat(auto-fill, minmax(340px, 1fr))',
  gap: '20px'
}

const getCardStyle = (index) => ({
  backgroundColor: 'white',
  borderRadius: '20px',
  padding: '28px',
  boxShadow: hoveredCard.value === index 
    ? '0 20px 40px rgba(0, 0, 0, 0.1)' 
    : '0 2px 8px rgba(0, 0, 0, 0.06)',
  cursor: 'pointer',
  transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)',
  transform: hoveredCard.value === index ? 'translateY(-6px)' : 'translateY(0)',
  border: hoveredCard.value === index ? '1px solid #e2e8f0' : '1px solid #f1f5f9'
})

const getCardGlowStyle = (index, color) => ({
  position: 'absolute',
  inset: '-2px',
  background: `linear-gradient(135deg, ${color}20, transparent)`,
  borderRadius: '20px',
  opacity: hoveredCard.value === index ? 1 : 0,
  transition: 'opacity 0.4s',
  filter: 'blur(12px)',
  zIndex: -1
})

const cardHeaderStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'flex-start',
  marginBottom: '20px'
}

const getIconContainerStyle = (index, color) => ({
  width: '56px',
  height: '56px',
  borderRadius: '16px',
  background: hoveredCard.value === index 
    ? `linear-gradient(135deg, ${color} 0%, ${adjustColor(color, -15)} 100%)`
    : `${color}12`,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)',
  transform: hoveredCard.value === index ? 'rotate(-5deg) scale(1.05)' : 'rotate(0deg) scale(1)',
  boxShadow: hoveredCard.value === index ? `0 8px 20px ${color}40` : 'none'
})

const cardActionsStyle = {
  display: 'flex',
  gap: '8px'
}

const actionBtnStyle = {
  width: '36px',
  height: '36px',
  borderRadius: '10px',
  border: '1px solid #e2e8f0',
  backgroundColor: 'white',
  cursor: 'pointer',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  color: '#64748b',
  transition: 'all 0.2s',
  '&:hover': {
    backgroundColor: '#f8fafc',
    borderColor: '#cbd5e1',
    color: '#3b82f6'
  }
}

const getDeleteBtnStyle = (index) => ({
  ...actionBtnStyle,
  color: hoveredAction.value === 'delete-' + index ? '#ef4444' : '#64748b',
  backgroundColor: hoveredAction.value === 'delete-' + index ? '#fef2f2' : 'white',
  borderColor: hoveredAction.value === 'delete-' + index ? '#fecaca' : '#e2e8f0'
})

const cardTitleStyle = {
  fontSize: '20px',
  fontWeight: '700',
  color: '#0f172a',
  margin: '0 0 8px 0',
  letterSpacing: '-0.01em'
}

const cardDescStyle = {
  fontSize: '14px',
  color: '#64748b',
  margin: '0 0 20px 0',
  display: 'flex',
  alignItems: 'center',
  gap: '6px',
  fontWeight: '500'
}

const statsContainerStyle = {
  display: 'flex',
  flexDirection: 'column',
  gap: '12px',
  padding: '20px 0',
  borderTop: '1px solid #f1f5f9',
  borderBottom: '1px solid #f1f5f9',
  marginBottom: '20px'
}

const statItemStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '12px'
}

const statCircleStyle = (color) => ({
  width: '36px',
  height: '36px',
  borderRadius: '10px',
  background: `${color}12`,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  color: color,
  flexShrink: 0
})

const statValueInlineStyle = {
  fontSize: '16px',
  fontWeight: '700',
  color: '#0f172a',
  marginRight: '4px',
  letterSpacing: '-0.01em'
}

const statLabelInlineStyle = {
  fontSize: '13px',
  color: '#94a3b8',
  fontWeight: '500'
}

const cardFooterStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center'
}

const getStatusStyle = (actif) => ({
  display: 'inline-flex',
  alignItems: 'center',
  gap: '6px',
  padding: '6px 14px',
  borderRadius: '20px',
  fontSize: '13px',
  fontWeight: '600',
  backgroundColor: actif ? '#dcfce7' : '#fee2e2',
  color: actif ? '#166534' : '#991b1b',
  letterSpacing: '0.01em'
})

const statusDotStyle = (actif) => ({
  width: '6px',
  height: '6px',
  borderRadius: '50%',
  backgroundColor: actif ? '#22c55e' : '#ef4444'
})

const getViewDetailStyle = (index) => ({
  fontSize: '14px',
  color: '#3b82f6',
  fontWeight: '600',
  display: 'flex',
  alignItems: 'center',
  gap: '4px',
  transition: 'all 0.2s',
  transform: hoveredCard.value === index ? 'translateX(4px)' : 'translateX(0)'
})

// Modal styles
const modalOverlayStyle = {
  position: 'fixed',
  top: 0,
  left: 0,
  right: 0,
  bottom: 0,
  backgroundColor: 'rgba(15, 23, 42, 0.7)',
  backdropFilter: 'blur(8px)',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  zIndex: 1000,
  padding: '20px'
}

const modalStyle = {
  backgroundColor: 'white',
  borderRadius: '24px',
  width: '100%',
  maxWidth: '540px',
  maxHeight: '90vh',
  overflow: 'auto',
  boxShadow: '0 25px 50px -12px rgba(0, 0, 0, 0.25)'
}

const modalHeaderStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'flex-start',
  padding: '32px 32px 24px',
  borderBottom: '1px solid #f1f5f9'
}

const modalBadgeStyle = {
  width: '40px',
  height: '40px',
  background: 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
  borderRadius: '12px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  color: 'white',
  marginBottom: '16px'
}

const modalTitleStyle = {
  fontSize: '24px',
  fontWeight: '700',
  color: '#0f172a',
  margin: '0 0 6px 0',
  letterSpacing: '-0.01em'
}

const modalSubtitleStyle = {
  fontSize: '14px',
  color: '#64748b',
  margin: 0,
  fontWeight: '500'
}

const closeButtonStyle = {
  background: 'none',
  border: 'none',
  cursor: 'pointer',
  color: '#94a3b8',
  padding: '8px',
  borderRadius: '8px',
  transition: 'all 0.2s',
  '&:hover': {
    backgroundColor: '#f1f5f9',
    color: '#64748b'
  }
}

const formStyle = {
  padding: '24px 32px 32px'
}

const formGroupStyle = {
  marginBottom: '20px',
  flex: 1
}

const formRowStyle = {
  display: 'flex',
  gap: '16px'
}

const labelStyle = {
  display: 'block',
  fontSize: '14px',
  fontWeight: '600',
  color: '#334155',
  marginBottom: '8px',
  letterSpacing: '0.01em'
}

const requiredStyle = {
  color: '#ef4444',
  marginLeft: '2px'
}

const inputStyle = {
  width: '100%',
  padding: '12px 16px',
  border: '1px solid #e2e8f0',
  borderRadius: '12px',
  fontSize: '14px',
  boxSizing: 'border-box',
  transition: 'all 0.2s',
  fontWeight: '500',
  color: '#1e293b',
  '&:focus': {
    outline: 'none',
    borderColor: '#10b981',
    boxShadow: '0 0 0 3px rgba(16, 185, 129, 0.1)'
  }
}

const colorPickerStyle = {
  display: 'flex',
  gap: '10px',
  flexWrap: 'wrap'
}

const getColorOptionStyle = (color) => ({
  width: '44px',
  height: '44px',
  borderRadius: '12px',
  backgroundColor: color,
  border: formData.couleur === color ? '3px solid #0f172a' : '3px solid transparent',
  cursor: 'pointer',
  transition: 'all 0.2s',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  transform: formData.couleur === color ? 'scale(1.1)' : 'scale(1)'
})

const checkboxLabelStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '10px',
  fontSize: '14px',
  color: '#334155',
  cursor: 'pointer',
  fontWeight: '500'
}

const checkboxStyle = {
  width: '20px',
  height: '20px',
  cursor: 'pointer',
  accentColor: '#10b981'
}

const checkboxTextStyle = {
  fontSize: '14px',
  fontWeight: '600',
  color: '#334155'
}

const formActionsStyle = {
  display: 'flex',
  gap: '12px',
  marginTop: '28px',
  paddingTop: '24px',
  borderTop: '1px solid #f1f5f9'
}

const cancelButtonStyle = {
  flex: 1,
  padding: '14px',
  backgroundColor: '#f8fafc',
  border: '1px solid #e2e8f0',
  borderRadius: '12px',
  fontSize: '14px',
  fontWeight: '600',
  color: '#64748b',
  cursor: 'pointer',
  transition: 'all 0.2s',
  letterSpacing: '0.01em'
}

const submitButtonStyle = {
  flex: 1,
  padding: '14px',
  background: 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
  border: 'none',
  borderRadius: '12px',
  fontSize: '14px',
  fontWeight: '600',
  color: 'white',
  cursor: 'pointer',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  gap: '8px',
  transition: 'all 0.2s',
  boxShadow: '0 4px 12px rgba(16, 185, 129, 0.25)',
  letterSpacing: '0.01em'
}

// Helper
const adjustColor = (color, amount) => {
  const num = parseInt(color.replace('#', ''), 16)
  const r = Math.max(0, Math.min(255, (num >> 16) + amount))
  const g = Math.max(0, Math.min(255, ((num >> 8) & 0x00FF) + amount))
  const b = Math.max(0, Math.min(255, (num & 0x0000FF) + amount))
  return '#' + ((r << 16) | (g << 8) | b).toString(16).padStart(6, '0')
}
</script>
<style>
      @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

      * {
        font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif;
      }

      @keyframes fadeIn {
        from {
          opacity: 0;
          transform: translateY(20px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }

      @keyframes spin {
        to { transform: rotate(360deg); }
      }

      .fade-in {
        animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) backwards;
      }

      .delay-2 { animation-delay: 0.2s; }
      .delay-3 { animation-delay: 0.3s; }
      .delay-4 { animation-delay: 0.4s; }
      .delay-5 { animation-delay: 0.5s; }

      .card {
        position: relative;
        overflow: hidden;
      }

      .card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.4) 50%, transparent 70%);
        transform: translateX(-100%) rotate(45deg);
        transition: transform 0.6s;
      }

      .card:hover::before {
        transform: translateX(100%) rotate(45deg);
      }

      .modal-enter-active, .modal-leave-active {
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
      }

      .modal-enter-from, .modal-leave-to {
        opacity: 0;
      }

      .modal-enter-from .modal-content,
      .modal-leave-to .modal-content {
        transform: scale(0.95) translateY(20px);
      }

      .modal-content {
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
      }
    </style>
