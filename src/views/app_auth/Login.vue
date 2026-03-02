<template>
  <div :style="containerStyle">
    <!-- Animated Background -->
    <div class="animated-bg">
      <div class="gradient-orb orb-1"></div>
      <div class="gradient-orb orb-2"></div>
      <div class="gradient-orb orb-3"></div>
      <div class="grid-overlay"></div>
    </div>

    <!-- Main Container -->
    <div :style="mainContainerStyle">

      <!-- Left Side - Features -->
      <div :style="leftSideStyle" class="fade-in">
        <div :style="logoHeaderStyle">
          <div :style="logoContainerStyle">
            <svg :style="logoStyle" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
          </div>
          <h1 :style="brandTitleStyle">Next Stock</h1>
        </div>

        <div :style="featuresContainerStyle">
          <div :style="featureItemStyle" v-for="(feature, index) in features" :key="index"
            class="feature-card">
            <div :style="featureIconStyle">
              <component :is="'div'" v-html="feature.icon"></component>
            </div>
            <div>
              <h3 :style="featureTitleStyle">{{ feature.title }}</h3>
              <p :style="featureDescStyle">{{ feature.description }}</p>
            </div>
          </div>
        </div>

        <div :style="footerLinksStyle">
          <a href="#" :style="footerLinkStyle">Conditions</a>
          <span :style="separatorStyle">·</span>
          <a href="#" :style="footerLinkStyle">Confidentialité</a>
          <span :style="separatorStyle">·</span>
          <a href="#" :style="footerLinkStyle">Documentation</a>
        </div>
      </div>

      <!-- Right Side - Login Form / Activation Code -->
      <div :style="rightSideStyle" class="fade-in-delay">
        <div :style="formCardStyle">

          <!-- ===================================================
               VUE 1 — Formulaire de connexion
               =================================================== -->
          <template v-if="currentView === 'login'">
            <div :style="formHeaderStyle">
              <h2 :style="formTitleStyle">Connexion</h2>
              <p :style="formSubtitleStyle">Accédez à votre espace Next Stock</p>
            </div>

            <!-- Alert Messages -->
            <Transition name="alert">
              <div v-if="errorMessage" :style="getAlertStyle('error')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"/>
                  <line x1="12" y1="8" x2="12" y2="12"/>
                  <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span>{{ errorMessage }}</span>
              </div>
            </Transition>

            <Transition name="alert">
              <div v-if="successMessage" :style="getAlertStyle('success')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                  <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                <span>{{ successMessage }}</span>
              </div>
            </Transition>

            <!-- Social Login -->
            <div :style="socialButtonsStyle">
              <button :style="socialButtonStyle" @click="handleSocialLogin('google')" class="social-btn">
                <svg width="18" height="18" viewBox="0 0 24 24">
                  <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                  <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                  <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                  <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Google
              </button>
              <button :style="socialButtonStyle" @click="handleSocialLogin('github')" class="social-btn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                </svg>
                Github
              </button>
              <button :style="socialButtonStyle" @click="handleSocialLogin('gitlab')" class="social-btn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                  <path fill="#FC6D26" d="M22.65 14.39L12 22.13 1.35 14.39a.84.84 0 01-.3-.94l1.22-3.78 2.44-7.51A.42.42 0 014.82 2a.43.43 0 01.58 0 .42.42 0 01.11.18l2.44 7.49h8.1l2.44-7.51A.42.42 0 0118.6 2a.43.43 0 01.58 0 .42.42 0 01.11.18l2.44 7.51L23 13.45a.84.84 0 01-.35.94z"/>
                </svg>
                Gitlab
              </button>
            </div>

            <div :style="dividerStyle">
              <span>Ou</span>
            </div>

            <!-- Login Form -->
            <form @submit.prevent="handleLogin" :style="formStyle">
              <div :style="formGroupStyle">
                <label :style="labelStyle">Email</label>
                <div :style="inputWrapperStyle">
                  <svg :style="inputIconStyle" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                  <input
                    v-model="formData.email"
                    type="email"
                    :style="getInputStyle('email')"
                    @focus="focusedInput = 'email'"
                    @blur="focusedInput = null"
                    placeholder="alaskayoung@gmail.com"
                    required
                    autocomplete="email"
                  />
                </div>
              </div>

              <div :style="formGroupStyle">
                <label :style="labelStyle">Mot de passe</label>
                <div :style="inputWrapperStyle">
                  <svg :style="inputIconStyle" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                  </svg>
                  <input
                    v-model="formData.password"
                    :type="showPassword ? 'text' : 'password'"
                    :style="getInputStyle('password', true)"
                    @focus="focusedInput = 'password'"
                    @blur="focusedInput = null"
                    placeholder="••••••••"
                    required
                    autocomplete="current-password"
                  />
                  <button type="button" :style="togglePasswordStyle" @click="showPassword = !showPassword" tabindex="-1">
                    <svg v-if="!showPassword" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                      <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/>
                      <line x1="1" y1="1" x2="23" y2="23"/>
                    </svg>
                  </button>
                </div>
              </div>

              <button type="submit" :style="submitButtonStyle" :disabled="loading"
                @mouseenter="submitHovered = true" @mouseleave="submitHovered = false">
                <span v-if="!loading">Se connecter</span>
                <span v-else style="display:flex;align-items:center;gap:10px">
                  <div class="spinner"></div> Connexion...
                </span>
              </button>
            </form>

            <div :style="formFooterStyle">
              <p :style="footerTextStyle">
                Pas encore de compte ?
                <a href="#" :style="linkStyle" @click.prevent="goToRegister">S'inscrire</a>
              </p>
            </div>
          </template>

          <!-- ===================================================
               VUE 2 — Saisie du code d'activation
               =================================================== -->
          <template v-else-if="currentView === 'activation'">
            <div :style="formHeaderStyle">
              <!-- Icône envelope -->
              <div :style="activationIconStyle">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                </svg>
              </div>
              <h2 :style="formTitleStyle">Vérifiez votre email</h2>
              <p :style="formSubtitleStyle">
                Un code d'activation à <strong>4 caractères</strong> a été envoyé à<br>
                <span :style="emailHighlightStyle">{{ pendingEmail }}</span>
              </p>
            </div>

            <!-- Alerte activation -->
            <Transition name="alert">
              <div v-if="activationError" :style="getAlertStyle('error')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"/>
                  <line x1="12" y1="8" x2="12" y2="12"/>
                  <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span>{{ activationError }}</span>
              </div>
            </Transition>

            <Transition name="alert">
              <div v-if="activationSuccess" :style="getAlertStyle('success')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                  <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                <span>{{ activationSuccess }}</span>
              </div>
            </Transition>

            <!-- 4 champs OTP -->
            <div :style="otpContainerStyle">
              <input
                v-for="(_, i) in 4"
                :key="i"
                :ref="el => { if (el) otpRefs[i] = el }"
                v-model="otpCode[i]"
                type="text"
                maxlength="1"
                :style="getOtpInputStyle(i)"
                @input="handleOtpInput(i, $event)"
                @keydown="handleOtpKeydown(i, $event)"
                @focus="focusedOtp = i"
                @blur="focusedOtp = null"
                autocomplete="off"
                spellcheck="false"
              />
            </div>

            <button :style="submitButtonStyle" :disabled="activationLoading || otpFull === false"
              @click="handleActivation"
              @mouseenter="submitHovered = true"
              @mouseleave="submitHovered = false">
              <span v-if="!activationLoading">Valider le code</span>
              <span v-else style="display:flex;align-items:center;gap:10px">
                <div class="spinner"></div> Vérification...
              </span>
            </button>

            <div :style="formFooterStyle">
              <p :style="footerTextStyle">
                Code non reçu ?
                <a href="#" :style="linkStyle" @click.prevent="resendCode">Renvoyer</a>
                &nbsp;·&nbsp;
                <a href="#" :style="{ ...linkStyle, color: '#888' }" @click.prevent="currentView = 'login'">Retour</a>
              </p>
            </div>
          </template>

          <!-- ===================================================
               VUE 3 — Définir le mot de passe  ← NOUVEAU
               =================================================== -->
          <template v-else-if="currentView === 'set-password'">

            <!-- Stepper : étape 1 ✓ / étape 2 active -->
            <div :style="stepperStyle">
              <div :style="stepItemStyle">
                <div :style="stepDoneCircleStyle">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                </div>
                <span :style="stepDoneLabelStyle">Code</span>
              </div>
              <div :style="stepLineDoneStyle"></div>
              <div :style="stepItemStyle">
                <div :style="stepActiveCircleStyle">2</div>
                <span :style="stepActiveLabelStyle">Mot de passe</span>
              </div>
            </div>

            <div :style="formHeaderStyle">
              <div :style="activationIconStyle">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                </svg>
              </div>
              <h2 :style="formTitleStyle">Créez votre mot de passe</h2>
              <p :style="formSubtitleStyle">Choisissez un mot de passe sécurisé pour activer votre compte</p>
            </div>

            <Transition name="alert">
              <div v-if="passwordSetError" :style="getAlertStyle('error')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"/>
                  <line x1="12" y1="8" x2="12" y2="12"/>
                  <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span>{{ passwordSetError }}</span>
              </div>
            </Transition>

            <div :style="{ display:'flex', flexDirection:'column', gap:'18px' }">

              <!-- Nouveau mot de passe -->
              <div :style="formGroupStyle">
                <label :style="labelStyle">Nouveau mot de passe</label>
                <div :style="inputWrapperStyle">
                  <svg :style="inputIconStyle" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                  </svg>
                  <input
                    v-model="newPassword"
                    :type="showNewPassword ? 'text' : 'password'"
                    :style="getInputStyle('newPassword', true)"
                    @focus="focusedInput = 'newPassword'"
                    @blur="focusedInput = null"
                    @input="checkPasswordStrength"
                    placeholder="Votre nouveau mot de passe"
                    autocomplete="new-password"
                  />
                  <button type="button" :style="togglePasswordStyle" @click="showNewPassword = !showNewPassword" tabindex="-1">
                    <svg v-if="!showNewPassword" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                      <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/>
                      <line x1="1" y1="1" x2="23" y2="23"/>
                    </svg>
                  </button>
                </div>

                <!-- Indicateur de force (même logique que Register.vue) -->
                <div v-if="newPassword" style="margin-top:10px">
                  <!-- 4 segments colorés -->
                  <div style="display:flex;gap:4px;margin-bottom:6px">
                    <div v-for="seg in 4" :key="seg" :style="getStrengthSegmentStyle(seg)"></div>
                  </div>
                  <!-- Label force + compteur critères -->
                  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
                    <span :style="{ fontSize:'12px', fontWeight:'600', color: passwordStrengthColor }">
                      {{ passwordStrengthLabel }}
                    </span>
                    <span style="font-size:11px;color:#90a8a0">{{ passwordScore }}/4 critères</span>
                  </div>
                  <!-- Critères en grille 2 colonnes -->
                  <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px">
                    <div v-for="req in passwordRequirements" :key="req.key" :style="getCriterionStyle(req.met)">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline v-if="req.met" points="20 6 9 17 4 12"/>
                        <circle v-else cx="12" cy="12" r="8"/>
                      </svg>
                      {{ req.label }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Confirmer mot de passe -->
              <div :style="formGroupStyle">
                <label :style="labelStyle">Confirmer le mot de passe</label>
                <div :style="inputWrapperStyle">
                  <svg :style="inputIconStyle" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                  </svg>
                  <input
                    v-model="newPasswordConfirm"
                    :type="showNewPasswordConfirm ? 'text' : 'password'"
                    :style="getConfirmPasswordStyle()"
                    @focus="focusedInput = 'newPasswordConfirm'"
                    @blur="focusedInput = null"
                    placeholder="Confirmez le mot de passe"
                    autocomplete="new-password"
                  />
                  <button type="button" :style="togglePasswordStyle" @click="showNewPasswordConfirm = !showNewPasswordConfirm" tabindex="-1">
                    <svg v-if="!showNewPasswordConfirm" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                      <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/>
                      <line x1="1" y1="1" x2="23" y2="23"/>
                    </svg>
                  </button>
                </div>
                <!-- Feedback correspondance en temps réel -->
                <div v-if="newPasswordConfirm && newPassword !== newPasswordConfirm"
                  style="font-size:12px;color:#dc2626;margin-top:4px;font-weight:500">
                  Les mots de passe ne correspondent pas
                </div>
                <div v-if="newPasswordConfirm && newPassword === newPasswordConfirm && newPassword"
                  style="font-size:12px;color:#059669;margin-top:4px;font-weight:500;display:flex;align-items:center;gap:4px">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  Les mots de passe correspondent
                </div>
              </div>

              <button :style="submitButtonStyle"
                :disabled="passwordSetLoading || !isPasswordValid"
                @click="handleSetPassword"
                @mouseenter="submitHovered = true"
                @mouseleave="submitHovered = false">
                <span v-if="!passwordSetLoading">Activer mon compte</span>
                <span v-else style="display:flex;align-items:center;gap:10px">
                  <div class="spinner"></div> Activation...
                </span>
              </button>

            </div>
          </template>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/authStore'
import { authLogin, authActivate, authSetPassword  } from '../../services/api.js' // ← authSetPassword ajouté

const router    = useRouter()
const authStore = useAuthStore()

// ─── State ───────────────────────────────────────────────
const currentView      = ref('login')   // 'login' | 'activation' | 'set-password'
const loading          = ref(false)
const submitHovered    = ref(false)
const focusedInput     = ref(null)
const showPassword     = ref(false)
const errorMessage     = ref('')
const successMessage   = ref('')

// Activation
const pendingEmail      = ref('')
const otpCode           = reactive(['', '', '', ''])
const otpRefs           = ref([])
const focusedOtp        = ref(null)
const activationError   = ref('')
const activationSuccess = ref('')
const activationLoading = ref(false)

// ── NOUVEAU — État pour la vue set-password ───────────────
const newPassword            = ref('')
const newPasswordConfirm     = ref('')
const showNewPassword        = ref(false)
const showNewPasswordConfirm = ref(false)
const passwordSetError       = ref('')
const passwordSetLoading     = ref(false)

// Critères de force (même logique que Register.vue)
const passwordRequirements = reactive([
  { key: 'uppercase', label: 'Majuscule',         met: false },
  { key: 'lowercase', label: 'Minuscule',         met: false },
  { key: 'number',    label: 'Chiffre',            met: false },
  { key: 'special',   label: 'Caractère spécial',  met: false },
])

const passwordScore = computed(() => passwordRequirements.filter(r => r.met).length)

const passwordStrengthLabel = computed(() => {
  const s = passwordScore.value
  if (s === 0) return 'Très faible'
  if (s === 1) return 'Faible'
  if (s === 2) return 'Moyen'
  if (s === 3) return 'Fort'
  return 'Très fort'
})

const passwordStrengthColor = computed(() => {
  const s = passwordScore.value
  if (s <= 1) return '#ef4444'
  if (s === 2) return '#f59e0b'
  if (s === 3) return '#84cc16'
  return '#10b981'
})

const checkPasswordStrength = () => {
  const p = newPassword.value
  passwordRequirements[0].met = /[A-Z]/.test(p)
  passwordRequirements[1].met = /[a-z]/.test(p)
  passwordRequirements[2].met = /[0-9]/.test(p)
  passwordRequirements[3].met = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(p)
}

// Valide si ≥ Moyen (score ≥ 2) + longueur ≥ 8 + mots de passe identiques
const isPasswordValid = computed(() =>
  newPassword.value.length >= 8 &&
  newPasswordConfirm.value === newPassword.value &&
  passwordScore.value >= 2
)
// ─────────────────────────────────────────────────────────

const formData = reactive({ email: '', password: '' })

const otpFull = computed(() => otpCode.every(c => c.trim().length === 1))

// ─── Features ────────────────────────────────────────────
const features = [
  {
    icon: '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>',
    title: 'Suivi des stocks',
    description: 'Surveillez vos entrées et sorties en temps réel pour éviter les ruptures.'
  },
  {
    icon: '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>',
    title: 'Alertes automatiques',
    description: 'Recevez des notifications pour les produits faibles en stock ou périmés.'
  },
  {
    icon: '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
    title: 'Rapports détaillés',
    description: 'Analysez vos ventes, achats et tendances pour une gestion efficace.'
  },
  {
    icon: '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
    title: 'Sécurité et permissions',
    description: "Contrôlez qui peut accéder, modifier ou valider les stocks dans l'application."
  }
]

// ─── Handlers ────────────────────────────────────────────
const handleLogin = async () => {
  errorMessage.value  = ''
  successMessage.value = ''
  loading.value       = true

  try {
    const response = await authLogin(formData.email, formData.password)
    const data = response.data

    if (data.success) {
      await authStore.login(data.data)
      successMessage.value = 'Connexion réussie ! Redirection...'
      setTimeout(() => {
        const redirect = router.currentRoute.value.query.redirect
        router.push(redirect || '/')
      }, 1200)

    } else {
      // Gestion des statuts spéciaux renvoyés par l'API
      if (data.error_code === 'ACCOUNT_PENDING_VALIDATION') {
        pendingEmail.value = data.email || formData.email
        otpCode.splice(0, 4, '', '', '', '')
        activationError.value   = ''
        activationSuccess.value = ''
        currentView.value       = 'activation'
      } else {
        errorMessage.value = data.error || 'Erreur lors de la connexion'
      }
    }
  } catch (error) {
    const apiData = error.response?.data
    if (apiData?.error_code === 'ACCOUNT_PENDING_VALIDATION') {
      pendingEmail.value = apiData.email || formData.email
      otpCode.splice(0, 4, '', '', '', '')
      activationError.value   = ''
      activationSuccess.value = ''
      currentView.value       = 'activation'
    } else {
      errorMessage.value = apiData?.error || error.message || 'Impossible de se connecter au serveur'
    }
  } finally {
    loading.value = false
  }
}

// OTP helpers
const handleOtpInput = async (index, event) => {
  const val = event.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '')
  otpCode[index] = val.slice(-1)
  event.target.value = otpCode[index]
  if (otpCode[index] && index < 3) {
    await nextTick()
    otpRefs.value[index + 1]?.focus()
  }
}

