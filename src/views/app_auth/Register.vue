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
          <div :style="featureItemStyle" v-for="(feature, index) in features" :key="index" class="feature-card">
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

      <!-- Right Side - Registration Form -->
      <div :style="rightSideStyle" class="fade-in-delay">
        <div :style="formCardStyle">

          <!-- Progress Indicator -->
          <div :style="progressIndicatorStyle">
            <div :style="progressStepStyle">
              <div :style="progressStepCircleStyle(1)">
                <svg v-if="currentStep > 1" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <span v-else>1</span>
              </div>
              <span :style="progressStepLabelStyle(1)">Informations</span>
            </div>
            <div :style="progressLineStyle"></div>
            <div :style="progressStepStyle">
              <div :style="progressStepCircleStyle(2)">
                <span>2</span>
              </div>
              <span :style="progressStepLabelStyle(2)">Sécurité</span>
            </div>
          </div>

          <!-- Header -->
          <div :style="formHeaderStyle">
            <h2 :style="formTitleStyle">{{ currentStep === 1 ? 'Inscription' : 'Sécurité et finalisation' }}</h2>
            <p :style="formSubtitleStyle">{{ currentStep === 1 ? 'Créez votre compte Next Stock' : 'Finalisez votre inscription' }}</p>
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

          <!-- Form -->
          <form @submit.prevent="handleFormSubmit" :style="formStyle">

            <!-- ── STEP 1 ─────────────────────────────────── -->
            <div v-if="currentStep === 1" :style="stepContainerStyle">

              <!-- User Type -->
              <div :style="formGroupStyle">
                <label :style="labelStyle">Type de compte</label>
                <div :style="userTypeButtonsStyle">
                  <button type="button" :style="getUserTypeButtonStyle('particulier')" @click="userType = 'particulier'">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                      <circle cx="12" cy="7" r="4"/>
                    </svg>
                    Particulier
                  </button>
                  <button type="button" :style="getUserTypeButtonStyle('entreprise')" @click="userType = 'entreprise'">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Entreprise
                  </button>
                </div>
              </div>

              <!-- Particulier Fields -->
              <template v-if="userType === 'particulier'">
                <div :style="twoColsStyle">
                  <div :style="formGroupStyle">
                    <label :style="labelStyle">Nom</label>
                    <div :style="inputWrapperStyle">
                      <svg :style="inputIconStyle" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                      <input v-model="formData.nom" type="text" :style="getInputStyle('nom', fieldErrors.nom)" @focus="focusedInput='nom'" @blur="validateField('nom', formData.nom, 'Le nom est requis')" @input="validateField('nom', formData.nom, 'Le nom est requis')" placeholder="Votre nom" required/>
                    </div>
                    <div v-if="fieldErrors.nom" :style="fieldErrorStyle">{{ fieldErrors.nom }}</div>
                  </div>
                  <div :style="formGroupStyle">
                    <label :style="labelStyle">Prénom</label>
                    <div :style="inputWrapperStyle">
                      <svg :style="inputIconStyle" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                      <input v-model="formData.prenom" type="text" :style="getInputStyle('prenom', fieldErrors.prenom)" @focus="focusedInput='prenom'" @blur="validateField('prenom', formData.prenom, 'Le prénom est requis')" @input="validateField('prenom', formData.prenom, 'Le prénom est requis')" placeholder="Votre prénom" required/>
                    </div>
                    <div v-if="fieldErrors.prenom" :style="fieldErrorStyle">{{ fieldErrors.prenom }}</div>
                  </div>
                </div>

                <div :style="twoColsStyle">
                  <div :style="formGroupStyle">
                    <label :style="labelStyle">Contact</label>
                    <div :style="phoneInputWrapperStyle">
                      <select v-model="selectedCountryCode" :style="countrySelectStyle" @change="validateContact('contact')">
                        <option v-for="c in countries" :key="c.code + c.name" :value="c.code">{{ c.flag }} {{ c.code }}</option>
                      </select>
                      <input v-model="formData.contact" type="tel" :style="getPhoneInputStyle('contact')" @focus="focusedInput='contact'" @blur="validateContact('contact')" @input="handlePhoneInput('contact')" :placeholder="`${selectedCountryCode} XX XX XX XX`" required maxlength="15"/>
                    </div>
                    <div v-if="fieldErrors.contact" :style="fieldErrorStyle">{{ fieldErrors.contact }}</div>
                  </div>
                  <div :style="formGroupStyle">
                    <label :style="labelStyle">Email</label>
                    <div :style="inputWrapperStyle">
                      <svg :style="inputIconStyle" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                      <input v-model="formData.email" type="email" :style="getInputStyle('email', fieldErrors.email)" @focus="focusedInput='email'" @blur="validateEmail" @input="validateEmail" placeholder="exemple@email.com" required autocomplete="email"/>
                    </div>
                    <div v-if="fieldErrors.email" :style="fieldErrorStyle">{{ fieldErrors.email }}</div>
                  </div>
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Type d'activité</label>
                  <div :style="inputWrapperStyle">
                    <svg :style="inputIconStyle" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <select v-model="formData.type_activite" :style="getInputStyle('type_activite')" @focus="focusedInput='type_activite'" @blur="focusedInput=null" required>
                      <option value="">Sélectionnez votre type d'activité...</option>
                      <option value="boutique">Boutique</option>
                      <option value="magasin">Magasin</option>
                      <option value="entrepot">Entrepôt</option>
                      <option value="supermarché">Supermarché</option>
                      <option value="pharmacie">Pharmacie</option>
                      <option value="restaurant">Restaurant</option>
                      <option value="e-commerce">E-commerce</option>
                      <option value="distributeur">Distributeur</option>
                      <option value="grossiste">Grossiste</option>
                      <option value="détaillant">Détaillant</option>
                      <option value="autre">Autre activité</option>
                    </select>
                  </div>
                </div>
              </template>

              <!-- Entreprise Fields -->
              <template v-if="userType === 'entreprise'">
                <div :style="twoColsStyle">
                  <div :style="formGroupStyle">
                    <label :style="labelStyle">Nom admin</label>
                    <div :style="inputWrapperStyle">
                      <svg :style="inputIconStyle" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                      <input v-model="formData.nom_admin" type="text" :style="getInputStyle('nom_admin', fieldErrors.nom_admin)" @focus="focusedInput='nom_admin'" @blur="validateField('nom_admin', formData.nom_admin, 'Le nom est requis')" @input="validateField('nom_admin', formData.nom_admin, 'Le nom est requis')" placeholder="Nom de l'admin" required/>
                    </div>
                    <div v-if="fieldErrors.nom_admin" :style="fieldErrorStyle">{{ fieldErrors.nom_admin }}</div>
                  </div>
                  <div :style="formGroupStyle">
                    <label :style="labelStyle">Prénom admin</label>
                    <div :style="inputWrapperStyle">
                      <svg :style="inputIconStyle" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                      <input v-model="formData.prenom_admin" type="text" :style="getInputStyle('prenom_admin', fieldErrors.prenom_admin)" @focus="focusedInput='prenom_admin'" @blur="validateField('prenom_admin', formData.prenom_admin, 'Le prénom est requis')" @input="validateField('prenom_admin', formData.prenom_admin, 'Le prénom est requis')" placeholder="Prénom de l'admin" required/>
                    </div>
                    <div v-if="fieldErrors.prenom_admin" :style="fieldErrorStyle">{{ fieldErrors.prenom_admin }}</div>
                  </div>
                </div>

                <div :style="twoColsStyle">
                  <div :style="formGroupStyle">
                    <label :style="labelStyle">Nom entreprise</label>
                    <div :style="inputWrapperStyle">
                      <svg :style="inputIconStyle" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                      <input v-model="formData.nom_entreprise" type="text" :style="getInputStyle('nom_entreprise', fieldErrors.nom_entreprise)" @focus="focusedInput='nom_entreprise'" @blur="validateField('nom_entreprise', formData.nom_entreprise, 'Le nom est requis')" @input="validateField('nom_entreprise', formData.nom_entreprise, 'Le nom est requis')" placeholder="Nom de votre entreprise" required/>
                    </div>
                    <div v-if="fieldErrors.nom_entreprise" :style="fieldErrorStyle">{{ fieldErrors.nom_entreprise }}</div>
                  </div>
                  <div :style="formGroupStyle">
                    <label :style="labelStyle">Contact</label>
                    <div :style="phoneInputWrapperStyle">
                      <select v-model="selectedCountryCodeEntreprise" :style="countrySelectStyle" @change="validateContact('contact_entreprise')">
                        <option v-for="c in countries" :key="c.code + c.name" :value="c.code">{{ c.flag }} {{ c.code }}</option>
                      </select>
                      <input v-model="formData.contact_entreprise" type="tel" :style="getPhoneInputStyle('contact_entreprise')" @focus="focusedInput='contact_entreprise'" @blur="validateContact('contact_entreprise')" @input="handlePhoneInput('contact_entreprise')" :placeholder="`${selectedCountryCodeEntreprise} XX XX XX XX`" required maxlength="15"/>
                    </div>
                    <div v-if="fieldErrors.contact_entreprise" :style="fieldErrorStyle">{{ fieldErrors.contact_entreprise }}</div>
                  </div>
                </div>

                <div :style="twoColsStyle">
                  <div :style="formGroupStyle">
                    <label :style="labelStyle">Email</label>
                    <div :style="inputWrapperStyle">
                      <svg :style="inputIconStyle" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                      <input v-model="formData.email" type="email" :style="getInputStyle('email', fieldErrors.email)" @focus="focusedInput='email'" @blur="validateEmail" @input="validateEmail" placeholder="contact@entreprise.com" required autocomplete="email"/>
                    </div>
                    <div v-if="fieldErrors.email" :style="fieldErrorStyle">{{ fieldErrors.email }}</div>
                  </div>
                  <div :style="formGroupStyle">
                    <label :style="labelStyle">Adresse</label>
                    <div :style="inputWrapperStyle">
                      <svg :style="inputIconStyle" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                      <input v-model="formData.adresse_entreprise" type="text" :style="getInputStyle('adresse_entreprise', fieldErrors.adresse_entreprise)" @focus="focusedInput='adresse_entreprise'" @blur="validateField('adresse_entreprise', formData.adresse_entreprise, 'L\'adresse est requise')" @input="validateField('adresse_entreprise', formData.adresse_entreprise, 'L\'adresse est requise')" placeholder="Adresse complète" required/>
                    </div>
                    <div v-if="fieldErrors.adresse_entreprise" :style="fieldErrorStyle">{{ fieldErrors.adresse_entreprise }}</div>
                  </div>
                </div>
              </template>

              <button type="button" :style="submitButtonStyle" :disabled="!isStep1Valid" @click="goToNextStep" @mouseenter="submitHovered=true" @mouseleave="submitHovered=false">
                Suivant
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
              </button>
            </div>

            <!-- ── STEP 2 ─────────────────────────────────── -->
            <div v-if="currentStep === 2" :style="stepContainerStyle">

              <!-- Password -->
              <div :style="formGroupStyle">
                <label :style="labelStyle">Mot de passe</label>
                <div :style="inputWrapperStyle">
                  <svg :style="inputIconStyle" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                  <input v-model="formData.password" :type="showPassword ? 'text' : 'password'" :style="getInputStyle('password', false, true)" @focus="focusedInput='password'" @blur="focusedInput=null" @input="checkPasswordStrength" placeholder="Votre mot de passe" required autocomplete="new-password"/>
                  <button type="button" :style="togglePasswordStyle" @click="showPassword=!showPassword" tabindex="-1">
                    <svg v-if="!showPassword" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                  </button>
                </div>

                <!-- Strength indicator -->
                <div v-if="formData.password" style="margin-top:10px">
                  <div style="display:flex;gap:4px;margin-bottom:6px">
                    <div v-for="seg in 4" :key="seg" :style="getStrengthSegmentStyle(seg)"></div>
                  </div>
                  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
                    <span :style="{ fontSize:'12px', fontWeight:'600', color: passwordStrengthColor }">{{ passwordStrengthLabel }}</span>
                    <span style="font-size:11px;color:#90a8a0">{{ passwordScore }}/4 critères</span>
                  </div>
                  <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px">
                    <div v-for="req in passwordRequirementsArr" :key="req.key" :style="getCriterionStyle(req.met)">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline v-if="req.met" points="20 6 9 17 4 12"/>
                        <circle v-else cx="12" cy="12" r="8"/>
                      </svg>
                      {{ req.label }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Confirm Password -->
              <div :style="formGroupStyle">
                <label :style="labelStyle">Confirmer le mot de passe</label>
                <div :style="inputWrapperStyle">
                  <svg :style="inputIconStyle" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                  <input v-model="formData.passwordConfirm" :type="showPasswordConfirm ? 'text' : 'password'" :style="getConfirmInputStyle()" @focus="focusedInput='passwordConfirm'" @blur="focusedInput=null" placeholder="Confirmez votre mot de passe" required autocomplete="new-password"/>
                  <button type="button" :style="togglePasswordStyle" @click="showPasswordConfirm=!showPasswordConfirm" tabindex="-1">
                    <svg v-if="!showPasswordConfirm" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                  </button>
                </div>
                <div v-if="formData.passwordConfirm && formData.password !== formData.passwordConfirm" style="font-size:12px;color:#dc2626;margin-top:4px;font-weight:500">Les mots de passe ne correspondent pas</div>
                <div v-if="formData.passwordConfirm && formData.password === formData.passwordConfirm && formData.password" style="font-size:12px;color:#059669;margin-top:4px;font-weight:500;display:flex;align-items:center;gap:4px">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                  Les mots de passe correspondent
                </div>
              </div>

              <!-- Checkboxes -->
              <div style="display:flex;flex-direction:column;gap:14px">
                <label :style="checkboxLabelStyle">
                  <input type="checkbox" v-model="acceptTerms" :style="checkboxStyle" required/>
                  <span>J'accepte les <a href="#" :style="linkStyle">Conditions d'utilisation</a> et la <a href="#" :style="linkStyle">Politique de confidentialité</a></span>
                </label>
                <label :style="checkboxLabelStyle">
                  <input type="checkbox" v-model="acceptMarketing" :style="checkboxStyle"/>
                  <span>J'accepte de recevoir des emails marketing (optionnel)</span>
                </label>
              </div>

              <!-- Buttons -->
              <div style="display:flex;gap:10px;margin-top:4px">
                <button type="button" :style="prevButtonStyle" @click="goToPreviousStep">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                  Précédent
                </button>
                <button type="submit" :style="submitButtonStyle" :disabled="loading" @mouseenter="submitHovered=true" @mouseleave="submitHovered=false">
                  <span v-if="!loading">Créer mon compte</span>
                  <span v-else style="display:flex;align-items:center;gap:10px">
                    <div class="spinner"></div> Inscription...
                  </span>
                </button>
              </div>
            </div>

          </form>

          <!-- Footer -->
          <div :style="formFooterStyle">
            <p :style="footerTextStyle">
              Déjà un compte ?
              <a href="#" :style="linkStyle" @click.prevent="goToLogin">Se connecter</a>
            </p>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { useRouter } from 'vue-router'
import { authRegister } from '../../services/api.js'

const router = useRouter()

const loading            = ref(false)
const submitHovered      = ref(false)
const focusedInput       = ref(null)
const showPassword       = ref(false)
const showPasswordConfirm = ref(false)
const errorMessage       = ref('')
const successMessage     = ref('')
const userType           = ref('particulier')
const currentStep        = ref(1)
const acceptTerms        = ref(false)
const acceptMarketing    = ref(false)
const selectedCountryCode          = ref('+225')
const selectedCountryCodeEntreprise = ref('+225')

const formData = reactive({
  email: '', password: '', passwordConfirm: '',
  nom: '', prenom: '', contact: '', type_activite: '',
  nom_admin: '', prenom_admin: '', nom_entreprise: '',
  contact_entreprise: '', adresse_entreprise: ''
})

const fieldErrors = reactive({
  nom: '', prenom: '', contact: '', email: '',
  nom_admin: '', prenom_admin: '', nom_entreprise: '',
  contact_entreprise: '', adresse_entreprise: ''
})

// ── Password strength ──────────────────────────────────────
const passwordRequirementsArr = reactive([
  { key: 'uppercase', label: 'Majuscule',        met: false },
  { key: 'lowercase', label: 'Minuscule',        met: false },
  { key: 'number',    label: 'Chiffre',           met: false },
  { key: 'special',   label: 'Caractère spécial', met: false },
])

const passwordScore = computed(() => passwordRequirementsArr.filter(r => r.met).length)

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
  const p = formData.password
  passwordRequirementsArr[0].met = /[A-Z]/.test(p)
  passwordRequirementsArr[1].met = /[a-z]/.test(p)
  passwordRequirementsArr[2].met = /[0-9]/.test(p)
  passwordRequirementsArr[3].met = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(p)
}

