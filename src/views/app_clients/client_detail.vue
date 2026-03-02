<template>
  <SidebarLayout currentPage="clients">
    <!-- Loading State -->
    <div v-if="loading" :style="loadingContainerStyle">
      <div :style="spinnerStyle"></div>
      <p :style="loadingTextStyle">Chargement...</p>
    </div>

    <!-- Affichage message d'erreur -->
    <div v-if="errorMessage && !loading" style="color: #ef4444; text-align: center; margin: 40px 0; font-size: 18px; font-weight: 600;">
      {{ errorMessage }}<br>
      <span style="font-size:14px;color:#64748b">Paramètre id reçu : {{ debugClientId }}</span>
    </div>


    <!-- Main Content -->
    <div v-if="!loading && client && !errorMessage">
      <!-- Header -->
      <header :style="headerStyle" class="fade-in">
        <div :style="headerLeftStyle">
          <button 
            :style="backButtonStyle" 
            @click="goBack"
            @mouseenter="backHovered = true"
            @mouseleave="backHovered = false"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            <span>Retour</span>
          </button>
          <div :style="headerInfoStyle">
            <div :style="breadcrumbStyle">
              <span :style="breadcrumbItemStyle" @click="goBack">Clients</span>
              <svg :style="breadcrumbArrowStyle" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M9 5l7 7-7 7"/>
              </svg>
              <span :style="breadcrumbActiveStyle">{{ client.nom }}</span>
            </div>
            <h1 :style="titleStyle">{{ client.nom }} {{ client.prenom || '' }}</h1>
            <div :style="clientMetaStyle">
              <span :style="phoneStyle">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/>
                </svg>
                {{ client.telephone }}
              </span>
              <span :style="getStatusBadgeStyle(client.statut)">{{ client.statut }}</span>
            </div>
          </div>
        </div>
      </header>

      <!-- Stats Cards -->
      <div :style="statsGridStyle" class="fade-in">
        <div :style="statsCardStyle">
          <div :style="statsIconStyle('#10b981')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
              <line x1="16" y1="2" x2="16" y2="6"/>
              <line x1="8" y1="2" x2="8" y2="6"/>
              <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
          </div>
          <div>
            <p :style="statsLabelStyle">Total crédits</p>
            <p :style="statsValueStyle">{{ stats.nombre_credits || 0 }}</p>
          </div>
        </div>

        <div :style="statsCardStyle">
          <div :style="statsIconStyle('#3b82f6')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <circle cx="12" cy="12" r="10"/>
              <polyline points="12 6 12 12 16 14"/>
            </svg>
          </div>
          <div>
            <p :style="statsLabelStyle">Crédits en cours</p>
            <p :style="statsValueStyle">{{ stats.credits_en_cours || 0 }}</p>
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
            <p :style="statsLabelStyle">Dette totale</p>
            <p :style="statsValueStyle">{{ formatMoney(stats.total_dette || 0) }}</p>
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
            <p :style="statsLabelStyle">Dernier paiement</p>
            <p :style="statsSubValueStyle">{{ formatDate(stats.dernier_paiement) }}</p>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div :style="tabsContainerStyle" class="fade-in">
        <button 
          v-for="tab in tabs" 
          :key="tab.id"
          :style="getTabStyle(tab.id)"
          @click="activeTab = tab.id"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Tab: Crédits -->
      <div v-if="activeTab === 'credits'" :style="tableContainerStyle" class="fade-in">
        <div :style="tableHeaderStyle">
          <h3 :style="sectionTitleStyle">Liste des crédits</h3>
          <button 
            :style="addButtonStyle" 
            @click="showCreditModal = true"
            @mouseenter="addCreditHovered = true"
            @mouseleave="addCreditHovered = false"
          >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M12 5v14M5 12h14"/>
            </svg>
            Nouveau crédit
          </button>
        </div>

        <div :style="tableWrapperStyle">
          <table :style="tableStyle">
            <thead>
              <tr>
                <th :style="thStyle">Date achat</th>
                <th :style="thStyle">Description</th>
                <th :style="thStyle">Montant total</th>
                <th :style="thStyle">Payé</th>
                <th :style="thStyle">Reste</th>
                <th :style="thStyle">Échéance</th>
                <th :style="thStyle">Statut</th>
                <th :style="thStyle">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="credit in credits" :key="credit.id" :style="trStyle">
                <td :style="tdStyle">{{ formatDate(credit.date_achat) }}</td>
                <td :style="tdStyle">
                  <p :style="descriptionStyle">{{ credit.description }}</p>
                  <p v-if="credit.numero_reference" :style="refStyle">{{ credit.numero_reference }}</p>
                </td>
                <td :style="tdStyle">
                  <span :style="montantStyle">{{ formatMoney(credit.montant_total) }}</span>
                </td>
                <td :style="tdStyle">
                  <span :style="payeStyle">{{ formatMoney(credit.montant_paye) }}</span>
                </td>
                <td :style="tdStyle">
                  <span :style="getResteStyle(credit.montant_restant)">
                    {{ formatMoney(credit.montant_restant) }}
                  </span>
                </td>
                <td :style="tdStyle">
                  <span :style="echeanceStyle(credit)">{{ formatDate(credit.date_echeance) }}</span>
                </td>
                <td :style="tdStyle">
                  <span :style="getCreditStatusBadgeStyle(credit.statut)">{{ credit.statut }}</span>
                </td>
                <td :style="tdStyle">
                  <div :style="actionsStyle">
                    <button 
                      v-if="credit.statut === 'En cours' || credit.statut === 'En retard'"
                      :style="actionButtonStyle('#10b981')" 
                      @click="openPaiementModal(credit)"
                      title="Enregistrer paiement"
                    >
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"/>
                        <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                      </svg>
                    </button>
                    <button 
                      :style="actionButtonStyle('#3b82f6')" 
                      @click="editCredit(credit)"
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

        <div v-if="credits.length === 0" :style="emptyStateStyle">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
          <p :style="emptyTextStyle">Aucun crédit enregistré</p>
        </div>
      </div>

      <!-- Tab: Paiements -->
      <div v-if="activeTab === 'paiements'" :style="tableContainerStyle" class="fade-in">
        <div :style="tableHeaderStyle">
          <h3 :style="sectionTitleStyle">Historique des paiements</h3>
          <button 
            :style="addButtonStyle" 
            @click="showPaiementModal = true"
            @mouseenter="addPaiementHovered = true"
            @mouseleave="addPaiementHovered = false"
          >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M12 5v14M5 12h14"/>
            </svg>
            Enregistrer paiement
          </button>
        </div>

        <div v-for="credit in credits" :key="'paiements-' + credit.id" :style="paiementGroupStyle">
          <div :style="paiementHeaderStyle">
            <h4 :style="paiementTitleStyle">{{ credit.description }}</h4>
            <div :style="paiementMetaStyle">
              <span :style="paiementAmountStyle">Total: {{ formatMoney(credit.montant_total) }}</span>
              <span :style="getCreditStatusBadgeStyle(credit.statut)">{{ credit.statut }}</span>
            </div>
          </div>

          <div :style="tableWrapperStyle">
            <table :style="tableStyle">
              <thead>
                <tr>
                  <th :style="thStyle">Date</th>
                  <th :style="thStyle">Montant payé</th>
                  <th :style="thStyle">Mode</th>
                  <th :style="thStyle">Référence</th>
                  <th :style="thStyle">Observation</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="paiement in getPaiementsForCredit(credit.id)" :key="paiement.id" :style="trStyle">
                  <td :style="tdStyle">{{ formatDateTime(paiement.date_paiement) }}</td>
                  <td :style="tdStyle">
                    <span :style="paiementMontantStyle">{{ formatMoney(paiement.montant_paye) }}</span>
                  </td>
                  <td :style="tdStyle">
                    <span :style="modeBadgeStyle(paiement.mode_paiement)">{{ paiement.mode_paiement }}</span>
                  </td>
                  <td :style="tdStyle">{{ paiement.reference_paiement || 'N/A' }}</td>
                  <td :style="tdStyle">{{ paiement.observation || '-' }}</td>
                </tr>
                <tr v-if="getPaiementsForCredit(credit.id).length === 0" :style="trStyle">
                  <td :style="{...tdStyle, textAlign: 'center'}" colspan="5">
                    <span :style="noPaiementStyle">Aucun paiement enregistré</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div v-if="credits.length === 0" :style="emptyStateStyle">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2">
            <line x1="12" y1="1" x2="12" y2="23"/>
            <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
          </svg>
          <p :style="emptyTextStyle">Aucun paiement enregistré</p>
        </div>
      </div>

      <!-- Tab: Rapport -->
      <div v-if="activeTab === 'rapport'" :style="tableContainerStyle" class="fade-in">
        <ClientRapport 
          :clientId="clientId"
          :apiBaseUrl="API_BASE_URL"
        />
      </div>

      <!-- Modal Crédit -->
      <Transition name="modal">
        <div v-if="showCreditModal" :style="modalOverlayStyle" @click.self="closeCreditModal">
          <div :style="modalStyle" class="modal-content">
            <div :style="modalHeaderStyle">
              <div>
                <h2 :style="modalTitleStyle">{{ editingCredit ? 'Modifier' : 'Nouveau' }} crédit</h2>
                <p :style="modalSubtitleStyle">Décrivez ce que le client a pris</p>
              </div>
              <button :style="closeButtonStyle" @click="closeCreditModal">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
              </button>
            </div>
            
            <div :style="modalContentStyle">
              <div :style="formGridStyle">
                <div :style="{...formGroupStyle, gridColumn: '1 / -1'}">
                  <label :style="labelStyle">Description *</label>
                  <textarea 
                    v-model="formCredit.description" 
                    :style="textareaStyle" 
                    rows="3"
                    placeholder="Ex: 10 sacs de riz 50kg + 5 bidons d'huile 20L"
                    required
                  ></textarea>
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Date d'achat *</label>
                  <input v-model="formCredit.date_achat" type="date" :style="inputStyle" required />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Montant total *</label>
                  <input v-model.number="formCredit.montant_total" type="number" :style="inputStyle" placeholder="150000" required />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Montant déjà payé</label>
                  <input v-model.number="formCredit.montant_paye" type="number" :style="inputStyle" placeholder="50000" />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Date d'échéance</label>
                  <input v-model="formCredit.date_echeance" type="date" :style="inputStyle" />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">N° de référence</label>
                  <input v-model="formCredit.numero_reference" type="text" :style="inputStyle" placeholder="CRED-2026-001" />
                </div>

                <div :style="{...formGroupStyle, gridColumn: '1 / -1'}">
                  <label :style="labelStyle">Observation</label>
                  <textarea v-model="formCredit.observation" :style="textareaStyle" rows="2"></textarea>
                </div>
              </div>

              <div :style="modalActionsStyle">
                <button :style="cancelButtonStyle" @click="closeCreditModal">Annuler</button>
                <button :style="saveButtonStyle" @click="saveCredit" :disabled="saving">
                  <div v-if="saving" :style="smallSpinnerStyle"></div>
                  <span v-else>{{ editingCredit ? 'Mettre à jour' : 'Créer' }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>

      <!-- Modal Paiement -->
      <Transition name="modal">
        <div v-if="showPaiementModal" :style="modalOverlayStyle" @click.self="closePaiementModal">
          <div :style="modalStyle" class="modal-content">
            <div :style="modalHeaderStyle">
              <div>
                <h2 :style="modalTitleStyle">Enregistrer un paiement</h2>
                <p :style="modalSubtitleStyle">Sélectionnez le crédit et le montant</p>
              </div>
              <button :style="closeButtonStyle" @click="closePaiementModal">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
              </button>
            </div>
            
            <div :style="modalContentStyle">
              <div :style="formGridStyle">
                <div :style="{...formGroupStyle, gridColumn: '1 / -1'}">
                  <label :style="labelStyle">Crédit *</label>
                  <select v-model="formPaiement.credit_id" :style="selectStyle" required>
                    <option value="">Sélectionner un crédit...</option>
                    <option v-for="credit in creditsEnCours" :key="credit.id" :value="credit.id">
                      {{ credit.description.substring(0, 60) }}{{ credit.description.length > 60 ? '...' : '' }} 
                      - Reste: {{ formatMoney(credit.montant_restant) }}
                    </option>
                  </select>
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Montant à payer *</label>
                  <input 
                    v-model.number="formPaiement.montant_paye" 
                    type="number" 
                    :style="inputStyle" 
                    placeholder="50000"
                    required 
                  />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Mode de paiement *</label>
                  <select v-model="formPaiement.mode_paiement" :style="selectStyle" required>
                    <option value="Especes">Espèces</option>
                    <option value="Mobile Money">Mobile Money</option>
                    <option value="Virement">Virement</option>
                    <option value="Cheque">Chèque</option>
                    <option value="Autre">Autre</option>
                  </select>
                </div>

                <div :style="{...formGroupStyle, gridColumn: '1 / -1'}">
                  <label :style="labelStyle">Référence paiement</label>
                  <input 
                    v-model="formPaiement.reference_paiement" 
                    type="text" 
                    :style="inputStyle" 
                    placeholder="MM-2026-001234 ou N° chèque"
                  />
                </div>

                <div :style="{...formGroupStyle, gridColumn: '1 / -1'}">
                  <label :style="labelStyle">Observation</label>
                  <textarea v-model="formPaiement.observation" :style="textareaStyle" rows="2"></textarea>
                </div>
              </div>

              <div v-if="selectedCreditForPaiement" :style="paiementSummaryStyle">
                <div :style="summaryItemStyle">
                  <span>Montant total:</span>
                  <strong>{{ formatMoney(selectedCreditForPaiement.montant_total) }}</strong>
                </div>
                <div :style="summaryItemStyle">
                  <span>Déjà payé:</span>
                  <strong>{{ formatMoney(selectedCreditForPaiement.montant_paye) }}</strong>
                </div>
                <div :style="summaryItemStyle">
                  <span>Reste dû:</span>
                  <strong :style="{color: '#ef4444'}">{{ formatMoney(selectedCreditForPaiement.montant_restant) }}</strong>
                </div>
                <div v-if="formPaiement.montant_paye > 0" :style="summaryItemStyle">
                  <span>Après paiement:</span>
                  <strong :style="{color: '#10b981'}">
                    {{ formatMoney(Math.max(0, selectedCreditForPaiement.montant_restant - formPaiement.montant_paye)) }}
                  </strong>
                </div>
              </div>

              <div :style="modalActionsStyle">
                <button :style="cancelButtonStyle" @click="closePaiementModal">Annuler</button>
                <button :style="saveButtonStyle" @click="savePaiement" :disabled="saving">
                  <div v-if="saving" :style="smallSpinnerStyle"></div>
                  <span v-else>Enregistrer</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </div>
  </SidebarLayout>
</template>

<script setup>

import { ref, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import SidebarLayout from '../SidebarLayout.vue'
import ClientRapport from './ClientRapport.vue'
import {
  getClient,
  getClients,
  getStatsGlobal,
  getCreditsData,
  addClient,
  updateClient,
  deleteClient,
  apiGet,
  apiPost
} from '../../services/api.js'

const router = useRouter()
const route = useRoute()

// State
const clientId = computed(() => route.params.id)

// Déclenche le chargement des données lors du montage et lors du changement de clientId
onMounted(() => {
  loadAll();
});

watch(clientId, () => {
  loadAll();
});
const loading = ref(false)
const saving = ref(false)
const client = ref(null)
const stats = ref({})
const credits = ref([])
const paiements = ref([])
const activeTab = ref('credits')
const showCreditModal = ref(false)
const showPaiementModal = ref(false)
const editingCredit = ref(null)
const errorMessage = ref('')

// Debug : afficher l'id reçu
const debugClientId = computed(() => clientId.value)

const backHovered = ref(false)
const addCreditHovered = ref(false)
const addPaiementHovered = ref(false)

const tabs = [
  { id: 'credits', label: 'Crédits' },
  { id: 'paiements', label: 'Paiements' },
  { id: 'rapport', label: 'Rapport client' }
]

const formCredit = ref({
  id_client: '',
  description: '',
  date_achat: new Date().toISOString().split('T')[0],
  montant_total: '',
  montant_paye: 0,
  date_echeance: '',
  numero_reference: '',
  observation: ''
})

const formPaiement = ref({
  credit_id: '',
  id_client: '',
  montant_paye: '',
  mode_paiement: 'Especes',
  reference_paiement: '',
  observation: ''
})

// Computed
const creditsEnCours = computed(() => {
  return credits.value.filter(c => c.statut === 'En cours' || c.statut === 'En retard')
})

const selectedCreditForPaiement = computed(() => {
  if (!formPaiement.value.credit_id) return null
  return credits.value.find(c => c.id === formPaiement.value.credit_id)
})

const randomParam = () => `&_=${Date.now()}_${Math.random().toString(36).slice(2)}`

// Methods
const loadClient = async () => {
  if (!clientId.value) {
    errorMessage.value = "Aucun identifiant client fourni dans l'URL.";
    client.value = null;
    return;
  }
  try {
    const { data } = await getClient(clientId.value)
    console.log('Réponse API getClient:', data)
    if (data.success) {
      client.value = data.data
      errorMessage.value = ''
    } else {
      errorMessage.value = data.error || "Client introuvable.";
      client.value = null;
    }
  } catch (error) {
    errorMessage.value = "Erreur lors du chargement du client.";
    client.value = null;
  }
}



const loadStats = async () => {
  if (!clientId.value) return
  try {
    const { data } = await apiGet('api_clients.php?action=stats_client&id=' + clientId.value)
    if (data.success) {
      stats.value = data.data
    }
  } catch (error) {}
}



const loadCredits = async () => {
  if (!clientId.value) return
  try {
    const { data } = await apiGet('api_clients.php?action=list_credits&client_id=' + clientId.value)
    if (data.success) {
      credits.value = data.data
    }
  } catch (error) {}
}

const loadPaiements = async () => {
  if (!clientId.value) return
  try {
    paiements.value = []
    for (const credit of credits.value) {
      const { data } = await apiGet('api_clients.php?action=historique_paiements&credit_id=' + credit.id)
      if (data.success) {
        paiements.value.push(...data.data)
      }
    }
  } catch (error) {}
}


const loadAll = async () => {
  if (!clientId.value) {
    errorMessage.value = "Aucun identifiant client fourni dans l'URL.";
    client.value = null;
    loading.value = false;
    return;
  }
  loading.value = true
  await loadClient()
  if (!client.value) {
    loading.value = false
    return
  }
  await loadStats()
  await loadCredits()
  await loadPaiements()
  loading.value = false
}

const goBack = () => {
  router.push('/clients')
}

const editCredit = (credit) => {
  editingCredit.value = credit
  formCredit.value = { ...credit }
  showCreditModal.value = true
}

const openPaiementModal = (credit) => {
  formPaiement.value.credit_id = credit.id
  showPaiementModal.value = true
}

const closeCreditModal = () => {
  showCreditModal.value = false
  editingCredit.value = null
  formCredit.value = {
    id_client: clientId.value,
    description: '',
    date_achat: new Date().toISOString().split('T')[0],
    montant_total: '',
    montant_paye: 0,
    date_echeance: '',
    numero_reference: '',
    observation: ''
  }
}

const closePaiementModal = () => {
  showPaiementModal.value = false
  formPaiement.value = {
    credit_id: '',
    id_client: clientId.value,
    montant_paye: '',
    mode_paiement: 'Especes',
    reference_paiement: '',
    observation: ''
  }
}

const saveCredit = async () => {
  if (!formCredit.value.description || !formCredit.value.montant_total) {
    alert('Description et montant total requis')
    return
  }

  saving.value = true
  try {
    formCredit.value.id_client = clientId.value
    let response
    if (editingCredit.value) {
      response = await updateClient(formCredit.value)
    } else {
      response = await addClient(formCredit.value)
    }
    const data = response.data
    if (data.success) {
      await loadCredits()
      await loadStats()
      closeCreditModal()
      alert(data.message)
    } else {
      alert(data.error)
    }
  } catch (error) {
    console.error('Erreur:', error)
    alert('Erreur de sauvegarde')
  } finally {
    saving.value = false
  }
}

const savePaiement = async () => {
  if (!formPaiement.value.credit_id || !formPaiement.value.montant_paye) {
    alert('Crédit et montant requis')
    return
  }

  const credit = selectedCreditForPaiement.value
  if (formPaiement.value.montant_paye > credit.montant_restant) {
    if (!confirm(`Le montant (${formatMoney(formPaiement.value.montant_paye)}) dépasse le reste dû (${formatMoney(credit.montant_restant)}). Continuer ?`)) {
      return
    }
  }

  saving.value = true
  try {
    formPaiement.value.id_client = clientId.value
    const response = await apiPost('api_clients.php?action=add_paiement', formPaiement.value)
    const data = response.data
    if (data.success) {
      await Promise.all([loadCredits(), loadPaiements(), loadStats()])
      closePaiementModal()
      if (data.data.credit_solde) {
        alert('✅ ' + data.message)
      } else {
        alert(data.message + '\nNouveau reste: ' + formatMoney(data.data.reste_a_payer))
      }
    } else {
      alert(data.error)
    }
  } catch (error) {
    console.error('Erreur:', error)
    alert('Erreur de sauvegarde')
  } finally {
    saving.value = false
  }
}

const getPaiementsForCredit = (creditId) => {
  return paiements.value.filter(p => p.credit_id === creditId)
}

const formatMoney = (amount) => {
  return new Intl.NumberFormat('fr-FR', { 
    style: 'currency', 
    currency: 'XOF',
    maximumFractionDigits: 0
  }).format(amount || 0)
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR')
}

const formatDateTime = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleString('fr-FR', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  })
}


