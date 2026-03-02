
<template>
  <SidebarLayout currentPage="clients">
    <!-- Loading State -->
    <div v-if="loading" :style="loadingContainerStyle">
      <div :style="spinnerStyle"></div>
      <p :style="loadingTextStyle">Chargement...</p>
    </div>

    <!-- Main Content -->
    <div v-if="!loading">
      <!-- Header -->
      <header :style="headerStyle" class="fade-in">
        <div :style="headerLeftStyle">
          <div :style="headerInfoStyle">
            <div :style="breadcrumbStyle">
              <span :style="breadcrumbActiveStyle">Clients</span>
            </div>
            <h1 :style="titleStyle">Gestion des Clients</h1>
            <p :style="subtitleStyle">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
              </svg>
              {{ clients.length }} client(s) enregistre(s)
            </p>
          </div>
        </div>
        <button 
          :style="newButtonStyle" 
          @click="showClientModal = true"
          @mouseenter="newHovered = true"
          @mouseleave="newHovered = false"
        >
          <div :style="newIconStyle">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M12 5v14M5 12h14"/>
            </svg>
          </div>
          <span>Nouveau client</span>
        </button>
      </header>

      <!-- Stats Cards -->
      <div :style="statsGridStyle" class="fade-in">
        <div :style="statsCardStyle">
          <div :style="statsIconStyle('#10b981')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
            </svg>
          </div>
          <div>
            <p :style="statsLabelStyle">Total clients</p>
            <p :style="statsValueStyle">{{ clients.length }}</p>
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
            <p :style="statsLabelStyle">Credits en cours</p>
            <p :style="statsValueStyle">{{ statsGlobal.credits_en_cours || 0 }}</p>
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
            <p :style="statsValueStyle">{{ formatMoney(statsGlobal.total_dette_globale || 0) }}</p>
          </div>
        </div>

        <div :style="statsCardStyle">
          <div :style="statsIconStyle('#f59e0b')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
              <line x1="12" y1="9" x2="12" y2="13"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </div>
          <div>
            <p :style="statsLabelStyle">Credits en retard</p>
            <p :style="statsValueStyle">{{ statsGlobal.credits_retard || 0 }}</p>
          </div>
        </div>
      </div>

      <!-- Search and Filters Bar -->
      <div :style="searchContainerStyle" class="fade-in">
        <div :style="searchInputWrapperStyle">
          <svg :style="searchIconStyle" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/>
            <path d="M21 21l-4.35-4.35"/>
          </svg>
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Rechercher un client (nom, telephone)..."
            :style="searchInputStyle"
            @input="handleSearch"
          />
        </div>

        <select v-model="filterDette" :style="filterSelectStyle">
          <option value="tous">Tous les clients</option>
          <option value="avec_dette">Avec dette uniquement</option>
          <option value="sans_dette">Sans dette uniquement</option>
        </select>

        <select v-model="sortBy" :style="filterSelectStyle">
          <option value="nom">Trier par nom</option>
          <option value="dette_desc">Dette (decroissant)</option>
          <option value="dette_asc">Dette (croissant)</option>
          <option value="echeance_proche">Echeance la plus proche</option>
        </select>

        <button :style="exportPDFButtonStyle" @click="exporterListePDF" :disabled="exportingPDF">
          <div v-if="exportingPDF" :style="smallSpinnerStyle"></div>
          <template v-else>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
              <polyline points="7 10 12 15 17 10"/>
              <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            PDF
          </template>
        </button>

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

      <!-- Clients Table -->
      <div :style="tableContainerStyle" class="fade-in">
        <div :style="tableWrapperStyle">
          <table :style="tableStyle">
            <thead>
              <tr>
                <th :style="thStyle">Client</th>
                <th :style="thStyle">Telephone</th>
                <th :style="thStyle">Adresse</th>
                <th :style="thStyle">Categorie</th>
                <th :style="thStyle">Credits</th>
                <th :style="thStyle">Dette totale</th>
                <th :style="thStyle">Statut</th>
                <th :style="thStyle">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(client, index) in filteredClients" :key="client.id_client" :style="trStyle">
                <td :style="tdStyle">
                  <p :style="clientNameStyle">{{ client.nom }} {{ client.prenom || '' }}</p>
                  <p :style="clientInfoStyle">{{ client.nom_entreprise || '—' }}</p>
                </td>
                <td :style="tdStyle">
                  <p :style="phoneStyle">{{ client.telephone }}</p>
                  <p :style="phoneSubStyle">{{ client.email || '' }}</p>
                </td>
                <td :style="tdStyle">
                  <p :style="addressStyle">{{ client.adresse || '—' }}</p>
                  <p :style="addressSubStyle">{{ client.ville || '' }}, {{ client.pays || '' }}</p>
                </td>
                <td :style="tdStyle">
                  <span :style="getCategoryBadgeStyle(client.categorie_client)">{{ client.categorie_client }}</span>
                  <span :style="getCategoryBadgeStyle(client.type_client)">{{ client.type_client }}</span>
                </td>
                <td :style="tdStyle">
                  <span :style="creditCountStyle">{{ client.nombre_commandes || 0 }}</span>
                </td>
                <td :style="tdStyle">
                  <span :style="getDetteStyle(client.total_dette)">
                    {{ formatMoney(client.total_dette || 0) }}
                  </span>
                </td>
                <td :style="tdStyle">
                  <span :style="getStatusBadgeStyle(client.statut)">{{ client.statut }}</span>
                </td>
                <td :style="tdStyle">
                  <div :style="actionsStyle">
                    <button 
                      :style="actionButtonStyle('#3b82f6')" 
                      @click="viewClient(client)"
                      title="Voir details"
                    >
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                      </svg>
                    </button>
                    <button 
                      :style="actionButtonStyle('#10b981')" 
                      @click="editClient(client)"
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

        <div v-if="filteredClients.length === 0" :style="emptyStateStyle">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2">
            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
          </svg>
          <p :style="emptyTextStyle">{{ searchQuery ? 'Aucun client trouve' : 'Aucun client enregistre' }}</p>
          
        </div>
      </div>

      <!-- Modal Client -->
      <Transition name="modal">
        <div v-if="showClientModal" :style="modalOverlayStyle" @click.self="closeClientModal">
          <div :style="modalStyle" class="modal-content">
            <div :style="modalHeaderStyle">
              <div>
                <h2 :style="modalTitleStyle">{{ editingClient ? 'Modifier' : 'Nouveau' }} client</h2>
                <p :style="modalSubtitleStyle">Renseignez les informations du client</p>
              </div>
              <button :style="closeButtonStyle" @click="closeClientModal">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
              </button>
            </div>
            
            <div :style="modalContentStyle">

              <div :style="formGridStyle">
                <div :style="formGroupStyle">
                  <label :style="labelStyle">Nom *</label>
                  <input v-model="formClient.nom" type="text" :style="inputStyle" required />
                </div>
                <div :style="formGroupStyle">
                  <label :style="labelStyle">Prenom</label>
                  <input v-model="formClient.prenom" type="text" :style="inputStyle" />
                </div>
                <div :style="formGroupStyle">
                  <label :style="labelStyle">Nom entreprise</label>
                  <input v-model="formClient.nom_entreprise" type="text" :style="inputStyle" />
                </div>
                <div :style="formGroupStyle">
                  <label :style="labelStyle">Telephone *</label>
                  <input v-model="formClient.telephone" type="tel" :style="inputStyle" required />
                </div>
                <div :style="formGroupStyle">
                  <label :style="labelStyle">Email</label>
                  <input v-model="formClient.email" type="email" :style="inputStyle" />
                </div>
                <div :style="formGroupStyle">
                  <label :style="labelStyle">Adresse</label>
                  <input v-model="formClient.adresse" type="text" :style="inputStyle" />
                </div>
                <div :style="formGroupStyle">
                  <label :style="labelStyle">Ville</label>
                  <input v-model="formClient.ville" type="text" :style="inputStyle" />
                </div>
                <div :style="formGroupStyle">
                  <label :style="labelStyle">Pays</label>
                  <input v-model="formClient.pays" type="text" :style="inputStyle" />
                </div>
                <div :style="formGroupStyle">
                  <label :style="labelStyle">Catégorie client</label>
                  <select v-model="formClient.categorie_client" :style="selectStyle">
                    <option value="particulier">Particulier</option>
                    <option value="entreprise">Entreprise</option>
                  </select>
                </div>
                <div :style="formGroupStyle">
                  <label :style="labelStyle">Type client</label>
                  <select v-model="formClient.type_client" :style="selectStyle">
                    <option value="detaillant">Détaillant</option>
                    <option value="grossiste">Grossiste</option>
                    <option value="autre">Autre</option>
                  </select>
                </div>
                <div :style="formGroupStyle">
                  <label :style="labelStyle">Statut</label>
                  <select v-model="formClient.statut" :style="selectStyle">
                    <option value="actif">Actif</option>
                    <option value="inactif">Inactif</option>
                  </select>
                </div>
              </div>

              <div :style="modalActionsStyle">
                <button :style="cancelButtonStyle" @click="closeClientModal">Annuler</button>
                <button 
                  :style="saveButtonStyle" 
                  @click="saveClient"
                  :disabled="saving"
                >
                  <div v-if="saving" :style="smallSpinnerStyle"></div>
                  <span v-else>{{ editingClient ? 'Mettre a jour' : 'Creer' }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>

      <!-- Dashboard Modal -->
      <Transition name="modal">
        <div v-if="showDashboard" :style="modalOverlayStyle" @click.self="showDashboard = false">
          <div :style="dashboardModalStyle" class="modal-content">
            <div :style="modalHeaderStyle">
              <div>
                <h2 :style="modalTitleStyle">Tableau de bord credits</h2>
                <p :style="modalSubtitleStyle">Vue d'ensemble des credits et dettes</p>
              </div>
              <button :style="closeButtonStyle" @click="showDashboard = false">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
              </button>
            </div>
            
            <div :style="modalContentStyle">
              <ClientsDashboard />
            </div>
          </div>
        </div>
      </Transition>
    </div>

    <!-- Hidden PDF Content -->
    <div id="pdf-content-liste-clients" :style="pdfContentStyle">
      <div v-if="filteredClients.length > 0" :style="pdfInnerStyle">
        <!-- PDF Header -->
        <div :style="pdfHeaderStyle">
          <div>
            <h1 :style="pdfTitleStyle">LISTE DES CLIENTS</h1>
            <p :style="pdfSubtitleStyle">
              {{ filterDette === 'avec_dette' ? 'Clients avec dette' : filterDette === 'sans_dette' ? 'Clients sans dette' : 'Tous les clients' }}
            </p>
            <p :style="pdfDateStyle">Genere le {{ formatDateLong(new Date()) }}</p>
          </div>
        </div>

        <!-- PDF Stats -->
        <div :style="pdfStatsGridStyle">
          <div :style="pdfStatCardStyle">
            <p :style="pdfStatLabelStyle">Total clients</p>
            <p :style="pdfStatValueStyle">{{ filteredClients.length }}</p>
          </div>
          <div :style="pdfStatCardStyle">
            <p :style="pdfStatLabelStyle">Avec dette</p>
            <p :style="pdfStatValueStyle">{{ filteredClients.filter(c => c.total_dette > 0).length }}</p>
          </div>
          <div :style="pdfStatCardStyle">
            <p :style="pdfStatLabelStyle">Dette totale</p>
            <p :style="pdfStatValueStyle">{{ formatMoney(filteredClients.reduce((sum, c) => sum + (c.total_dette || 0), 0)) }}</p>
          </div>
          <div :style="pdfStatCardStyle">
            <p :style="pdfStatLabelStyle">Sans dette</p>
            <p :style="pdfStatValueStyle">{{ filteredClients.filter(c => (c.total_dette || 0) === 0).length }}</p>
          </div>
        </div>

        <!-- PDF Table -->
        <div :style="pdfTableSectionStyle">
          <h3 :style="pdfTableTitleStyle">Detail des clients</h3>
          <table :style="pdfTableStyle">
            <thead>
              <tr :style="pdfTheadRowStyle">
                <th :style="pdfThStyle">Client</th>
                <th :style="pdfThStyle">Telephone</th>
                <th :style="pdfThStyle">Adresse</th>
                <th :style="pdfThStyle">Categorie</th>
                <th :style="pdfThStyle">Credits</th>
                <th :style="pdfThStyle">Dette</th>
                <th :style="pdfThStyle">Prochaine echeance</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="client in filteredClients" :key="client.id" :style="pdfTbodyRowStyle">
                <td :style="pdfTdStyle">{{ client.nom }} {{ client.prenom || '' }}</td>
                <td :style="pdfTdStyle">{{ client.telephone }}</td>
                <td :style="pdfTdStyle">{{ client.quartier || 'N/A' }}, {{ client.ville || '' }}</td>
                <td :style="pdfTdStyle">{{ client.categorie }}</td>
                <td :style="pdfTdStyle">{{ client.nombre_credits || 0 }}</td>
                <td :style="pdfTdStyle">
                  <span :style="getPdfDetteBadgeStyle(client.total_dette)">
                    {{ formatMoney(client.total_dette || 0) }}
                  </span>
                </td>
                <td :style="pdfTdStyle">{{ formatDate(getProchaineDateEcheance(client.id)) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- PDF Footer -->
        <div :style="pdfFooterStyle">
          <p>Systeme de gestion des clients - {{ new Date().getFullYear() }}</p>
        </div>
      </div>
    </div>
  </SidebarLayout>
</template>

<script setup>

import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import SidebarLayout from '../SidebarLayout.vue'
import { getClients, addClient, updateClient, getStatsGlobal, getCreditsData } from '../../services/api.js'
import ClientsDashboard from './ClientsDashboard.vue'
import { useAuthStore } from '../../stores/authStore'
import { useToast } from 'vue-toastification'

const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()

// State
const loading = ref(false)
const saving = ref(false)
const exportingPDF = ref(false)
const clients = ref([])
const creditsData = ref([])
const statsGlobal = ref({})
const showClientModal = ref(false)
const showDashboard = ref(false)
const editingClient = ref(null)
const searchQuery = ref('')
const filterDette = ref('tous')
const sortBy = ref('nom')
const newHovered = ref(false)

const formClient = ref({
  nom: '',
  prenom: '',
  telephone: '',
  telephone2: '',
  email: '',
  adresse: '',
  quartier: '',
  ville: '',
  profession: '',
  categorie: 'Particulier',
  limite_credit: 0,
  statut: 'Actif',
  notes: ''
})

// Nettoyage des champs indésirables dans la réponse API
const cleanClientsData = (data) => {
  return data.map(client => {
    const { user_id, role, _rand, ...rest } = client
    return rest
  })
}

// Computed
const filteredClients = computed(() => {
  let result = [...clients.value]
  
  // Filtre par recherche
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(client => 
      client.nom.toLowerCase().includes(query) ||
      (client.prenom && client.prenom.toLowerCase().includes(query)) ||
      client.telephone.includes(query) ||
      (client.telephone2 && client.telephone2.includes(query))
    )
  }
  
  // Filtre par dette
  if (filterDette.value === 'avec_dette') {
    result = result.filter(client => (client.total_dette || 0) > 0)
  } else if (filterDette.value === 'sans_dette') {
    result = result.filter(client => (client.total_dette || 0) === 0)
  }
  
  // Tri
  if (sortBy.value === 'nom') {
    result.sort((a, b) => a.nom.localeCompare(b.nom))
  } else if (sortBy.value === 'dette_desc') {
    result.sort((a, b) => (b.total_dette || 0) - (a.total_dette || 0))
  } else if (sortBy.value === 'dette_asc') {
    result.sort((a, b) => (a.total_dette || 0) - (b.total_dette || 0))
  } else if (sortBy.value === 'echeance_proche') {
    result.sort((a, b) => {
      const echeanceA = getProchaineDateEcheance(a.id)
      const echeanceB = getProchaineDateEcheance(b.id)
      
      if (!echeanceA && !echeanceB) return 0
      if (!echeanceA) return 1
      if (!echeanceB) return -1
      
      return new Date(echeanceA) - new Date(echeanceB)
    })
  }
  
  return result
})

// Methods
const randomParam = () => `&_=${Date.now()}_${Math.random().toString(36).slice(2)}`

// Construire les parametres user pour filtrer selon le role
const getUserParams = () => {
  const id_entreprise = authStore.user?.id_entreprise
  const role = authStore.isAdmin ? 'admin' : 'user'
  return { id_entreprise, role }
}

const apiRawResponse = ref(null);
const loadClients = async () => {
  loading.value = true
  try {
    const response = await getClients(); // plus de params
    apiRawResponse.value = JSON.stringify(response.data, null, 2);
    console.log('Réponse API getClients:', response.data);
    if (response.data.success) {
      // Nettoyage des champs indésirables et harmonisation
      clients.value = cleanClientsData(response.data.data).map(client => ({
        ...client,
        total_dette: client.total_dette !== undefined ? client.total_dette : (client.total_achats || 0),
        id_client: client.id_client !== undefined ? client.id_client : client.id,
      }));
    }
  } catch (error) {
    apiRawResponse.value = error?.response ? JSON.stringify(error.response.data, null, 2) : error.toString();
    console.error('Erreur:', error);
    alert('Erreur de chargement des clients');
  } finally {
    loading.value = false;
  }
};

const loadStatsGlobal = async () => {
  try {
    const response = await getStatsGlobal()
    if (response.data.success) {
      statsGlobal.value = response.data.data.statistiques
    }
  } catch (error) {
    console.error('Erreur:', error)
  }
}

const loadCreditsData = async () => {
  try {
    const response = await getCreditsData()
    if (response.data.success) {
      creditsData.value = response.data.data
    }
  } catch (error) {
    console.error('Erreur:', error)
  }
}


const getProchaineDateEcheance = (clientId) => {
  const clientCredits = creditsData.value.filter(c => 
    c.client_id === clientId && 
    c.date_echeance && 
    (c.statut === 'En cours' || c.statut === 'En retard')
  )
  
  if (clientCredits.length === 0) return null
  
  const sorted = clientCredits.sort((a, b) => 
    new Date(a.date_echeance) - new Date(b.date_echeance)
  )
  
  return sorted[0].date_echeance
}

const exporterListePDF = async () => {
  exportingPDF.value = true
  
  try {
    const html2canvas = (await import('html2canvas')).default
    const jsPDF = (await import('jspdf')).default
    
    const element = document.getElementById('pdf-content-liste-clients')
    element.style.display = 'block'
    
    const canvas = await html2canvas(element, {
      scale: 2,
      useCORS: true,
      logging: false,
      backgroundColor: '#ffffff'
    })
    
    element.style.display = 'none'
    
    const imgData = canvas.toDataURL('image/png')
    const pdf = new jsPDF('p', 'mm', 'a4')
    const pdfWidth = pdf.internal.pageSize.getWidth()
    const pdfHeight = pdf.internal.pageSize.getHeight()
    const imgWidth = pdfWidth - 20
    const imgHeight = (canvas.height * imgWidth) / canvas.width
    
    let heightLeft = imgHeight
    let position = 10
    
    pdf.addImage(imgData, 'PNG', 10, position, imgWidth, imgHeight)
    heightLeft -= pdfHeight
    
    while (heightLeft > 0) {
      position = heightLeft - imgHeight + 10
      pdf.addPage()
      pdf.addImage(imgData, 'PNG', 10, position, imgWidth, imgHeight)
      heightLeft -= pdfHeight
    }
    
    const filename = `liste_clients_${new Date().toISOString().split('T')[0]}.pdf`
    pdf.save(filename)
    
  } catch (error) {
    console.error('Erreur export PDF:', error)
    alert('Erreur lors de l\'export PDF')
  } finally {
    exportingPDF.value = false
  }
}

const handleSearch = () => {
  // Search is handled by computed property
}

const viewClient = (client) => {
  router.push({ name: 'clients-detail', params: { id: client.id_client } })
}

const editClient = (client) => {
  editingClient.value = client
  formClient.value = { ...client }
  showClientModal.value = true
}

const closeClientModal = () => {
  showClientModal.value = false
  editingClient.value = null
  formClient.value = {
    nom: '',
    prenom: '',
    telephone: '',
    telephone2: '',
    email: '',
    adresse: '',
    quartier: '',
    ville: '',
    profession: '',
    categorie: 'Particulier',
    limite_credit: 0,
    statut: 'Actif',
    notes: ''
  }
}

const saveClient = async () => {
  if (!formClient.value.nom || !formClient.value.telephone) {
    toast.error('Nom et telephone requis')
    return
  }
  saving.value = true
  try {
    const payload = { ...formClient.value }
    // Adapter les champs pour la table app_clients
    payload.id_entreprise = authStore.user?.id_entreprise
    payload.create_by = authStore.user?.id
    let response
    if (editingClient.value && editingClient.value.id_client) {
      payload.id_client = editingClient.value.id_client
      response = await updateClient(payload)
    } else {
      response = await addClient(payload)
    }
    if (response.data.success) {
      await loadClients()
      await loadStatsGlobal()
      closeClientModal()
      toast.success(response.data.message)
    } else {
      toast.error(response.data.error || 'Erreur inconnue')
    }
  } catch (error) {
    console.error('Erreur:', error)
    toast.error('Erreur de sauvegarde')
  } finally {
    saving.value = false
  }
}

const formatMoney = (amount) => {
  return new Intl.NumberFormat('fr-FR', { 
    style: 'currency', 
    currency: 'XOF',
    maximumFractionDigits: 0
  }).format(amount)
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR')
}

const formatDateLong = (date) => {
  return date.toLocaleDateString('fr-FR', { 
    day: 'numeric', 
    month: 'long', 
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Lifecycle
onMounted(() => {
  loadClients()
  loadStatsGlobal()
  loadCreditsData()
})

// Styles
const loadingContainerStyle = {
  textAlign: 'center',
  padding: '80px 20px'
}

const loadingTextStyle = {
  marginTop: '16px',
  fontSize: '16px',
  fontWeight: '500',
  color: '#64748b'
}

const spinnerStyle = {
  width: '48px',
  height: '48px',
  margin: '0 auto',
  border: '4px solid #f1f5f9',
  borderTop: '4px solid #10b981',
  borderRadius: '50%',
  animation: 'spin 1s linear infinite'
}

const smallSpinnerStyle = {
  width: '18px',
  height: '18px',
  border: '2px solid rgba(255, 255, 255, 0.3)',
  borderTop: '2px solid white',
  borderRadius: '50%',
  animation: 'spin 1s linear infinite'
}

const headerStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'flex-start',
  marginBottom: '32px',
  flexWrap: 'wrap',
  gap: '20px'
}

const headerLeftStyle = {
  display: 'flex',
  alignItems: 'flex-start',
  gap: '16px'
}

const headerInfoStyle = {
  display: 'flex',
  flexDirection: 'column',
  gap: '6px'
}

const breadcrumbStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '6px',
  marginBottom: '4px'
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

const newButtonStyle = computed(() => ({
  display: 'flex',
  alignItems: 'center',
  gap: '10px',
  padding: '14px 24px',
  background: newHovered.value
    ? 'linear-gradient(135deg, #059669 0%, #047857 100%)'
    : 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
  color: 'white',
  border: 'none',
  borderRadius: '12px',
  cursor: 'pointer',
  fontSize: '14px',
  fontWeight: '600',
  transition: 'all 0.3s',
  boxShadow: newHovered.value ? '0 12px 28px rgba(16, 185, 129, 0.35)' : '0 4px 12px rgba(16, 185, 129, 0.25)',
  transform: newHovered.value ? 'translateY(-2px)' : 'translateY(0)'
}))

const newIconStyle = {
  width: '32px',
  height: '32px',
  background: 'rgba(255, 255, 255, 0.2)',
  borderRadius: '8px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center'
}

const statsGridStyle = {
  display: 'grid',
  gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))',
  gap: '20px',
  marginBottom: '32px'
}

const statsCardStyle = {
  backgroundColor: 'white',
  borderRadius: '20px',
  padding: '24px',
  display: 'flex',
  alignItems: 'center',
  gap: '16px',
  boxShadow: '0 2px 8px rgba(0, 0, 0, 0.06)',
  border: '1px solid #f1f5f9'
}

const statsIconStyle = (color) => ({
  width: '56px',
  height: '56px',
  background: `${color}12`,
  borderRadius: '16px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  color: color,
  flexShrink: 0
})

const statsLabelStyle = {
  fontSize: '13px',
  color: '#64748b',
  margin: '0 0 4px 0',
  fontWeight: '500'
}

const statsValueStyle = {
  fontSize: '26px',
  fontWeight: '700',
  color: '#0f172a',
  margin: '0',
  letterSpacing: '-0.02em'
}

const searchContainerStyle = {
  display: 'flex',
  gap: '12px',
  marginBottom: '24px',
  alignItems: 'center',
  flexWrap: 'wrap'
}

const searchInputWrapperStyle = {
  position: 'relative',
  flex: 1
}

const searchIconStyle = {
  position: 'absolute',
  left: '16px',
  top: '50%',
  transform: 'translateY(-50%)',
  width: '20px',
  height: '20px',
  color: '#94a3b8',
  pointerEvents: 'none'
}

const searchInputStyle = {
  width: '100%',
  padding: '14px 16px 14px 48px',
  border: '1px solid #e2e8f0',
  borderRadius: '12px',
  fontSize: '14px',
  fontWeight: '500',
  color: '#1e293b',
  backgroundColor: 'white'
}

const dashboardButtonStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '8px',
  padding: '14px 20px',
  background: 'white',
  border: '1px solid #e2e8f0',
  borderRadius: '12px',
  cursor: 'pointer',
  fontSize: '14px',
  fontWeight: '600',
  color: '#475569',
  transition: 'all 0.2s',
  whiteSpace: 'nowrap'
}

const filterSelectStyle = {
  padding: '14px 16px',
  border: '1px solid #e2e8f0',
  borderRadius: '12px',
  fontSize: '14px',
  fontWeight: '500',
  color: '#1e293b',
  backgroundColor: 'white',
  cursor: 'pointer',
  minWidth: '180px'
}

const exportPDFButtonStyle = computed(() => ({
  display: 'flex',
  alignItems: 'center',
  gap: '8px',
  padding: '14px 20px',
  background: exportingPDF.value ? '#94a3b8' : 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)',
  color: 'white',
  border: 'none',
  borderRadius: '12px',
  cursor: exportingPDF.value ? 'not-allowed' : 'pointer',
  fontSize: '14px',
  fontWeight: '600',
  transition: 'all 0.2s',
  boxShadow: '0 4px 12px rgba(239, 68, 68, 0.25)',
  whiteSpace: 'nowrap'
}))


