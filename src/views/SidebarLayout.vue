<template>
  <div :style="layoutStyle">
    <!-- Mobile Overlay -->
    <div 
      v-if="isMobileMenuOpen" 
      :style="overlayStyle" 
      @click="isMobileMenuOpen = false"
    ></div>

    <!-- Mobile Header -->
    <div :style="mobileHeaderStyle">
      <button :style="hamburgerBtnStyle" @click="isMobileMenuOpen = !isMobileMenuOpen">
        <svg v-if="!isMobileMenuOpen" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="3" y1="12" x2="21" y2="12"/>
          <line x1="3" y1="6" x2="21" y2="6"/>
          <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
        <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M18 6L6 18M6 6l12 12"/>
        </svg>
      </button>
      <div :style="mobileLogoStyle">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
          <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
          <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        <span>GestShop</span>
      </div>
      <!-- User Info Mobile -->
      <div :style="mobileUserStyle">
        <span>{{ userInitials }}</span>
      </div>
    </div>

    <!-- Sidebar Navigation -->
    <aside :style="sidebarComputedStyle">
      <div :style="sidebarHeaderStyle">
        <div :style="logoStyle">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
            <polyline points="9 22 9 12 15 12 15 22"/>
          </svg>
        </div>
        <span :style="logoTextStyle">GestShop</span>
        <button v-if="isMobile" :style="closeSidebarBtnStyle" @click="isMobileMenuOpen = false">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 6L6 18M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- User Info Card -->
      <div :style="userCardStyle">
        <div :style="userAvatarStyle">
          {{ userInitials }}
        </div>
        <div :style="userInfoStyle">
          <div :style="userNameStyle">{{ userName }}</div>
          <div :style="userRoleStyle">{{ userRoleLabel }}</div>
        </div>
      </div>

      <nav :style="navStyle">
        <button 
          :style="getNavItemStyle('dashboard')" 
          @click="navigateTo('/')"
          @mouseenter="hoveredNav = 'dashboard'" 
          @mouseleave="hoveredNav = null"
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="7" height="7"/>
            <rect x="14" y="3" width="7" height="7"/>
            <rect x="14" y="14" width="7" height="7"/>
            <rect x="3" y="14" width="7" height="7"/>
          </svg>
          <span>Dashboard</span>
        </button>

        <!-- Section Magasins (si accès) -->
        <div v-if="authStore.hasAccessToMagasins" :style="navSectionStyle">
          <span :style="navSectionTitleStyle">MAGASINS</span>
          
          <button 
            :style="getNavItemStyle('magasins')" 
            @click="navigateTo('/magasins')"
            @mouseenter="hoveredNav = 'magasins'" 
            @mouseleave="hoveredNav = null"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
              <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            <span>Liste des Magasins</span>
            <span v-if="accessibleMagasinsCount !== 'all'" :style="countBadgeStyle">{{ accessibleMagasinsCount }}</span>
          </button>

          <button 
            :style="getNavItemStyle('produits')" 
            @click="navigateTo(currentMagasinId ? `/magasins/${currentMagasinId}/produits` : '/magasins')"
            @mouseenter="hoveredNav = 'produits'" 
            @mouseleave="hoveredNav = null"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
            </svg>
            <span>Produits</span>
          </button>

          <button 
            :style="getNavItemStyle('stock')" 
            @click="navigateTo(currentMagasinId ? `/magasins/${currentMagasinId}/stock` : '/magasins')"
            @mouseenter="hoveredNav = 'stock'" 
            @mouseleave="hoveredNav = null"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
            </svg>
            <span>Gestion Stock</span>
          </button>

          <button 
            :style="getNavItemStyle('ventes')" 
            @click="navigateTo(currentMagasinId ? `/points_de_vente` : '/points_de_vente')"
            @mouseenter="hoveredNav = 'ventes'" 
            @mouseleave="hoveredNav = null"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="9" cy="21" r="1"/>
              <circle cx="20" cy="21" r="1"/>
              <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
            </svg>
            <span>Ventes</span>
          </button>
        </div>

        <!-- Section Autres -->
        <div :style="navSectionStyle">
          <span :style="navSectionTitleStyle">AUTRES</span>
          
          <!-- Entrepôts (si accès) -->
          <button 
            v-if="authStore.hasAccessToEntrepots"
            :style="getNavItemStyle('entrepot')" 
            @click="navigateTo('/entrepots')"
            @mouseenter="hoveredNav = 'entrepot'" 
            @mouseleave="hoveredNav = null"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 8.35V20a2 2 0 01-2 2H4a2 2 0 01-2-2V8.35A2 2 0 013.26 6.5l8-3.2a2 2 0 011.48 0l8 3.2A2 2 0 0122 8.35z"/>
              <path d="M6 18h12"/>
              <path d="M6 14h12"/>
            </svg>
            <span>Entrepôts</span>
            <span v-if="accessibleEntrepotsCount !== 'all'" :style="countBadgeStyle">{{ accessibleEntrepotsCount }}</span>
          </button>

          <!-- Clients (si accès) -->
          <button 
            v-if="authStore.hasAccessToClients"
            :style="getNavItemStyle('clients')" 
            @click="navigateTo('/clients')"
            @mouseenter="hoveredNav = 'clients'" 
            @mouseleave="hoveredNav = null"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 00-3-3.87"/>
              <path d="M16 3.13a4 4 0 010 7.75"/>
            </svg>
            <span>Clients</span>
          </button>

          <!-- Fournisseurs (si accès) -->
          <button 
            v-if="authStore.hasAccessToFournisseurs"
            :style="getNavItemStyle('fournisseurs')" 
            @click="navigateTo('/fournisseurs')"
            @mouseenter="hoveredNav = 'fournisseurs'" 
            @mouseleave="hoveredNav = null"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="1" y="3" width="15" height="13"/>
              <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
              <circle cx="5.5" cy="18.5" r="2.5"/>
              <circle cx="18.5" cy="18.5" r="2.5"/>
            </svg>
            <span>Fournisseurs</span>
          </button>

          <!-- Comptabilités (si accès) -->
          <button 
            v-if="authStore.hasAccessToComptabilites"
            :style="getNavItemStyle('comptabilites')" 
            @click="navigateTo('/comptabilites')"
            @mouseenter="hoveredNav = 'comptabilites'" 
            @mouseleave="hoveredNav = null"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="4" y="3" width="16" height="18" rx="2"/>
              <line x1="8" y1="7" x2="16" y2="7"/>
              <line x1="8" y1="11" x2="16" y2="11"/>
              <line x1="8" y1="15" x2="12" y2="15"/>
              <line x1="14" y1="15" x2="16" y2="15"/>
            </svg>
            <span>Comptabilités</span>
          </button>

          <!-- Utilisateurs (admin uniquement) -->
          <button 
            v-if="authStore.hasAccessToUsers"
            :style="getNavItemStyle('utilisateurs')" 
            @click="navigateTo('/utilisateurs')"
            @mouseenter="hoveredNav = 'utilisateurs'" 
            @mouseleave="hoveredNav = null"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
            <span>Utilisateurs</span>
            <span :style="adminBadgeStyle">Admin</span>
          </button>
        </div>
      </nav>

      <div :style="sidebarFooterStyle">
        <button 
          :style="getNavItemStyle('settings')" 
          @click="navigateTo('/parametres')"
          @mouseenter="hoveredNav = 'settings'" 
          @mouseleave="hoveredNav = null"
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="3"/>
            <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/>
          </svg>
          <span>Paramètres</span>
        </button>
        
        <button 
          :style="logoutButtonStyle" 
          @click="handleLogout"
          @mouseenter="hoveredNav = 'logout'" 
          @mouseleave="hoveredNav = null"
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
          <span>Déconnexion</span>
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <div :style="mainContentComputedStyle">
      <slot></slot>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/authStore'