// ── Validation ─────────────────────────────────────────────
const validateField = (name, value, msg) => {
  if (!value || value.trim() === '') { fieldErrors[name] = msg; return false }
  fieldErrors[name] = ''; return true
}

const validateEmail = () => {
  if (!formData.email) { fieldErrors.email = "L'email est requis"; return false }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) { fieldErrors.email = 'Email invalide'; return false }
  fieldErrors.email = ''; return true
}

const validateContact = (field) => {
  const v = formData[field]
  if (!v) { fieldErrors[field] = 'Le contact est requis'; return false }
  if (!/^[0-9\s]+$/.test(v)) { fieldErrors[field] = 'Chiffres uniquement'; return false }
  if (v.replace(/\s/g, '').length < 8) { fieldErrors[field] = 'Minimum 8 chiffres'; return false }
  fieldErrors[field] = ''; return true
}

const handlePhoneInput = (field) => { formData[field] = formData[field].replace(/[^0-9\s]/g, '') }

const isStep1Valid = computed(() => {
  if (userType.value === 'particulier') {
    return formData.nom && formData.prenom && formData.contact && formData.email && formData.type_activite &&
      !fieldErrors.nom && !fieldErrors.prenom && !fieldErrors.contact && !fieldErrors.email &&
      /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email) &&
      /^[0-9\s]+$/.test(formData.contact) && formData.contact.replace(/\s/g, '').length >= 8
  }
  return formData.nom_admin && formData.prenom_admin && formData.nom_entreprise &&
    formData.contact_entreprise && formData.email && formData.adresse_entreprise &&
    !fieldErrors.nom_admin && !fieldErrors.prenom_admin && !fieldErrors.nom_entreprise &&
    !fieldErrors.contact_entreprise && !fieldErrors.email && !fieldErrors.adresse_entreprise &&
    /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email) &&
    /^[0-9\s]+$/.test(formData.contact_entreprise) && formData.contact_entreprise.replace(/\s/g, '').length >= 8
})