const tableContainerStyle = {
  backgroundColor: 'white',
  borderRadius: '20px',
  padding: '24px',
  boxShadow: '0 2px 8px rgba(0, 0, 0, 0.06)',
  border: '1px solid #f1f5f9'
}

const tableWrapperStyle = {
  overflowX: 'auto',
  borderRadius: '12px',
  border: '1px solid #e2e8f0'
}

const tableStyle = {
  width: '100%',
  borderCollapse: 'collapse',
  minWidth: '1000px'
}

const thStyle = {
  textAlign: 'left',
  padding: '16px',
  fontSize: '12px',
  fontWeight: '700',
  color: '#64748b',
  textTransform: 'uppercase',
  backgroundColor: '#f8fafc',
  borderBottom: '2px solid #e2e8f0',
  letterSpacing: '0.05em'
}

const trStyle = {
  borderBottom: '1px solid #f1f5f9',
  transition: 'background-color 0.2s'
}

const tdStyle = {
  padding: '16px',
  fontSize: '14px',
  color: '#1e293b'
}

const clientNameStyle = {
  margin: '0',
  fontWeight: '600',
  fontSize: '14px',
  color: '#0f172a'
}

const clientInfoStyle = {
  margin: '4px 0 0 0',
  fontSize: '12px',
  color: '#94a3b8',
  fontWeight: '500'
}

