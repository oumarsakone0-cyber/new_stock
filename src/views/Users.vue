<template>
  <SidebarLayout currentPage="utilisateurs">
    <!-- Elegant Header -->
    <header :style="headerStyle" class="fade-in">
      <div :style="headerContentStyle">
        <div :style="breadcrumbStyle">
          <span :style="breadcrumbItemStyle">Tableau de bord</span>
          <svg :style="breadcrumbArrowStyle" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
          <span :style="breadcrumbActiveStyle">Utilisateurs</span>
        </div>
        <h1 :style="titleStyle">Gestion des Utilisateurs</h1>
        <p :style="subtitleStyle">
          <svg :style="subtitleIconStyle" viewBox="0 0 24 24" fill="currentColor">
            <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>
          {{ filteredUsers.length }} utilisateur(s){{ activeFilter !== 'all' ? ' filtr&eacute;(s)' : '' }}
        </p>
        
        <!-- Filtres par codes access -->
        <div :style="filtersContainerStyle">
          <button 
            :style="getFilterButtonStyle('all')"
            @click="activeFilter = 'all'"
          >
            Tous
          </button>
          <button 
            v-for="code in ACCESS_CODES_LIST"
            :key="code.code"
            :style="getFilterButtonStyle(code.code)"
            @click="activeFilter = code.code"
          >
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path :d="code.icon"/>
            </svg>
            {{ code.short }}
          </button>
        </div>
      </div>
      <button 
        :style="addButtonStyle" 
        @click="openCreateModal" 
        @mouseenter="addHovered = true" 
        @mouseleave="addHovered = false"
        class="fade-in"
        style="animation-delay: 0.2s"
      >
        <div :style="addButtonIconStyle">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
          </svg>
        </div>
        <span>Nouvel Utilisateur</span>
      </button>
    </header>

    <!-- Stats Overview -->
    <div :style="statsOverviewStyle" class="fade-in" style="animation-delay: 0.1s">
      <div :style="statCardStyle">
        <div :style="statIconWrapperStyle('#10b981')">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 00-3-3.87m-4-12a4 4 0 010 7.75"/>
          </svg>
        </div>
        <div>
          <div :style="statValueStyle">{{ users.length }}</div>
          <div :style="statLabelStyle">Total Utilisateurs</div>
        </div>
      </div>
      
      <div :style="statCardStyle">
        <div :style="statIconWrapperStyle('#3b82f6')">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <path d="M12 6v6l4 2"/>
          </svg>
        </div>
        <div>
          <div :style="statValueStyle">{{ countByStatut('actif') }}</div>
          <div :style="statLabelStyle">Actifs</div>
        </div>
      </div>
      
      <div :style="statCardStyle">
        <div :style="statIconWrapperStyle('#8b5cf6')">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
            <circle cx="8.5" cy="7" r="4"/>
            <polyline points="17 11 19 13 23 9"/>
          </svg>
        </div>
        <div>
          <div :style="statValueStyle">{{ countByRole(1) }}</div>
          <div :style="statLabelStyle">Administrateurs</div>
        </div>
      </div>
      
      <div :style="statCardStyle">
        <div :style="statIconWrapperStyle('#f59e0b')">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0110 0v4"/>
          </svg>
        </div>
        <div>
          <div :style="statValueStyle">{{ getUsersWithAccess() }}</div>
          <div :style="statLabelStyle">Avec acc&egrave;s</div>
        </div>
      </div>
    </div>

    <!-- Search Bar -->
    <div :style="searchBarContainerStyle" class="fade-in" style="animation-delay: 0.15s">
      <div :style="searchInputWrapperStyle">
        <svg :style="searchIconStyle" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"/>
          <path d="M21 21l-4.35-4.35"/>
        </svg>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Rechercher par nom, pr&eacute;nom ou email..."
          :style="searchInputStyle"
        />
        <button
          v-if="searchQuery"
          :style="searchClearBtnStyle"
          @click="searchQuery = ''"
        >
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 6L6 18M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" :style="loadingStyle">
      <div :style="spinnerStyle"></div>
      <p>Chargement des utilisateurs...</p>
    </div>

    <!-- Error State -->
    <div v-if="error" :style="errorStyle">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      <p>{{ error }}</p>
      <button :style="retryButtonStyle" @click="loadUsers">R&eacute;essayer</button>
    </div>

    <!-- Empty State -->
    <div v-if="!loading && !error && filteredUsers.length === 0" :style="emptyStateStyle">
      <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5">
        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
        <circle cx="9" cy="7" r="4"/>
        <path d="M23 21v-2a4 4 0 00-3-3.87"/>
        <path d="M16 3.13a4 4 0 010 7.75"/>
      </svg>
      <p :style="emptyStateTitleStyle">Aucun utilisateur trouv&eacute;</p>
      <p :style="emptyStateSubStyle">
        {{ searchQuery ? 'Essayez de modifier votre recherche' : 'Aucun utilisateur ne correspond au filtre s&eacute;lectionn&eacute;' }}
      </p>
    </div>

    <!-- Users Grid -->
    <div v-if="!loading && !error && filteredUsers.length > 0" :style="gridStyle">
      <div 
        v-for="(user, index) in filteredUsers" 
        :key="user.id"
        :style="getCardStyle(index)"
        @mouseenter="hoveredCard = index"
        @mouseleave="hoveredCard = null"
        class="card fade-in"
        :class="'delay-' + Math.min(index + 2, 5)"
      >
        <!-- Card Glow Effect -->
        <div :style="getCardGlowStyle(index)"></div>
        
        <div :style="cardHeaderStyle">
          <div :style="getAvatarStyle(user, index)">
            <img 
              v-if="user.photo" 
              :src="user.photo" 
              :alt="user.nom"
              :style="avatarImgStyle"
            />
            <span v-else>{{ getInitials(user) }}</span>
          </div>
          <div :style="cardActionsStyle">
            <button 
              :style="getEditBtnStyle(index)" 
              @click.stop="editUser(user)" 
              @mouseenter="hoveredAction = 'edit-' + index"
              @mouseleave="hoveredAction = null"
              title="Modifier"
            >
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
              </svg>
            </button>
            <button 
              :style="getDeleteBtnStyle(index)" 
              @click.stop="deleteUser(user.id)" 
              @mouseenter="hoveredAction = 'delete-' + index"
              @mouseleave="hoveredAction = null"
              title="Supprimer"
            >
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="3 6 5 6 21 6"/>
                <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
              </svg>
            </button>
          </div>
        </div>
        
        <h3 :style="cardTitleStyle">{{ user.nom }} {{ user.prenom }}</h3>
        <p :style="cardEmailStyle">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
            <polyline points="22,6 12,13 2,6"/>
          </svg>
          {{ user.email }}
        </p>

        <!-- Contact -->
        <p v-if="user.contact" :style="cardContactStyle">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/>
          </svg>
          {{ user.contact }}
        </p>
        
        <!-- Role Badge -->
        <div :style="getRoleBadgeStyle(user.id_role)">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 2l2.4 7.4h7.6l-6 4.6 2.3 7.4-6.3-4.6-6.3 4.6 2.3-7.4-6-4.6h7.6z"/>
          </svg>
          {{ getRoleLabel(user.id_role) }}
        </div>
        
        <!-- Access Codes Display -->
        <div :style="accessContainerStyle">
          <!-- ALL or ALL_TEST -->
          <div v-if="userHasAllAccess(user)" :style="allAccessBadgeStyle">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
              <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <span>{{ getUserAccessCodes(user).includes('ALL_TEST') && !getUserAccessCodes(user).includes('ALL') ? 'Test Utilisateur (ALL_TEST)' : 'Tous les droits (ALL)' }}</span>
          </div>

          <!-- Individual codes -->
          <div v-else-if="getUserAccessCodes(user).length > 0" :style="accessCodesWrapperStyle">
            <div 
              v-for="code in getUserAccessCodes(user)" 
              :key="code"
              :style="getAccessCodeBadgeStyle(code)"
            >
              <span :style="accessCodeLabelStyle">{{ code }}</span>
              <span :style="accessCodeDescStyle">{{ getAccessCodeLabel(code) }}</span>
            </div>
          </div>

          <!-- No access -->
          <div v-else :style="noAccessBadgeStyle">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
            </svg>
            <span>Aucun acc&egrave;s d&eacute;fini</span>
          </div>
        </div>

        <div :style="cardFooterStyle">
          <button 
            :style="getStatusToggleStyle(user.statut === 'actif', index)"
            @click.stop="toggleUserStatus(user.id)"
            @mouseenter="hoveredAction = 'status-' + index"
            @mouseleave="hoveredAction = null"
          >
            <span :style="statusDotStyle(user.statut === 'actif')"></span>
            {{ user.statut === 'actif' ? 'Actif' : 'Inactif' }}
          </button>
          
          <span :style="lastConnexionStyle" v-if="user.last_login">
            Vu {{ formatDate(user.last_login) }}
          </span>
          <span :style="dateCreateStyle" v-else-if="user.date_create">
            Cr&eacute;&eacute; {{ formatDate(user.date_create) }}
          </span>
        </div>
      </div>
    </div>

    <!-- Premium Modal -->
    <Transition name="modal">
      <div v-if="showModal" :style="modalOverlayStyle" @click.self="closeModal">
        <div :style="modalStyle" class="modal-content">
          <!-- Modal Header -->
          <div :style="modalHeaderStyle">
            <div>
              <div :style="modalBadgeStyle">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                  <circle cx="12" cy="7" r="4"/>
                </svg>
              </div>
              <h2 :style="modalTitleStyle">{{ editingUser ? 'Modifier l\'Utilisateur' : 'Nouvel Utilisateur' }}</h2>
              <p :style="modalSubtitleStyle">{{ editingUser ? 'Mettez &agrave; jour les informations et les acc&egrave;s' : 'Cr&eacute;ez un nouvel utilisateur et d&eacute;finissez ses acc&egrave;s' }}</p>
            </div>
            <button :style="closeButtonStyle" @click="closeModal">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 6L6 18M6 6l12 12"/>
              </svg>
            </button>
          </div>
          
          <!-- Form -->
          <form @submit.prevent="saveUser" :style="formStyle">
            <!-- Informations personnelles -->
            <div :style="sectionTitleStyle">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </svg>
              Informations personnelles
            </div>

            <div :style="formRowStyle">
              <div :style="formGroupStyle">
                <label :style="labelStyle">
                  Nom <span :style="requiredStyle">*</span>
                </label>
                <input 
                  v-model="formData.nom" 
                  type="text" 
                  :style="inputStyle"
                  placeholder="Dupont"
                  required
                />
              </div>
              
              <div :style="formGroupStyle">
                <label :style="labelStyle">
                  Pr&eacute;nom <span :style="requiredStyle">*</span>
                </label>
                <input 
                  v-model="formData.prenom" 
                  type="text" 
                  :style="inputStyle"
                  placeholder="Jean"
                  required
                />
              </div>
            </div>
            
            <div :style="formRowStyle">
              <div :style="formGroupStyle">
                <label :style="labelStyle">
                  Email <span :style="requiredStyle">*</span>
                </label>
                <input 
                  v-model="formData.email" 
                  type="email" 
                  :style="inputStyle"
                  placeholder="jean.dupont@example.com"
                  required
                />
              </div>
              
              <div :style="formGroupStyle">
                <label :style="labelStyle">Contact</label>
                <input 
                  v-model="formData.contact" 
                  type="tel" 
                  :style="inputStyle"
                  placeholder="+225 07 00 00 00 00"
                />
              </div>
            </div>

            <div :style="formRowStyle">
              <div :style="formGroupStyle">
                <label :style="labelStyle">
                  Mot de passe <span v-if="!editingUser" :style="requiredStyle">*</span>
                </label>
                <input 
                  v-model="formData.password" 
                  type="password" 
                  :style="inputStyle"
                  :placeholder="editingUser ? 'Laisser vide pour ne pas changer' : 'Minimum 6 caract&egrave;res'"
                  :required="!editingUser"
                />
              </div>
              
              <div :style="formGroupStyle">
                <label :style="labelStyle">
                  R&ocirc;le <span :style="requiredStyle">*</span>
                </label>
                <select v-model="formData.id_role" :style="inputStyle" required>
                  <option :value="3">Vendeur</option>
                  <option :value="2">Manager</option>
                  <option :value="1">Administrateur</option>
                </select>
              </div>
            </div>
            
            <!-- Acces et permissions -->
            <div :style="sectionTitleStyle">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0110 0v4"/>
              </svg>
              Droits d'acc&egrave;s (codes)
            </div>

            <div :style="formGroupStyle">
              <label :style="checkboxLabelStyle">
                <input
                  type="checkbox"
                  v-model="formData.permissions_all"
                  :style="checkboxStyle"
                />
                <span :style="checkboxTextStyle">
                  Tous les droits (ALL)
                </span>
              </label>
              <p style="font-size:12px;color:#64748b;margin-top:4px;">
                Cochez cette case pour donner acc&egrave;s &agrave; tous les modules.
              </p>
            </div>

            <div v-if="!formData.permissions_all" :style="checkboxGridStyle">
              <label 
                v-for="perm in PERMISSION_LIST"
                :key="perm.code"
                :style="getPermissionItemStyle(perm.code)"
              >
                <input
                  type="checkbox"
                  :value="perm.code"
                  v-model="formData.permissions_codes"
                  :style="checkboxStyle"
                />
                <div :style="permissionLabelWrapperStyle">
                  <span :style="permissionCodeStyle">{{ perm.code }}</span>
                  <span :style="permissionNameStyle">{{ perm.label }}</span>
                </div>
              </label>
            </div>
            
            <div :style="formActionsStyle">
              <button type="button" :style="cancelButtonStyle" @click="closeModal">
                Annuler
              </button>
              <button type="submit" :style="submitButtonStyle" :disabled="saving">
                <svg v-if="!saving" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                  <polyline points="17 21 17 13 7 13 7 21"/>
                  <polyline points="7 3 7 8 15 8"/>
                </svg>
                <div v-else :style="btnSpinnerStyle"></div>
                {{ saving ? 'Enregistrement...' : (editingUser ? 'Enregistrer' : 'Cr&eacute;er') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

  </SidebarLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { getUsers, addUser, updateUser, adeleteUser as apiDeleteUser, toggleUserStatus as apiToggleStatus } from '../services/api'
import SidebarLayout from './SidebarLayout.vue'

const API_BASE_URL = 'https://aliadjame.com/api'

// ─── Access codes mapping ───────────────────────────────────────────
const ACCESS_CODES_MAP = {
  GP:  'Gestion produit',
  GS:  'Gestion stock',
  GV:  'Gestion vente',
  GU:  'Gestion utilisateur',
  GC:  'Gestion comptabilit\u00e9',
  GT:  'Gestion entrep\u00f4t',
  GCM: 'Gestion commandes',
  GF:  'Gestion fournisseur',
  GCL: 'Gestion clients',
  GCS: 'Gestion caisse',
  ALL_TEST: 'Test Utilisateur',
  ALL: 'Tous les droits'
}

// Icons for filter buttons (SVG path data)
const ACCESS_ICONS = {
  GP:  'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
  GS:  'M20 12V8H6a2 2 0 01-2-2c0-1.1.9-2 2-2h12v4M4 6v12a2 2 0 002 2h14',
  GV:  'M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18M16 10a4 4 0 01-8 0',
  GU:  'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 7a4 4 0 100-8 4 4 0 000 8',
  GC:  'M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6',
  GT:  'M22 8.35V20a2 2 0 01-2 2H4a2 2 0 01-2-2V8.35A2 2 0 013.26 6.5l8-3.2a2 2 0 011.48 0l8 3.2A2 2 0 0122 8.35z',
  GCM: 'M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8zM14 2v6h6M16 13H8M16 17H8M10 9H8',
  GF:  'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
  GCL: 'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 7a4 4 0 100-8 4 4 0 000 8M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75',
  GCS: 'M2 7h20l-1.5 9a2 2 0 01-2 2H5.5a2 2 0 01-2-2L2 7zM16 3l2 4M8 3L6 7',
}

// List for filter buttons (excluding ALL and ALL_TEST)
const ACCESS_CODES_LIST = [
  { code: 'GP',  short: 'Produits',     icon: ACCESS_ICONS.GP },
  { code: 'GS',  short: 'Stock',        icon: ACCESS_ICONS.GS },
  { code: 'GV',  short: 'Ventes',       icon: ACCESS_ICONS.GV },
  { code: 'GU',  short: 'Utilisateurs', icon: ACCESS_ICONS.GU },
  { code: 'GC',  short: 'Comptabilit\u00e9', icon: ACCESS_ICONS.GC },
  { code: 'GT',  short: 'Entrep\u00f4ts',    icon: ACCESS_ICONS.GT },
  { code: 'GCM', short: 'Commandes',    icon: ACCESS_ICONS.GCM },
  { code: 'GF',  short: 'Fournisseurs', icon: ACCESS_ICONS.GF },
  { code: 'GCL', short: 'Clients',      icon: ACCESS_ICONS.GCL },
  { code: 'GCS', short: 'Caisse',       icon: ACCESS_ICONS.GCS },
]

// Permission list for the modal form
const PERMISSION_LIST = [
  { code: 'GP',  label: 'Gestion Produits' },
  { code: 'GS',  label: 'Gestion Stock' },
  { code: 'GV',  label: 'Gestion Ventes' },
  { code: 'GU',  label: 'Gestion Utilisateurs' },
  { code: 'GC',  label: 'Comptabilit\u00e9' },
  { code: 'GT',  label: 'Gestion Entrep\u00f4ts' },
  { code: 'GCM', label: 'Gestion Commandes' },
  { code: 'GF',  label: 'Gestion Fournisseurs' },
  { code: 'GCL', label: 'Gestion Clients' },
  { code: 'GCS', label: 'Gestion Caisse' },
]

// Access code badge colors
const ACCESS_CODE_COLORS = {
  GP:  { bg: '#fef3c7', text: '#92400e', border: '#fde68a' },
  GS:  { bg: '#dcfce7', text: '#166534', border: '#bbf7d0' },
  GV:  { bg: '#dbeafe', text: '#1e40af', border: '#bfdbfe' },
  GU:  { bg: '#f3e8ff', text: '#6b21a8', border: '#e9d5ff' },
  GC:  { bg: '#fce7f3', text: '#9d174d', border: '#fbcfe8' },
  GT:  { bg: '#e0e7ff', text: '#3730a3', border: '#c7d2fe' },
  GCM: { bg: '#fed7aa', text: '#9a3412', border: '#fdba74' },
  GF:  { bg: '#ccfbf1', text: '#115e59', border: '#99f6e4' },
  GCL: { bg: '#ede9fe', text: '#5b21b6', border: '#ddd6fe' },
  GCS: { bg: '#fef9c3', text: '#854d0e', border: '#fef08a' },
}

// ─── State ──────────────────────────────────────────────────────────
const hoveredCard = ref(null)
const hoveredAction = ref(null)
const addHovered = ref(false)
const showModal = ref(false)
const editingUser = ref(null)
const loading = ref(false)
const saving = ref(false)
const error = ref(null)
const activeFilter = ref('all')
const searchQuery = ref('')

const users = ref([])

const formData = reactive({
  nom: '',
  prenom: '',
  email: '',
  contact: '',
  password: '',
  id_role: 3,
  statut_actif: true,
  permissions_all: false,
  permissions_codes: []
})

// ─── Helpers ────────────────────────────────────────────────────────
const getUserAccessCodes = (user) => {
  if (!user.access) return []
  try {
    const parsed = typeof user.access === 'string' ? JSON.parse(user.access) : user.access
    return Array.isArray(parsed) ? parsed : []
  } catch (e) {
    return []
  }
}

const userHasAllAccess = (user) => {
  const codes = getUserAccessCodes(user)
  return codes.includes('ALL') || codes.includes('ALL_TEST')
}

const userHasCode = (user, code) => {
  const codes = getUserAccessCodes(user)
  return codes.includes('ALL') || codes.includes(code)
}

const getAccessCodeLabel = (code) => ACCESS_CODES_MAP[code] || code

// ─── Computed ───────────────────────────────────────────────────────
const filteredUsers = computed(() => {
  let result = users.value

  // Filter by access code
  if (activeFilter.value !== 'all') {
    result = result.filter(user => userHasCode(user, activeFilter.value))
  }

  // Filter by search query
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.trim().toLowerCase()
    result = result.filter(user => {
      const fullName = `${user.nom} ${user.prenom}`.toLowerCase()
      const email = (user.email || '').toLowerCase()
      const contact = (user.contact || '').toLowerCase()
      return fullName.includes(q) || email.includes(q) || contact.includes(q)
    })
  }

  return result
})

const countByStatut = (statut) => {
  return users.value.filter(u => u.statut === statut).length
}

const countByRole = (roleId) => {
  return users.value.filter(u => u.id_role === roleId).length
}

const getUsersWithAccess = () => {
  return users.value.filter(user => getUserAccessCodes(user).length > 0).length
}

// ─── Methods ────────────────────────────────────────────────────────
const getInitials = (user) => {
  return `${(user.nom || '').charAt(0)}${(user.prenom || '').charAt(0)}`.toUpperCase()
}

const getRoleLabel = (id_role) => {
  const labels = {
    1: 'Administrateur',
    2: 'Manager',
    3: 'Vendeur'
  }
  return labels[id_role] || 'Inconnu'
}

const formatDate = (date) => {
  if (!date) return ''
  const d = new Date(date)
  const now = new Date()
  const diff = Math.floor((now - d) / 1000)
  
  if (diff < 60) return '\u00e0 l\'instant'
  if (diff < 3600) return `il y a ${Math.floor(diff / 60)}m`
  if (diff < 86400) return `il y a ${Math.floor(diff / 3600)}h`
  if (diff < 604800) return `il y a ${Math.floor(diff / 86400)}j`
  return d.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' })
}

// ─── API Functions ──────────────────────────────────────────────────
const loadUsers = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await getUsers()        // ← Axios + token auto
    users.value = response.data.data
  } catch (err) {
    error.value = err.response?.data?.error || 'Impossible de se connecter'
  } finally {
    loading.value = false
  }
}

