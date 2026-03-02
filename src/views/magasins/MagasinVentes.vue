<template>
  <SidebarLayout currentPage="ventes" :magasinId="magasinId">

    <!-- ======================================================= -->
    <!-- VUE CAISSE (POS) — plein écran                          -->
    <!-- ======================================================= -->
    <div v-if="showPOSView" :style="posContainerStyle">
      <!-- POS Header -->
      <div :style="posHeaderStyle">
        <div :style="posHeaderLeftStyle">
          <button :style="posBackBtnStyle" @click="closePOS">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            Retour aux ventes
          </button>
          <h2 :style="posTitleStyle">Nouvelle vente</h2>
        </div>
        <div :style="posHeaderRightStyle">
          <div :style="posClientWrapperStyle">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" :style="{ position: 'absolute', left: '14px', color: '#94a3b8' }">
              <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <input 
              v-model="clientName" 
              type="text" 
              :style="posClientInputStyle"
              placeholder="Nom du client (optionnel)"
            />
          </div>
        </div>
      </div>

      <!-- POS Body: left=cart, right=products -->
      <div :style="posBodyStyle">

        <!-- LEFT PANEL: Panier / Résumé -->
        <div :style="posLeftPanelStyle">
          <div :style="posCartHeaderStyle">
            <div :style="posCartHeaderLeftStyle">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="9" cy="21" r="1"/>
                <circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
              </svg>
              <span :style="posCartTitleTextStyle">Panier</span>
            </div>
            <span :style="posCartCountStyle">{{ cartItems.length }} article(s)</span>
          </div>

          <!-- Cart Items -->
          <div :style="posCartItemsWrapperStyle">
            <div v-if="cartItems.length === 0" :style="posEmptyCartStyle">
              <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" :style="{ color: '#cbd5e1' }">
                <circle cx="9" cy="21" r="1"/>
                <circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
              </svg>
              <p :style="posEmptyCartTextStyle">Cliquez sur un produit pour l'ajouter</p>
            </div>

            <div v-for="(item, idx) in cartItems" :key="idx" :style="posCartItemStyle">
              <div :style="posCartItemTopStyle">
                <div :style="posCartItemInfoStyle">
                  <p :style="posCartItemNameStyle">{{ item.nom }}</p>
                  <p :style="posCartItemStockStyle">Stock: {{ item.stockOriginal }}</p>
                </div>
                <button :style="posRemoveItemBtnStyle" @click="removeFromCart(idx)">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
              <div :style="posCartItemBottomStyle">
                <!-- Quantité -->
                <div :style="posQtyControlStyle">
                  <button :style="posQtyBtnStyle" @click="decrementQty(idx)">-</button>
                  <span :style="posQtyValueStyle">{{ item.qty }}</span>
                  <button :style="posQtyBtnStyle" @click="incrementQty(idx)">+</button>
                </div>
                <!-- Prix unitaire modifiable -->
                <div :style="posPriceEditWrapperStyle">
                  <span :style="posPriceLabelStyle">Prix:</span>
                  <input 
                    :value="item.prixVente"
                    @input="updateItemPrice(idx, $event)"
                    type="number"
                    min="0"
                    :style="posPriceInputStyle"
                  />
                  <span :style="posPriceCurrencyStyle">F</span>
                </div>
                <!-- Sous-total -->
                <span :style="posCartItemTotalStyle">{{ formatMoney(item.prixVente * item.qty) }}</span>
              </div>
            </div>
          </div>

          <!-- Cart Total + Payment -->
          <div :style="posCartFooterStyle">
            <div :style="posCartTotalRowStyle">
              <span :style="posCartTotalLabelStyle">Total</span>
              <span :style="posCartTotalValueStyle">{{ formatMoney(cartTotal) }}</span>
            </div>

            <!-- Mode de paiement -->
            <div :style="posPaymentTypesStyle">
              <button 
                :style="getPosPayTypeStyle('total')" 
                @click="paymentType = 'total'"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Total
              </button>
              <button 
                :style="getPosPayTypeStyle('partiel')" 
                @click="paymentType = 'partiel'; partialAmount = Math.floor(cartTotal / 2)"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="1" y="4" width="22" height="16" rx="2"/>
                  <line x1="1" y1="10" x2="23" y2="10"/>
                </svg>
                Partiel
              </button>
              <button 
                :style="getPosPayTypeStyle('credit')" 
                @click="paymentType = 'credit'"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Crédit
              </button>
            </div>

            <!-- Montant partiel -->
            <div v-if="paymentType === 'partiel'" :style="posPartialWrapperStyle">
              <label :style="posPartialLabelStyle">Montant payé maintenant</label>
              <input 
                v-model.number="partialAmount" 
                type="number" 
                min="1" 
                :max="cartTotal"
                :style="posPartialInputStyle"
              />
              <p :style="posPartialResteStyle">Reste: <strong>{{ formatMoney(cartTotal - partialAmount) }}</strong></p>
            </div>

            <!-- Bouton Valider -->
            <button 
              :style="posValidateBtnStyle"
              @click="confirmSale"
              :disabled="cartItems.length === 0 || saving"
            >
              <div v-if="saving" :style="smallSpinnerStyle"></div>
              <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ saving ? 'Enregistrement...' : 'Valider la vente' }}
            </button>
          </div>
        </div>

        <!-- RIGHT PANEL: Produits en grille -->
        <div :style="posRightPanelStyle">
          <!-- Search bar -->
          <div :style="posSearchWrapperStyle">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" :style="{ position: 'absolute', left: '14px', top: '50%', transform: 'translateY(-50%)', color: '#94a3b8' }">
              <circle cx="11" cy="11" r="8"/>
              <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input 
              v-model="productSearch" 
              type="text" 
              :style="posSearchInputStyle"
              placeholder="Rechercher un produit..."
            />
          </div>

          <!-- Products Grid -->
          <div :style="posProductsGridStyle">
            <div 
              v-for="product in filteredProducts" 
              :key="product.id"
              :style="getProductCardStyle(product)"
              @click="addProductToCart(product)"
              @mouseenter="hoveredProduct = product.id"
              @mouseleave="hoveredProduct = null"
            >
              <div :style="productCardTopStyle">
                <div :style="productCardIconStyle">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                  </svg>
                </div>
                <span :style="productStockBadgeStyle(product)">{{ product.stock }}</span>
              </div>
              <p :style="productCardNameStyle">{{ product.nom }}</p>
              <p :style="productCardPriceStyle">{{ formatMoney(product.prix) }}</p>
              <div v-if="getCartQty(product.id) > 0" :style="productCartBadgeStyle">
                {{ getCartQty(product.id) }} dans le panier
              </div>
            </div>

            <!-- Empty state -->
            <div v-if="filteredProducts.length === 0" :style="posNoProductStyle">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" :style="{ color: '#cbd5e1' }">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
              </svg>
              <p>Aucun produit trouvé</p>
            </div>
          </div>
        </div>
      </div>
    </div>


    <!-- ======================================================= -->
    <!-- VUE LISTE VENTES (page principale)                      -->
    <!-- ======================================================= -->
    <template v-else>
      <!-- Loading State -->
      <VentesFilterExport 
        :magasinId="magasinId"
        :apiBaseUrl="API_BASE_URL"
        @filter-applied="handleFilterApplied"
      />
      <div v-if="loading" :style="loadingContainerStyle">
        <div :style="spinnerStyle"></div>
        <p :style="loadingTextStyle">Chargement des ventes...</p>
      </div>

      <!-- Error State -->
      <div v-if="error && !loading" :style="errorContainerStyle">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" y1="8" x2="12" y2="12"/>
          <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <p :style="errorTextStyle">{{ error }}</p>
        <button :style="retryButtonStyle" @click="reloadAllData">Réessayer</button>
      </div>

      <!-- Main Content -->
      <div v-if="!loading">
        <!-- Premium Header -->
        <header :style="headerStyle" class="fade-in">
          <div :style="headerLeftStyle">
            <button 
              :style="backButtonStyle" 
              @click="goBack"
              @mouseenter="backHovered = true"
              @mouseleave="backHovered = false"
            >
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5M12 19l-7-7 7-7"/>
              </svg>
              <span>Retour</span>
            </button>
            <div :style="headerInfoStyle">
              <div :style="breadcrumbStyle">
                <span :style="breadcrumbItemStyle">{{ magasin.nom }}</span>
                <svg :style="breadcrumbArrowStyle" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span :style="breadcrumbActiveStyle">Ventes</span>
              </div>
              <h1 :style="titleStyle">Gestion des Ventes</h1>
              <p :style="subtitleStyle">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="9" cy="21" r="1"/>
                  <circle cx="20" cy="21" r="1"/>
                  <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
                </svg>
                {{ ventes.length }} transactions enregistrées
              </p>
            </div>
          </div>
          <button 
            :style="newSaleButtonStyle" 
            @click="openPOS"
            @mouseenter="newSaleHovered = true"
            @mouseleave="newSaleHovered = false"
            :disabled="saving"
          >
            <div :style="newSaleIconStyle">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
              </svg>
            </div>
            <span>Nouvelle vente</span>
          </button>
        </header>

        <!-- Enhanced Stats -->
        <div :style="statsGridStyle" class="fade-in" style="animation-delay: 0.1s">
          <div 
            :style="getStatCardStyle(0)"
            @mouseenter="hoveredStat = 0"
            @mouseleave="hoveredStat = null"
          >
            <div :style="statIconWrapperStyle('#10b981')">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div :style="statContentStyle">
              <p :style="statLabelStyle">Ventes aujourd'hui</p>
              <p :style="statValueStyle">{{ formatMoney(ventesAujourdhui) }}</p>
              <p :style="statSubStyle">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                </svg>
                {{ nbVentesAujourdhui }} transactions
              </p>
            </div>
          </div>
          
          <div 
            :style="getStatCardStyle(1)"
            @mouseenter="hoveredStat = 1"
            @mouseleave="hoveredStat = null"
          >
            <div :style="statIconWrapperStyle('#3b82f6')">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
              </svg>
            </div>
            <div :style="statContentStyle">
              <p :style="statLabelStyle">Ce mois</p>
              <p :style="statValueStyle">{{ formatMoney(ventesMois) }}</p>
              <p :style="statSubStyle">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                </svg>
                {{ nbVentesMois }} transactions
              </p>
            </div>
          </div>
          
          <div 
            :style="getStatCardStyle(2)"
            @mouseenter="hoveredStat = 2"
            @mouseleave="hoveredStat = null"
          >
            <div :style="statIconWrapperStyle('#ef4444')">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div :style="statContentStyle">
              <p :style="statLabelStyle">Crédits en cours</p>
              <p :style="statValueCreditStyle">{{ formatMoney(totalCredits) }}</p>
              <p :style="statSubStyle">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                {{ nbCredits }} clients
              </p>
            </div>
          </div>
        </div>

        <!-- Premium Tabs -->
        <div :style="tabsContainerStyle" class="fade-in" style="animation-delay: 0.2s">
          <button 
            v-for="tab in tabs" 
            :key="tab.id"
            :style="getTabStyle(tab.id)"
            @click="activeTab = tab.id"
            @mouseenter="hoveredTab = tab.id"
            @mouseleave="hoveredTab = null"
          >
            <span>{{ tab.label }}</span>
            <span v-if="tab.count" :style="tabBadgeStyle">{{ tab.count }}</span>
          </button>
        </div>

        <!-- Ventes List -->
        <div v-if="activeTab === 'ventes'" :style="tableContainerStyle" class="fade-in" style="animation-delay: 0.3s">
          <div :style="tableWrapperStyle">
            <table :style="tableStyle">
              <thead>
                <tr>
                  <th :style="thStyle"><div :style="thContentStyle"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>Référence</div></th>
                  <th :style="thStyle"><div :style="thContentStyle"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>Client</div></th>
                  <th :style="thStyle"><div :style="thContentStyle"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>Date</div></th>
                  <th :style="thStyle"><div :style="thContentStyle"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>Montant</div></th>
                  <th :style="thStyle"><div :style="thContentStyle"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Payé</div></th>
                  <th :style="thStyle"><div :style="thContentStyle"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>Statut</div></th>
                  <th :style="thStyle">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr 
                  v-for="(vente, index) in ventes" 
                  :key="vente.id" 
                  :style="getTrStyle(index)"
                  @mouseenter="hoveredRow = index"
                  @mouseleave="hoveredRow = null"
                >
                  <td :style="tdStyle"><span :style="refStyle">#{{ vente.id }}</span></td>
                  <td :style="tdStyle">
                    <div :style="clientCellStyle">
                      <div :style="clientAvatarStyle">{{ (vente.client || 'A')[0].toUpperCase() }}</div>
                      <span>{{ vente.client_nom || 'Client anonyme' }}</span>
                    </div>
                  </td>
                  <td :style="tdStyle">{{ vente.date }}</td>
                  <td :style="tdStyle"><span :style="amountStyle">{{ formatMoney(vente.montant) }}</span></td>
                  <td :style="tdStyle"><span :style="paidStyle">{{ formatMoney(vente.paye) }}</span></td>
                  <td :style="tdStyle">
                    <span :style="getStatusBadgeStyle(vente.statut)">
                      <span :style="statusDotStyle(vente.statut)"></span>
                      {{ vente.statut }}
                    </span>
                  </td>
                  <td :style="tdStyle">
                    <div :style="actionsStyle">
                      <button v-if="vente.statut !== 'Paye'" :style="actionButtonStyle('#10b981')" @click="openPaymentModal(vente)" title="Encaisser">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                      </button>
                      <ReceiptPDFButton :vente="vente" :magasinInfo="magasin" />
                      <button :style="actionButtonStyle('#3b82f6')" @click="viewVente(vente)" title="Détails">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Credits List -->
        <div v-if="activeTab === 'credits'" :style="tableContainerStyle" class="fade-in" style="animation-delay: 0.3s">
          <div :style="tableWrapperStyle">
            <div style="display: flex; justify-content: space-between; padding: 20px;">
              <h3>Liste des crédits en cours</h3>
              <ExportCreditsPDFButton :credits="credits" :magasinInfo="magasin" />
            </div>
            <table :style="tableStyle">
              <thead>
                <tr>
                  <th :style="thStyle"><div :style="thContentStyle"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>Client</div></th>
                  <th :style="thStyle"><div :style="thContentStyle"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>Réf vente</div></th>
                  <th :style="thStyle"><div :style="thContentStyle"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>Montant dû</div></th>
                  <th :style="thStyle"><div :style="thContentStyle"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Déjà payé</div></th>
                  <th :style="thStyle"><div :style="thContentStyle"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Reste</div></th>
                  <th :style="thStyle">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr 
                  v-for="(credit, index) in credits" 
                  :key="credit.id" 
                  :style="getTrStyle(index)"
                  @mouseenter="hoveredRow = index"
                  @mouseleave="hoveredRow = null"
                >
                  <td :style="tdStyle">
                    <div :style="creditClientCellStyle">
                      <div :style="clientAvatarStyle">{{ credit.client[0].toUpperCase() }}</div>
                      <div>
                        <p :style="clientNameStyle">{{ credit.client }}</p>
                        <p :style="clientPhoneStyle">
                          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
                          {{ credit.telephone }}
                        </p>
                      </div>
                    </div>
                  </td>
                  <td :style="tdStyle"><span :style="refStyle">#{{ credit.venteId }}</span></td>
                  <td :style="tdStyle"><span :style="amountStyle">{{ formatMoney(credit.montantTotal) }}</span></td>
                  <td :style="tdStyle"><span :style="paidStyle">{{ formatMoney(credit.paye) }}</span></td>
                  <td :style="tdStyle"><span :style="resteStyle">{{ formatMoney(credit.montantTotal - credit.paye) }}</span></td>
                  <td :style="tdStyle">
                    <button 
                      :style="encaisserButtonStyle" 
                      @click="openCreditPaymentModal(credit)"
                      @mouseenter="hoveredEncaisser = index"
                      @mouseleave="hoveredEncaisser = null"
                    >
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                      Encaisser
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Payment Modal (crédits uniquement) -->
        <Transition name="modal">
          <div v-if="showPaymentModal" :style="modalOverlayStyle" @click.self="closePaymentModal">
            <div :style="paymentModalStyle" class="modal-content">
              <div :style="modalHeaderStyle">
                <div>
                  <div :style="modalBadgeStyle">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                  </div>
                  <h2 :style="modalTitleStyle">Encaissement</h2>
                  <p :style="modalSubtitleStyle">Saisir un paiement partiel ou total</p>
                </div>
                <button :style="closeButtonStyle" @click="closePaymentModal">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
                </button>
              </div>
              <div :style="paymentContentStyle">
                <div :style="paymentInfoCardStyle">
                  <div :style="paymentInfoRowStyle">
                    <span :style="paymentInfoLabelStyle">Client</span>
                    <span :style="paymentClientStyle">{{ selectedCredit?.client || 'N/A' }}</span>
                  </div>
                  <div :style="paymentInfoRowStyle">
                    <span :style="paymentInfoLabelStyle">Montant du crédit</span>
                    <span :style="paymentAmountDisplayStyle">{{ formatMoney(selectedCredit?.montantTotal || 0) }}</span>
                  </div>
                  <div :style="paymentInfoRowStyle">
                    <span :style="paymentInfoLabelStyle">Déjà payé</span>
                    <span :style="paymentPayedStyle">{{ formatMoney(selectedCredit?.paye || 0) }}</span>
                  </div>
                  <div :style="paymentInfoRowDividerStyle"></div>
                  <div :style="paymentInfoRowStyle">
                    <span :style="paymentInfoLabelBoldStyle">Reste à payer</span>
                    <span :style="paymentResteStyle">{{ formatMoney((selectedCredit?.montantTotal || 0) - (selectedCredit?.paye || 0)) }}</span>
                  </div>
                </div>
                <div :style="formGroupStyle">
                  <label :style="labelStyle">Montant à encaisser <span :style="requiredStyle">*</span></label>
                  <div :style="paymentInputWrapperStyle">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                    <input v-model.number="paymentAmount" type="number" min="1" :style="paymentInputStyle" placeholder="Entrer le montant" />
                  </div>
                </div>
                <div :style="paymentActionsStyle">
                  <button type="button" :style="cancelButtonStyle" @click="closePaymentModal">Annuler</button>
                  <button 
                    type="button" 
                    :style="getConfirmPaymentButtonStyle" 
                    @click="processPayment"
                    @mouseenter="confirmPaymentHovered = true"
                    @mouseleave="confirmPaymentHovered = false"
                    :disabled="paymentAmount <= 0 || saving"
                  >
                    <div v-if="saving" :style="smallSpinnerStyle"></div>
                    <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ saving ? 'Traitement...' : "Confirmer l'encaissement" }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </Transition>
      </div>

      <ReceiptPDFButton :vente="vente" :magasinInfo="magasin" />
    </template>
  </SidebarLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import ExportCreditsPDFButton from './ExportCreditsPDFButton.vue'
