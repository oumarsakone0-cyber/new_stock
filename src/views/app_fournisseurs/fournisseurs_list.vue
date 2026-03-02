<template>
  <SidebarLayout currentPage="fournisseurs">
    <!-- Loading -->
    <div v-if="loading" :style="loadingContainerStyle">
      <div :style="spinnerStyle"></div>
      <p :style="loadingTextStyle">Chargement...</p>
    </div>

    <!-- Main Content -->
    <div v-if="!loading">
      <!-- Header -->
      <header :style="headerStyle" class="fade-in">
        <div>
          <h1 :style="titleStyle">Gestion des Fournisseurs</h1>
          <p :style="subtitleStyle">Gérez vos fournisseurs, produits et commandes</p>
        </div>
        <button 
          :style="newButtonStyle" 
          @click="showFournisseurModal = true"
          @mouseenter="newHovered = true"
          @mouseleave="newHovered = false"
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M12 5v14M5 12h14"/>
          </svg>
          Nouveau fournisseur
        </button>
      </header>

      <!-- Stats Cards -->
      <div :style="statsGridStyle" class="fade-in">
        <div :style="statsCardStyle">
          <div :style="statsIconStyle('#10b981')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
              <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
          </div>
          <div>
            <p :style="statsLabelStyle">Fournisseurs actifs</p>
            <p :style="statsValueStyle">{{ statsGlobal.fournisseurs_actifs || 0 }}</p>
          </div>
        </div>

        <div :style="statsCardStyle">
          <div :style="statsIconStyle('#3b82f6')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
              <line x1="1" y1="10" x2="23" y2="10"/>
            </svg>
          </div>
          <div>
            <p :style="statsLabelStyle">Produits catalogue</p>
            <p :style="statsValueStyle">{{ statsGlobal.total_produits || 0 }}</p>
          </div>
        </div>

        <div :style="statsCardStyle">
          <div :style="statsIconStyle('#f59e0b')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <circle cx="12" cy="12" r="10"/>
              <polyline points="12 6 12 12 16 14"/>
            </svg>
          </div>
          <div>
            <p :style="statsLabelStyle">Commandes en cours</p>
            <p :style="statsValueStyle">{{ statsGlobal.commandes_en_cours || 0 }}</p>
          </div>
        </div>

        <div :style="statsCardStyle">
          <div :style="statsIconStyle('#ef4444')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <line x1="12" y1="1" x2="12" y2="23"/>
              <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
            </svg>
          </div>
          <div>
            <p :style="statsLabelStyle">Dettes totales</p>
            <p :style="statsValueStyle">{{ formatMoney(statsGlobal.total_dettes || 0) }}</p>
          </div>
        </div>
      </div>

      <!-- Search and Filters -->
      <div :style="searchContainerStyle" class="fade-in">
        <div :style="searchInputWrapperStyle">
          <svg :style="searchIconStyle" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/>
            <path d="M21 21l-4.35-4.35"/>
          </svg>
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Rechercher un fournisseur..."
            :style="searchInputStyle"
          />
        </div>

        <select v-model="filterType" :style="filterSelectStyle">
          <option value="">Tous les types</option>
          <option value="Grossiste">Grossiste</option>
          <option value="Producteur">Producteur</option>
          <option value="Importateur">Importateur</option>
          <option value="Distributeur">Distributeur</option>
        </select>

        <select v-model="filterStatut" :style="filterSelectStyle">
          <option value="">Tous les statuts</option>
          <option value="Actif">Actif</option>
          <option value="Inactif">Inactif</option>
          <option value="Suspendu">Suspendu</option>
        </select>

        <button :style="dashboardButtonStyle" @click="showDashboard = true">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="7" height="7"/>
            <rect x="14" y="3" width="7" height="7"/>
            <rect x="14" y="14" width="7" height="7"/>
            <rect x="3" y="14" width="7" height="7"/>
          </svg>
          Dashboard
        </button>
      </div>

      <!-- Fournisseurs Table -->
      <div :style="tableContainerStyle" class="fade-in">
        <div :style="tableWrapperStyle">
          <table :style="tableStyle">
            <thead>
              <tr>
                <th :style="thStyle">Fournisseur</th>
                <th :style="thStyle">Contact</th>
                <th :style="thStyle">Adresse</th>
                <th :style="thStyle">Type</th>
                <th :style="thStyle">Produits</th>
                <th :style="thStyle">Commandes</th>
                <th :style="thStyle">Dette</th>
                <th :style="thStyle">Statut</th>
                <th :style="thStyle">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="fournisseur in filteredFournisseurs" :key="fournisseur.id" :style="trStyle">
                <td :style="tdStyle">
                  <p :style="nameStyle">{{ fournisseur.nom }}</p>
                  <p v-if="fournisseur.nom_entreprise" :style="entrepriseStyle">{{ fournisseur.nom_entreprise }}</p>
                </td>
                <td :style="tdStyle">
                  <p :style="phoneStyle">{{ fournisseur.telephone || fournisseur.contact || 'N/A' }}</p>
                  <p v-if="fournisseur.email" :style="emailStyle">{{ fournisseur.email }}</p>
                </td>
                <td :style="tdStyle">
                  <p :style="adresseStyle">{{ fournisseur.adresse || fournisseur.quartier || 'N/A' }}</p>
                  <p :style="villeStyle">{{ fournisseur.ville || '' }}</p>
                </td>
                <td :style="tdStyle">
                  <span :style="typeBadgeStyle(fournisseur.type_fournisseur || fournisseur.categorie_fournisseur)">
                    {{
                      fournisseur.categorie_fournisseur && fournisseur.type_fournisseur
                        ? fournisseur.categorie_fournisseur + ' (' + fournisseur.type_fournisseur + ')'
                        : (fournisseur.categorie_fournisseur || fournisseur.type_fournisseur || 'N/A')
                    }}
                  </span>
                </td>
                <td :style="tdStyle">
                  <span :style="countStyle">{{ fournisseur.nombre_produits || 0 }}</span>
                </td>
                <td :style="tdStyle">
                  <span :style="countStyle">{{ fournisseur.nombre_commandes || 0 }}</span>
                </td>
                <td :style="tdStyle">
                  <span :style="detteStyle(fournisseur.total_dette)">
                    {{ formatMoney(fournisseur.total_dette || 0) }}
                  </span>
                </td>
                <td :style="tdStyle">
                  <span :style="getStatusBadgeStyle(fournisseur.statut)">
                    {{ fournisseur.statut }}
                  </span>
                </td>
                <td :style="tdStyle">
                  <div :style="actionsStyle">
                    <button 
                      :style="actionButtonStyle('#3b82f6')" 
                      @click="goToDetail(fournisseur.id)"
                      title="Voir détails"
                    >
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                      </svg>
                    </button>
                    <button 
                      :style="actionButtonStyle('#10b981')" 
                      @click="editFournisseur(fournisseur)"
                      title="Modifier"
                    >
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="filteredFournisseurs.length === 0" :style="emptyStateStyle">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2">
            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
          </svg>
          <p :style="emptyTextStyle">Aucun fournisseur trouvé</p>
        </div>
      </div>

      <!-- Modal Fournisseur -->
      <Transition name="modal">
        <div v-if="showFournisseurModal" :style="modalOverlayStyle" @click.self="closeFournisseurModal">
          <div :style="modalStyle" class="modal-content">
            <div :style="modalHeaderStyle">
              <div>
                <h2 :style="modalTitleStyle">{{ editingFournisseur ? 'Modifier' : 'Nouveau' }} fournisseur</h2>
                <p :style="modalSubtitleStyle">Informations du fournisseur</p>
              </div>
              <button :style="closeButtonStyle" @click="closeFournisseurModal">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
              </button>
            </div>
            
            <div :style="modalContentStyle">
              <div :style="formGridStyle">
                <div :style="formGroupStyle">
                  <label :style="labelStyle">Nom *</label>
                  <input v-model="formFournisseur.nom" type="text" :style="inputStyle" required />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Nom entreprise</label>
                  <input v-model="formFournisseur.nom_entreprise" type="text" :style="inputStyle" />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Téléphone *</label>
                  <input v-model="formFournisseur.telephone" type="text" :style="inputStyle" required />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Téléphone 2</label>
                  <input v-model="formFournisseur.telephone2" type="text" :style="inputStyle" />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Email</label>
                  <input v-model="formFournisseur.email" type="email" :style="inputStyle" />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Type fournisseur</label>
                  <select v-model="formFournisseur.type_fournisseur" :style="selectStyle">
                    <option value="Grossiste">Grossiste</option>
                    <option value="Producteur">Producteur</option>
                    <option value="Importateur">Importateur</option>
                    <option value="Distributeur">Distributeur</option>
                    <option value="Autre">Autre</option>
                  </select>
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Quartier</label>
                  <input v-model="formFournisseur.quartier" type="text" :style="inputStyle" />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Ville</label>
                  <input v-model="formFournisseur.ville" type="text" :style="inputStyle" />
                </div>

                <div :style="{...formGroupStyle, gridColumn: '1 / -1'}">
                  <label :style="labelStyle">Adresse complète</label>
                  <textarea v-model="formFournisseur.adresse" :style="textareaStyle" rows="2"></textarea>
                </div>

                <div :style="{...formGroupStyle, gridColumn: '1 / -1'}">
                  <label :style="labelStyle">Catégories de produits</label>
                  <input v-model="formFournisseur.categorie_produits" type="text" :style="inputStyle" placeholder="Ex: Alimentaire, Électronique" />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Conditions de paiement</label>
                  <input v-model="formFournisseur.conditions_paiement" type="text" :style="inputStyle" placeholder="Ex: 30 jours" />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Délai livraison (jours)</label>
                  <input v-model.number="formFournisseur.delai_livraison_moyen" type="number" :style="inputStyle" />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Nom commercial</label>
                  <input v-model="formFournisseur.nom_commercial" type="text" :style="inputStyle" />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Catégorie fournisseur</label>
                  <select v-model="formFournisseur.categorie_fournisseur" :style="selectStyle">
                    <option value="">Sélectionner</option>
                    <option value="particulier">Particulier</option>
                    <option value="entreprise">Entreprise</option>
                  </select>
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Tél commercial</label>
                  <input v-model="formFournisseur.telephone_commercial" type="text" :style="inputStyle" />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Statut</label>
                  <select v-model="formFournisseur.statut" :style="selectStyle">
                    <option value="Actif">Actif</option>
                    <option value="Inactif">Inactif</option>
                    <option value="Suspendu">Suspendu</option>
                  </select>
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Note (/5)</label>
                  <input v-model.number="formFournisseur.note_evaluation" type="number" step="0.1" min="0" max="5" :style="inputStyle" />
                </div>

                <div :style="{...formGroupStyle, gridColumn: '1 / -1'}">
                  <label :style="labelStyle">Notes</label>
                  <textarea v-model="formFournisseur.notes" :style="textareaStyle" rows="2"></textarea>
                </div>
              </div>

              <div :style="modalActionsStyle">
                <button :style="cancelButtonStyle" @click="closeFournisseurModal">Annuler</button>
                <button :style="saveButtonStyle" @click="saveFournisseur" :disabled="saving">
                  <div v-if="saving" :style="smallSpinnerStyle"></div>
                  <span v-else>{{ editingFournisseur ? 'Mettre à jour' : 'Créer' }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>

      <!-- Modal Dashboard -->
      <Transition name="modal">
        <div v-if="showDashboard" :style="modalOverlayStyle" @click.self="showDashboard = false">
          <div :style="{...modalStyle, maxWidth: '1200px'}" class="modal-content">
            <div :style="modalHeaderStyle">
              <h2 :style="modalTitleStyle">Tableau de bord Fournisseurs</h2>
              <button :style="closeButtonStyle" @click="showDashboard = false">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </div>
  </SidebarLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import SidebarLayout from '../SidebarLayout.vue'
import { useAuthStore } from '../../stores/authStore'
import api from '../../services/api.js'

import { useToast } from 'vue-toastification'
const toast = useToast()

const router = useRouter()
const authStore = useAuthStore()

// State
const loading = ref(false)
const saving = ref(false)
const fournisseurs = ref([])
const statsGlobal = ref({})
const showFournisseurModal = ref(false)
const showDashboard = ref(false)
const editingFournisseur = ref(null)
const searchQuery = ref('')
const filterType = ref('')
const filterStatut = ref('')
const newHovered = ref(false)

const formFournisseur = ref({
  nom: '',
  nom_entreprise: '',
  categorie_fournisseur: '', // enum('particulier', 'entreprise')
  type_fournisseur: '', // varchar(50), pour compatibilité
  adresse: '',
  quartier: '',
  ville: 'Abidjan',
  pays: 'Côte d\'Ivoire',
  contact: '',
  telephone: '', // pour compatibilité frontend
  telephone2: '',
  email: '',
  nom_commercial: '',
  telephone_commercial: '',
  categorie_client: '',
  delai_livraison: '',
  delai_livraison_moyen: '',
  conditions_paiement: '',
  statut: 'Actif',
  evaluation: null,
  note_evaluation: null,
  notes: '',
  for_shop: 0
})

// Computed
const filteredFournisseurs = computed(() => {
  let result = [...fournisseurs.value]
  
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(f => {
      const nomMatch = f.nom && f.nom.toLowerCase().includes(query)
      const entrepriseMatch = f.nom_entreprise && f.nom_entreprise.toLowerCase().includes(query)
      const contactMatch = (f.contact || f.telephone || '').toString().includes(query)
      return nomMatch || entrepriseMatch || contactMatch
    })
  }
  
  if (filterType.value) {
    result = result.filter(f => {
      const type = f.type_fournisseur || f.categorie_fournisseur
      return type === filterType.value
    })
  }
  
  if (filterStatut.value) {
    result = result.filter(f => {
      // Le champ statut peut ne pas exister, donc on ne filtre que s'il existe
      return !f.statut || f.statut === filterStatut.value
    })
  }
  
  return result
})

