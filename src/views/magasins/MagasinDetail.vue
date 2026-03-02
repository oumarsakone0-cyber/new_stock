<template>
  <SidebarLayout currentPage="magasins" :magasinId="magasinId">
    <!-- Loading State -->
    <div v-if="loading" :style="loadingStyle">
      <div :style="spinnerStyle"></div>
      <p>Chargement des informations...</p>
    </div>

    <!-- Error State -->
    <div v-if="error" :style="errorStyle">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      <p>{{ error }}</p>
      <button :style="retryButtonStyle" @click="loadMagasinData">Réessayer</button>
    </div>

    <!-- Content -->
    <div v-if="!loading && !error">
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
              <span :style="breadcrumbItemStyle">Magasins</span>
              <svg :style="breadcrumbArrowStyle" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
              <span :style="breadcrumbActiveStyle">{{ magasin.nom }}</span>
            </div>
            <h1 :style="titleStyle">{{ magasin.nom }}</h1>
            <p :style="subtitleStyle">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                <circle cx="12" cy="10" r="3"/>
              </svg>
              {{ magasin.adresse }}
            </p>
          </div>
        </div>
        <div :style="headerActionsStyle">
          <span :style="getStatusStyle(magasin.actif)">
            <span :style="statusDotStyle(magasin.actif)"></span>
            {{ magasin.actif ? 'Actif' : 'Inactif' }}
          </span>
          <button :style="editButtonStyle" @mouseenter="editHovered = true" @mouseleave="editHovered = false">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
              <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
            </svg>
            Modifier
          </button>
        </div>
      </header>

      <!-- Enhanced Stats Cards -->
      <div :style="statsGridStyle" class="fade-in" style="animation-delay: 0.1s">
        <div 
          :style="getStatCardStyle(0)"
          @mouseenter="hoveredStat = 0"
          @mouseleave="hoveredStat = null"
        >
          <div :style="statCardHeaderStyle">
            <div :style="getStatIconStyle('#3b82f6', 0)">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
              </svg>
            </div>
            <div :style="trendBadgeStyle('#10b981')">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
              </svg>
              {{ stats.stock_total > 0 ? '+' : '' }}{{ Math.round((stats.stock_total / stats.total_produits) * 100) || 0 }}%
            </div>
          </div>
          <div :style="statContentStyle">
            <p :style="statLabelStyle">Produits en stock</p>
            <p :style="statValueStyle">{{ stats.stock_total || 0 }}</p>
            <p :style="statSubtextStyle">sur {{ stats.total_produits || 0 }} articles référencés</p>
          </div>
        </div>

        <div 
          :style="getStatCardStyle(1)"
          @mouseenter="hoveredStat = 1"
          @mouseleave="hoveredStat = null"
        >
          <div :style="statCardHeaderStyle">
            <div :style="getStatIconStyle('#10b981', 1)">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
              </svg>
            </div>
            <div :style="trendBadgeStyle('#10b981')">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
              </svg>
              {{ stats.ventes_mois?.nombre_ventes || 0 }}%
            </div>
          </div>
          <div :style="statContentStyle">
            <p :style="statLabelStyle">Chiffre d'affaires</p>
            <p :style="statValueStyle">{{ formatMoney(stats.ventes_mois?.montant_total || 0) }}</p>
            <p :style="statSubtextStyle">ce mois-ci</p>
          </div>
        </div>

        <div 
          :style="getStatCardStyle(2)"
          @mouseenter="hoveredStat = 2"
          @mouseleave="hoveredStat = null"
        >
          <div :style="statCardHeaderStyle">
            <div :style="getStatIconStyle('#f59e0b', 2)">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                <circle cx="9" cy="21" r="1"/>
                <circle cx="20" cy="21" r="1"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
              </svg>
            </div>
            <div :style="trendBadgeStyle('#10b981')">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
              </svg>
              {{ stats.ventes_jour?.nombre_ventes || 0 }}
            </div>
          </div>
          <div :style="statContentStyle">
            <p :style="statLabelStyle">Ventes aujourd'hui</p>
            <p :style="statValueStyle">{{ formatMoney(stats.ventes_jour?.montant_total || 0) }}</p>
            <p :style="statSubtextStyle">{{ stats.ventes_jour?.nombre_ventes || 0 }} transactions</p>
          </div>
        </div>

        <div 
          :style="getStatCardStyle(3)"
          @mouseenter="hoveredStat = 3"
          @mouseleave="hoveredStat = null"
        >
          <div :style="statCardHeaderStyle">
            <div :style="getStatIconStyle('#ef4444', 3)">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
              </svg>
            </div>
            <div :style="trendBadgeStyle('#ef4444')">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
              </svg>
              {{ stats.stock_bas || 0 }}
            </div>
          </div>
          <div :style="statContentStyle">
            <p :style="statLabelStyle">Stock bas</p>
            <p :style="statValueStyle">{{ stats.stock_bas || 0 }}</p>
            <p :style="statSubtextStyle">produits sous le seuil</p>
          </div>
        </div>
      </div>

      <!-- Navigation Cards -->
      <div :style="sectionHeaderStyle" class="fade-in" style="animation-delay: 0.2s">
        <h2 :style="sectionTitleStyle">Gestion du magasin</h2>
        <p :style="sectionSubtitleStyle">Accédez rapidement aux différents modules</p>
      </div>
      
      <div :style="navGridStyle">
        <div 
          v-for="(item, index) in navItems" 
          :key="item.id"
          :style="getNavCardStyle(index)"
          @mouseenter="hoveredNav = index"
          @mouseleave="hoveredNav = null"
          @click="navigateTo(item.route)"
          class="nav-card fade-in"
        >
          <div :style="navCardGlowStyle(index, item.color)"></div>
          <div :style="getNavIconContainerStyle(index, item.color)">
            <component :is="item.icon" />
          </div>
          <div :style="navContentStyle">
            <h3 :style="navTitleStyle">{{ item.title }}</h3>
            <p :style="navDescStyle">{{ item.description }}</p>
            <div :style="navMetaStyle">
              <span :style="navCountStyle">{{ item.count }} éléments</span>
              <svg :style="navArrowStyle(index)" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
              </svg>
            </div>
          </div>
        </div>
      </div>
    </div>
  </SidebarLayout>