const isStep2Valid = computed(() =>
  formData.password && formData.passwordConfirm &&
  formData.password === formData.passwordConfirm &&
  passwordScore.value >= 2 &&
  formData.password.length >= 8 &&
  acceptTerms.value
)

const goToNextStep = () => {
  if (isStep1Valid.value) { currentStep.value = 2; errorMessage.value = '' }
  else errorMessage.value = 'Veuillez remplir tous les champs requis'
}
const goToPreviousStep = () => { currentStep.value = 1; errorMessage.value = '' }

const handleFormSubmit = async () => {
  errorMessage.value = ''
  successMessage.value = ''

  // Étape 1 : on passe simplement à l'étape suivante
  if (currentStep.value === 1) {
    goToNextStep()
    return
  }

  // Étape 2 : vérifications détaillées pour afficher une alerte précise
  const issues = []

  if (!formData.password || !formData.passwordConfirm) {
    issues.push('Veuillez saisir et confirmer votre mot de passe')
  }

  if (formData.password && formData.password.length < 8) {
    issues.push('Le mot de passe doit contenir au moins 8 caractères')
  }

  if (formData.password && passwordScore.value < 2) {
    issues.push("Le mot de passe n'est pas assez sécurisé (majuscule, minuscule, chiffre, caractère spécial)")
  }

  if (formData.password && formData.passwordConfirm && formData.password !== formData.passwordConfirm) {
    issues.push('Les mots de passe ne correspondent pas')
  }

  if (!acceptTerms.value) {
    issues.push("Vous devez accepter les conditions d'utilisation pour créer un compte")
  }

  if (!isStep2Valid.value || issues.length) {
    errorMessage.value = issues[0] || 'Veuillez vérifier les informations de sécurité avant de continuer'
    return
  }

  await handleRegister()
}