// Methods
const randomParam = () => `&_=${Date.now()}_${Math.random().toString(36).slice(2)}`

// Construire les parametres user_id et role pour filtrer par utilisateur
const getUserParams = () => {
  const userId = authStore.user?.id
  const role = authStore.isAdmin ? 'admin' : 'user'
  return `&user_id=${userId}&role=${role}`
}

// Charger la liste des fournisseurs
const loadFournisseurs = async () => {
  loading.value = true
  try {
    const response = await api.get('api_fournisseurs.php?action=list_fournisseurs');
    if (response.data.success) {
      fournisseurs.value = response.data.data || [];
    } else {
      fournisseurs.value = [];
    }
  } catch (error) {
    console.error('Erreur lors du chargement des fournisseurs:', error);
    fournisseurs.value = [];
  } finally {
    loading.value = false;
  }
}

// Charger les stats globales des fournisseurs
const loadStatsGlobal = async () => {
  try {
    const response = await api.get('api_fournisseurs.php?action=rapport_global');
    if (response.data.success) {
      statsGlobal.value = response.data.data?.statistiques || response.data.data || {};
    } else {
      console.warn('Stats: API retourne success=false:', response.data.message || response.data.error);
    }
  } catch (error) {
    console.error('Erreur lors du chargement des stats:', error);
  }
}


