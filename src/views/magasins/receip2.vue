<!-- Composant de reçu PDF pour une vente individuelle - Format A4 -->
<template>
  <div>
    <!-- Bouton d'impression du reçu -->
    <button 
        v-if="vente"
      :style="printButtonStyle"
      @click="generateReceipt"
      @mouseenter="printHovered = true"
      @mouseleave="printHovered = false"
      :disabled="loading"
      :title="'Imprimer le reçu #' + vente.id"
    >
      <div v-if="loading" :style="spinnerStyle"></div>
      <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="6 9 6 2 18 2 18 9"/>
        <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
        <rect x="6" y="14" width="12" height="8"/>
      </svg>
    </button>

    <!-- Contenu caché pour l'export (ne sera pas visible) -->
    <div ref="receiptContent" :style="receiptContainerStyle">
      <!-- En-tête avec logo et infos magasin -->
      <div :style="receiptHeaderStyle">
        <div :style="receiptHeaderLeftStyle">
          <div :style="receiptLogoContainerStyle">
            <div :style="receiptLogoStyle">🏪</div>
            <div :style="receiptMagasinInfoStyle">
              <h1 :style="receiptMagasinNameStyle">{{ magasinInfo.nom }}</h1>
              <p v-if="magasinInfo.adresse" :style="receiptAddressStyle">{{ magasinInfo.adresse }}</p>
              <p v-if="magasinInfo.telephone" :style="receiptContactStyle">
                Tél: {{ magasinInfo.telephone }}
                <span v-if="magasinInfo.email"> • Email: {{ magasinInfo.email }}</span>
              </p>
            </div>
          </div>
        </div>
        <div :style="receiptHeaderRightStyle">
          <div :style="receiptTitleBoxStyle" v-if="vente">
            <h2 :style="receiptTitleStyle">REÇU DE VENTE</h2>
            <div :style="receiptRefStyle">N° {{ String(vente.id).padStart(6, '0') }}</div>
          </div>
        </div>
      </div>

      <!-- Informations vente et client -->
      <div :style="receiptInfoGridStyle">
        <!-- Informations de la vente -->
        <div :style="receiptInfoBoxStyle">
          <div :style="receiptBoxTitleStyle">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
              <line x1="16" y1="2" x2="16" y2="6"/>
              <line x1="8" y1="2" x2="8" y2="6"/>
              <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            Informations de la vente
          </div>
          <div :style="receiptInfoLineStyle">
            <span :style="receiptInfoLabelStyle">Date :</span>
            <span :style="receiptInfoValueStyle">{{ formatDate(vente.date_vente) }}</span>
          </div>
          <div :style="receiptInfoLineStyle">
            <span :style="receiptInfoLabelStyle">Heure :</span>
            <span :style="receiptInfoValueStyle">{{ formatTime(vente.date_vente) }}</span>
          </div>
          <div :style="receiptInfoLineStyle">
            <span :style="receiptInfoLabelStyle">Vendeur :</span>
            <span :style="receiptInfoValueStyle">{{ vente.utilisateur || 'Système' }}</span>
          </div>
        </div>

        <!-- Informations client -->
        <div :style="receiptInfoBoxStyle">
          <div :style="receiptBoxTitleStyle">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Informations client
          </div>
          <div :style="receiptInfoLineStyle">
            <span :style="receiptInfoLabelStyle">Nom :</span>
            <span :style="receiptInfoValueStyle">{{ vente.client_nom || 'Client anonyme' }}</span>
          </div>
          <div v-if="vente.client_telephone" :style="receiptInfoLineStyle">
            <span :style="receiptInfoLabelStyle">Téléphone :</span>
            <span :style="receiptInfoValueStyle">{{ vente.client_telephone }}</span>
          </div>
        </div>
      </div>

      <!-- Tableau des articles -->
      <div :style="receiptTableSectionStyle">
        <table :style="receiptTableStyle">
          <thead>
            <tr :style="receiptTheadRowStyle">
              <th :style="receiptThLeftStyle">Désignation</th>
              <th :style="receiptThCenterStyle">Quantité</th>
              <th :style="receiptThRightStyle">Prix unitaire</th>
              <th :style="receiptThRightStyle">Réduction</th>
              <th :style="receiptThRightStyle">Montant TTC</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(ligne, index) in vente.lignes" :key="ligne.id" :style="getReceiptTrStyle(index)">
              <td :style="receiptTdLeftStyle">
                <div :style="receiptProductNameStyle">{{ ligne.produit_nom }}</div>
              </td>
              <td :style="receiptTdCenterStyle">{{ ligne.quantite }}</td>
              <td :style="receiptTdRightStyle">{{ formatMoney(ligne.prix_unitaire) }}</td>
              <td :style="receiptTdRightStyle">
                <span v-if="ligne.reduction > 0" :style="receiptReductionStyle">
                  -{{ formatMoney(ligne.reduction) }}
                </span>
                <span v-else :style="receiptNoReductionStyle">-</span>
              </td>
              <td :style="receiptTdRightBoldStyle">
                {{ formatMoney((ligne.prix_unitaire * ligne.quantite) - ligne.reduction) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Totaux -->
      <div :style="receiptTotalsContainerStyle">
        <div :style="receiptTotalsSummaryStyle">
          <!-- Mention légale importante -->
          <div :style="receiptLegalBoxStyle">
            <div :style="receiptWarningIconStyle">⚠️</div>
            <div>
              <div :style="receiptLegalTitleStyle">ARTICLE NON REMBOURSABLE</div>
              <div :style="receiptLegalSubtitleStyle">Non échangeable sauf défaut de fabrication</div>
            </div>
          </div>
          
          <div :style="receiptNotesStyle">
            <p :style="receiptNoteLineStyle">• Ce reçu fait office de preuve d'achat</p>
            <p :style="receiptNoteLineStyle">• À conserver précieusement</p>
            <p :style="receiptNoteLineStyle">• Garantie selon conditions du fabricant</p>
          </div>
        </div>

        <div :style="receiptTotalsBoxStyle">
          <div :style="receiptTotalLineStyle">
            <span :style="receiptTotalLabelStyle">Sous-total HT</span>
            <span :style="receiptTotalValueStyle">{{ formatMoney(calculateSousTotal()) }}</span>
          </div>
          
          <div v-if="totalReductions > 0" :style="receiptTotalLineStyle">
            <span :style="receiptTotalLabelStyle">Remise totale</span>
            <span :style="receiptReductionTotalStyle">-{{ formatMoney(totalReductions) }}</span>
          </div>
          
          <div :style="receiptTotalLineSeparatorStyle"></div>
          
          <div :style="receiptTotalLineFinalStyle">
            <span :style="receiptTotalLabelFinalStyle">MONTANT TOTAL TTC</span>
            <span :style="receiptTotalValueFinalStyle">{{ formatMoney(vente.montant_total) }}</span>
          </div>
          
          <div :style="receiptTotalLineSeparatorStyle"></div>
          
          <div :style="receiptPaymentLineStyle">
            <span :style="receiptPaymentLabelStyle">Montant payé</span>
            <span :style="receiptPayedValueStyle">{{ formatMoney(vente.montant_paye) }}</span>
          </div>
          
          <div v-if="vente.statut !== 'Paye'" :style="receiptPaymentLineStyle">
            <span :style="receiptPaymentLabelStyle">Reste à payer</span>
            <span :style="receiptResteValueStyle">{{ formatMoney(vente.montant_total - vente.montant_paye) }}</span>
          </div>
          
          <!-- Badge statut -->
          <div :style="receiptStatusContainerStyle">
            <div :style="getReceiptStatusBadgeStyle(vente.statut)">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path v-if="vente.statut === 'Paye'" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                <circle v-else-if="vente.statut === 'Partiel'" cx="12" cy="12" r="10"/>
                <path v-else d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <span>
                {{ vente.statut === 'Paye' ? 'PAYÉ' : vente.statut === 'Partiel' ? 'PAIEMENT PARTIEL' : 'EN CRÉDIT' }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Pied de page -->
      <div :style="receiptFooterStyle">
        <div :style="receiptFooterTopStyle">
          <div :style="receiptThankYouStyle">
            ✨ Merci pour votre confiance et à bientôt ! ✨
          </div>
        </div>
        
        <div :style="receiptFooterBottomStyle">
          <div :style="receiptFooterLeftStyle">
            <p :style="receiptFooterTextStyle"><strong>{{ magasinInfo.nom }}</strong></p>
            <p :style="receiptFooterTextStyle">{{ magasinInfo.adresse }}</p>
            <p :style="receiptFooterTextStyle">{{ magasinInfo.telephone }}</p>
          </div>
          
          <div :style="receiptFooterCenterStyle">
            <p :style="receiptFooterSmallStyle">Document généré le</p>
            <p :style="receiptFooterTextStyle">{{ formatDateTime(new Date()) }}</p>
          </div>
          
          <div :style="receiptFooterRightStyle">
            <div :style="receiptBarcodeStyle" v-if="vente">
              <svg :style="receiptBarcodeSvgStyle" viewBox="0 0 100 40">
                <rect x="5" width="2" height="40" fill="#000"/>
                <rect x="10" width="3" height="40" fill="#000"/>
                <rect x="15" width="2" height="40" fill="#000"/>
                <rect x="20" width="4" height="40" fill="#000"/>
                <rect x="27" width="2" height="40" fill="#000"/>
                <rect x="32" width="3" height="40" fill="#000"/>
                <rect x="38" width="2" height="40" fill="#000"/>
                <rect x="42" width="5" height="40" fill="#000"/>
                <rect x="50" width="2" height="40" fill="#000"/>
                <rect x="55" width="3" height="40" fill="#000"/>
                <rect x="61" width="2" height="40" fill="#000"/>
                <rect x="66" width="4" height="40" fill="#000"/>
                <rect x="73" width="2" height="40" fill="#000"/>
                <rect x="78" width="3" height="40" fill="#000"/>
                <rect x="84" width="2" height="40" fill="#000"/>
                <rect x="89" width="5" height="40" fill="#000"/>
              </svg>
              <p :style="receiptBarcodeTextStyle">{{ String(vente.id).padStart(8, '0') }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, defineProps } from 'vue'
import html2canvas from 'html2canvas'
import jsPDF from 'jspdf'

const props = defineProps({
  vente: {
    type: Object,
    required: true
  },
  magasinInfo: {
    type: Object,
    required: true
  }
})

// State
const loading = ref(false)
const printHovered = ref(false)
const receiptContent = ref(null)

// Computed
const totalReductions = computed(() => {
  if (!props.vente.lignes) return 0
  return props.vente.lignes.reduce((sum, ligne) => sum + (ligne.reduction || 0), 0)
})

// Methods
const calculateSousTotal = () => {
  if (!props.vente.lignes) return props.vente.montant_total
  return props.vente.lignes.reduce((sum, ligne) => {
    return sum + (ligne.prix_unitaire * ligne.quantite)
  }, 0)
}

const formatMoney = (amount) => {
  return new Intl.NumberFormat('fr-FR', { 
    style: 'currency', 
    currency: 'XOF', 
    minimumFractionDigits: 0 
  }).format(amount)
}

const formatDate = (date) => {
  return new Intl.DateTimeFormat('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  }).format(new Date(date))
}

const formatTime = (date) => {
  return new Intl.DateTimeFormat('fr-FR', {
    hour: '2-digit',
    minute: '2-digit'
  }).format(new Date(date))
}

const formatDateTime = (date) => {
  const d = new Date(date)
  if (isNaN(d.getTime())) return ''

  return new Intl.DateTimeFormat('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(d)
}

const generateReceipt = async () => {
  loading.value = true
  
  try {
    // Afficher temporairement le contenu
    receiptContent.value.style.display = 'block'
    
    // Attendre que le contenu soit rendu
    await new Promise(resolve => setTimeout(resolve, 200))
    
    // Capturer le contenu HTML avec haute qualité
    const canvas = await html2canvas(receiptContent.value, {
      scale: 2,
      useCORS: true,
      logging: false,
      backgroundColor: '#ffffff',
      windowWidth: 794,
      windowHeight: 1123
    })
    
    // Cacher le contenu
    receiptContent.value.style.display = 'none'
    
    // Créer le PDF au format A4
    const pdf = new jsPDF({
      orientation: 'portrait',
      unit: 'mm',
      format: 'a4'
    })
    
    // Dimensions A4
    const pdfWidth = 210
    const pdfHeight = 297
    
    // Calculer les dimensions de l'image
    const imgWidth = pdfWidth
    const imgHeight = (canvas.height * pdfWidth) / canvas.width
    
    // Ajouter l'image au PDF
    const imgData = canvas.toDataURL('image/png')
    pdf.addImage(imgData, 'PNG', 0, 0, imgWidth, imgHeight)
    
    // Télécharger le PDF
    const filename = `Recu_N${String(props.vente.id).padStart(6, '0')}_${Date.now()}.pdf`
    pdf.save(filename)
    
  } catch (error) {
    console.error('Erreur lors de la génération du reçu:', error)
    alert('Erreur lors de la génération du reçu. Veuillez réessayer.')
  } finally {
    loading.value = false
  }
}

// Styles
const printButtonStyle = computed(() => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  width: '36px',
  height: '36px',
  borderRadius: '10px',
  border: printHovered.value && !loading.value ? '1px solid #8b5cf6' : '1px solid #8b5cf620',
  backgroundColor: printHovered.value && !loading.value ? '#8b5cf610' : '#8b5cf608',
  cursor: loading.value ? 'not-allowed' : 'pointer',
  color: '#8b5cf6',
  transition: 'all 0.2s',
  opacity: loading.value ? 0.6 : 1
}))

const spinnerStyle = {
  width: '16px',
  height: '16px',
  border: '2px solid #8b5cf620',
  borderTop: '2px solid #8b5cf6',
  borderRadius: '50%',
  animation: 'spin 1s linear infinite'
}

const receiptContainerStyle = {
  position: 'fixed',
  top: '-9999px',
  left: '-9999px',
  width: '794px', // A4 width at 96dpi (210mm)
  minHeight: '1123px', // A4 height at 96dpi (297mm)
  backgroundColor: 'white',
  padding: '60px 50px',
  fontFamily: '"Segoe UI", Arial, sans-serif',
  fontSize: '14px',
  lineHeight: '1.6',
  color: '#1e293b',
  display: 'none',
  boxSizing: 'border-box'
}

const receiptHeaderStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'flex-start',
  marginBottom: '40px',
  paddingBottom: '30px',
  borderBottom: '3px solid #0f172a'
}

const receiptHeaderLeftStyle = {
  flex: 1
}

const receiptLogoContainerStyle = {
  display: 'flex',
  alignItems: 'flex-start',
  gap: '20px'
}

const receiptLogoStyle = {
  fontSize: '60px',
  lineHeight: 1
}

const receiptMagasinInfoStyle = {
  flex: 1
}

const receiptMagasinNameStyle = {
  fontSize: '28px',
  fontWeight: '700',
  color: '#0f172a',
  margin: '0 0 8px 0',
  textTransform: 'uppercase',
  letterSpacing: '0.5px'
}

const receiptAddressStyle = {
  fontSize: '14px',
  color: '#475569',
  margin: '4px 0',
  fontWeight: '500'
}

const receiptContactStyle = {
  fontSize: '13px',
  color: '#64748b',
  margin: '4px 0',
  fontWeight: '500'
}

const receiptHeaderRightStyle = {
  textAlign: 'right'
}

const receiptTitleBoxStyle = {
  padding: '20px 30px',
  backgroundColor: '#0f172a',
  borderRadius: '8px',
  textAlign: 'center'
}

const receiptTitleStyle = {
  fontSize: '24px',
  fontWeight: '700',
  color: 'white',
  margin: '0 0 10px 0',
  letterSpacing: '1px'
}

const receiptRefStyle = {
  fontSize: '18px',
  fontWeight: '600',
  color: '#10b981',
  margin: 0,
  letterSpacing: '2px'
}

const receiptInfoGridStyle = {
  display: 'grid',
  gridTemplateColumns: '1fr 1fr',
  gap: '20px',
  marginBottom: '40px'
}

const receiptInfoBoxStyle = {
  padding: '20px',
  backgroundColor: '#f8fafc',
  border: '2px solid #e2e8f0',
  borderRadius: '8px'
}

const receiptBoxTitleStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '8px',
  fontSize: '15px',
  fontWeight: '700',
  color: '#0f172a',
  marginBottom: '15px',
  paddingBottom: '10px',
  borderBottom: '2px solid #cbd5e1',
  textTransform: 'uppercase',
  letterSpacing: '0.5px'
}

const receiptInfoLineStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  padding: '8px 0',
  fontSize: '14px'
}

