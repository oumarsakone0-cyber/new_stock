<template>
  <SidebarLayout currentPage="comptabilites">
    <div class="motifs-page">
      <!-- Header -->
      <header class="motifs-header fade-in">
        <div>
          <h1 class="motifs-title">Motifs de Depense</h1>
          <p class="motifs-subtitle">Creez et organisez les motifs (categories) utilises par les depenses</p>
        </div>
        <div class="header-actions">
          <button class="btn btn--dark" @click="goTo('/comptabilites/depenses')">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Retour depenses
          </button>
          <button class="btn btn--primary" @click="openModal()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nouveau motif
          </button>
        </div>
      </header>

      <!-- Stats -->
      <div class="stats-row fade-in">
        <div class="stat-card">
          <div class="stat-icon stat-icon--total">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"/></svg>
          </div>
          <div>
            <p class="stat-value">{{ motifs.length }}</p>
            <p class="stat-label">Total motifs</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon stat-icon--filtered">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
          </div>
          <div>
            <p class="stat-value">{{ filtered.length }}</p>
            <p class="stat-label">Affiches</p>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="filters-bar fade-in">
        <div class="search-wrapper">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="search-icon"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input v-model="search" type="text" placeholder="Rechercher un motif..." class="search-input" />
        </div>
        <button class="btn btn--dark btn--sm" @click="loadMotifs">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
          Actualiser
        </button>
      </div>

      <!-- Motifs Grid -->
      <div v-if="loading" class="loading-state fade-in">
        <div class="spinner"></div>
        <p class="loading-text">Chargement...</p>
      </div>

      <div v-if="!loading && filtered.length > 0" class="motifs-grid fade-in">
        <div v-for="m in filtered" :key="m.id" class="motif-card">
          <div class="motif-card-top">
            <div class="motif-badge">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
            </div>
            <div class="motif-actions">
              <button class="motif-btn motif-btn--edit" @click="openModal(m)" title="Modifier">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </button>
              <button class="motif-btn motif-btn--delete" @click="deleteMotif(m)" title="Supprimer">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
              </button>
            </div>
          </div>
          <h3 class="motif-name">{{ m.libelle }}</h3>
          <p class="motif-desc">{{ m.description || 'Aucune description' }}</p>
        </div>
      </div>

      <!-- Empty -->
      <div v-if="!loading && filtered.length === 0" class="empty-state fade-in">
        <div class="empty-icon-wrapper">
          <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="empty-icon"><path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"/></svg>
        </div>
        <p class="empty-text">Aucun motif trouve</p>
        <p class="empty-hint">Cliquez sur "Nouveau motif" pour commencer</p>
      </div>

      <!-- Modal -->
      <Transition name="modal">
        <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
          <div class="modal">
            <div class="modal-header">
              <div class="modal-header-left">
                <div class="modal-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                </div>
                <h2 class="modal-title">{{ editing ? 'Modifier le motif' : 'Nouveau motif' }}</h2>
              </div>
              <button class="modal-close" @click="closeModal">&times;</button>
            </div>
            <div class="modal-body">
              <div class="form-group">
                <label class="form-label">Libelle *</label>
                <input v-model="form.libelle" type="text" class="form-input" placeholder="Ex: Transport, Eau, Electricite..." />
              </div>
              <div class="form-group">
                <label class="form-label">Description</label>
                <textarea v-model="form.description" class="form-input form-textarea" placeholder="Description optionnelle du motif..."></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn--dark" @click="closeModal">Annuler</button>
              <button class="btn btn--primary" @click="saveMotif" :disabled="saving">
                <svg v-if="!saving" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                {{ saving ? 'Enregistrement...' : 'Enregistrer' }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </div>
  </SidebarLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import SidebarLayout from '../SidebarLayout.vue'
import { useAuthStore } from '../../stores/authStore'
import { getApiBaseUrl } from '../../services/api.js'

const router = useRouter()
const authStore = useAuthStore()
const API_BASE_URL = getApiBaseUrl()

const loading = ref(false)
const saving = ref(false)
const motifs = ref([])
const search = ref('')

const showModal = ref(false)
const editing = ref(null)
const form = ref({ id_motif: null, libelle: '', description: '' })

const randomParam = () => `&_=${Date.now()}_${Math.random().toString(36).slice(2)}`
const getUserParams = () => {
  const userId = authStore.user?.id
  const role = authStore.isAdmin ? 'admin' : 'user'
  const id_entreprise = authStore.user?.id_entreprise
  const base = `&user_id=${userId || ''}&role=${role}`
  return id_entreprise ? `${base}&id_entreprise=${id_entreprise}` : base
}

const goTo = (path) => router.push(path)

const filtered = computed(() => {
  const q = (search.value || '').trim().toLowerCase()
  if (!q) return motifs.value
  return motifs.value.filter(m => String(m.libelle || '').toLowerCase().includes(q))
})

const loadMotifs = async () => {
  loading.value = true
  try {
    const res = await fetch(`${API_BASE_URL}/api_comptabilites.php?action=list_motifs${getUserParams()}${randomParam()}`)
    const data = await res.json()
    motifs.value = data.success ? (data.data || []) : []
  } catch (e) {
    console.error(e)
    motifs.value = []
  } finally {
    loading.value = false
  }
}

const openModal = (m = null) => {
  editing.value = m
  form.value = m
    ? { id_motif: m.id, libelle: m.libelle || '', description: m.description || '' }
    : { id_motif: null, libelle: '', description: '' }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editing.value = null
  form.value = { id_motif: null, libelle: '', description: '' }
}

const saveMotif = async () => {
  if (!form.value.libelle?.trim()) {
    alert('Libelle requis')
    return
  }
  saving.value = true
  try {
    const action = editing.value ? 'update_motif' : 'add_motif'
    const payload = {
      ...form.value,
      user_id: authStore.user?.id,
      id_entreprise: authStore.user?.id_entreprise
    }
    const res = await fetch(`${API_BASE_URL}/api_comptabilites.php?action=${action}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })
    const data = await res.json()
    if (!data.success) throw new Error(data.error || data.message || 'Erreur')
    closeModal()
    await loadMotifs()
  } catch (e) {
    console.error(e)
    alert(e.message || 'Erreur sauvegarde')
  } finally {
    saving.value = false
  }
}

const deleteMotif = async (m) => {
  if (!confirm('Supprimer ce motif ?')) return
  try {
    const res = await fetch(`${API_BASE_URL}/api_comptabilites.php?action=delete_motif`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id_motif: m.id, user_id: authStore.user?.id, id_entreprise: authStore.user?.id_entreprise })
    })
    const data = await res.json()
    if (!data.success) throw new Error(data.error || data.message || 'Erreur')
    await loadMotifs()
  } catch (e) {
    console.error(e)
    alert(e.message || 'Erreur suppression')
  }
}

onMounted(loadMotifs)
</script>

<style scoped>
/* ===================== Page layout ===================== */
.motifs-page { display: flex; flex-direction: column; gap: 20px; }

/* ===================== Header ===================== */
.motifs-header { display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; }
.motifs-title { font-size: 28px; font-weight: 800; color: #0f172a; margin: 0 0 4px 0; }
.motifs-subtitle { font-size: 14px; color: #64748b; margin: 0; }
.header-actions { display: flex; gap: 10px; flex-wrap: wrap; }

/* ===================== Buttons ===================== */
.btn {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 11px 20px; border: none; border-radius: 12px;
  font-weight: 700; font-size: 14px; cursor: pointer;
  transition: all .2s ease;
}
.btn:active { transform: scale(0.97); }
.btn--primary { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #fff; }
.btn--primary:hover { box-shadow: 0 4px 14px rgba(16, 185, 129, .35); }
.btn--dark { background: #0f172a; color: #fff; }
.btn--dark:hover { background: #1e293b; }
.btn--sm { padding: 9px 14px; font-size: 13px; }

/* ===================== Stats ===================== */
.stats-row { display: flex; gap: 16px; flex-wrap: wrap; }
.stat-card {
  display: flex; align-items: center; gap: 14px;
  background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
  padding: 16px 22px; flex: 1; min-width: 180px;
  transition: box-shadow .2s;
}
.stat-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.06); }
.stat-icon {
  width: 44px; height: 44px; border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
}
.stat-icon--total { background: #ecfdf5; color: #10b981; }
.stat-icon--filtered { background: #eff6ff; color: #3b82f6; }
.stat-value { font-size: 24px; font-weight: 800; color: #0f172a; margin: 0; }
.stat-label { font-size: 12px; color: #64748b; margin: 0; font-weight: 600; }

/* ===================== Filters ===================== */
.filters-bar {
  display: flex; align-items: center; gap: 12px;
  background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
  padding: 12px 16px;
}
.search-wrapper { position: relative; flex: 1; }
.search-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; }
.search-input {
  width: 100%; padding: 11px 14px 11px 42px;
  border: 1px solid #e2e8f0; border-radius: 10px;
  font-size: 14px; outline: none; background: #f8fafc;
  transition: border-color .2s, box-shadow .2s;
}
.search-input:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,.12); background: #fff; }

/* ===================== Motifs Grid ===================== */
.motifs-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }
.motif-card {
  background: #fff; border: 1px solid #e2e8f0; border-radius: 16px;
  padding: 20px; display: flex; flex-direction: column; gap: 10px;
  transition: all .25s ease; position: relative;
}
.motif-card:hover { border-color: #10b981; box-shadow: 0 8px 24px rgba(16,185,129,.1); transform: translateY(-2px); }
.motif-card-top { display: flex; justify-content: space-between; align-items: flex-start; }
.motif-badge {
  width: 40px; height: 40px; border-radius: 12px;
  background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
  color: #10b981; display: flex; align-items: center; justify-content: center;
}
.motif-actions { display: flex; gap: 6px; opacity: 0; transition: opacity .2s; }
.motif-card:hover .motif-actions { opacity: 1; }
.motif-btn {
  width: 34px; height: 34px; border: none; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: all .2s;
}
.motif-btn:active { transform: scale(0.92); }
.motif-btn--edit { background: #eff6ff; color: #3b82f6; }
.motif-btn--edit:hover { background: #3b82f6; color: #fff; }
.motif-btn--delete { background: #fef2f2; color: #ef4444; }
.motif-btn--delete:hover { background: #ef4444; color: #fff; }
.motif-name { font-size: 16px; font-weight: 700; color: #0f172a; margin: 0; }
.motif-desc { font-size: 13px; color: #64748b; margin: 0; line-height: 1.5; }

/* ===================== Loading ===================== */
.loading-state { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 16px; }
.spinner {
  width: 40px; height: 40px; border: 4px solid #f1f5f9;
  border-top-color: #10b981; border-radius: 50%;
  animation: spin 1s linear infinite;
}
.loading-text { margin-top: 12px; font-size: 14px; font-weight: 700; color: #64748b; }

/* ===================== Empty ===================== */
.empty-state {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  padding: 60px 16px; background: #fff; border: 1px solid #e2e8f0; border-radius: 16px;
}
.empty-icon-wrapper {
  width: 72px; height: 72px; border-radius: 50%;
  background: #f1f5f9; display: flex; align-items: center; justify-content: center;
  margin-bottom: 16px;
}
.empty-icon { color: #94a3b8; }
.empty-text { font-size: 16px; font-weight: 700; color: #64748b; margin: 0 0 4px 0; }
.empty-hint { font-size: 13px; color: #94a3b8; margin: 0; }

/* ===================== Modal ===================== */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(15,23,42,.5);
  backdrop-filter: blur(4px); display: flex; align-items: center;
  justify-content: center; padding: 16px; z-index: 999;
}
.modal {
  width: 100%; max-width: 520px; background: #fff;
  border-radius: 20px; border: 1px solid #e2e8f0;
  box-shadow: 0 24px 64px rgba(0,0,0,.2);
  animation: modalIn .25s ease-out;
}
.modal-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 18px 22px; border-bottom: 1px solid #e2e8f0;
}
.modal-header-left { display: flex; align-items: center; gap: 12px; }
.modal-icon {
  width: 38px; height: 38px; border-radius: 10px;
  background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
  color: #10b981; display: flex; align-items: center; justify-content: center;
}
.modal-title { margin: 0; font-size: 17px; font-weight: 800; color: #0f172a; }
.modal-close {
  border: none; background: #f1f5f9; width: 34px; height: 34px;
  border-radius: 10px; font-size: 20px; font-weight: 700; color: #64748b;
  cursor: pointer; display: flex; align-items: center; justify-content: center;
  transition: all .2s;
}
.modal-close:hover { background: #ef4444; color: #fff; }
.modal-body { padding: 22px; display: flex; flex-direction: column; gap: 16px; }
.modal-footer {
  padding: 16px 22px; border-top: 1px solid #e2e8f0;
  display: flex; justify-content: flex-end; gap: 10px;
}

/* ===================== Form ===================== */
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-label { font-size: 13px; font-weight: 700; color: #0f172a; }
.form-input {
  width: 100%; padding: 12px 14px; border: 1px solid #e2e8f0;
  border-radius: 10px; font-size: 14px; outline: none;
  background: #f8fafc; transition: border-color .2s, box-shadow .2s;
}
.form-input:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,.12); background: #fff; }
.form-textarea { min-height: 100px; resize: vertical; font-family: inherit; }

/* ===================== Animations ===================== */
@keyframes spin { to { transform: rotate(360deg); } }
@keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
@keyframes modalIn { from { opacity: 0; transform: scale(0.95) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
.fade-in { animation: fadeIn .3s ease-out; }

.modal-enter-active { transition: opacity .25s ease; }
.modal-leave-active { transition: opacity .2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }

/* ===================== Responsive ===================== */
@media (max-width: 640px) {
  .motifs-header { flex-direction: column; align-items: flex-start; }
  .motifs-title { font-size: 22px; }
  .header-actions { width: 100%; }
  .header-actions .btn { flex: 1; justify-content: center; }
  .stats-row { flex-direction: column; }
  .filters-bar { flex-direction: column; }
  .motifs-grid { grid-template-columns: 1fr; }
}
</style>
