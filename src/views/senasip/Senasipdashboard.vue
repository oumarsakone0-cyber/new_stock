<template>
  <div :style="containerStyle">
    <!-- Loading State -->
    <div v-if="loading" :style="loadingContainerStyle">
      <div :style="spinnerStyle"></div>
      <p :style="loadingTextStyle">Chargement des données...</p>
    </div>

    <!-- Error State -->
    <div v-if="error && !loading" :style="errorContainerStyle">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      <p :style="errorTextStyle">{{ error }}</p>
      <button :style="retryButtonStyle" @click="reloadAllData">Réessayer</button>
    </div>

    <!-- Main Content -->
    <div v-if="!loading">
      <!-- Premium Header -->
      <header :style="headerStyle" class="fade-in">
        <div :style="headerLeftStyle">
          <button 
            :style="backButtonStyle" 
            @click="goBack"
            @mouseenter="backHovered = true"
            @mouseleave="backHovered = false"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            <span>Retour</span>
          </button>
          <div :style="headerInfoStyle">
            <div :style="breadcrumbStyle">
              <span :style="breadcrumbItemStyle">AEEMCI</span>
              <svg :style="breadcrumbArrowStyle" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
              <span :style="breadcrumbActiveStyle">SENASIP</span>
            </div>
            <h1 :style="titleStyle">Affaires Sociales & Insertion</h1>
            <p :style="subtitleStyle">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              Plateforme d'accompagnement des membres AEEMCI
            </p>
          </div>
        </div>
        <div :style="headerActionsStyle">
          <button 
            :style="newBourseButtonStyle" 
            @click="showAddBourseModal = true"
            @mouseenter="newBourseHovered = true"
            @mouseleave="newBourseHovered = false"
          >
            <div :style="buttonIconContainerStyle">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
              </svg>
            </div>
            <span>Nouvelle bourse</span>
          </button>
          <button 
            :style="newEmploiButtonStyle" 
            @click="showAddEmploiModal = true"
            @mouseenter="newEmploiHovered = true"
            @mouseleave="newEmploiHovered = false"
          >
            <div :style="buttonIconContainerStyle">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
              </svg>
            </div>
            <span>Nouvelle offre</span>
          </button>
        </div>
      </header>

      <!-- Enhanced Stats - 4 Cards -->
      <div :style="statsGridStyle" class="fade-in" style="animation-delay: 0.1s">
        <div 
          :style="getStatCardStyle(0)"
          @mouseenter="hoveredStat = 0"
          @mouseleave="hoveredStat = null"
        >
          <div :style="statIconWrapperStyle('#10b981')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
          </div>
          <div :style="statContentStyle">
            <p :style="statLabelStyle">Bourses actives</p>
            <p :style="statValueStyle">{{ stats.bourses_actives || 0 }}</p>
            <p :style="statSubStyle">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
              </svg>
              {{ stats.candidatures_bourses_mois || 0 }} candidatures ce mois
            </p>
          </div>
        </div>
        
        <div 
          :style="getStatCardStyle(1)"
          @mouseenter="hoveredStat = 1"
          @mouseleave="hoveredStat = null"
        >
          <div :style="statIconWrapperStyle('#3b82f6')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
          </div>
          <div :style="statContentStyle">
            <p :style="statLabelStyle">Emplois & Stages</p>
            <p :style="statValueStyle">{{ stats.emplois_actifs || 0 }}</p>
            <p :style="statSubStyle">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
              </svg>
              {{ stats.candidatures_emplois_mois || 0 }} candidatures ce mois
            </p>
          </div>
        </div>
        
        <div 
          :style="getStatCardStyle(2)"
          @mouseenter="hoveredStat = 2"
          @mouseleave="hoveredStat = null"
        >
          <div :style="statIconWrapperStyle('#f59e0b')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div :style="statContentStyle">
            <p :style="statLabelStyle">Demandes en attente</p>
            <p :style="statValueStyle">{{ stats.demandes_en_attente || 0 }}</p>
            <p :style="statSubStyle">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
              Traitement urgent
            </p>
          </div>
        </div>

        <div 
          :style="getStatCardStyle(3)"
          @mouseenter="hoveredStat = 3"
          @mouseleave="hoveredStat = null"
        >
          <div :style="statIconWrapperStyle('#8b5cf6')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div :style="statContentStyle">
            <p :style="statLabelStyle">Aide accordée (mois)</p>
            <p :style="statValueStyle">{{ formatMontant(stats.montant_total_aide_mois || 0) }}</p>
            <p :style="statSubStyle">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ stats.demandes_approuvees_mois || 0 }} demandes
            </p>
          </div>
        </div>
      </div>

      <!-- Premium Tabs -->
      <div :style="tabsContainerStyle" class="fade-in" style="animation-delay: 0.2s">
        <button 
          v-for="tab in tabs" 
          :key="tab.id"
          :style="getTabStyle(tab.id)"
          @click="activeTab = tab.id"
          @mouseenter="hoveredTab = tab.id"
          @mouseleave="hoveredTab = null"
        >
          <span>{{ tab.label }}</span>
          <span v-if="tab.badge" :style="tabBadgeStyle">{{ tab.badge }}</span>
        </button>
      </div>

      <!-- Le reste des sections (bourses, emplois, aide) viendra après -->
      <div :style="{ padding: '40px', textAlign: 'center', background: 'white', borderRadius: '20px', boxShadow: '0 2px 8px rgba(0,0,0,0.06)' }">
        <p style="font-size: 18px; color: #64748b; font-weight: 600;">
          Section {{ activeTab }} - Interface complète à venir
        </p>
        <p style="font-size: 14px; color: #94a3b8; margin-top: 8px;">
          Utilisez les fichiers fournis pour compléter chaque section
        </p>
      </div>

    </div>
  </div>
