<template>
  <div>
    <!-- Bouton d'import -->
    <button 
      :style="importButtonStyle"
      @click="showModal = true"
      @mouseenter="importHovered = true"
      @mouseleave="importHovered = false"
    >
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-15M7 10l5-5 5 5M12 15V3"/>
      </svg>
      Importer PDF
    </button>

    <!-- Modal d'import -->
    <div v-if="showModal" :style="modalOverlayStyle" @click.self="closeModal">
      <div :style="modalStyle">
        <div :style="modalHeaderStyle">
          <h2 :style="modalTitleStyle">📤 Importer mouvements depuis PDF</h2>
          <button :style="closeButtonStyle" @click="closeModal">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <div :style="modalBodyStyle">
          <!-- Étapes -->
          <div :style="stepsContainerStyle">
            <div :style="getStepStyle(1)">
              <div :style="stepNumberStyle(currentStep >= 1)">1</div>
              <span :style="stepLabelStyle(currentStep >= 1)">Sélectionner PDF</span>
            </div>
            <div :style="stepConnectorStyle(currentStep >= 2)"></div>
            <div :style="getStepStyle(2)">
              <div :style="stepNumberStyle(currentStep >= 2)">2</div>
              <span :style="stepLabelStyle(currentStep >= 2)">Extraire données</span>
            </div>
            <div :style="stepConnectorStyle(currentStep >= 3)"></div>
            <div :style="getStepStyle(3)">
              <div :style="stepNumberStyle(currentStep >= 3)">3</div>
              <span :style="stepLabelStyle(currentStep >= 3)">Valider</span>
            </div>
          </div>

          <!-- Étape 1: Upload -->
          <div v-if="currentStep === 1" :style="stepContentStyle">
            <div :style="uploadZoneStyle" @click="triggerFileInput" @dragover.prevent @drop.prevent="handleDrop">
              <input 
                ref="fileInput"
                type="file"
                accept="application/pdf"
                @change="handleFileSelect"
                style="display: none"
              />
              
              <div v-if="!selectedFile" :style="uploadPlaceholderStyle">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2">
                  <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-15M7 10l5-5 5 5M12 15V3"/>
                </svg>
                <p :style="uploadTextStyle">Cliquez ou glissez un PDF ici</p>
                <p :style="uploadSubTextStyle">Format: PDF uniquement</p>
              </div>

              <div v-else :style="fileSelectedStyle">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2">
                  <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                  <path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/>
                </svg>
                <p :style="fileNameStyle">{{ selectedFile.name }}</p>
                <p :style="fileSizeStyle">{{ formatFileSize(selectedFile.size) }}</p>
                <button :style="removeFileButtonStyle" @click.stop="removeFile">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6L6 18M6 6l12 12"/>
                  </svg>
                  Supprimer
                </button>
              </div>
            </div>

            <button 
              v-if="selectedFile"
              :style="nextButtonStyle"
              @click="extractPDF"
              :disabled="processing"
            >
              <div v-if="processing" :style="spinnerStyle"></div>
              <span v-else>Analyser le PDF</span>
            </button>
          </div>

          <!-- Étape 2: Extraction et mapping -->
          <div v-if="currentStep === 2" :style="stepContentStyle">
            <div :style="extractionInfoStyle">
              <p :style="infoTextStyle">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2">
                  <circle cx="12" cy="12" r="10"/>
                  <path d="M12 16v-4M12 8h.01"/>
                </svg>
                PDF extrait avec succès. Vérifiez les données ci-dessous.
              </p>
            </div>

            <!-- Aperçu des données extraites -->
            <div :style="previewContainerStyle">
              <h4 :style="previewTitleStyle">Données extraites ({{ extractedData.length }} mouvements)</h4>
              
              <div :style="tableWrapperStyle">
                <table :style="tableStyle">
                  <thead>
                    <tr>
                      <th :style="thStyle">
                        <input type="checkbox" :checked="allSelected" @change="toggleSelectAll" />
                      </th>
                      <th :style="thStyle">Date</th>
                      <th :style="thStyle">Type</th>
                      <th :style="thStyle">Produit</th>
                      <th :style="thStyle">Quantité</th>
                      <th :style="thStyle">Motif</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, index) in extractedData" :key="index" :style="trStyle">
                      <td :style="tdStyle">
                        <input type="checkbox" v-model="item.selected" />
                      </td>
                      <td :style="tdStyle">
                        <input v-model="item.date" type="date" :style="inputEditStyle" />
                      </td>
                      <td :style="tdStyle">
                        <select v-model="item.type" :style="selectEditStyle">
                          <option value="entree">Entrée</option>
                          <option value="sortie">Sortie</option>
                        </select>
                      </td>
                      <td :style="tdStyle">
                        <select v-model="item.produit_id" :style="selectEditStyle">
                          <option value="">Sélectionner...</option>
                          <option v-for="p in produits" :key="p.id" :value="p.id">
                            {{ p.nom }}
                          </option>
                        </select>
                      </td>
                      <td :style="tdStyle">
                        <input v-model.number="item.quantite" type="number" min="1" :style="inputEditStyle" />
                      </td>
                      <td :style="tdStyle">
                        <input v-model="item.motif" type="text" :style="inputEditStyle" placeholder="Motif..." />
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <div :style="actionsContainerStyle">
              <button :style="backButtonStyle" @click="currentStep = 1">
                Retour
              </button>
              <button :style="nextButtonStyle" @click="validateData" :disabled="selectedCount === 0">
                Valider ({{ selectedCount }} sélectionnés)
              </button>
            </div>
          </div>

          <!-- Étape 3: Confirmation -->
          <div v-if="currentStep === 3" :style="stepContentStyle">
            <div :style="confirmationStyle">
              <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                <path d="M22 4L12 14.01l-3-3"/>
              </svg>
              <h3 :style="confirmTitleStyle">Prêt à importer</h3>
              <p :style="confirmTextStyle">
                {{ selectedCount }} mouvement(s) vont être importés dans le stock.
              </p>
            </div>

            <div :style="summaryStyle">
              <div :style="summaryItemStyle">
                <span>Entrées:</span>
                <strong>{{ countByType('entree') }}</strong>
              </div>
              <div :style="summaryItemStyle">
                <span>Sorties:</span>
                <strong>{{ countByType('sortie') }}</strong>
              </div>
            </div>

            <div :style="actionsContainerStyle">
              <button :style="backButtonStyle" @click="currentStep = 2">
                Retour
              </button>
              <button :style="importFinalButtonStyle" @click="importMovements" :disabled="importing">
                <div v-if="importing" :style="spinnerStyle"></div>
                <span v-else>Confirmer l'import</span>
              </button>
            </div>
          </div>

          <!-- Étape 4: Succès -->
          <div v-if="currentStep === 4" :style="stepContentStyle">
            <div :style="successStyle">
              <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                <path d="M22 4L12 14.01l-3-3"/>
              </svg>
              <h3 :style="successTitleStyle">Import réussi !</h3>
              <p :style="successTextStyle">
                {{ importedCount }} mouvement(s) ont été importés avec succès.
              </p>
              <button :style="doneButtonStyle" @click="closeModal">
                Terminer
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, defineProps, defineEmits } from 'vue'
import * as pdfjsLib from 'pdfjs-dist'

