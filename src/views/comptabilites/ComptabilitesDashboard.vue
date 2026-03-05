<template>
  <SidebarLayout currentPage="comptabilites">
    <div class="compta-page">
      <!-- Header -->
      <header class="compta-header fade-in">
        <div>
          <h1 class="compta-title">Comptabilites</h1>
          <p class="compta-subtitle">Gerez les depenses, pieces justificatives et la gestion bancaire</p>
        </div>
      </header>

      <!-- Cards Grid -->
      <div class="compta-grid fade-in">

        <!-- Card Depenses -->
        <div class="compta-card">
          <div class="card-icon card-icon--green">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/>
              <path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/>
              <path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/>
            </svg>
          </div>
          <h3 class="card-title">Gestion des depenses</h3>
          <p class="card-text">Depenses avec filtres par periode, motifs et pieces justificatives (factures).</p>
          <div class="card-actions">
            <button class="btn btn--primary" @click="goTo('/comptabilites/depenses')">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
              Ouvrir
            </button>
            <button class="btn btn--secondary" @click="goTo('/comptabilites/motifs')">Motifs</button>
            <button class="btn btn--secondary" @click="goTo('/comptabilites/factures')">Factures</button>
          </div>
        </div>

        <!-- Card Bancaire -->
        <div class="compta-card">
          <div class="card-icon card-icon--blue">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
          </div>
          <h3 class="card-title">Gestion bancaire</h3>
          <p class="card-text">Comptes bancaires, transactions (debit/credit), historique et soldes.</p>
          <div class="card-actions">
            <button class="btn btn--primary" @click="goTo('/comptabilites/banque')">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
              Ouvrir
            </button>
            <button class="btn btn--secondary" @click="goTo('/comptabilites/banque/comptes')">Comptes</button>
            <button class="btn btn--secondary" @click="goTo('/comptabilites/banque/transactions')">Transactions</button>
          </div>
        </div>

      </div>
    </div>
  </SidebarLayout>
</template>

<script setup>
import { useRouter } from 'vue-router'
import SidebarLayout from '../SidebarLayout.vue'

const router = useRouter()
const goTo = (path) => router.push(path)
</script>

<style scoped>
/* ========== Page ========== */
.compta-page {
  padding: 0;
}

/* ========== Header ========== */
.compta-header {
  margin-bottom: 24px;
}
.compta-title {
  font-size: 28px;
  font-weight: 800;
  color: #0f172a;
  margin: 0 0 4px 0;
  letter-spacing: -0.5px;
}
.compta-subtitle {
  font-size: 14px;
  color: #64748b;
  margin: 0;
  font-weight: 500;
}

/* ========== Grid ========== */
.compta-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 16px;
}

/* ========== Card ========== */
.compta-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  transition: box-shadow 0.2s ease, transform 0.2s ease;
}
.compta-card:hover {
  box-shadow: 0 4px 24px rgba(15, 23, 42, 0.08);
  transform: translateY(-2px);
}

/* ========== Card Icon ========== */
.card-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.card-icon--green {
  background: #ecfdf5;
  color: #059669;
}
.card-icon--blue {
  background: #eff6ff;
  color: #2563eb;
}

/* ========== Card Content ========== */
.card-title {
  margin: 0;
  font-size: 18px;
  font-weight: 800;
  color: #0f172a;
}
.card-text {
  margin: 0;
  color: #64748b;
  font-weight: 500;
  font-size: 14px;
  line-height: 1.5;
}

/* ========== Buttons ========== */
.card-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  margin-top: 4px;
}
.btn {
  padding: 10px 18px;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 700;
  font-size: 13px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.btn:hover {
  opacity: 0.9;
  transform: translateY(-1px);
}
.btn:active {
  transform: translateY(0);
}
.btn--primary {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: #ffffff;
}
.btn--secondary {
  background: #0f172a;
  color: #ffffff;
}

/* ========== Animation ========== */
.fade-in {
  animation: fadeIn 0.25s ease-out;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* ========== Responsive ========== */
@media (max-width: 640px) {
  .compta-title {
    font-size: 22px;
  }
  .compta-grid {
    grid-template-columns: 1fr;
  }
  .compta-card {
    padding: 18px;
  }
}
</style>