</template>

<script setup>
// COPIEZ ICI LE CONTENU DU FICHIER senasip-dashboard-script.js
// SCRIPT COMPLET POUR SENASIP DASHBOARD
// Ajoutez ce code dans votre section <script setup>

import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// API Configuration
const API_BASE_URL = 'http://votre-api.com/senasip_api.php'

// State
const loading = ref(false)
const saving = ref(false)
const error = ref(null)

// Hover states
const backHovered = ref(false)
const newBourseHovered = ref(false)
const newEmploiHovered = ref(false)
const hoveredStat = ref(null)
const hoveredTab = ref(null)
const hoveredCard = ref(null)

// Active tab
const activeTab = ref('bourses')
const candidatureTab = ref('bourses')

// Search and filters
const searchBourses = ref('')
const filterTypeBourse = ref('')
const filterNiveauBourse = ref('')
const searchEmplois = ref('')
const filterTypeEmploi = ref('')
const filterPaysEmploi = ref('')

// Modals
const showAddBourseModal = ref(false)
const showAddEmploiModal = ref(false)
const showDemandeAideModal = ref(false)

// Data
const bourses = ref([])
const emplois = ref([])
const demandes = ref([])
const mesCandidaturesBourses = ref([])
const mesCandidaturesEmplois = ref([])

const stats = ref({
  bourses_actives: 0,
  emplois_actifs: 0,
  candidatures_bourses_mois: 0,
  candidatures_emplois_mois: 0,
  demandes_en_attente: 0,
  demandes_approuvees_mois: 0,
  montant_total_aide_mois: 0
})

// Tabs configuration
const tabs = computed(() => [
  { id: 'bourses', label: 'Bourses', badge: bourses.value.length },
  { id: 'emplois', label: 'Emplois & Stages', badge: emplois.value.length },
  { id: 'aide', label: 'Aide Sociale', badge: stats.value.demandes_en_attente },
  { id: 'candidatures', label: 'Mes Candidatures', badge: null }
])

// Computed
const filteredBourses = computed(() => {
  let result = bourses.value

  if (searchBourses.value) {
    const search = searchBourses.value.toLowerCase()
    result = result.filter(b => 
      b.titre.toLowerCase().includes(search) ||
      b.organisme.toLowerCase().includes(search) ||
      b.pays.toLowerCase().includes(search)
    )
  }

  if (filterTypeBourse.value) {
    result = result.filter(b => b.type_bourse === filterTypeBourse.value)
  }

  if (filterNiveauBourse.value) {
    result = result.filter(b => b.niveau_etude === filterNiveauBourse.value)
  }

  return result
})

const filteredEmplois = computed(() => {
  let result = emplois.value

  if (searchEmplois.value) {
    const search = searchEmplois.value.toLowerCase()
    result = result.filter(e => 
      e.titre_poste.toLowerCase().includes(search) ||
      e.entreprise.toLowerCase().includes(search) ||
      e.localisation.toLowerCase().includes(search)
    )
  }

  if (filterTypeEmploi.value) {
    result = result.filter(e => e.type_opportunite === filterTypeEmploi.value)
  }

  if (filterPaysEmploi.value) {
    result = result.filter(e => e.pays === filterPaysEmploi.value)
  }

  return result
})