const phoneStyle = {
  margin: '0',
  fontWeight: '600',
  fontSize: '14px',
  color: '#0f172a',
  fontFamily: 'monospace'
}

const phoneSubStyle = {
  margin: '4px 0 0 0',
  fontSize: '12px',
  color: '#94a3b8',
  fontFamily: 'monospace'
}

const addressStyle = {
  margin: '0',
  fontSize: '14px',
  color: '#1e293b'
}

const addressSubStyle = {
  margin: '4px 0 0 0',
  fontSize: '12px',
  color: '#94a3b8'
}

const getCategoryBadgeStyle = (categorie) => ({
  padding: '4px 12px',
  borderRadius: '12px',
  fontSize: '12px',
  fontWeight: '600',
  backgroundColor: categorie === 'VIP' ? '#fef3c7' : categorie === 'Professionnel' ? '#dbeafe' : categorie === 'Entreprise' ? '#e0e7ff' : '#f1f5f9',
  color: categorie === 'VIP' ? '#92400e' : categorie === 'Professionnel' ? '#1e40af' : categorie === 'Entreprise' ? '#4338ca' : '#475569'
})

const creditCountStyle = {
  fontWeight: '700',
  fontSize: '16px',
  color: '#0f172a'
}

const getDetteStyle = (dette) => ({
  fontWeight: '700',
  fontSize: '16px',
  color: dette > 0 ? '#ef4444' : '#10b981'
})

