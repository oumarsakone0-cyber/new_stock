<template>
  <SidebarLayout currentPage="comptabilites">
    <div class="banque-page">
      <!-- Header -->
      <header class="banque-header fade-in">
        <div>
          <h1 class="banque-title">Gestion Bancaire</h1>
          <p class="banque-subtitle">Comptes bancaires et transactions (debits/credits/transferts)</p>
        </div>
        <div class="header-actions">
          <button class="btn btn--dark" @click="goTo('/comptabilites/depenses')">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/>
              <path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/>
              <path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/>
            </svg>
            Depenses
          </button>
          <button class="btn btn--primary" @click="goTo('/comptabilites/banque/comptes')">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>
            </svg>
            Comptes
          </button>
          <button class="btn btn--primary" @click="goTo('/comptabilites/banque/transactions')">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/>
            </svg>
            Transactions
          </button>
        </div>
      </header>

      <!-- Cards Grid -->
      <div class="banque-grid fade-in">

        <!-- Card Comptes -->
        <div class="banque-card">
          <div class="card-icon card-icon--blue">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>
            </svg>
          </div>
          <h3 class="card-title">Comptes</h3>
          <p class="card-text">Creer / modifier vos comptes bancaires (banque, numero, devise, solde initial).</p>
          <button class="btn btn--dark btn--full" @click="goTo('/comptabilites/banque/comptes')">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            Aller
          </button>
        </div>

        <!-- Card Transactions -->
        <div class="banque-card">
          <div class="card-icon card-icon--green">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/>
            </svg>
          </div>
          <h3 class="card-title">Transactions</h3>
          <p class="card-text">Enregistrer les entrees/sorties, filtrer par periode et compte, suivre l'historique.</p>
          <button class="btn btn--dark btn--full" @click="goTo('/comptabilites/banque/transactions')">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            Aller
          </button>
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
.banque-page {
  padding: 0;
}

/* ========== Header ========== */
.banque-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
  margin-bottom: 24px;
}
.banque-title {
  font-size: 28px;
  font-weight: 800;
  color: #0f172a;
  margin: 0 0 4px 0;
  letter-spacing: -0.5px;
}
.banque-subtitle {
  font-size: 14px;
  color: #64748b;
  margin: 0;
  font-weight: 500;
}
.header-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

/* ========== Grid ========== */
.banque-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 16px;
}

/* ========== Card ========== */
.banque-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  transition: box-shadow 0.2s ease, transform 0.2s ease;
}
.banque-card:hover {
  box-shadow: 0 4px 24px rgba(15, 23, 42, 0.08);
  transform: translateY(-2px);
}

/* ========== Card Icon ========== */
.card-icon {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.card-icon--blue {
  background: #eff6ff;
  color: #2563eb;
}
.card-icon--green {
  background: #ecfdf5;
  color: #059669;
}

/* ========== Card Content ========== */
.card-title {
  margin: 0;
  font-size: 20px;
  font-weight: 800;
  color: #0f172a;
}
.card-text {
  margin: 0;
  color: #64748b;
  font-weight: 500;
  font-size: 14px;
  line-height: 1.5;
  flex: 1;
}

/* ========== Buttons ========== */
.btn {
  padding: 10px 18px;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 700;
  font-size: 13px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
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
.btn--dark {
  background: #0f172a;
  color: #ffffff;
}
.btn--full {
  width: 100%;
  padding: 12px 18px;
  font-size: 14px;
  border-radius: 12px;
  margin-top: auto;
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
  .banque-header {
    flex-direction: column;
    align-items: flex-start;
  }
  .banque-title {
    font-size: 22px;
  }
  .header-actions {
    width: 100%;
  }
  .header-actions .btn {
    flex: 1;
    justify-content: center;
  }
  .banque-grid {
    grid-template-columns: 1fr;
  }
  .banque-card {
    padding: 18px;
  }
}
</style>