const handleRegister = async () => {
  errorMessage.value = ''; successMessage.value = ''
  loading.value = true
  try {
    const contactField = userType.value === 'particulier' ? formData.contact : formData.contact_entreprise
    const code = userType.value === 'particulier' ? selectedCountryCode.value : selectedCountryCodeEntreprise.value
    const payload = {
      email: formData.email, password: formData.password,
      telephone: code + contactField.replace(/\s/g, ''),
      photo: '', role: 1, access: JSON.stringify(['ALL_TEST']), statut: 'actif',
      nom: userType.value === 'particulier' ? formData.nom : formData.nom_admin,
      prenom: userType.value === 'particulier' ? formData.prenom : formData.prenom_admin,
      ...(userType.value === 'entreprise' && { entreprise: formData.nom_entreprise })
    }
    const response = await authRegister(payload)
    const data = response.data
    if (data.success) {
      successMessage.value = 'Inscription réussie ! Redirection...'
      setTimeout(() => router.push('/login'), 2000)
    } else {
      errorMessage.value = data.error || data.message || "Erreur lors de l'inscription"
    }
  } catch (error) {
    const d = error.response?.data
    errorMessage.value = d?.error || d?.details || error.message || 'Impossible de se connecter au serveur'
  } finally {
    loading.value = false
  }
}