// ─── API Functions ──────────────────────────────────────────────────
const saveUser = async () => {
  saving.value = true
  try {
    // Construire le tableau des permissions
    let accessCodes = formData.permissions_all ? ['ALL'] : [...formData.permissions_codes];

    const payload = {
      nom: formData.nom,
      prenom: formData.prenom,
      email: formData.email,
      contact: formData.contact,
      password: formData.password,
      id_role: formData.id_role,
      statut: formData.statut_actif ? 'actif' : 'inactif',
      access: JSON.stringify(accessCodes)
    };

    let response;
    if (editingUser.value) {
      // Ajoute l'ID pour la modification
      payload.id = editingUser.value.id;
      response = await updateUser(payload);   // ← Utilise updateUser de api.js
    } else {
      response = await addUser(payload);      // ← Utilise addUser de api.js
    }

    // Axios renvoie directement la data
    if (response.data.success) {
      await loadUsers();
      closeModal();
    } else {
      alert(response.data.error || "Erreur lors de l'enregistrement");
    }

  } catch (err) {
    console.error("Erreur:", err);
    alert("Erreur de connexion au serveur");
  } finally {
    saving.value = false;
  }
};

const deleteUser = async (id) => {
  if (!confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) return;

  try {
    const response = await adeleteUser(id);  // ← Utilise deleteUser de api.js
    // Axios renvoie la réponse sous response.data
    if (response.data.success) {
      await loadUsers();
    } else {
      alert(response.data.error || 'Erreur lors de la suppression');
    }
  } catch (err) {
    console.error('Erreur:', err);
    alert('Erreur de connexion au serveur');
  }
};