const handleOtpKeydown = async (index, event) => {
  if (event.key === 'Backspace' && !otpCode[index] && index > 0) {
    await nextTick()
    otpRefs.value[index - 1]?.focus()
  }
}

const handleActivation = async () => {
  activationError.value   = ''
  activationSuccess.value = ''
  activationLoading.value = true

  const code = otpCode.join('')

  try {
    const response = await authActivate(pendingEmail.value, code)
    const data = response.data

    if (data.success) {
      activationSuccess.value = 'Code validé ! Définissez maintenant votre mot de passe.'

      // Réinitialiser les champs mot de passe avant d'afficher la vue
      newPassword.value = newPasswordConfirm.value = ''
      passwordRequirements.forEach(r => r.met = false)
      passwordSetError.value = ''

      setTimeout(() => {
        otpCode.splice(0, 4, '', '', '', '')
        currentView.value = 'set-password'   // ← redirige vers vue 3 au lieu de 'login'
      }, 900)

    } else {
      activationError.value = data.error || 'Code incorrect'
    }

  } catch (error) {
    const apiData = error.response?.data
    activationError.value = apiData?.error || error.message || 'Erreur serveur'
  } finally {
    activationLoading.value = false
  }
}

// ── NOUVEAU — Handler set-password ───────────────────────
const handleSetPassword = async () => {
  if (!isPasswordValid.value) return
  passwordSetError.value   = ''
  passwordSetLoading.value = true

  try {
    const response = await authSetPassword(pendingEmail.value, newPassword.value)
    const data = response.data

    if (data.success) {
      // Retour au login avec message de succès
      currentView.value    = 'login'
      successMessage.value = 'Compte activé ! Vous pouvez maintenant vous connecter.'
    } else {
      passwordSetError.value = data.error || 'Erreur lors de la définition du mot de passe'
    }
  } catch (error) {
    const apiData = error.response?.data
    passwordSetError.value = apiData?.error || error.message || 'Erreur serveur'
  } finally {
    passwordSetLoading.value = false
  }
}
// ─────────────────────────────────────────────────────────

