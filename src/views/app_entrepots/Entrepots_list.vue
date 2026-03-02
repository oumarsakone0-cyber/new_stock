<template>
  <SidebarLayout currentPage="entrepots">
    <!-- Loading State -->
    <div v-if="loading" :style="loadingContainerStyle">
      <div :style="spinnerStyle"></div>
      <p :style="loadingTextStyle">Chargement...</p>
    </div>

    <!-- Main Content -->
    <div v-if="!loading">
      <!-- Header -->
      <header :style="headerStyle" class="fade-in">
        <div :style="headerLeftStyle">
          <div :style="headerInfoStyle">
            <div :style="breadcrumbStyle">
              <span :style="breadcrumbActiveStyle">Entrepôts</span>
            </div>
            <h1 :style="titleStyle">Gestion des Entrepôts</h1>
            <p :style="subtitleStyle">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
              </svg>
              {{ entrepots.length }} entrepôt(s) enregistré(s)
            </p>
          </div>
        </div>
        <button 
          :style="newButtonStyle" 
          @click="showEntrepotModal = true"
          @mouseenter="newHovered = true"
          @mouseleave="newHovered = false"
        >
          <div :style="newIconStyle">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M12 5v14M5 12h14"/>
            </svg>
          </div>
          <span>Nouvel entrepôt</span>
        </button>
      </header>

      <!-- Entrepôts Grid -->
      <div :style="entrepotsGridStyle" class="fade-in">
        <div 
          v-for="(entrepot, index) in entrepots" 
          :key="entrepot.id"
          :style="getEntrepotCardStyle(index)"
          @mouseenter="hoveredCard = index"
          @mouseleave="hoveredCard = null"
          @click="selectEntrepot(entrepot)"
        >
          <div :style="cardHeaderStyle">
            <div :style="cardIconStyle">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
              </svg>
            </div>
            <span :style="getStatusBadgeStyle(entrepot.statut)">{{ entrepot.statut }}</span>
          </div>
          
          <h3 :style="cardTitleStyle">{{ entrepot.nom }}</h3>
          <p :style="cardCodeStyle">{{ entrepot.code }}</p>
          <p :style="cardLocationStyle">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
              <circle cx="12" cy="10" r="3"/>
            </svg>
            {{ entrepot.ville || 'Non spécifié' }}
          </p>

          <div :style="cardStatsStyle">
            <div :style="cardStatStyle">
              <span :style="statLabelStyle">Produits</span>
              <span :style="statValueStyle">{{ entrepot.nombre_produits }}</span>
            </div>
            <div :style="cardStatStyle">
              <span :style="statLabelStyle">Quantité totale</span>
              <span :style="statValueStyle">{{ Math.round(entrepot.quantite_totale) }}</span>
            </div>
          </div>

          <div :style="cardActionsStyle">
            <button 
              :style="cardActionButtonStyle('#3b82f6')" 
              @click.stop="viewEntrepot(entrepot)"
              title="Voir détails"
            >
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
            <button 
              :style="cardActionButtonStyle('#10b981')" 
              @click.stop="editEntrepot(entrepot)"
              title="Modifier"
            >
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="entrepots.length === 0" :style="emptyStateStyle">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2">
          <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
        </svg>
        <p :style="emptyTextStyle">Aucun entrepôt enregistré</p>
        <button :style="emptyButtonStyle" @click="showEntrepotModal = true">
          Créer le premier entrepôt
        </button>
      </div>

      <!-- Modal Entrepôt -->
      <Transition name="modal">
        <div v-if="showEntrepotModal" :style="modalOverlayStyle" @click.self="closeEntrepotModal">
          <div :style="modalStyle" class="modal-content">
            <div :style="modalHeaderStyle">
              <div>
                <div :style="modalBadgeStyle">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                  </svg>
                </div>
                <h2 :style="modalTitleStyle">{{ editingEntrepot ? 'Modifier' : 'Nouvel' }} entrepôt</h2>
                <p :style="modalSubtitleStyle">Renseignez les informations de l'entrepôt</p>
              </div>
              <button :style="closeButtonStyle" @click="closeEntrepotModal">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
              </button>
            </div>
            
            <div :style="modalContentStyle">
              <div :style="formGridStyle">
                <!-- Nom -->
                <div :style="formGroupStyle">
                  <label :style="labelStyle">Nom de l'entrepôt *</label>
                  <input v-model="formEntrepot.nom" type="text" :style="inputStyle" required />
                </div>

                <!-- Code -->
                <div :style="formGroupStyle">
                  <label :style="labelStyle">Code *</label>
                  <input v-model="formEntrepot.code" type="text" :style="inputStyle" required />
                </div>

                <!-- Ville -->
                <div :style="formGroupStyle">
                  <label :style="labelStyle">Ville</label>
                  <input v-model="formEntrepot.ville" type="text" :style="inputStyle" />
                </div>

                <!-- Type -->
                <div :style="formGroupStyle">
                  <label :style="labelStyle">Type de stockage</label>
                  <select v-model="formEntrepot.type_stockage" :style="selectStyle">
                    <option value="General">Général</option>
                    <option value="Refrigere">Réfrigéré</option>
                    <option value="Sec">Sec</option>
                    <option value="Liquide">Liquide</option>
                    <option value="Autre">Autre</option>
                  </select>
                </div>

                <!-- Adresse -->
                <div :style="{...formGroupStyle, gridColumn: '1 / -1'}">
                  <label :style="labelStyle">Adresse</label>
                  <textarea v-model="formEntrepot.adresse" :style="textareaStyle" rows="2"></textarea>
                </div>

                <!-- Téléphone -->
                <div :style="formGroupStyle">
                  <label :style="labelStyle">Téléphone</label>
                  <input v-model="formEntrepot.telephone" type="tel" :style="inputStyle" />
                </div>

                <!-- Email -->
                <div :style="formGroupStyle">
                  <label :style="labelStyle">Email</label>
                  <input v-model="formEntrepot.email" type="email" :style="inputStyle" />
                </div>

                <!-- Responsable -->
                <div :style="formGroupStyle">
                  <label :style="labelStyle">Nom du responsable</label>
                  <input v-model="formEntrepot.responsable_nom" type="text" :style="inputStyle" />
                </div>

                <!-- Tél responsable -->
                <div :style="formGroupStyle">
                  <label :style="labelStyle">Tél. responsable</label>
                  <input v-model="formEntrepot.responsable_telephone" type="tel" :style="inputStyle" />
                </div>

                <!-- Statut -->
                <div :style="formGroupStyle">
                  <label :style="labelStyle">Statut</label>
                  <select v-model="formEntrepot.statut" :style="selectStyle">
                    <option value="Actif">Actif</option>
                    <option value="Inactif">Inactif</option>
                    <option value="Maintenance">Maintenance</option>
                  </select>
                </div>
              </div>

              <div :style="modalActionsStyle">
                <button :style="cancelButtonStyle" @click="closeEntrepotModal">Annuler</button>
                <button 
                  :style="saveButtonStyle" 
                  @click="saveEntrepot"
                  @mouseenter="saveHovered = true"
                  @mouseleave="saveHovered = false"
                  :disabled="saving"
                >
                  <div v-if="saving" :style="smallSpinnerStyle"></div>
                  <span v-else>{{ editingEntrepot ? 'Mettre à jour' : 'Créer' }}</span>
                </button>
              </div>
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
import { getEntrepots, addEntrepot, updateEntrepot, deleteEntrepot } from '../../services/api'
import { useToast } from 'vue-toastification'