const goToLogin = () => router.push('/login')

// ── Countries (condensed — same list as original) ──────────
const countries = [
  { code: '+225', name: 'CI', flag: '🇨🇮' },
  { code: '+221', name: 'SN', flag: '🇸🇳' },
  { code: '+226', name: 'BF', flag: '🇧🇫' },
  { code: '+223', name: 'ML', flag: '🇲🇱' },
  { code: '+229', name: 'BJ', flag: '🇧🇯' },
  { code: '+228', name: 'TG', flag: '🇹🇬' },
  { code: '+233', name: 'GH', flag: '🇬🇭' },
  { code: '+234', name: 'NG', flag: '🇳🇬' },
  { code: '+237', name: 'CM', flag: '🇨🇲' },
  { code: '+212', name: 'MA', flag: '🇲🇦' },
  { code: '+213', name: 'DZ', flag: '🇩🇿' },
  { code: '+216', name: 'TN', flag: '🇹🇳' },
  { code: '+20',  name: 'EG', flag: '🇪🇬' },
  { code: '+27',  name: 'ZA', flag: '🇿🇦' },
  { code: '+254', name: 'KE', flag: '🇰🇪' },
  { code: '+33',  name: 'FR', flag: '🇫🇷' },
  { code: '+32',  name: 'BE', flag: '🇧🇪' },
  { code: '+41',  name: 'CH', flag: '🇨🇭' },
  { code: '+49',  name: 'DE', flag: '🇩🇪' },
  { code: '+44',  name: 'GB', flag: '🇬🇧' },
  { code: '+39',  name: 'IT', flag: '🇮🇹' },
  { code: '+34',  name: 'ES', flag: '🇪🇸' },
  { code: '+351', name: 'PT', flag: '🇵🇹' },
  { code: '+1',   name: 'US', flag: '🇺🇸' },
  { code: '+1',   name: 'CA', flag: '🇨🇦' },
  { code: '+55',  name: 'BR', flag: '🇧🇷' },
  { code: '+86',  name: 'CN', flag: '🇨🇳' },
  { code: '+91',  name: 'IN', flag: '🇮🇳' },
  { code: '+81',  name: 'JP', flag: '🇯🇵' },
  { code: '+971', name: 'AE', flag: '🇦🇪' },
  { code: '+61',  name: 'AU', flag: '🇦🇺' },
]