// API Methods
const loadBourses = async () => {
  try {
    loading.value = true
    const response = await fetch(`${API_BASE_URL}?request=bourses&statut=active`)
    const data = await response.json()
    if (data.success) {
      bourses.value = data.data
    }
  } catch (err) {
    console.error('Erreur chargement bourses:', err)
    error.value = 'Erreur lors du chargement des bourses'
  } finally {
    loading.value = false
  }
}

const loadEmplois = async () => {
  try {
    const response = await fetch(`${API_BASE_URL}?request=emplois&statut=active`)
    const data = await response.json()
    if (data.success) {
      emplois.value = data.data
    }
  } catch (err) {
    console.error('Erreur chargement emplois:', err)
  }
}

const loadDemandes = async () => {
  const idMembre = localStorage.getItem('id_membre')
  try {
    const response = await fetch(`${API_BASE_URL}?request=demandes-aide&id_membre=${idMembre}`)
    const data = await response.json()
    if (data.success) {
      demandes.value = data.data
    }
  } catch (err) {
    console.error('Erreur chargement demandes:', err)
  }
}

const loadStatistiques = async () => {
  try {
    const response = await fetch(`${API_BASE_URL}?request=statistiques`)
    const data = await response.json()
    if (data.success) {
      stats.value = data.data
    }
  } catch (err) {
    console.error('Erreur chargement stats:', err)
  }
}

const loadCandidaturesBourses = async () => {
  const idMembre = localStorage.getItem('id_membre')
  try {
    const response = await fetch(`${API_BASE_URL}?request=candidatures-bourses&id_membre=${idMembre}`)
    const data = await response.json()
    if (data.success) {
      mesCandidaturesBourses.value = data.data
    }
  } catch (err) {
    console.error('Erreur chargement candidatures bourses:', err)
  }
}

const loadCandidaturesEmplois = async () => {
  const idMembre = localStorage.getItem('id_membre')
  try {
    const response = await fetch(`${API_BASE_URL}?request=candidatures-emplois&id_membre=${idMembre}`)
    const data = await response.json()
    if (data.success) {
      mesCandidaturesEmplois.value = data.data
    }
  } catch (err) {
    console.error('Erreur chargement candidatures emplois:', err)
  }
}

const reloadAllData = async () => {
  await Promise.all([
    loadBourses(),
    loadEmplois(),
    loadDemandes(),
    loadStatistiques(),
    loadCandidaturesBourses(),
    loadCandidaturesEmplois()
  ])
}

// Navigation Methods
const goBack = () => {
  router.push('/') // Ajustez selon votre route
}

const viewBourseDetails = (bourse) => {
  router.push(`/senasip/bourse/${bourse.id}`)
}

const viewEmploiDetails = (emploi) => {
  router.push(`/senasip/emploi/${emploi.id}`)
}

const viewDemandeDetails = (demande) => {
  router.push(`/senasip/demande/${demande.id}`)
}

// Format Methods
const formatMontant = (montant) => {
  return new Intl.NumberFormat('fr-FR', { 
    style: 'currency', 
    currency: 'XOF',
    minimumFractionDigits: 0
  }).format(montant)
}

const formatSalaire = (min, max, devise) => {
  if (!min) return 'Non spécifié'
  if (max) return `${min} - ${max} ${devise}`
  return `${min}+ ${devise}`
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const truncateText = (text, length) => {
  if (text.length <= length) return text
  return text.substring(0, length) + '...'
}

// Lifecycle
onMounted(() => {
  reloadAllData()
})

// EXEMPLE DE DONNÉES POUR TESTER (à supprimer une fois l'API connectée)
if (!API_BASE_URL.includes('votre-api')) {
  // Données de test
  bourses.value = [
    {
      id: 1,
      titre: 'Bourse d\'Excellence Master en France',
      organisme: 'Campus France',
      pays: 'France',
      type_bourse: 'études',
      niveau_etude: 'master',
      priorite: 'haute',
      jours_restants: 30,
      nombre_vues: 245,
      nombre_candidatures: 18
    },
    {
      id: 2,
      titre: 'Programme de Recherche Doctorale',
      organisme: 'UNESCO',
      pays: 'Canada',
      type_bourse: 'recherche',
      niveau_etude: 'doctorat',
      priorite: 'urgente',
      jours_restants: 5,
      nombre_vues: 189,
      nombre_candidatures: 32
    }
  ]

  emplois.value = [
    {
      id: 1,
      titre_poste: 'Développeur Full Stack',
      entreprise: 'Tech Solutions CI',
      type_opportunite: 'emploi',
      localisation: 'Abidjan, Côte d\'Ivoire',
      pays: 'Côte d\'Ivoire',
      salaire_min: 500000,
      salaire_max: 800000,
      devise: 'FCFA',
      jours_restants: 15,
      nombre_vues: 312,
      nombre_candidatures: 45
    }
  ]

  stats.value = {
    bourses_actives: 24,
    emplois_actifs: 18,
    candidatures_bourses_mois: 156,
    candidatures_emplois_mois: 203,
    demandes_en_attente: 12,
    demandes_approuvees_mois: 8,
    montant_total_aide_mois: 2500000
  }
}
</script>

<style>
/* COPIEZ ICI LE CONTENU DU FICHIER senasip-premium-styles.js */

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

.opportunity-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
  border-color: #8b5cf6;
}