const router = useRouter()
const toast = useToast()

// API Configuration (remplacé par api.js)

// State
const loading = ref(false)
const saving = ref(false)
const entrepots = ref([])
const showEntrepotModal = ref(false)
const editingEntrepot = ref(null)
const hoveredCard = ref(null)
const newHovered = ref(false)
const saveHovered = ref(false)

const formEntrepot = ref({
  nom: '',
  code: '',
  adresse: '',
  ville: '',
  pays: 'Côte d\'Ivoire',
  telephone: '',
  email: '',
  responsable_nom: '',
  responsable_telephone: '',
  type_stockage: 'General',
  statut: 'Actif'
})

// Methods
const loadEntrepots = async () => {
  loading.value = true
  try {
    const { data } = await getEntrepots()
    if (data.success) {
      entrepots.value = data.data
    }
  } catch (error) {
    console.error('Erreur:', error)
    toast.error('Erreur de chargement')
  } finally {
    loading.value = false
  }
}

const selectEntrepot = (entrepot) => {
  router.push(`/entrepots/${entrepot.id}`)
}

const viewEntrepot = (entrepot) => {
  router.push(`/entrepots/${entrepot.id}`)
}

const editEntrepot = (entrepot) => {
  editingEntrepot.value = entrepot
  formEntrepot.value = { ...entrepot }
  showEntrepotModal.value = true
}

const closeEntrepotModal = () => {
  showEntrepotModal.value = false
  editingEntrepot.value = null
  formEntrepot.value = {
    nom: '',
    code: '',
    adresse: '',
    ville: '',
    pays: 'Côte d\'Ivoire',
    telephone: '',
    email: '',
    responsable_nom: '',
    responsable_telephone: '',
    type_stockage: 'General',
    statut: 'Actif'
  }
}

