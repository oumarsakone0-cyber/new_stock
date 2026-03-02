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
    </div>

    <!-- Sidebar Navigation -->
    <aside :style="sidebarStyle">
      <div :style="sidebarHeaderStyle">
        <div :style="logoStyle">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
            <polyline points="9 22 9 12 15 12 15 22"/>
          </svg>
        </div>
        <span :style="logoTextStyle">GestShop</span>
        <!-- Close button for mobile -->
        <button :style="closeSidebarBtnStyle" @click="isMobileMenuOpen = false">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 6L6 18M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <nav :style="navStyle">
        <button 
          :style="getNavItemStyle('dashboard')" 
          @click="handleNavigate('dashboard')"
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

        <div :style="navSectionStyle">
          <span :style="navSectionTitleStyle">MAGASINS</span>
          
          <button 
            :style="getNavItemStyle('magasins')" 
            @click="handleNavigate('magasins')"
            @mouseenter="hoveredNav = 'magasins'" 
            @mouseleave="hoveredNav = null"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
              <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            <span>Liste des Magasins</span>
          </button>

          <button 
            :style="getNavItemStyle('produits')" 
            @click="handleNavigate('produits')"
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
            @click="handleNavigate('stock')"
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
            @click="handleNavigate('ventes')"
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

          <button 
            :style="getNavItemStyle('credits')" 
            @click="handleNavigate('credits')"
            @mouseenter="hoveredNav = 'credits'" 
            @mouseleave="hoveredNav = null"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
              <line x1="1" y1="10" x2="23" y2="10"/>
            </svg>
            <span>Crédits Clients</span>
          </button>
        </div>

        <div :style="navSectionStyle">
          <span :style="navSectionTitleStyle">AUTRES</span>
          
          <button 
            :style="getNavItemStyle('entrepot')" 
            @click="handleNavigate('entrepot')"
            @mouseenter="hoveredNav = 'entrepot'" 
            @mouseleave="hoveredNav = null"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 8.35V20a2 2 0 01-2 2H4a2 2 0 01-2-2V8.35A2 2 0 013.26 6.5l8-3.2a2 2 0 011.48 0l8 3.2A2 2 0 0122 8.35z"/>
              <path d="M6 18h12"/>
              <path d="M6 14h12"/>
            </svg>
            <span>Entrepot</span>
          </button>

          <button 
            :style="getNavItemStyle('clients')" 
            @click="handleNavigate('clients')"
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

          <button 
            :style="getNavItemStyle('fournisseurs')" 
            @click="handleNavigate('fournisseurs')"
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
        </div>
      </nav>

      <div :style="sidebarFooterStyle">
        <button 
          :style="getNavItemStyle('settings')" 
          @click="handleNavigate('settings')"
          @mouseenter="hoveredNav = 'settings'" 
          @mouseleave="hoveredNav = null"
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="3"/>
            <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/>
          </svg>
          <span>Parametres</span>
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <div :style="mainContentStyle">
      <slot></slot>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  currentPage: { type: String, default: 'magasins' }
})

const emit = defineEmits(['navigate', 'back'])

const hoveredNav = ref(null)
const isMobileMenuOpen = ref(false)
const isMobile = ref(false)

const checkMobile = () => {
  isMobile.value = window.innerWidth < 768
  if (!isMobile.value) {
    isMobileMenuOpen.value = false
  }
}

onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
})

const handleNavigate = (page) => {
  isMobileMenuOpen.value = false
  if (page === 'dashboard') {
    emit('back')
  } else {
    emit('navigate', { page })
  }
}

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
  fontWeight: '700'
}

const sidebarStyle = computed(() => ({
  width: '260px',
  backgroundColor: '#1e293b',
  display: 'flex',
  flexDirection: 'column',
  position: 'fixed',
  top: '0',
  left: isMobile.value ? (isMobileMenuOpen.value ? '0' : '-280px') : '0',
  bottom: '0',
  zIndex: '100',
  transition: 'left 0.3s ease'
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
  backgroundColor: '#10b981',
  borderRadius: '10px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center'
}

const logoTextStyle = {
  fontSize: '20px',
  fontWeight: '700',
  color: 'white',
  flex: '1'
}

const closeSidebarBtnStyle = computed(() => ({
  display: isMobile.value ? 'flex' : 'none',
  background: 'none',
  border: 'none',
  color: '#94a3b8',
  cursor: 'pointer',
  padding: '4px'
}))

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

const sidebarFooterStyle = {
  padding: '16px 12px',
  borderTop: '1px solid #334155'
}

const mainContentStyle = computed(() => ({
  flex: '1',
  marginLeft: isMobile.value ? '0' : '260px',
  marginTop: isMobile.value ? '56px' : '0',
  padding: '24px',
  transition: 'margin-left 0.3s ease'
}))
</script>