const getStatusBadgeStyle = (statut) => ({
  padding: '4px 12px',
  borderRadius: '12px',
  fontSize: '12px',
  fontWeight: '600',
  backgroundColor: statut === 'Actif' ? '#dcfce7' : statut === 'Inactif' ? '#f1f5f9' : '#fee2e2',
  color: statut === 'Actif' ? '#166534' : statut === 'Inactif' ? '#64748b' : '#991b1b'
})

const actionsStyle = {
  display: 'flex',
  gap: '8px'
}

const actionButtonStyle = (color) => ({
  width: '36px',
  height: '36px',
  borderRadius: '10px',
  border: `1px solid ${color}20`,
  backgroundColor: `${color}10`,
  cursor: 'pointer',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  color: color,
  transition: 'all 0.2s'
})

const emptyStateStyle = {
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  justifyContent: 'center',
  padding: '60px 20px',
  gap: '16px'
}

const emptyTextStyle = {
  fontSize: '16px',
  color: '#94a3b8',
  fontWeight: '500'
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
  padding: '20px'
}

const modalStyle = {
  backgroundColor: 'white',
  borderRadius: '24px',
  width: '100%',
  maxWidth: '700px',
  maxHeight: '90vh',
  overflow: 'auto'
}

const dashboardModalStyle = {
  backgroundColor: 'white',
  borderRadius: '24px',
  width: '100%',
  maxWidth: '1200px',
  maxHeight: '90vh',
  overflow: 'auto'
}

const modalHeaderStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'flex-start',
  padding: '32px 32px 24px',
  borderBottom: '1px solid #f1f5f9'
}

const modalTitleStyle = {
  fontSize: '24px',
  fontWeight: '700',
  color: '#0f172a',
  margin: '0 0 6px 0'
}

const modalSubtitleStyle = {
  fontSize: '14px',
  color: '#64748b',
  margin: 0
}

const closeButtonStyle = {
  background: 'none',
  border: 'none',
  cursor: 'pointer',
  color: '#94a3b8',
  padding: '8px',
  borderRadius: '8px'
}

const modalContentStyle = {
  padding: '24px 32px 32px'
}

const formGridStyle = {
  display: 'grid',
  gridTemplateColumns: '1fr 1fr',
  gap: '16px',
  marginBottom: '24px'
}

const formGroupStyle = {
  display: 'flex',
  flexDirection: 'column',
  gap: '8px'
}

const labelStyle = {
  fontSize: '14px',
  fontWeight: '600',
  color: '#334155'
}

const inputStyle = {
  padding: '12px 16px',
  border: '1px solid #e2e8f0',
  borderRadius: '12px',
  fontSize: '14px',
  fontWeight: '500',
  color: '#1e293b'
}

const selectStyle = {
  padding: '12px 16px',
  border: '1px solid #e2e8f0',
  borderRadius: '12px',
  fontSize: '14px',
  fontWeight: '500',
  color: '#1e293b',
  backgroundColor: 'white'
}