const editFournisseur = (fournisseur) => {
  editingFournisseur.value = fournisseur
  formFournisseur.value = { ...fournisseur }
  showFournisseurModal.value = true
}

const closeFournisseurModal = () => {
  showFournisseurModal.value = false
  editingFournisseur.value = null
  formFournisseur.value = {
    nom: '',
    nom_entreprise: '',
    categorie_fournisseur: '',
    type_fournisseur: '',
    adresse: '',
    quartier: '',
    ville: 'Abidjan',
    pays: 'Côte d\'Ivoire',
    contact: '',
    telephone: '',
    telephone2: '',
    email: '',
    email_commercial: '',
    nom_commercial: '',
    telephone_commercial: '',
    delai_livraison: '',
    delai_livraison_moyen: '',
    conditions_paiement: '',
    statut: 'Actif',
    evaluation: null,
    note_evaluation: null,
    notes: '',
    for_shop: 0
  }
}

const saveFournisseur = async () => {
  // Champs obligatoires : nom et contact (ou telephone)
  if (!formFournisseur.value.nom || (!formFournisseur.value.contact && !formFournisseur.value.telephone)) {
    toast.error('Nom et téléphone requis');
    return;
  }

  saving.value = true;

  try {

    // Construction du payload strictement aligné avec la table, sans id_entreprise, created_by, modified_by (gérés côté backend)
    const {
      nom,
      nom_entreprise,
      categorie_fournisseur,
      type_fournisseur,
      adresse,
      quartier,
      ville,
      pays,
      contact,
      telephone,
      telephone2,
      email,
      email_commercial,
      nom_commercial,
      telephone_commercial,
      delai_livraison,
      delai_livraison_moyen,
      conditions_paiement,
      statut,
      evaluation,
      note_evaluation,
      notes,
      for_shop
    } = formFournisseur.value;

    // Correction : n'envoyer categorie_fournisseur qu'une seule fois
    const categorie = categorie_fournisseur || type_fournisseur || '';
    const type = type_fournisseur || categorie_fournisseur || '';

    const payload = {
      nom,
      nom_entreprise,
      categorie_fournisseur: categorie,
      type_fournisseur: type,
      adresse,
      quartier,
      ville,
      pays,
      contact: contact || telephone || '',
      telephone,
      telephone2,
      email,
      email_commercial,
      nom_commercial,
      telephone_commercial,
      delai_livraison: delai_livraison || delai_livraison_moyen || '',
      delai_livraison_moyen: delai_livraison_moyen || delai_livraison || '',
      conditions_paiement,
      statut,
      evaluation: evaluation ?? note_evaluation,
      note_evaluation: note_evaluation ?? evaluation,
      notes,
      for_shop: for_shop ? 1 : 0
    };

    // Suppression explicite des champs interdits si jamais présents
    delete payload.id_entreprise;
    delete payload.created_by;
    delete payload.modified_by;
    delete payload.user_id;

    // Nettoyage : suppression des champs vides ou nulls
    Object.keys(payload).forEach(key => {
      if (payload[key] === undefined || payload[key] === null || payload[key] === '') {
        delete payload[key];
      }
    });

    let response;
    if (editingFournisseur.value) {
      // Edition
      payload.id = editingFournisseur.value.id || editingFournisseur.value.id_fournisseur;
      response = await api.post('api_fournisseurs.php?action=update_fournisseur', payload);
    } else {
      // Création
      response = await api.post('api_fournisseurs.php?action=add_fournisseur', payload);
    }
    if (response.data.success) {
      await loadFournisseurs();
      await loadStatsGlobal();
      closeFournisseurModal();
      toast.success(response.data.message);
    } else {
      toast.error(response.data.error || response.data.message);
    }
  } catch (error) {
    console.error('Erreur:', error);
    toast.error('Erreur de sauvegarde');
  } finally {
    saving.value = false;
  }
}

