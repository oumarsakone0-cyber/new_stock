<template>
  <div :style="containerStyle">
    <!-- Animated Background -->
    <div class="animated-bg">
      <div class="gradient-orb orb-1"></div>
      <div class="gradient-orb orb-2"></div>
      <div class="gradient-orb orb-3"></div>
      <div class="gradient-orb orb-4"></div>
    </div>

    <!-- Subtle Grid Pattern -->
    <div :style="gridPatternStyle"></div>

    <!-- User Info Header -->
    <div :style="userHeaderStyle" class="fade-in-down">
      <div :style="userInfoContainerStyle">
        <div :style="userAvatarStyle">
          {{ userInitials }}
        </div>
        <div :style="userDetailsStyle">
          <div :style="userNameStyle">{{ userName }}</div>
          <div :style="userRoleStyle">{{ userRoleLabel }}</div>
        </div>
      </div>
      <button 
        :style="logoutButtonStyle"
        @click="handleLogout"
        @mouseenter="logoutHovered = true"
        @mouseleave="logoutHovered = false"
      >
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
          <polyline points="16 17 21 12 16 7"/>
          <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
        <span>Déconnexion</span>
      </button>
    </div>

    <div :style="wrapperStyle">
      <!-- Header with Premium Badge -->
      <div :style="headerStyle" class="fade-in-up">
        <div :style="badgeStyle">
          <svg :style="sparkleStyle" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2l2.4 7.4h7.6l-6 4.6 2.3 7.4-6.3-4.6-6.3 4.6 2.3-7.4-6-4.6h7.6z"/>
          </svg>
          <span>Premium</span>
        </div>
        <h1 :style="titleStyle">Gestion Stock</h1>
        <p :style="subtitleStyle">Excellence & Performance au service de votre entreprise</p>
      </div>

      <!-- Quick Stats -->
      <div :style="quickStatsStyle" class="fade-in-up" style="animation-delay: 0.05s">
        <div :style="quickStatItemStyle">
          <div :style="quickStatIconStyle('#10b981')">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
            </svg>
          </div>
          <div>
            <div :style="quickStatValueStyle">{{ userAccessStats.magasins }}</div>
            <div :style="quickStatLabelStyle">Magasins</div>
          </div>
        </div>
        
        <div :style="quickStatItemStyle">
          <div :style="quickStatIconStyle('#3b82f6')">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 8.35V20a2 2 0 01-2 2H4a2 2 0 01-2-2V8.35A2 2 0 013.26 6.5l8-3.2a2 2 0 011.48 0l8 3.2A2 2 0 0122 8.35z"/>
            </svg>
          </div>
          <div>
            <div :style="quickStatValueStyle">{{ userAccessStats.entrepots }}</div>
            <div :style="quickStatLabelStyle">Entrepôts</div>
          </div>
        </div>
      </div>

      <!-- Compact Cards Grid -->
      <div :style="gridStyle">
        <!-- Magasins -->
        <button 
          v-if="authStore.hasAccessToMagasins"
          @click="navigateTo('magasins')"
          @mouseenter="hoveredCard = 'magasins'"
          @mouseleave="hoveredCard = null"
          :style="getCardStyle('magasins')"
          class="card fade-in-up"
          style="animation-delay: 0.1s"
        >
          <div :style="cardShineStyle('magasins')"></div>
          <div :style="cardContentStyle">
            <div :style="getIconContainerStyle('magasins', '#10b981')">
              <svg :style="iconStyle" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
              </svg>
            </div>
            <div :style="cardTextStyle">
              <h2 :style="cardTitleStyle">Magasins</h2>
              <p :style="cardDescStyle">Points de vente & stocks</p>
            </div>
          </div>
          <div :style="getCardFooterStyle('magasins')">
            <span :style="statsTextStyle">{{ userAccessStats.magasins }} accessibles</span>
            <svg :style="getArrowStyle('magasins')" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
            </svg>
          </div>
        </button>

        <!-- Entrepôt -->
        <button 
          v-if="authStore.hasAccessToEntrepots"
          @click="navigateTo('entrepots')"
          @mouseenter="hoveredCard = 'entrepot'"
          @mouseleave="hoveredCard = null"
          :style="getCardStyle('entrepot')"
          class="card fade-in-up"
          style="animation-delay: 0.2s"
        >
          <div :style="cardShineStyle('entrepot')"></div>
          <div :style="cardContentStyle">
            <div :style="getIconContainerStyle('entrepot', '#3b82f6')">
              <svg :style="iconStyle" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
              </svg>
            </div>
            <div :style="cardTextStyle">
              <h2 :style="cardTitleStyle">Entrepôt</h2>
              <p :style="cardDescStyle">Inventaires & logistique</p>
            </div>
          </div>
          <div :style="getCardFooterStyle('entrepot')">
            <span :style="statsTextStyle">{{ userAccessStats.entrepots }} accessibles</span>
            <svg :style="getArrowStyle('entrepot')" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
            </svg>
          </div>
        </button>

        <!-- Clients -->
        <button 
          v-if="authStore.hasAccessToClients"
          @click="navigateTo('clients')"
          @mouseenter="hoveredCard = 'clients'"
          @mouseleave="hoveredCard = null"
          :style="getCardStyle('clients')"
          class="card fade-in-up"
          style="animation-delay: 0.3s"
        >
          <div :style="cardShineStyle('clients')"></div>
          <div :style="cardContentStyle">
            <div :style="getIconContainerStyle('clients', '#f59e0b')">
              <svg :style="iconStyle" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
            </div>
            <div :style="cardTextStyle">
              <h2 :style="cardTitleStyle">Clients</h2>
              <p :style="cardDescStyle">Relations & commandes</p>
            </div>
          </div>
          <div :style="getCardFooterStyle('clients')">
            <span :style="statsTextStyle">Gestion complète</span>
            <svg :style="getArrowStyle('clients')" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
            </svg>
          </div>
        </button>

        <!-- Fournisseurs -->
        <button 
          v-if="authStore.hasAccessToFournisseurs"
          @click="navigateTo('fournisseurs')"
          @mouseenter="hoveredCard = 'fournisseurs'"
          @mouseleave="hoveredCard = null"
          :style="getCardStyle('fournisseurs')"
          class="card fade-in-up"
          style="animation-delay: 0.4s"
        >
          <div :style="cardShineStyle('fournisseurs')"></div>
          <div :style="cardContentStyle">
            <div :style="getIconContainerStyle('fournisseurs', '#8b5cf6')">
              <svg :style="iconStyle" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
              </svg>
            </div>
            <div :style="cardTextStyle">
              <h2 :style="cardTitleStyle">Fournisseurs</h2>
              <p :style="cardDescStyle">Partenaires & contrats</p>
            </div>
          </div>
          <div :style="getCardFooterStyle('fournisseurs')">
            <span :style="statsTextStyle">Gestion complète</span>
            <svg :style="getArrowStyle('fournisseurs')" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
            </svg>
          </div>
        </button>

        <!-- Utilisateurs (Admin only) -->
        <button 
          v-if="authStore.hasAccessToUsers"
          @click="navigateTo('utilisateurs')"
          @mouseenter="hoveredCard = 'utilisateurs'"
          @mouseleave="hoveredCard = null"
          :style="getCardStyle('utilisateurs')"
          class="card fade-in-up"
          style="animation-delay: 0.5s"
        >
          <div :style="cardShineStyle('utilisateurs')"></div>
          <div :style="cardContentStyle">
            <div :style="getIconContainerStyle('utilisateurs', '#ef4444')">
              <svg :style="iconStyle" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </svg>
            </div>
            <div :style="cardTextStyle">
              <h2 :style="cardTitleStyle">Utilisateurs</h2>
              <p :style="cardDescStyle">Gestion des accès</p>
            </div>
          </div>
          <div :style="getCardFooterStyle('utilisateurs')">
            <span :style="adminBadgeInCardStyle">Admin</span>
            <svg :style="getArrowStyle('utilisateurs')" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
            </svg>
          </div>
        </button>
      </div>

      <!-- No Access Message -->
      <div v-if="!hasAnyAccess" :style="noAccessStyle" class="fade-in-up">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5">
          <circle cx="12" cy="12" r="10"/>
          <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
        </svg>
        <h3 :style="noAccessTitleStyle">Aucun accès configuré</h3>
        <p :style="noAccessTextStyle">Veuillez contacter votre administrateur pour obtenir des permissions d'accès.</p>
      </div>

      <!-- Footer -->
      <div :style="footerStyle" class="fade-in-up" style="animation-delay: 0.6s">
        <div :style="footerLineStyle"></div>
        <p :style="footerTextStyle">© 2026 Gestion Stock par SAKONE · Conçu avec excellence</p>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/authStore'