const resendCode = () => {
  // TODO: appel API renvoi de code
  activationSuccess.value = 'Un nouveau code a été envoyé à votre adresse email.'
  activationError.value   = ''
}

const handleSocialLogin = (provider) => {
  alert(`Connexion ${provider} en cours de développement`)
}
const goToRegister = () => router.push('/register')

// ─── Styles ───────────────────────────────────────────────
const containerStyle = {
  minHeight: '100vh',
  background: '#F7F8FA',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  padding: '20px',
  fontFamily: '"Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
  position: 'relative',
  overflow: 'hidden'
}

const mainContainerStyle = {
  width: '100%',
  maxWidth: '1180px',
  display: 'grid',
  gridTemplateColumns: '1fr 1fr',
  gap: '60px',
  alignItems: 'center',
  position: 'relative',
  zIndex: 1
}

const leftSideStyle = {
  color: '#36454F',
  padding: '40px 40px 40px 20px'
}

const logoHeaderStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '14px',
  marginBottom: '56px'
}

const logoContainerStyle = {
  width: '46px',
  height: '46px',
  background: 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
  borderRadius: '13px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  boxShadow: '0 8px 20px rgba(16, 185, 129, 0.3)'
}

const logoStyle = {
  width: '22px',
  height: '22px',
  color: '#fff',
  strokeWidth: '2.5'
}