const toggleUserStatus = async (id) => {
  try {
    const response = await fetch(`${API_BASE_URL}/api_users.php?action=toggle-status`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    })
    
    const data = await response.json()
    
    if (data.success) {
      await loadUsers()
    } else {
      alert(data.error || 'Erreur lors du changement de statut')
    }
  } catch (err) {
    alert('Erreur de connexion au serveur')
    console.error('Erreur:', err)
  }
}

const openCreateModal = () => {
  editingUser.value = null
  resetForm()
  showModal.value = true
}

const editUser = (user) => {
  editingUser.value = user
  formData.nom = user.nom
  formData.prenom = user.prenom
  formData.email = user.email
  formData.contact = user.contact || ''
  formData.password = ''
  formData.id_role = user.id_role
  formData.statut_actif = user.statut === 'actif'

  // Parse access codes
  formData.permissions_all = false
  formData.permissions_codes = []
  const codes = getUserAccessCodes(user)
  if (codes.includes('ALL') || codes.includes('ALL_TEST')) {
    formData.permissions_all = true
  }
  formData.permissions_codes = codes.filter(c => c !== 'ALL' && c !== 'ALL_TEST')

  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingUser.value = null
  resetForm()
}

const resetForm = () => {
  formData.nom = ''
  formData.prenom = ''
  formData.email = ''
  formData.contact = ''
  formData.password = ''
  formData.id_role = 3
  formData.statut_actif = true
  formData.permissions_all = false
  formData.permissions_codes = []
}

