<template>
  <div>
    <!-- Panel de filtres -->
    <div :style="filterPanelStyle" class="fade-in">
      <div :style="filterHeaderStyle">
        <div :style="filterTitleContainerStyle">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
          <h3 :style="filterTitleStyle">Rapport de stock par période</h3>
        </div>
        
        <button 
          :style="toggleFilterButtonStyle"
          @click="showFilters = !showFilters"
          @mouseenter="toggleHovered = true"
          @mouseleave="toggleHovered = false"
        >
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path :d="showFilters ? 'M18 15l-6-6-6 6' : 'M6 9l6 6 6-6'"/>
          </svg>
          {{ showFilters ? 'Masquer' : 'Afficher' }}
        </button>
      </div>

      <Transition name="slide">
        <div v-if="showFilters" :style="filterContentStyle">
          <div :style="filterGridStyle">
            <!-- Date début -->
            <div :style="filterFieldStyle">
              <label :style="filterLabelStyle">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                  <line x1="16" y1="2" x2="16" y2="6"/>
                  <line x1="8" y1="2" x2="8" y2="6"/>
                  <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Date début *
              </label>
              <input 
                v-model="dateDebut"
                type="date"
                :style="filterInputStyle"
                required
              />
            </div>

            <!-- Date fin -->
            <div :style="filterFieldStyle">
              <label :style="filterLabelStyle">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                  <line x1="16" y1="2" x2="16" y2="6"/>
                  <line x1="8" y1="2" x2="8" y2="6"/>
                  <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Date fin *
              </label>
              <input 
                v-model="dateFin"
                type="date"
                :style="filterInputStyle"
                required
              />
            </div>

            <!-- Raccourcis de période -->
            <div :style="filterFieldStyle">
              <label :style="filterLabelStyle">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"/>
                  <path d="M12 6v6l4 2"/>
                </svg>
                Période rapide
              </label>
              <select 
                v-model="periodeRapide"
                :style="filterSelectStyle"
                @change="applyPeriodeRapide"
              >
                <option value="">Choisir...</option>
                <option value="today">Aujourd'hui</option>
                <option value="yesterday">Hier</option>
                <option value="week">Cette semaine</option>
                <option value="month">Ce mois</option>
                <option value="last_month">Mois dernier</option>
                <option value="year">Cette année</option>
              </select>
            </div>

            <!-- Boutons d'action -->
            <div :style="filterActionsStyle">
              <button 
                :style="clearFilterButtonStyle"
                @click="clearFilters"
                @mouseenter="clearHovered = true"
                @mouseleave="clearHovered = false"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                </svg>
                Effacer
              </button>
              
              <button 
                :style="generateButtonStyle"
                @click="generateRapport"
                @mouseenter="generateHovered = true"
                @mouseleave="generateHovered = false"
                :disabled="!dateDebut || !dateFin || loadingReport"
              >
                <div v-if="loadingReport" :style="smallSpinnerStyle"></div>
                <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                {{ loadingReport ? 'Génération...' : 'Générer rapport' }}
              </button>

              <ExportStockPDFButton 
                v-if="rapport"
                :magasinInfo="rapport.magasin"
                :periode="rapport.periode"
                :stockAvant="rapport.stock_avant"
                :stockApres="rapport.stock_apres"
                :mouvements="rapport.mouvements"
                :variations="rapport.variations"
                :statistiques="rapport.statistiques"
              />
            </div>
          </div>
        </div>
      </Transition>
    </div>

    <!-- Rapport généré -->
    <div v-if="rapport" :style="rapportContainerStyle" class="fade-in">
      <!-- En-tête du rapport -->
      <div :style="rapportHeaderStyle">
        <div>
          <h2 :style="rapportTitleStyle">📊 Rapport de Stock</h2>
          <p :style="rapportSubtitleStyle">{{ rapport.periode.libelle }}</p>
        </div>
      </div>

      <!-- Statistiques de la période -->
      <div :style="statsGridStyle">
        <div :style="statsCardStyle">
          <div :style="statsIconStyle('#10b981')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
              <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
              <polyline points="17 6 23 6 23 12"/>
            </svg>
          </div>
          <div>
            <p :style="statsLabelStyle">Total entrées</p>
            <p :style="statsValueStyle">{{ rapport.statistiques.total_entrees }}</p>
            <p :style="statsSubStyle">{{ rapport.statistiques.nombre_entrees }} mouvements</p>
          </div>
        </div>

        <div :style="statsCardStyle">
          <div :style="statsIconStyle('#f59e0b')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
              <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/>
              <polyline points="17 18 23 18 23 12"/>
            </svg>
          </div>
          <div>
            <p :style="statsLabelStyle">Total sorties</p>
            <p :style="statsValueStyle">{{ rapport.statistiques.total_sorties }}</p>
            <p :style="statsSubStyle">{{ rapport.statistiques.nombre_sorties }} mouvements</p>
          </div>
        </div>

        <div :style="statsCardStyle">
          <div :style="statsIconStyle('#3b82f6')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
              <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <p :style="statsLabelStyle">Mouvements totaux</p>
            <p :style="statsValueStyle">{{ rapport.statistiques.nombre_mouvements }}</p>
            <p :style="statsSubStyle">Pendant la période</p>
          </div>
        </div>

        <div :style="statsCardStyle">
          <div :style="statsIconStyle(rapport.statistiques.variation_nette >= 0 ? '#10b981' : '#ef4444')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
              <line x1="12" y1="5" x2="12" y2="19"/>
              <polyline points="19 12 12 19 5 12"/>
            </svg>
          </div>
          <div>
            <p :style="statsLabelStyle">Variation nette</p>
            <p :style="{...statsValueStyle, color: rapport.statistiques.variation_nette >= 0 ? '#10b981' : '#ef4444'}">
              {{ rapport.statistiques.variation_nette >= 0 ? '+' : '' }}{{ rapport.statistiques.variation_nette }}
            </p>
            <p :style="statsSubStyle">{{ rapport.statistiques.variation_nette >= 0 ? 'Augmentation' : 'Diminution' }}</p>
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

      <!-- Tab: État avant -->
      <div v-if="activeTab === 'avant'" :style="tableContainerStyle">
        <h3 :style="tableTitleStyle">📦 Stock au début de la période ({{ formatDate(rapport.periode.date_debut) }})</h3>
        <div :style="tableWrapperStyle">
          <table :style="tableStyle">
            <thead>
              <tr>
                <th :style="thStyle">Produit</th>
                <th :style="thStyle">Stock début</th>
                <th :style="thStyle">Seuil alerte</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="produit in rapport.stock_avant" :key="produit.id" :style="trStyle">
                <td :style="tdStyle">
                  <p :style="productNameStyle">{{ produit.nom }}</p>
                  <p :style="productSkuStyle">{{ produit.sku }}</p>
                </td>
                <td :style="tdStyle">
                  <span :style="stockValueStyle">{{ produit.stock_debut_periode }}</span> unités
                </td>
                <td :style="tdStyle">{{ produit.seuil_alerte }} unités</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Tab: Mouvements -->
      <div v-if="activeTab === 'mouvements'" :style="tableContainerStyle">
        <h3 :style="tableTitleStyle">📋 Mouvements pendant la période</h3>
        <div :style="tableWrapperStyle">
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
              <tr v-for="mvt in rapport.mouvements" :key="mvt.id" :style="trStyle">
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
                <td :style="tdStyle">{{ mvt.utilisateur }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Tab: État après -->
      <div v-if="activeTab === 'apres'" :style="tableContainerStyle">
        <h3 :style="tableTitleStyle">📦 Stock à la fin de la période ({{ formatDate(rapport.periode.date_fin) }})</h3>
        <div :style="tableWrapperStyle">
          <table :style="tableStyle">
            <thead>
              <tr>
                <th :style="thStyle">Produit</th>
                <th :style="thStyle">Stock fin</th>
                <th :style="thStyle">Seuil alerte</th>
                <th :style="thStyle">Statut</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="produit in rapport.stock_apres" :key="produit.id" :style="trStyle">
                <td :style="tdStyle">
                  <p :style="productNameStyle">{{ produit.nom }}</p>
                  <p :style="productSkuStyle">{{ produit.sku }}</p>
                </td>
                <td :style="tdStyle">
                  <span :style="stockValueStyle">{{ produit.stock_fin_periode }}</span> unités
                </td>
                <td :style="tdStyle">{{ produit.seuil_alerte }} unités</td>
                <td :style="tdStyle">
                  <span :style="getStatusBadgeStyle(produit.statut)">
                    {{ produit.statut }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Tab: Variations -->
      <div v-if="activeTab === 'variations'" :style="tableContainerStyle">
        <h3 :style="tableTitleStyle">📊 Variations de stock</h3>
        <div :style="tableWrapperStyle">
          <table :style="tableStyle">
            <thead>
              <tr>
                <th :style="thStyle">Produit</th>
                <th :style="thStyle">Stock début</th>
                <th :style="thStyle">Stock fin</th>
                <th :style="thStyle">Variation</th>
                <th :style="thStyle">% Variation</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="variation in rapport.variations" :key="variation.produit_id" :style="trStyle">
                <td :style="tdStyle">
                  <p :style="productNameStyle">{{ variation.produit_nom }}</p>
                  <p :style="productSkuStyle">{{ variation.sku }}</p>
                </td>
                <td :style="tdStyle">{{ variation.stock_debut }}</td>
                <td :style="tdStyle">{{ variation.stock_fin }}</td>
                <td :style="tdStyle">
                  <span :style="getVariationStyle(variation.variation)">
                    {{ variation.variation >= 0 ? '+' : '' }}{{ variation.variation }}
                  </span>
                </td>
                <td :style="tdStyle">
                  <span :style="getVariationStyle(variation.variation)">
                    {{ variation.variation_pourcent >= 0 ? '+' : '' }}{{ variation.variation_pourcent }}%
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Message si pas de rapport -->
    <div v-else :style="emptyStateStyle">
      <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2">
        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
      </svg>
      <p :style="emptyTextStyle">Sélectionnez une période et générez un rapport</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, defineProps, defineEmits } from 'vue'
import ExportStockPDFButton from './ExportStockPDFButton.vue'

const props = defineProps({
  magasinId: {
    type: [String, Number],
    required: true
  },
  apiBaseUrl: {
    type: String,
    required: true
  }
})

const emit = defineEmits(['rapport-generated'])

// State
const showFilters = ref(true)
const dateDebut = ref('')
const dateFin = ref('')
const periodeRapide = ref('')
const rapport = ref(null)
const loadingReport = ref(false)
const activeTab = ref('avant')

const toggleHovered = ref(false)
const clearHovered = ref(false)
const generateHovered = ref(false)

const tabs = [
  { id: 'avant', label: 'État avant' },
  { id: 'mouvements', label: 'Mouvements' },
  { id: 'apres', label: 'État après' },
  { id: 'variations', label: 'Variations' }
]

// Methods
const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR')
}