import SidebarLayout from '../SidebarLayout.vue'
import VentesFilterExport from './VentesFilterExport.vue'
import ReceiptPDFButton from './ReceiptPDFButton.vue'

const router = useRouter()
const route = useRoute()

// API Configuration
const API_BASE_URL = 'https://sogetrag.com/apistok'

// State
const magasinId = computed(() => route.params.id)
const magasin = ref({ id: 1, nom: 'Boutique Centre-Ville' })

const loading = ref(false)
const saving = ref(false)
const error = ref(null)

const backHovered = ref(false)
const newSaleHovered = ref(false)
const hoveredStat = ref(null)
const hoveredTab = ref(null)
const hoveredRow = ref(null)
const hoveredAction = ref(null)
const hoveredEncaisser = ref(null)
const confirmPaymentHovered = ref(false)

const activeTab = ref('ventes')

// POS state
const showPOSView = ref(false)
const productSearch = ref('')
const hoveredProduct = ref(null)
const clientName = ref('')
const paymentType = ref('total')
const partialAmount = ref(0)
const cartItems = ref([])

// Payment modal (crédits)
const showPaymentModal = ref(false)
const selectedCredit = ref(null)
const paymentAmount = ref(0)

// Data
const ventes = ref([])
const credits = ref([])
const availableProducts = ref([])