// Load data on mount
onMounted(() => {
  loadUsers()
})

// ─── Styles ─────────────────────────────────────────────────────────
const headerStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'flex-start',
  marginBottom: '32px',
  flexWrap: 'wrap',
  gap: '20px'
}

const headerContentStyle = {
  display: 'flex',
  flexDirection: 'column',
  gap: '8px'
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

const subtitleIconStyle = {
  width: '16px',
  height: '16px'
}

const filtersContainerStyle = {
  display: 'flex',
  flexWrap: 'wrap',
  gap: '8px',
  marginTop: '12px'
}

const getFilterButtonStyle = (filter) => ({
  display: 'flex',
  alignItems: 'center',
  gap: '6px',
  padding: '8px 14px',
  borderRadius: '10px',
  border: activeFilter.value === filter ? 'none' : '1px solid #e2e8f0',
  background: activeFilter.value === filter 
    ? 'linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%)'
    : 'white',
  color: activeFilter.value === filter ? 'white' : '#64748b',
  fontSize: '12px',
  fontWeight: '600',
  cursor: 'pointer',
  transition: 'all 0.2s',
  boxShadow: activeFilter.value === filter 
    ? '0 4px 12px rgba(139, 92, 246, 0.25)'
    : 'none'
})

const addButtonStyle = computed(() => ({
  display: 'flex',
  alignItems: 'center',
  gap: '10px',
  padding: '14px 24px',
  background: addHovered.value 
    ? 'linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%)'
    : 'linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%)',
  color: 'white',
  border: 'none',
  borderRadius: '12px',
  cursor: 'pointer',
  fontSize: '14px',
  fontWeight: '600',
  transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
  boxShadow: addHovered.value 
    ? '0 12px 28px rgba(139, 92, 246, 0.35)'
    : '0 4px 12px rgba(139, 92, 246, 0.25)',
  transform: addHovered.value ? 'translateY(-2px)' : 'translateY(0)',
  letterSpacing: '0.01em'
}))

const addButtonIconStyle = {
  width: '32px',
  height: '32px',
  background: 'rgba(255, 255, 255, 0.2)',
  borderRadius: '8px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center'
}

const statsOverviewStyle = {
  display: 'grid',
  gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))',
  gap: '16px',
  marginBottom: '24px'
}