const textareaStyle = {
  padding: '12px 16px',
  border: '1px solid #e2e8f0',
  borderRadius: '12px',
  fontSize: '14px',
  fontWeight: '500',
  color: '#1e293b',
  resize: 'vertical'
}

const modalActionsStyle = {
  display: 'flex',
  gap: '12px',
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
  cursor: 'pointer'
}

const saveButtonStyle = computed(() => ({
  flex: 1,
  padding: '14px',
  background: 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
  border: 'none',
  borderRadius: '12px',
  fontSize: '14px',
  fontWeight: '600',
  color: 'white',
  cursor: saving.value ? 'not-allowed' : 'pointer',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  gap: '8px',
  opacity: saving.value ? 0.6 : 1
}))

// PDF Styles
const pdfContentStyle = {
  display: 'none',
  position: 'fixed',
  left: '-9999px',
  top: 0
}

const pdfInnerStyle = {
  width: '210mm',
  backgroundColor: 'white',
  padding: '20mm',
  fontFamily: 'Arial, sans-serif'
}

const pdfHeaderStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'flex-start',
  marginBottom: '30px',
  paddingBottom: '20px',
  borderBottom: '3px solid #10b981'
}

const pdfTitleStyle = {
  fontSize: '24px',
  fontWeight: '700',
  color: '#0f172a',
  margin: '0 0 8px 0'
}