const stats = ref({
  ventes_jour: { nombre_ventes: 0, montant_total: 0, montant_paye: 0 },
  ventes_mois: { nombre_ventes: 0, montant_total: 0, montant_paye: 0 },
  credits: { count: 0, total: 0 }
})

// Computed
const tabs = computed(() => [
  { id: 'ventes', label: 'Toutes les ventes' },
  { id: 'credits', label: 'Crédits', count: credits.value.length }
])

const ventesAujourdhui = computed(() => parseFloat(stats.value.ventes_jour?.montant_total || 0))
const nbVentesAujourdhui = computed(() => parseInt(stats.value.ventes_jour?.nombre_ventes || 0))
const ventesMois = computed(() => parseFloat(stats.value.ventes_mois?.montant_total || 0))
const nbVentesMois = computed(() => parseInt(stats.value.ventes_mois?.nombre_ventes || 0))
const totalCredits = computed(() => parseFloat(stats.value.credits?.total || 0))
const nbCredits = computed(() => parseInt(stats.value.credits?.count || 0))

const filteredProducts = computed(() => {
  const search = productSearch.value.toLowerCase().trim()
  if (!search) return availableProducts.value
  return availableProducts.value.filter(p => p.nom.toLowerCase().includes(search))
})

const cartTotal = computed(() => {
  return cartItems.value.reduce((sum, item) => {
    return sum + (item.prixVente * item.qty)
  }, 0)
})

