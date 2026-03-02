<template>
  <div :style="containerStyle">
    <div :style="contentStyle" class="fade-in-up">
      <div :style="codeStyle">404</div>
      
      <h1 :style="titleStyle">Page Non Trouvée</h1>
      <p :style="messageStyle">
        Désolé, la page que vous recherchez n'existe pas ou a été déplacée.
      </p>
      
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

const codeStyle = {
  fontSize: '120px',
  fontWeight: '800',
  background: 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)',
  WebkitBackgroundClip: 'text',
  WebkitTextFillColor: 'transparent',
  backgroundClip: 'text',
  lineHeight: '1',
  marginBottom: '24px',
  letterSpacing: '-0.05em'
}

const titleStyle = {
  fontSize: '32px',
  fontWeight: '700',
  color: '#0f172a',
  margin: '0 0 16px 0',
  letterSpacing: '-0.02em'
}

const messageStyle = {
  fontSize: '18px',
  color: '#64748b',
  margin: '0 0 40px 0',
  lineHeight: '1.6'
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