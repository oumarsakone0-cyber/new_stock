<template>
  <SidebarLayout currentPage="entrepots">
    <!-- Loading State -->
    <div v-if="loading" :style="loadingContainerStyle">
      <div :style="spinnerStyle"></div>
      <p :style="loadingTextStyle">Chargement...</p>
    </div>

    <!-- Main Content -->
    <div v-if="!loading && entrepot">
      <!-- Header -->
      <header :style="headerStyle" class="fade-in">
        <div :style="headerLeftStyle">
          <button 
            :style="backButtonStyle" 
            @click="goBack"
            @mouseenter="backHovered = true"
            @mouseleave="backHovered = false"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            <span>Retour</span>
          </button>
          <div :style="headerInfoStyle">
            <div :style="breadcrumbStyle">
              <span :style="breadcrumbItemStyle" @click="goBack">Entrepôts</span>
              <svg :style="breadcrumbArrowStyle" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M9 5l7 7-7 7"/>
              </svg>
              <span :style="breadcrumbActiveStyle">{{ entrepot.nom }}</span>
            </div>
            <h1 :style="titleStyle">{{ entrepot.nom }}</h1>
            <p :style="subtitleStyle">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
              </svg>
              {{ entrepot.code }}
            </p>
          </div>
        </div>
      </header>

      <!-- Stats Cards -->
      <div :style="statsGridStyle" class="fade-in">
        <div :style="statsCardStyle">
          <div :style="statsIconStyle('#10b981')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M20 7h-9M14 17H5M18 12H8"/>
            </svg>
          </div>
          <div>
            <p :style="statsLabelStyle">Produits</p>
            <p :style="statsValueStyle">{{ stats.nombre_produits || 0 }}</p>
          </div>
        </div>

        <div :style="statsCardStyle">
          <div :style="statsIconStyle('#3b82f6')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
              <polyline points="17 6 23 6 23 12"/>
            </svg>
          </div>
          <div>
            <p :style="statsLabelStyle">Entrées totales</p>
            <p :style="statsValueStyle">{{ stats.entrees_totales || 0 }}</p>
          </div>
        </div>

        <div :style="statsCardStyle">
          <div :style="statsIconStyle('#f59e0b')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/>
              <polyline points="17 18 23 18 23 12"/>
            </svg>
          </div>
          <div>
            <p :style="statsLabelStyle">Sorties totales</p>
            <p :style="statsValueStyle">{{ stats.sorties_totales || 0 }}</p>
          </div>
        </div>

        <div :style="statsCardStyle">
          <div :style="statsIconStyle('#ef4444')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
              <line x1="12" y1="9" x2="12" y2="13"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </div>
          <div>
            <p :style="statsLabelStyle">Quantité totale</p>
            <p :style="statsValueStyle">{{ stats.quantite_totale || 0 }}</p>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div :style="tabsContainerStyle" class="fade-in">
        <button 
          v-for="tab in tabs" 
          :key="tab.id"
          :style="getTabStyle(tab.id)"
          @click="activeTab = tab.id"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Tab: Produits -->
      <div v-if="activeTab === 'produits'" :style="tableContainerStyle" class="fade-in">
        <div :style="tableHeaderStyle">
          <h3 :style="sectionTitleStyle">Liste des produits</h3>
          <button 
            :style="addButtonStyle" 
            @click="showProduitModal = true"
            @mouseenter="addProduitHovered = true"
            @mouseleave="addProduitHovered = false"
          >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M12 5v14M5 12h14"/>
            </svg>
            Nouveau produit
          </button>
        </div>

        <div :style="tableWrapperStyle">
          <table :style="tableStyle">
            <thead>
              <tr>
                <th :style="thStyle">Référence</th>
                <th :style="thStyle">Produit</th>
                <th :style="thStyle">Catégorie</th>
                <th :style="thStyle">Quantité</th>
                <th :style="thStyle">Unité</th>
                <th :style="thStyle">Emplacement</th>
                <th :style="thStyle">Statut</th>
                <th :style="thStyle">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="produit in produits" :key="produit.id" :style="trStyle">
                <td :style="tdStyle">
                  <span :style="refStyle">{{ produit.reference }}</span>
                </td>
                <td :style="tdStyle">
                  <p :style="productNameStyle">{{ produit.nom }}</p>
                  <p :style="productDescStyle">{{ produit.description || 'N/A' }}</p>
                </td>
                <td :style="tdStyle">{{ produit.categorie || 'N/A' }}</td>
                <td :style="tdStyle">
                  <span :style="quantityStyle">{{ Math.round(produit.quantite_actuelle) }}</span>
                </td>
                <td :style="tdStyle">{{ produit.unite_mesure }}</td>
                <td :style="tdStyle">{{ produit.emplacement || 'N/A' }}</td>
                <td :style="tdStyle">
                  <span :style="getStatusBadgeStyle(produit.statut)">{{ produit.statut }}</span>
                </td>
                <td :style="tdStyle">
                  <div :style="actionsStyle">
                    <button :style="actionButtonStyle('#3b82f6')" @click="editProduit(produit)" title="Modifier">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="produits.length === 0" :style="emptyStateStyle">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2">
            <path d="M20 7h-9M14 17H5M18 12H8"/>
          </svg>
          <p :style="emptyTextStyle">Aucun produit dans cet entrepôt</p>
        </div>
      </div>

      <!-- Tab: Mouvements -->
      <div v-if="activeTab === 'mouvements'" :style="tableContainerStyle" class="fade-in">
        <div :style="tableHeaderStyle">
          <h3 :style="sectionTitleStyle">Historique des mouvements</h3>
          <button 
            :style="addButtonStyle" 
            @click="showMouvementModal = true"
            @mouseenter="addMouvementHovered = true"
            @mouseleave="addMouvementHovered = false"
          >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M12 5v14M5 12h14"/>
            </svg>
            Nouveau mouvement
          </button>
        </div>

        <div :style="tableWrapperStyle">
          <table :style="tableStyle">
            <thead>
              <tr>
                <th :style="thStyle">Date</th>
                <th :style="thStyle">Type</th>
                <th :style="thStyle">Produit</th>
                <th :style="thStyle">Quantité</th>
                <th :style="thStyle">Motif</th>
                <th :style="thStyle">Destination/Provenance</th>
                <th :style="thStyle">Responsable</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="mvt in mouvements" :key="mvt.id" :style="trStyle">
                <td :style="tdStyle">{{ formatDate(mvt.date_mouvement) }}</td>
                <td :style="tdStyle">
                  <span :style="getTypeBadgeStyle(mvt.type_mouvement)">
                    {{ mvt.type_mouvement }}
                  </span>
                </td>
                <td :style="tdStyle">
                  <p :style="productNameStyle">{{ mvt.produit_nom }}</p>
                  <p :style="productDescStyle">{{ mvt.reference }}</p>
                </td>
                <td :style="tdStyle">
                  <span :style="getQuantityStyle(mvt.type_mouvement)">
                    {{ mvt.type_mouvement === 'Entree' ? '+' : '-' }}{{ Math.round(mvt.quantite) }}
                  </span>
                  <span :style="unitStyle">{{ mvt.unite_mesure }}</span>
                </td>
                <td :style="tdStyle">{{ mvt.motif }}</td>
                <td :style="tdStyle">{{ mvt.destination_ou_provenance || 'N/A' }}</td>
                <td :style="tdStyle">{{ mvt.responsable_nom || 'N/A' }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="mouvements.length === 0" :style="emptyStateStyle">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2">
            <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <p :style="emptyTextStyle">Aucun mouvement enregistré</p>
        </div>
      </div>

      <!-- Tab: Rapport -->
      <div v-if="activeTab === 'rapport'" :style="tableContainerStyle" class="fade-in">
        <EntrepotRapportPeriode 
          :entrepotId="entrepotId"
          :apiBaseUrl="API_BASE_URL"
        />
      </div>

      <!-- Modal Produit -->
      <Transition name="modal">
        <div v-if="showProduitModal" :style="modalOverlayStyle" @click.self="closeProduitModal">
          <div :style="modalStyle" class="modal-content">
            <div :style="modalHeaderStyle">
              <div>
                <h2 :style="modalTitleStyle">{{ editingProduit ? 'Modifier' : 'Nouveau' }} produit</h2>
              </div>
              <button :style="closeButtonStyle" @click="closeProduitModal">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
              </button>
            </div>
            
            <div :style="modalContentStyle">
              <div :style="formGridStyle">
                <div :style="formGroupStyle">
                  <label :style="labelStyle">Référence *</label>
                  <input v-model="formProduit.reference" type="text" :style="inputStyle" required />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Nom *</label>
                  <input v-model="formProduit.nom" type="text" :style="inputStyle" required />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Catégorie</label>
                  <input v-model="formProduit.categorie" type="text" :style="inputStyle" />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Unité de mesure</label>
                  <select v-model="formProduit.unite_mesure" :style="selectStyle">
                    <option value="Unite">Unité</option>
                    <option value="Kg">Kilogramme</option>
                    <option value="Litre">Litre</option>
                    <option value="M3">Mètre cube</option>
                    <option value="Tonne">Tonne</option>
                    <option value="Carton">Carton</option>
                    <option value="Palette">Palette</option>
                  </select>
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Seuil d'alerte</label>
                  <input v-model.number="formProduit.seuil_alerte" type="number" :style="inputStyle" />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Emplacement</label>
                  <input v-model="formProduit.emplacement" type="text" :style="inputStyle" placeholder="Allée A - Rack 1" />
                </div>

                <div :style="{...formGroupStyle, gridColumn: '1 / -1'}">
                  <label :style="labelStyle">Description</label>
                  <textarea v-model="formProduit.description" :style="textareaStyle" rows="2"></textarea>
                </div>
              </div>

              <div :style="modalActionsStyle">
                <button :style="cancelButtonStyle" @click="closeProduitModal">Annuler</button>
                <button :style="saveButtonStyle" @click="saveProduit" :disabled="saving">
                  <div v-if="saving" :style="smallSpinnerStyle"></div>
                  <span v-else>{{ editingProduit ? 'Mettre à jour' : 'Créer' }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>

      <!-- Modal Mouvement -->
      <Transition name="modal">
        <div v-if="showMouvementModal" :style="modalOverlayStyle" @click.self="closeMouvementModal">
          <div :style="modalStyle" class="modal-content">
            <div :style="modalHeaderStyle">
              <div>
                <h2 :style="modalTitleStyle">Nouveau mouvement</h2>
              </div>
              <button :style="closeButtonStyle" @click="closeMouvementModal">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
              </button>
            </div>
            
            <div :style="modalContentStyle">
              <div :style="formGridStyle">
                <div :style="formGroupStyle">
                  <label :style="labelStyle">Type de mouvement *</label>
                  <select v-model="formMouvement.type_mouvement" :style="selectStyle">
                    <option value="Entree">Entrée</option>
                    <option value="Sortie">Sortie</option>
                  </select>
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Produit *</label>
                  <select v-model="formMouvement.produit_id" :style="selectStyle">
                    <option value="">Sélectionner...</option>
                    <option v-for="p in produits" :key="p.id" :value="p.id">
                      {{ p.nom }} ({{ Math.round(p.quantite_actuelle) }} {{ p.unite_mesure }})
                    </option>
                  </select>
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Quantité *</label>
                  <input v-model.number="formMouvement.quantite" type="number" min="1" :style="inputStyle" required />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Motif *</label>
                  <input v-model="formMouvement.motif" type="text" :style="inputStyle" placeholder="Reception, Expedition..." required />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">{{ formMouvement.type_mouvement === 'Entree' ? 'Provenance' : 'Destination' }}</label>
                  <input v-model="formMouvement.destination_ou_provenance" type="text" :style="inputStyle" />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Responsable</label>
                  <input v-model="formMouvement.responsable_nom" type="text" :style="inputStyle" />
                </div>

                <div :style="formGroupStyle" v-if="formMouvement.type_mouvement === 'Sortie'">
                  <label :style="labelStyle">Bénéficiaire (qui récupère)</label>
                  <input v-model="formMouvement.beneficiaire_nom" type="text" :style="inputStyle" />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">N° Document</label>
                  <input v-model="formMouvement.numero_document" type="text" :style="inputStyle" placeholder="BL-2025-001" />
                </div>

                <div :style="{...formGroupStyle, gridColumn: '1 / -1'}">
                  <label :style="labelStyle">Observation</label>
                  <textarea v-model="formMouvement.observation" :style="textareaStyle" rows="2"></textarea>
                </div>
              </div>

              <div :style="modalActionsStyle">
                <button :style="cancelButtonStyle" @click="closeMouvementModal">Annuler</button>
                <button :style="saveButtonStyle" @click="saveMouvement" :disabled="saving">
                  <div v-if="saving" :style="smallSpinnerStyle"></div>
                  <span v-else>Enregistrer</span>
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
import { useToast } from 'vue-toastification'
import { useRouter, useRoute } from 'vue-router'
import SidebarLayout from '../SidebarLayout.vue'
import EntrepotRapportPeriode from './EntrepotRapportPeriode.vue'
import { getEntrepot, getEntrepotStats, getEntrepotProduits, getEntrepotMouvements, addEntrepotProduit, updateEntrepotProduit, addEntrepotMouvement } from '../../services/api'

const router = useRouter()
const route = useRoute()
const toast = useToast()


// State
const entrepotId = computed(() => route.params.id)
const loading = ref(false)
const saving = ref(false)
const entrepot = ref(null)
const stats = ref({})
const produits = ref([])
const mouvements = ref([])
const activeTab = ref('produits')
const showProduitModal = ref(false)
const showMouvementModal = ref(false)
const editingProduit = ref(null)

const backHovered = ref(false)
const addProduitHovered = ref(false)
const addMouvementHovered = ref(false)

const tabs = [
  { id: 'produits', label: 'Produits' },
  { id: 'mouvements', label: 'Mouvements' },
  { id: 'rapport', label: 'Rapport période' }
]

const formProduit = ref({
  entrepot_id: '',
  reference: '',
  nom: '',
  description: '',
  categorie: '',
  unite_mesure: 'Unite',
  quantite_actuelle: 0,
  seuil_alerte: 10,
  emplacement: ''
})

const formMouvement = ref({
  entrepot_id: '',
  produit_id: '',
  type_mouvement: 'Entree',
  quantite: '',
  motif: '',
  destination_ou_provenance: '',
  responsable_nom: '',
  beneficiaire_nom: '',
  numero_document: '',
  observation: ''
})

const randomParam = () => `&_=${Date.now()}_${Math.random().toString(36).slice(2)}`

// Methods
const loadEntrepot = async () => {
  try {
    const { data } = await getEntrepot(entrepotId.value)
    if (data.success) {
      entrepot.value = data.data
    }
  } catch (error) {
    console.error('Erreur:', error)
  }
}

const loadStats = async () => {
  try {
    const { data } = await getEntrepotStats(entrepotId.value)
    console.log('Réponse API stats_entrepot:', data)
    if (data.success) {
      stats.value = data.data
    }
  } catch (error) {
    console.error('Erreur:', error)
  }
}

const loadProduits = async () => {
  try {
    const { data } = await getEntrepotProduits(entrepotId.value)
    if (data.success) {
      produits.value = data.data
    }
  } catch (error) {
    console.error('Erreur:', error)
  }
}

const loadMouvements = async () => {
  try {
    const { data } = await getEntrepotMouvements(entrepotId.value)
    if (data.success) {
      mouvements.value = data.data
    }
  } catch (error) {
    console.error('Erreur:', error)
  }
}


const loadAll = async () => {
  loading.value = true
  await Promise.all([
    loadEntrepot(),
    loadStats(),
    loadProduits(),
    loadMouvements()
  ])
  loading.value = false
}

const goBack = () => {
  router.push('/entrepots')
}

const editProduit = (produit) => {
  editingProduit.value = produit
  formProduit.value = { ...produit }
  showProduitModal.value = true
}

const closeProduitModal = () => {
  showProduitModal.value = false
  editingProduit.value = null
  formProduit.value = {
    entrepot_id: entrepotId.value,
    reference: '',
    nom: '',
    description: '',
    categorie: '',
    unite_mesure: 'Unite',
    quantite_actuelle: 0,
    seuil_alerte: 10,
    emplacement: ''
  }
}

const saveProduit = async () => {
  if (!formProduit.value.reference || !formProduit.value.nom) {
    toast.error('Référence et nom requis')
    return
  }
  saving.value = true
  try {
    formProduit.value.entrepot_id = entrepotId.value
    // Nettoyage du payload : suppression des champs interdits
    const payload = { ...formProduit.value }
    // Pour update, il faut garder id, mais supprimer les autres champs interdits
    delete payload.id_entreprise
    delete payload.cree_par
    delete payload.modifie_par
    const apiCall = editingProduit.value ? updateEntrepotProduit : addEntrepotProduit
    const { data } = await apiCall(payload)
    if (data.success) {
      await loadProduits()
      await loadStats()
      closeProduitModal()
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

const closeMouvementModal = () => {
  showMouvementModal.value = false
  formMouvement.value = {
    entrepot_id: entrepotId.value,
    produit_id: '',
    type_mouvement: 'Entree',
    quantite: '',
    motif: '',
    destination_ou_provenance: '',
    responsable_nom: '',
    beneficiaire_nom: '',
    numero_document: '',
    observation: ''
  }
}

const saveMouvement = async () => {
  if (!formMouvement.value.produit_id || !formMouvement.value.quantite || !formMouvement.value.motif) {
    toast.error('Produit, quantité et motif requis')
    return
  }
  saving.value = true
  try {
    formMouvement.value.entrepot_id = entrepotId.value
    // Nettoyage du payload : suppression des champs interdits
    const payload = { ...formMouvement.value }
    delete payload.id
    delete payload.id_entreprise
    delete payload.cree_par
    delete payload.modifie_par
    const { data } = await addEntrepotMouvement(payload)
    if (data.success) {
      await Promise.all([loadMouvements(), loadProduits(), loadStats()])
      closeMouvementModal()
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

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleString('fr-FR', { 
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Lifecycle
onMounted(() => {
  loadAll()
})

// Styles (reprise des styles des autres pages avec adaptations)
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
  marginBottom: '32px'
}

const headerLeftStyle = {
  display: 'flex',
  alignItems: 'flex-start',
  gap: '16px'
}

const backButtonStyle = computed(() => ({
  display: 'flex',
  alignItems: 'center',
  gap: '8px',
  padding: '12px 18px',
  backgroundColor: backHovered.value ? '#f8fafc' : 'white',
  border: '1px solid #e2e8f0',
  borderRadius: '12px',
  cursor: 'pointer',
  fontSize: '14px',
  fontWeight: '600',
  color: '#475569',
  transition: 'all 0.2s',
  boxShadow: backHovered.value ? '0 4px 12px rgba(0, 0, 0, 0.08)' : '0 1px 3px rgba(0, 0, 0, 0.05)',
  transform: backHovered.value ? 'translateX(-4px)' : 'translateX(0)'
}))

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

const breadcrumbItemStyle = {
  fontSize: '13px',
  color: '#94a3b8',
  fontWeight: '500',
  cursor: 'pointer'
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

const statsGridStyle = {
  display: 'grid',
  gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))',
  gap: '20px',
  marginBottom: '32px'
}

const statsCardStyle = {
  backgroundColor: 'white',
  borderRadius: '20px',
  padding: '24px',
  display: 'flex',
  alignItems: 'center',
  gap: '16px',
  boxShadow: '0 2px 8px rgba(0, 0, 0, 0.06)',
  border: '1px solid #f1f5f9'
}

const statsIconStyle = (color) => ({
  width: '56px',
  height: '56px',
  background: `${color}12`,
  borderRadius: '16px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  color: color,
  flexShrink: 0
})

const statsLabelStyle = {
  fontSize: '13px',
  color: '#64748b',
  margin: '0 0 4px 0',
  fontWeight: '500'
}

const statsValueStyle = {
  fontSize: '26px',
  fontWeight: '700',
  color: '#0f172a',
  margin: '0',
  letterSpacing: '-0.02em'
}

const tabsContainerStyle = {
  display: 'flex',
  gap: '8px',
  marginBottom: '24px',
  backgroundColor: '#f1f5f9',
  padding: '6px',
  borderRadius: '12px',
  width: 'fit-content'
}

const getTabStyle = (tabId) => ({
  padding: '12px 20px',
  border: 'none',
  borderRadius: '10px',
  fontSize: '14px',
  fontWeight: '600',
  cursor: 'pointer',
  backgroundColor: activeTab.value === tabId ? 'white' : 'transparent',
  color: activeTab.value === tabId ? '#1e293b' : '#64748b',
  boxShadow: activeTab.value === tabId ? '0 2px 8px rgba(0, 0, 0, 0.06)' : 'none',
  transition: 'all 0.2s'
})

const tableContainerStyle = {
  backgroundColor: 'white',
  borderRadius: '20px',
  padding: '24px',
  boxShadow: '0 2px 8px rgba(0, 0, 0, 0.06)',
  border: '1px solid #f1f5f9'
}

const tableHeaderStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  marginBottom: '20px'
}

const sectionTitleStyle = {
  fontSize: '18px',
  fontWeight: '700',
  color: '#0f172a',
  margin: 0
}

const addButtonStyle = computed(() => ({
  display: 'flex',
  alignItems: 'center',
  gap: '8px',
  padding: '10px 20px',
  background: (addProduitHovered.value || addMouvementHovered.value)
    ? 'linear-gradient(135deg, #059669 0%, #047857 100%)'
    : 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
  color: 'white',
  border: 'none',
  borderRadius: '10px',
  cursor: 'pointer',
  fontSize: '14px',
  fontWeight: '600',
  transition: 'all 0.2s',
  boxShadow: '0 4px 12px rgba(16, 185, 129, 0.25)'
}))

const tableWrapperStyle = {
  overflowX: 'auto',
  borderRadius: '12px',
  border: '1px solid #e2e8f0'
}

const tableStyle = {
  width: '100%',
  borderCollapse: 'collapse',
  minWidth: '800px'
}

const thStyle = {
  textAlign: 'left',
  padding: '16px',
  fontSize: '12px',
  fontWeight: '700',
  color: '#64748b',
  textTransform: 'uppercase',
  backgroundColor: '#f8fafc',
  borderBottom: '2px solid #e2e8f0',
  letterSpacing: '0.05em'
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

const refStyle = {
  fontWeight: '700',
  color: '#3b82f6',
  fontSize: '14px'
}

const productNameStyle = {
  margin: '0',
  fontWeight: '600',
  fontSize: '14px',
  color: '#0f172a'
}

const productDescStyle = {
  margin: '4px 0 0 0',
  fontSize: '12px',
  color: '#94a3b8',
  fontWeight: '500'
}

const quantityStyle = {
  fontWeight: '700',
  fontSize: '16px',
  color: '#0f172a'
}

const getStatusBadgeStyle = (statut) => ({
  padding: '4px 12px',
  borderRadius: '12px',
  fontSize: '12px',
  fontWeight: '600',
  backgroundColor: statut === 'Rupture' ? '#fee2e2' : statut === 'Stock bas' ? '#fef3c7' : '#dcfce7',
  color: statut === 'Rupture' ? '#991b1b' : statut === 'Stock bas' ? '#92400e' : '#166534'
})

const getTypeBadgeStyle = (type) => ({
  padding: '4px 12px',
  borderRadius: '12px',
  fontSize: '12px',
  fontWeight: '600',
  backgroundColor: type === 'Entree' ? '#dcfce7' : '#fef3c7',
  color: type === 'Entree' ? '#166534' : '#92400e'
})

const getQuantityStyle = (type) => ({
  fontWeight: '700',
  fontSize: '16px',
  color: type === 'Entree' ? '#10b981' : '#f59e0b'
})

const unitStyle = {
  fontSize: '12px',
  color: '#94a3b8',
  marginLeft: '4px'
}

const actionsStyle = {
  display: 'flex',
  gap: '8px'
}

const actionButtonStyle = (color) => ({
  width: '36px',
  height: '36px',
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
  padding: '60px 20px',
  gap: '16px'
}

const emptyTextStyle = {
  fontSize: '16px',
  color: '#94a3b8',
  fontWeight: '500'
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

const modalTitleStyle = {
  fontSize: '24px',
  fontWeight: '700',
  color: '#0f172a',
  margin: '0'
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
  background: 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
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

tr:hover {
  background-color: #f8fafc;
}
</style>