</template>

<script setup>
import { ref, computed, h, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import SidebarLayout from '../SidebarLayout.vue'

const router = useRouter()
const route = useRoute()

const API_BASE_URL = 'https://sogetrag.com/apistok'

const magasinId = computed(() => route.params.id)

const magasin = ref({
  id: null,
  nom: '',
  adresse: '',
  telephone: '',
  couleur: '#10b981',
  actif: true
})

const stats = ref({
  ventes_jour: { nombre_ventes: 0, montant_total: 0, montant_paye: 0 },
  ventes_mois: { nombre_ventes: 0, montant_total: 0, montant_paye: 0 },
  stock_bas: 0,
  total_produits: 0,
  stock_total: 0
})

const backHovered = ref(false)
const editHovered = ref(false)
const hoveredStat = ref(null)
const hoveredNav = ref(null)
const loading = ref(false)
const error = ref(null)

const formatMoney = (amount) => {
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF', minimumFractionDigits: 0 }).format(amount)
}

const goBack = () => router.push('/magasins')
const navigateTo = (routePath) => router.push(routePath)

// Icons as render functions
const ProductIcon = () => h('svg', { width: 24, height: 24, viewBox: '0 0 24 24', fill: 'none', stroke: 'white', 'stroke-width': 2.5 }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z' }),
  h('polyline', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', points: '3.27 6.96 12 12.01 20.73 6.96' }),
  h('line', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', x1: 12, y1: 22.08, x2: 12, y2: 12 })
])

const SalesIcon = () => h('svg', { width: 24, height: 24, viewBox: '0 0 24 24', fill: 'none', stroke: 'white', 'stroke-width': 2.5 }, [
  h('circle', { cx: 9, cy: 21, r: 1 }),
  h('circle', { cx: 20, cy: 21, r: 1 }),
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6' })
])

const StockIcon = () => h('svg', { width: 24, height: 24, viewBox: '0 0 24 24', fill: 'none', stroke: 'white', 'stroke-width': 2.5 }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4' })
])

const navItems = computed(() => [
  { id: 1, title: 'Produits', description: 'Gérer le catalogue et les prix', route: `/magasins/${magasinId.value}/produits`, color: '#3b82f6', icon: ProductIcon, count: stats.value.total_produits || 0 },
  { id: 2, title: 'Ventes', description: 'Créer et suivre les ventes', route: `/magasins/${magasinId.value}/ventes`, color: '#10b981', icon: SalesIcon, count: stats.value.ventes_mois?.nombre_ventes || 0 },
  { id: 3, title: 'Stock', description: 'Mouvements et inventaire', route: `/magasins/${magasinId.value}/stock`, color: '#f59e0b', icon: StockIcon, count: stats.value.stock_total || 0 }
])

// API Functions
const loadMagasinData = async () => {
  loading.value = true
  error.value = null
  
  try {
    // Charger les détails du magasin
    const detailsResponse = await fetch(`${API_BASE_URL}/api_magasins.php?action=details&id=${magasinId.value}`)
    const detailsData = await detailsResponse.json()
    
    if (!detailsData.success) {
      throw new Error(detailsData.error || 'Erreur lors du chargement du magasin')
    }
    
    magasin.value = detailsData.data
    
    // Charger les statistiques
    const statsResponse = await fetch(`${API_BASE_URL}/api_magasins.php?action=stats&id=${magasinId.value}`)
    const statsData = await statsResponse.json()
    
    if (statsData.success) {
      stats.value = statsData.data
    }
    
  } catch (err) {
    error.value = err.message || 'Impossible de charger les données'
    console.error('Erreur:', err)
  } finally {
    loading.value = false
  }
}

// Load data on mount
onMounted(() => {
  loadMagasinData()
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
  gap: '16px',
  flexWrap: 'wrap'
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
  transition: 'all 0.2s cubic-bezier(0.4, 0, 0.2, 1)',
  boxShadow: backHovered.value ? '0 4px 12px rgba(0, 0, 0, 0.08)' : '0 1px 3px rgba(0, 0, 0, 0.05)',
  transform: backHovered.value ? 'translateX(-4px)' : 'translateX(0)',
  letterSpacing: '0.01em'
}))

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

const headerActionsStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '12px'
}

const getStatusStyle = (actif) => ({
  display: 'inline-flex',
  alignItems: 'center',
  gap: '6px',
  padding: '8px 16px',
  borderRadius: '12px',
  fontSize: '14px',
  fontWeight: '600',
  backgroundColor: actif ? '#dcfce7' : '#fee2e2',
  color: actif ? '#166534' : '#991b1b',
  letterSpacing: '0.01em'
})

const statusDotStyle = (actif) => ({
  width: '8px',
  height: '8px',
  borderRadius: '50%',
  backgroundColor: actif ? '#22c55e' : '#ef4444',
  animation: actif ? 'pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite' : 'none'
})

const editButtonStyle = computed(() => ({
  display: 'flex',
  alignItems: 'center',
  gap: '8px',
  padding: '12px 20px',
  background: editHovered.value 
    ? 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)'
    : 'linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%)',
  color: 'white',
  border: 'none',
  borderRadius: '12px',
  cursor: 'pointer',
  fontSize: '14px',
  fontWeight: '600',
  transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
  boxShadow: editHovered.value 
    ? '0 8px 20px rgba(59, 130, 246, 0.35)'
    : '0 4px 12px rgba(59, 130, 246, 0.25)',
  transform: editHovered.value ? 'translateY(-2px)' : 'translateY(0)',
  letterSpacing: '0.01em'
}))

const statsGridStyle = {
  display: 'grid',
  gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))',
  gap: '20px',
  marginBottom: '40px'
}

const getStatCardStyle = (index) => ({
  backgroundColor: 'white',
  borderRadius: '20px',
  padding: '24px',
  boxShadow: hoveredStat.value === index 
    ? '0 12px 32px rgba(0, 0, 0, 0.1)' 
    : '0 2px 8px rgba(0, 0, 0, 0.06)',
  border: '1px solid #f1f5f9',
  transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)',
  transform: hoveredStat.value === index ? 'translateY(-4px)' : 'translateY(0)',
  cursor: 'default'
})

const statCardHeaderStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  marginBottom: '16px'
}

const getStatIconStyle = (color, index) => ({
  width: '56px',
  height: '56px',
  borderRadius: '16px',
  background: hoveredStat.value === index 
    ? `linear-gradient(135deg, ${color} 0%, ${adjustColor(color, -15)} 100%)`
    : `${color}12`,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  flexShrink: 0,
  transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)',
  transform: hoveredStat.value === index ? 'rotate(-5deg) scale(1.05)' : 'rotate(0) scale(1)',
  boxShadow: hoveredStat.value === index ? `0 8px 20px ${color}40` : 'none'
})