const applyPeriodeRapide = () => {
  const today = new Date()
  const year = today.getFullYear()
  const month = String(today.getMonth() + 1).padStart(2, '0')
  const day = String(today.getDate()).padStart(2, '0')
  
  switch (periodeRapide.value) {
    case 'today':
      dateDebut.value = `${year}-${month}-${day}`
      dateFin.value = `${year}-${month}-${day}`
      break
      
    case 'yesterday':
      const yesterday = new Date(today)
      yesterday.setDate(yesterday.getDate() - 1)
      const yYear = yesterday.getFullYear()
      const yMonth = String(yesterday.getMonth() + 1).padStart(2, '0')
      const yDay = String(yesterday.getDate()).padStart(2, '0')
      dateDebut.value = `${yYear}-${yMonth}-${yDay}`
      dateFin.value = `${yYear}-${yMonth}-${yDay}`
      break
      
    case 'week':
      const weekStart = new Date(today)
      weekStart.setDate(today.getDate() - today.getDay() + 1)
      const wsYear = weekStart.getFullYear()
      const wsMonth = String(weekStart.getMonth() + 1).padStart(2, '0')
      const wsDay = String(weekStart.getDate()).padStart(2, '0')
      dateDebut.value = `${wsYear}-${wsMonth}-${wsDay}`
      dateFin.value = `${year}-${month}-${day}`
      break
      
    case 'month':
      dateDebut.value = `${year}-${month}-01`
      dateFin.value = `${year}-${month}-${day}`
      break
      
    case 'last_month':
      const lastMonth = new Date(today)
      lastMonth.setMonth(lastMonth.getMonth() - 1)
      const lmYear = lastMonth.getFullYear()
      const lmMonth = String(lastMonth.getMonth() + 1).padStart(2, '0')
      const lastDay = new Date(lmYear, lastMonth.getMonth() + 1, 0).getDate()
      dateDebut.value = `${lmYear}-${lmMonth}-01`
      dateFin.value = `${lmYear}-${lmMonth}-${lastDay}`
      break
      
    case 'year':
      dateDebut.value = `${year}-01-01`
      dateFin.value = `${year}-${month}-${day}`
      break
  }
}

