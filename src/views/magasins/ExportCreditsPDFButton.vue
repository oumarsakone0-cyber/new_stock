<template>
  <button 
    :style="exportButtonStyle"
    @click="exportToPDF"
    @mouseenter="exportHovered = true"
    @mouseleave="exportHovered = false"
    :disabled="loading || credits.length === 0"
  >
    <div v-if="loading" :style="spinnerStyle"></div>
    <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/>
    </svg>
    {{ loading ? 'Génération...' : 'Exporter les crédits en PDF' }}
  </button>

  <!-- Contenu caché pour PDF -->
  <div ref="pdfContent" :style="pdfContainerStyle">
    <!-- En-tête -->
    <div :style="pdfHeaderStyle">
      <div :style="pdfLogoStyle">💳</div>
      <h1 :style="pdfMagasinNameStyle">{{ magasinInfo.nom }}</h1>
      <div :style="pdfMagasinInfoStyle">
        <p>📍 {{ magasinInfo.adresse }}</p>
        <p>📞 {{ magasinInfo.telephone }}</p>
      </div>
    </div>

    <!-- Titre -->
    <div :style="pdfTitleSectionStyle">
      <h2 :style="pdfTitleStyle">LISTE DES CRÉDITS EN COURS</h2>
      <p :style="pdfDateStyle">Généré le {{ formatDateTime(new Date()) }}</p>
    </div>

    <!-- Statistiques -->
    <div :style="pdfStatsGridStyle">
      <div :style="pdfStatsCardStyle">
        <div :style="pdfStatsLabelStyle">Nombre de clients</div>
        <div :style="pdfStatsValueStyle">{{ credits.length }}</div>
      </div>
      
      <div :style="pdfStatsCardStyle">
        <div :style="pdfStatsLabelStyle">Total des crédits</div>
        <div :style="pdfStatsValueStyle">{{ formatMoney(totalCredits) }}</div>
      </div>
      
      <div :style="pdfStatsCardStyle">
        <div :style="pdfStatsLabelStyle">Déjà encaissé</div>
        <div :style="pdfStatsValueStyle">{{ formatMoney(totalPaye) }}</div>
      </div>

      <div :style="pdfStatsCardStyle">
        <div :style="pdfStatsLabelStyle">Reste à encaisser</div>
        <div :style="pdfStatsValueRestStyle">{{ formatMoney(totalReste) }}</div>
      </div>
    </div>

    <!-- Table des crédits -->
    <div :style="pdfSectionStyle">
      <table :style="pdfTableStyle">
        <thead>
          <tr :style="pdfTheadRowStyle">
            <th :style="pdfThStyle">Client</th>
            <th :style="pdfThStyle">Téléphone</th>
            <th :style="pdfThStyle">Réf Vente</th>
            <th :style="pdfThStyle">Montant Total</th>
            <th :style="pdfThStyle">Déjà Payé</th>
            <th :style="pdfThStyle">Reste à Payer</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="credit in credits" :key="credit.id" :style="pdfTrStyle">
            <td :style="pdfTdStyle">
              <strong>{{ credit.client }}</strong>
            </td>
            <td :style="pdfTdStyle">{{ credit.telephone || 'N/A' }}</td>
            <td :style="pdfTdRefStyle">#{{ credit.venteId }}</td>
            <td :style="pdfTdStyle">{{ formatMoney(credit.montantTotal) }}</td>
            <td :style="pdfTdPayeStyle">{{ formatMoney(credit.paye) }}</td>
            <td :style="pdfTdResteStyle">{{ formatMoney(credit.montantTotal - credit.paye) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pied de page -->
    <div :style="pdfFooterStyle">
      <p>Ce rapport a été généré automatiquement par le système de gestion</p>
      <p><strong>{{ magasinInfo.nom }}</strong> - {{ new Date().getFullYear() }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, defineProps } from 'vue'
import html2canvas from 'html2canvas'
import jsPDF from 'jspdf'

const props = defineProps({
  credits: {
    type: Array,
    required: true
  },
  magasinInfo: {
    type: Object,
    required: true
  }
})

const loading = ref(false)
const exportHovered = ref(false)
const pdfContent = ref(null)

// Computed
const totalCredits = computed(() => {
  return props.credits.reduce((sum, c) => sum + c.montantTotal, 0)
})

const totalPaye = computed(() => {
  return props.credits.reduce((sum, c) => sum + c.paye, 0)
})

const totalReste = computed(() => {
  return props.credits.reduce((sum, c) => sum + (c.montantTotal - c.paye), 0)
})

// Methods
const formatMoney = (amount) => {
  return new Intl.NumberFormat('fr-FR', { 
    style: 'currency', 
    currency: 'EUR',
    minimumFractionDigits: 0 
  }).format(amount)
}

const formatDateTime = (date) => {
  return new Intl.DateTimeFormat('fr-FR', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date)
}

const exportToPDF = async () => {
  loading.value = true
  
  try {
    // Afficher le contenu
    pdfContent.value.style.display = 'block'
    await new Promise(resolve => setTimeout(resolve, 100))
    
    // Capturer avec html2canvas
    const canvas = await html2canvas(pdfContent.value, {
      scale: 2,
      useCORS: true,
      logging: false,
      backgroundColor: '#ffffff'
    })
    
    // Masquer le contenu
    pdfContent.value.style.display = 'none'
    
    // Créer le PDF
    const pdf = new jsPDF('p', 'mm', 'a4')
    const imgWidth = 210 // A4 width
    const imgHeight = (canvas.height * imgWidth) / canvas.width
    let heightLeft = imgHeight
    
    const imgData = canvas.toDataURL('image/png')
    let position = 0
    
    // Première page
    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight)
    heightLeft -= 297 // A4 height
    
    // Pages supplémentaires si nécessaire
    while (heightLeft > 0) {
      position = heightLeft - imgHeight
      pdf.addPage()
      pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight)
      heightLeft -= 297
    }
    
    // Télécharger
    const filename = `credits_${Date.now()}.pdf`
    pdf.save(filename)
    
  } catch (error) {
    console.error('Erreur PDF:', error)
    alert('Erreur lors de la génération du PDF')
  } finally {
    loading.value = false
  }
}