const handleFilterApplied = (rapportData) => {
  ventes.value = rapportData.ventes.map(v => ({
    id: v.id,
    client: v.client_nom,
    date: new Date(v.date_vente).toLocaleDateString('fr-FR'),
    montant: parseFloat(v.montant_total),
    paye: parseFloat(v.montant_paye),
    statut: v.statut
  }))
}

// ========== API Functions ==========
const loadMagasinInfo = async () => {
  try {
    const response = await fetch(`${API_BASE_URL}/api_magasins.php?action=details&id=${magasinId.value}&_=${Math.random()}`)
    const data = await response.json()
    if (data.success) {
      magasin.value = data.data
    }
  } catch (err) {
    console.error('Erreur chargement magasin:', err)
  }
}

const loadProduits = async () => {
  try {
    const response = await fetch(
      `${API_BASE_URL}/api_produits.php?action=list&magasin_id=${magasinId.value}&_=${Math.random()}`
    )
    const data = await response.json()
    if (data.success) {
      availableProducts.value = data.data.map(p => ({
        id: p.id,
        nom: p.nom,
        prix: parseFloat(p.prix_vente),
        stock: p.stock_actuel
      }))
    }
  } catch (err) {
    console.error('Erreur chargement produits:', err)
  }
}

const loadVentes = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await fetch(
      `${API_BASE_URL}/api_ventes.php?action=list&magasin_id=${magasinId.value}&_=${Math.random()}`
    )
    const data = await response.json()
    if (data.success) {
      const ventesAvecDetails = await Promise.all(
        data.data.map(async (v) => {
          const detailsResponse = await fetch(
            `${API_BASE_URL}/api_ventes.php?action=details&id=${v.id}&_=${Math.random()}`
          )
          const detailsData = await detailsResponse.json()
          return {
            id: v.id,
            client_nom: v.client_nom,
            client_telephone: v.client_telephone || '',
            date_vente: v.date_vente,
            date: new Date(v.date_vente).toLocaleDateString('fr-FR'),
            montant_total: parseFloat(v.montant_total),
            montant_paye: parseFloat(v.montant_paye),
            montant: parseFloat(v.montant_total),
            paye: parseFloat(v.montant_paye),
            statut: v.statut,
            lignes: detailsData.success ? detailsData.data.lignes : []
          }
        })
      )
      ventes.value = ventesAvecDetails
    } else {
      error.value = data.error || 'Erreur lors du chargement des ventes'
    }
  } catch (err) {
    error.value = 'Impossible de se connecter au serveur'
    console.error('Erreur:', err)
  } finally {
    loading.value = false
  }
}

const loadStats = async () => {
  try {
    const response = await fetch(`${API_BASE_URL}/api_ventes.php?action=stats&magasin_id=${magasinId.value}&_=${Math.random()}`)
    const data = await response.json()
    if (data.success) {
      stats.value = data.data
    }
  } catch (err) {
    console.error('Erreur chargement stats:', err)
  }
}

const loadCredits = async () => {
  try {
    const response = await fetch(
      `${API_BASE_URL}/api_credits.php?action=list&magasin_id=${magasinId.value}&statut=Actif&_=${Math.random()}`
    )
    const data = await response.json()
    if (data.success) {
      credits.value = data.data.map(c => ({
        id: c.id,
        venteId: c.vente_id,
        client: c.client_nom,
        telephone: c.client_telephone,
        montantTotal: parseFloat(c.montant_total),
        paye: parseFloat(c.montant_paye)
      }))
    }
  } catch (err) {
    console.error('Erreur chargement crédits:', err)
  }
}

const reloadAllData = async () => {
  await Promise.all([
    loadVentes(),
    loadStats(),
    loadCredits(),
    loadProduits()
  ])
}

// ========== POS / Cart Functions ==========
const openPOS = () => {
  showPOSView.value = true
  cartItems.value = []
  clientName.value = ''
  paymentType.value = 'total'
  partialAmount.value = 0
  productSearch.value = ''
  // Recharger les produits pour avoir le stock frais
  loadProduits()
}

const closePOS = () => {
  showPOSView.value = false
  cartItems.value = []
  clientName.value = ''
  paymentType.value = 'total'
  partialAmount.value = 0
  productSearch.value = ''
}

const addProductToCart = (product) => {
  if (product.stock <= 0) return

  const existing = cartItems.value.find(item => item.id === product.id)
  if (existing) {
    if (existing.qty >= product.stock) {
      alert(`Stock insuffisant ! Stock disponible : ${product.stock}`)
      return
    }
    existing.qty += 1
  } else {
    cartItems.value.push({
      id: product.id,
      nom: product.nom,
      prix: product.prix,
      prixVente: product.prix, // prix modifiable par le vendeur
      qty: 1,
      stockOriginal: product.stock
    })
  }
}

const removeFromCart = (index) => {
  cartItems.value.splice(index, 1)
}

const incrementQty = (index) => {
  const item = cartItems.value[index]
  const product = availableProducts.value.find(p => p.id === item.id)
  if (product && item.qty < product.stock) {
    item.qty += 1
  } else {
    alert(`Stock insuffisant ! Stock disponible : ${product?.stock || 0}`)
  }
}

const decrementQty = (index) => {
  const item = cartItems.value[index]
  if (item.qty > 1) {
    item.qty -= 1
  } else {
    cartItems.value.splice(index, 1)
  }
}

const updateItemPrice = (index, event) => {
  const val = parseFloat(event.target.value)
  if (!isNaN(val) && val >= 0) {
    cartItems.value[index].prixVente = val
  }
}

const getCartQty = (productId) => {
  const item = cartItems.value.find(i => i.id === productId)
  return item ? item.qty : 0
}

// ========== Sale Functions ==========
const confirmSale = async () => {
  if (cartItems.value.length === 0) return
  saving.value = true
  try {
    const lignes = cartItems.value.map(item => ({
      produit_id: item.id,
      quantite: item.qty,
      prix_unitaire: item.prixVente,
      reduction: 0
    }))
    const payload = {
      magasin_id: parseInt(magasinId.value),
      client_nom: clientName.value || null,
      client_telephone: '',
      type_paiement: paymentType.value,
      lignes: lignes
    }
    if (paymentType.value === 'partiel') {
      payload.montant_paye = partialAmount.value
    }
    const response = await fetch(`${API_BASE_URL}/api_ventes.php?action=add`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })
    const data = await response.json()
    if (data.success) {
      await reloadAllData()
      closePOS()
      alert(`Vente #${data.data.vente_id} créée avec succès !`)
    } else {
      alert(data.error || 'Erreur lors de la création de la vente')
    }
  } catch (err) {
    alert('Erreur de connexion au serveur')
    console.error('Erreur:', err)
  } finally {
    saving.value = false
  }
}

// ========== Payment Functions (crédits) ==========
const openPaymentModal = (vente) => {
  const credit = credits.value.find(c => c.venteId === vente.id)
  if (credit) {
    selectedCredit.value = credit
    paymentAmount.value = credit.montantTotal - credit.paye
    showPaymentModal.value = true
  }
}