const receiptInfoLabelStyle = {
  color: '#64748b',
  fontWeight: '600'
}

const receiptInfoValueStyle = {
  color: '#1e293b',
  fontWeight: '600'
}

const receiptTableSectionStyle = {
  marginBottom: '40px'
}

const receiptTableStyle = {
  width: '100%',
  borderCollapse: 'collapse',
  fontSize: '14px'
}

const receiptTheadRowStyle = {
  backgroundColor: '#0f172a',
  color: 'white'
}

const receiptThLeftStyle = {
  padding: '15px 12px',
  textAlign: 'left',
  fontWeight: '700',
  fontSize: '13px',
  textTransform: 'uppercase',
  letterSpacing: '0.5px'
}

const receiptThCenterStyle = {
  padding: '15px 12px',
  textAlign: 'center',
  fontWeight: '700',
  fontSize: '13px',
  textTransform: 'uppercase',
  letterSpacing: '0.5px'
}

const receiptThRightStyle = {
  padding: '15px 12px',
  textAlign: 'right',
  fontWeight: '700',
  fontSize: '13px',
  textTransform: 'uppercase',
  letterSpacing: '0.5px'
}

const getReceiptTrStyle = (index) => ({
  backgroundColor: index % 2 === 0 ? '#ffffff' : '#f8fafc',
  borderBottom: '1px solid #e2e8f0'
})