const pdfSubtitleStyle = {
  fontSize: '16px',
  color: '#64748b',
  margin: '0 0 8px 0',
  fontWeight: '600'
}

const pdfDateStyle = {
  fontSize: '12px',
  color: '#94a3b8',
  margin: '0'
}

const pdfStatsGridStyle = {
  display: 'grid',
  gridTemplateColumns: 'repeat(4, 1fr)',
  gap: '15px',
  marginBottom: '30px'
}

const pdfStatCardStyle = {
  backgroundColor: '#f8fafc',
  padding: '15px',
  borderRadius: '8px',
  border: '1px solid #e2e8f0'
}

const pdfStatLabelStyle = {
  fontSize: '11px',
  color: '#64748b',
  margin: '0 0 6px 0',
  fontWeight: '600',
  textTransform: 'uppercase'
}

const pdfStatValueStyle = {
  fontSize: '20px',
  fontWeight: '700',
  color: '#0f172a',
  margin: '0'
}

const pdfTableSectionStyle = {
  marginBottom: '30px'
}

const pdfTableTitleStyle = {
  fontSize: '16px',
  fontWeight: '700',
  color: '#0f172a',
  margin: '0 0 15px 0'
}

const pdfTableStyle = {
  width: '100%',
  borderCollapse: 'collapse',
  fontSize: '9px'
}