const props = defineProps({
  currentPage: { type: String, default: 'magasins' },
  magasinId: { type: [String, Number], default: null }
})

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const hoveredNav = ref(null)
const isMobileMenuOpen = ref(false)
const isMobile = ref(false)

// User info computed
const userName = computed(() => {
  if (!authStore.currentUser) return 'Utilisateur'
  return `${authStore.currentUser.prenom} ${authStore.currentUser.nom}`
})

const userInitials = computed(() => {
  if (!authStore.currentUser) return 'U'
  const prenom = authStore.currentUser.prenom || ''
  const nom = authStore.currentUser.nom || ''
  return `${prenom.charAt(0)}${nom.charAt(0)}`.toUpperCase()
})

const userRoleLabel = computed(() => {
  const role = authStore.currentUser?.role
  const labels = {
    'admin': 'Administrateur',
    'manager': 'Manager',
    'vendeur': 'Vendeur'
  }
  return labels[role] || role
})

// Access counts
const accessibleMagasinsCount = computed(() => {
  const acces = authStore.accessibleMagasins
  if (acces === 'all') return 'all'
  return Array.isArray(acces) ? acces.length : 0
})

const accessibleEntrepotsCount = computed(() => {
  const acces = authStore.accessibleEntrepots
  if (acces === 'all') return 'all'
  return Array.isArray(acces) ? acces.length : 0
})