// ── Features ───────────────────────────────────────────────
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

// ── Styles (tokens identiques à Login.vue) ─────────────────
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

const featureTitleStyle = { fontSize: '15px', fontWeight: '600', color: '#1a2e2a', margin: '0 0 5px 0' }
const featureDescStyle  = { fontSize: '13.5px', color: '#5a7a70', margin: 0, lineHeight: '1.6' }

const footerLinksStyle = {
  display: 'flex', alignItems: 'center', gap: '12px', marginTop: '52px', paddingLeft: '4px'
}
const footerLinkStyle  = { fontSize: '13px', color: '#90a8a0', textDecoration: 'none', transition: 'color 0.2s' }
const separatorStyle   = { color: '#c0d0cc' }

const rightSideStyle   = { display: 'flex', justifyContent: 'center' }

const formCardStyle = {
  width: '100%',
  maxWidth: '480px',
  background: 'rgba(255,255,255,0.96)',
  backdropFilter: 'blur(24px)',
  borderRadius: '22px',
  padding: '36px 36px',
  border: '1px solid rgba(16, 185, 129, 0.12)',
  boxShadow: '0 4px 6px rgba(0,0,0,0.04), 0 16px 32px rgba(0,0,0,0.10), 0 32px 48px rgba(16, 185, 129, 0.06)'
}

// Progress stepper
const progressIndicatorStyle = {
  display: 'flex', alignItems: 'center', marginBottom: '24px'
}
const progressStepStyle = {
  display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '5px', flex: 1
}
const progressStepCircleStyle = (step) => ({
  width: '32px', height: '32px', borderRadius: '50%',
  display: 'flex', alignItems: 'center', justifyContent: 'center',
  fontSize: '13px', fontWeight: '700',
  background: currentStep.value >= step
    ? 'linear-gradient(135deg, #10b981, #059669)'
    : '#e8f0ed',
  color: currentStep.value >= step ? '#fff' : '#90a8a0',
  boxShadow: currentStep.value >= step ? '0 4px 12px rgba(16,185,129,0.3)' : 'none',
  transition: 'all 0.3s'
})
const progressStepLabelStyle = (step) => ({
  fontSize: '11px', fontWeight: '600', textAlign: 'center', whiteSpace: 'nowrap',
  color: currentStep.value >= step ? '#10b981' : '#90a8a0'
})
const progressLineStyle = {
  flex: 1, height: '2px', margin: '0 8px', marginBottom: '18px',
  background: currentStep.value > 1
    ? 'linear-gradient(90deg, #10b981, #059669)'
    : '#dceee8',
  transition: 'all 0.3s'
}

const formHeaderStyle  = { marginBottom: '22px', textAlign: 'left' }
const formTitleStyle   = { fontSize: '24px', fontWeight: '700', color: '#1a2e2a', margin: '0 0 6px 0', letterSpacing: '-0.025em' }
const formSubtitleStyle = { fontSize: '14px', color: '#5a7a70', margin: 0, lineHeight: '1.6' }

const getAlertStyle = (type) => ({
  display: 'flex', alignItems: 'center', gap: '10px',
  padding: '11px 14px',
  background: type === 'error' ? 'rgba(239,68,68,0.08)' : 'rgba(16,185,129,0.08)',
  border: `1px solid ${type === 'error' ? 'rgba(239,68,68,0.25)' : 'rgba(16,185,129,0.25)'}`,
  borderRadius: '11px',
  color: type === 'error' ? '#dc2626' : '#059669',
  fontSize: '13.5px', marginBottom: '16px', lineHeight: '1.5'
})

