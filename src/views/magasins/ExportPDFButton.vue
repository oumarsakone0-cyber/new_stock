<!-- Composant d'export PDF côté frontend -->
<template>
  <div>
    <!-- Bouton d'export -->
    <button 
      :style="exportButtonStyle"
      @click="exportToPDF"
      @mouseenter="exportHovered = true"
      @mouseleave="exportHovered = false"
      :disabled="loading"
    >
      <div v-if="loading" :style="spinnerStyle"></div>
      <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/>
      </svg>
      {{ loading ? 'Génération...' : 'Exporter PDF' }}
    </button>

    <!-- Contenu caché pour l'export (ne sera pas visible) -->
    <div ref="pdfContent" :style="pdfContainerStyle">
      <!-- En-tête -->
      <div :style="pdfHeaderStyle">
        <div :style="pdfLogoStyle">🏪</div>
        <h1 :style="pdfMagasinNameStyle">{{ magasinInfo.nom }}</h1>
        <div :style="pdfMagasinInfoStyle">
          <p>📍 {{ magasinInfo.adresse }}</p>
          <p>📞 {{ magasinInfo.telephone }}</p>
        </div>
      </div>

      <!-- Titre du rapport -->
      <div :style="pdfTitleSectionStyle">
        <h2 :style="pdfTitleStyle">RAPPORT DES VENTES</h2>
        <p :style="pdfPeriodeStyle">{{ periodeLibelle }}</p>
        <p :style="pdfDateStyle">Généré le {{ formatDate(new Date()) }}</p>
      </div>

      <!-- Totaux -->
      <div :style="pdfTotauxGridStyle">
        <div :style="pdfTotauxCardStyle">
          <div :style="pdfTotauxLabelStyle">Nombre de ventes</div>
          <div :style="pdfTotauxValueStyle">{{ totaux.nombre_ventes }}</div>
        </div>
        
        <div :style="pdfTotauxCardStyle">
          <div :style="pdfTotauxLabelStyle">Total vendu</div>
          <div :style="pdfTotauxValueStyle">{{ formatMoney(totaux.total_vendu) }}</div>
        </div>
        
        <div :style="pdfTotauxCardStyle">
          <div :style="pdfTotauxLabelStyle">Total encaissé</div>
          <div :style="{...pdfTotauxValueStyle, color: '#10b981'}">{{ formatMoney(totaux.total_encaisse) }}</div>
        </div>
        
        <div :style="pdfTotauxCardStyle">
          <div :style="pdfTotauxLabelStyle">Total en crédit</div>
          <div :style="{...pdfTotauxValueStyle, color: '#ef4444'}">{{ formatMoney(totaux.total_credit) }}</div>
        </div>
        
        <div :style="pdfTotauxCardStyle">
          <div :style="pdfTotauxLabelStyle">Taux de recouvrement</div>
          <div :style="pdfTotauxValueStyle">{{ totaux.taux_recouvrement }} %</div>
        </div>
      </div>

      <!-- Tableau des ventes -->
      <div :style="pdfTableSectionStyle">
        <h3 :style="pdfTableTitleStyle">Détail des ventes</h3>
        
        <table :style="pdfTableStyle">
          <thead>
            <tr :style="pdfTheadRowStyle">
              <th :style="pdfThStyle">Réf</th>
              <th :style="pdfThStyle">Date</th>
              <th :style="pdfThStyle">Client</th>
              <th :style="pdfThStyle">Montant</th>
              <th :style="pdfThStyle">Payé</th>
              <th :style="pdfThStyle">Statut</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="vente in ventes" :key="vente.id" :style="pdfTrStyle">
              <td :style="pdfTdStyle"><strong>#{{ vente.id }}</strong></td>
              <td :style="pdfTdStyle">{{ vente.date_vente }}</td>
              <td :style="pdfTdStyle">{{ vente.client_nom || 'Client anonyme' }}</td>
              <td :style="pdfTdStyle">{{ formatMoney(vente.montant_total) }}</td>
              <td :style="pdfTdStyle">{{ formatMoney(vente.montant_paye) }}</td>
              <td :style="pdfTdStyle">
                <span :style="getPdfBadgeStyle(vente.statut)">{{ vente.statut }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pied de page -->
      <div :style="pdfFooterStyle">
        <p>Ce document a été généré automatiquement par le système de gestion</p>
        <p><strong>{{ magasinInfo.nom }}</strong> - {{ new Date().getFullYear() }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, defineProps } from 'vue'
import html2canvas from 'html2canvas'
import jsPDF from 'jspdf'

const props = defineProps({
  magasinInfo: {
    type: Object,
    required: true
  },
  periodeLibelle: {
    type: String,
    default: 'Toutes les ventes'
  },
  totaux: {
    type: Object,
    required: true
  },
  ventes: {
    type: Array,
    required: true
  }
})

// State
const loading = ref(false)
const exportHovered = ref(false)
const pdfContent = ref(null)

// Methods
const formatMoney = (amount) => {
  return new Intl.NumberFormat('fr-FR', { 
    style: 'currency', 
    currency: 'EUR', 
    minimumFractionDigits: 0 
  }).format(amount)
}

const formatDate = (date) => {
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
    // Afficher temporairement le contenu
    pdfContent.value.style.display = 'block'
    
    // Attendre que le contenu soit rendu
    await new Promise(resolve => setTimeout(resolve, 100))
    
    // Capturer le contenu HTML
    const canvas = await html2canvas(pdfContent.value, {
      scale: 2,
      useCORS: true,
      logging: false,
      backgroundColor: '#ffffff'
    })
    
    // Cacher le contenu
    pdfContent.value.style.display = 'none'
    
    // Créer le PDF
    const imgWidth = 210 // A4 width in mm
    const pageHeight = 297 // A4 height in mm
    const imgHeight = (canvas.height * imgWidth) / canvas.width
    let heightLeft = imgHeight
    
    const pdf = new jsPDF('p', 'mm', 'a4')
    let position = 0
    
    // Ajouter l'image au PDF
    const imgData = canvas.toDataURL('image/png')
    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight)
    heightLeft -= pageHeight
    
    // Ajouter des pages supplémentaires si nécessaire
    while (heightLeft > 0) {
      position = heightLeft - imgHeight
      pdf.addPage()
      pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight)
      heightLeft -= pageHeight
    }
    
    // Télécharger le PDF
    const filename = `rapport_ventes_${new Date().getTime()}.pdf`
    pdf.save(filename)
    
  } catch (error) {
    console.error('Erreur lors de la génération du PDF:', error)
    alert('Erreur lors de la génération du PDF. Veuillez réessayer.')
  } finally {
    loading.value = false
  }
}