const statCardStyle = {
  background: 'white',
  borderRadius: '16px',
  padding: '20px',
  display: 'flex',
  alignItems: 'center',
  gap: '16px',
  boxShadow: '0 1px 3px rgba(0, 0, 0, 0.05)',
  border: '1px solid #f1f5f9'
}

const statIconWrapperStyle = (color) => ({
  width: '48px',
  height: '48px',
  background: `${color}15`,
  borderRadius: '12px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  color: color,
  flexShrink: 0
})

const statValueStyle = {
  fontSize: '24px',
  fontWeight: '700',
  color: '#0f172a',
  lineHeight: '1.2',
  letterSpacing: '-0.01em'
}

const statLabelStyle = {
  fontSize: '13px',
  color: '#64748b',
  fontWeight: '500',
  marginTop: '2px'
}

// Search bar styles
const searchBarContainerStyle = {
  marginBottom: '24px'
}

const searchInputWrapperStyle = {
  position: 'relative',
  display: 'flex',
  alignItems: 'center'
}

const searchIconStyle = {
  position: 'absolute',
  left: '16px',
  color: '#94a3b8',
  pointerEvents: 'none'
}

const searchInputStyle = {
  width: '100%',
  padding: '14px 44px 14px 48px',
  border: '1px solid #e2e8f0',
  borderRadius: '14px',
  fontSize: '14px',
  fontWeight: '500',
  color: '#1e293b',
  background: 'white',
  boxSizing: 'border-box',
  transition: 'all 0.2s',
  outline: 'none'
}