const clearFilters = () => {
  dateDebut.value = ''
  dateFin.value = ''
  periodeRapide.value = ''
  rapport.value = null
}

const generateRapport = async () => {
  if (!dateDebut.value || !dateFin.value) {
    alert('Veuillez sélectionner une période')
    return
  }

  loadingReport.value = true
  
  try {
    const url = `${props.apiBaseUrl}/api_stock.php?action=rapport&magasin_id=${props.magasinId}&date_debut=${dateDebut.value}&date_fin=${dateFin.value}`
    
    const response = await fetch(url)
    const data = await response.json()
    
    if (data.success) {
      rapport.value = data.data
      emit('rapport-generated', data.data)
    } else {
      alert(data.error || 'Erreur lors de la génération du rapport')
    }
  } catch (err) {
    console.error('Erreur:', err)
    alert('Erreur de connexion au serveur')
  } finally {
    loadingReport.value = false
  }
}

// Styles (reprise des styles du composant VentesFilterExport avec adaptations)
const filterPanelStyle = {
  backgroundColor: 'white',
  borderRadius: '20px',
  padding: '24px',
  marginBottom: '24px',
  boxShadow: '0 2px 8px rgba(0, 0, 0, 0.06)',
  border: '1px solid #f1f5f9'
}

const filterHeaderStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  marginBottom: '20px'
}

const filterTitleContainerStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '12px'
}

const filterTitleStyle = {
  margin: 0,
  fontSize: '18px',
  fontWeight: '700',
  color: '#0f172a'
}

const toggleFilterButtonStyle = computed(() => ({
  display: 'flex',
  alignItems: 'center',
  gap: '6px',
  padding: '8px 16px',
  backgroundColor: toggleHovered.value ? '#f1f5f9' : '#f8fafc',
  border: '1px solid #e2e8f0',
  borderRadius: '10px',
  cursor: 'pointer',
  fontSize: '13px',
  fontWeight: '600',
  color: '#64748b',
  transition: 'all 0.2s'
}))

const filterContentStyle = {
  paddingTop: '8px'
}

const filterGridStyle = {
  display: 'grid',
  gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))',
  gap: '16px'
}

const filterFieldStyle = {
  display: 'flex',
  flexDirection: 'column',
  gap: '8px'
}

const filterLabelStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '6px',
  fontSize: '13px',
  fontWeight: '600',
  color: '#475569'
}

const filterInputStyle = {
  padding: '10px 14px',
  border: '1px solid #e2e8f0',
  borderRadius: '10px',
  fontSize: '14px',
  color: '#1e293b',
  fontWeight: '500',
  transition: 'all 0.2s'
}

const filterSelectStyle = {
  padding: '10px 14px',
  border: '1px solid #e2e8f0',
  borderRadius: '10px',
  fontSize: '14px',
  color: '#1e293b',
  fontWeight: '500',
  transition: 'all 0.2s',
  backgroundColor: 'white'
}

const filterActionsStyle = {
  display: 'flex',
  gap: '12px',
  alignItems: 'flex-end',
  gridColumn: '1 / -1'
}

