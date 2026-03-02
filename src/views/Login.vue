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

    <!-- Login Card -->
    <div :style="loginCardStyle" class="fade-in-up">
      <!-- Premium Badge -->
      <div :style="badgeStyle">
        <svg :style="sparkleStyle" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 2l2.4 7.4h7.6l-6 4.6 2.3 7.4-6.3-4.6-6.3 4.6 2.3-7.4-6-4.6h7.6z"/>
        </svg>
        <span>Premium</span>
      </div>

      <!-- Logo & Title -->
      <div :style="headerStyle">
        <div :style="logoContainerStyle">
          <svg :style="logoStyle" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
          </svg>
        </div>
        <h1 :style="titleStyle">Gestion Commerciale</h1>
        <p :style="subtitleStyle">Connectez-vous à votre espace</p>
      </div>

      <!-- Alert Messages -->
      <Transition name="alert">
        <div v-if="errorMessage" :style="errorAlertStyle">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          {{ errorMessage }}
        </div>
      </Transition>

      <Transition name="alert">
        <div v-if="successMessage" :style="successAlertStyle">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
            <polyline points="22 4 12 14.01 9 11.01"/>
          </svg>
          {{ successMessage }}
        </div>
      </Transition>

      <!-- Login Form -->
      <form @submit.prevent="handleLogin" :style="formStyle">
        <!-- Email Input -->
        <div :style="formGroupStyle">
          <label :style="labelStyle">
            <svg :style="labelIconStyle" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            Adresse email
          </label>
          <input 
            v-model="formData.email"
            type="email"
            :style="getInputStyle('email')"
            @focus="focusedInput = 'email'"
            @blur="focusedInput = null"
            placeholder="exemple@sogetrag.com"
            required
            autocomplete="email"
          />
        </div>

        <!-- Password Input -->
        <div :style="formGroupStyle">
          <label :style="labelStyle">
            <svg :style="labelIconStyle" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            Mot de passe
          </label>
          <div :style="passwordWrapperStyle">
            <input 
              v-model="formData.password"
              :type="showPassword ? 'text' : 'password'"
              :style="getPasswordInputStyle"
              @focus="focusedInput = 'password'"
              @blur="focusedInput = null"
              placeholder="••••••••"
              required
              autocomplete="current-password"
            />
            <button 
              type="button" 
              :style="togglePasswordStyle"
              @click="showPassword = !showPassword"
              tabindex="-1"
            >
              <svg v-if="!showPassword" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
              <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Remember Me & Forgot Password -->
        <div :style="optionsRowStyle">
          <label :style="checkboxLabelStyle">
            <input 
              type="checkbox" 
              v-model="formData.rememberMe"
              :style="checkboxStyle"
            />
            <span>Se souvenir de moi</span>
          </label>
          <a href="#" :style="forgotLinkStyle" @click.prevent="handleForgotPassword">
            Mot de passe oublié ?
          </a>
        </div>

        <!-- Submit Button -->
        <button 
          type="submit" 
          :style="submitButtonStyle"
          :disabled="loading"
          @mouseenter="submitHovered = true"
          @mouseleave="submitHovered = false"
        >
          <span v-if="!loading">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4m-5-4l5-5-5-5m5 5H3"/>
            </svg>
            Se connecter
          </span>
          <span v-else :style="loadingSpinnerStyle">
            <div class="spinner"></div>
            Connexion en cours...
          </span>
        </button>
      </form>
 

      <!-- Footer -->
      <div :style="footerStyle">
        <p :style="footerTextStyle">
          © 2026 Gestion Commerciale · Sécurisé et fiable
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/authStore'
import { authLogin } from '../services/api'

const router = useRouter()
const authStore = useAuthStore()

const loading = ref(false)
const submitHovered = ref(false)
const registerHovered = ref(false)
const focusedInput = ref(null)
const showPassword = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const formData = reactive({
  email: '',
  password: '',
  rememberMe: false
})