.view-btn:hover {
  background: #8b5cf6;
  color: white;
}

input:focus,
select:focus {
  border-color: #8b5cf6;
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

/* ==================== STYLES CSS PREMIUM SENASIP ==================== */

/* Container principal */
.senasip-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
  padding: 32px;
  font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Loading State */
.loading-container {
  text-align: center;
  padding: 80px 20px;
  color: #64748b;
}

.loading-text {
  margin-top: 16px;
  font-size: 16px;
  font-weight: 500;
}

.spinner {
  width: 48px;
  height: 48px;
  margin: 0 auto;
  border: 4px solid #f1f5f9;
  border-top: 4px solid #8b5cf6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

/* Error State */
.error-container {
  text-align: center;
  padding: 80px 20px;
  color: #ef4444;
}

.error-text {
  margin-top: 16px;
  font-size: 16px;
  font-weight: 500;
}

.retry-button {
  margin-top: 24px;
  padding: 12px 32px;
  background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
  color: white;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  box-shadow: 0 4px 12px rgba(139, 92, 246, 0.25);
  transition: all 0.3s ease;
}

.retry-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(139, 92, 246, 0.35);
}

/* Header Premium */
.senasip-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 32px;
  flex-wrap: wrap;
  gap: 20px;
}

.header-left {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  flex-wrap: wrap;
}

.header-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.header-info {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.breadcrumb {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 4px;
}

.breadcrumb-item {
  font-size: 13px;
  color: #94a3b8;
  font-weight: 500;
}

.breadcrumb-arrow {
  width: 14px;
  height: 14px;
  color: #cbd5e1;
  stroke-width: 2;
}

.breadcrumb-active {
  font-size: 13px;
  color: #1e293b;
  font-weight: 600;
}

.back-button {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 18px;
  background-color: white;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  color: #475569;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.back-button:hover {
  background-color: #f8fafc;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transform: translateX(-4px);
}

.senasip-title {
  font-size: 32px;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
  letter-spacing: -0.02em;
}

.senasip-subtitle {
  font-size: 14px;
  color: #64748b;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: 500;
}

.btn-new-bourse,
.btn-new-emploi {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 14px 24px;
  color: white;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  letter-spacing: 0.01em;
}

.btn-new-bourse {
  background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
  box-shadow: 0 4px 12px rgba(139, 92, 246, 0.25);
}

.btn-new-bourse:hover {
  background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
  box-shadow: 0 12px 28px rgba(139, 92, 246, 0.35);
  transform: translateY(-2px);
}

.btn-new-emploi {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
}

.btn-new-emploi:hover {
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  box-shadow: 0 12px 28px rgba(59, 130, 246, 0.35);
  transform: translateY(-2px);
}

.button-icon-container {
  width: 32px;
  height: 32px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Enhanced Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 20px;
  margin-bottom: 32px;
}

.stat-card {
  background-color: white;
  border-radius: 20px;
  padding: 24px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  border: 1px solid #f1f5f9;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: default;
}

.stat-card:hover {
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.1);
  transform: translateY(-4px);
}