const router = useRouter()
const authStore = useAuthStore()

const hoveredCard = ref(null)
const logoutHovered = ref(false)

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

const userAccessStats = computed(() => {
  const magasins = authStore.accessibleMagasins
  const entrepots = authStore.accessibleEntrepots
  
  return {
    magasins: magasins === 'all' ? 'Tous' : Array.isArray(magasins) ? magasins.length : 0,
    entrepots: entrepots === 'all' ? 'Tous' : Array.isArray(entrepots) ? entrepots.length : 0
  }
})

const hasAnyAccess = computed(() => {
  return authStore.hasAccessToMagasins || 
         authStore.hasAccessToEntrepots || 
         authStore.hasAccessToClients || 
         authStore.hasAccessToFournisseurs ||
         authStore.hasAccessToUsers
})

const navigateTo = (module) => {
  router.push(`/${module}`)
}

const handleLogout = () => {
  if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
    authStore.logout()
    router.push('/login')
  }
}

// Styles
const containerStyle = {
  minHeight: '100vh',
  background: 'linear-gradient(135deg, #fdfcfb 0%, #f7f4f0 25%, #faf8f6 50%, #f5f2ee 75%, #fdfcfb 100%)',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  padding: '40px 24px',
  fontFamily: '"SF Pro Display", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
  position: 'relative',
  overflow: 'hidden'
}