const searchClearBtnStyle = {
  position: 'absolute',
  right: '12px',
  width: '28px',
  height: '28px',
  borderRadius: '8px',
  border: 'none',
  background: '#f1f5f9',
  color: '#64748b',
  cursor: 'pointer',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  transition: 'all 0.2s'
}

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
  borderTop: '4px solid #8b5cf6',
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
  background: '#8b5cf6',
  color: 'white',
  border: 'none',
  borderRadius: '8px',
  cursor: 'pointer',
  fontSize: '14px',
  fontWeight: '600'
}

const emptyStateStyle = {
  textAlign: 'center',
  padding: '80px 20px',
  color: '#94a3b8'
}

const emptyStateTitleStyle = {
  fontSize: '18px',
  fontWeight: '600',
  color: '#475569',
  margin: '16px 0 8px'
}

const emptyStateSubStyle = {
  fontSize: '14px',
  color: '#94a3b8',
  margin: '0'
}

const gridStyle = {
  display: 'grid',
  gridTemplateColumns: 'repeat(auto-fill, minmax(340px, 1fr))',
  gap: '20px'
}

const getCardStyle = (index) => ({
  backgroundColor: 'white',
  borderRadius: '20px',
  padding: '28px',
  boxShadow: hoveredCard.value === index 
    ? '0 20px 40px rgba(0, 0, 0, 0.1)' 
    : '0 2px 8px rgba(0, 0, 0, 0.06)',
  cursor: 'pointer',
  transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)',
  transform: hoveredCard.value === index ? 'translateY(-6px)' : 'translateY(0)',
  border: hoveredCard.value === index ? '1px solid #e2e8f0' : '1px solid #f1f5f9'
})

const getCardGlowStyle = (index) => ({
  position: 'absolute',
  inset: '-2px',
  background: 'linear-gradient(135deg, rgba(139, 92, 246, 0.2), transparent)',
  borderRadius: '20px',
  opacity: hoveredCard.value === index ? 1 : 0,
  transition: 'opacity 0.4s',
  filter: 'blur(12px)',
  zIndex: -1
})

const cardHeaderStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'flex-start',
  marginBottom: '20px'
}

const getAvatarStyle = (user, index) => {
  const colors = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899']
  const color = colors[user.id % colors.length]
  
  return {
    width: '56px',
    height: '56px',
    borderRadius: '16px',
    background: hoveredCard.value === index 
      ? `linear-gradient(135deg, ${color} 0%, ${adjustColor(color, -15)} 100%)`
      : `${color}12`,
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    fontSize: '18px',
    fontWeight: '700',
    color: hoveredCard.value === index ? 'white' : color,
    transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)',
    transform: hoveredCard.value === index ? 'rotate(-5deg) scale(1.05)' : 'rotate(0deg) scale(1)',
    boxShadow: hoveredCard.value === index ? `0 8px 20px ${color}40` : 'none',
    overflow: 'hidden',
    flexShrink: 0
  }
}

const avatarImgStyle = {
  width: '100%',
  height: '100%',
  objectFit: 'cover',
  borderRadius: '16px'
}

const cardActionsStyle = {
  display: 'flex',
  gap: '8px'
}

const actionBtnStyle = {
  width: '36px',
  height: '36px',
  borderRadius: '10px',
  border: '1px solid #e2e8f0',
  backgroundColor: 'white',
  cursor: 'pointer',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  color: '#64748b',
  transition: 'all 0.2s'
}

const getEditBtnStyle = (index) => ({
  ...actionBtnStyle,
  color: hoveredAction.value === 'edit-' + index ? '#3b82f6' : '#64748b',
  backgroundColor: hoveredAction.value === 'edit-' + index ? '#eff6ff' : 'white',
  borderColor: hoveredAction.value === 'edit-' + index ? '#bfdbfe' : '#e2e8f0'
})

const getDeleteBtnStyle = (index) => ({
  ...actionBtnStyle,
  color: hoveredAction.value === 'delete-' + index ? '#ef4444' : '#64748b',
  backgroundColor: hoveredAction.value === 'delete-' + index ? '#fef2f2' : 'white',
  borderColor: hoveredAction.value === 'delete-' + index ? '#fecaca' : '#e2e8f0'
})