const brandTitleStyle = {
  fontSize: '23px',
  fontWeight: '700',
  color: '#1a2e2a',
  margin: 0,
  letterSpacing: '-0.03em'
}

const featuresContainerStyle = {
  display: 'flex',
  flexDirection: 'column',
  gap: '28px'
}

const featureItemStyle = {
  display: 'flex',
  gap: '18px',
  alignItems: 'flex-start',
  padding: '16px',
  borderRadius: '14px',
  transition: 'background 0.2s'
}

const featureIconStyle = {
  width: '44px',
  height: '44px',
  background: 'rgba(16, 185, 129, 0.1)',
  borderRadius: '11px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  flexShrink: 0,
  color: '#10b981',
  border: '1px solid rgba(16, 185, 129, 0.15)'
}

const featureTitleStyle = {
  fontSize: '15px',
  fontWeight: '600',
  color: '#1a2e2a',
  margin: '0 0 5px 0'
}

const featureDescStyle = {
  fontSize: '13.5px',
  color: '#5a7a70',
  margin: 0,
  lineHeight: '1.6'
}

const footerLinksStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '12px',
  marginTop: '52px',
  paddingLeft: '4px'
}

const footerLinkStyle = {
  fontSize: '13px',
  color: '#90a8a0',
  textDecoration: 'none',
  transition: 'color 0.2s'
}