// Styles (simplified - similar to previous pages)
const loadingContainerStyle = { textAlign: 'center', padding: '80px 20px' }
const loadingTextStyle = { marginTop: '16px', fontSize: '16px', fontWeight: '500', color: '#64748b' }
const spinnerStyle = { width: '48px', height: '48px', margin: '0 auto', border: '4px solid #f1f5f9', borderTop: '4px solid #10b981', borderRadius: '50%', animation: 'spin 1s linear infinite' }
const smallSpinnerStyle = { width: '18px', height: '18px', border: '2px solid rgba(255,255,255,0.3)', borderTop: '2px solid white', borderRadius: '50%', animation: 'spin 1s linear infinite' }

const headerStyle = { display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '32px' }
const headerLeftStyle = { display: 'flex', alignItems: 'flex-start', gap: '16px' }
const backButtonStyle = computed(() => ({ display: 'flex', alignItems: 'center', gap: '8px', padding: '12px 18px', backgroundColor: backHovered.value ? '#f8fafc' : 'white', border: '1px solid #e2e8f0', borderRadius: '12px', cursor: 'pointer', fontSize: '14px', fontWeight: '600', color: '#475569', transition: 'all 0.2s', boxShadow: backHovered.value ? '0 4px 12px rgba(0,0,0,0.08)' : '0 1px 3px rgba(0,0,0,0.05)', transform: backHovered.value ? 'translateX(-4px)' : 'translateX(0)' }))