const gridPatternStyle = {
  position: 'fixed',
  top: 0,
  left: 0,
  width: '100%',
  height: '100%',
  backgroundImage: `
    linear-gradient(rgba(0, 0, 0, 0.015) 1px, transparent 1px),
    linear-gradient(90deg, rgba(0, 0, 0, 0.015) 1px, transparent 1px)
  `,
  backgroundSize: '60px 60px',
  zIndex: 0,
  pointerEvents: 'none'
}

const userHeaderStyle = {
  position: 'fixed',
  top: '24px',
  right: '24px',
  display: 'flex',
  alignItems: 'center',
  gap: '16px',
  zIndex: 10,
  background: 'rgba(255, 255, 255, 0.95)',
  backdropFilter: 'blur(12px)',
  padding: '12px 20px',
  borderRadius: '20px',
  boxShadow: '0 8px 32px rgba(0, 0, 0, 0.1)',
  border: '1px solid rgba(255, 255, 255, 0.8)'
}

const userInfoContainerStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '12px'
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

const userDetailsStyle = {
  display: 'flex',
  flexDirection: 'column',
  gap: '2px'
}

const userNameStyle = {
  fontSize: '14px',
  fontWeight: '700',
  color: '#0f172a',
  lineHeight: '1.2'
}

const userRoleStyle = {
  fontSize: '12px',
  color: '#64748b',
  fontWeight: '500'
}

const logoutButtonStyle = computed(() => ({
  display: 'flex',
  alignItems: 'center',
  gap: '8px',
  padding: '10px 16px',
  background: logoutHovered.value 
    ? 'linear-gradient(135deg, #dc2626 0%, #b91c1c 100%)'
    : 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)',
  color: 'white',
  border: 'none',
  borderRadius: '12px',
  cursor: 'pointer',
  fontSize: '13px',
  fontWeight: '600',
  transition: 'all 0.3s',
  boxShadow: logoutHovered.value 
    ? '0 8px 20px rgba(239, 68, 68, 0.4)'
    : '0 4px 12px rgba(239, 68, 68, 0.3)'
}))

const wrapperStyle = {
  width: '100%',
  maxWidth: '680px',
  position: 'relative',
  zIndex: 1
}

const headerStyle = {
  textAlign: 'center',
  marginBottom: '32px'
}

const badgeStyle = {
  display: 'inline-flex',
  alignItems: 'center',
  gap: '8px',
  background: 'linear-gradient(135deg, #f59e0b15 0%, #f59e0b08 100%)',
  border: '1px solid #f59e0b30',
  padding: '6px 16px',
  borderRadius: '50px',
  fontSize: '0.75rem',
  fontWeight: '600',
  color: '#d97706',
  marginBottom: '20px',
  letterSpacing: '0.05em',
  textTransform: 'uppercase'
}

const sparkleStyle = {
  width: '14px',
  height: '14px',
  color: '#f59e0b',
  animation: 'sparkle 2s ease-in-out infinite'
}