// Styles
const exportButtonStyle = computed(() => ({
  display: 'flex',
  alignItems: 'center',
  gap: '8px',
  padding: '10px 20px',
  background: exportHovered.value && !loading.value
    ? 'linear-gradient(135deg, #dc2626 0%, #b91c1c 100%)'
    : 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)',
  color: 'white',
  border: 'none',
  borderRadius: '10px',
  cursor: loading.value ? 'not-allowed' : 'pointer',
  fontSize: '14px',
  fontWeight: '600',
  transition: 'all 0.2s',
  boxShadow: '0 4px 12px rgba(239, 68, 68, 0.25)',
  opacity: loading.value ? 0.7 : 1
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
  width: '794px', // A4 width in pixels (210mm at 96dpi)
  backgroundColor: 'white',
  padding: '40px',
  fontFamily: 'Arial, sans-serif',
  display: 'none'
}

const pdfHeaderStyle = {
  textAlign: 'center',
  marginBottom: '30px',
  paddingBottom: '20px',
  borderBottom: '3px solid #10b981'
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
  backgroundColor: '#f8fafc',
  borderRadius: '8px'
}

const pdfTitleStyle = {
  fontSize: '24px',
  fontWeight: 'bold',
  color: '#0f172a',
  margin: '0 0 10px 0'
}

const pdfPeriodeStyle = {
  fontSize: '16px',
  color: '#64748b',
  margin: '5px 0'
}

const pdfDateStyle = {
  fontSize: '12px',
  color: '#94a3b8',
  margin: '5px 0'
}

const pdfTotauxGridStyle = {
  display: 'grid',
  gridTemplateColumns: 'repeat(5, 1fr)',
  gap: '15px',
  marginBottom: '30px'
}

const pdfTotauxCardStyle = {
  padding: '15px',
  backgroundColor: '#f8fafc',
  border: '2px solid #e2e8f0',
  borderRadius: '8px',
  textAlign: 'center'
}

const pdfTotauxLabelStyle = {
  fontSize: '11px',
  color: '#64748b',
  fontWeight: 'bold',
  textTransform: 'uppercase',
  marginBottom: '8px'
}

const pdfTotauxValueStyle = {
  fontSize: '20px',
  fontWeight: 'bold',
  color: '#0f172a'
}

const pdfTableSectionStyle = {
  marginBottom: '30px'
}

const pdfTableTitleStyle = {
  fontSize: '18px',
  fontWeight: 'bold',
  color: '#0f172a',
  marginBottom: '15px',
  paddingBottom: '10px',
  borderBottom: '2px solid #10b981'
}

const pdfTableStyle = {
  width: '100%',
  borderCollapse: 'collapse',
  fontSize: '12px'
}

const pdfTheadRowStyle = {
  backgroundColor: '#0f172a'
}

const pdfThStyle = {
  padding: '12px 8px',
  textAlign: 'left',
  color: 'white',
  fontWeight: 'bold',
  fontSize: '11px',
  textTransform: 'uppercase'
}

const pdfTrStyle = {
  borderBottom: '1px solid #e2e8f0'
}

const pdfTdStyle = {
  padding: '10px 8px',
  color: '#1e293b'
}

const getPdfBadgeStyle = (statut) => ({
  display: 'inline-block',
  padding: '4px 8px',
  borderRadius: '4px',
  fontSize: '10px',
  fontWeight: 'bold',
  backgroundColor: statut === 'Paye' ? '#dcfce7' : statut === 'Partiel' ? '#fef3c7' : '#fee2e2',
  color: statut === 'Paye' ? '#166534' : statut === 'Partiel' ? '#92400e' : '#991b1b'
})

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