const trendBadgeStyle = (color) => ({
  display: 'flex',
  alignItems: 'center',
  gap: '4px',
  padding: '4px 10px',
  borderRadius: '8px',
  fontSize: '12px',
  fontWeight: '700',
  color: color,
  backgroundColor: `${color}15`,
  letterSpacing: '0.01em'
})

const statContentStyle = {
  display: 'flex',
  flexDirection: 'column',
  gap: '4px'
}

const statLabelStyle = {
  fontSize: '13px',
  color: '#64748b',
  margin: '0',
  fontWeight: '500'
}

const statValueStyle = {
  fontSize: '28px',
  fontWeight: '700',
  color: '#0f172a',
  margin: '0',
  letterSpacing: '-0.02em',
  lineHeight: '1.2'
}

const statSubtextStyle = {
  fontSize: '12px',
  color: '#94a3b8',
  margin: '0',
  fontWeight: '500'
}

const sectionHeaderStyle = {
  marginBottom: '20px'
}

const sectionTitleStyle = {
  fontSize: '20px',
  fontWeight: '700',
  color: '#0f172a',
  margin: '0 0 4px 0',
  letterSpacing: '-0.01em'
}

const sectionSubtitleStyle = {
  fontSize: '14px',
  color: '#64748b',
  margin: '0',
  fontWeight: '500'
}

const navGridStyle = {
  display: 'grid',
  gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))',
  gap: '20px',
  marginBottom: '40px'
}

const getNavCardStyle = (index) => ({
  backgroundColor: 'white',
  borderRadius: '20px',
  padding: '24px',
  cursor: 'pointer',
  boxShadow: hoveredNav.value === index 
    ? '0 16px 32px rgba(0, 0, 0, 0.12)' 
    : '0 2px 8px rgba(0, 0, 0, 0.06)',
  border: hoveredNav.value === index ? '1px solid #e2e8f0' : '1px solid #f1f5f9',
  transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)',
  transform: hoveredNav.value === index ? 'translateY(-6px)' : 'translateY(0)',
  position: 'relative'
})

const navCardGlowStyle = (index, color) => ({
  position: 'absolute',
  inset: '-2px',
  background: `linear-gradient(135deg, ${color}20, transparent)`,
  borderRadius: '20px',
  opacity: hoveredNav.value === index ? 1 : 0,
  transition: 'opacity 0.4s',
  filter: 'blur(12px)',
  zIndex: -1
})

const getNavIconContainerStyle = (index, color) => ({
  width: '64px',
  height: '64px',
  borderRadius: '16px',
  background: hoveredNav.value === index 
    ? `linear-gradient(135deg, ${color} 0%, ${adjustColor(color, -15)} 100%)`
    : `${color}12`,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  marginBottom: '16px',
  transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)',
  transform: hoveredNav.value === index ? 'rotate(-8deg) scale(1.1)' : 'rotate(0) scale(1)',
  boxShadow: hoveredNav.value === index ? `0 8px 24px ${color}40` : 'none'
})

const navContentStyle = {
  flex: 1
}

const navTitleStyle = {
  fontSize: '18px',
  fontWeight: '700',
  color: '#0f172a',
  margin: '0 0 6px 0',
  letterSpacing: '-0.01em'
}

const navDescStyle = {
  fontSize: '14px',
  color: '#64748b',
  margin: '0 0 12px 0',
  fontWeight: '500',
  lineHeight: '1.5'
}

const navMetaStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  marginTop: '12px',
  paddingTop: '12px',
  borderTop: '1px solid #f1f5f9'
}

const navCountStyle = {
  fontSize: '13px',
  fontWeight: '600',
  color: '#94a3b8'
}

const navArrowStyle = (index) => ({
  color: '#3b82f6',
  transition: 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
  transform: hoveredNav.value === index ? 'translateX(4px)' : 'translateX(0)',
  strokeWidth: '2.5'
})

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

      @keyframes pulse {
        0%, 100% {
          opacity: 1;
        }
        50% {
          opacity: 0.5;
        }
      }

      .fade-in {
        animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) backwards;
      }

      .nav-card {
        position: relative;
        overflow: hidden;
      }

      .nav-card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.3) 50%, transparent 70%);
        transform: translateX(-100%) rotate(45deg);
        transition: transform 0.6s;
      }

      .nav-card:hover::before {
        transform: translateX(100%) rotate(45deg);
      }
    </style>