const goToDetail = (id) => {
  router.push(`/fournisseurs/${id}`)
}

const formatMoney = (amount) => {
  return new Intl.NumberFormat('fr-FR', { 
    style: 'currency', 
    currency: 'XOF',
    maximumFractionDigits: 0
  }).format(amount)
}

// Lifecycle
onMounted(() => {
  loadFournisseurs()
  loadStatsGlobal()
})

// Styles (simplified for space)
const loadingContainerStyle = { textAlign: 'center', padding: '80px 20px' }
const loadingTextStyle = { marginTop: '16px', fontSize: '16px', fontWeight: '500', color: '#64748b' }
const spinnerStyle = { width: '48px', height: '48px', margin: '0 auto', border: '4px solid #f1f5f9', borderTop: '4px solid #10b981', borderRadius: '50%', animation: 'spin 1s linear infinite' }
const smallSpinnerStyle = { width: '18px', height: '18px', border: '2px solid rgba(255,255,255,0.3)', borderTop: '2px solid white', borderRadius: '50%', animation: 'spin 1s linear infinite' }

const headerStyle = { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '32px' }
const titleStyle = { fontSize: '32px', fontWeight: '700', color: '#0f172a', margin: '0 0 4px 0' }
const subtitleStyle = { fontSize: '16px', color: '#64748b', margin: 0 }
const newButtonStyle = computed(() => ({ display: 'flex', alignItems: 'center', gap: '8px', padding: '14px 24px', background: newHovered.value ? 'linear-gradient(135deg, #059669 0%, #047857 100%)' : 'linear-gradient(135deg, #10b981 0%, #059669 100%)', color: 'white', border: 'none', borderRadius: '12px', cursor: 'pointer', fontSize: '14px', fontWeight: '600', boxShadow: '0 4px 12px rgba(16,185,129,0.25)', transition: 'all 0.2s' }))

