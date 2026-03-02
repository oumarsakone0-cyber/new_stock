
<template>
  <div>
    <!-- Loading -->
    <div v-if="loading" :style="loadingStyle">
      <div :style="spinnerStyle"></div>
      <p :style="loadingTextStyle">Chargement du tableau de bord...</p>
    </div>

    <!-- Dashboard -->
    <div v-if="!loading && dashboard">
      <!-- Global Stats -->
      <div :style="statsGridStyle">
        <div :style="statsCardStyle">
          <div :style="statsIconStyle('#10b981')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
            </svg>
          </div>
          <div>
            <p :style="statsLabelStyle">Clients actifs</p>
            <p :style="statsValueStyle">{{ dashboard.statistiques.clients_actifs }}</p>
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
            <p :style="statsValueStyle">{{ dashboard.statistiques.credits_en_cours }}</p>
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
            <p :style="statsLabelStyle">Dette globale</p>
            <p :style="statsValueStyle">{{ formatMoney(dashboard.statistiques.total_dette_globale) }}</p>
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
            <p :style="statsLabelStyle">Crédits en retard</p>
            <p :style="statsValueStyle">{{ dashboard.statistiques.credits_retard }}</p>
          </div>
        </div>
      </div>

      <!-- Top 10 Clients Endettés -->
      <section :style="sectionStyle">
        <div :style="sectionHeaderStyle">
          <h3 :style="sectionTitleStyle">🔴 Top 10 clients les plus endettés</h3>
        </div>
        
        <div :style="tableWrapperStyle">
          <table :style="tableStyle">
            <thead>
              <tr>
                <th :style="thStyle">#</th>
                <th :style="thStyle">Client</th>
                <th :style="thStyle">Téléphone</th>
                <th :style="thStyle">Nombre crédits</th>
                <th :style="thStyle">Dette totale</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(client, index) in dashboard.top_clients" :key="client.client_id" :style="trStyle">
                <td :style="tdStyle">
                  <span :style="rankStyle(index)">{{ index + 1 }}</span>
                </td>
                <td :style="tdStyle">
                  <p :style="clientNameStyle">{{ client.nom }} {{ client.prenom || '' }}</p>
                </td>
                <td :style="tdStyle">
                  <span :style="phoneStyle">{{ client.telephone }}</span>
                </td>
                <td :style="tdStyle">
                  <span :style="creditCountStyle">{{ client.nombre_credits }}</span>
                </td>
                <td :style="tdStyle">
                  <span :style="detteStyle">{{ formatMoney(client.total_dette) }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="dashboard.top_clients.length === 0" :style="emptyStateStyle">
          <p :style="emptyTextStyle">✅ Aucune dette en cours</p>
        </div>
      </section>

      <!-- Crédits en Retard -->
      <section :style="sectionStyle">
        <div :style="sectionHeaderStyle">
          <h3 :style="sectionTitleStyle">⏰ Crédits en retard</h3>
        </div>
        
        <div :style="tableWrapperStyle">
          <table :style="tableStyle">
            <thead>
              <tr>
                <th :style="thStyle">Client</th>
                <th :style="thStyle">Téléphone</th>
                <th :style="thStyle">Description</th>
                <th :style="thStyle">Reste dû</th>
                <th :style="thStyle">Échéance</th>
                <th :style="thStyle">Retard</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="credit in dashboard.credits_retard" :key="credit.credit_id" :style="trStyle">
                <td :style="tdStyle">
                  <p :style="clientNameStyle">{{ credit.client_nom }} {{ credit.client_prenom || '' }}</p>
                </td>
                <td :style="tdStyle">
                  <span :style="phoneStyle">{{ credit.telephone }}</span>
                </td>
                <td :style="tdStyle">
                  <p :style="descriptionStyle">{{ credit.description.substring(0, 60) }}{{ credit.description.length > 60 ? '...' : '' }}</p>
                </td>
                <td :style="tdStyle">
                  <span :style="detteStyle">{{ formatMoney(credit.montant_restant) }}</span>
                </td>
                <td :style="tdStyle">
                  <span :style="echeanceStyle">{{ formatDate(credit.date_echeance) }}</span>
                </td>
                <td :style="tdStyle">
                  <span :style="retardBadgeStyle(credit.jours_retard)">{{ credit.jours_retard }} jour(s)</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="dashboard.credits_retard.length === 0" :style="emptyStateStyle">
          <p :style="emptyTextStyle">✅ Aucun crédit en retard</p>
        </div>
      </section>

      <!-- Export PDF -->
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
            Exporter le rapport global en PDF
          </template>
        </button>
      </div>
    </div>

    <!-- Hidden PDF Content -->
    <div id="pdf-content-dashboard" :style="pdfContentStyle">
      <div v-if="dashboard" :style="pdfInnerStyle">
        <div :style="pdfHeaderStyle">
          <div>
            <h1 :style="pdfTitleStyle">TABLEAU DE BORD CRÉDITS</h1>
            <p :style="pdfDateStyle">Généré le {{ formatDateLong(new Date()) }}</p>
          </div>
          <div :style="pdfLogoStyle">📊</div>
        </div>

        <div :style="pdfStatsGridStyle">
          <div :style="pdfStatCardStyle">
            <p :style="pdfStatLabelStyle">Clients actifs</p>
            <p :style="pdfStatValueStyle">{{ dashboard.statistiques.clients_actifs }}</p>
          </div>
          <div :style="pdfStatCardStyle">
            <p :style="pdfStatLabelStyle">Crédits en cours</p>
            <p :style="pdfStatValueStyle">{{ dashboard.statistiques.credits_en_cours }}</p>
          </div>
          <div :style="pdfStatCardStyle">
            <p :style="pdfStatLabelStyle">Dette globale</p>
            <p :style="pdfStatValueStyle">{{ formatMoney(dashboard.statistiques.total_dette_globale) }}</p>
          </div>
          <div :style="pdfStatCardStyle">
            <p :style="pdfStatLabelStyle">En retard</p>
            <p :style="pdfStatValueStyle">{{ dashboard.statistiques.credits_retard }}</p>
          </div>
        </div>

        <div :style="pdfSectionStyle">
          <h3 :style="pdfSectionTitleStyle">Top 10 clients endettés</h3>
          <table :style="pdfTableStyle">
            <thead>
              <tr :style="pdfTheadRowStyle">
                <th :style="pdfThStyle">#</th>
                <th :style="pdfThStyle">Client</th>
                <th :style="pdfThStyle">Téléphone</th>
                <th :style="pdfThStyle">Crédits</th>
                <th :style="pdfThStyle">Dette</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(client, index) in dashboard.top_clients" :key="client.client_id" :style="pdfTbodyRowStyle">
                <td :style="pdfTdStyle">{{ index + 1 }}</td>
                <td :style="pdfTdStyle">{{ client.nom }} {{ client.prenom || '' }}</td>
                <td :style="pdfTdStyle">{{ client.telephone }}</td>
                <td :style="pdfTdStyle">{{ client.nombre_credits }}</td>
                <td :style="pdfTdStyle">{{ formatMoney(client.total_dette) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div :style="pdfSectionStyle">
          <h3 :style="pdfSectionTitleStyle">Crédits en retard</h3>
          <table :style="pdfTableStyle">
            <thead>
              <tr :style="pdfTheadRowStyle">
                <th :style="pdfThStyle">Client</th>
                <th :style="pdfThStyle">Description</th>
                <th :style="pdfThStyle">Reste</th>
                <th :style="pdfThStyle">Retard</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="credit in dashboard.credits_retard" :key="credit.credit_id" :style="pdfTbodyRowStyle">
                <td :style="pdfTdStyle">{{ credit.client_nom }}</td>
                <td :style="pdfTdStyle">{{ credit.description.substring(0, 40) }}...</td>
                <td :style="pdfTdStyle">{{ formatMoney(credit.montant_restant) }}</td>
                <td :style="pdfTdStyle">{{ credit.jours_retard }} j</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div :style="pdfFooterStyle">
          <p>Système de gestion des clients - {{ new Date().getFullYear() }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({
  apiBaseUrl: {
    type: String,
    required: true
  }
})

const loading = ref(false)
const exporting = ref(false)
const dashboard = ref(null)
const exportHovered = ref(false)


const loadDashboard = async () => {
  loading.value = true
  try {
    const response = await fetch(`${props.apiBaseUrl}/api_clients.php?action=rapport_global`)
    const data = await response.json()
    if (data.success) {
      dashboard.value = cleanDashboardData(data.data)
    }
  } catch (error) {
    console.error('Erreur:', error)
    alert('Erreur de chargement du dashboard')
  } finally {
    loading.value = false
  }
}

const exporterPDF = async () => {
  if (!dashboard.value) return
  
  exporting.value = true
  try {
    const html2canvas = (await import('html2canvas')).default
    const jsPDF = (await import('jspdf')).default
    
    const element = document.getElementById('pdf-content-dashboard')
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
    
    const filename = `dashboard_credits_${new Date().toISOString().split('T')[0]}.pdf`
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

onMounted(() => {
  loadDashboard()
})

// Styles
const loadingStyle = { textAlign: 'center', padding: '60px 20px' }
const loadingTextStyle = { marginTop: '16px', fontSize: '16px', fontWeight: '500', color: '#64748b' }
const spinnerStyle = { width: '48px', height: '48px', margin: '0 auto', border: '4px solid #f1f5f9', borderTop: '4px solid #10b981', borderRadius: '50%', animation: 'spin 1s linear infinite' }
const smallSpinnerStyle = { width: '18px', height: '18px', border: '2px solid rgba(255,255,255,0.3)', borderTop: '2px solid white', borderRadius: '50%', animation: 'spin 1s linear infinite' }

const statsGridStyle = { display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))', gap: '20px', marginBottom: '32px' }
const statsCardStyle = { backgroundColor: 'white', borderRadius: '20px', padding: '24px', display: 'flex', alignItems: 'center', gap: '16px', boxShadow: '0 2px 8px rgba(0,0,0,0.06)', border: '1px solid #f1f5f9' }
const statsIconStyle = (color) => ({ width: '56px', height: '56px', background: `${color}12`, borderRadius: '16px', display: 'flex', alignItems: 'center', justifyContent: 'center', color: color, flexShrink: 0 })
const statsLabelStyle = { fontSize: '13px', color: '#64748b', margin: '0 0 4px 0', fontWeight: '500' }
const statsValueStyle = { fontSize: '26px', fontWeight: '700', color: '#0f172a', margin: '0', letterSpacing: '-0.02em' }

const sectionStyle = { backgroundColor: 'white', borderRadius: '20px', padding: '24px', boxShadow: '0 2px 8px rgba(0,0,0,0.06)', border: '1px solid #f1f5f9', marginBottom: '24px' }
const sectionHeaderStyle = { marginBottom: '20px' }
const sectionTitleStyle = { fontSize: '18px', fontWeight: '700', color: '#0f172a', margin: 0 }

const tableWrapperStyle = { overflowX: 'auto', borderRadius: '12px', border: '1px solid #e2e8f0' }
const tableStyle = { width: '100%', borderCollapse: 'collapse', minWidth: '800px' }
const thStyle = { textAlign: 'left', padding: '16px', fontSize: '12px', fontWeight: '700', color: '#64748b', textTransform: 'uppercase', backgroundColor: '#f8fafc', borderBottom: '2px solid #e2e8f0', letterSpacing: '0.05em' }
const trStyle = { borderBottom: '1px solid #f1f5f9', transition: 'background-color 0.2s' }
const tdStyle = { padding: '16px', fontSize: '14px', color: '#1e293b' }

const rankStyle = (index) => ({ display: 'inline-flex', alignItems: 'center', justifyContent: 'center', width: '32px', height: '32px', borderRadius: '8px', fontWeight: '700', fontSize: '14px', backgroundColor: index === 0 ? '#fef3c7' : index === 1 ? '#e0e7ff' : index === 2 ? '#fee2e2' : '#f1f5f9', color: index === 0 ? '#92400e' : index === 1 ? '#4338ca' : index === 2 ? '#991b1b' : '#64748b' })

const clientNameStyle = { margin: '0', fontWeight: '600', fontSize: '14px', color: '#0f172a' }
const phoneStyle = { fontSize: '13px', color: '#64748b', fontFamily: 'monospace', fontWeight: '500' }
const creditCountStyle = { fontWeight: '700', fontSize: '16px', color: '#3b82f6' }
const detteStyle = { fontWeight: '700', fontSize: '16px', color: '#ef4444' }
const descriptionStyle = { margin: '0', fontSize: '14px', color: '#1e293b', fontWeight: '500' }
const echeanceStyle = { fontSize: '14px', color: '#ef4444', fontWeight: '600' }
const retardBadgeStyle = (jours) => ({ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: '600', backgroundColor: jours > 30 ? '#fee2e2' : jours > 7 ? '#fef3c7' : '#fed7aa', color: jours > 30 ? '#991b1b' : jours > 7 ? '#92400e' : '#9a3412' })

const emptyStateStyle = { display: 'flex', justifyContent: 'center', padding: '40px 20px' }
const emptyTextStyle = { fontSize: '16px', color: '#10b981', fontWeight: '600' }

const exportContainerStyle = { display: 'flex', justifyContent: 'flex-end', marginTop: '24px' }
const exportButtonStyle = computed(() => ({ display: 'flex', alignItems: 'center', gap: '10px', padding: '14px 24px', background: exportHovered.value && !exporting.value ? 'linear-gradient(135deg, #dc2626 0%, #b91c1c 100%)' : 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)', color: 'white', border: 'none', borderRadius: '12px', cursor: exporting.value ? 'not-allowed' : 'pointer', fontSize: '14px', fontWeight: '600', opacity: exporting.value ? 0.6 : 1, boxShadow: '0 4px 12px rgba(239,68,68,0.25)', transition: 'all 0.2s' }))

// PDF Styles
const pdfContentStyle = { display: 'none', position: 'fixed', left: '-9999px', top: 0 }
const pdfInnerStyle = { width: '210mm', backgroundColor: 'white', padding: '20mm', fontFamily: 'Arial, sans-serif' }
const pdfHeaderStyle = { display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '30px', paddingBottom: '20px', borderBottom: '3px solid #10b981' }
const pdfLogoStyle = { fontSize: '48px' }
const pdfTitleStyle = { fontSize: '24px', fontWeight: '700', color: '#0f172a', margin: '0 0 8px 0' }
const pdfDateStyle = { fontSize: '12px', color: '#94a3b8', margin: '0' }
const pdfStatsGridStyle = { display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: '15px', marginBottom: '30px' }
const pdfStatCardStyle = { backgroundColor: '#f8fafc', padding: '15px', borderRadius: '8px', border: '1px solid #e2e8f0' }
const pdfStatLabelStyle = { fontSize: '11px', color: '#64748b', margin: '0 0 6px 0', fontWeight: '600', textTransform: 'uppercase' }
const pdfStatValueStyle = { fontSize: '20px', fontWeight: '700', color: '#0f172a', margin: '0' }
const pdfSectionStyle = { marginBottom: '30px' }
const pdfSectionTitleStyle = { fontSize: '16px', fontWeight: '700', color: '#0f172a', margin: '0 0 15px 0' }
const pdfTableStyle = { width: '100%', borderCollapse: 'collapse', fontSize: '10px' }
const pdfTheadRowStyle = { backgroundColor: '#f8fafc' }
const pdfThStyle = { textAlign: 'left', padding: '10px 8px', fontSize: '9px', fontWeight: '700', color: '#64748b', textTransform: 'uppercase', borderBottom: '2px solid #e2e8f0' }
const pdfTbodyRowStyle = { borderBottom: '1px solid #f1f5f9' }
const pdfTdStyle = { padding: '10px 8px', fontSize: '10px', color: '#1e293b' }
const pdfFooterStyle = { marginTop: '40px', paddingTop: '20px', borderTop: '2px solid #e2e8f0', textAlign: 'center', fontSize: '10px', color: '#94a3b8' }
</script>

<style scoped>
@keyframes spin {
  to { transform: rotate(360deg); }
}
tr:hover { background-color: #f8fafc; }
</style>