// Configuration de PDF.js
pdfjsLib.GlobalWorkerOptions.workerSrc = `//cdnjs.cloudflare.com/ajax/libs/pdf.js/${pdfjsLib.version}/pdf.worker.min.js`

const props = defineProps({
  magasinId: {
    type: [String, Number],
    required: true
  },
  produits: {
    type: Array,
    required: true
  },
  apiBaseUrl: {
    type: String,
    required: true
  }
})

const emit = defineEmits(['import-success'])

// State
const showModal = ref(false)
const currentStep = ref(1)
const selectedFile = ref(null)
const extractedData = ref([])
const processing = ref(false)
const importing = ref(false)
const importedCount = ref(0)
const importHovered = ref(false)
const fileInput = ref(null)

// Computed
const allSelected = computed(() => {
  return extractedData.value.length > 0 && extractedData.value.every(item => item.selected)
})

const selectedCount = computed(() => {
  return extractedData.value.filter(item => item.selected).length
})

// Methods
const triggerFileInput = () => {
  fileInput.value?.click()
}

const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (file && file.type === 'application/pdf') {
    selectedFile.value = file
  } else {
    alert('Veuillez sélectionner un fichier PDF')
  }
}

const handleDrop = (event) => {
  const file = event.dataTransfer.files[0]
  if (file && file.type === 'application/pdf') {
    selectedFile.value = file
  } else {
    alert('Veuillez déposer un fichier PDF')
  }
}