const statsGridStyle = { display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))', gap: '20px', marginBottom: '32px' }
const statsCardStyle = { backgroundColor: 'white', borderRadius: '20px', padding: '24px', display: 'flex', alignItems: 'center', gap: '16px', boxShadow: '0 2px 8px rgba(0,0,0,0.06)', border: '1px solid #f1f5f9' }
const statsIconStyle = (color) => ({ width: '56px', height: '56px', background: `${color}12`, borderRadius: '16px', display: 'flex', alignItems: 'center', justifyContent: 'center', color: color, flexShrink: 0 })
const statsLabelStyle = { fontSize: '13px', color: '#64748b', margin: '0 0 4px 0', fontWeight: '500' }
const statsValueStyle = { fontSize: '26px', fontWeight: '700', color: '#0f172a', margin: '0' }

const searchContainerStyle = { display: 'flex', gap: '12px', marginBottom: '24px', alignItems: 'center', flexWrap: 'wrap' }
const searchInputWrapperStyle = { flex: '1', minWidth: '300px', position: 'relative' }
const searchIconStyle = { position: 'absolute', left: '16px', top: '50%', transform: 'translateY(-50%)', width: '20px', height: '20px', color: '#94a3b8' }
const searchInputStyle = { width: '100%', padding: '14px 16px 14px 48px', border: '1px solid #e2e8f0', borderRadius: '12px', fontSize: '14px', fontWeight: '500' }
const filterSelectStyle = { padding: '14px 16px', border: '1px solid #e2e8f0', borderRadius: '12px', fontSize: '14px', fontWeight: '500', backgroundColor: 'white', cursor: 'pointer', minWidth: '180px' }
const dashboardButtonStyle = { display: 'flex', alignItems: 'center', gap: '8px', padding: '14px 20px', background: 'white', border: '1px solid #e2e8f0', borderRadius: '12px', cursor: 'pointer', fontSize: '14px', fontWeight: '600', color: '#475569', whiteSpace: 'nowrap' }