const headerInfoStyle = { display: 'flex', flexDirection: 'column', gap: '6px' }
const breadcrumbStyle = { display: 'flex', alignItems: 'center', gap: '6px', marginBottom: '4px' }
const breadcrumbItemStyle = { fontSize: '13px', color: '#94a3b8', fontWeight: '500', cursor: 'pointer' }
const breadcrumbArrowStyle = { width: '14px', height: '14px', color: '#cbd5e1', strokeWidth: '2' }
const breadcrumbActiveStyle = { fontSize: '13px', color: '#1e293b', fontWeight: '600' }

const titleStyle = { fontSize: '32px', fontWeight: '700', color: '#0f172a', margin: '0', letterSpacing: '-0.02em' }
const clientMetaStyle = { display: 'flex', alignItems: 'center', gap: '12px', marginTop: '4px' }
const phoneStyle = { fontSize: '14px', color: '#64748b', display: 'flex', alignItems: 'center', gap: '6px', fontWeight: '500' }

const getStatusBadgeStyle = (statut) => ({ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: '600', backgroundColor: statut === 'Actif' ? '#dcfce7' : statut === 'Inactif' ? '#f1f5f9' : '#fee2e2', color: statut === 'Actif' ? '#166534' : statut === 'Inactif' ? '#64748b' : '#991b1b' })