const pdfTheadRowStyle = {
  backgroundColor: '#f8fafc'
}

const pdfThStyle = {
  textAlign: 'left',
  padding: '10px 6px',
  fontSize: '8px',
  fontWeight: '700',
  color: '#64748b',
  textTransform: 'uppercase',
  borderBottom: '2px solid #e2e8f0'
}

const pdfTbodyRowStyle = {
  borderBottom: '1px solid #f1f5f9'
}

const pdfTdStyle = {
  padding: '10px 6px',
  fontSize: '9px',
  color: '#1e293b'
}

const getPdfDetteBadgeStyle = (dette) => ({
  padding: '2px 8px',
  borderRadius: '4px',
  fontSize: '8px',
  fontWeight: '600',
  backgroundColor: dette > 0 ? '#fee2e2' : '#dcfce7',
  color: dette > 0 ? '#991b1b' : '#166534'
})

const pdfFooterStyle = {
  marginTop: '40px',
  paddingTop: '20px',
  borderTop: '2px solid #e2e8f0',
  textAlign: 'center',
  fontSize: '10px',
  color: '#94a3b8'
}

</script>

<style>
@keyframes spin {
  to { transform: rotate(360deg); }
}

.fade-in {
  animation: fadeIn 0.6s ease-in;
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

.modal-enter-active, .modal-leave-active {
  transition: all 0.3s;
}

.modal-enter-from, .modal-leave-to {
  opacity: 0;
}

.modal-content {
  transition: all 0.3s;
}

tr:hover {
  background-color: #f8fafc;
}
</style>