const separatorStyle = {
  color: '#c0d0cc'
}

const rightSideStyle = {
  display: 'flex',
  justifyContent: 'center'
}

const formCardStyle = {
  width: '100%',
  maxWidth: '460px',
  background: 'rgba(255,255,255,0.96)',
  backdropFilter: 'blur(24px)',
  borderRadius: '22px',
  padding: '40px 36px',
  border: '1px solid rgba(16, 185, 129, 0.12)',
  boxShadow: '0 4px 6px rgba(0,0,0,0.04), 0 16px 32px rgba(0,0,0,0.10), 0 32px 48px rgba(16, 185, 129, 0.06)'
}

const formHeaderStyle = {
  marginBottom: '28px',
  textAlign: 'left'
}

const formTitleStyle = {
  fontSize: '26px',
  fontWeight: '700',
  color: '#1a2e2a',
  margin: '0 0 6px 0',
  letterSpacing: '-0.025em'
}

const formSubtitleStyle = {
  fontSize: '14px',
  color: '#5a7a70',
  margin: 0,
  lineHeight: '1.6'
}

const emailHighlightStyle = {
  color: '#10b981',
  fontWeight: '600'
}

const activationIconStyle = {
  width: '60px',
  height: '60px',
  background: 'rgba(16, 185, 129, 0.08)',
  borderRadius: '16px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  marginBottom: '20px',
  border: '1px solid rgba(16, 185, 129, 0.15)'
}