const saveEntrepot = async () => {
  if (!formEntrepot.value.nom || !formEntrepot.value.code) {
    toast.error('Nom et code requis')
    return
  }
  saving.value = true
  try {
    // Nettoyage du payload : on retire tout champ interdit
    const payload = { ...formEntrepot.value }
    delete payload.id
    delete payload.id_entreprise
    delete payload.cree_par
    delete payload.modifie_par
    const apiCall = editingEntrepot.value ? updateEntrepot : addEntrepot
    const { data } = await apiCall(payload)
    if (data.success) {
      await loadEntrepots()
      closeEntrepotModal()
      toast.success(data.message)
    } else {
      toast.error(data.error)
    }
  } catch (error) {
    console.error('Erreur:', error)
    toast.error('Erreur de sauvegarde')
  } finally {
    saving.value = false
  }
}

const removeEntrepot = async (entrepot) => {
  if (!confirm(`Supprimer l'entrepôt "${entrepot.nom}" ?`)) return
  try {
    const { data } = await deleteEntrepot(entrepot.id)
    if (data.success) {
      await loadEntrepots()
      toast.success('Entrepôt supprimé !')
    } else {
      toast.error(data.error)
    }
  } catch (error) {
    console.error('Erreur:', error)
    toast.error('Erreur lors de la suppression')
  }
}

// Lifecycle
onMounted(() => {
  loadEntrepots()
})

// Styles
const loadingContainerStyle = {
  textAlign: 'center',
  padding: '80px 20px'
}

const loadingTextStyle = {
  marginTop: '16px',
  fontSize: '16px',
  fontWeight: '500',
  color: '#64748b'
}

const spinnerStyle = {
  width: '48px',
  height: '48px',
  margin: '0 auto',
  border: '4px solid #f1f5f9',
  borderTop: '4px solid #10b981',
  borderRadius: '50%',
  animation: 'spin 1s linear infinite'
}

const smallSpinnerStyle = {
  width: '18px',
  height: '18px',
  border: '2px solid rgba(255, 255, 255, 0.3)',
  borderTop: '2px solid white',
  borderRadius: '50%',
  animation: 'spin 1s linear infinite'
}

const headerStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'flex-start',
  marginBottom: '32px',
  flexWrap: 'wrap',
  gap: '20px'
}

const headerLeftStyle = {
  display: 'flex',
  alignItems: 'flex-start',
  gap: '16px'
}

const headerInfoStyle = {
  display: 'flex',
  flexDirection: 'column',
  gap: '6px'
}

const breadcrumbStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '6px',
  marginBottom: '4px'
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

const newButtonStyle = computed(() => ({
  display: 'flex',
  alignItems: 'center',
  gap: '10px',
  padding: '14px 24px',
  background: newHovered.value && !saving.value
    ? 'linear-gradient(135deg, #059669 0%, #047857 100%)'
    : 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
  color: 'white',
  border: 'none',
  borderRadius: '12px',
  cursor: 'pointer',
  fontSize: '14px',
  fontWeight: '600',
  transition: 'all 0.3s',
  boxShadow: newHovered.value ? '0 12px 28px rgba(16, 185, 129, 0.35)' : '0 4px 12px rgba(16, 185, 129, 0.25)',
  transform: newHovered.value ? 'translateY(-2px)' : 'translateY(0)'
}))

const newIconStyle = {
  width: '32px',
  height: '32px',
  background: 'rgba(255, 255, 255, 0.2)',
  borderRadius: '8px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center'
}

const entrepotsGridStyle = {
  display: 'grid',
  gridTemplateColumns: 'repeat(auto-fill, minmax(320px, 1fr))',
  gap: '24px',
  marginBottom: '32px'
}

const getEntrepotCardStyle = (index) => ({
  backgroundColor: 'white',
  borderRadius: '20px',
  padding: '24px',
  border: '1px solid #f1f5f9',
  cursor: 'pointer',
  transition: 'all 0.3s',
  boxShadow: hoveredCard.value === index ? '0 12px 32px rgba(0, 0, 0, 0.1)' : '0 2px 8px rgba(0, 0, 0, 0.06)',
  transform: hoveredCard.value === index ? 'translateY(-4px)' : 'translateY(0)'
})

const cardHeaderStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  marginBottom: '16px'
}

const cardIconStyle = {
  width: '56px',
  height: '56px',
  background: 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
  borderRadius: '16px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  color: 'white'
}

const getStatusBadgeStyle = (statut) => ({
  padding: '4px 12px',
  borderRadius: '12px',
  fontSize: '12px',
  fontWeight: '600',
  backgroundColor: statut === 'Actif' ? '#dcfce7' : statut === 'Maintenance' ? '#fef3c7' : '#fee2e2',
  color: statut === 'Actif' ? '#166534' : statut === 'Maintenance' ? '#92400e' : '#991b1b'
})

const cardTitleStyle = {
  fontSize: '18px',
  fontWeight: '700',
  color: '#0f172a',
  margin: '0 0 4px 0'
}