// Get magasinId from route params or props
const currentMagasinId = computed(() => {
  return props.magasinId || route.params.id || null
})

const checkMobile = () => {
  if (typeof window !== 'undefined') {
    isMobile.value = window.innerWidth < 768
    if (!isMobile.value) {
      isMobileMenuOpen.value = false
    }
  }
}

onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
})

const navigateTo = (path) => {
  isMobileMenuOpen.value = false
  router.push(path)
}

const handleLogout = () => {
  if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
    authStore.logout()
    router.push('/login')
  }
}

// Styles
const layoutStyle = {
  display: 'flex',
  minHeight: '100vh',
  backgroundColor: '#f8fafc'
}

const overlayStyle = {
  position: 'fixed',
  top: '0',
  left: '0',
  right: '0',
  bottom: '0',
  backgroundColor: 'rgba(0, 0, 0, 0.5)',
  zIndex: '90'
}

const mobileHeaderStyle = computed(() => ({
  display: isMobile.value ? 'flex' : 'none',
  alignItems: 'center',
  justifyContent: 'space-between',
  gap: '12px',
  padding: '12px 16px',
  backgroundColor: '#1e293b',
  position: 'fixed',
  top: '0',
  left: '0',
  right: '0',
  zIndex: '80'
}))

const hamburgerBtnStyle = {
  background: 'none',
  border: 'none',
  color: 'white',
  cursor: 'pointer',
  padding: '8px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center'
}

const mobileLogoStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '8px',
  color: 'white',
  fontSize: '18px',
  fontWeight: '700',
  flex: 1
}

const mobileUserStyle = {
  width: '36px',
  height: '36px',
  borderRadius: '50%',
  background: 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  fontSize: '14px',
  fontWeight: '700',
  color: 'white'
}

const sidebarComputedStyle = computed(() => ({
  width: '260px',
  backgroundColor: '#1e293b',
  display: 'flex',
  flexDirection: 'column',
  position: 'fixed',
  top: '0',
  left: isMobile.value ? (isMobileMenuOpen.value ? '0' : '-280px') : '0',
  bottom: '0',
  zIndex: '100',
  transition: 'left 0.3s ease',
  boxShadow: '2px 0 8px rgba(0, 0, 0, 0.1)'
}))

const sidebarHeaderStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '12px',
  padding: '20px',
  borderBottom: '1px solid #334155'
}

const logoStyle = {
  width: '40px',
  height: '40px',
  background: 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
  borderRadius: '10px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  boxShadow: '0 4px 12px rgba(16, 185, 129, 0.3)'
}

const logoTextStyle = {
  fontSize: '20px',
  fontWeight: '700',
  color: 'white',
  flex: '1'
}

const closeSidebarBtnStyle = {
  display: 'flex',
  background: 'none',
  border: 'none',
  color: '#94a3b8',
  cursor: 'pointer',
  padding: '4px'
}

const userCardStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '12px',
  padding: '16px 20px',
  margin: '12px 12px 0',
  background: 'rgba(148, 163, 184, 0.1)',
  borderRadius: '12px',
  border: '1px solid rgba(148, 163, 184, 0.2)'
}

const userAvatarStyle = {
  width: '44px',
  height: '44px',
  borderRadius: '12px',
  background: 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  fontSize: '16px',
  fontWeight: '700',
  color: 'white',
  boxShadow: '0 4px 12px rgba(16, 185, 129, 0.3)'
}

const userInfoStyle = {
  flex: 1,
  overflow: 'hidden'
}

const userNameStyle = {
  fontSize: '14px',
  fontWeight: '600',
  color: 'white',
  marginBottom: '2px',
  whiteSpace: 'nowrap',
  overflow: 'hidden',
  textOverflow: 'ellipsis'
}

const userRoleStyle = {
  fontSize: '12px',
  color: '#94a3b8',
  fontWeight: '500'
}

const navStyle = {
  flex: '1',
  padding: '16px 12px',
  overflowY: 'auto'
}

const navSectionStyle = {
  marginTop: '24px'
}

const navSectionTitleStyle = {
  fontSize: '11px',
  fontWeight: '600',
  color: '#64748b',
  letterSpacing: '0.05em',
  padding: '0 12px',
  marginBottom: '8px',
  display: 'block'
}

const getNavItemStyle = (page) => {
  const active = props.currentPage === page
  const hovered = hoveredNav.value === page
  return {
    display: 'flex',
    alignItems: 'center',
    gap: '12px',
    width: '100%',
    padding: '12px',
    backgroundColor: active ? '#334155' : hovered ? '#334155' : 'transparent',
    border: 'none',
    borderRadius: '8px',
    cursor: 'pointer',
    fontSize: '14px',
    fontWeight: '500',
    color: active ? 'white' : '#94a3b8',
    textAlign: 'left',
    transition: 'all 0.2s ease',
    marginBottom: '4px'
  }
}

const countBadgeStyle = {
  marginLeft: 'auto',
  padding: '2px 8px',
  borderRadius: '12px',
  fontSize: '11px',
  fontWeight: '600',
  backgroundColor: 'rgba(16, 185, 129, 0.2)',
  color: '#10b981'
}

const adminBadgeStyle = {
  marginLeft: 'auto',
  padding: '2px 8px',
  borderRadius: '12px',
  fontSize: '10px',
  fontWeight: '700',
  backgroundColor: 'rgba(245, 158, 11, 0.2)',
  color: '#f59e0b',
  letterSpacing: '0.05em',
  textTransform: 'uppercase'
}

const sidebarFooterStyle = {
  padding: '16px 12px',
  borderTop: '1px solid #334155'
}

const logoutButtonStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '12px',
  width: '100%',
  padding: '12px',
  backgroundColor: hoveredNav.value === 'logout' ? 'rgba(239, 68, 68, 0.1)' : 'transparent',
  border: 'none',
  borderRadius: '8px',
  cursor: 'pointer',
  fontSize: '14px',
  fontWeight: '500',
  color: hoveredNav.value === 'logout' ? '#ef4444' : '#94a3b8',
  textAlign: 'left',
  transition: 'all 0.2s ease',
  marginTop: '8px'
}

const mainContentComputedStyle = computed(() => ({
  flex: '1',
  marginLeft: isMobile.value ? '0' : '260px',
  marginTop: isMobile.value ? '56px' : '0',
  padding: '24px',
  transition: 'margin-left 0.3s ease'
}))
</script>

<style scoped>
/* Custom scrollbar for sidebar */
nav::-webkit-scrollbar {
  width: 6px;
}

nav::-webkit-scrollbar-track {
  background: transparent;
}

nav::-webkit-scrollbar-thumb {
  background: #475569;
  border-radius: 3px;
}

nav::-webkit-scrollbar-thumb:hover {
  background: #64748b;
}
</style>