const cardTitleStyle = {
  fontSize: '20px',
  fontWeight: '700',
  color: '#0f172a',
  margin: '0 0 8px 0',
  letterSpacing: '-0.01em'
}

const cardEmailStyle = {
  fontSize: '14px',
  color: '#64748b',
  margin: '0 0 6px 0',
  display: 'flex',
  alignItems: 'center',
  gap: '6px',
  fontWeight: '500'
}

const cardContactStyle = {
  fontSize: '13px',
  color: '#94a3b8',
  margin: '0 0 16px 0',
  display: 'flex',
  alignItems: 'center',
  gap: '6px',
  fontWeight: '500'
}

const getRoleBadgeStyle = (id_role) => {
  const colors = {
    1: { bg: '#fef3c7', text: '#92400e', border: '#fde68a' },
    2: { bg: '#dbeafe', text: '#1e40af', border: '#bfdbfe' },
    3: { bg: '#f3e8ff', text: '#6b21a8', border: '#e9d5ff' }
  }
  const color = colors[id_role] || colors[3]
  
  return {
    display: 'inline-flex',
    alignItems: 'center',
    gap: '6px',
    padding: '6px 14px',
    borderRadius: '20px',
    fontSize: '13px',
    fontWeight: '600',
    backgroundColor: color.bg,
    color: color.text,
    border: `1px solid ${color.border}`,
    marginBottom: '16px',
    letterSpacing: '0.01em'
  }
}

const accessContainerStyle = {
  display: 'flex',
  flexDirection: 'column',
  gap: '8px',
  padding: '16px 0',
  borderTop: '1px solid #f1f5f9',
  borderBottom: '1px solid #f1f5f9',
  marginBottom: '16px'
}

const allAccessBadgeStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '8px',
  padding: '10px 14px',
  background: 'linear-gradient(135deg, #dcfce7, #d1fae5)',
  border: '1px solid #86efac',
  borderRadius: '10px',
  fontSize: '13px',
  color: '#166534',
  fontWeight: '600'
}

const accessCodesWrapperStyle = {
  display: 'flex',
  flexWrap: 'wrap',
  gap: '6px'
}

const getAccessCodeBadgeStyle = (code) => {
  const color = ACCESS_CODE_COLORS[code] || { bg: '#f1f5f9', text: '#475569', border: '#e2e8f0' }
  return {
    display: 'flex',
    alignItems: 'center',
    gap: '6px',
    padding: '4px 10px',
    borderRadius: '8px',
    fontSize: '11px',
    fontWeight: '600',
    backgroundColor: color.bg,
    color: color.text,
    border: `1px solid ${color.border}`
  }
}

const accessCodeLabelStyle = {
  fontWeight: '700',
  fontSize: '11px'
}

const accessCodeDescStyle = {
  fontWeight: '500',
  fontSize: '10px',
  opacity: '0.8'
}

const noAccessBadgeStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '8px',
  padding: '8px 12px',
  background: '#fef2f2',
  border: '1px solid #fecaca',
  borderRadius: '8px',
  fontSize: '12px',
  color: '#991b1b',
  fontWeight: '600',
  width: '100%',
  justifyContent: 'center'
}

const cardFooterStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center'
}

const getStatusToggleStyle = (isActif, index) => ({
  display: 'inline-flex',
  alignItems: 'center',
  gap: '6px',
  padding: '6px 14px',
  borderRadius: '20px',
  fontSize: '13px',
  fontWeight: '600',
  backgroundColor: isActif ? '#dcfce7' : '#fee2e2',
  color: isActif ? '#166534' : '#991b1b',
  border: 'none',
  cursor: 'pointer',
  transition: 'all 0.2s',
  letterSpacing: '0.01em'
})

const statusDotStyle = (isActif) => ({
  width: '6px',
  height: '6px',
  borderRadius: '50%',
  backgroundColor: isActif ? '#22c55e' : '#ef4444'
})

const lastConnexionStyle = {
  fontSize: '12px',
  color: '#94a3b8',
  fontWeight: '500'
}

const dateCreateStyle = {
  fontSize: '12px',
  color: '#cbd5e1',
  fontWeight: '500',
  fontStyle: 'italic'
}

// Modal styles
const modalOverlayStyle = {
  position: 'fixed',
  top: 0,
  left: 0,
  right: 0,
  bottom: 0,
  backgroundColor: 'rgba(15, 23, 42, 0.7)',
  backdropFilter: 'blur(8px)',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  zIndex: 1000,
  padding: '20px',
  overflowY: 'auto'
}

const modalStyle = {
  backgroundColor: 'white',
  borderRadius: '24px',
  width: '100%',
  maxWidth: '700px',
  maxHeight: '90vh',
  overflow: 'auto',
  boxShadow: '0 25px 50px -12px rgba(0, 0, 0, 0.25)'
}

const modalHeaderStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'flex-start',
  padding: '32px 32px 24px',
  borderBottom: '1px solid #f1f5f9'
}

const modalBadgeStyle = {
  width: '40px',
  height: '40px',
  background: 'linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%)',
  borderRadius: '12px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  color: 'white',
  marginBottom: '16px'
}

const modalTitleStyle = {
  fontSize: '24px',
  fontWeight: '700',
  color: '#0f172a',
  margin: '0 0 6px 0',
  letterSpacing: '-0.01em'
}

const modalSubtitleStyle = {
  fontSize: '14px',
  color: '#64748b',
  margin: 0,
  fontWeight: '500'
}

