<template>
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

  <!-- Contenu caché pour PDF -->
  <div ref="pdfContent" :style="pdfContainerStyle">
    <!-- En-tête -->
    <div :style="pdfHeaderStyle">
      <div :style="pdfLogoStyle">📦</div>
      <h1 :style="pdfMagasinNameStyle">{{ magasinInfo.nom }}</h1>
      <div :style="pdfMagasinInfoStyle">
        <p>📍 {{ magasinInfo.adresse }}</p>
        <p>📞 {{ magasinInfo.telephone }}</p>
      </div>
    </div>

    <!-- Titre -->
    <div :style="pdfTitleSectionStyle">
      <h2 :style="pdfTitleStyle">RAPPORT DE STOCK PAR PÉRIODE</h2>
      <p :style="pdfPeriodeStyle">{{ periode.libelle }}</p>
      <p :style="pdfDateStyle">Généré le {{ formatDate(new Date()) }}</p>
    </div>

    <!-- Statistiques -->
    <div :style="pdfStatsGridStyle">
      <div :style="pdfStatsCardStyle">
        <div :style="pdfStatsLabelStyle">Total entrées</div>
        <div :style="pdfStatsValueStyle">{{ statistiques.total_entrees }}</div>
        <div :style="pdfStatsSubStyle">{{ statistiques.nombre_entrees }} mouvements</div>
      </div>
      
      <div :style="pdfStatsCardStyle">
        <div :style="pdfStatsLabelStyle">Total sorties</div>
        <div :style="pdfStatsValueStyle">{{ statistiques.total_sorties }}</div>
        <div :style="pdfStatsSubStyle">{{ statistiques.nombre_sorties }} mouvements</div>
      </div>
      
      <div :style="pdfStatsCardStyle">
        <div :style="pdfStatsLabelStyle">Variation nette</div>
        <div :style="{...pdfStatsValueStyle, color: statistiques.variation_nette >= 0 ? '#10b981' : '#ef4444'}">
          {{ statistiques.variation_nette >= 0 ? '+' : '' }}{{ statistiques.variation_nette }}
        </div>
        <div :style="pdfStatsSubStyle">{{ statistiques.nombre_mouvements }} mouvements</div>
      </div>
    </div>

    <!-- Stock avant -->
    <div :style="pdfSectionStyle">
      <h3 :style="pdfSectionTitleStyle">📦 Stock au début de la période</h3>
      <table :style="pdfTableStyle">
        <thead>
          <tr :style="pdfTheadRowStyle">
            <th :style="pdfThStyle">Produit</th>
            <th :style="pdfThStyle">Stock début</th>
            <th :style="pdfThStyle">Seuil</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in stockAvant.slice(0, 15)" :key="p.id" :style="pdfTrStyle">
            <td :style="pdfTdStyle">
              <strong>{{ p.nom }}</strong><br>
              <small style="color: #94a3b8;">{{ p.sku }}</small>
            </td>
            <td :style="pdfTdStyle"><strong>{{ p.stock_debut_periode }}</strong></td>
            <td :style="pdfTdStyle">{{ p.seuil_alerte }}</td>
          </tr>
        </tbody>
      </table>
      <p v-if="stockAvant.length > 15" :style="pdfNoteStyle">
        + {{ stockAvant.length - 15 }} autres produits
      </p>
    </div>

    <!-- Mouvements -->
    <div :style="pdfSectionStyle">
      <h3 :style="pdfSectionTitleStyle">📋 Mouvements pendant la période</h3>
      <table :style="pdfTableStyle">
        <thead>
          <tr :style="pdfTheadRowStyle">
            <th :style="pdfThStyle">Date</th>
            <th :style="pdfThStyle">Type</th>
            <th :style="pdfThStyle">Produit</th>
            <th :style="pdfThStyle">Quantité</th>
            <th :style="pdfThStyle">Motif</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="m in mouvements.slice(0, 20)" :key="m.id" :style="pdfTrStyle">
            <td :style="pdfTdStyle">{{ formatDateShort(m.date_mouvement) }}</td>
            <td :style="pdfTdStyle">
              <span :style="getPdfBadgeStyle(m.type_mouvement)">
                {{ m.type_mouvement === 'entree' ? 'E' : 'S' }}
              </span>
            </td>
            <td :style="pdfTdStyle">{{ m.produit_nom }}</td>
            <td :style="pdfTdStyle">
              <span :style="{fontWeight: 'bold', color: m.type_mouvement === 'entree' ? '#10b981' : '#f59e0b'}">
                {{ m.type_mouvement === 'entree' ? '+' : '-' }}{{ m.quantite }}
              </span>
            </td>
            <td :style="pdfTdStyle">{{ m.motif || 'N/A' }}</td>
          </tr>
        </tbody>
      </table>
      <p v-if="mouvements.length > 20" :style="pdfNoteStyle">
        + {{ mouvements.length - 20 }} autres mouvements
      </p>
    </div>

    <!-- Stock après -->
    <div :style="pdfSectionStyle">
      <h3 :style="pdfSectionTitleStyle">📦 Stock à la fin de la période</h3>
      <table :style="pdfTableStyle">
        <thead>
          <tr :style="pdfTheadRowStyle">
            <th :style="pdfThStyle">Produit</th>
            <th :style="pdfThStyle">Stock fin</th>
            <th :style="pdfThStyle">Statut</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in stockApres.slice(0, 15)" :key="p.id" :style="pdfTrStyle">
            <td :style="pdfTdStyle">
              <strong>{{ p.nom }}</strong><br>
              <small style="color: #94a3b8;">{{ p.sku }}</small>
            </td>
            <td :style="pdfTdStyle"><strong>{{ p.stock_fin_periode }}</strong></td>
            <td :style="pdfTdStyle">
              <span :style="getPdfStatusBadgeStyle(p.statut)">{{ p.statut }}</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Variations -->
    <div :style="pdfSectionStyle">
      <h3 :style="pdfSectionTitleStyle">📊 Variations de stock</h3>
      <table :style="pdfTableStyle">
        <thead>
          <tr :style="pdfTheadRowStyle">
            <th :style="pdfThStyle">Produit</th>
            <th :style="pdfThStyle">Début</th>
            <th :style="pdfThStyle">Fin</th>
            <th :style="pdfThStyle">Variation</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="v in variations.slice(0, 15)" :key="v.produit_id" :style="pdfTrStyle">
            <td :style="pdfTdStyle"><strong>{{ v.produit_nom }}</strong></td>
            <td :style="pdfTdStyle">{{ v.stock_debut }}</td>
            <td :style="pdfTdStyle">{{ v.stock_fin }}</td>
            <td :style="pdfTdStyle">
              <span :style="{fontWeight: 'bold', color: v.variation >= 0 ? '#10b981' : '#ef4444'}">
                {{ v.variation >= 0 ? '+' : '' }}{{ v.variation }} ({{ v.variation_pourcent }}%)
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Footer -->
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
  magasinInfo: { type: Object, required: true },
  periode: { type: Object, required: true },
  stockAvant: { type: Array, required: true },
  stockApres: { type: Array, required: true },
  mouvements: { type: Array, required: true },
  variations: { type: Array, required: true },
  statistiques: { type: Object, required: true }
})

