<template>
  <div>
    <!-- Filters Section -->
    <div :style="filtersContainerStyle">
      <div :style="filtersGridStyle">
        <div :style="filterGroupStyle">
          <label :style="labelStyle">Date début</label>
          <input 
            v-model="dateDebut" 
            type="date" 
            :style="inputStyle"
            :max="dateFin || new Date().toISOString().split('T')[0]"
          />
        </div>

        <div :style="filterGroupStyle">
          <label :style="labelStyle">Date fin</label>
          <input 
            v-model="dateFin" 
            type="date" 
            :style="inputStyle"
            :min="dateDebut"
            :max="new Date().toISOString().split('T')[0]"
          />
        </div>

        <div :style="filterGroupStyle">
          <label :style="labelStyle">Période rapide</label>
          <select v-model="periodeRapide" :style="selectStyle" @change="appliquerPeriodeRapide">
            <option value="">Sélectionner...</option>
            <option value="aujourdhui">Aujourd'hui</option>
            <option value="hier">Hier</option>
            <option value="cette_semaine">Cette semaine</option>
            <option value="semaine_derniere">Semaine dernière</option>
            <option value="ce_mois">Ce mois</option>
            <option value="mois_dernier">Mois dernier</option>
            <option value="cette_annee">Cette année</option>
          </select>
        </div>

        <div :style="filterGroupStyle">
          <label :style="labelStyle">&nbsp;</label>
          <button 
            :style="generateButtonStyle" 
            @click="genererRapport"
            @mouseenter="generateHovered = true"
            @mouseleave="generateHovered = false"
            :disabled="loading || !dateDebut || !dateFin"
          >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M9 11l3 3L22 4"/>
              <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
            </svg>
            Générer rapport
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" :style="loadingContainerStyle">
      <div :style="spinnerStyle"></div>
      <p :style="loadingTextStyle">Génération du rapport...</p>
    </div>

    <!-- Rapport Results -->
    <div v-if="!loading && rapport" class="fade-in">
      <!-- Summary Cards -->
      <div :style="summaryGridStyle">
        <div :style="summaryCardStyle">
          <div :style="summaryIconStyle('#10b981')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
            </svg>
          </div>
          <div>
            <p :style="summaryLabelStyle">Total mouvements</p>
            <p :style="summaryValueStyle">{{ rapport.statistiques.nombre_mouvements }}</p>
          </div>
        </div>

        <div :style="summaryCardStyle">
          <div :style="summaryIconStyle('#3b82f6')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
              <polyline points="17 6 23 6 23 12"/>
            </svg>
          </div>
          <div>
            <p :style="summaryLabelStyle">Entrées</p>
            <p :style="summaryValueStyle">{{ rapport.statistiques.nombre_entrees }}</p>
            <p :style="summarySubValueStyle">+{{ Math.round(rapport.statistiques.total_entrees) }} unités</p>
          </div>
        </div>

        <div :style="summaryCardStyle">
          <div :style="summaryIconStyle('#f59e0b')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/>
              <polyline points="17 18 23 18 23 12"/>
            </svg>
          </div>
          <div>
            <p :style="summaryLabelStyle">Sorties</p>
            <p :style="summaryValueStyle">{{ rapport.statistiques.nombre_sorties }}</p>
            <p :style="summarySubValueStyle">-{{ Math.round(rapport.statistiques.total_sorties) }} unités</p>
          </div>
        </div>

        <div :style="summaryCardStyle">
          <div :style="summaryIconStyle(rapport.statistiques.solde_net >= 0 ? '#10b981' : '#ef4444')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <line x1="12" y1="1" x2="12" y2="23"/>
              <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
            </svg>
          </div>
          <div>
            <p :style="summaryLabelStyle">Solde net</p>
            <p :style="getSoldeValueStyle()">
              {{ rapport.statistiques.solde_net >= 0 ? '+' : '' }}{{ Math.round(rapport.statistiques.solde_net) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Export Button -->
      <div :style="exportContainerStyle">
        <button 
          :style="exportButtonStyle" 
          @click="exporterPDF"
          @mouseenter="exportHovered = true"
          @mouseleave="exportHovered = false"
          :disabled="exporting"
        >
          <div v-if="exporting" :style="smallSpinnerStyle"></div>
          <template v-else>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
              <polyline points="7 10 12 15 17 10"/>
              <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Exporter en PDF
          </template>
        </button>
      </div>

      <!-- Movements Table -->
      <div :style="tableContainerStyle">
        <h3 :style="tableTitleStyle">Détail des mouvements ({{ rapport.mouvements.length }})</h3>
        
        <div :style="tableWrapperStyle">
          <table :style="tableStyle">
            <thead>
              <tr>
                <th :style="thStyle">Date & Heure</th>
                <th :style="thStyle">Type</th>
                <th :style="thStyle">Produit</th>
                <th :style="thStyle">Quantité</th>
                <th :style="thStyle">Motif</th>
                <th :style="thStyle">Destination/Provenance</th>
                <th :style="thStyle">Responsable</th>
                <th :style="thStyle">Document</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="mvt in rapport.mouvements" :key="mvt.id" :style="trStyle">
                <td :style="tdStyle">
                  <p :style="dateMainStyle">{{ formatDate(mvt.date_mouvement) }}</p>
                  <p :style="dateSubStyle">{{ formatTime(mvt.date_mouvement) }}</p>
                </td>
                <td :style="tdStyle">
                  <span :style="getTypeBadgeStyle(mvt.type_mouvement)">
                    {{ mvt.type_mouvement }}
                  </span>
                </td>
                <td :style="tdStyle">
                  <p :style="productNameStyle">{{ mvt.produit_nom }}</p>
                  <p :style="productDescStyle">{{ mvt.reference }}</p>
                </td>
                <td :style="tdStyle">
                  <span :style="getQuantityStyle(mvt.type_mouvement)">
                    {{ mvt.type_mouvement === 'Entree' ? '+' : '-' }}{{ Math.round(mvt.quantite) }}
                  </span>
                  <span :style="unitStyle">{{ mvt.unite_mesure }}</span>
                </td>
                <td :style="tdStyle">{{ mvt.motif }}</td>
                <td :style="tdStyle">{{ mvt.destination_ou_provenance || 'N/A' }}</td>
                <td :style="tdStyle">
                  <p :style="responsableMainStyle">{{ mvt.responsable_nom || 'N/A' }}</p>
                  <p v-if="mvt.beneficiaire_nom" :style="responsableSubStyle">
                    Récup: {{ mvt.beneficiaire_nom }}
                  </p>
                </td>
                <td :style="tdStyle">
                  <p :style="docStyle">{{ mvt.numero_document || 'N/A' }}</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="rapport.mouvements.length === 0" :style="emptyStateStyle">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          <p :style="emptyTextStyle">Aucun mouvement pour cette période</p>
        </div>
      </div>
    </div>

    <!-- Initial State -->
    <div v-if="!loading && !rapport" :style="initialStateStyle">
      <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2">
        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
        <polyline points="14 2 14 8 20 8"/>
        <line x1="16" y1="13" x2="8" y2="13"/>
        <line x1="16" y1="17" x2="8" y2="17"/>
        <polyline points="10 9 9 9 8 9"/>
      </svg>
      <p :style="initialTextStyle">Sélectionnez une période et générez le rapport</p>
    </div>

    <!-- Hidden PDF Content -->
    <div id="pdf-content-entrepot" :style="pdfContentStyle">
      <div v-if="rapport" :style="pdfInnerStyle">
        <!-- PDF Header -->
        <div :style="pdfHeaderStyle">
          <div>
            <h1 :style="pdfTitleStyle">{{ rapport.entrepot.nom }}</h1>
            <p :style="pdfSubtitleStyle">{{ rapport.entrepot.code }}</p>
            <p :style="pdfInfoStyle">{{ rapport.entrepot.adresse }} - {{ rapport.entrepot.ville }}</p>
          </div>
          <div :style="pdfLogoStyle">🏭</div>
        </div>

        <div :style="pdfSectionStyle">
          <h2 :style="pdfSectionTitleStyle">RAPPORT DE MOUVEMENTS</h2>
          <p :style="pdfPeriodeStyle">{{ rapport.periode.libelle }}</p>
          <p :style="pdfDateStyle">Généré le {{ formatDateLong(new Date()) }}</p>
        </div>

        <!-- PDF Statistics -->
        <div :style="pdfStatsGridStyle">
          <div :style="pdfStatCardStyle">
            <p :style="pdfStatLabelStyle">Total mouvements</p>
            <p :style="pdfStatValueStyle">{{ rapport.statistiques.nombre_mouvements }}</p>
          </div>
          <div :style="pdfStatCardStyle">
            <p :style="pdfStatLabelStyle">Entrées</p>
            <p :style="pdfStatValueStyle">{{ rapport.statistiques.nombre_entrees }}</p>
            <p :style="pdfStatSubStyle">+{{ Math.round(rapport.statistiques.total_entrees) }} unités</p>
          </div>
          <div :style="pdfStatCardStyle">
            <p :style="pdfStatLabelStyle">Sorties</p>
            <p :style="pdfStatValueStyle">{{ rapport.statistiques.nombre_sorties }}</p>
            <p :style="pdfStatSubStyle">-{{ Math.round(rapport.statistiques.total_sorties) }} unités</p>
          </div>
          <div :style="pdfStatCardStyle">
            <p :style="pdfStatLabelStyle">Solde net</p>
            <p :style="pdfStatValueStyle">
              {{ rapport.statistiques.solde_net >= 0 ? '+' : '' }}{{ Math.round(rapport.statistiques.solde_net) }}
            </p>
          </div>
        </div>

        <!-- PDF Table -->
        <div :style="pdfTableSectionStyle">
          <h3 :style="pdfTableTitleStyle">Détail des mouvements</h3>
          <table :style="pdfTableStyle">
            <thead>
              <tr :style="pdfTheadRowStyle">
                <th :style="pdfThStyle">Date</th>
                <th :style="pdfThStyle">Type</th>
                <th :style="pdfThStyle">Produit</th>
                <th :style="pdfThStyle">Qté</th>
                <th :style="pdfThStyle">Motif</th>
                <th :style="pdfThStyle">Dest/Prov</th>
                <th :style="pdfThStyle">Resp.</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="mvt in rapport.mouvements" :key="mvt.id" :style="pdfTbodyRowStyle">
                <td :style="pdfTdStyle">{{ formatDateShort(mvt.date_mouvement) }}</td>
                <td :style="pdfTdStyle">
                  <span :style="getPdfTypeBadgeStyle(mvt.type_mouvement)">
                    {{ mvt.type_mouvement }}
                  </span>
                </td>
                <td :style="pdfTdStyle">{{ mvt.produit_nom }}</td>
                <td :style="pdfTdStyle">
                  {{ mvt.type_mouvement === 'Entree' ? '+' : '-' }}{{ Math.round(mvt.quantite) }}
                </td>
                <td :style="pdfTdStyle">{{ mvt.motif }}</td>
                <td :style="pdfTdStyle">{{ mvt.destination_ou_provenance || '-' }}</td>
                <td :style="pdfTdStyle">{{ mvt.responsable_nom || '-' }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- PDF Footer -->
        <div :style="pdfFooterStyle">
          <p>Généré automatiquement par le système de gestion d'entrepôts</p>
          <p>{{ rapport.entrepot.nom }} - {{ new Date().getFullYear() }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  entrepotId: {
    type: [String, Number],
    required: true
  },
  apiBaseUrl: {
    type: String,
    required: true
  }
})

// State
const loading = ref(false)
const exporting = ref(false)
const rapport = ref(null)
const dateDebut = ref('')
const dateFin = ref('')
const periodeRapide = ref('')
const generateHovered = ref(false)
const exportHovered = ref(false)

// Methods
const appliquerPeriodeRapide = () => {
  const aujourdhui = new Date()
  let debut, fin
  
  switch (periodeRapide.value) {
    case 'aujourdhui':
      debut = fin = new Date()
      break

    case 'hier':
      debut = fin = new Date(aujourdhui.setDate(aujourdhui.getDate() - 1))
      break

    case 'cette_semaine': {
      const jour = aujourdhui.getDay()
      debut = new Date(
        aujourdhui.setDate(aujourdhui.getDate() - jour + (jour === 0 ? -6 : 1))
      )
      fin = new Date()
      break
    }

    case 'semaine_derniere': {
      const jourSem = aujourdhui.getDay()
      fin = new Date(aujourdhui.setDate(aujourdhui.getDate() - jourSem))
      debut = new Date(fin.getTime() - 6 * 24 * 60 * 60 * 1000)
      break
    }

    case 'ce_mois':
      debut = new Date(aujourdhui.getFullYear(), aujourdhui.getMonth(), 1)
      fin = new Date()
      break

    case 'mois_dernier':
      debut = new Date(aujourdhui.getFullYear(), aujourdhui.getMonth() - 1, 1)
      fin = new Date(aujourdhui.getFullYear(), aujourdhui.getMonth(), 0)
      break

    case 'cette_annee':
      debut = new Date(aujourdhui.getFullYear(), 0, 1)
      fin = new Date()
      break
  }
  
  if (debut && fin) {
    dateDebut.value = debut.toISOString().split('T')[0]
    dateFin.value = fin.toISOString().split('T')[0]
  }
}


const genererRapport = async () => {
  if (!dateDebut.value || !dateFin.value) {
    alert('Veuillez sélectionner une période')
    return
  }
  
  loading.value = true
  try {
    const response = await fetch(
      `${props.apiBaseUrl}/api_entrepots.php?action=rapport_periode&entrepot_id=${props.entrepotId}&date_debut=${dateDebut.value}&date_fin=${dateFin.value}`
    )
    const data = await response.json()
    
    if (data.success) {
      rapport.value = data.data
    } else {
      alert(data.error)
    }
  } catch (error) {
    console.error('Erreur:', error)
    alert('Erreur lors de la génération du rapport')
  } finally {
    loading.value = false
  }
}

const exporterPDF = async () => {
  if (!rapport.value) return
  
  exporting.value = true
  
  try {
    // Dynamically import libraries
    const html2canvas = (await import('html2canvas')).default
    const jsPDF = (await import('jspdf')).default
    
    const element = document.getElementById('pdf-content-entrepot')
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
    
    const filename = `rapport_entrepot_${rapport.value.entrepot.code}_${dateDebut.value}_${dateFin.value}.pdf`
    pdf.save(filename)
    
  } catch (error) {
    console.error('Erreur export PDF:', error)
    alert('Erreur lors de l\'export PDF')
  } finally {
    exporting.value = false
  }
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const formatTime = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })
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

const formatDateShort = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' })
}