const formStyle        = { display: 'flex', flexDirection: 'column', gap: '0' }
const stepContainerStyle = { display: 'flex', flexDirection: 'column', gap: '16px' }
const twoColsStyle     = { display: 'flex', gap: '12px' }
const formGroupStyle   = { flex: 1, display: 'flex', flexDirection: 'column', gap: '7px' }
const labelStyle       = { fontSize: '13.5px', fontWeight: '500', color: '#2d4a42' }
const fieldErrorStyle  = { fontSize: '12px', color: '#dc2626', fontWeight: '500' }

const inputWrapperStyle = { position: 'relative', display: 'flex', alignItems: 'center' }
const inputIconStyle   = {
  position: 'absolute', left: '13px', width: '16px', height: '16px',
  color: '#90a8a0', strokeWidth: '2', pointerEvents: 'none'
}

const getInputStyle = (name, error = false, hasRightIcon = false) => ({
  width: '100%',
  padding: `11px ${hasRightIcon ? '40px' : '12px'} 11px 38px`,
  background: focusedInput.value === name ? '#fff' : '#f4f9f7',
  border: error
    ? '1.5px solid #ef4444'
    : focusedInput.value === name
      ? '1.5px solid #10b981'
      : '1.5px solid #dceee8',
  borderRadius: '11px', fontSize: '14px', color: '#1a2e2a',
  transition: 'all 0.2s', outline: 'none', boxSizing: 'border-box',
  boxShadow: focusedInput.value === name ? '0 0 0 3px rgba(16,185,129,0.1)' : 'none',
  appearance: 'none'
})

const getConfirmInputStyle = () => ({
  width: '100%',
  padding: '11px 40px 11px 38px',
  background: focusedInput.value === 'passwordConfirm' ? '#fff' : '#f4f9f7',
  border: formData.passwordConfirm
    ? formData.password === formData.passwordConfirm
      ? '1.5px solid #10b981'
      : '1.5px solid #ef4444'
    : focusedInput.value === 'passwordConfirm'
      ? '1.5px solid #10b981'
      : '1.5px solid #dceee8',
  borderRadius: '11px', fontSize: '14px', color: '#1a2e2a',
  transition: 'all 0.2s', outline: 'none', boxSizing: 'border-box',
  boxShadow: focusedInput.value === 'passwordConfirm' ? '0 0 0 3px rgba(16,185,129,0.1)' : 'none'
})

// Phone input
const phoneInputWrapperStyle = { display: 'flex', alignItems: 'stretch', width: '100%' }
const countrySelectStyle = {
  padding: '11px 4px', border: '1.5px solid #dceee8',
  borderRight: 'none', borderTopLeftRadius: '11px', borderBottomLeftRadius: '11px',
  fontSize: '12px', fontWeight: '500', color: '#1a2e2a',
  background: '#f4f9f7', cursor: 'pointer', outline: 'none', appearance: 'none',
  backgroundImage: 'url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'9\' height=\'9\' viewBox=\'0 0 12 12\'%3E%3Cpath fill=\'%2310b981\' d=\'M6 9L1 4h10z\'/%3E%3C/svg%3E")',
  backgroundRepeat: 'no-repeat', backgroundPosition: 'right 4px center',
  paddingRight: '18px', width: '64px', flexShrink: 0, transition: 'all 0.2s'
}
const getPhoneInputStyle = (name) => ({
  flex: 1, padding: '11px 12px',
  background: focusedInput.value === name ? '#fff' : '#f4f9f7',
  border: fieldErrors[name]
    ? '1.5px solid #ef4444'
    : focusedInput.value === name ? '1.5px solid #10b981' : '1.5px solid #dceee8',
  borderLeft: 'none', borderTopRightRadius: '11px', borderBottomRightRadius: '11px',
  fontSize: '14px', color: '#1a2e2a', outline: 'none', boxSizing: 'border-box',
  transition: 'all 0.2s'
})

// Password strength segments
const getStrengthSegmentStyle = (seg) => ({
  flex: 1, height: '5px', borderRadius: '3px', transition: 'all 0.3s',
  background: seg <= passwordScore.value ? passwordStrengthColor.value : '#dceee8'
})
const getCriterionStyle = (met) => ({
  display: 'flex', alignItems: 'center', gap: '5px',
  fontSize: '11.5px', fontWeight: met ? '600' : '500',
  color: met ? '#059669' : '#90a8a0', transition: 'color 0.2s'
})