const tableContainerStyle = { backgroundColor: 'white', borderRadius: '20px', padding: '24px', boxShadow: '0 2px 8px rgba(0,0,0,0.06)', border: '1px solid #f1f5f9' }
const tableWrapperStyle = { overflowX: 'auto', borderRadius: '12px', border: '1px solid #e2e8f0' }
const tableStyle = { width: '100%', borderCollapse: 'collapse', minWidth: '1200px' }
const thStyle = { textAlign: 'left', padding: '16px', fontSize: '12px', fontWeight: '700', color: '#64748b', textTransform: 'uppercase', backgroundColor: '#f8fafc', borderBottom: '2px solid #e2e8f0' }
const trStyle = { borderBottom: '1px solid #f1f5f9', transition: 'background-color 0.2s' }
const tdStyle = { padding: '16px', fontSize: '14px', color: '#1e293b' }

const nameStyle = { margin: '0', fontWeight: '600', fontSize: '14px', color: '#0f172a' }
const entrepriseStyle = { margin: '4px 0 0 0', fontSize: '12px', color: '#94a3b8' }
const phoneStyle = { margin: '0', fontSize: '14px', color: '#1e293b', fontFamily: 'monospace' }
const emailStyle = { margin: '4px 0 0 0', fontSize: '12px', color: '#64748b' }
const adresseStyle = { margin: '0', fontSize: '14px', color: '#1e293b' }
const villeStyle = { margin: '4px 0 0 0', fontSize: '12px', color: '#94a3b8' }
const countStyle = { fontWeight: '700', fontSize: '16px', color: '#3b82f6' }
const detteStyle = (dette) => ({ fontWeight: '700', fontSize: '16px', color: dette > 0 ? '#ef4444' : '#10b981' })