// Styles
const filtersContainerStyle = {
  backgroundColor: 'white',
  borderRadius: '20px',
  padding: '24px',
  marginBottom: '24px',
  boxShadow: '0 2px 8px rgba(0, 0, 0, 0.06)',
  border: '1px solid #f1f5f9'
}

const filtersGridStyle = {
  display: 'grid',
  gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))',
  gap: '16px'
}

const filterGroupStyle = {
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

const generateButtonStyle = computed(() => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  gap: '8px',
  padding: '12px 20px',
  background: generateHovered.value && !loading.value
    ? 'linear-gradient(135deg, #059669 0%, #047857 100%)'
    : 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
  color: 'white',
  border: 'none',
  borderRadius: '12px',
  cursor: loading.value ? 'not-allowed' : 'pointer',
  fontSize: '14px',
  fontWeight: '600',
  opacity: loading.value ? 0.6 : 1,
  boxShadow: '0 4px 12px rgba(16, 185, 129, 0.25)'
}))

const loadingContainerStyle = {
  textAlign: 'center',
  padding: '60px 20px',
  backgroundColor: 'white',
  borderRadius: '20px',
  boxShadow: '0 2px 8px rgba(0, 0, 0, 0.06)'
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

const summaryGridStyle = {
  display: 'grid',
  gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))',
  gap: '20px',
  marginBottom: '24px'
}