const receiptTdLeftStyle = {
  padding: '12px',
  textAlign: 'left',
  fontSize: '14px',
  color: '#1e293b'
}

const receiptProductNameStyle = {
  fontWeight: '600',
  color: '#0f172a'
}

const receiptTdCenterStyle = {
  padding: '12px',
  textAlign: 'center',
  fontSize: '14px',
  fontWeight: '600',
  color: '#1e293b'
}

const receiptTdRightStyle = {
  padding: '12px',
  textAlign: 'right',
  fontSize: '14px',
  color: '#1e293b'
}

const receiptTdRightBoldStyle = {
  padding: '12px',
  textAlign: 'right',
  fontSize: '14px',
  fontWeight: '700',
  color: '#0f172a'
}

const receiptReductionStyle = {
  color: '#ef4444',
  fontWeight: '700'
}

const receiptNoReductionStyle = {
  color: '#94a3b8'
}

const receiptTotalsContainerStyle = {
  display: 'grid',
  gridTemplateColumns: '1fr 400px',
  gap: '30px',
  marginBottom: '40px'
}

const receiptTotalsSummaryStyle = {
  display: 'flex',
  flexDirection: 'column',
  gap: '20px'
}

const receiptLegalBoxStyle = {
  display: 'flex',
  alignItems: 'flex-start',
  gap: '15px',
  padding: '20px',
  backgroundColor: '#fef3c7',
  border: '3px solid #f59e0b',
  borderRadius: '8px'
}

