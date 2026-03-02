<!-- Composant de filtrage et export pour la page ventes -->
<template>
  <div>
    <!-- Panneau de filtres -->
    <div :style="filterPanelStyle" class="fade-in">
      <div :style="filterHeaderStyle">
        <div :style="filterTitleContainerStyle">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 6h18M7 12h10M11 18h2"/>
          </svg>
          <h3 :style="filterTitleStyle">Filtres et rapports</h3>
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
                Date début
              </label>
              <input 
                v-model="dateDebut"
                type="date"
                :style="filterInputStyle"
                @change="applyFilters"
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
                Date fin
              </label>
              <input 
                v-model="dateFin"
                type="date"
                :style="filterInputStyle"
                @change="applyFilters"
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
              
              <!-- Export PDF côté frontend -->
              <ExportPDFButton 
                v-if="rapport"
                :magasinInfo="rapport.magasin"
                :periodeLibelle="rapport.periode.libelle"
                :totaux="rapport.totaux"
                :ventes="rapport.ventes"
              />
            </div>
          </div>
        </div>
      </Transition>
    </div>

    <!-- Résumé des totaux -->
    <div v-if="rapport" :style="resumeGridStyle" class="fade-in">
      <div 
        :style="getResumeCardStyle(0)"
        @mouseenter="hoveredResume = 0"
        @mouseleave="hoveredResume = null"
      >
        <div :style="resumeIconStyle('#3b82f6')">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
        </div>
        <div :style="resumeContentStyle">
          <p :style="resumeLabelStyle">Nombre de ventes</p>
          <p :style="resumeValueStyle">{{ rapport.totaux.nombre_ventes }}</p>
          <p :style="resumeSubStyle">{{ rapport.periode.libelle }}</p>
        </div>
      </div>

      <div 
        :style="getResumeCardStyle(1)"
        @mouseenter="hoveredResume = 1"
        @mouseleave="hoveredResume = null"
      >
        <div :style="resumeIconStyle('#10b981')">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
          </svg>
        </div>
        <div :style="resumeContentStyle">
          <p :style="resumeLabelStyle">Total vendu</p>
          <p :style="resumeValueStyle">{{ formatMoney(rapport.totaux.total_vendu) }}</p>
          <p :style="resumeSubStyle">
            Taux: {{ rapport.totaux.taux_recouvrement }}%
          </p>
        </div>
      </div>

      <div 
        :style="getResumeCardStyle(2)"
        @mouseenter="hoveredResume = 2"
        @mouseleave="hoveredResume = null"
      >
        <div :style="resumeIconStyle('#10b981')">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <div :style="resumeContentStyle">
          <p :style="resumeLabelStyle">Total encaissé</p>
          <p :style="resumeValueEncaisseStyle">{{ formatMoney(rapport.totaux.total_encaisse) }}</p>
          <p :style="resumeSubStyle">
            {{ Math.round((rapport.totaux.total_encaisse / rapport.totaux.total_vendu) * 100) || 0 }}% du total
          </p>
        </div>
      </div>

      <div 
        :style="getResumeCardStyle(3)"
        @mouseenter="hoveredResume = 3"
        @mouseleave="hoveredResume = null"
      >
        <div :style="resumeIconStyle('#ef4444')">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <div :style="resumeContentStyle">
          <p :style="resumeLabelStyle">Total en crédit</p>
          <p :style="resumeValueCreditStyle">{{ formatMoney(rapport.totaux.total_credit) }}</p>
          <p :style="resumeSubStyle">
            À recouvrer
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, defineEmits, defineProps } from 'vue'
import ExportPDFButton from './ExportPDFButton.vue'

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

const emit = defineEmits(['filter-applied'])

// State
const showFilters = ref(true)
const dateDebut = ref('')
const dateFin = ref('')
const periodeRapide = ref('')
const rapport = ref(null)
const loadingReport = ref(false)

const toggleHovered = ref(false)
const clearHovered = ref(false)
const hoveredResume = ref(null)

// Methods
const applyFilters = async () => {
  loadingReport.value = true
  
  try {
    const random = Math.random() // ou Date.now()
    
    let url = `${props.apiBaseUrl}/api_ventes.php?action=rapport&magasin_id=${props.magasinId}&r=${random}`
    
    if (dateDebut.value) {
      url += `&date_debut=${dateDebut.value}`
    }
    
    if (dateFin.value) {
      url += `&date_fin=${dateFin.value}`
    }
    
    const response = await fetch(url)
    const data = await response.json()
    
    if (data.success) {
      rapport.value = data.data
      emit('filter-applied', data.data)
    } else {
      alert(data.error || 'Erreur lors du chargement du rapport')
    }
  } catch (err) {
    console.error('Erreur:', err)
    alert('Erreur de connexion au serveur')
  } finally {
    loadingReport.value = false
  }
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
  
  applyFilters()
}

const clearFilters = () => {
  dateDebut.value = ''
  dateFin.value = ''
  periodeRapide.value = ''
  applyFilters()
}

const formatMoney = (amount) => {
  return new Intl.NumberFormat('fr-FR', { 
    style: 'currency', 
    currency: 'XOF', 
    minimumFractionDigits: 0 
  }).format(amount)
}

// Charger le rapport initial
applyFilters()

// Styles
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
  alignItems: 'flex-end'
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

const resumeGridStyle = {
  display: 'grid',
  gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))',
  gap: '20px',
  marginBottom: '32px'
}

const getResumeCardStyle = (index) => ({
  backgroundColor: 'white',
  borderRadius: '20px',
  padding: '24px',
  display: 'flex',
  alignItems: 'center',
  gap: '16px',
  boxShadow: hoveredResume.value === index 
    ? '0 12px 32px rgba(0, 0, 0, 0.1)' 
    : '0 2px 8px rgba(0, 0, 0, 0.06)',
  border: '1px solid #f1f5f9',
  transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)',
  transform: hoveredResume.value === index ? 'translateY(-4px)' : 'translateY(0)',
  cursor: 'default'
})

const resumeIconStyle = (color) => ({
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

const resumeContentStyle = {
  flex: 1,
  display: 'flex',
  flexDirection: 'column',
  gap: '4px'
}

const resumeLabelStyle = {
  fontSize: '13px',
  color: '#64748b',
  margin: '0',
  fontWeight: '500'
}

const resumeValueStyle = {
  fontSize: '26px',
  fontWeight: '700',
  color: '#0f172a',
  margin: '0',
  letterSpacing: '-0.02em'
}

const resumeValueEncaisseStyle = {
  fontSize: '26px',
  fontWeight: '700',
  color: '#10b981',
  margin: '0',
  letterSpacing: '-0.02em'
}

const resumeValueCreditStyle = {
  fontSize: '26px',
  fontWeight: '700',
  color: '#ef4444',
  margin: '0',
  letterSpacing: '-0.02em'
}

const resumeSubStyle = {
  fontSize: '12px',
  color: '#94a3b8',
  margin: '0',
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

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>