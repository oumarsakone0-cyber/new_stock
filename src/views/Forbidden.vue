<template>
  <div :style="containerStyle">
    <div :style="contentStyle" class="fade-in-up">
      <div :style="iconContainerStyle">
        <svg :style="iconStyle" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" y1="8" x2="12" y2="12"/>
          <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
      </div>
      
      <h1 :style="titleStyle">Accès Refusé</h1>
      <p :style="messageStyle">
        Vous n'avez pas les permissions nécessaires pour accéder à cette page.
      </p>
      
      <div :style="detailsStyle">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
          <path d="M7 11V7a5 5 0 0110 0v4"/>
        </svg>
        <span>Contactez votre administrateur si vous pensez qu'il s'agit d'une erreur</span>
      </div>
      
      <div :style="actionsStyle">
        <button :style="primaryButtonStyle" @click="goHome">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
          </svg>
          Retour à l'accueil
        </button>
        
        <button :style="secondaryButtonStyle" @click="goBack">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
          </svg>
          Retour
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

const goHome = () => {
  router.push('/')
}

const goBack = () => {
  router.go(-1)
}

const containerStyle = {
  minHeight: '100vh',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  background: 'linear-gradient(135deg, #fdfcfb 0%, #f7f4f0 100%)',
  padding: '24px'
}

const contentStyle = {
  textAlign: 'center',
  maxWidth: '500px'
}

const iconContainerStyle = {
  width: '120px',
  height: '120px',
  margin: '0 auto 32px',
  background: 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)',
  borderRadius: '30px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  boxShadow: '0 20px 40px rgba(239, 68, 68, 0.3)'
}

const iconStyle = {
  width: '60px',
  height: '60px',
  color: 'white',
  strokeWidth: '2'
}

const titleStyle = {
  fontSize: '48px',
  fontWeight: '700',
  color: '#0f172a',
  margin: '0 0 16px 0',
  letterSpacing: '-0.02em'
}

const messageStyle = {
  fontSize: '18px',
  color: '#64748b',
  margin: '0 0 32px 0',
  lineHeight: '1.6'
}

const detailsStyle = {
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  gap: '10px',
  padding: '16px 24px',
  background: '#fef2f2',
  border: '1px solid #fecaca',
  borderRadius: '12px',
  color: '#991b1b',
  fontSize: '14px',
  fontWeight: '500',
  marginBottom: '32px'
}

const actionsStyle = {
  display: 'flex',
  gap: '12px',
  justifyContent: 'center',
  flexWrap: 'wrap'
}

const primaryButtonStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '8px',
  padding: '14px 28px',
  background: 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
  color: 'white',
  border: 'none',
  borderRadius: '12px',
  fontSize: '15px',
  fontWeight: '600',
  cursor: 'pointer',
  transition: 'all 0.2s',
  boxShadow: '0 4px 12px rgba(16, 185, 129, 0.25)'
}

const secondaryButtonStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '8px',
  padding: '14px 28px',
  background: 'white',
  color: '#64748b',
  border: '2px solid #e2e8f0',
  borderRadius: '12px',
  fontSize: '15px',
  fontWeight: '600',
  cursor: 'pointer',
  transition: 'all 0.2s'
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

.fade-in-up {
  animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) backwards;
}
</style>