const receiptWarningIconStyle = {
  fontSize: '28px',
  lineHeight: 1
}

const receiptLegalTitleStyle = {
  fontSize: '15px',
  fontWeight: '700',
  color: '#92400e',
  marginBottom: '5px',
  textTransform: 'uppercase',
  letterSpacing: '0.5px'
}

const receiptLegalSubtitleStyle = {
  fontSize: '13px',
  color: '#92400e',
  fontWeight: '500'
}

const receiptNotesStyle = {
  padding: '15px 20px',
  backgroundColor: '#f1f5f9',
  border: '2px solid #cbd5e1',
  borderRadius: '8px'
}

const receiptNoteLineStyle = {
  fontSize: '13px',
  color: '#475569',
  margin: '6px 0',
  fontWeight: '500'
}

const receiptTotalsBoxStyle = {
  padding: '25px',
  backgroundColor: '#f8fafc',
  border: '3px solid #e2e8f0',
  borderRadius: '8px'
}

const receiptTotalLineStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  padding: '10px 0',
  fontSize: '15px'
}

const receiptTotalLineSeparatorStyle = {
  height: '2px',
  backgroundColor: '#cbd5e1',
  margin: '15px 0'
}

const receiptTotalLineFinalStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  padding: '20px 0',
  fontSize: '18px',
  fontWeight: '700',
  backgroundColor: '#0f172a',
  color: 'white',
  margin: '0 -25px',
  paddingLeft: '25px',
  paddingRight: '25px'
}

const receiptPaymentLineStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  padding: '10px 0',
  fontSize: '15px',
  fontWeight: '600'
}

const receiptTotalLabelStyle = {
  fontWeight: '600',
  color: '#475569'
}

const receiptTotalLabelFinalStyle = {
  fontWeight: '700',
  letterSpacing: '1px'
}

const receiptPaymentLabelStyle = {
  fontWeight: '600',
  color: '#1e293b'
}

const receiptTotalValueStyle = {
  fontWeight: '700',
  color: '#1e293b'
}

const receiptTotalValueFinalStyle = {
  fontWeight: '700',
  fontSize: '20px',
  letterSpacing: '1px'
}

const receiptPayedValueStyle = {
  fontWeight: '700',
  color: '#10b981',
  fontSize: '16px'
}

const receiptResteValueStyle = {
  fontWeight: '700',
  color: '#ef4444',
  fontSize: '16px'
}

const receiptReductionTotalStyle = {
  fontWeight: '700',
  color: '#ef4444'
}

const receiptStatusContainerStyle = {
  marginTop: '20px',
  textAlign: 'center'
}

const getReceiptStatusBadgeStyle = (statut) => ({
  display: 'inline-flex',
  alignItems: 'center',
  justifyContent: 'center',
  gap: '10px',
  padding: '12px 24px',
  border: '3px solid',
  borderColor: statut === 'Paye' ? '#10b981' : statut === 'Partiel' ? '#f59e0b' : '#ef4444',
  backgroundColor: statut === 'Paye' ? '#dcfce7' : statut === 'Partiel' ? '#fef3c7' : '#fee2e2',
  color: statut === 'Paye' ? '#166534' : statut === 'Partiel' ? '#92400e' : '#991b1b',
  borderRadius: '8px',
  fontSize: '14px',
  fontWeight: '700',
  textTransform: 'uppercase',
  letterSpacing: '1px'
})