const removeFile = () => {
  selectedFile.value = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const formatFileSize = (bytes) => {
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB'
  return (bytes / (1024 * 1024)).toFixed(2) + ' MB'
}

const extractPDF = async () => {
  processing.value = true
  
  try {
    const arrayBuffer = await selectedFile.value.arrayBuffer()
    const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise
    
    let fullText = ''
    
    // Extraire le texte de toutes les pages
    for (let i = 1; i <= pdf.numPages; i++) {
      const page = await pdf.getPage(i)
      const textContent = await page.getTextContent()
      const pageText = textContent.items.map(item => item.str).join(' ')
      fullText += pageText + '\n'
    }
    
    // Parser le texte pour extraire les mouvements
    const movements = parseMovementsFromText(fullText)
    
    if (movements.length === 0) {
      alert('Aucun mouvement détecté dans le PDF. Vérifiez le format du fichier.')
      return
    }
    
    extractedData.value = movements
    currentStep.value = 2
    
  } catch (error) {
    console.error('Erreur extraction PDF:', error)
    alert('Erreur lors de l\'extraction du PDF: ' + error.message)
  } finally {
    processing.value = false
  }
}

const parseMovementsFromText = (text) => {
  // Parser intelligent pour détecter les mouvements
  // Format attendu: Date | Type | Produit | Quantité | Motif
  const movements = []
  const lines = text.split('\n')
  
  // Patterns de détection
  const datePattern = /(\d{1,2}\/\d{1,2}\/\d{4}|\d{4}-\d{2}-\d{2})/
  const typePattern = /(entrée|sortie|entree|e|s)/i
  const quantityPattern = /[+-]?\d+/
  
  for (let i = 0; i < lines.length; i++) {
    const line = lines[i].trim()
    if (!line) continue
    
    // Détecter une ligne de mouvement
    const dateMatch = line.match(datePattern)
    const typeMatch = line.match(typePattern)
    const quantityMatch = line.match(quantityPattern)
    
    if (dateMatch && typeMatch && quantityMatch) {
      // Convertir la date au format YYYY-MM-DD
      let date = dateMatch[0]
      if (date.includes('/')) {
        const [d, m, y] = date.split('/')
        date = `${y}-${m.padStart(2, '0')}-${d.padStart(2, '0')}`
      }
      
      // Déterminer le type
      const typeStr = typeMatch[0].toLowerCase()
      const type = typeStr.includes('e') || typeStr.includes('entree') || typeStr.includes('entrée') 
        ? 'entree' 
        : 'sortie'
      
      // Extraire le produit (texte entre type et quantité)
      const parts = line.split(/\s+/)
      const productName = parts.slice(2, -2).join(' ') || 'Produit inconnu'
      
      movements.push({
        selected: true,
        date: date,
        type: type,
        produit_id: '',
        produit_name: productName,
        quantite: Math.abs(parseInt(quantityMatch[0])),
        motif: 'Import PDF'
      })
    }
  }
  
  return movements
}

const toggleSelectAll = () => {
  const newValue = !allSelected.value
  extractedData.value.forEach(item => {
    item.selected = newValue
  })
}

const validateData = () => {
  // Vérifier que tous les mouvements sélectionnés ont un produit
  const selectedItems = extractedData.value.filter(item => item.selected)
  const invalidItems = selectedItems.filter(item => !item.produit_id)
  
  if (invalidItems.length > 0) {
    alert(`${invalidItems.length} mouvement(s) n'ont pas de produit sélectionné`)
    return
  }
  
  currentStep.value = 3
}

const countByType = (type) => {
  return extractedData.value.filter(item => item.selected && item.type === type).length
}

const importMovements = async () => {
  importing.value = true
  
  try {
    const selectedItems = extractedData.value.filter(item => item.selected)
    let successCount = 0
    let errorCount = 0
    
    for (const item of selectedItems) {
      try {
        const action = item.type === 'entree' ? 'entree' : 'sortie'
        const response = await fetch(`${props.apiBaseUrl}/api_stock.php?action=${action}`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            produit_id: item.produit_id,
            magasin_id: props.magasinId,
            quantite: item.quantite,
            motif: item.motif,
            notes: 'Import depuis PDF',
            utilisateur: 'Admin'
          })
        })
        
        const data = await response.json()
        
        if (data.success) {
          successCount++
        } else {
          errorCount++
          console.error('Erreur import:', data.error)
        }
      } catch (error) {
        errorCount++
        console.error('Erreur:', error)
      }
    }
    
    importedCount.value = successCount
    
    if (errorCount > 0) {
      alert(`Import terminé avec ${errorCount} erreur(s)`)
    }
    
    currentStep.value = 4
    emit('import-success', successCount)
    
  } catch (error) {
    console.error('Erreur import:', error)
    alert('Erreur lors de l\'import')
  } finally {
    importing.value = false
  }
}