const statsGridStyle = { display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))', gap: '20px', marginBottom: '32px' }
const statsCardStyle = { backgroundColor: 'white', borderRadius: '20px', padding: '24px', display: 'flex', alignItems: 'center', gap: '16px', boxShadow: '0 2px 8px rgba(0,0,0,0.06)', border: '1px solid #f1f5f9' }
const statsIconStyle = (color) => ({ width: '56px', height: '56px', background: `${color}12`, borderRadius: '16px', display: 'flex', alignItems: 'center', justifyContent: 'center', color: color, flexShrink: 0 })
const statsLabelStyle = { fontSize: '13px', color: '#64748b', margin: '0 0 4px 0', fontWeight: '500' }
const statsValueStyle = { fontSize: '26px', fontWeight: '700', color: '#0f172a', margin: '0', letterSpacing: '-0.02em' }
const statsSubValueStyle = { fontSize: '14px', fontWeight: '600', color: '#64748b', margin: '0' }

const tabsContainerStyle = { display: 'flex', gap: '8px', marginBottom: '24px', backgroundColor: '#f1f5f9', padding: '6px', borderRadius: '12px', width: 'fit-content' }
const getTabStyle = (tabId) => ({ padding: '12px 20px', border: 'none', borderRadius: '10px', fontSize: '14px', fontWeight: '600', cursor: 'pointer', backgroundColor: activeTab.value === tabId ? 'white' : 'transparent', color: activeTab.value === tabId ? '#1e293b' : '#64748b', boxShadow: activeTab.value === tabId ? '0 2px 8px rgba(0,0,0,0.06)' : 'none', transition: 'all 0.2s' })