const clearFilterButtonStyle = computed(() => ({
  display: 'flex',
  alignItems: 'center',
  gap: '6px',
  padding: '10px 18px',
  backgroundColor: clearHovered.value ? '#f8fafc' : 'white',
  border: '1px solid #e2e8f0',
  borderRadius: '10px',
  cursor: 'pointer',
  fontSize: '14px',
  fontWeight: '600',
  color: '#64748b',
  transition: 'all 0.2s'
}))

const generateButtonStyle = computed(() => ({
  display: 'flex',
  alignItems: 'center',
  gap: '8px',
  padding: '10px 20px',
  background: generateHovered.value && !loadingReport.value
    ? 'linear-gradient(135deg, #059669 0%, #047857 100%)'
    : 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
  color: 'white',
  border: 'none',
  borderRadius: '10px',
  cursor: loadingReport.value ? 'not-allowed' : 'pointer',
  fontSize: '14px',
  fontWeight: '600',
  transition: 'all 0.2s',
  boxShadow: '0 4px 12px rgba(16, 185, 129, 0.25)',
  opacity: loadingReport.value ? 0.7 : 1
}))

const smallSpinnerStyle = {
  width: '16px',
  height: '16px',
  border: '2px solid rgba(255, 255, 255, 0.3)',
  borderTop: '2px solid white',
  borderRadius: '50%',
  animation: 'spin 1s linear infinite'
}

const rapportContainerStyle = {
  backgroundColor: 'white',
  borderRadius: '20px',
  padding: '32px',
  boxShadow: '0 2px 8px rgba(0, 0, 0, 0.06)',
  border: '1px solid #f1f5f9'
}

const rapportHeaderStyle = {
  marginBottom: '32px',
  paddingBottom: '24px',
  borderBottom: '2px solid #f1f5f9'
}

const rapportTitleStyle = {
  fontSize: '24px',
  fontWeight: '700',
  color: '#0f172a',
  margin: '0 0 8px 0'
}

const rapportSubtitleStyle = {
  fontSize: '16px',
  color: '#64748b',
  margin: 0
}

const statsGridStyle = {
  display: 'grid',
  gridTemplateColumns: 'repeat(auto-fit, minmax(220px, 1fr))',
  gap: '20px',
  marginBottom: '32px'
}

const statsCardStyle = {
  backgroundColor: '#f8fafc',
  borderRadius: '16px',
  padding: '24px',
  display: 'flex',
  alignItems: 'center',
  gap: '16px',
  border: '1px solid #e2e8f0'
}

const statsIconStyle = (color) => ({
  width: '56px',
  height: '56px',
  background: color,
  borderRadius: '16px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
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

const statsSubStyle = {
  fontSize: '12px',
  color: '#94a3b8',
  margin: '4px 0 0 0',
  fontWeight: '500'
}

const tabsContainerStyle = {
  display: 'flex',
  gap: '8px',
  marginBottom: '24px',
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
  marginBottom: '24px'
}

const tableTitleStyle = {
  fontSize: '18px',
  fontWeight: '600',
  color: '#0f172a',
  marginBottom: '16px'
}

const tableWrapperStyle = {
  overflowX: 'auto',
  backgroundColor: 'white',
  borderRadius: '12px',
  border: '1px solid #e2e8f0'
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

const getVariationStyle = (variation) => ({
  fontWeight: '700',
  color: variation >= 0 ? '#10b981' : '#ef4444'
})

const emptyStateStyle = {
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  justifyContent: 'center',
  padding: '60px 20px',
  backgroundColor: 'white',
  borderRadius: '16px',
  gap: '16px',
  border: '1px solid #f1f5f9'
}

const emptyTextStyle = {
  fontSize: '16px',
  color: '#94a3b8',
  fontWeight: '500'
}
</script>

<style scoped>
.slide-enter-active, .slide-leave-active {
  transition: all 0.3s ease;
}

.slide-enter-from, .slide-leave-to {
  opacity: 0;
  max-height: 0;
  overflow: hidden;
}

.slide-enter-to, .slide-leave-from {
  opacity: 1;
  max-height: 500px;
}

.fade-in {
  animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

tr:hover {
  background-color: #f8fafc;
}
</style>