const typeBadgeStyle = (type) => {
  const colors = {
    'Grossiste': { bg: '#dbeafe', color: '#1e40af' },
    'Producteur': { bg: '#dcfce7', color: '#166534' },
    'Importateur': { bg: '#fef3c7', color: '#92400e' },
    'Distributeur': { bg: '#e0e7ff', color: '#4338ca' }
  }
  const style = colors[type] || { bg: '#f1f5f9', color: '#475569' }
  return { padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: '600', backgroundColor: style.bg, color: style.color }
}

const getStatusBadgeStyle = (statut) => ({ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: '600', backgroundColor: statut === 'Actif' ? '#dcfce7' : statut === 'Inactif' ? '#f1f5f9' : '#fee2e2', color: statut === 'Actif' ? '#166534' : statut === 'Inactif' ? '#64748b' : '#991b1b' })

const actionsStyle = { display: 'flex', gap: '8px' }
const actionButtonStyle = (color) => ({ width: '36px', height: '36px', borderRadius: '10px', border: `1px solid ${color}20`, backgroundColor: `${color}10`, cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center', color: color, transition: 'all 0.2s' })

const emptyStateStyle = { display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', padding: '60px 20px', gap: '16px' }
const emptyTextStyle = { fontSize: '16px', color: '#94a3b8', fontWeight: '500' }

// Modal styles
const modalOverlayStyle = { position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, backgroundColor: 'rgba(15,23,42,0.7)', backdropFilter: 'blur(8px)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 1000, padding: '20px' }
const modalStyle = { backgroundColor: 'white', borderRadius: '24px', width: '100%', maxWidth: '900px', maxHeight: '90vh', overflow: 'auto' }
const modalHeaderStyle = { display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', padding: '32px 32px 24px', borderBottom: '1px solid #f1f5f9' }
const modalTitleStyle = { fontSize: '24px', fontWeight: '700', color: '#0f172a', margin: '0 0 6px 0' }
const modalSubtitleStyle = { fontSize: '14px', color: '#64748b', margin: 0 }
const closeButtonStyle = { background: 'none', border: 'none', cursor: 'pointer', color: '#94a3b8', padding: '8px', borderRadius: '8px' }
const modalContentStyle = { padding: '24px 32px 32px' }
const formGridStyle = { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px', marginBottom: '24px' }
const formGroupStyle = { display: 'flex', flexDirection: 'column', gap: '8px' }
const labelStyle = { fontSize: '14px', fontWeight: '600', color: '#334155' }
const inputStyle = { padding: '12px 16px', border: '1px solid #e2e8f0', borderRadius: '12px', fontSize: '14px', fontWeight: '500', color: '#1e293b' }
const selectStyle = { padding: '12px 16px', border: '1px solid #e2e8f0', borderRadius: '12px', fontSize: '14px', fontWeight: '500', color: '#1e293b', backgroundColor: 'white' }
const textareaStyle = { padding: '12px 16px', border: '1px solid #e2e8f0', borderRadius: '12px', fontSize: '14px', fontWeight: '500', color: '#1e293b', resize: 'vertical' }
const modalActionsStyle = { display: 'flex', gap: '12px', paddingTop: '24px', borderTop: '1px solid #f1f5f9' }
const cancelButtonStyle = { flex: 1, padding: '14px', backgroundColor: '#f8fafc', border: '1px solid #e2e8f0', borderRadius: '12px', fontSize: '14px', fontWeight: '600', color: '#64748b', cursor: 'pointer' }
const saveButtonStyle = computed(() => ({ flex: 1, padding: '14px', background: 'linear-gradient(135deg, #10b981 0%, #059669 100%)', border: 'none', borderRadius: '12px', fontSize: '14px', fontWeight: '600', color: 'white', cursor: saving.value ? 'not-allowed' : 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '8px', opacity: saving.value ? 0.6 : 1 }))
</script>

<style scoped>
@keyframes spin {
  to { transform: rotate(360deg); }
}
.fade-in { animation: fadeIn 0.6s ease-in; }
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
.modal-enter-active, .modal-leave-active { transition: all 0.3s; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-content { transition: all 0.3s; }
tr:hover { background-color: #f8fafc; }
</style>
