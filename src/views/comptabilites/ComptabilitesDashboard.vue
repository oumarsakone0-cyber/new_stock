<template>
  <SidebarLayout currentPage="comptabilites">
    <div>
      <header :style="headerStyle" class="fade-in">
        <div>
          <h1 :style="titleStyle">Comptabilités</h1>
          <p :style="subtitleStyle">Gérez les dépenses, pièces justificatives et la gestion bancaire</p>
        </div>
      </header>

      <div :style="cardsGridStyle" class="fade-in">
        <div :style="bigCardStyle">
          <h3 :style="cardTitleStyle">Gestion des dépenses</h3>
          <p :style="cardTextStyle">Dépenses avec filtres par période, motifs et pièces justificatives (factures).</p>
          <div :style="{display:'flex', gap:'10px', flexWrap:'wrap'}">
            <button :style="primaryBtnStyle" @click="goTo('/comptabilites/depenses')">Ouvrir</button>
            <button :style="secondaryBtnStyle" @click="goTo('/comptabilites/motifs')">Motifs</button>
            <button :style="secondaryBtnStyle" @click="goTo('/comptabilites/factures')">Factures</button>
          </div>
        </div>

        <div :style="bigCardStyle">
          <h3 :style="cardTitleStyle">Gestion bancaire</h3>
          <p :style="cardTextStyle">Comptes bancaires, transactions (débit/crédit), historique et soldes.</p>
          <div :style="{display:'flex', gap:'10px', flexWrap:'wrap'}">
            <button :style="primaryBtnStyle" @click="goTo('/comptabilites/banque')">Ouvrir</button>
            <button :style="secondaryBtnStyle" @click="goTo('/comptabilites/banque/comptes')">Comptes</button>
            <button :style="secondaryBtnStyle" @click="goTo('/comptabilites/banque/transactions')">Transactions</button>
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

const headerStyle = { display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '12px', flexWrap: 'wrap', marginBottom: '18px' }
const titleStyle = { fontSize: '32px', fontWeight: '800', color: '#0f172a', margin: '0 0 4px 0' }
const subtitleStyle = { fontSize: '14px', color: '#64748b', margin: 0 }
const primaryBtnStyle = { padding: '12px 18px', background: 'linear-gradient(135deg, #10b981 0%, #059669 100%)', color: 'white', border: 'none', borderRadius: '12px', cursor: 'pointer', fontWeight: '700' }
const secondaryBtnStyle = { padding: '12px 18px', background: '#0f172a', color: 'white', border: 'none', borderRadius: '12px', cursor: 'pointer', fontWeight: '700' }
const cardsGridStyle = { display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(320px, 1fr))', gap: '12px' }
const bigCardStyle = { background: 'white', border: '1px solid #e2e8f0', borderRadius: '16px', padding: '18px', display: 'grid', gap: '10px' }
const cardTitleStyle = { margin: 0, fontSize: '18px', fontWeight: '900', color: '#0f172a' }
const cardTextStyle = { margin: 0, color: '#64748b', fontWeight: '600' }
</script>

<style scoped>
.fade-in { animation: fadeIn .2s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
</style>