const titleStyle = {
  fontSize: '2.75rem',
  fontWeight: '700',
  background: 'linear-gradient(135deg, #1e293b 0%, #475569 100%)',
  WebkitBackgroundClip: 'text',
  WebkitTextFillColor: 'transparent',
  backgroundClip: 'text',
  margin: '0 0 12px 0',
  letterSpacing: '-0.03em',
  lineHeight: '1.1'
}

const subtitleStyle = {
  fontSize: '1rem',
  color: '#64748b',
  margin: '0',
  fontWeight: '400',
  letterSpacing: '0.01em'
}

const quickStatsStyle = {
  display: 'grid',
  gridTemplateColumns: '1fr 1fr',
  gap: '12px',
  marginBottom: '32px',
  maxWidth: '400px',
  margin: '0 auto 32px'
}

const quickStatItemStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '12px',
  background: 'white',
  padding: '16px',
  borderRadius: '16px',
  boxShadow: '0 2px 8px rgba(0, 0, 0, 0.06)',
  border: '1px solid #f1f5f9'
}

const quickStatIconStyle = (color) => ({
  width: '40px',
  height: '40px',
  borderRadius: '12px',
  background: `${color}15`,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  color: color,
  flexShrink: 0
})

const quickStatValueStyle = {
  fontSize: '20px',
  fontWeight: '700',
  color: '#0f172a',
  lineHeight: '1.2'
}

const quickStatLabelStyle = {
  fontSize: '12px',
  color: '#64748b',
  fontWeight: '500'
}

const gridStyle = {
  display: 'grid',
  gridTemplateColumns: 'repeat(2, 1fr)',
  gap: '16px',
  maxWidth: '600px',
  margin: '0 auto'
}

const cardContentStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '16px',
  marginBottom: '12px'
}

const cardTextStyle = {
  flex: 1,
  textAlign: 'left'
}

const cardTitleStyle = {
  fontSize: '1.125rem',
  fontWeight: '700',
  color: '#1e293b',
  margin: '0 0 2px 0',
  letterSpacing: '-0.01em'
}

const cardDescStyle = {
  fontSize: '0.8125rem',
  color: '#64748b',
  margin: '0',
  lineHeight: '1.4',
  fontWeight: '500'
}

const statsTextStyle = {
  fontSize: '0.75rem',
  fontWeight: '600',
  color: '#64748b'
}

const adminBadgeInCardStyle = {
  fontSize: '0.75rem',
  fontWeight: '700',
  color: '#d97706',
  background: '#fef3c7',
  padding: '4px 10px',
  borderRadius: '8px',
  letterSpacing: '0.05em',
  textTransform: 'uppercase'
}

const iconStyle = {
  width: '24px',
  height: '24px',
  strokeWidth: '2.5'
}

const noAccessStyle = {
  textAlign: 'center',
  padding: '60px 20px',
  background: 'white',
  borderRadius: '24px',
  boxShadow: '0 4px 20px rgba(0, 0, 0, 0.06)',
  border: '1px solid #f1f5f9'
}

const noAccessTitleStyle = {
  fontSize: '20px',
  fontWeight: '700',
  color: '#0f172a',
  margin: '16px 0 8px 0'
}

const noAccessTextStyle = {
  fontSize: '14px',
  color: '#64748b',
  margin: '0',
  lineHeight: '1.6'
}

const footerStyle = {
  textAlign: 'center',
  marginTop: '56px'
}

const footerLineStyle = {
  width: '60px',
  height: '2px',
  background: 'linear-gradient(90deg, transparent, #cbd5e1, transparent)',
  margin: '0 auto 20px',
  borderRadius: '1px'
}

const footerTextStyle = {
  color: '#94a3b8',
  fontSize: '0.8125rem',
  margin: '0',
  fontWeight: '500',
  letterSpacing: '0.01em'
}

// Fonctions pour styles dynamiques
const getCardStyle = (cardName) => {
  const isHovered = hoveredCard.value === cardName
  return {
    background: '#ffffff',
    borderRadius: '20px',
    padding: '24px',
    boxShadow: isHovered 
      ? '0 20px 60px rgba(0, 0, 0, 0.12), 0 0 0 1px rgba(0, 0, 0, 0.06)'
      : '0 4px 20px rgba(0, 0, 0, 0.06), 0 0 0 1px rgba(0, 0, 0, 0.04)',
    border: 'none',
    cursor: 'pointer',
    textAlign: 'left',
    transition: 'all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1)',
    transform: isHovered ? 'translateY(-8px) scale(1.02)' : 'translateY(0) scale(1)',
    position: 'relative'
  }
}