const closeModal = () => {
  showModal.value = false
  currentStep.value = 1
  selectedFile.value = null
  extractedData.value = []
  importedCount.value = 0
  
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

// Styles
const importButtonStyle = computed(() => ({
  display: 'flex',
  alignItems: 'center',
  gap: '8px',
  padding: '10px 20px',
  background: importHovered.value
    ? 'linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%)'
    : 'linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%)',
  color: 'white',
  border: 'none',
  borderRadius: '10px',
  cursor: 'pointer',
  fontSize: '14px',
  fontWeight: '600',
  transition: 'all 0.2s',
  boxShadow: '0 4px 12px rgba(139, 92, 246, 0.25)'
}))

const modalOverlayStyle = {
  position: 'fixed',
  top: 0,
  left: 0,
  right: 0,
  bottom: 0,
  backgroundColor: 'rgba(0, 0, 0, 0.5)',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  zIndex: 1000,
  padding: '20px'
}

const modalStyle = {
  backgroundColor: 'white',
  borderRadius: '20px',
  width: '100%',
  maxWidth: '800px',
  maxHeight: '90vh',
  overflow: 'hidden',
  display: 'flex',
  flexDirection: 'column'
}

const modalHeaderStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  padding: '24px',
  borderBottom: '1px solid #e2e8f0'
}

const modalTitleStyle = {
  fontSize: '20px',
  fontWeight: '600',
  color: '#1e293b',
  margin: 0
}

const closeButtonStyle = {
  background: 'none',
  border: 'none',
  cursor: 'pointer',
  color: '#64748b',
  padding: '4px'
}

const modalBodyStyle = {
  padding: '24px',
  overflowY: 'auto',
  flex: 1
}

const stepsContainerStyle = {
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  marginBottom: '32px',
  gap: '8px'
}

const getStepStyle = (step) => ({
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  gap: '8px'
})

const stepNumberStyle = (active) => ({
  width: '40px',
  height: '40px',
  borderRadius: '50%',
  backgroundColor: active ? '#8b5cf6' : '#e2e8f0',
  color: active ? 'white' : '#94a3b8',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  fontWeight: '600',
  fontSize: '16px',
  transition: 'all 0.3s'
})

const stepLabelStyle = (active) => ({
  fontSize: '12px',
  fontWeight: '500',
  color: active ? '#8b5cf6' : '#94a3b8',
  transition: 'all 0.3s'
})

const stepConnectorStyle = (active) => ({
  width: '60px',
  height: '2px',
  backgroundColor: active ? '#8b5cf6' : '#e2e8f0',
  marginBottom: '28px',
  transition: 'all 0.3s'
})

const stepContentStyle = {
  animation: 'fadeIn 0.3s ease-in'
}

const uploadZoneStyle = {
  border: '2px dashed #cbd5e1',
  borderRadius: '16px',
  padding: '48px 24px',
  textAlign: 'center',
  cursor: 'pointer',
  transition: 'all 0.2s',
  backgroundColor: '#f8fafc',
  marginBottom: '24px'
}

const uploadPlaceholderStyle = {
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  gap: '12px'
}

const uploadTextStyle = {
  fontSize: '16px',
  fontWeight: '500',
  color: '#475569',
  margin: 0
}

const uploadSubTextStyle = {
  fontSize: '14px',
  color: '#94a3b8',
  margin: 0
}

const fileSelectedStyle = {
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  gap: '12px'
}

const fileNameStyle = {
  fontSize: '16px',
  fontWeight: '600',
  color: '#0f172a',
  margin: 0
}

const fileSizeStyle = {
  fontSize: '14px',
  color: '#64748b',
  margin: 0
}

const removeFileButtonStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '6px',
  padding: '8px 16px',
  backgroundColor: '#fee2e2',
  color: '#991b1b',
  border: 'none',
  borderRadius: '8px',
  cursor: 'pointer',
  fontSize: '13px',
  fontWeight: '500',
  marginTop: '8px'
}