const cardCodeStyle = {
  fontSize: '13px',
  color: '#64748b',
  fontWeight: '500',
  margin: '0 0 8px 0'
}

const cardLocationStyle = {
  fontSize: '13px',
  color: '#94a3b8',
  display: 'flex',
  alignItems: 'center',
  gap: '4px',
  margin: '0 0 16px 0'
}

const cardStatsStyle = {
  display: 'grid',
  gridTemplateColumns: '1fr 1fr',
  gap: '12px',
  padding: '16px 0',
  borderTop: '1px solid #f1f5f9',
  borderBottom: '1px solid #f1f5f9',
  marginBottom: '16px'
}

const cardStatStyle = {
  display: 'flex',
  flexDirection: 'column',
  gap: '4px'
}

const statLabelStyle = {
  fontSize: '12px',
  color: '#64748b',
  fontWeight: '500'
}

const statValueStyle = {
  fontSize: '20px',
  fontWeight: '700',
  color: '#0f172a'
}

const cardActionsStyle = {
  display: 'flex',
  gap: '8px'
}

const cardActionButtonStyle = (color) => ({
  flex: 1,
  padding: '10px',
  borderRadius: '10px',
  border: `1px solid ${color}20`,
  backgroundColor: `${color}10`,
  cursor: 'pointer',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  color: color,
  transition: 'all 0.2s'
})

const emptyStateStyle = {
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  justifyContent: 'center',
  padding: '80px 20px',
  backgroundColor: 'white',
  borderRadius: '20px',
  gap: '16px'
}

const emptyTextStyle = {
  fontSize: '16px',
  color: '#94a3b8',
  fontWeight: '500'
}

const emptyButtonStyle = {
  padding: '12px 24px',
  background: 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
  color: 'white',
  border: 'none',
  borderRadius: '12px',
  cursor: 'pointer',
  fontSize: '14px',
  fontWeight: '600'
}

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
  maxWidth: '700px',
  maxHeight: '90vh',
  overflow: 'auto'
}

const modalHeaderStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'flex-start',
  padding: '32px 32px 24px',
  borderBottom: '1px solid #f1f5f9'
}

const modalBadgeStyle = {
  width: '48px',
  height: '48px',
  background: 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
  borderRadius: '14px',
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
  margin: '0 0 6px 0'
}

const modalSubtitleStyle = {
  fontSize: '14px',
  color: '#64748b',
  margin: 0
}

const closeButtonStyle = {
  background: 'none',
  border: 'none',
  cursor: 'pointer',
  color: '#94a3b8',
  padding: '8px',
  borderRadius: '8px'
}

const modalContentStyle = {
  padding: '24px 32px 32px'
}

const formGridStyle = {
  display: 'grid',
  gridTemplateColumns: '1fr 1fr',
  gap: '16px',
  marginBottom: '24px'
}

const formGroupStyle = {
  display: 'flex',
  flexDirection: 'column',
  gap: '8px'
}

const labelStyle = {
  fontSize: '14px',
  fontWeight: '600',
  color: '#334155'
}

const inputStyle = {
  padding: '12px 16px',
  border: '1px solid #e2e8f0',
  borderRadius: '12px',
  fontSize: '14px',
  fontWeight: '500',
  color: '#1e293b'
}

const selectStyle = {
  padding: '12px 16px',
  border: '1px solid #e2e8f0',
  borderRadius: '12px',
  fontSize: '14px',
  fontWeight: '500',
  color: '#1e293b',
  backgroundColor: 'white'
}

const textareaStyle = {
  padding: '12px 16px',
  border: '1px solid #e2e8f0',
  borderRadius: '12px',
  fontSize: '14px',
  fontWeight: '500',
  color: '#1e293b',
  resize: 'vertical'
}

const modalActionsStyle = {
  display: 'flex',
  gap: '12px',
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
  cursor: 'pointer'
}

const saveButtonStyle = computed(() => ({
  flex: 1,
  padding: '14px',
  background: saveHovered.value && !saving.value
    ? 'linear-gradient(135deg, #059669 0%, #047857 100%)'
    : 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
  border: 'none',
  borderRadius: '12px',
  fontSize: '14px',
  fontWeight: '600',
  color: 'white',
  cursor: saving.value ? 'not-allowed' : 'pointer',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  gap: '8px',
  opacity: saving.value ? 0.6 : 1
}))
</script>

<style>
@keyframes spin {
  to { transform: rotate(360deg); }
}

.fade-in {
  animation: fadeIn 0.6s ease-in;
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

.modal-enter-active, .modal-leave-active {
  transition: all 0.3s;
}

.modal-enter-from, .modal-leave-to {
  opacity: 0;
}

.modal-content {
  transition: all 0.3s;
}
</style>