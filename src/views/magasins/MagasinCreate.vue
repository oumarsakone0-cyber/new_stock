
<template>
  <Transition name="pdv-modal">
    <div :style="overlayStyle">
      <div :style="modalStyle" class="pdv-modal-content">
        <div :style="modalHeaderStyle">
          <div>
            <div :style="modalBadgeStyle">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
              </svg>
            </div>
            <h2 :style="modalTitleStyle">Créer un nouveau magasin</h2>
            <p :style="modalSubStyle">Enregistrez une nouvelle boutique dans le système</p>
          </div>
        </div>
        <form @submit.prevent="saveMagasin" :style="formStyle">
          <div :style="sectionTitleStyle">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
              <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            Informations du magasin
          </div>
          <div :style="rowStyle">
            <div :style="groupStyle">
              <label :style="labelStyle">Nom du magasin <span :style="reqStyle">*</span></label>
              <input v-model="formData.nom" type="text" :style="inputStyle" placeholder="Nom du magasin" required />
            </div>
            <div :style="groupStyle">
              <label :style="labelStyle">Adresse</label>
              <input v-model="formData.adresse" type="text" :style="inputStyle" placeholder="Adresse" />
            </div>
          </div>
          <div :style="rowStyle">
            <div :style="groupStyle">
              <label :style="labelStyle">Téléphone</label>
              <input v-model="formData.telephone" type="text" :style="inputStyle" placeholder="Téléphone" />
            </div>
            <div :style="groupStyle">
              <label :style="labelStyle">Couleur</label>
              <input v-model="formData.couleur" type="color" :style="inputStyle" />
            </div>
          </div>
          <div :style="rowStyle">
            <div :style="groupStyle">
              <label :style="labelStyle">Actif</label>
              <input v-model="formData.actif" type="checkbox" />
            </div>
          </div>
          <div :style="actionsStyle">
            <button type="submit" :style="submitBtnStyle" :disabled="saving">
              <svg v-if="!saving" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                <polyline points="17 21 17 13 7 13 7 21"/>
                <polyline points="7 3 7 8 15 8"/>
              </svg>
              <div v-else :style="btnSpinnerStyle"></div>
              {{ saving ? 'Enregistrement...' : 'Créer le magasin' }}
            </button>
          </div>
          <span v-if="error" :style="{ color: '#ef4444', fontSize: '14px', marginTop: '10px', display: 'block' }">{{ error }}</span>
          <span v-if="success" :style="{ color: '#22c55e', fontSize: '14px', marginTop: '10px', display: 'block' }">{{ success }}</span>
        </form>
      </div>
    </div>
  </Transition>
</template>
// ...existing code...
// Styles modale PDV importés depuis Ventes.vue
const overlayStyle = { position:'fixed', top:0, left:0, right:0, bottom:0, background:'rgba(15,23,42,0.75)', backdropFilter:'blur(8px)', display:'flex', alignItems:'center', justifyContent:'center', zIndex:1000, padding:'20px', overflowY:'auto' }
const modalStyle = { background:'white', borderRadius:'24px', width:'100%', maxWidth:'680px', maxHeight:'90vh', overflow:'auto', boxShadow:'0 25px 50px -12px rgba(0,0,0,0.25)' }
const modalHeaderStyle = { display:'flex', justifyContent:'space-between', alignItems:'flex-start', padding:'28px 32px 22px', borderBottom:'1px solid #f1f5f9' }
const modalBadgeStyle = { width:'38px', height:'38px', background:'linear-gradient(135deg,#0ea5e9,#0284c7)', borderRadius:'10px', display:'flex', alignItems:'center', justifyContent:'center', color:'white', marginBottom:'14px' }
const modalTitleStyle = { fontSize:'22px', fontWeight:'800', color:'#0f172a', margin:'0 0 4px', letterSpacing:'-0.02em' }
const modalSubStyle = { fontSize:'13px', color:'#64748b', margin:0, fontWeight:'500' }
const formStyle = { padding:'22px 32px 30px' }
const sectionTitleStyle = { display:'flex', alignItems:'center', gap:'8px', fontSize:'15px', fontWeight:'700', color:'#0f172a', marginBottom:'16px', marginTop:'20px', paddingBottom:'10px', borderBottom:'2px solid #f1f5f9' }
const rowStyle = { display:'flex', gap:'14px' }
const groupStyle = { marginBottom:'18px', flex:1 }
const labelStyle = { display:'flex', alignItems:'center', gap:'4px', fontSize:'13px', fontWeight:'700', color:'#334155', marginBottom:'7px' }
const reqStyle = { color:'#ef4444' }
const inputStyle = { width:'100%', padding:'11px 14px', border:'1px solid #e2e8f0', borderRadius:'10px', fontSize:'14px', boxSizing:'border-box', fontWeight:'500', color:'#1e293b', outline:'none', transition:'all 0.2s' }
const actionsStyle = { display:'flex', gap:'12px', marginTop:'24px', paddingTop:'20px', borderTop:'1px solid #f1f5f9' }
const submitBtnStyle = { flex:1, padding:'13px', background:'linear-gradient(135deg,#0ea5e9,#0284c7)', border:'none', borderRadius:'10px', fontSize:'14px', fontWeight:'700', color:'white', cursor:'pointer', display:'flex', alignItems:'center', justifyContent:'center', gap:'8px', boxShadow:'0 4px 12px rgba(14,165,233,0.25)' }
const btnSpinnerStyle = { width:'16px', height:'16px', border:'2px solid rgba(255,255,255,0.3)', borderTop:'2px solid white', borderRadius:'50%', animation:'pdv-spin 0.8s linear infinite' }

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { addMagasin } from '../../services/api.js'

const router = useRouter()

const formData = reactive({
  nom: '',
  adresse: '',
  telephone: '',
  couleur: '#10b981',
  actif: true
})

const saving = ref(false)
const error = ref('')
const success = ref('')

const saveMagasin = async () => {
  saving.value = true
  error.value = ''
  success.value = ''
  try {
    const response = await addMagasin(formData)
    const data = response.data
    if (data.success && data.id) {
      success.value = 'Magasin créé avec succès !'
      setTimeout(() => router.push(`/magasins/${data.id}`), 1000)
    } else {
      error.value = data.error || 'Erreur lors de la création'
    }
  } catch (e) {
    error.value = 'Erreur de connexion au serveur'
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
.magasin-create-container {
  max-width: 400px;
  margin: 40px auto;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 8px #0001;
  padding: 32px 24px;
}
h1 {
  font-size: 24px;
  margin-bottom: 24px;
  color: #1e293b;
}
.form-group {
  margin-bottom: 18px;
}
label {
  display: block;
  font-weight: 500;
  margin-bottom: 6px;
  color: #374151;
}
input[type="text"], input[type="color"] {
  width: 100%;
  padding: 10px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 15px;
  box-sizing: border-box;
}
input[type="checkbox"] {
  margin-right: 8px;
}
.form-actions {
  margin-top: 24px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
button {
  background: #10b981;
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 12px 0;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}
button:disabled {
  background: #a7f3d0;
  cursor: not-allowed;
}
.error {
  color: #ef4444;
  font-size: 14px;
}
.success {
  color: #22c55e;
  font-size: 14px;
}
</style>