const nextButtonStyle = {
  width: '100%',
  padding: '14px',
  backgroundColor: '#8b5cf6',
  color: 'white',
  border: 'none',
  borderRadius: '12px',
  fontSize: '15px',
  fontWeight: '600',
  cursor: 'pointer',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  gap: '8px'
}

const spinnerStyle = {
  width: '20px',
  height: '20px',
  border: '2px solid rgba(255, 255, 255, 0.3)',
  borderTop: '2px solid white',
  borderRadius: '50%',
  animation: 'spin 1s linear infinite'
}

const extractionInfoStyle = {
  backgroundColor: '#dbeafe',
  border: '1px solid #93c5fd',
  borderRadius: '12px',
  padding: '16px',
  marginBottom: '24px'
}

const infoTextStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '8px',
  margin: 0,
  fontSize: '14px',
  color: '#1e40af',
  fontWeight: '500'
}

const previewContainerStyle = {
  marginBottom: '24px'
}

const previewTitleStyle = {
  fontSize: '16px',
  fontWeight: '600',
  color: '#0f172a',
  marginBottom: '16px'
}

const tableWrapperStyle = {
  overflowX: 'auto',
  border: '1px solid #e2e8f0',
  borderRadius: '12px'
}

const tableStyle = {
  width: '100%',
  borderCollapse: 'collapse',
  fontSize: '13px'
}

const thStyle = {
  padding: '12px',
  textAlign: 'left',
  backgroundColor: '#f8fafc',
  borderBottom: '1px solid #e2e8f0',
  fontWeight: '600',
  color: '#64748b',
  fontSize: '12px',
  textTransform: 'uppercase'
}

const trStyle = {
  borderBottom: '1px solid #f1f5f9'
}

const tdStyle = {
  padding: '12px'
}

const inputEditStyle = {
  width: '100%',
  padding: '6px 10px',
  border: '1px solid #e2e8f0',
  borderRadius: '6px',
  fontSize: '13px'
}

const selectEditStyle = {
  width: '100%',
  padding: '6px 10px',
  border: '1px solid #e2e8f0',
  borderRadius: '6px',
  fontSize: '13px',
  backgroundColor: 'white'
}

const actionsContainerStyle = {
  display: 'flex',
  gap: '12px'
}

const backButtonStyle = {
  flex: 1,
  padding: '12px',
  backgroundColor: '#f1f5f9',
  color: '#64748b',
  border: 'none',
  borderRadius: '10px',
  fontSize: '14px',
  fontWeight: '600',
  cursor: 'pointer'
}

const confirmationStyle = {
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  gap: '16px',
  marginBottom: '24px'
}

const confirmTitleStyle = {
  fontSize: '20px',
  fontWeight: '600',
  color: '#0f172a',
  margin: 0
}

const confirmTextStyle = {
  fontSize: '15px',
  color: '#64748b',
  textAlign: 'center',
  margin: 0
}

const summaryStyle = {
  display: 'grid',
  gridTemplateColumns: '1fr 1fr',
  gap: '16px',
  marginBottom: '24px'
}

const summaryItemStyle = {
  padding: '16px',
  backgroundColor: '#f8fafc',
  borderRadius: '12px',
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  fontSize: '14px',
  color: '#64748b'
}

const importFinalButtonStyle = {
  flex: 1,
  padding: '14px',
  backgroundColor: '#10b981',
  color: 'white',
  border: 'none',
  borderRadius: '12px',
  fontSize: '15px',
  fontWeight: '600',
  cursor: 'pointer',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  gap: '8px'
}

const successStyle = {
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  gap: '16px',
  padding: '32px'
}

const successTitleStyle = {
  fontSize: '24px',
  fontWeight: '600',
  color: '#10b981',
  margin: 0
}

const successTextStyle = {
  fontSize: '15px',
  color: '#64748b',
  textAlign: 'center',
  margin: 0
}

const doneButtonStyle = {
  padding: '12px 32px',
  backgroundColor: '#8b5cf6',
  color: 'white',
  border: 'none',
  borderRadius: '10px',
  fontSize: '15px',
  fontWeight: '600',
  cursor: 'pointer',
  marginTop: '16px'
}
</script>

<style scoped>
@keyframes spin {
  to { transform: rotate(360deg); }
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

input[type="checkbox"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
}
</style>