// User type selector
const getUserTypeButtonStyle = (type) => ({
  flex: 1, display: 'flex', alignItems: 'center', justifyContent: 'center',
  gap: '7px', padding: '10px 12px',
  background: userType.value === type ? 'rgba(16,185,129,0.08)' : '#f4f9f7',
  border: userType.value === type ? '1.5px solid #10b981' : '1.5px solid #dceee8',
  borderRadius: '11px', color: userType.value === type ? '#10b981' : '#5a7a70',
  fontSize: '13.5px', fontWeight: '600', cursor: 'pointer', transition: 'all 0.2s'
})

// Toggle password eye
const togglePasswordStyle = {
  position: 'absolute', right: '11px', background: 'none', border: 'none',
  cursor: 'pointer', color: '#90a8a0', padding: '6px',
  display: 'flex', alignItems: 'center', justifyContent: 'center',
  borderRadius: '7px', transition: 'all 0.2s'
}

// Submit button
const submitButtonStyle = computed(() => ({
  flex: 1, width: '100%',
  padding: '13px',
  background: submitHovered.value && !loading.value
    ? 'linear-gradient(135deg, #059669 0%, #047857 100%)'
    : 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
  color: '#fff', border: 'none', borderRadius: '12px',
  fontSize: '14.5px', fontWeight: '600',
  cursor: loading.value ? 'not-allowed' : 'pointer',
  display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '8px',
  transition: 'all 0.25s', opacity: loading.value ? 0.75 : 1,
  transform: submitHovered.value && !loading.value ? 'translateY(-1px)' : 'translateY(0)',
  boxShadow: submitHovered.value && !loading.value
    ? '0 8px 20px rgba(16, 185, 129, 0.35)'
    : '0 3px 10px rgba(16, 185, 129, 0.2)',
  letterSpacing: '0.01em'
}))

// Prev button
const prevButtonStyle = {
  padding: '13px 18px', background: '#f4f9f7',
  color: '#5a7a70', border: '1.5px solid #dceee8', borderRadius: '12px',
  fontSize: '13.5px', fontWeight: '600', cursor: 'pointer',
  display: 'flex', alignItems: 'center', gap: '6px', transition: 'all 0.2s',
  flexShrink: 0
}

// Checkboxes
const checkboxLabelStyle = {
  display: 'flex', alignItems: 'flex-start', gap: '10px',
  fontSize: '13px', color: '#5a7a70', fontWeight: '500', cursor: 'pointer', lineHeight: '1.5'
}
const checkboxStyle = {
  width: '16px', height: '16px', minWidth: '16px', marginTop: '2px',
  cursor: 'pointer', accentColor: '#10b981'
}

const linkStyle = { color: '#10b981', textDecoration: 'none', fontWeight: '600', transition: 'color 0.2s' }

const formFooterStyle  = { marginTop: '20px', textAlign: 'center' }
const footerTextStyle  = { fontSize: '13.5px', color: '#5a7a70', margin: 0 }
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

* { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }

/* ── Background ─────────────────────── */
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

/* ── Animations ─────────────────────── */
@keyframes float {
  0%,100% { transform: translate(0,0); }
  33%      { transform: translate(28px,-28px); }
  66%      { transform: translate(-20px,18px); }
}
@keyframes fadeIn      { from { opacity:0; transform:translateX(-24px); } to { opacity:1; transform:translateX(0); } }
@keyframes fadeInDelay { from { opacity:0; transform:translateX(24px);  } to { opacity:1; transform:translateX(0); } }
@keyframes spin        { to   { transform: rotate(360deg); } }

.fade-in       { animation: fadeIn      0.7s cubic-bezier(0.16,1,0.3,1) backwards; }
.fade-in-delay { animation: fadeInDelay 0.7s cubic-bezier(0.16,1,0.3,1) 0.15s backwards; }

/* ── Alert transition ───────────────── */
.alert-enter-active, .alert-leave-active { transition: all 0.3s ease; }
.alert-enter-from { opacity:0; transform:translateY(-8px); }
.alert-leave-to   { opacity:0; transform:translateY(6px); }

/* ── Spinner ────────────────────────── */
.spinner {
  width:15px; height:15px;
  border:2px solid rgba(255,255,255,0.35);
  border-top-color:#fff;
  border-radius:50%;
  animation: spin 0.75s linear infinite;
}

/* ── Feature card hover ─────────────── */
.feature-card:hover { background: rgba(16,185,129,0.04) !important; }

/* ── Input placeholder ──────────────── */
input::placeholder { color: #a8c0b8; }

/* ── Select focus ───────────────────── */
select:focus { border-color: #10b981 !important; }

/* ── Link hover ─────────────────────── */
a:hover { opacity: 0.8; }

/* ── Responsive ─────────────────────── */
@media (max-width: 900px) {
  div[style*="gridTemplateColumns"] {
    grid-template-columns: 1fr !important;
    gap: 32px !important;
  }
}
</style>