// Styles
const exportButtonStyle = computed(() => ({
  display: 'flex',
  alignItems: 'center',
  gap: '8px',
  padding: '12px 20px',
  background: exportHovered.value && !loading.value && props.credits.length > 0
    ? 'linear-gradient(135deg, #dc2626 0%, #b91c1c 100%)'
    : 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)',
  color: 'white',
  border: 'none',
  borderRadius: '12px',
  cursor: (loading.value || props.credits.length === 0) ? 'not-allowed' : 'pointer',
  fontSize: '14px',
  fontWeight: '600',
  transition: 'all 0.2s',
  boxShadow: '0 4px 12px rgba(239, 68, 68, 0.25)',
  opacity: (loading.value || props.credits.length === 0) ? 0.6 : 1,
  letterSpacing: '0.01em'
}))

const spinnerStyle = {
  width: '16px',
  height: '16px',
  border: '2px solid rgba(255, 255, 255, 0.3)',
  borderTop: '2px solid white',
  borderRadius: '50%',
  animation: 'spin 1s linear infinite'
}

const pdfContainerStyle = {
  position: 'fixed',
  top: '-9999px',
  left: '-9999px',
  width: '794px',
  backgroundColor: 'white',
  padding: '40px',
  fontFamily: 'Arial, sans-serif',
  display: 'none'
}

const pdfHeaderStyle = {
  textAlign: 'center',
  marginBottom: '30px',
  paddingBottom: '20px',
  borderBottom: '3px solid #ef4444'
}

const pdfLogoStyle = {
  fontSize: '48px',
  marginBottom: '10px'
}

const pdfMagasinNameStyle = {
  fontSize: '28px',
  fontWeight: 'bold',
  color: '#0f172a',
  margin: '10px 0'
}

const pdfMagasinInfoStyle = {
  fontSize: '14px',
  color: '#64748b',
  lineHeight: '1.6'
}

const pdfTitleSectionStyle = {
  textAlign: 'center',
  marginBottom: '30px',
  padding: '20px',
  backgroundColor: '#fef2f2',
  borderRadius: '8px',
  border: '2px solid #fecaca'
}

const pdfTitleStyle = {
  fontSize: '24px',
  fontWeight: 'bold',
  color: '#0f172a',
  margin: '0 0 10px 0'
}

const pdfDateStyle = {
  fontSize: '12px',
  color: '#94a3b8',
  margin: '5px 0'
}

const pdfStatsGridStyle = {
  display: 'grid',
  gridTemplateColumns: 'repeat(4, 1fr)',
  gap: '15px',
  marginBottom: '30px'
}

const pdfStatsCardStyle = {
  padding: '15px',
  backgroundColor: '#f8fafc',
  border: '2px solid #e2e8f0',
  borderRadius: '8px',
  textAlign: 'center'
}

const pdfStatsLabelStyle = {
  fontSize: '11px',
  color: '#64748b',
  fontWeight: 'bold',
  textTransform: 'uppercase',
  marginBottom: '8px'
}

const pdfStatsValueStyle = {
  fontSize: '20px',
  fontWeight: 'bold',
  color: '#0f172a'
}

const pdfStatsValueRestStyle = {
  fontSize: '20px',
  fontWeight: 'bold',
  color: '#ef4444'
}

const pdfSectionStyle = {
  marginBottom: '25px',
  pageBreakInside: 'avoid'
}

const pdfTableStyle = {
  width: '100%',
  borderCollapse: 'collapse',
  fontSize: '11px',
  marginBottom: '10px'
}

const pdfTheadRowStyle = {
  backgroundColor: '#0f172a'
}

const pdfThStyle = {
  padding: '12px 8px',
  textAlign: 'left',
  color: 'white',
  fontWeight: 'bold',
  fontSize: '10px',
  textTransform: 'uppercase',
  borderBottom: '2px solid #ef4444'
}

const pdfTrStyle = {
  borderBottom: '1px solid #e2e8f0'
}

const pdfTdStyle = {
  padding: '10px 8px',
  color: '#1e293b',
  fontSize: '11px'
}

const pdfTdRefStyle = {
  padding: '10px 8px',
  color: '#3b82f6',
  fontSize: '11px',
  fontWeight: 'bold'
}

const pdfTdPayeStyle = {
  padding: '10px 8px',
  color: '#10b981',
  fontSize: '11px',
  fontWeight: 'bold'
}

const pdfTdResteStyle = {
  padding: '10px 8px',
  color: '#ef4444',
  fontSize: '12px',
  fontWeight: 'bold'
}

const pdfFooterStyle = {
  marginTop: '40px',
  paddingTop: '20px',
  borderTop: '2px solid #e2e8f0',
  textAlign: 'center',
  fontSize: '11px',
  color: '#94a3b8',
  lineHeight: '1.6'
}
</script>

<style scoped>
@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>