const getAlertStyle = (type) => ({
  display: 'flex',
  alignItems: 'center',
  gap: '10px',
  padding: '11px 14px',
  background: type === 'error' ? 'rgba(239,68,68,0.08)' : 'rgba(16,185,129,0.08)',
  border: `1px solid ${type === 'error' ? 'rgba(239,68,68,0.25)' : 'rgba(16,185,129,0.25)'}`,
  borderRadius: '11px',
  color: type === 'error' ? '#dc2626' : '#059669',
  fontSize: '13.5px',
  marginBottom: '18px',
  lineHeight: '1.5'
})

const socialButtonsStyle = {
  display: 'grid',
  gridTemplateColumns: 'repeat(3, 1fr)',
  gap: '10px',
  marginBottom: '20px'
}

const socialButtonStyle = {
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  gap: '7px',
  padding: '10px',
  background: '#f4f7f6',
  border: '1px solid #e0eae6',
  borderRadius: '11px',
  color: '#36454F',
  fontSize: '13px',
  fontWeight: '500',
  cursor: 'pointer',
  transition: 'all 0.2s'
}

const dividerStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '14px',
  margin: '20px 0',
  fontSize: '12px',
  color: '#90a8a0',
  letterSpacing: '0.04em',
  textTransform: 'uppercase'
}

const formStyle = {
  display: 'flex',
  flexDirection: 'column',
  gap: '18px'
}

const formGroupStyle = {
  display: 'flex',
  flexDirection: 'column',
  gap: '7px'
}

const labelStyle = {
  fontSize: '13.5px',
  fontWeight: '500',
  color: '#2d4a42'
}

const inputWrapperStyle = {
  position: 'relative',
  display: 'flex',
  alignItems: 'center'
}

const inputIconStyle = {
  position: 'absolute',
  left: '13px',
  width: '16px',
  height: '16px',
  color: '#90a8a0',
  strokeWidth: '2',
  pointerEvents: 'none'
}

const getInputStyle = (inputName, hasRightIcon = false) => ({
  width: '100%',
  padding: `11px ${hasRightIcon ? '40px' : '12px'} 11px 38px`,
  background: focusedInput.value === inputName ? '#fff' : '#f4f9f7',
  border: focusedInput.value === inputName ? '1.5px solid #10b981' : '1.5px solid #dceee8',
  borderRadius: '11px',
  fontSize: '14px',
  color: '#1a2e2a',
  transition: 'all 0.2s',
  outline: 'none',
  boxSizing: 'border-box',
  boxShadow: focusedInput.value === inputName ? '0 0 0 3px rgba(16,185,129,0.1)' : 'none'
})