const handleLogin = async () => {
  // Reset messages
  errorMessage.value = ''
  successMessage.value = ''
  loading.value = true

  try {
    const response = await authLogin(formData.email, formData.password)
    const data = response.data

    if (data.success) {
      // Utiliser le store Pinia pour gérer l'authentification
      await authStore.login(data.data)
      
      if (formData.rememberMe) {
        localStorage.setItem('rememberEmail', formData.email)
      } else {
        localStorage.removeItem('rememberEmail')
      }

      successMessage.value = 'Connexion réussie ! Redirection...'
      
      // Rediriger vers la page demandée ou dashboard
      setTimeout(() => {
        const redirect = router.currentRoute.value.query.redirect
        router.push(redirect || '/')
      }, 1500)
    } else {
      errorMessage.value = data.error || 'Erreur lors de la connexion'
    }
  } catch (error) {
    console.error('Erreur de connexion:', error)
    errorMessage.value = error.response?.data?.error || error.message || 'Impossible de se connecter au serveur'
  } finally {
    loading.value = false
  }
}

const handleRegister = () => {
  router.push('/register')
}

const handleForgotPassword = () => {
  alert('Fonctionnalité "Mot de passe oublié" en cours de développement')
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

const loginCardStyle = {
  width: '100%',
  maxWidth: '460px',
  background: 'white',
  borderRadius: '28px',
  padding: '48px 40px',
  boxShadow: '0 20px 60px rgba(0, 0, 0, 0.12), 0 0 0 1px rgba(0, 0, 0, 0.04)',
  position: 'relative',
  zIndex: 1
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
  marginBottom: '24px',
  letterSpacing: '0.05em',
  textTransform: 'uppercase'
}

const sparkleStyle = {
  width: '14px',
  height: '14px',
  color: '#f59e0b',
  animation: 'sparkle 2s ease-in-out infinite'
}

const headerStyle = {
  textAlign: 'center',
  marginBottom: '32px'
}

const logoContainerStyle = {
  width: '72px',
  height: '72px',
  margin: '0 auto 20px',
  background: 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
  borderRadius: '20px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  boxShadow: '0 12px 28px rgba(16, 185, 129, 0.3)'
}

const logoStyle = {
  width: '36px',
  height: '36px',
  color: 'white',
  strokeWidth: '2.5'
}

const titleStyle = {
  fontSize: '28px',
  fontWeight: '700',
  color: '#0f172a',
  margin: '0 0 8px 0',
  letterSpacing: '-0.02em'
}

const subtitleStyle = {
  fontSize: '15px',
  color: '#64748b',
  margin: '0',
  fontWeight: '500'
}

const errorAlertStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '12px',
  padding: '14px 16px',
  background: '#fef2f2',
  border: '1px solid #fecaca',
  borderRadius: '12px',
  color: '#991b1b',
  fontSize: '14px',
  fontWeight: '500',
  marginBottom: '20px'
}

const successAlertStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '12px',
  padding: '14px 16px',
  background: '#dcfce7',
  border: '1px solid #86efac',
  borderRadius: '12px',
  color: '#166534',
  fontSize: '14px',
  fontWeight: '500',
  marginBottom: '20px'
}

const formStyle = {
  display: 'flex',
  flexDirection: 'column',
  gap: '20px'
}

const formGroupStyle = {
  display: 'flex',
  flexDirection: 'column',
  gap: '8px'
}

const labelStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '8px',
  fontSize: '14px',
  fontWeight: '600',
  color: '#334155',
  letterSpacing: '0.01em'
}

const labelIconStyle = {
  width: '16px',
  height: '16px',
  strokeWidth: '2'
}

const getInputStyle = (inputName) => ({
  width: '100%',
  padding: '14px 16px',
  border: focusedInput.value === inputName ? '2px solid #10b981' : '2px solid #e2e8f0',
  borderRadius: '12px',
  fontSize: '15px',
  fontWeight: '500',
  color: '#1e293b',
  transition: 'all 0.2s',
  outline: 'none',
  boxSizing: 'border-box',
  background: focusedInput.value === inputName ? '#f8fafc' : 'white'
})

const passwordWrapperStyle = {
  position: 'relative',
  display: 'flex',
  alignItems: 'center'
}