const summaryCardStyle = {
  backgroundColor: 'white',
  borderRadius: '20px',
  padding: '24px',
  display: 'flex',
  alignItems: 'center',
  gap: '16px',
  boxShadow: '0 2px 8px rgba(0, 0, 0, 0.06)',
  border: '1px solid #f1f5f9'
}

const summaryIconStyle = (color) => ({
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

const summaryLabelStyle = {
  fontSize: '13px',
  color: '#64748b',
  margin: '0 0 4px 0',
  fontWeight: '500'
}

const summaryValueStyle = {
  fontSize: '26px',
  fontWeight: '700',
  color: '#0f172a',
  margin: '0',
  letterSpacing: '-0.02em'
}

const summarySubValueStyle = {
  fontSize: '13px',
  color: '#64748b',
  margin: '4px 0 0 0',
  fontWeight: '500'
}

const getSoldeValueStyle = () => ({
  fontSize: '26px',
  fontWeight: '700',
  color: rapport.value.statistiques.solde_net >= 0 ? '#10b981' : '#ef4444',
  margin: '0',
  letterSpacing: '-0.02em'
})

const exportContainerStyle = {
  display: 'flex',
  justifyContent: 'flex-end',
  marginBottom: '24px'
}

const exportButtonStyle = computed(() => ({
  display: 'flex',
  alignItems: 'center',
  gap: '10px',
  padding: '14px 24px',
  background: exportHovered.value && !exporting.value
    ? 'linear-gradient(135deg, #dc2626 0%, #b91c1c 100%)'
    : 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)',
  color: 'white',
  border: 'none',
  borderRadius: '12px',
  cursor: exporting.value ? 'not-allowed' : 'pointer',
  fontSize: '14px',
  fontWeight: '600',
  opacity: exporting.value ? 0.6 : 1,
  boxShadow: '0 4px 12px rgba(239, 68, 68, 0.25)',
  transition: 'all 0.2s'
}))

const tableContainerStyle = {
  backgroundColor: 'white',
  borderRadius: '20px',
  padding: '24px',
  boxShadow: '0 2px 8px rgba(0, 0, 0, 0.06)',
  border: '1px solid #f1f5f9'
}

const tableTitleStyle = {
  fontSize: '18px',
  fontWeight: '700',
  color: '#0f172a',
  margin: '0 0 20px 0'
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

const dateMainStyle = {
  margin: '0',
  fontWeight: '600',
  fontSize: '14px',
  color: '#0f172a'
}

const dateSubStyle = {
  margin: '4px 0 0 0',
  fontSize: '12px',
  color: '#94a3b8',
  fontWeight: '500'
}

const getTypeBadgeStyle = (type) => ({
  padding: '4px 12px',
  borderRadius: '12px',
  fontSize: '12px',
  fontWeight: '600',
  backgroundColor: type === 'Entree' ? '#dcfce7' : '#fef3c7',
  color: type === 'Entree' ? '#166534' : '#92400e'
})

const productNameStyle = {
  margin: '0',
  fontWeight: '600',
  fontSize: '14px',
  color: '#0f172a'
}

const productDescStyle = {
  margin: '4px 0 0 0',
  fontSize: '12px',
  color: '#94a3b8',
  fontWeight: '500'
}

const getQuantityStyle = (type) => ({
  fontWeight: '700',
  fontSize: '16px',
  color: type === 'Entree' ? '#10b981' : '#f59e0b'
})

const unitStyle = {
  fontSize: '12px',
  color: '#94a3b8',
  marginLeft: '4px'
}

const responsableMainStyle = {
  margin: '0',
  fontWeight: '500',
  fontSize: '14px',
  color: '#1e293b'
}

const responsableSubStyle = {
  margin: '4px 0 0 0',
  fontSize: '12px',
  color: '#64748b',
  fontStyle: 'italic'
}

const docStyle = {
  margin: '0',
  fontFamily: 'monospace',
  fontSize: '13px',
  color: '#3b82f6'
}

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

const initialStateStyle = {
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  justifyContent: 'center',
  padding: '80px 20px',
  backgroundColor: 'white',
  borderRadius: '20px',
  gap: '16px',
  boxShadow: '0 2px 8px rgba(0, 0, 0, 0.06)'
}

const initialTextStyle = {
  fontSize: '16px',
  color: '#94a3b8',
  fontWeight: '500'
}

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

const pdfLogoStyle = {
  fontSize: '48px'
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

const pdfInfoStyle = {
  fontSize: '12px',
  color: '#94a3b8',
  margin: '0'
}

const pdfSectionStyle = {
  marginBottom: '30px'
}

const pdfSectionTitleStyle = {
  fontSize: '20px',
  fontWeight: '700',
  color: '#0f172a',
  margin: '0 0 8px 0',
  textTransform: 'uppercase',
  letterSpacing: '0.05em'
}

const pdfPeriodeStyle = {
  fontSize: '14px',
  color: '#64748b',
  margin: '0 0 4px 0',
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

const pdfStatSubStyle = {
  fontSize: '11px',
  color: '#64748b',
  margin: '4px 0 0 0'
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
  fontSize: '10px'
}

const pdfTheadRowStyle = {
  backgroundColor: '#f8fafc'
}

const pdfThStyle = {
  textAlign: 'left',
  padding: '10px 8px',
  fontSize: '9px',
  fontWeight: '700',
  color: '#64748b',
  textTransform: 'uppercase',
  borderBottom: '2px solid #e2e8f0'
}

const pdfTbodyRowStyle = {
  borderBottom: '1px solid #f1f5f9'
}

const pdfTdStyle = {
  padding: '10px 8px',
  fontSize: '10px',
  color: '#1e293b'
}

const getPdfTypeBadgeStyle = (type) => ({
  padding: '2px 8px',
  borderRadius: '4px',
  fontSize: '9px',
  fontWeight: '600',
  backgroundColor: type === 'Entree' ? '#dcfce7' : '#fef3c7',
  color: type === 'Entree' ? '#166534' : '#92400e'
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

<style scoped>
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

tr:hover {
  background-color: #f8fafc;
}
</style>