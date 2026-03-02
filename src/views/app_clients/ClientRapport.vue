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
            <option value="ce_mois">Ce mois</option>
            <option value="mois_dernier">Mois dernier</option>
            <option value="3_mois">3 derniers mois</option>
            <option value="6_mois">6 derniers mois</option>
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
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
              <line x1="16" y1="2" x2="16" y2="6"/>
              <line x1="8" y1="2" x2="8" y2="6"/>
              <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
          </div>
          <div>
            <p :style="summaryLabelStyle">Crédits créés</p>
            <p :style="summaryValueStyle">{{ rapport.statistiques.nombre_credits }}</p>
          </div>
        </div>

        <div :style="summaryCardStyle">
          <div :style="summaryIconStyle('#3b82f6')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <line x1="12" y1="1" x2="12" y2="23"/>
              <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
            </svg>
          </div>
          <div>
            <p :style="summaryLabelStyle">Montant total</p>
            <p :style="summaryValueStyle">{{ formatMoney(rapport.statistiques.total_credits) }}</p>
          </div>
        </div>

        <div :style="summaryCardStyle">
          <div :style="summaryIconStyle('#10b981')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polyline points="20 6 9 17 4 12"/>
            </svg>
          </div>
          <div>
            <p :style="summaryLabelStyle">Montant payé</p>
            <p :style="summaryValueStyle">{{ formatMoney(rapport.statistiques.total_paye) }}</p>
          </div>
        </div>

        <div :style="summaryCardStyle">
          <div :style="summaryIconStyle('#ef4444')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
              <line x1="12" y1="9" x2="12" y2="13"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </div>
          <div>
            <p :style="summaryLabelStyle">Reste dû</p>
            <p :style="summaryValueStyle">{{ formatMoney(rapport.statistiques.reste_du) }}</p>
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

      <!-- Credits Table -->
      <div :style="tableContainerStyle">
        <h3 :style="tableTitleStyle">Détail des crédits ({{ rapport.credits.length }})</h3>
        
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
              </tr>
            </thead>
            <tbody>
              <tr v-for="credit in rapport.credits" :key="credit.id" :style="trStyle">
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
                <td :style="tdStyle">{{ formatDate(credit.date_echeance) }}</td>
                <td :style="tdStyle">
                  <span :style="getStatusBadgeStyle(credit.statut)">{{ credit.statut }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="rapport.credits.length === 0" :style="emptyStateStyle">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          <p :style="emptyTextStyle">Aucun crédit pour cette période</p>
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
    <div id="pdf-content-client" :style="pdfContentStyle">
      <div v-if="rapport" :style="pdfInnerStyle">
        <!-- PDF Header -->
        <div :style="pdfHeaderStyle">
          <div>
            <h1 :style="pdfTitleStyle">{{ rapport.client.nom }} {{ rapport.client.prenom || '' }}</h1>
            <p :style="pdfSubtitleStyle">{{ rapport.client.telephone }}</p>
            <p :style="pdfInfoStyle">{{ rapport.client.adresse || '' }}</p>
          </div>
          <div :style="pdfLogoStyle">👤</div>
        </div>

        <div :style="pdfSectionStyle">
          <h2 :style="pdfSectionTitleStyle">RAPPORT DES CRÉDITS</h2>
          <p :style="pdfPeriodeStyle">{{ formatDate(dateDebut) }} au {{ formatDate(dateFin) }}</p>
          <p :style="pdfDateStyle">Généré le {{ formatDateLong(new Date()) }}</p>
        </div>

        <!-- PDF Statistics -->
        <div :style="pdfStatsGridStyle">
          <div :style="pdfStatCardStyle">
            <p :style="pdfStatLabelStyle">Crédits créés</p>
            <p :style="pdfStatValueStyle">{{ rapport.statistiques.nombre_credits }}</p>
          </div>
          <div :style="pdfStatCardStyle">
            <p :style="pdfStatLabelStyle">Montant total</p>
            <p :style="pdfStatValueStyle">{{ formatMoney(rapport.statistiques.total_credits) }}</p>
          </div>
          <div :style="pdfStatCardStyle">
            <p :style="pdfStatLabelStyle">Montant payé</p>
            <p :style="pdfStatValueStyle">{{ formatMoney(rapport.statistiques.total_paye) }}</p>
          </div>
          <div :style="pdfStatCardStyle">
            <p :style="pdfStatLabelStyle">Reste dû</p>
            <p :style="pdfStatValueStyle">{{ formatMoney(rapport.statistiques.reste_du) }}</p>
          </div>
        </div>

        <!-- PDF Table -->
        <div :style="pdfTableSectionStyle">
          <h3 :style="pdfTableTitleStyle">Détail des crédits</h3>
          <table :style="pdfTableStyle">
            <thead>
              <tr :style="pdfTheadRowStyle">
                <th :style="pdfThStyle">Date</th>
                <th :style="pdfThStyle">Description</th>
                <th :style="pdfThStyle">Total</th>
                <th :style="pdfThStyle">Payé</th>
                <th :style="pdfThStyle">Reste</th>
                <th :style="pdfThStyle">Statut</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="credit in rapport.credits" :key="credit.id" :style="pdfTbodyRowStyle">
                <td :style="pdfTdStyle">{{ formatDateShort(credit.date_achat) }}</td>
                <td :style="pdfTdStyle">{{ credit.description.substring(0, 50) }}{{ credit.description.length > 50 ? '...' : '' }}</td>
                <td :style="pdfTdStyle">{{ formatMoney(credit.montant_total) }}</td>
                <td :style="pdfTdStyle">{{ formatMoney(credit.montant_paye) }}</td>
                <td :style="pdfTdStyle">{{ formatMoney(credit.montant_restant) }}</td>
                <td :style="pdfTdStyle">
                  <span :style="getPdfStatusBadgeStyle(credit.statut)">{{ credit.statut }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- PDF Footer -->
        <div :style="pdfFooterStyle">
          <p>Généré automatiquement par le système de gestion des clients</p>
          <p>{{ new Date().getFullYear() }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  clientId: {
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
    case 'ce_mois':
      debut = new Date(aujourdhui.getFullYear(), aujourdhui.getMonth(), 1)
      fin = new Date()
      break
    case 'mois_dernier':
      debut = new Date(aujourdhui.getFullYear(), aujourdhui.getMonth() - 1, 1)
      fin = new Date(aujourdhui.getFullYear(), aujourdhui.getMonth(), 0)
      break
    case '3_mois':
      debut = new Date(aujourdhui.getFullYear(), aujourdhui.getMonth() - 3, 1)
      fin = new Date()
      break
    case '6_mois':
      debut = new Date(aujourdhui.getFullYear(), aujourdhui.getMonth() - 6, 1)
      fin = new Date()
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

const randomParam = () => `&_=${Date.now()}_${Math.random().toString(36).slice(2)}`

const genererRapport = async () => {
  if (!dateDebut.value || !dateFin.value) {
    alert('Veuillez sélectionner une période')
    return
  }

  loading.value = true
  try {
    // Charger les infos du client
    const clientResponse = await fetch(
      `${props.apiBaseUrl}/api_clients.php?action=get_client&id=${props.clientId}${randomParam()}`
    )
    const clientData = await clientResponse.json()

    // Charger les crédits de la période
    const creditsResponse = await fetch(
      `${props.apiBaseUrl}/api_clients.php?action=list_credits&client_id=${props.clientId}${randomParam()}`
    )
    const creditsData = await creditsResponse.json()

    if (clientData.success && creditsData.success) {
      // Filtrer les crédits par période
      const creditsFiltres = creditsData.data.filter(credit => {
        const dateAchat = new Date(credit.date_achat)
        const debut = new Date(dateDebut.value)
        const fin = new Date(dateFin.value)
        return dateAchat >= debut && dateAchat <= fin
      })

      // Calculer les statistiques
      const stats = {
        nombre_credits: creditsFiltres.length,
        total_credits: creditsFiltres.reduce((sum, c) => sum + parseFloat(c.montant_total), 0),
        total_paye: creditsFiltres.reduce((sum, c) => sum + parseFloat(c.montant_paye), 0),
        reste_du: creditsFiltres.reduce((sum, c) => sum + parseFloat(c.montant_restant), 0)
      }

      rapport.value = {
        client: clientData.data,
        credits: creditsFiltres,
        statistiques: stats
      }
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
    const html2canvas = (await import('html2canvas')).default
    const jsPDF = (await import('jspdf')).default
    
    const element = document.getElementById('pdf-content-client')
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
    
    const filename = `rapport_client_${rapport.value.client.nom}_${dateDebut.value}_${dateFin.value}.pdf`
    pdf.save(filename)
    
  } catch (error) {
    console.error('Erreur export PDF:', error)
    alert('Erreur lors de l\'export PDF')
  } finally {
    exporting.value = false
  }
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
  return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: '2-digit' })
}

// Styles
const filtersContainerStyle = { backgroundColor: 'white', borderRadius: '20px', padding: '24px', marginBottom: '24px', boxShadow: '0 2px 8px rgba(0,0,0,0.06)', border: '1px solid #f1f5f9' }
const filtersGridStyle = { display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '16px' }
const filterGroupStyle = { display: 'flex', flexDirection: 'column', gap: '8px' }
const labelStyle = { fontSize: '14px', fontWeight: '600', color: '#334155' }
const inputStyle = { padding: '12px 16px', border: '1px solid #e2e8f0', borderRadius: '12px', fontSize: '14px', fontWeight: '500', color: '#1e293b' }
const selectStyle = { padding: '12px 16px', border: '1px solid #e2e8f0', borderRadius: '12px', fontSize: '14px', fontWeight: '500', color: '#1e293b', backgroundColor: 'white' }
const generateButtonStyle = computed(() => ({ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '8px', padding: '12px 20px', background: generateHovered.value && !loading.value ? 'linear-gradient(135deg, #059669 0%, #047857 100%)' : 'linear-gradient(135deg, #10b981 0%, #059669 100%)', color: 'white', border: 'none', borderRadius: '12px', cursor: loading.value ? 'not-allowed' : 'pointer', fontSize: '14px', fontWeight: '600', opacity: loading.value ? 0.6 : 1, boxShadow: '0 4px 12px rgba(16,185,129,0.25)' }))

const loadingContainerStyle = { textAlign: 'center', padding: '60px 20px', backgroundColor: 'white', borderRadius: '20px', boxShadow: '0 2px 8px rgba(0,0,0,0.06)' }
const loadingTextStyle = { marginTop: '16px', fontSize: '16px', fontWeight: '500', color: '#64748b' }
const spinnerStyle = { width: '48px', height: '48px', margin: '0 auto', border: '4px solid #f1f5f9', borderTop: '4px solid #10b981', borderRadius: '50%', animation: 'spin 1s linear infinite' }
const smallSpinnerStyle = { width: '18px', height: '18px', border: '2px solid rgba(255,255,255,0.3)', borderTop: '2px solid white', borderRadius: '50%', animation: 'spin 1s linear infinite' }

const summaryGridStyle = { display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))', gap: '20px', marginBottom: '24px' }
const summaryCardStyle = { backgroundColor: 'white', borderRadius: '20px', padding: '24px', display: 'flex', alignItems: 'center', gap: '16px', boxShadow: '0 2px 8px rgba(0,0,0,0.06)', border: '1px solid #f1f5f9' }
const summaryIconStyle = (color) => ({ width: '56px', height: '56px', background: `${color}12`, borderRadius: '16px', display: 'flex', alignItems: 'center', justifyContent: 'center', color: color, flexShrink: 0 })
const summaryLabelStyle = { fontSize: '13px', color: '#64748b', margin: '0 0 4px 0', fontWeight: '500' }
const summaryValueStyle = { fontSize: '26px', fontWeight: '700', color: '#0f172a', margin: '0', letterSpacing: '-0.02em' }

const exportContainerStyle = { display: 'flex', justifyContent: 'flex-end', marginBottom: '24px' }
const exportButtonStyle = computed(() => ({ display: 'flex', alignItems: 'center', gap: '10px', padding: '14px 24px', background: exportHovered.value && !exporting.value ? 'linear-gradient(135deg, #dc2626 0%, #b91c1c 100%)' : 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)', color: 'white', border: 'none', borderRadius: '12px', cursor: exporting.value ? 'not-allowed' : 'pointer', fontSize: '14px', fontWeight: '600', opacity: exporting.value ? 0.6 : 1, boxShadow: '0 4px 12px rgba(239,68,68,0.25)', transition: 'all 0.2s' }))

const tableContainerStyle = { backgroundColor: 'white', borderRadius: '20px', padding: '24px', boxShadow: '0 2px 8px rgba(0,0,0,0.06)', border: '1px solid #f1f5f9' }
const tableTitleStyle = { fontSize: '18px', fontWeight: '700', color: '#0f172a', margin: '0 0 20px 0' }
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
const getStatusBadgeStyle = (statut) => ({ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: '600', backgroundColor: statut === 'Soldé' ? '#dcfce7' : statut === 'En retard' ? '#fee2e2' : '#fef3c7', color: statut === 'Soldé' ? '#166534' : statut === 'En retard' ? '#991b1b' : '#92400e' })

const emptyStateStyle = { display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', padding: '60px 20px', gap: '16px' }
const emptyTextStyle = { fontSize: '16px', color: '#94a3b8', fontWeight: '500' }

const initialStateStyle = { display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', padding: '80px 20px', backgroundColor: 'white', borderRadius: '20px', gap: '16px', boxShadow: '0 2px 8px rgba(0,0,0,0.06)' }
const initialTextStyle = { fontSize: '16px', color: '#94a3b8', fontWeight: '500' }

// PDF Styles
const pdfContentStyle = { display: 'none', position: 'fixed', left: '-9999px', top: 0 }
const pdfInnerStyle = { width: '210mm', backgroundColor: 'white', padding: '20mm', fontFamily: 'Arial, sans-serif' }
const pdfHeaderStyle = { display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '30px', paddingBottom: '20px', borderBottom: '3px solid #10b981' }
const pdfLogoStyle = { fontSize: '48px' }
const pdfTitleStyle = { fontSize: '24px', fontWeight: '700', color: '#0f172a', margin: '0 0 8px 0' }
const pdfSubtitleStyle = { fontSize: '16px', color: '#64748b', margin: '0 0 8px 0', fontWeight: '600' }
const pdfInfoStyle = { fontSize: '12px', color: '#94a3b8', margin: '0' }
const pdfSectionStyle = { marginBottom: '30px' }
const pdfSectionTitleStyle = { fontSize: '20px', fontWeight: '700', color: '#0f172a', margin: '0 0 8px 0', textTransform: 'uppercase', letterSpacing: '0.05em' }
const pdfPeriodeStyle = { fontSize: '14px', color: '#64748b', margin: '0 0 4px 0', fontWeight: '600' }
const pdfDateStyle = { fontSize: '12px', color: '#94a3b8', margin: '0' }
const pdfStatsGridStyle = { display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: '15px', marginBottom: '30px' }
const pdfStatCardStyle = { backgroundColor: '#f8fafc', padding: '15px', borderRadius: '8px', border: '1px solid #e2e8f0' }
const pdfStatLabelStyle = { fontSize: '11px', color: '#64748b', margin: '0 0 6px 0', fontWeight: '600', textTransform: 'uppercase' }
const pdfStatValueStyle = { fontSize: '20px', fontWeight: '700', color: '#0f172a', margin: '0' }
const pdfTableSectionStyle = { marginBottom: '30px' }
const pdfTableTitleStyle = { fontSize: '16px', fontWeight: '700', color: '#0f172a', margin: '0 0 15px 0' }
const pdfTableStyle = { width: '100%', borderCollapse: 'collapse', fontSize: '10px' }
const pdfTheadRowStyle = { backgroundColor: '#f8fafc' }
const pdfThStyle = { textAlign: 'left', padding: '10px 8px', fontSize: '9px', fontWeight: '700', color: '#64748b', textTransform: 'uppercase', borderBottom: '2px solid #e2e8f0' }
const pdfTbodyRowStyle = { borderBottom: '1px solid #f1f5f9' }
const pdfTdStyle = { padding: '10px 8px', fontSize: '10px', color: '#1e293b' }
const getPdfStatusBadgeStyle = (statut) => ({ padding: '2px 8px', borderRadius: '4px', fontSize: '9px', fontWeight: '600', backgroundColor: statut === 'Soldé' ? '#dcfce7' : statut === 'En retard' ? '#fee2e2' : '#fef3c7', color: statut === 'Soldé' ? '#166534' : statut === 'En retard' ? '#991b1b' : '#92400e' })
const pdfFooterStyle = { marginTop: '40px', paddingTop: '20px', borderTop: '2px solid #e2e8f0', textAlign: 'center', fontSize: '10px', color: '#94a3b8' }
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
tr:hover { background-color: #f8fafc; }
</style>