const getPasswordInputStyle = computed(() => ({
  width: '100%',
  padding: '14px 48px 14px 16px',
  border: focusedInput.value === 'password' ? '2px solid #10b981' : '2px solid #e2e8f0',
  borderRadius: '12px',
  fontSize: '15px',
  fontWeight: '500',
  color: '#1e293b',
  transition: 'all 0.2s',
  outline: 'none',
  boxSizing: 'border-box',
  background: focusedInput.value === 'password' ? '#f8fafc' : 'white'
}))

const togglePasswordStyle = {
  position: 'absolute',
  right: '12px',
  background: 'none',
  border: 'none',
  cursor: 'pointer',
  color: '#94a3b8',
  padding: '8px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  borderRadius: '8px',
  transition: 'all 0.2s',
  '&:hover': {
    background: '#f1f5f9',
    color: '#64748b'
  }
}

const optionsRowStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  marginTop: '-8px'
}

const checkboxLabelStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '8px',
  fontSize: '14px',
  color: '#64748b',
  fontWeight: '500',
  cursor: 'pointer'
}

const checkboxStyle = {
  width: '18px',
  height: '18px',
  cursor: 'pointer',
  accentColor: '#10b981'
}

const forgotLinkStyle = {
  fontSize: '14px',
  color: '#10b981',
  fontWeight: '600',
  textDecoration: 'none',
  transition: 'color 0.2s',
  '&:hover': {
    color: '#059669'
  }
}

const submitButtonStyle = computed(() => ({
  width: '100%',
  padding: '16px',
  background: submitHovered.value 
    ? 'linear-gradient(135deg, #059669 0%, #047857 100%)'
    : 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
  color: 'white',
  border: 'none',
  borderRadius: '12px',
  fontSize: '15px',
  fontWeight: '600',
  cursor: loading.value ? 'not-allowed' : 'pointer',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  gap: '10px',
  transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
  boxShadow: submitHovered.value && !loading.value
    ? '0 12px 28px rgba(16, 185, 129, 0.4)'
    : '0 4px 12px rgba(16, 185, 129, 0.25)',
  transform: submitHovered.value && !loading.value ? 'translateY(-2px)' : 'translateY(0)',
  letterSpacing: '0.01em',
  opacity: loading.value ? 0.7 : 1
}))

const loadingSpinnerStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '10px'
}

const dividerStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '16px',
  margin: '28px 0',
  fontSize: '13px',
  color: '#94a3b8',
  fontWeight: '500',
  '&::before': {
    content: '""',
    flex: 1,
    height: '1px',
    background: '#e2e8f0'
  },
  '&::after': {
    content: '""',
    flex: 1,
    height: '1px',
    background: '#e2e8f0'
  }
}

const registerSectionStyle = {
  textAlign: 'center'
}

const registerTextStyle = {
  fontSize: '14px',
  color: '#64748b',
  marginBottom: '12px',
  fontWeight: '500'
}

const registerButtonStyle = computed(() => ({
  width: '100%',
  padding: '14px',
  background: registerHovered.value ? '#f1f5f9' : 'white',
  color: '#334155',
  border: '2px solid #e2e8f0',
  borderRadius: '12px',
  fontSize: '15px',
  fontWeight: '600',
  cursor: 'pointer',
  transition: 'all 0.2s',
  letterSpacing: '0.01em'
}))

const footerStyle = {
  marginTop: '32px',
  paddingTop: '24px',
  borderTop: '1px solid #f1f5f9',
  textAlign: 'center'
}

const footerTextStyle = {
  fontSize: '13px',
  color: '#94a3b8',
  margin: '0',
  fontWeight: '500'
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

* {
  font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif;
}

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
  animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) backwards;
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

.spinner {
  width: 18px;
  height: 18px;
  border: 3px solid rgba(255, 255, 255, 0.3);
  border-top: 3px solid white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.alert-enter-active, .alert-leave-active {
  transition: all 0.3s ease;
}

.alert-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}

.alert-leave-to {
  opacity: 0;
  transform: translateY(10px);
}
</style>