// ── NOUVEAU — Style champ confirmation avec feedback couleur
const getConfirmPasswordStyle = () => ({
  width: '100%',
  padding: '11px 40px 11px 38px',
  background: focusedInput.value === 'newPasswordConfirm' ? '#fff' : '#f4f9f7',
  border: newPasswordConfirm.value
    ? newPassword.value === newPasswordConfirm.value
      ? '1.5px solid #10b981'
      : '1.5px solid #ef4444'
    : focusedInput.value === 'newPasswordConfirm'
      ? '1.5px solid #10b981'
      : '1.5px solid #dceee8',
  borderRadius: '11px',
  fontSize: '14px',
  color: '#1a2e2a',
  transition: 'all 0.2s',
  outline: 'none',
  boxSizing: 'border-box',
  boxShadow: focusedInput.value === 'newPasswordConfirm' ? '0 0 0 3px rgba(16,185,129,0.1)' : 'none'
})

// ── NOUVEAU — Segments de la barre de force (1 à 4)
const getStrengthSegmentStyle = (seg) => ({
  flex: 1,
  height: '5px',
  borderRadius: '3px',
  transition: 'all 0.3s',
  background: seg <= passwordScore.value ? passwordStrengthColor.value : '#dceee8'
})

// ── NOUVEAU — Style d'un critère individuel
const getCriterionStyle = (met) => ({
  display: 'flex',
  alignItems: 'center',
  gap: '5px',
  fontSize: '11.5px',
  fontWeight: met ? '600' : '500',
  color: met ? '#059669' : '#90a8a0',
  transition: 'color 0.2s'
})

// ── NOUVEAU — Styles stepper (vue set-password)
const stepperStyle = {
  display: 'flex',
  alignItems: 'center',
  marginBottom: '24px'
}
const stepItemStyle = {
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  gap: '5px',
  flex: 1
}
const _baseCircle = {
  width: '32px',
  height: '32px',
  borderRadius: '50%',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  fontSize: '13px',
  fontWeight: '700',
  transition: 'all 0.3s'
}
const stepDoneCircleStyle = {
  ..._baseCircle,
  background: 'linear-gradient(135deg, #10b981, #059669)',
  color: '#fff',
  boxShadow: '0 4px 12px rgba(16,185,129,0.3)'
}
const stepActiveCircleStyle = {
  ..._baseCircle,
  background: 'linear-gradient(135deg, #10b981, #059669)',
  color: '#fff',
  boxShadow: '0 4px 12px rgba(16,185,129,0.3)'
}
const _baseLabel = { fontSize: '11px', fontWeight: '600', textAlign: 'center', whiteSpace: 'nowrap' }
const stepDoneLabelStyle   = { ..._baseLabel, color: '#10b981' }
const stepActiveLabelStyle = { ..._baseLabel, color: '#10b981' }
const stepLineDoneStyle = {
  flex: 1,
  height: '2px',
  background: 'linear-gradient(90deg, #10b981, #059669)',
  margin: '0 8px',
  marginBottom: '18px'
}
// ─────────────────────────────────────────────────────────

const togglePasswordStyle = {
  position: 'absolute',
  right: '11px',
  background: 'none',
  border: 'none',
  cursor: 'pointer',
  color: '#90a8a0',
  padding: '6px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  borderRadius: '7px',
  transition: 'all 0.2s'
}

// OTP inputs
const otpContainerStyle = {
  display: 'flex',
  gap: '12px',
  justifyContent: 'center',
  margin: '24px 0'
}

const getOtpInputStyle = (i) => ({
  width: '64px',
  height: '72px',
  textAlign: 'center',
  fontSize: '26px',
  fontWeight: '700',
  letterSpacing: '0.05em',
  textTransform: 'uppercase',
  color: '#1a2e2a',
  background: focusedOtp.value === i ? '#fff' : '#f4f9f7',
  border: focusedOtp.value === i
    ? '2px solid #10b981'
    : otpCode[i]
      ? '2px solid rgba(16,185,129,0.5)'
      : '2px solid #dceee8',
  borderRadius: '14px',
  outline: 'none',
  transition: 'all 0.2s',
  boxShadow: focusedOtp.value === i ? '0 0 0 4px rgba(16,185,129,0.12)' : 'none',
  caretColor: '#10b981'
})