const receiptFooterStyle = {
  marginTop: 'auto',
  paddingTop: '40px',
  borderTop: '3px solid #0f172a'
}

const receiptFooterTopStyle = {
  textAlign: 'center',
  marginBottom: '30px'
}

const receiptThankYouStyle = {
  fontSize: '18px',
  fontWeight: '700',
  color: '#0f172a',
  letterSpacing: '0.5px'
}

const receiptFooterBottomStyle = {
  display: 'grid',
  gridTemplateColumns: '1fr 1fr 1fr',
  gap: '20px',
  alignItems: 'center'
}

const receiptFooterLeftStyle = {
  textAlign: 'left'
}

const receiptFooterCenterStyle = {
  textAlign: 'center'
}

const receiptFooterRightStyle = {
  textAlign: 'right',
  display: 'flex',
  justifyContent: 'flex-end'
}

const receiptFooterTextStyle = {
  fontSize: '12px',
  color: '#475569',
  margin: '3px 0',
  fontWeight: '500'
}

const receiptFooterSmallStyle = {
  fontSize: '11px',
  color: '#64748b',
  margin: '3px 0'
}

const receiptBarcodeStyle = {
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  gap: '8px'
}

const receiptBarcodeSvgStyle = {
  width: '120px',
  height: '40px'
}

const receiptBarcodeTextStyle = {
  fontSize: '12px',
  fontWeight: '700',
  color: '#0f172a',
  letterSpacing: '3px',
  fontFamily: 'Courier New, monospace'
}
</script>

<style scoped>
@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>