const loading = ref(false)
const exportHovered = ref(false)
const pdfContent = ref(null)

const formatDate = (date) => {
  return new Intl.DateTimeFormat('fr-FR', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date)
}

const formatDateShort = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' })
}

const exportToPDF = async () => {
  loading.value = true
  
  try {
    pdfContent.value.style.display = 'block'
    await new Promise(resolve => setTimeout(resolve, 100))
    
    const canvas = await html2canvas(pdfContent.value, {
      scale: 2,
      useCORS: true,
      logging: false,
      backgroundColor: '#ffffff'
    })
    
    pdfContent.value.style.display = 'none'
    
    const imgWidth = 210
    const pageHeight = 297
    const imgHeight = (canvas.height * imgWidth) / canvas.width
    let heightLeft = imgHeight
    
    const pdf = new jsPDF('p', 'mm', 'a4')
    let position = 0
    
    const imgData = canvas.toDataURL('image/png')
    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight)
    heightLeft -= pageHeight
    
    while (heightLeft > 0) {
      position = heightLeft - imgHeight
      pdf.addPage()
      pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight)
      heightLeft -= pageHeight
    }
    
    const filename = `rapport_stock_${new Date().getTime()}.pdf`
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

const pdfStatsGridStyle = {
  display: 'grid',
  gridTemplateColumns: 'repeat(3, 1fr)',
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
  fontSize: '24px',
  fontWeight: 'bold',
  color: '#0f172a'
}

const pdfStatsSubStyle = {
  fontSize: '11px',
  color: '#94a3b8',
  marginTop: '4px'
}

const pdfSectionStyle = {
  marginBottom: '25px',
  pageBreakInside: 'avoid'
}

const pdfSectionTitleStyle = {
  fontSize: '16px',
  fontWeight: 'bold',
  color: '#0f172a',
  marginBottom: '12px',
  paddingBottom: '8px',
  borderBottom: '2px solid #10b981'
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
  padding: '10px 8px',
  textAlign: 'left',
  color: 'white',
  fontWeight: 'bold',
  fontSize: '10px',
  textTransform: 'uppercase'
}

const pdfTrStyle = {
  borderBottom: '1px solid #e2e8f0'
}

const pdfTdStyle = {
  padding: '8px',
  color: '#1e293b',
  fontSize: '11px'
}

const getPdfBadgeStyle = (type) => ({
  display: 'inline-block',
  padding: '2px 6px',
  borderRadius: '4px',
  fontSize: '9px',
  fontWeight: 'bold',
  backgroundColor: type === 'entree' ? '#dcfce7' : '#fef3c7',
  color: type === 'entree' ? '#166534' : '#92400e'
})

const getPdfStatusBadgeStyle = (statut) => ({
  display: 'inline-block',
  padding: '2px 6px',
  borderRadius: '4px',
  fontSize: '9px',
  fontWeight: 'bold',
  backgroundColor: statut === 'Rupture' ? '#fee2e2' : statut === 'Stock bas' ? '#fef3c7' : '#dcfce7',
  color: statut === 'Rupture' ? '#991b1b' : statut === 'Stock bas' ? '#92400e' : '#166534'
})

const pdfNoteStyle = {
  fontSize: '11px',
  color: '#94a3b8',
  fontStyle: 'italic',
  marginTop: '8px'
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