const submitButtonStyle = computed(() => ({
  width: '100%',
  padding: '13px',
  background: submitHovered.value && !loading.value && !activationLoading.value && !passwordSetLoading.value
    ? 'linear-gradient(135deg, #059669 0%, #047857 100%)'
    : 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
  color: '#fff',
  border: 'none',
  borderRadius: '12px',
  fontSize: '14.5px',
  fontWeight: '600',
  cursor: (loading.value || activationLoading.value || passwordSetLoading.value) ? 'not-allowed' : 'pointer',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  gap: '10px',
  transition: 'all 0.25s',
  marginTop: '6px',
  opacity: (loading.value || activationLoading.value || passwordSetLoading.value) ? 0.75 : 1,
  transform: submitHovered.value && !loading.value && !activationLoading.value && !passwordSetLoading.value
    ? 'translateY(-1px)' : 'translateY(0)',
  boxShadow: submitHovered.value && !loading.value && !activationLoading.value && !passwordSetLoading.value
    ? '0 8px 20px rgba(16, 185, 129, 0.35)'
    : '0 3px 10px rgba(16, 185, 129, 0.2)',
  letterSpacing: '0.01em'
}))

const formFooterStyle = {
  marginTop: '20px',
  textAlign: 'center'
}

const footerTextStyle = {
  fontSize: '13.5px',
  color: '#5a7a70',
  margin: 0
}

const linkStyle = {
  color: '#10b981',
  textDecoration: 'none',
  fontWeight: '600',
  transition: 'color 0.2s'
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

* { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }

/* ── Background ────────────────── */
.animated-bg {
  position: fixed;
  inset: 0;
  overflow: hidden;
  z-index: 0;
  pointer-events: none;
}

.grid-overlay {
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(16,185,129,0.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(16,185,129,0.04) 1px, transparent 1px);
  background-size: 40px 40px;
}

.gradient-orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.25;
  animation: float 22s ease-in-out infinite;
}

.orb-1 {
  width: 480px; height: 480px;
  background: radial-gradient(circle, rgba(16,185,129,0.45), transparent);
  top: -180px; left: -180px;
}
.orb-2 {
  width: 560px; height: 560px;
  background: radial-gradient(circle, rgba(5,150,105,0.35), transparent);
  bottom: -220px; right: -180px;
  animation-delay: 11s;
}
.orb-3 {
  width: 400px; height: 400px;
  background: radial-gradient(circle, rgba(16,185,129,0.2), transparent);
  top: 40%; left: 45%;
  animation-delay: 5s;
}

/* ── Animations ────────────────── */
@keyframes float {
  0%,100% { transform: translate(0,0); }
  33%      { transform: translate(28px,-28px); }
  66%      { transform: translate(-20px,18px); }
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateX(-24px); }
  to   { opacity: 1; transform: translateX(0); }
}
@keyframes fadeInDelay {
  from { opacity: 0; transform: translateX(24px); }
  to   { opacity: 1; transform: translateX(0); }
}
@keyframes spin { to { transform: rotate(360deg); } }

.fade-in       { animation: fadeIn 0.7s cubic-bezier(0.16,1,0.3,1) backwards; }
.fade-in-delay { animation: fadeInDelay 0.7s cubic-bezier(0.16,1,0.3,1) 0.15s backwards; }

/* ── Divider pseudo-elements ──── */
div[style*="dividerStyle"]::before,
div[style*="dividerStyle"]::after {
  content: "";
  flex: 1;
  height: 1px;
  background: #e0eae6;
}

/* ── Alert transition ─────────── */
.alert-enter-active, .alert-leave-active { transition: all 0.3s ease; }
.alert-enter-from { opacity: 0; transform: translateY(-8px); }
.alert-leave-to   { opacity: 0; transform: translateY(6px); }

/* ── Spinner ──────────────────── */
.spinner {
  width: 15px; height: 15px;
  border: 2px solid rgba(255,255,255,0.35);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.75s linear infinite;
}

/* ── Feature card hover ───────── */
.feature-card:hover {
  background: rgba(16,185,129,0.04) !important;
}

/* ── Social button hover ──────── */
.social-btn:hover {
  background: #eaf4f0 !important;
  border-color: #b2d8cc !important;
}

/* ── Input placeholder ────────── */
input::placeholder { color: #a8c0b8; }

/* ── Link hover ───────────────── */
a:hover { opacity: 0.8; }

/* ── Responsive ───────────────── */
@media (max-width: 900px) {
  div[style*="gridTemplateColumns"] {
    grid-template-columns: 1fr !important;
    gap: 32px !important;
  }
}
</style>