const openCreditPaymentModal = (credit) => {
  selectedCredit.value = credit
  paymentAmount.value = credit.montantTotal - credit.paye
  showPaymentModal.value = true
}

const closePaymentModal = () => {
  showPaymentModal.value = false
  selectedCredit.value = null
  paymentAmount.value = 0
}

const processPayment = async () => {
  if (!selectedCredit.value || paymentAmount.value <= 0) return
  saving.value = true
  try {
    const payload = {
      credit_id: selectedCredit.value.id,
      montant_paye: paymentAmount.value,
      utilisateur: 'Système'
    }
    const response = await fetch(`${API_BASE_URL}/api_ventes.php?action=paiement`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })
    const data = await response.json()
    if (data.success) {
      await reloadAllData()
      closePaymentModal()
      alert('Paiement enregistré avec succès !')
      window.location.reload()
    } else {
      alert(data.error || "Erreur lors de l'enregistrement du paiement")
    }
  } catch (err) {
    alert('Erreur de connexion au serveur')
    console.error('Erreur:', err)
  } finally {
    saving.value = false
  }
}

// ========== Other Functions ==========
const goBack = () => router.push(`/magasins/${magasinId.value}`)
const viewVente = (vente) => { /* existant */ }
const formatMoney = (amount) => new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF', minimumFractionDigits: 0 }).format(amount)

// Lifecycle
onMounted(() => {
  loadMagasinInfo()
  reloadAllData()
})


// ============================================================
// STYLES
// ============================================================

// --- POS Styles ---
const posContainerStyle = {
  position: 'fixed',
  top: 0,
  left: 0,
  right: 0,
  bottom: 0,
  zIndex: 900,
  backgroundColor: '#f1f5f9',
  display: 'flex',
  flexDirection: 'column',
  overflow: 'hidden'
}

const posHeaderStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  padding: '16px 24px',
  backgroundColor: 'white',
  borderBottom: '1px solid #e2e8f0',
  flexShrink: 0,
  flexWrap: 'wrap',
  gap: '12px'
}

const posHeaderLeftStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '16px'
}

const posHeaderRightStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '12px'
}

const posBackBtnStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '8px',
  padding: '10px 18px',
  backgroundColor: '#f8fafc',
  border: '1px solid #e2e8f0',
  borderRadius: '10px',
  cursor: 'pointer',
  fontSize: '14px',
  fontWeight: '600',
  color: '#475569',
  transition: 'all 0.2s'
}

const posTitleStyle = {
  fontSize: '22px',
  fontWeight: '700',
  color: '#0f172a',
  margin: 0,
  letterSpacing: '-0.01em'
}

const posClientWrapperStyle = {
  position: 'relative',
  display: 'flex',
  alignItems: 'center'
}

const posClientInputStyle = {
  padding: '10px 16px 10px 42px',
  border: '1px solid #e2e8f0',
  borderRadius: '10px',
  fontSize: '14px',
  fontWeight: '500',
  color: '#1e293b',
  width: '260px',
  outline: 'none'
}

const posBodyStyle = {
  display: 'flex',
  flex: 1,
  overflow: 'hidden'
}

const posLeftPanelStyle = {
  width: '400px',
  minWidth: '360px',
  backgroundColor: 'white',
  display: 'flex',
  flexDirection: 'column',
  borderRight: '1px solid #e2e8f0',
  flexShrink: 0
}

const posCartHeaderStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  padding: '16px 20px',
  borderBottom: '1px solid #e2e8f0',
  backgroundColor: '#f8fafc'
}

const posCartHeaderLeftStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '10px',
  color: '#334155',
  fontWeight: '700',
  fontSize: '15px'
}

const posCartTitleTextStyle = {
  fontWeight: '700',
  fontSize: '15px'
}

const posCartCountStyle = {
  fontSize: '13px',
  color: '#64748b',
  fontWeight: '600',
  backgroundColor: '#f1f5f9',
  padding: '4px 12px',
  borderRadius: '20px'
}

const posCartItemsWrapperStyle = {
  flex: 1,
  overflowY: 'auto',
  padding: '0'
}

const posEmptyCartStyle = {
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  justifyContent: 'center',
  padding: '60px 20px',
  gap: '16px'
}

const posEmptyCartTextStyle = {
  fontSize: '14px',
  color: '#94a3b8',
  fontWeight: '500',
  textAlign: 'center',
  margin: 0
}

const posCartItemStyle = {
  padding: '14px 20px',
  borderBottom: '1px solid #f1f5f9'
}

const posCartItemTopStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'flex-start',
  marginBottom: '10px'
}

const posCartItemInfoStyle = {
  flex: 1
}

const posCartItemNameStyle = {
  margin: 0,
  fontSize: '14px',
  fontWeight: '600',
  color: '#0f172a'
}

const posCartItemStockStyle = {
  margin: '2px 0 0 0',
  fontSize: '11px',
  color: '#94a3b8',
  fontWeight: '500'
}

const posRemoveItemBtnStyle = {
  width: '28px',
  height: '28px',
  borderRadius: '8px',
  border: 'none',
  backgroundColor: '#fee2e2',
  color: '#ef4444',
  cursor: 'pointer',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  transition: 'all 0.2s',
  flexShrink: 0
}

const posCartItemBottomStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '12px',
  justifyContent: 'space-between'
}

const posQtyControlStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '0',
  border: '1px solid #e2e8f0',
  borderRadius: '8px',
  overflow: 'hidden'
}

const posQtyBtnStyle = {
  width: '32px',
  height: '32px',
  border: 'none',
  backgroundColor: '#f8fafc',
  cursor: 'pointer',
  fontSize: '16px',
  fontWeight: '700',
  color: '#475569',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  transition: 'background-color 0.15s'
}

const posQtyValueStyle = {
  width: '36px',
  textAlign: 'center',
  fontSize: '14px',
  fontWeight: '700',
  color: '#0f172a',
  backgroundColor: 'white',
  lineHeight: '32px'
}

const posPriceEditWrapperStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '4px',
  flex: 1,
  maxWidth: '140px'
}

const posPriceLabelStyle = {
  fontSize: '12px',
  color: '#64748b',
  fontWeight: '600',
  whiteSpace: 'nowrap'
}

const posPriceInputStyle = {
  width: '80px',
  padding: '6px 8px',
  border: '1px solid #e2e8f0',
  borderRadius: '6px',
  fontSize: '13px',
  fontWeight: '700',
  color: '#0f172a',
  textAlign: 'right',
  outline: 'none'
}

const posPriceCurrencyStyle = {
  fontSize: '12px',
  color: '#94a3b8',
  fontWeight: '600'
}

const posCartItemTotalStyle = {
  fontWeight: '700',
  fontSize: '14px',
  color: '#10b981',
  whiteSpace: 'nowrap',
  minWidth: '70px',
  textAlign: 'right'
}

const posCartFooterStyle = {
  borderTop: '2px solid #e2e8f0',
  padding: '16px 20px',
  backgroundColor: 'white',
  flexShrink: 0
}

const posCartTotalRowStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  marginBottom: '16px'
}

const posCartTotalLabelStyle = {
  fontSize: '16px',
  fontWeight: '700',
  color: '#334155'
}