const closeButtonStyle = {
  background: 'none',
  border: 'none',
  cursor: 'pointer',
  color: '#94a3b8',
  padding: '8px',
  borderRadius: '8px',
  transition: 'all 0.2s'
}

const formStyle = {
  padding: '24px 32px 32px'
}

const sectionTitleStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '8px',
  fontSize: '16px',
  fontWeight: '700',
  color: '#0f172a',
  marginBottom: '20px',
  marginTop: '24px',
  paddingBottom: '12px',
  borderBottom: '2px solid #f1f5f9'
}

const formGroupStyle = {
  marginBottom: '20px',
  flex: 1
}

const formRowStyle = {
  display: 'flex',
  gap: '16px'
}

const labelStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '6px',
  fontSize: '14px',
  fontWeight: '600',
  color: '#334155',
  marginBottom: '8px',
  letterSpacing: '0.01em'
}

const requiredStyle = {
  color: '#ef4444',
  marginLeft: '2px'
}

const inputStyle = {
  width: '100%',
  padding: '12px 16px',
  border: '1px solid #e2e8f0',
  borderRadius: '12px',
  fontSize: '14px',
  boxSizing: 'border-box',
  transition: 'all 0.2s',
  fontWeight: '500',
  color: '#1e293b'
}

const checkboxLabelStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '10px',
  fontSize: '14px',
  color: '#334155',
  cursor: 'pointer',
  fontWeight: '500'
}

const checkboxStyle = {
  width: '20px',
  height: '20px',
  cursor: 'pointer',
  accentColor: '#8b5cf6'
}

const checkboxTextStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '8px',
  fontSize: '14px',
  fontWeight: '600',
  color: '#334155'
}

const checkboxGridStyle = {
  display: 'grid',
  gridTemplateColumns: 'repeat(auto-fill, minmax(220px, 1fr))',
  gap: '10px',
  padding: '16px',
  background: '#f8fafc',
  borderRadius: '12px'
}

const getPermissionItemStyle = (code) => {
  const color = ACCESS_CODE_COLORS[code] || { bg: '#f1f5f9', text: '#475569', border: '#e2e8f0' }
  const isSelected = formData.permissions_codes.includes(code)
  return {
    display: 'flex',
    alignItems: 'center',
    gap: '10px',
    padding: '10px 12px',
    borderRadius: '10px',
    cursor: 'pointer',
    fontWeight: '500',
    fontSize: '13px',
    backgroundColor: isSelected ? color.bg : 'white',
    border: `1px solid ${isSelected ? color.border : '#e2e8f0'}`,
    color: isSelected ? color.text : '#475569',
    transition: 'all 0.2s'
  }
}

const permissionLabelWrapperStyle = {
  display: 'flex',
  flexDirection: 'column',
  gap: '2px'
}

const permissionCodeStyle = {
  fontSize: '12px',
  fontWeight: '700',
  letterSpacing: '0.03em'
}

const permissionNameStyle = {
  fontSize: '11px',
  fontWeight: '500',
  opacity: '0.75'
}

const formActionsStyle = {
  display: 'flex',
  gap: '12px',
  marginTop: '28px',
  paddingTop: '24px',
  borderTop: '1px solid #f1f5f9'
}

const cancelButtonStyle = {
  flex: 1,
  padding: '14px',
  backgroundColor: '#f8fafc',
  border: '1px solid #e2e8f0',
  borderRadius: '12px',
  fontSize: '14px',
  fontWeight: '600',
  color: '#64748b',
  cursor: 'pointer',
  transition: 'all 0.2s',
  letterSpacing: '0.01em'
}

const submitButtonStyle = {
  flex: 1,
  padding: '14px',
  background: 'linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%)',
  border: 'none',
  borderRadius: '12px',
  fontSize: '14px',
  fontWeight: '600',
  color: 'white',
  cursor: 'pointer',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  gap: '8px',
  transition: 'all 0.2s',
  boxShadow: '0 4px 12px rgba(139, 92, 246, 0.25)',
  letterSpacing: '0.01em'
}

const btnSpinnerStyle = {
  width: '18px',
  height: '18px',
  border: '2px solid rgba(255,255,255,0.3)',
  borderTop: '2px solid white',
  borderRadius: '50%',
  animation: 'spin 0.8s linear infinite'
}

// Helper
const adjustColor = (color, amount) => {
  const num = parseInt(color.replace('#', ''), 16)
  const r = Math.max(0, Math.min(255, (num >> 16) + amount))
  const g = Math.max(0, Math.min(255, ((num >> 8) & 0x00FF) + amount))
  const b = Math.max(0, Math.min(255, (num & 0x0000FF) + amount))
  return '#' + ((r << 16) | (g << 8) | b).toString(16).padStart(6, '0')
}
</script>

<style scoped>
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

.delay-2 { animation-delay: 0.2s; }
.delay-3 { animation-delay: 0.3s; }
.delay-4 { animation-delay: 0.4s; }
.delay-5 { animation-delay: 0.5s; }

.card {
  position: relative;
  overflow: hidden;
}

.card::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.4) 50%, transparent 70%);
  transform: translateX(-100%) rotate(45deg);
  transition: transform 0.6s;
}

.card:hover::before {
  transform: translateX(100%) rotate(45deg);
}

.modal-enter-active, .modal-leave-active {
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.modal-enter-from, .modal-leave-to {
  opacity: 0;
}

.modal-enter-from .modal-content,
.modal-leave-to .modal-content {
  transform: scale(0.95) translateY(20px);
}

.modal-content {
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
</style>