.stat-icon-wrapper {
  width: 56px;
  height: 56px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-icon-green {
  background: rgba(16, 185, 129, 0.12);
  color: #10b981;
}

.stat-icon-blue {
  background: rgba(59, 130, 246, 0.12);
  color: #3b82f6;
}

.stat-icon-orange {
  background: rgba(245, 158, 11, 0.12);
  color: #f59e0b;
}

.stat-icon-purple {
  background: rgba(139, 92, 246, 0.12);
  color: #8b5cf6;
}

.stat-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.stat-label {
  font-size: 13px;
  color: #64748b;
  margin: 0;
  font-weight: 500;
}

.stat-value {
  font-size: 26px;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
  letter-spacing: -0.02em;
}

.stat-sub {
  font-size: 12px;
  color: #94a3b8;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 4px;
  font-weight: 500;
}

/* Premium Tabs */
.tabs-container {
  display: flex;
  gap: 8px;
  margin-bottom: 24px;
  background-color: #f1f5f9;
  padding: 6px;
  border-radius: 12px;
  width: fit-content;
  flex-wrap: wrap;
}

.tab-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  border: none;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  background-color: transparent;
  color: #64748b;
  transition: all 0.2s;
  letter-spacing: 0.01em;
}

.tab-btn.active {
  background-color: white;
  color: #1e293b;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.tab-badge {
  background-color: #ef4444;
  color: white;
  padding: 3px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 700;
}

/* Table Container */
.table-container {
  background-color: white;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  border: 1px solid #f1f5f9;
}

/* Filtres */
.filters-container {
  padding: 24px;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.search-wrapper {
  flex: 1;
  min-width: 250px;
  position: relative;
  display: flex;
  align-items: center;
}

.search-wrapper svg {
  position: absolute;
  left: 16px;
  color: #94a3b8;
  pointer-events: none;
}

.search-input {
  width: 100%;
  padding: 12px 16px 12px 44px;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 500;
  color: #1e293b;
  outline: none;
  transition: all 0.2s;
}

.search-input:focus {
  border-color: #8b5cf6;
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

.select-filter {
  padding: 12px 16px;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 500;
  color: #1e293b;
  outline: none;
  cursor: pointer;
  background-color: #ffffff;
  transition: all 0.2s;
}

.select-filter:focus {
  border-color: #8b5cf6;
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

/* Cards Grid */
.cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 20px;
  padding: 24px;
}

.opportunity-card {
  background: #ffffff;
  border-radius: 16px;
  padding: 20px;
  border: 2px solid #f1f5f9;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
}

.opportunity-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
  border-color: #8b5cf6;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.priority-badge,
.type-badge {
  padding: 4px 12px;
  border-radius: 8px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.priority-normale {
  background: #f3f4f6;
  color: #6b7280;
}

.priority-haute {
  background: #fef3c7;
  color: #d97706;
}

.priority-urgente {
  background: #fee2e2;
  color: #dc2626;
}

.type-emploi {
  background: #dbeafe;
  color: #1e40af;
}

.type-stage {
  background: #fef3c7;
  color: #d97706;
}

.type-alternance {
  background: #e0e7ff;
  color: #5b21b6;
}

.date-remaining {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  font-weight: 600;
}

.date-urgent {
  color: #dc2626;
}

.date-warning {
  color: #d97706;
}

.date-normal {
  color: #059669;
}

.card-title {
  font-size: 16px;
  font-weight: 700;
  color: #111827;
  margin: 0 0 8px 0;
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.card-organisme {
  font-size: 14px;
  color: #6b7280;
  margin: 0 0 16px 0;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 6px;
}

.card-meta {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid #f3f4f6;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 13px;
  color: #6b7280;
  font-weight: 500;
}

.card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.stats-group {
  display: flex;
  align-items: center;
  gap: 16px;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  color: #6b7280;
  font-weight: 600;
}

.view-btn {
  padding: 8px 16px;
  background: #f9fafb;
  color: #8b5cf6;
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 4px;
  transition: all 0.2s;
}

.view-btn:hover {
  background: #8b5cf6;
  color: white;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 80px 32px;
  color: #94a3b8;
}

.empty-text {
  font-size: 16px;
  color: #9ca3af;
  margin: 16px 0 0 0;
  font-weight: 500;
}

/* Animations */
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
  to {
    transform: rotate(360deg);
  }
}

.fade-in {
  animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) backwards;
}

/* Responsive */
@media (max-width: 768px) {
  .senasip-container {
    padding: 16px;
  }

  .senasip-header {
    flex-direction: column;
  }

  .header-actions {
    width: 100%;
  }

  .btn-new-bourse,
  .btn-new-emploi {
    flex: 1;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .cards-grid {
    grid-template-columns: 1fr;
  }

  .filters-container {
    flex-direction: column;
  }

  .search-wrapper {
    width: 100%;
  }
}
</style>