const posCartTotalValueStyle = {
  fontSize: '28px',
  fontWeight: '800',
  color: '#10b981',
  letterSpacing: '-0.02em'
}

const posPaymentTypesStyle = {
  display: 'flex',
  gap: '8px',
  marginBottom: '12px'
}

const getPosPayTypeStyle = (type) => ({
  flex: 1,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  gap: '6px',
  padding: '10px 8px',
  border: paymentType.value === type ? '2px solid #3b82f6' : '1px solid #e2e8f0',
  backgroundColor: paymentType.value === type ? '#eff6ff' : 'white',
  borderRadius: '10px',
  cursor: 'pointer',
  fontSize: '13px',
  fontWeight: '600',
  color: paymentType.value === type ? '#2563eb' : '#64748b',
  transition: 'all 0.2s'
})

const posPartialWrapperStyle = {
  marginBottom: '12px'
}

const posPartialLabelStyle = {
  display: 'block',
  fontSize: '12px',
  fontWeight: '600',
  color: '#475569',
  marginBottom: '6px'
}

const posPartialInputStyle = {
  width: '100%',
  padding: '10px 14px',
  border: '1px solid #e2e8f0',
  borderRadius: '8px',
  fontSize: '15px',
  fontWeight: '700',
  color: '#0f172a',
  outline: 'none',
  boxSizing: 'border-box'
}

const posPartialResteStyle = {
  marginTop: '6px',
  fontSize: '13px',
  color: '#ef4444',
  fontWeight: '500'
}

const posValidateBtnStyle = {
  width: '100%',
  padding: '14px',
  border: 'none',
  borderRadius: '12px',
  fontSize: '15px',
  fontWeight: '700',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  gap: '10px',
  transition: 'all 0.2s',
  letterSpacing: '0.01em'
}

const getPosValidateBtnStyle = computed(() => ({
  ...posValidateBtnStyle,
  background: cartItems.value.length > 0 && !saving.value
    ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)'
    : '#e2e8f0',
  color: cartItems.value.length > 0 && !saving.value ? 'white' : '#94a3b8',
  cursor: cartItems.value.length > 0 && !saving.value ? 'pointer' : 'not-allowed',
  boxShadow: cartItems.value.length > 0 ? '0 4px 14px rgba(16, 185, 129, 0.3)' : 'none'
}))

const posRightPanelStyle = {
  flex: 1,
  display: 'flex',
  flexDirection: 'column',
  overflow: 'hidden'
}

const posSearchWrapperStyle = {
  position: 'relative',
  padding: '16px 20px',
  backgroundColor: 'white',
  borderBottom: '1px solid #e2e8f0',
  flexShrink: 0
}

const posSearchInputStyle = {
  width: '100%',
  padding: '12px 16px 12px 44px',
  border: '1px solid #e2e8f0',
  borderRadius: '12px',
  fontSize: '14px',
  fontWeight: '500',
  color: '#1e293b',
  outline: 'none',
  boxSizing: 'border-box'
}

const posProductsGridStyle = {
  flex: 1,
  overflowY: 'auto',
  padding: '20px',
  display: 'grid',
  gridTemplateColumns: 'repeat(6, 1fr)',
  gap: '14px',
  alignContent: 'start'
}

const getProductCardStyle = (product) => ({
  backgroundColor: product.stock <= 0 ? '#f8fafc' : 'white',
  border: hoveredProduct.value === product.id && product.stock > 0
    ? '2px solid #10b981'
    : getCartQty(product.id) > 0
      ? '2px solid #3b82f6'
      : '1px solid #e2e8f0',
  borderRadius: '14px',
  padding: '16px',
  cursor: product.stock <= 0 ? 'not-allowed' : 'pointer',
  transition: 'all 0.2s cubic-bezier(0.4, 0, 0.2, 1)',
  opacity: product.stock <= 0 ? 0.5 : 1,
  transform: hoveredProduct.value === product.id && product.stock > 0 ? 'translateY(-2px)' : 'translateY(0)',
  boxShadow: hoveredProduct.value === product.id && product.stock > 0
    ? '0 8px 20px rgba(0, 0, 0, 0.08)'
    : '0 1px 3px rgba(0, 0, 0, 0.04)',
  display: 'flex',
  flexDirection: 'column',
  gap: '8px',
  position: 'relative'
})

const productCardTopStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'flex-start'
}

const productCardIconStyle = {
  width: '44px',
  height: '44px',
  borderRadius: '12px',
  background: 'linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%)',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  color: '#3b82f6'
}

const productStockBadgeStyle = (product) => ({
  fontSize: '11px',
  fontWeight: '700',
  padding: '3px 8px',
  borderRadius: '8px',
  backgroundColor: product.stock <= 0 ? '#fee2e2' : product.stock <= 5 ? '#fef3c7' : '#dcfce7',
  color: product.stock <= 0 ? '#991b1b' : product.stock <= 5 ? '#92400e' : '#166534'
})

const productCardNameStyle = {
  margin: 0,
  fontSize: '13px',
  fontWeight: '600',
  color: '#1e293b',
  lineHeight: '1.3',
  overflow: 'hidden',
  textOverflow: 'ellipsis',
  whiteSpace: 'nowrap'
}

const productCardPriceStyle = {
  margin: 0,
  fontSize: '15px',
  fontWeight: '700',
  color: '#10b981',
  letterSpacing: '-0.01em'
}

const productCartBadgeStyle = {
  position: 'absolute',
  top: '-6px',
  right: '-6px',
  backgroundColor: '#3b82f6',
  color: 'white',
  fontSize: '10px',
  fontWeight: '700',
  padding: '3px 8px',
  borderRadius: '10px',
  boxShadow: '0 2px 6px rgba(59, 130, 246, 0.3)'
}

const posNoProductStyle = {
  gridColumn: '1 / -1',
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  justifyContent: 'center',
  padding: '60px 20px',
  gap: '12px',
  color: '#94a3b8',
  fontSize: '14px',
  fontWeight: '500'
}


// --- Original List View Styles ---
const loadingContainerStyle = {
  textAlign: 'center',
  padding: '80px 20px',
  color: '#64748b'
}

const loadingTextStyle = {
  marginTop: '16px',
  fontSize: '16px',
  fontWeight: '500'
}

const spinnerStyle = {
  width: '48px',
  height: '48px',
  margin: '0 auto',
  border: '4px solid #f1f5f9',
  borderTop: '4px solid #10b981',
  borderRadius: '50%',
  animation: 'spin 1s linear infinite'
}

const smallSpinnerStyle = {
  width: '18px',
  height: '18px',
  border: '2px solid rgba(255, 255, 255, 0.3)',
  borderTop: '2px solid white',
  borderRadius: '50%',
  animation: 'spin 1s linear infinite'
}

const errorContainerStyle = {
  textAlign: 'center',
  padding: '80px 20px',
  color: '#ef4444'
}

const errorTextStyle = {
  marginTop: '16px',
  fontSize: '16px',
  fontWeight: '500'
}

const retryButtonStyle = {
  marginTop: '24px',
  padding: '12px 32px',
  background: 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
  color: 'white',
  border: 'none',
  borderRadius: '12px',
  cursor: 'pointer',
  fontSize: '14px',
  fontWeight: '600',
  boxShadow: '0 4px 12px rgba(16, 185, 129, 0.25)'
}

const headerStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'flex-start',
  marginBottom: '32px',
  flexWrap: 'wrap',
  gap: '20px'
}

const headerLeftStyle = {
  display: 'flex',
  alignItems: 'flex-start',
  gap: '16px',
  flexWrap: 'wrap'
}

const headerInfoStyle = {
  display: 'flex',
  flexDirection: 'column',
  gap: '6px'
}

const breadcrumbStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '6px',
  marginBottom: '4px'
}

const breadcrumbItemStyle = {
  fontSize: '13px',
  color: '#94a3b8',
  fontWeight: '500'
}

const breadcrumbArrowStyle = {
  width: '14px',
  height: '14px',
  color: '#cbd5e1',
  strokeWidth: '2'
}

const breadcrumbActiveStyle = {
  fontSize: '13px',
  color: '#1e293b',
  fontWeight: '600'
}

const backButtonStyle = computed(() => ({
  display: 'flex',
  alignItems: 'center',
  gap: '8px',
  padding: '12px 18px',
  backgroundColor: backHovered.value ? '#f8fafc' : 'white',
  border: '1px solid #e2e8f0',
  borderRadius: '12px',
  cursor: 'pointer',
  fontSize: '14px',
  fontWeight: '600',
  color: '#475569',
  transition: 'all 0.2s cubic-bezier(0.4, 0, 0.2, 1)',
  boxShadow: backHovered.value ? '0 4px 12px rgba(0, 0, 0, 0.08)' : '0 1px 3px rgba(0, 0, 0, 0.05)',
  transform: backHovered.value ? 'translateX(-4px)' : 'translateX(0)',
  letterSpacing: '0.01em'
}))

const titleStyle = {
  fontSize: '32px',
  fontWeight: '700',
  color: '#0f172a',
  margin: '0',
  letterSpacing: '-0.02em'
}

const subtitleStyle = {
  fontSize: '14px',
  color: '#64748b',
  margin: '0',
  display: 'flex',
  alignItems: 'center',
  gap: '6px',
  fontWeight: '500'
}

const newSaleButtonStyle = computed(() => ({
  display: 'flex',
  alignItems: 'center',
  gap: '10px',
  padding: '14px 24px',
  background: newSaleHovered.value && !saving.value
    ? 'linear-gradient(135deg, #059669 0%, #047857 100%)'
    : 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
  color: 'white',
  border: 'none',
  borderRadius: '12px',
  cursor: saving.value ? 'not-allowed' : 'pointer',
  fontSize: '14px',
  fontWeight: '600',
  transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
  boxShadow: newSaleHovered.value && !saving.value
    ? '0 12px 28px rgba(16, 185, 129, 0.35)'
    : '0 4px 12px rgba(16, 185, 129, 0.25)',
  transform: newSaleHovered.value && !saving.value ? 'translateY(-2px)' : 'translateY(0)',
  letterSpacing: '0.01em',
  opacity: saving.value ? 0.6 : 1
}))

const newSaleIconStyle = {
  width: '32px',
  height: '32px',
  background: 'rgba(255, 255, 255, 0.2)',
  borderRadius: '8px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center'
}

const statsGridStyle = {
  display: 'grid',
  gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))',
  gap: '20px',
  marginBottom: '32px'
}

const getStatCardStyle = (index) => ({
  backgroundColor: 'white',
  borderRadius: '20px',
  padding: '24px',
  display: 'flex',
  alignItems: 'center',
  gap: '16px',
  boxShadow: hoveredStat.value === index 
    ? '0 12px 32px rgba(0, 0, 0, 0.1)' 
    : '0 2px 8px rgba(0, 0, 0, 0.06)',
  border: '1px solid #f1f5f9',
  transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)',
  transform: hoveredStat.value === index ? 'translateY(-4px)' : 'translateY(0)',
  cursor: 'default'
})

const statIconWrapperStyle = (color) => ({
  width: '56px',
  height: '56px',
  background: `${color}12`,
  borderRadius: '16px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  color: color,
  flexShrink: 0
})

const statContentStyle = { flex: 1, display: 'flex', flexDirection: 'column', gap: '4px' }
const statLabelStyle = { fontSize: '13px', color: '#64748b', margin: '0', fontWeight: '500' }
const statValueStyle = { fontSize: '26px', fontWeight: '700', color: '#0f172a', margin: '0', letterSpacing: '-0.02em' }
const statValueCreditStyle = { fontSize: '26px', fontWeight: '700', color: '#ef4444', margin: '0', letterSpacing: '-0.02em' }
const statSubStyle = { fontSize: '12px', color: '#94a3b8', margin: '0', display: 'flex', alignItems: 'center', gap: '4px', fontWeight: '500' }

const tabsContainerStyle = {
  display: 'flex', gap: '8px', marginBottom: '24px', backgroundColor: '#f1f5f9',
  padding: '6px', borderRadius: '12px', width: 'fit-content', flexWrap: 'wrap'
}

const getTabStyle = (tabId) => ({
  display: 'flex', alignItems: 'center', gap: '8px', padding: '12px 20px',
  border: 'none', borderRadius: '10px', fontSize: '14px', fontWeight: '600', cursor: 'pointer',
  backgroundColor: activeTab.value === tabId ? 'white' : 'transparent',
  color: activeTab.value === tabId ? '#1e293b' : '#64748b',
  boxShadow: activeTab.value === tabId ? '0 2px 8px rgba(0, 0, 0, 0.06)' : 'none',
  transition: 'all 0.2s', letterSpacing: '0.01em'
})

const tabBadgeStyle = { backgroundColor: '#ef4444', color: 'white', padding: '3px 10px', borderRadius: '12px', fontSize: '12px', fontWeight: '700' }

const tableContainerStyle = { backgroundColor: 'white', borderRadius: '20px', overflow: 'hidden', boxShadow: '0 2px 8px rgba(0, 0, 0, 0.06)', border: '1px solid #f1f5f9' }
const tableWrapperStyle = { overflowX: 'auto' }
const tableStyle = { width: '100%', borderCollapse: 'collapse', minWidth: '900px' }

const thStyle = {
  textAlign: 'left', padding: '18px 20px', fontSize: '12px', fontWeight: '700',
  color: '#64748b', textTransform: 'uppercase', backgroundColor: '#f8fafc',
  borderBottom: '2px solid #e2e8f0', whiteSpace: 'nowrap', letterSpacing: '0.05em'
}

const thContentStyle = { display: 'flex', alignItems: 'center', gap: '6px' }

const getTrStyle = (index) => ({
  borderBottom: '1px solid #f1f5f9',
  backgroundColor: hoveredRow.value === index ? '#f8fafc' : 'transparent',
  transition: 'background-color 0.2s'
})