const tableContainerStyle = { backgroundColor: 'white', borderRadius: '20px', padding: '24px', boxShadow: '0 2px 8px rgba(0,0,0,0.06)', border: '1px solid #f1f5f9' }
const tableHeaderStyle = { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' }
const sectionTitleStyle = { fontSize: '18px', fontWeight: '700', color: '#0f172a', margin: 0 }

const addButtonStyle = computed(() => ({ display: 'flex', alignItems: 'center', gap: '8px', padding: '10px 20px', background: (addCreditHovered.value || addPaiementHovered.value) ? 'linear-gradient(135deg, #059669 0%, #047857 100%)' : 'linear-gradient(135deg, #10b981 0%, #059669 100%)', color: 'white', border: 'none', borderRadius: '10px', cursor: 'pointer', fontSize: '14px', fontWeight: '600', transition: 'all 0.2s', boxShadow: '0 4px 12px rgba(16,185,129,0.25)' }))

const tableWrapperStyle = { overflowX: 'auto', borderRadius: '12px', border: '1px solid #e2e8f0' }
const tableStyle = { width: '100%', borderCollapse: 'collapse', minWidth: '1000px' }
const thStyle = { textAlign: 'left', padding: '16px', fontSize: '12px', fontWeight: '700', color: '#64748b', textTransform: 'uppercase', backgroundColor: '#f8fafc', borderBottom: '2px solid #e2e8f0', letterSpacing: '0.05em' }
const trStyle = { borderBottom: '1px solid #f1f5f9', transition: 'background-color 0.2s' }
const tdStyle = { padding: '16px', fontSize: '14px', color: '#1e293b' }

const descriptionStyle = { margin: '0', fontWeight: '600', fontSize: '14px', color: '#0f172a' }
const refStyle = { margin: '4px 0 0 0', fontSize: '12px', color: '#94a3b8', fontFamily: 'monospace' }
const montantStyle = { fontWeight: '700', fontSize: '16px', color: '#0f172a' }
const payeStyle = { fontWeight: '600', fontSize: '16px', color: '#10b981' }
const getResteStyle = (reste) => ({ fontWeight: '700', fontSize: '16px', color: reste > 0 ? '#ef4444' : '#10b981' })
const echeanceStyle = (credit) => ({ fontSize: '14px', color: credit.statut === 'En retard' ? '#ef4444' : '#64748b', fontWeight: credit.statut === 'En retard' ? '600' : '500' })

const getCreditStatusBadgeStyle = (statut) => ({ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: '600', backgroundColor: statut === 'Soldé' ? '#dcfce7' : statut === 'En retard' ? '#fee2e2' : '#fef3c7', color: statut === 'Soldé' ? '#166534' : statut === 'En retard' ? '#991b1b' : '#92400e' })

const actionsStyle = { display: 'flex', gap: '8px' }
const actionButtonStyle = (color) => ({ width: '36px', height: '36px', borderRadius: '10px', border: `1px solid ${color}20`, backgroundColor: `${color}10`, cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center', color: color, transition: 'all 0.2s' })

const emptyStateStyle = { display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', padding: '60px 20px', gap: '16px' }
const emptyTextStyle = { fontSize: '16px', color: '#94a3b8', fontWeight: '500' }

const paiementGroupStyle = { marginBottom: '32px', backgroundColor: '#f8fafc', borderRadius: '16px', padding: '20px', border: '1px solid #e2e8f0' }
const paiementHeaderStyle = { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '16px' }
const paiementTitleStyle = { fontSize: '16px', fontWeight: '700', color: '#0f172a', margin: 0 }
const paiementMetaStyle = { display: 'flex', alignItems: 'center', gap: '12px' }
const paiementAmountStyle = { fontSize: '14px', fontWeight: '600', color: '#64748b' }
const paiementMontantStyle = { fontWeight: '700', fontSize: '16px', color: '#10b981' }
const noPaiementStyle = { fontSize: '14px', color: '#94a3b8', fontStyle: 'italic' }

const modeBadgeStyle = (mode) => ({ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: '600', backgroundColor: mode === 'Mobile Money' ? '#dbeafe' : mode === 'Virement' ? '#e0e7ff' : mode === 'Cheque' ? '#fef3c7' : '#f1f5f9', color: mode === 'Mobile Money' ? '#1e40af' : mode === 'Virement' ? '#4338ca' : mode === 'Cheque' ? '#92400e' : '#475569' })

// Modal styles
const modalOverlayStyle = { position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, backgroundColor: 'rgba(15,23,42,0.7)', backdropFilter: 'blur(8px)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 1000, padding: '20px' }
const modalStyle = { backgroundColor: 'white', borderRadius: '24px', width: '100%', maxWidth: '700px', maxHeight: '90vh', overflow: 'auto' }
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

const paiementSummaryStyle = { backgroundColor: '#f8fafc', borderRadius: '12px', padding: '16px', marginTop: '16px' }
const summaryItemStyle = { display: 'flex', justifyContent: 'space-between', padding: '8px 0', fontSize: '14px', color: '#1e293b' }
</script>

<style>
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