const cardShineStyle = (cardName) => {
  const isHovered = hoveredCard.value === cardName
  return {
    position: 'absolute',
    top: '-50%',
    left: '-50%',
    width: '200%',
    height: '200%',
    background: 'linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.6) 50%, transparent 70%)',
    transform: 'translateX(-100%) translateY(-100%) rotate(45deg)',
    animation: isHovered ? 'shimmer 1.5s ease-in-out' : 'none',
    pointerEvents: 'none'
  }
}

const getIconContainerStyle = (cardName, accentColor) => {
  const isHovered = hoveredCard.value === cardName
  return {
    width: '48px',
    height: '48px',
    background: isHovered 
      ? accentColor
      : `${accentColor}12`,
    borderRadius: '14px',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    transition: 'all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1)',
    flexShrink: 0,
    color: isHovered ? '#ffffff' : accentColor,
    transform: isHovered ? 'rotate(-8deg) scale(1.1)' : 'rotate(0deg) scale(1)',
    boxShadow: isHovered ? `0 8px 24px ${accentColor}40` : 'none'
  }
}

const getCardFooterStyle = (cardName) => {
  const isHovered = hoveredCard.value === cardName
  return {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingTop: '12px',
    borderTop: '1px solid #f1f5f9',
    opacity: isHovered ? 1 : 0.6,
    transition: 'opacity 0.3s ease'
  }
}

const getArrowStyle = (cardName) => {
  const isHovered = hoveredCard.value === cardName
  const colors = {
    'magasins': '#10b981',
    'entrepot': '#3b82f6',
    'clients': '#f59e0b',
    'fournisseurs': '#8b5cf6',
    'utilisateurs': '#ef4444'
  }
  
  return {
    width: '18px',
    height: '18px',
    color: colors[cardName],
    transition: 'transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)',
    transform: isHovered ? 'translateX(4px)' : 'translateX(0)',
    strokeWidth: '2.5'
  }
}
</script>

<style scoped>
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeInDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes float {
  0%, 100% {
    transform: translate(0, 0) scale(1);
  }
  33% {
    transform: translate(30px, -30px) scale(1.1);
  }
  66% {
    transform: translate(-20px, 20px) scale(0.9);
  }
}

@keyframes shimmer {
  0% {
    transform: translateX(-100%) translateY(-100%) rotate(45deg);
  }
  100% {
    transform: translateX(100%) translateY(100%) rotate(45deg);
  }
}

@keyframes sparkle {
  0%, 100% {
    opacity: 1;
    transform: scale(1) rotate(0deg);
  }
  50% {
    opacity: 0.7;
    transform: scale(1.2) rotate(180deg);
  }
}

.fade-in-up {
  animation: fadeInUp 1s cubic-bezier(0.16, 1, 0.3, 1) backwards;
}

.fade-in-down {
  animation: fadeInDown 0.8s cubic-bezier(0.16, 1, 0.3, 1) backwards;
}

.animated-bg {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
  z-index: 0;
  pointer-events: none;
}

.gradient-orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.4;
  animation: float 25s ease-in-out infinite;
}

.orb-1 {
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, rgba(16, 185, 129, 0.3), transparent);
  top: -200px;
  left: -200px;
  animation-delay: 0s;
}

.orb-2 {
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, rgba(59, 130, 246, 0.25), transparent);
  top: 20%;
  right: -250px;
  animation-delay: 8s;
}

.orb-3 {
  width: 450px;
  height: 450px;
  background: radial-gradient(circle, rgba(245, 158, 11, 0.25), transparent);
  bottom: -150px;
  left: 25%;
  animation-delay: 16s;
}

.orb-4 {
  width: 550px;
  height: 550px;
  background: radial-gradient(circle, rgba(139, 92, 246, 0.2), transparent);
  top: 40%;
  left: 50%;
  animation-delay: 12s;
}

.card {
  position: relative;
  overflow: hidden;
}

/* Responsive */
@media (max-width: 768px) {
  .user-header {
    top: 16px;
    right: 16px;
    left: 16px;
    flex-direction: column;
    align-items: stretch;
  }
}
</style>