const tdStyle = { padding: '18px 20px', fontSize: '14px', color: '#1e293b', fontWeight: '500' }
const refStyle = { fontWeight: '700', color: '#3b82f6', fontSize: '14px' }
const clientCellStyle = { display: 'flex', alignItems: 'center', gap: '12px' }
const clientAvatarStyle = {
  width: '36px', height: '36px', borderRadius: '10px',
  background: 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)',
  color: 'white', display: 'flex', alignItems: 'center', justifyContent: 'center',
  fontSize: '14px', fontWeight: '700', flexShrink: 0
}
const creditClientCellStyle = { display: 'flex', alignItems: 'center', gap: '12px' }
const clientNameStyle = { margin: '0', fontWeight: '600', fontSize: '14px', color: '#0f172a' }
const clientPhoneStyle = { margin: '4px 0 0 0', fontSize: '12px', color: '#94a3b8', display: 'flex', alignItems: 'center', gap: '4px', fontWeight: '500' }
const amountStyle = { fontWeight: '700', color: '#0f172a', fontSize: '14px' }
const paidStyle = { fontWeight: '600', color: '#10b981', fontSize: '14px' }
const resteStyle = { fontWeight: '700', color: '#ef4444', fontSize: '15px' }

const getStatusBadgeStyle = (statut) => ({
  display: 'inline-flex', alignItems: 'center', gap: '6px', padding: '6px 14px',
  borderRadius: '12px', fontSize: '12px', fontWeight: '600',
  backgroundColor: statut === 'Paye' ? '#dcfce7' : statut === 'Partiel' ? '#fef3c7' : '#fee2e2',
  color: statut === 'Paye' ? '#166534' : statut === 'Partiel' ? '#92400e' : '#991b1b',
  whiteSpace: 'nowrap', letterSpacing: '0.01em'
})

const statusDotStyle = (statut) => ({
  width: '6px', height: '6px', borderRadius: '50%',
  backgroundColor: statut === 'Paye' ? '#22c55e' : statut === 'Partiel' ? '#f59e0b' : '#ef4444'
})

const actionsStyle = { display: 'flex', gap: '8px' }
const actionButtonStyle = (color) => ({
  width: '36px', height: '36px', borderRadius: '10px', border: `1px solid ${color}20`,
  backgroundColor: `${color}10`, cursor: 'pointer', display: 'flex', alignItems: 'center',
  justifyContent: 'center', color: color, transition: 'all 0.2s'
})

const encaisserButtonStyle = computed(() => ({
  display: 'flex', alignItems: 'center', gap: '6px', padding: '8px 16px',
  background: 'linear-gradient(135deg, #10b981 0%, #059669 100%)', color: 'white',
  border: 'none', borderRadius: '10px', fontSize: '13px', fontWeight: '600',
  cursor: 'pointer', transition: 'all 0.2s', boxShadow: '0 2px 8px rgba(16, 185, 129, 0.25)',
  letterSpacing: '0.01em'
}))

// Modal styles (encaissement crédits uniquement)
const modalOverlayStyle = {
  position: 'fixed', top: 0, left: 0, right: 0, bottom: 0,
  backgroundColor: 'rgba(15, 23, 42, 0.7)', backdropFilter: 'blur(8px)',
  display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 1000, padding: '20px'
}

const paymentModalStyle = {
  backgroundColor: 'white', borderRadius: '24px', width: '100%', maxWidth: '560px',
  maxHeight: '90vh', overflow: 'auto', boxShadow: '0 25px 50px -12px rgba(0, 0, 0, 0.25)'
}

const modalHeaderStyle = {
  display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start',
  padding: '32px 32px 24px', borderBottom: '1px solid #f1f5f9'
}

const modalBadgeStyle = {
  width: '48px', height: '48px', background: 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
  borderRadius: '14px', display: 'flex', alignItems: 'center', justifyContent: 'center',
  color: 'white', marginBottom: '16px'
}

const modalTitleStyle = { fontSize: '24px', fontWeight: '700', color: '#0f172a', margin: '0 0 6px 0', letterSpacing: '-0.01em' }
const modalSubtitleStyle = { fontSize: '14px', color: '#64748b', margin: 0, fontWeight: '500' }

const closeButtonStyle = { background: 'none', border: 'none', cursor: 'pointer', color: '#94a3b8', padding: '8px', borderRadius: '8px', transition: 'all 0.2s' }

const paymentContentStyle = { padding: '24px 32px 32px' }
const formGroupStyle = { marginBottom: '20px' }
const labelStyle = { display: 'block', fontSize: '14px', fontWeight: '600', color: '#334155', marginBottom: '8px', letterSpacing: '0.01em' }
const requiredStyle = { color: '#ef4444', marginLeft: '2px' }

const paymentInfoCardStyle = { backgroundColor: '#f8fafc', borderRadius: '16px', padding: '20px', marginBottom: '24px', border: '1px solid #e2e8f0' }
const paymentInfoRowStyle = { display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '10px 0' }
const paymentInfoRowDividerStyle = { height: '1px', backgroundColor: '#e2e8f0', margin: '8px 0' }
const paymentInfoLabelStyle = { fontSize: '13px', color: '#64748b', fontWeight: '500' }
const paymentInfoLabelBoldStyle = { fontSize: '14px', color: '#0f172a', fontWeight: '700' }
const paymentClientStyle = { fontSize: '14px', fontWeight: '600', color: '#0f172a' }
const paymentAmountDisplayStyle = { fontSize: '14px', fontWeight: '700', color: '#3b82f6' }
const paymentPayedStyle = { fontSize: '14px', fontWeight: '600', color: '#10b981' }
const paymentResteStyle = { fontSize: '16px', fontWeight: '700', color: '#ef4444', letterSpacing: '-0.01em' }

const paymentInputWrapperStyle = { position: 'relative', display: 'flex', alignItems: 'center' }
const paymentInputStyle = { width: '100%', padding: '12px 16px 12px 44px', border: '1px solid #e2e8f0', borderRadius: '12px', fontSize: '16px', fontWeight: '700', color: '#0f172a', textAlign: 'left' }

const paymentActionsStyle = { display: 'flex', gap: '12px', marginTop: '28px', paddingTop: '24px', borderTop: '1px solid #f1f5f9' }
const cancelButtonStyle = { flex: 1, padding: '14px', backgroundColor: '#f8fafc', border: '1px solid #e2e8f0', borderRadius: '12px', fontSize: '14px', fontWeight: '600', color: '#64748b', cursor: 'pointer', transition: 'all 0.2s', letterSpacing: '0.01em' }

const getConfirmPaymentButtonStyle = computed(() => ({
  flex: 1, padding: '14px',
  background: confirmPaymentHovered.value && paymentAmount.value > 0 && !saving.value
    ? 'linear-gradient(135deg, #059669 0%, #047857 100%)'
    : 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
  border: 'none', borderRadius: '12px', fontSize: '14px', fontWeight: '600', color: 'white',
  cursor: (paymentAmount.value > 0 && !saving.value) ? 'pointer' : 'not-allowed',
  display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '8px',
  transition: 'all 0.2s', boxShadow: '0 4px 12px rgba(16, 185, 129, 0.25)',
  opacity: (paymentAmount.value <= 0 || saving.value) ? 0.5 : 1, letterSpacing: '0.01em'
}))
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

* {
  font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.fade-in {
  animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) backwards;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.modal-enter-active, .modal-leave-active {
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.modal-enter-from, .modal-leave-to {
  opacity: 0;
}

.modal-enter-from .modal-content,
.modal-leave-to .modal-content {
  transform: scale(0.95) translateY(20px);
}

.modal-content {
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
</style>
