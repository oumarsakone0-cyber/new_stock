<template>
  <SidebarLayout currentPage="produits">

    <!-- ══════════════════════════════════════════
         HEADER
    ══════════════════════════════════════════ -->
    <header :style="headerStyle" class="p-fade">
      <div>
        <div :style="breadcrumbStyle">
          <span :style="bcItem">Tableau de bord</span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
          <span :style="bcActive">Produits & Stock</span>
        </div>
        <h1 :style="titleStyle">Produits & Stock</h1>
        <p :style="subtitleStyle">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
          {{ filteredProduits.length }} produit(s) · {{ boutiques.length }} boutique(s) · {{ entrepots.length }} entrepôt(s)
        </p>
        <!-- Onglets vues -->
        <div :style="tabsRow">
          <button v-for="v in VIEWS" :key="v.key" :style="getViewTab(v.key)" @click="activeView = v.key">
            <span style="font-size:16px">{{ v.icon }}</span>
            {{ v.label }}
            <span v-if="v.count !== undefined" :style="tabCount(v.key)">{{ v.count }}</span>
          </button>
        </div>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-start">
        <button :style="secBtnStyle" @click="openBoutiqueModal(null)">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          Boutiques
        </button>
        <button :style="primaryBtnStyle" @click="openProduitModal(null)">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
          Nouveau Produit
        </button>
      </div>
    </header>

    <!-- ══════════════════════════════════════════
         STATS
    ══════════════════════════════════════════ -->
    <div :style="statsRow" class="p-fade" style="animation-delay:.06s">
      <div v-for="s in statsCards" :key="s.label" :style="s.cardStyle||statCard">
        <div :style="statIcon(s.color)">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" v-html="s.icon"></svg>
        </div>
        <div>
          <div :style="{ ...statVal, color: s.color }">{{ s.value }}</div>
          <div :style="statLbl">{{ s.label }}</div>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════════
         BARRE FILTRES / RECHERCHE
    ══════════════════════════════════════════ -->
    <div :style="filterBarStyle" class="p-fade" style="animation-delay:.1s">
      <div style="position:relative;flex:1;min-width:200px">
        <svg style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#94a3b8;pointer-events:none" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        <input v-model="search" :style="searchInputStyle" placeholder="Rechercher par nom, référence, catégorie..."/>
      </div>
      <select v-model="filterCat" :style="filterSelectStyle">
        <option value="">Toutes catégories</option>
        <option v-for="c in CATEGORIES" :key="c" :value="c">{{ c }}</option>
      </select>
      <select v-model="filterStock" :style="filterSelectStyle">
        <option value="">Tout le stock</option>
        <option value="ok">Stock suffisant (&gt;20)</option>
        <option value="low">Stock faible (≤10)</option>
        <option value="out">Rupture de stock</option>
      </select>
      <button v-if="search || filterCat || filterStock" :style="clearBtnStyle" @click="search='';filterCat='';filterStock=''">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
        Effacer
      </button>
    </div>

    <!-- ══════════════════════════════════════════
         VUE GRILLE PRODUITS
    ══════════════════════════════════════════ -->
    <div v-if="activeView === 'grid'" :style="prodGridStyle" class="p-fade" style="animation-delay:.14s">
      <div v-if="!filteredProduits.length" :style="emptyStateStyle">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg>
        <p style="font-size:16px;font-weight:700;color:#475569;margin:12px 0 4px">Aucun produit trouvé</p>
        <p style="font-size:13px;color:#94a3b8">Modifiez votre recherche ou créez un nouveau produit</p>
      </div>
      <div
        v-for="(p, i) in filteredProduits"
        :key="p.id"
        :style="getProdCard(i)"
        @mouseenter="hovCard = i"
        @mouseleave="hovCard = null"
        class="prod-item"
      >
        <!-- Bande couleur selon statut stock -->
        <div :style="prodAccent(p)"></div>

        <!-- Actions rapides -->
        <div :style="prodCardActions">
          <button :style="pca('edit')" @click.stop="openProduitModal(p)" title="Modifier">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          </button>
          <button :style="pca('stock')" @click.stop="openStockModal(p)" title="Ajuster stock">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="15" rx="2"/><path d="M16 3H8a1 1 0 00-1 1v3h10V4a1 1 0 00-1-1z"/></svg>
          </button>
          <button :style="pca('transfer')" @click.stop="openTransferModal(p)" title="Transférer">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
          </button>
          <button :style="pca('delete')" @click.stop="deleteProduit(p.id)" title="Supprimer">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
          </button>
        </div>

        <div style="text-align:center;padding:14px 12px 6px">
          <span style="font-size:38px">{{ p.emoji || '📦' }}</span>
        </div>
        <div style="padding:0 14px 6px;text-align:center">
          <div style="font-size:13px;font-weight:800;color:#0f172a;line-height:1.3;margin-bottom:3px">{{ p.nom }}</div>
          <div style="font-size:10px;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.05em">{{ p.reference || '—' }} · {{ p.categorie }}</div>
        </div>

        <!-- Prix -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;padding:8px 12px;background:#f8fafc;margin:0 12px;border-radius:8px;margin-bottom:10px">
          <div>
            <div style="font-size:9px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px">Prix vente</div>
            <div style="font-size:14px;font-weight:800;color:#0ea5e9">{{ formatAmount(p.prix_vente) }}</div>
          </div>
          <div>
            <div style="font-size:9px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px">Prix achat</div>
            <div style="font-size:12px;font-weight:700;color:#64748b">{{ formatAmount(p.prix_achat) }}</div>
          </div>
        </div>

        <!-- Stock -->
        <div style="padding:8px 14px 14px">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
            <span style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.04em">Stock</span>
            <span :style="stockBadge(p)">{{ p.stock }} {{ p.unite || 'unités' }}</span>
          </div>
          <div :style="stockTrack">
            <div :style="stockFill(p)"></div>
          </div>
          <div v-if="p.stock_min && p.stock <= p.stock_min && p.stock > 0" style="font-size:10px;color:#f59e0b;font-weight:700;margin-top:4px">⚠ Sous le seuil minimum ({{ p.stock_min }})</div>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════════
         VUE LISTE PRODUITS
    ══════════════════════════════════════════ -->
    <div v-if="activeView === 'list'" :style="tableSection" class="p-fade" style="animation-delay:.14s">
      <div :style="tableWrap">
        <table :style="tableStyle">
          <thead>
            <tr :style="theadTr">
              <th :style="th">Produit</th>
              <th :style="th">Référence</th>
              <th :style="th">Catégorie</th>
              <th :style="th">Prix vente</th>
              <th :style="th">Prix achat</th>
              <th :style="th">Marge</th>
              <th :style="th">Stock</th>
              <th :style="th">Seuil min</th>
              <th :style="th">Statut</th>
              <th :style="th">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!filteredProduits.length">
              <td colspan="10" :style="emptyTd">Aucun produit trouvé</td>
            </tr>
            <tr
              v-for="(p, i) in filteredProduits"
              :key="p.id"
              :style="getTr(i)"
              @mouseenter="hovRow = i"
              @mouseleave="hovRow = null"
            >
              <td :style="td">
                <div style="display:flex;align-items:center;gap:10px">
                  <span style="font-size:22px">{{ p.emoji || '📦' }}</span>
                  <div>
                    <div style="font-size:13px;font-weight:700;color:#0f172a">{{ p.nom }}</div>
                    <div style="font-size:11px;color:#94a3b8">{{ p.unite || 'unité' }}</div>
                  </div>
                </div>
              </td>
              <td :style="td"><span style="font-family:monospace;font-size:12px;color:#64748b;font-weight:600;background:#f1f5f9;padding:3px 8px;border-radius:5px">{{ p.reference || '—' }}</span></td>
              <td :style="td"><span :style="catPill(p.categorie)">{{ p.categorie }}</span></td>
              <td :style="td"><span style="font-size:13px;font-weight:800;color:#0ea5e9">{{ formatAmount(p.prix_vente) }}</span></td>
              <td :style="td"><span style="font-size:13px;font-weight:600;color:#64748b">{{ formatAmount(p.prix_achat) }}</span></td>
              <td :style="td">
                <span :style="{ fontSize:'12px', fontWeight:'700', color: (p.prix_vente - p.prix_achat) > 0 ? '#10b981' : '#ef4444' }">
                  {{ formatAmount(p.prix_vente - p.prix_achat) }}
                  <span style="font-weight:500;opacity:.8">({{ p.prix_achat > 0 ? Math.round((p.prix_vente - p.prix_achat) / p.prix_achat * 100) : 0 }}%)</span>
                </span>
              </td>
              <td :style="td"><span :style="stockBadgeInline(p)">{{ p.stock }}</span></td>
              <td :style="td"><span style="font-size:12px;color:#94a3b8">{{ p.stock_min || '—' }}</span></td>
              <td :style="td"><span :style="statusPill(p)">{{ getStockStatus(p) }}</span></td>
              <td :style="td">
                <div style="display:flex;gap:5px">
                  <button :style="aBtn('#3b82f6')" @click="openProduitModal(p)" title="Modifier">✏️</button>
                  <button :style="aBtn('#10b981')" @click="openStockModal(p)" title="Ajuster stock">📦</button>
                  <button :style="aBtn('#8b5cf6')" @click="openTransferModal(p)" title="Transférer">➡️</button>
                  <button :style="aBtn('#ef4444')" @click="deleteProduit(p.id)" title="Supprimer">🗑️</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ══════════════════════════════════════════
         VUE BOUTIQUES
    ══════════════════════════════════════════ -->
    <div v-if="activeView === 'boutiques'" class="p-fade" style="animation-delay:.14s">
      <div :style="boutiqueGridStyle">
        <!-- Card vide si aucune boutique -->
        <div v-if="!boutiques.length" :style="{ ...emptyStateStyle, gridColumn: '1/-1' }">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
          <p style="font-size:16px;font-weight:700;color:#475569;margin:12px 0 8px">Aucune boutique créée</p>
          <button :style="primaryBtnStyle" @click="openBoutiqueModal(null)">Créer la première boutique</button>
        </div>

        <div
          v-for="(b, i) in boutiques"
          :key="b.id"
          :style="getBoutiqueCard(i)"
          @mouseenter="hovBoutique = i"
          @mouseleave="hovBoutique = null"
        >
          <!-- Bande couleur -->
          <div :style="{ height:'3px', background:`linear-gradient(90deg,${CARD_COLORS[i % CARD_COLORS.length]},${CARD_COLORS[(i+2) % CARD_COLORS.length]})` }"></div>

          <div style="padding:20px 20px 0;display:flex;justify-content:space-between;align-items:flex-start">
            <div :style="boutiqueIconStyle(i)">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <div style="display:flex;gap:6px">
              <button :style="iconBtn('#3b82f6')" @click="openBoutiqueModal(b)" title="Modifier">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </button>
              <button :style="iconBtn('#ef4444')" @click="deleteBoutique(b.id)" title="Supprimer">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6"/></svg>
              </button>
            </div>
          </div>

          <div style="padding:12px 20px 20px">
            <h3 style="font-size:17px;font-weight:800;color:#0f172a;margin:0 0 4px;letter-spacing:-0.02em">{{ b.nom }}</h3>
            <p style="font-size:13px;color:#94a3b8;margin:0 0 14px;display:flex;align-items:center;gap:5px;font-weight:500">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
              {{ b.ville }}{{ b.quartier ? `, ${b.quartier}` : '' }}
              <span v-if="b.responsable" style="color:#cbd5e1">·</span>
              <span v-if="b.responsable" style="color:#475569;font-weight:600">{{ b.responsable }}</span>
            </p>

            <!-- Stocks en boutique -->
            <div style="background:#f8fafc;border-radius:10px;padding:12px;border:1px solid #f1f5f9;margin-bottom:12px">
              <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px">Produits en stock</div>
              <div v-if="!(b.stocks && b.stocks.length)" style="font-size:12px;color:#cbd5e1;font-weight:500">Aucun produit affecté</div>
              <div v-for="s in (b.stocks || []).slice(0, 5)" :key="s.produit_id" style="display:flex;justify-content:space-between;align-items:center;font-size:12px;margin-bottom:5px">
                <span style="font-weight:600;color:#475569">{{ getProduitNom(s.produit_id) }}</span>
                <span :style="{ fontWeight:'800', color: s.quantite <= 5 ? '#ef4444' : s.quantite <= 20 ? '#f59e0b' : '#10b981' }">{{ s.quantite }} {{ getProduitUnite(s.produit_id) }}</span>
              </div>
              <div v-if="(b.stocks || []).length > 5" style="font-size:11px;color:#94a3b8;margin-top:4px">+{{ b.stocks.length - 5 }} autres produits</div>
              <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px;padding-top:8px;border-top:1px solid #f1f5f9">
                <span style="font-size:11px;color:#94a3b8;font-weight:600">Total produits distincts</span>
                <span style="font-size:12px;font-weight:800;color:#0f172a">{{ (b.stocks || []).length }}</span>
              </div>
            </div>

            <!-- PDV autorisés -->
            <div>
              <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:7px">PDV autorisés à vendre</div>
              <div style="display:flex;flex-wrap:wrap;gap:5px">
                <span v-if="!(b.pdv_autorises && b.pdv_autorises.length)" style="font-size:12px;color:#cbd5e1">Aucun PDV autorisé</span>
                <span v-for="pid in (b.pdv_autorises || [])" :key="pid" :style="pdvPill">
                  🏪 {{ getPdvNom(pid) || `PDV #${pid}` }}
                </span>
              </div>
            </div>

            <!-- Statut -->
            <div style="margin-top:12px;display:flex;justify-content:space-between;align-items:center">
              <span :style="b.statut === 'active' ? boutiqueStatutActive : boutiqueStatutInactive">
                <span :style="{ width:'6px',height:'6px',borderRadius:'50%',background:b.statut==='active'?'#22c55e':'#ef4444',display:'inline-block',marginRight:'5px' }"></span>
                {{ b.statut === 'active' ? 'Active' : 'Inactive' }}
              </span>
              <button :style="transferBoutiqueBtn" @click="openTransferToBoutique(b)">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                Approvisionner
              </button>
            </div>
          </div>
        </div>

        <!-- Add boutique card -->
        <div :style="addBoutiqueCard" @click="openBoutiqueModal(null)">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5"><path d="M12 5v14M5 12h14"/></svg>
          <span style="font-size:13px;font-weight:700;color:#94a3b8;margin-top:8px">Nouvelle boutique</span>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════════
         MODAL CRÉER / MODIFIER PRODUIT
    ══════════════════════════════════════════ -->
    <Transition name="slide-modal">
      <div v-if="showProduitModal" :style="overlayStyle" @click.self="showProduitModal = false">
        <div :style="prodModalStyle" class="modal-panel">
          <div :style="modalHead">
            <div style="display:flex;align-items:center;gap:14px">
              <div :style="modalBadge('#0ea5e9')">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg>
              </div>
              <div>
                <h2 :style="modalTitle">{{ editingProduit ? 'Modifier le Produit' : 'Nouveau Produit' }}</h2>
                <p :style="modalSub">{{ editingProduit ? 'Mettez à jour les informations' : 'Enregistrez un nouveau produit dans le catalogue' }}</p>
              </div>
            </div>
            <button :style="closeBtnStyle" @click="showProduitModal = false">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
          </div>
          <div :style="modalBody">
            <!-- Emoji picker -->
            <div :style="formSec">
              <div :style="secTitle('#0ea5e9')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
                Emoji / Icône
              </div>
              <div style="display:flex;flex-wrap:wrap;gap:6px">
                <button v-for="e in EMOJIS" :key="e" :style="emojiBtn(e)" @click="produitForm.emoji = e">{{ e }}</button>
              </div>
            </div>

            <!-- Identité -->
            <div :style="formSec">
              <div :style="secTitle('#0ea5e9')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/></svg>
                Identité du produit
              </div>
              <div :style="formGrid">
                <div :style="fGroup" style="grid-column:1/-1">
                  <label :style="fLabel">Nom du produit <span style="color:#ef4444">*</span></label>
                  <input v-model="produitForm.nom" :style="fInput" placeholder="Ex: Riz Parfumé 5kg"/>
                </div>
                <div :style="fGroup">
                  <label :style="fLabel">Référence / Code</label>
                  <input v-model="produitForm.reference" :style="fInput" placeholder="REF-001"/>
                </div>
                <div :style="fGroup">
                  <label :style="fLabel">Catégorie <span style="color:#ef4444">*</span></label>
                  <select v-model="produitForm.categorie" :style="fInput">
                    <option value="">Sélectionner...</option>
                    <option v-for="c in CATEGORIES" :key="c" :value="c">{{ c }}</option>
                  </select>
                </div>
                <div :style="fGroup">
                  <label :style="fLabel">Unité de mesure</label>
                  <select v-model="produitForm.unite" :style="fInput">
                    <option v-for="u in UNITES" :key="u" :value="u">{{ u }}</option>
                  </select>
                </div>
                <div :style="fGroup" style="grid-column:1/-1">
                  <label :style="fLabel">Description</label>
                  <textarea v-model="produitForm.description" :style="{ ...fInput, height:'66px', resize:'vertical' }" placeholder="Description optionnelle..."></textarea>
                </div>
              </div>
            </div>

            <!-- Prix -->
            <div :style="formSec">
              <div :style="secTitle('#10b981')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                Prix
              </div>
              <div :style="formGrid">
                <div :style="fGroup">
                  <label :style="fLabel">Prix d'achat (FCFA) <span style="color:#ef4444">*</span></label>
                  <input v-model.number="produitForm.prix_achat" type="number" min="0" :style="fInput" placeholder="0"/>
                </div>
                <div :style="fGroup">
                  <label :style="fLabel">Prix de vente (FCFA) <span style="color:#ef4444">*</span></label>
                  <input v-model.number="produitForm.prix_vente" type="number" min="0" :style="fInput" placeholder="0"/>
                </div>
              </div>
              <div v-if="produitForm.prix_vente > 0 && produitForm.prix_achat > 0" :style="margeInfo">
                <span v-if="produitForm.prix_vente > produitForm.prix_achat" style="color:#10b981;font-weight:700">
                  ✓ Marge : {{ formatAmount(produitForm.prix_vente - produitForm.prix_achat) }} ({{ Math.round((produitForm.prix_vente - produitForm.prix_achat) / produitForm.prix_achat * 100) }}%)
                </span>
                <span v-else style="color:#ef4444;font-weight:800">⚠ PRIX DE VENTE INFÉRIEUR AU PRIX D'ACHAT — VENTE À PERTE</span>
              </div>
            </div>

            <!-- Stock initial -->
            <div :style="formSec">
              <div :style="secTitle('#f59e0b')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="15" rx="2"/><path d="M16 3H8a1 1 0 00-1 1v3h10V4a1 1 0 00-1-1z"/></svg>
                Stock
              </div>
              <div :style="formGrid">
                <div :style="fGroup">
                  <label :style="fLabel">Stock initial</label>
                  <input v-model.number="produitForm.stock" type="number" min="0" :style="fInput" placeholder="0"/>
                </div>
                <div :style="fGroup">
                  <label :style="fLabel">Seuil minimum d'alerte</label>
                  <input v-model.number="produitForm.stock_min" type="number" min="0" :style="fInput" placeholder="10"/>
                </div>
              </div>
            </div>

            <div :style="modalActions">
              <button :style="mCancel" @click="showProduitModal = false">Annuler</button>
              <button :style="mSubmit('#0ea5e9')" @click="saveProduit" :disabled="savingProduit">
                <div v-if="savingProduit" :style="spinSm"></div>
                <svg v-else width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                {{ savingProduit ? 'Enregistrement...' : (editingProduit ? 'Modifier le produit' : 'Créer le produit') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- ══════════════════════════════════════════
         MODAL AJUSTEMENT STOCK
    ══════════════════════════════════════════ -->
    <Transition name="slide-modal">
      <div v-if="showStockModal" :style="overlayStyle" @click.self="showStockModal = false">
        <div :style="stockModalStyle" class="modal-panel">
          <div :style="modalHead">
            <div style="display:flex;align-items:center;gap:14px">
              <div :style="modalBadge('#10b981')">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="15" rx="2"/><path d="M16 3H8a1 1 0 00-1 1v3h10V4a1 1 0 00-1-1z"/><path d="M12 12v4M10 14h4"/></svg>
              </div>
              <div>
                <h2 :style="modalTitle">Ajustement de Stock</h2>
                <p :style="modalSub">{{ stockProduit?.nom }} — Stock actuel : <strong style="color:#0f172a">{{ stockProduit?.stock }} {{ stockProduit?.unite || 'unités' }}</strong></p>
              </div>
            </div>
            <button :style="closeBtnStyle" @click="showStockModal = false">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
          </div>
          <div :style="modalBody">

            <!-- Historique récent -->
            <div v-if="stockProduit && getStockHistory(stockProduit.id).length" style="margin-bottom:20px">
              <div :style="secTitle('#10b981')" style="margin-bottom:10px">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v10l5 3"/><circle cx="12" cy="12" r="10"/></svg>
                Derniers mouvements
              </div>
              <div style="display:flex;flex-direction:column;gap:5px;max-height:130px;overflow-y:auto">
                <div v-for="h in getStockHistory(stockProduit.id)" :key="h.id" :style="histLine(h)">
                  <span :style="{ fontWeight:'800', color: h.type==='ajout' ? '#10b981' : '#ef4444' }">{{ h.type === 'ajout' ? '+' : '−' }}{{ h.quantite }}</span>
                  <span style="font-size:12px;color:#475569;font-weight:600;flex:1">{{ h.motif }}</span>
                  <span v-if="h.note" style="font-size:11px;color:#94a3b8;font-style:italic">{{ h.note }}</span>
                  <span style="font-size:11px;color:#cbd5e1;font-weight:500">{{ formatTimeAgo(h.date) }}</span>
                </div>
              </div>
            </div>

            <!-- Type de mouvement -->
            <div :style="fGroup" style="margin-bottom:16px">
              <label :style="fLabel">Type de mouvement</label>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                <button v-for="t in STOCK_TYPES" :key="t.key" :style="stockTypeBtn(t.key)" @click="stockForm.type = t.key">
                  <span style="font-size:20px">{{ t.icon }}</span>
                  <div>
                    <div style="font-size:13px;font-weight:700">{{ t.label }}</div>
                    <div style="font-size:11px;opacity:.65">{{ t.desc }}</div>
                  </div>
                </button>
              </div>
            </div>

            <div :style="formGrid" style="margin-bottom:14px">
              <div :style="fGroup">
                <label :style="fLabel">Quantité <span style="color:#ef4444">*</span></label>
                <input v-model.number="stockForm.quantite" type="number" min="1" :style="fInput" placeholder="0"/>
                <div v-if="stockForm.quantite && stockProduit" :style="previewInfo">
                  Nouveau stock :
                  <strong style="color:#8b5cf6">
                    {{ stockForm.type === 'ajout'
                      ? stockProduit.stock + stockForm.quantite
                      : Math.max(0, stockProduit.stock - stockForm.quantite) }}
                  </strong>
                  <span v-if="stockForm.type === 'retrait' && stockForm.quantite > stockProduit.stock" style="color:#ef4444;font-weight:800"> ⚠ Insuffisant</span>
                </div>
              </div>
              <div :style="fGroup">
                <label :style="fLabel">Motif <span style="color:#ef4444">*</span></label>
                <select v-model="stockForm.motif" :style="fInput">
                  <option value="">Sélectionner un motif...</option>
                  <optgroup v-if="stockForm.type === 'ajout'" label="Motifs d'ajout">
                    <option v-for="m in MOTIFS_AJOUT" :key="m" :value="m">{{ m }}</option>
                  </optgroup>
                  <optgroup v-if="stockForm.type === 'retrait'" label="Motifs de retrait">
                    <option v-for="m in MOTIFS_RETRAIT" :key="m" :value="m">{{ m }}</option>
                  </optgroup>
                </select>
              </div>
            </div>

            <div :style="fGroup">
              <label :style="fLabel">Note / Commentaire</label>
              <textarea v-model="stockForm.note" :style="{ ...fInput, height:'60px', resize:'vertical' }" placeholder="Informations complémentaires..."></textarea>
            </div>

            <div :style="modalActions">
              <button :style="mCancel" @click="showStockModal = false">Annuler</button>
              <button
                :style="mSubmit(stockForm.type === 'retrait' ? '#ef4444' : '#10b981')"
                @click="saveStock"
                :disabled="savingStock"
              >
                <div v-if="savingStock" :style="spinSm"></div>
                <svg v-else width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                {{ savingStock ? 'Enregistrement...' : (stockForm.type === 'ajout' ? 'Ajouter au stock' : 'Retirer du stock') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- ══════════════════════════════════════════
         MODAL TRANSFERT VERS BOUTIQUE / ENTREPÔT
    ══════════════════════════════════════════ -->
    <Transition name="slide-modal">
      <div v-if="showTransferModal" :style="overlayStyle" @click.self="showTransferModal = false">
        <div :style="transferModalStyle" class="modal-panel">
          <div :style="modalHead">
            <div style="display:flex;align-items:center;gap:14px">
              <div :style="modalBadge('#8b5cf6')">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
              </div>
              <div>
                <h2 :style="modalTitle">Transfert de Stock</h2>
                <p :style="modalSub">
                  {{ transferProduit?.nom }} ·
                  Stock principal : <strong style="color:#0f172a">{{ transferProduit?.stock }} {{ transferProduit?.unite || 'unités' }}</strong>
                </p>
              </div>
            </div>
            <button :style="closeBtnStyle" @click="showTransferModal = false">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
          </div>
          <div :style="modalBody">

            <!-- Destination type -->
            <div :style="fGroup" style="margin-bottom:18px">
              <label :style="fLabel">Destination du transfert</label>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                <button :style="destBtn('boutique')" @click="transferForm.dest_type = 'boutique'; transferForm.dest_id = ''">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                  <div>
                    <div style="font-size:13px;font-weight:700">Boutique de vente</div>
                    <div style="font-size:11px;opacity:.65">Pour la vente directe</div>
                  </div>
                </button>
                <button :style="destBtn('entrepot')" @click="transferForm.dest_type = 'entrepot'; transferForm.dest_id = ''">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                  <div>
                    <div style="font-size:13px;font-weight:700">Entrepôt de stockage</div>
                    <div style="font-size:11px;opacity:.65">Pour le stockage long terme</div>
                  </div>
                </button>
              </div>
            </div>

            <!-- Sélection boutique ou entrepôt -->
            <div v-if="transferForm.dest_type === 'boutique'" :style="fGroup">
              <label :style="fLabel">Boutique de destination <span style="color:#ef4444">*</span></label>
              <select v-model="transferForm.dest_id" :style="fInput">
                <option value="">Sélectionner une boutique...</option>
                <option v-for="b in boutiques" :key="b.id" :value="b.id">{{ b.nom }} — {{ b.ville }}</option>
              </select>
              <div v-if="transferForm.dest_id" :style="destInfoBox">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Stock actuel dans cette boutique :
                <strong style="color:#8b5cf6">{{ getBoutiqueStockQte(transferForm.dest_id, transferProduit?.id) }} {{ transferProduit?.unite || 'unités' }}</strong>
              </div>
            </div>

            <div v-if="transferForm.dest_type === 'entrepot'" :style="fGroup">
              <label :style="fLabel">Entrepôt de destination <span style="color:#ef4444">*</span></label>
              <select v-model="transferForm.dest_id" :style="fInput">
                <option value="">Sélectionner un entrepôt...</option>
                <option v-for="e in entrepots" :key="e.id" :value="e.id">{{ e.nom }} — {{ e.ville }}</option>
              </select>
            </div>

            <!-- Quantité -->
            <div :style="formGrid" style="margin-top:14px">
              <div :style="fGroup">
                <label :style="fLabel">Quantité à transférer <span style="color:#ef4444">*</span></label>
                <input v-model.number="transferForm.quantite" type="number" min="1" :max="transferProduit?.stock" :style="fInput" placeholder="0"/>
                <!-- Raccourcis rapides -->
                <div style="display:flex;gap:6px;margin-top:7px;flex-wrap:wrap">
                  <button :style="qteShortcut" @click="transferForm.quantite = Math.ceil((transferProduit?.stock || 0) * 0.25)">25%</button>
                  <button :style="qteShortcut" @click="transferForm.quantite = Math.ceil((transferProduit?.stock || 0) * 0.5)">50%</button>
                  <button :style="qteShortcut" @click="transferForm.quantite = Math.ceil((transferProduit?.stock || 0) * 0.75)">75%</button>
                  <button :style="qteShortcut" @click="transferForm.quantite = transferProduit?.stock || 0">Tout ({{ transferProduit?.stock }})</button>
                </div>
                <div v-if="transferForm.quantite" :style="previewInfo">
                  Reste dans le stock principal :
                  <strong style="color:#8b5cf6">{{ Math.max(0, (transferProduit?.stock || 0) - transferForm.quantite) }}</strong>
                  <span v-if="transferForm.quantite > (transferProduit?.stock || 0)" style="color:#ef4444;font-weight:800"> ⚠ Quantité insuffisante</span>
                </div>
              </div>
              <div :style="fGroup">
                <label :style="fLabel">Motif du transfert</label>
                <select v-model="transferForm.motif" :style="fInput">
                  <option value="Approvisionnement boutique">Approvisionnement boutique</option>
                  <option value="Transfert entrepôt">Transfert entrepôt</option>
                  <option value="Redistribution de stock">Redistribution de stock</option>
                  <option value="Urgence / Rupture imminente">Urgence / Rupture imminente</option>
                  <option value="Rotation de stock">Rotation de stock</option>
                  <option value="Autre">Autre motif</option>
                </select>
              </div>
            </div>

            <div :style="fGroup" style="margin-top:10px">
              <label :style="fLabel">Note de transfert</label>
              <textarea v-model="transferForm.note" :style="{ ...fInput, height:'60px', resize:'vertical' }" placeholder="Informations complémentaires sur ce transfert..."></textarea>
            </div>

            <div :style="modalActions">
              <button :style="mCancel" @click="showTransferModal = false">Annuler</button>
              <button :style="mSubmit('#8b5cf6')" @click="saveTransfer" :disabled="savingTransfer">
                <div v-if="savingTransfer" :style="spinSm"></div>
                <svg v-else width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                {{ savingTransfer ? 'Transfert en cours...' : 'Confirmer le transfert' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- ══════════════════════════════════════════
         MODAL CRÉER / MODIFIER BOUTIQUE
    ══════════════════════════════════════════ -->
    <Transition name="slide-modal">
      <div v-if="showBoutiqueModal" :style="overlayStyle" @click.self="showBoutiqueModal = false">
        <div :style="boutiqueModalStyle" class="modal-panel">
          <div :style="modalHead">
            <div style="display:flex;align-items:center;gap:14px">
              <div :style="modalBadge('#f59e0b')">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
              </div>
              <div>
                <h2 :style="modalTitle">{{ editingBoutique ? 'Modifier la Boutique' : 'Nouvelle Boutique' }}</h2>
                <p :style="modalSub">{{ editingBoutique ? 'Mettez à jour les informations' : 'Créez une boutique de vente et configurez les accès PDV' }}</p>
              </div>
            </div>
            <button :style="closeBtnStyle" @click="showBoutiqueModal = false">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
          </div>
          <div :style="modalBody">
            <div :style="formGrid">
              <div :style="fGroup">
                <label :style="fLabel">Nom de la boutique <span style="color:#ef4444">*</span></label>
                <input v-model="boutiqueForm.nom" :style="fInput" placeholder="Ex: Boutique Plateau"/>
              </div>
              <div :style="fGroup">
                <label :style="fLabel">Ville <span style="color:#ef4444">*</span></label>
                <input v-model="boutiqueForm.ville" :style="fInput" placeholder="Abidjan"/>
              </div>
              <div :style="fGroup">
                <label :style="fLabel">Quartier / Zone</label>
                <input v-model="boutiqueForm.quartier" :style="fInput" placeholder="Plateau, Cocody..."/>
              </div>
              <div :style="fGroup">
                <label :style="fLabel">Responsable</label>
                <input v-model="boutiqueForm.responsable" :style="fInput" placeholder="Nom du responsable"/>
              </div>
              <div :style="fGroup">
                <label :style="fLabel">Contact</label>
                <input v-model="boutiqueForm.contact" :style="fInput" placeholder="+225 07 00 00 00"/>
              </div>
              <div :style="fGroup">
                <label :style="fLabel">Statut</label>
                <select v-model="boutiqueForm.statut" :style="fInput">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>

            <!-- PDV autorisés -->
            <div :style="formSec">
              <div :style="secTitle('#f59e0b')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                Points de vente autorisés à vendre les produits de cette boutique
              </div>
              <div style="background:#f8fafc;border-radius:10px;padding:14px;border:1px solid #f1f5f9">
                <div v-if="!pdvList.length" style="font-size:13px;color:#94a3b8;font-style:italic">Aucun PDV disponible dans le système</div>
                <div v-for="pdv in pdvList" :key="pdv.id" style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
                  <input
                    type="checkbox"
                    :id="'pdv-chk-' + pdv.id"
                    :value="pdv.id"
                    v-model="boutiqueForm.pdv_autorises"
                    style="width:15px;height:15px;cursor:pointer;accent-color:#f59e0b"
                  />
                  <label :for="'pdv-chk-' + pdv.id" style="font-size:13px;font-weight:600;color:#0f172a;cursor:pointer;display:flex;align-items:center;gap:7px">
                    <span style="font-size:16px">🏪</span>
                    {{ pdv.nom }}
                    <span style="font-size:11px;color:#94a3b8;font-weight:500">{{ pdv.ville }}</span>
                  </label>
                </div>
                <div v-if="boutiqueForm.pdv_autorises.length" style="margin-top:10px;padding-top:10px;border-top:1px solid #e2e8f0;font-size:12px;color:#f59e0b;font-weight:700">
                  ✓ {{ boutiqueForm.pdv_autorises.length }} PDV autorisé(s)
                </div>
              </div>
            </div>

            <div :style="modalActions">
              <button :style="mCancel" @click="showBoutiqueModal = false">Annuler</button>
              <button :style="mSubmit('#f59e0b')" @click="saveBoutique" :disabled="savingBoutique">
                <div v-if="savingBoutique" :style="spinSm"></div>
                <svg v-else width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                {{ savingBoutique ? 'Enregistrement...' : (editingBoutique ? 'Modifier la boutique' : 'Créer la boutique') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Toast notification -->
    <Transition name="toast">
      <div v-if="showToast" :style="toastStyle">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        {{ toastMsg }}
      </div>
    </Transition>

  </SidebarLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import SidebarLayout from '../SidebarLayout.vue'

// ─── Constantes ─────────────────────────────────────────────────────────────
const CATEGORIES = ['Alimentation', 'Boissons', 'Hygiène', 'Électronique', 'Vêtements', 'Cosmétiques', 'Papeterie', 'Autre']
const UNITES = ['unité', 'kg', 'g', 'litre', 'ml', 'carton', 'sachet', 'boîte', 'pièce', 'mètre', 'sac']
const EMOJIS = ['📦', '🌾', '🫙', '🥤', '💧', '🍊', '🧼', '🥛', '🐟', '🍝', '🍫', '🦷', '🧻', '🍪', '🕯️', '👕', '🎧', '💊', '🧴', '📱', '💻', '🔧', '📝', '🧃', '🍕', '🍭', '🎒', '👟', '🛒', '🧊']
const STOCK_TYPES = [
  { key: 'ajout',   label: 'Ajout de stock',   icon: '➕', desc: 'Entrée / réception' },
  { key: 'retrait', label: 'Retrait de stock',  icon: '➖', desc: 'Sortie / correction' },
]
const MOTIFS_AJOUT   = ['Achat fournisseur', 'Retour client', 'Correction inventaire', 'Don / Cadeau', 'Transfert reçu', 'Production interne', 'Autre']
const MOTIFS_RETRAIT = ['Vente directe', 'Perte / Casse', 'Péremption / Expiration', 'Vol / Disparition', 'Transfert émis', 'Utilisation interne', 'Correction inventaire', 'Autre']
const CARD_COLORS = ['#0ea5e9', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#ec4899', '#06b6d4', '#84cc16']
const CAT_COLORS = {
  Alimentation: { bg: '#dcfce7', txt: '#166534' },
  Boissons:     { bg: '#dbeafe', txt: '#1e40af' },
  Hygiène:      { bg: '#fce7f3', txt: '#9d174d' },
  Électronique: { bg: '#ede9fe', txt: '#5b21b6' },
  Vêtements:    { bg: '#fef3c7', txt: '#92400e' },
  Cosmétiques:  { bg: '#fdf4ff', txt: '#7e22ce' },
  Papeterie:    { bg: '#ecfdf5', txt: '#14532d' },
  Autre:        { bg: '#f1f5f9', txt: '#475569' },
}

// ─── State ───────────────────────────────────────────────────────────────────
const activeView  = ref('grid')
const search      = ref('')
const filterCat   = ref('')
const filterStock = ref('')
const hovCard     = ref(null)
const hovRow      = ref(null)
const hovBoutique = ref(null)
const showToast   = ref(false)
const toastMsg    = ref('')

const showProduitModal  = ref(false)
const showStockModal    = ref(false)
const showTransferModal = ref(false)
const showBoutiqueModal = ref(false)

const editingProduit  = ref(null)
const editingBoutique = ref(null)
const stockProduit    = ref(null)
const transferProduit = ref(null)

const savingProduit   = ref(false)
const savingStock     = ref(false)
const savingTransfer  = ref(false)
const savingBoutique  = ref(false)

// ─── Données démo ────────────────────────────────────────────────────────────
const produits = ref([
  { id:1,  nom:'Riz Parfumé 5kg',      reference:'RIZ-001', prix_vente:5500,  prix_achat:4800,  stock:42,  stock_min:10, categorie:'Alimentation', emoji:'🌾', unite:'sac',      description:'Riz parfumé qualité supérieure' },
  { id:2,  nom:'Huile de Palme 1L',    reference:'HUI-001', prix_vente:1200,  prix_achat:950,   stock:30,  stock_min:15, categorie:'Alimentation', emoji:'🫙', unite:'litre' },
  { id:3,  nom:'Coca-Cola 1.5L',       reference:'BOI-001', prix_vente:800,   prix_achat:600,   stock:60,  stock_min:20, categorie:'Boissons',     emoji:'🥤', unite:'bouteille' },
  { id:4,  nom:'Eau Minérale 1.5L',    reference:'EAU-001', prix_vente:400,   prix_achat:280,   stock:120, stock_min:30, categorie:'Boissons',     emoji:'💧', unite:'bouteille' },
  { id:5,  nom:'Savon Lux',            reference:'HYG-001', prix_vente:350,   prix_achat:250,   stock:80,  stock_min:20, categorie:'Hygiène',      emoji:'🧼', unite:'unité' },
  { id:6,  nom:'Sardines Saupiquet',   reference:'ALI-002', prix_vente:900,   prix_achat:700,   stock:38,  stock_min:10, categorie:'Alimentation', emoji:'🐟', unite:'boîte' },
  { id:7,  nom:'Pâtes Spaghetti 500g', reference:'ALI-003', prix_vente:750,   prix_achat:580,   stock:7,   stock_min:10, categorie:'Alimentation', emoji:'🍝', unite:'paquet' },
  { id:8,  nom:'Tablette Chocolat',    reference:'ALI-004', prix_vente:1500,  prix_achat:1100,  stock:25,  stock_min:5,  categorie:'Alimentation', emoji:'🍫', unite:'unité' },
  { id:9,  nom:'Dentifrice Signal',    reference:'HYG-002', prix_vente:1200,  prix_achat:900,   stock:40,  stock_min:10, categorie:'Hygiène',      emoji:'🦷', unite:'unité' },
  { id:10, nom:'Papier Toilette ×6',   reference:'HYG-003', prix_vente:1800,  prix_achat:1400,  stock:22,  stock_min:8,  categorie:'Hygiène',      emoji:'🧻', unite:'paquet' },
  { id:11, nom:'Biscuits Oréo',        reference:'ALI-005', prix_vente:600,   prix_achat:420,   stock:90,  stock_min:25, categorie:'Alimentation', emoji:'🍪', unite:'paquet' },
  { id:12, nom:'Bougie ×10',           reference:'DIV-001', prix_vente:500,   prix_achat:350,   stock:4,   stock_min:10, categorie:'Hygiène',      emoji:'🕯️', unite:'boîte' },
  { id:13, nom:'T-shirt Blanc L',      reference:'VET-001', prix_vente:3500,  prix_achat:2200,  stock:12,  stock_min:5,  categorie:'Vêtements',    emoji:'👕', unite:'unité' },
  { id:14, nom:'Écouteurs Sans Fil',   reference:'ELE-001', prix_vente:15000, prix_achat:11000, stock:8,   stock_min:3,  categorie:'Électronique', emoji:'🎧', unite:'unité' },
  { id:15, nom:'Lait Condensé',        reference:'ALI-006', prix_vente:600,   prix_achat:450,   stock:0,   stock_min:15, categorie:'Alimentation', emoji:'🥛', unite:'boîte' },
  { id:16, nom:'Fanta Orange 1L',      reference:'BOI-002', prix_vente:700,   prix_achat:520,   stock:45,  stock_min:20, categorie:'Boissons',     emoji:'🍊', unite:'bouteille' },
])

const boutiques = ref([
  { id:1, nom:'Boutique Plateau',   ville:'Abidjan', quartier:'Plateau',   responsable:'Kouamé Jean',     contact:'+225 07 12 34 56', statut:'active',   pdv_autorises:[1, 2], stocks:[{produit_id:1, quantite:20},{produit_id:3, quantite:30},{produit_id:4, quantite:60},{produit_id:5, quantite:15}] },
  { id:2, nom:'Boutique Yopougon', ville:'Abidjan', quartier:'Yopougon', responsable:'Traoré Mariam',    contact:'+225 05 78 90 12', statut:'active',   pdv_autorises:[3],    stocks:[{produit_id:2, quantite:10},{produit_id:7, quantite:5},{produit_id:9, quantite:20}] },
])

const entrepots = ref([
  { id:1, nom:'Entrepôt Central', ville:'Abidjan', code:'ENT-001' },
  { id:2, nom:'Entrepôt Nord',    ville:'Bouaké',  code:'ENT-002' },
])

const pdvList = ref([
  { id:1, nom:'PDV Plateau',   ville:'Abidjan' },
  { id:2, nom:'PDV Cocody',    ville:'Abidjan' },
  { id:3, nom:'PDV Yopougon',  ville:'Abidjan' },
  { id:4, nom:'PDV Bouaké',    ville:'Bouaké'  },
])

const stockHistory = ref([
  { id:1, produit_id:1, type:'ajout',   quantite:50, motif:'Achat fournisseur',  date: new Date(Date.now()-86400000*3).toISOString() },
  { id:2, produit_id:1, type:'retrait', quantite:8,  motif:'Transfert émis',     date: new Date(Date.now()-86400000).toISOString() },
  { id:3, produit_id:3, type:'ajout',   quantite:100,motif:'Achat fournisseur',  date: new Date(Date.now()-86400000*4).toISOString() },
  { id:4, produit_id:3, type:'retrait', quantite:40, motif:'Approvisionnement boutique', date: new Date(Date.now()-86400000*2).toISOString() },
])

// ─── Forms ───────────────────────────────────────────────────────────────────
const produitForm = reactive({ nom:'', reference:'', categorie:'', unite:'unité', emoji:'📦', prix_achat:0, prix_vente:0, stock:0, stock_min:10, description:'' })
const stockForm   = reactive({ type:'ajout', quantite:0, motif:'', note:'' })
const transferForm= reactive({ dest_type:'boutique', dest_id:'', quantite:0, motif:'Approvisionnement boutique', note:'' })
const boutiqueForm= reactive({ nom:'', ville:'', quartier:'', responsable:'', contact:'', statut:'active', pdv_autorises:[] })

// ─── Computed ────────────────────────────────────────────────────────────────
const filteredProduits = computed(() => {
  let r = produits.value
  if (search.value.trim()) {
    const q = search.value.toLowerCase()
    r = r.filter(p => p.nom.toLowerCase().includes(q) || (p.reference||'').toLowerCase().includes(q) || p.categorie.toLowerCase().includes(q))
  }
  if (filterCat.value)   r = r.filter(p => p.categorie === filterCat.value)
  if (filterStock.value === 'ok')  r = r.filter(p => p.stock > 20)
  if (filterStock.value === 'low') r = r.filter(p => p.stock > 0 && p.stock <= 10)
  if (filterStock.value === 'out') r = r.filter(p => p.stock === 0)
  return r
})

const statsCards = computed(() => [
  {
    label: 'Total produits',
    value: produits.value.length,
    color: '#0ea5e9',
    icon: '<path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/>'
  },
  {
    label: 'Stock suffisant',
    value: produits.value.filter(p => p.stock > 20).length,
    color: '#10b981',
    icon: '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>'
  },
  {
    label: 'Stock faible (≤10)',
    value: produits.value.filter(p => p.stock > 0 && p.stock <= 10).length,
    color: '#f59e0b',
    icon: '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>'
  },
  {
    label: 'Rupture de stock',
    value: produits.value.filter(p => p.stock === 0).length,
    color: '#ef4444',
    icon: '<path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>',
    cardStyle: produits.value.filter(p => p.stock === 0).length > 0 ? { ...statCard, border:'1.5px solid #fca5a5' } : undefined
  },
  {
    label: 'Boutiques actives',
    value: boutiques.value.filter(b => b.statut === 'active').length,
    color: '#8b5cf6',
    icon: '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>'
  },
])

const VIEWS = computed(() => [
  { key: 'grid',     label: 'Grille',    icon: '⊞', count: undefined },
  { key: 'list',     label: 'Liste',     icon: '≡', count: undefined },
  { key: 'boutiques',label: 'Boutiques', icon: '🏪', count: boutiques.value.length },
])

// ─── Helpers ─────────────────────────────────────────────────────────────────
const formatAmount   = n => new Intl.NumberFormat('fr-FR').format(n) + ' FCFA'
const formatTimeAgo  = d => {
  const diff = (Date.now() - new Date(d)) / 1000
  if (diff < 60)     return "à l'instant"
  if (diff < 3600)   return `il y a ${Math.floor(diff / 60)}min`
  if (diff < 86400)  return `il y a ${Math.floor(diff / 3600)}h`
  return `il y a ${Math.floor(diff / 86400)}j`
}
const getStockStatus = p => p.stock === 0 ? 'Rupture' : (p.stock_min && p.stock <= p.stock_min) ? 'Stock faible' : 'En stock'
const getProduitNom  = id => { const p = produits.value.find(x => x.id === id); return p ? p.nom : '?' }
const getProduitUnite= id => { const p = produits.value.find(x => x.id === id); return p ? (p.unite || '') : '' }
const getPdvNom      = id => { const p = pdvList.value.find(x => x.id === id); return p ? p.nom : null }
const getBoutiqueStockQte = (boutique_id, produit_id) => {
  const b = boutiques.value.find(x => x.id === boutique_id)
  if (!b || !b.stocks) return 0
  const s = b.stocks.find(x => x.produit_id === produit_id)
  return s ? s.quantite : 0
}
const getStockHistory = produit_id => stockHistory.value.filter(h => h.produit_id === produit_id).slice(0, 6).reverse()

const toast = (msg) => { toastMsg.value = msg; showToast.value = true; setTimeout(() => showToast.value = false, 3200) }

// ─── CRUD Produits ────────────────────────────────────────────────────────────
const openProduitModal = (p) => {
  editingProduit.value = p
  if (p) Object.assign(produitForm, { ...p })
  else   Object.assign(produitForm, { nom:'', reference:'', categorie:'', unite:'unité', emoji:'📦', prix_achat:0, prix_vente:0, stock:0, stock_min:10, description:'' })
  showProduitModal.value = true
}
const saveProduit = async () => {
  if (!produitForm.nom || !produitForm.categorie) { alert('Nom et catégorie sont requis'); return }
  savingProduit.value = true
  await new Promise(r => setTimeout(r, 650))
  if (editingProduit.value) {
    const idx = produits.value.findIndex(p => p.id === editingProduit.value.id)
    if (idx >= 0) produits.value[idx] = { ...produits.value[idx], ...produitForm }
    toast('Produit modifié avec succès ✓')
  } else {
    produits.value.unshift({ id: Date.now(), ...produitForm })
    toast('Produit créé avec succès ✓')
  }
  savingProduit.value = false
  showProduitModal.value = false
}
const deleteProduit = (id) => {
  if (!confirm('Supprimer ce produit définitivement ?')) return
  produits.value = produits.value.filter(p => p.id !== id)
  toast('Produit supprimé')
}

// ─── Ajustement Stock ─────────────────────────────────────────────────────────
const openStockModal = (p) => {
  stockProduit.value = p
  Object.assign(stockForm, { type: 'ajout', quantite: 0, motif: '', note: '' })
  showStockModal.value = true
}
const saveStock = async () => {
  if (!stockForm.quantite || !stockForm.motif) { alert('Quantité et motif sont requis'); return }
  if (stockForm.type === 'retrait' && stockForm.quantite > stockProduit.value.stock) { alert('Quantité insuffisante dans le stock'); return }
  savingStock.value = true
  await new Promise(r => setTimeout(r, 600))
  const p = produits.value.find(x => x.id === stockProduit.value.id)
  if (p) {
    p.stock = stockForm.type === 'ajout'
      ? p.stock + stockForm.quantite
      : Math.max(0, p.stock - stockForm.quantite)
    stockProduit.value.stock = p.stock
  }
  stockHistory.value.unshift({
    id: Date.now(), produit_id: stockProduit.value.id,
    type: stockForm.type, quantite: stockForm.quantite,
    motif: stockForm.motif, note: stockForm.note,
    date: new Date().toISOString()
  })
  savingStock.value = false
  showStockModal.value = false
  toast(stockForm.type === 'ajout' ? `+${stockForm.quantite} ajouté au stock ✓` : `-${stockForm.quantite} retiré du stock ✓`)
}

// ─── Transfert ────────────────────────────────────────────────────────────────
const openTransferModal = (p) => {
  transferProduit.value = p
  Object.assign(transferForm, { dest_type: 'boutique', dest_id: '', quantite: 0, motif: 'Approvisionnement boutique', note: '' })
  showTransferModal.value = true
}
const openTransferToBoutique = (b) => {
  // Ouvre la modal de transfert pré-sélectionné sur une boutique
  // On laisse l'utilisateur choisir le produit via le modal standard
  // Pour simplifier ici : juste ouvre le modal transfer avec la boutique préselectionnée
  if (produits.value.length === 0) { alert('Aucun produit disponible'); return }
  transferProduit.value = produits.value[0] // placeholder — dans un vrai système on montrerait un sélecteur
  Object.assign(transferForm, { dest_type: 'boutique', dest_id: b.id, quantite: 0, motif: 'Approvisionnement boutique', note: '' })
  showTransferModal.value = true
}
const saveTransfer = async () => {
  if (!transferForm.dest_id || !transferForm.quantite) { alert('Destination et quantité sont requises'); return }
  if (transferForm.quantite > (transferProduit.value?.stock || 0)) { alert('Quantité insuffisante dans le stock principal'); return }
  savingTransfer.value = true
  await new Promise(r => setTimeout(r, 700))

  // Décrémenter le stock principal
  const p = produits.value.find(x => x.id === transferProduit.value.id)
  if (p) { p.stock -= transferForm.quantite; transferProduit.value.stock = p.stock }

  // Si boutique : mettre à jour le stock de la boutique
  if (transferForm.dest_type === 'boutique') {
    const b = boutiques.value.find(x => x.id === transferForm.dest_id)
    if (b) {
      if (!b.stocks) b.stocks = []
      const s = b.stocks.find(x => x.produit_id === transferProduit.value.id)
      if (s) s.quantite += transferForm.quantite
      else b.stocks.push({ produit_id: transferProduit.value.id, quantite: transferForm.quantite })
    }
  }

  // Ajouter dans l'historique
  stockHistory.value.unshift({
    id: Date.now(), produit_id: transferProduit.value.id,
    type: 'retrait', quantite: transferForm.quantite,
    motif: transferForm.motif,
    note: `→ ${transferForm.dest_type === 'boutique' ? boutiques.value.find(b => b.id === transferForm.dest_id)?.nom : entrepots.value.find(e => e.id === transferForm.dest_id)?.nom}`,
    date: new Date().toISOString()
  })

  savingTransfer.value = false
  showTransferModal.value = false
  const dest = transferForm.dest_type === 'boutique'
    ? boutiques.value.find(b => b.id === transferForm.dest_id)?.nom
    : entrepots.value.find(e => e.id === transferForm.dest_id)?.nom
  toast(`${transferForm.quantite} unité(s) transférée(s) vers ${dest} ✓`)
}

// ─── CRUD Boutiques ───────────────────────────────────────────────────────────
const openBoutiqueModal = (b) => {
  editingBoutique.value = b
  if (b) Object.assign(boutiqueForm, { ...b })
  else   Object.assign(boutiqueForm, { nom:'', ville:'', quartier:'', responsable:'', contact:'', statut:'active', pdv_autorises:[] })
  showBoutiqueModal.value = true
}
const saveBoutique = async () => {
  if (!boutiqueForm.nom || !boutiqueForm.ville) { alert('Nom et ville sont requis'); return }
  savingBoutique.value = true
  await new Promise(r => setTimeout(r, 600))
  if (editingBoutique.value) {
    const idx = boutiques.value.findIndex(b => b.id === editingBoutique.value.id)
    if (idx >= 0) boutiques.value[idx] = { ...boutiques.value[idx], ...boutiqueForm }
    toast('Boutique modifiée ✓')
  } else {
    boutiques.value.push({ id: Date.now(), ...boutiqueForm, stocks: [] })
    toast('Boutique créée ✓')
  }
  savingBoutique.value = false
  showBoutiqueModal.value = false
}
const deleteBoutique = (id) => {
  if (!confirm('Supprimer cette boutique ?')) return
  boutiques.value = boutiques.value.filter(b => b.id !== id)
  toast('Boutique supprimée')
}

// ─── STYLES ──────────────────────────────────────────────────────────────────
const headerStyle     = { display:'flex', justifyContent:'space-between', alignItems:'flex-start', marginBottom:'24px', flexWrap:'wrap', gap:'16px' }
const breadcrumbStyle = { display:'flex', alignItems:'center', gap:'6px', marginBottom:'6px' }
const bcItem          = { fontSize:'13px', color:'#94a3b8', fontWeight:'500' }
const bcActive        = { fontSize:'13px', color:'#1e293b', fontWeight:'600' }
const titleStyle      = { fontSize:'30px', fontWeight:'800', color:'#0f172a', margin:'0 0 4px', letterSpacing:'-0.03em' }
const subtitleStyle   = { fontSize:'14px', color:'#64748b', margin:'0 0 12px', display:'flex', alignItems:'center', gap:'6px', fontWeight:'500' }
const tabsRow         = { display:'flex', gap:'6px', marginTop:'10px', flexWrap:'wrap' }

const getViewTab = key => ({
  display:'flex', alignItems:'center', gap:'7px',
  padding:'8px 16px', borderRadius:'10px', border:'none',
  background: activeView.value === key ? 'linear-gradient(135deg,#0ea5e9,#0284c7)' : '#f1f5f9',
  color: activeView.value === key ? 'white' : '#64748b',
  fontSize:'13px', fontWeight:'700', cursor:'pointer', transition:'all 0.2s'
})
const tabCount = key => ({
  display:'inline-flex', alignItems:'center', justifyContent:'center',
  width:'20px', height:'20px', borderRadius:'50%',
  background: activeView.value === key ? 'rgba(255,255,255,0.25)' : '#e2e8f0',
  fontSize:'11px', fontWeight:'800', color: activeView.value === key ? 'white' : '#64748b'
})

const primaryBtnStyle = { display:'flex', alignItems:'center', gap:'8px', padding:'12px 20px', background:'linear-gradient(135deg,#0ea5e9,#0284c7)', border:'none', borderRadius:'11px', fontSize:'14px', fontWeight:'700', color:'white', cursor:'pointer', boxShadow:'0 4px 12px rgba(14,165,233,0.25)' }
const secBtnStyle     = { display:'flex', alignItems:'center', gap:'8px', padding:'11px 18px', background:'white', border:'1px solid #e2e8f0', borderRadius:'11px', fontSize:'13px', fontWeight:'700', color:'#475569', cursor:'pointer' }

const statsRow = { display:'grid', gridTemplateColumns:'repeat(auto-fit,minmax(160px,1fr))', gap:'12px', marginBottom:'20px' }
const statCard = { background:'white', borderRadius:'14px', padding:'18px', display:'flex', alignItems:'center', gap:'13px', border:'1px solid #f1f5f9', boxShadow:'0 1px 4px rgba(0,0,0,0.05)' }
const statIcon = c => ({ width:'42px', height:'42px', borderRadius:'11px', background:`${c}15`, display:'flex', alignItems:'center', justifyContent:'center', color:c, flexShrink:0 })
const statVal  = { fontSize:'20px', fontWeight:'800', letterSpacing:'-0.02em' }
const statLbl  = { fontSize:'12px', color:'#64748b', fontWeight:'500', marginTop:'2px' }

const filterBarStyle  = { display:'flex', gap:'10px', marginBottom:'20px', flexWrap:'wrap', alignItems:'center' }
const searchInputStyle= { width:'100%', padding:'11px 14px 11px 44px', border:'1px solid #e2e8f0', borderRadius:'10px', fontSize:'13px', fontWeight:'500', color:'#1e293b', outline:'none', boxSizing:'border-box' }
const filterSelectStyle={ padding:'10px 14px', border:'1px solid #e2e8f0', borderRadius:'10px', fontSize:'13px', fontWeight:'500', color:'#475569', outline:'none', background:'white' }
const clearBtnStyle   = { display:'flex', alignItems:'center', gap:'5px', padding:'10px 14px', background:'#fef2f2', border:'1px solid #fecaca', borderRadius:'10px', fontSize:'12px', fontWeight:'700', color:'#ef4444', cursor:'pointer' }

const prodGridStyle = { display:'grid', gridTemplateColumns:'repeat(auto-fill,minmax(220px,1fr))', gap:'14px' }
const emptyStateStyle = { textAlign:'center', padding:'60px 20px', background:'white', borderRadius:'16px', border:'1px solid #f1f5f9' }

const getProdCard = i => ({
  background:'white', borderRadius:'16px', overflow:'hidden', position:'relative',
  border:`1px solid ${hovCard.value === i ? '#e2e8f0' : '#f1f5f9'}`,
  boxShadow: hovCard.value === i ? '0 8px 24px rgba(0,0,0,0.1)' : '0 1px 4px rgba(0,0,0,0.04)',
  transition:'all 0.22s', transform: hovCard.value === i ? 'translateY(-3px)' : 'none'
})
const prodAccent  = p => ({ height:'3px', background: p.stock === 0 ? '#ef4444' : (p.stock_min && p.stock <= p.stock_min) ? '#f59e0b' : 'linear-gradient(90deg,#0ea5e9,#6366f1)' })
const prodCardActions = { display:'flex', justifyContent:'flex-end', gap:'4px', padding:'10px 10px 0' }
const pca = type => ({
  width:'26px', height:'26px', borderRadius:'7px', cursor:'pointer',
  display:'flex', alignItems:'center', justifyContent:'center',
  border:`1px solid ${type==='edit'?'#bfdbfe':type==='stock'?'#bbf7d0':type==='transfer'?'#ddd6fe':'#fecaca'}`,
  background: type==='edit'?'#eff6ff':type==='stock'?'#f0fdf4':type==='transfer'?'#f5f3ff':'#fef2f2',
  color: type==='edit'?'#3b82f6':type==='stock'?'#10b981':type==='transfer'?'#8b5cf6':'#ef4444'
})
const stockBadge = p => ({
  display:'inline-block', padding:'3px 9px', borderRadius:'20px', fontSize:'11px', fontWeight:'800',
  background: p.stock===0 ? '#fef2f2' : (p.stock_min&&p.stock<=p.stock_min) ? '#fffbeb' : '#f0fdf4',
  color: p.stock===0 ? '#ef4444' : (p.stock_min&&p.stock<=p.stock_min) ? '#f59e0b' : '#10b981'
})
const stockTrack = { height:'5px', background:'#f1f5f9', borderRadius:'10px', overflow:'hidden' }
const stockFill  = p => {
  const pct = p.stock_min ? Math.min(100, p.stock / (p.stock_min * 3) * 100) : Math.min(100, p.stock / 100 * 100)
  return { height:'100%', width:pct+'%', borderRadius:'10px', transition:'width 0.4s',
    background: p.stock===0 ? '#ef4444' : (p.stock_min&&p.stock<=p.stock_min) ? '#f59e0b' : '#10b981' }
}

// Liste
const tableSection = { background:'white', borderRadius:'16px', border:'1px solid #f1f5f9', overflow:'hidden', boxShadow:'0 1px 4px rgba(0,0,0,0.04)' }
const tableWrap    = { overflowX:'auto' }
const tableStyle   = { width:'100%', borderCollapse:'collapse' }
const theadTr      = { background:'#f8fafc' }
const th           = { padding:'12px 14px', textAlign:'left', fontSize:'11px', fontWeight:'800', color:'#64748b', textTransform:'uppercase', letterSpacing:'.06em', borderBottom:'1px solid #f1f5f9', whiteSpace:'nowrap' }
const getTr        = i => ({ background: hovRow.value===i ? '#f8fafc' : 'white', borderBottom:'1px solid #f8fafc', transition:'background 0.15s' })
const td           = { padding:'12px 14px', fontSize:'13px', verticalAlign:'middle' }
const emptyTd      = { textAlign:'center', padding:'48px', color:'#94a3b8', fontSize:'14px' }
const catPill      = c => { const x = CAT_COLORS[c]||{bg:'#f1f5f9',txt:'#475569'}; return { padding:'3px 10px', borderRadius:'20px', fontSize:'11px', fontWeight:'700', background:x.bg, color:x.txt } }
const stockBadgeInline = p => ({ padding:'4px 10px', borderRadius:'20px', fontSize:'12px', fontWeight:'800', background: p.stock===0?'#fef2f2':(p.stock_min&&p.stock<=p.stock_min)?'#fffbeb':'#f0fdf4', color: p.stock===0?'#ef4444':(p.stock_min&&p.stock<=p.stock_min)?'#f59e0b':'#10b981' })
const statusPill   = p => { const s=getStockStatus(p); return { padding:'3px 10px', borderRadius:'20px', fontSize:'11px', fontWeight:'700', background: s==='Rupture'?'#fef2f2':s==='Stock faible'?'#fffbeb':'#f0fdf4', color: s==='Rupture'?'#ef4444':s==='Stock faible'?'#f59e0b':'#10b981' } }
const aBtn         = c => ({ width:'28px', height:'28px', border:`1px solid ${c}30`, background:`${c}15`, color:c, borderRadius:'7px', cursor:'pointer', display:'flex', alignItems:'center', justifyContent:'center', fontSize:'14px' })

// Boutiques
const boutiqueGridStyle= { display:'grid', gridTemplateColumns:'repeat(auto-fill,minmax(320px,1fr))', gap:'16px' }
const getBoutiqueCard  = i => ({
  background:'white', borderRadius:'18px', overflow:'hidden',
  border:`1px solid ${hovBoutique.value===i?'#e2e8f0':'#f1f5f9'}`,
  boxShadow: hovBoutique.value===i ? '0 8px 24px rgba(0,0,0,0.09)' : '0 1px 4px rgba(0,0,0,0.05)',
  transition:'all 0.22s', transform: hovBoutique.value===i ? 'translateY(-2px)' : 'none'
})
const boutiqueIconStyle= i => ({ width:'46px', height:'46px', background:`${CARD_COLORS[i%CARD_COLORS.length]}18`, borderRadius:'13px', display:'flex', alignItems:'center', justifyContent:'center', color:CARD_COLORS[i%CARD_COLORS.length] })
const iconBtn          = c => ({ width:'30px', height:'30px', border:`1px solid ${c}25`, background:`${c}12`, color:c, borderRadius:'8px', cursor:'pointer', display:'flex', alignItems:'center', justifyContent:'center' })
const pdvPill          = { padding:'4px 10px', background:'#f0f9ff', border:'1px solid #bae6fd', borderRadius:'20px', fontSize:'11px', fontWeight:'700', color:'#0284c7' }
const boutiqueStatutActive  = { display:'inline-flex', alignItems:'center', padding:'4px 12px', background:'#dcfce7', borderRadius:'20px', fontSize:'12px', fontWeight:'700', color:'#166534' }
const boutiqueStatutInactive= { display:'inline-flex', alignItems:'center', padding:'4px 12px', background:'#fee2e2', borderRadius:'20px', fontSize:'12px', fontWeight:'700', color:'#991b1b' }
const transferBoutiqueBtn   = { display:'flex', alignItems:'center', gap:'5px', padding:'6px 12px', background:'linear-gradient(135deg,#8b5cf6,#6d28d9)', border:'none', borderRadius:'8px', fontSize:'11px', fontWeight:'700', color:'white', cursor:'pointer' }
const addBoutiqueCard       = { border:'2px dashed #e2e8f0', borderRadius:'18px', display:'flex', flexDirection:'column', alignItems:'center', justifyContent:'center', minHeight:'220px', cursor:'pointer', transition:'all 0.2s' }

// Modals communs
const overlayStyle     = { position:'fixed', inset:0, background:'rgba(15,23,42,0.72)', backdropFilter:'blur(8px)', display:'flex', alignItems:'center', justifyContent:'center', zIndex:1000, padding:'20px', overflowY:'auto' }
const prodModalStyle   = { background:'white', borderRadius:'22px', width:'100%', maxWidth:'680px', maxHeight:'92vh', overflow:'auto', boxShadow:'0 25px 50px rgba(0,0,0,0.25)' }
const stockModalStyle  = { background:'white', borderRadius:'22px', width:'100%', maxWidth:'560px', maxHeight:'90vh', overflow:'auto', boxShadow:'0 25px 50px rgba(0,0,0,0.25)' }
const transferModalStyle={ background:'white', borderRadius:'22px', width:'100%', maxWidth:'620px', maxHeight:'90vh', overflow:'auto', boxShadow:'0 25px 50px rgba(0,0,0,0.25)' }
const boutiqueModalStyle={ background:'white', borderRadius:'22px', width:'100%', maxWidth:'660px', maxHeight:'90vh', overflow:'auto', boxShadow:'0 25px 50px rgba(0,0,0,0.25)' }
const modalHead        = { display:'flex', justifyContent:'space-between', alignItems:'center', padding:'22px 26px 18px', borderBottom:'1px solid #f1f5f9' }
const modalBadge       = c => ({ width:'40px', height:'40px', background:`linear-gradient(135deg,${c},${c}bb)`, borderRadius:'11px', display:'flex', alignItems:'center', justifyContent:'center', color:'white', flexShrink:0 })
const modalTitle       = { fontSize:'19px', fontWeight:'800', color:'#0f172a', margin:'0 0 3px', letterSpacing:'-0.02em' }
const modalSub         = { fontSize:'12px', color:'#64748b', margin:0, fontWeight:'500' }
const closeBtnStyle    = { background:'none', border:'none', cursor:'pointer', color:'#94a3b8', padding:'8px', borderRadius:'8px' }
const modalBody        = { padding:'20px 26px 26px' }
const modalActions     = { display:'flex', gap:'10px', marginTop:'22px', paddingTop:'18px', borderTop:'1px solid #f1f5f9' }
const mCancel          = { flex:1, padding:'12px', background:'#f8fafc', border:'1px solid #e2e8f0', borderRadius:'10px', fontSize:'14px', fontWeight:'700', color:'#64748b', cursor:'pointer' }
const mSubmit          = c => ({ flex:2, padding:'12px', background:`linear-gradient(135deg,${c},${c}cc)`, border:'none', borderRadius:'10px', fontSize:'14px', fontWeight:'700', color:'white', cursor:'pointer', display:'flex', alignItems:'center', justifyContent:'center', gap:'8px', boxShadow:`0 4px 14px ${c}30` })
const spinSm           = { width:'15px', height:'15px', border:'2px solid rgba(255,255,255,0.3)', borderTop:'2px solid white', borderRadius:'50%', animation:'p-spin 0.8s linear infinite' }

// Form
const formSec  = { marginBottom:'20px' }
const secTitle = c => ({ display:'flex', alignItems:'center', gap:'7px', fontSize:'13px', fontWeight:'800', color:'#0f172a', paddingBottom:'10px', borderBottom:`2px solid ${c}22`, marginBottom:'14px' })
const formGrid = { display:'grid', gridTemplateColumns:'1fr 1fr', gap:'14px' }
const fGroup   = { marginBottom:'14px' }
const fLabel   = { display:'block', fontSize:'11px', fontWeight:'800', color:'#475569', textTransform:'uppercase', letterSpacing:'.06em', marginBottom:'7px' }
const fInput   = { width:'100%', padding:'10px 13px', border:'1px solid #e2e8f0', borderRadius:'9px', fontSize:'13px', fontWeight:'500', color:'#1e293b', boxSizing:'border-box', outline:'none', background:'white' }
const emojiBtn = e => ({ fontSize:'21px', padding:'6px', borderRadius:'8px', border:`2px solid ${produitForm.emoji===e?'#0ea5e9':'#f1f5f9'}`, background:produitForm.emoji===e?'#e0f2fe':'#f8fafc', cursor:'pointer', transition:'all 0.1s' })
const margeInfo= { fontSize:'12px', fontWeight:'600', padding:'8px 12px', background:'#f8fafc', borderRadius:'8px', marginTop:'6px' }

const stockTypeBtn = k => ({
  display:'flex', alignItems:'center', gap:'10px', padding:'12px 14px', borderRadius:'10px', cursor:'pointer',
  border:`1.5px solid ${stockForm.type===k ? (k==='ajout'?'#10b981':'#ef4444') : '#e2e8f0'}`,
  background: stockForm.type===k ? (k==='ajout'?'#f0fdf4':'#fef2f2') : 'white', transition:'all 0.15s'
})
const previewInfo = { fontSize:'12px', fontWeight:'700', color:'#8b5cf6', marginTop:'7px', padding:'6px 10px', background:'#f5f3ff', borderRadius:'7px' }
const histLine    = h => ({ display:'flex', alignItems:'center', gap:'10px', padding:'6px 10px', background:h.type==='ajout'?'#f0fdf4':'#fef2f2', borderRadius:'7px', fontSize:'12px' })

const destBtn = k => ({
  display:'flex', alignItems:'center', gap:'10px', padding:'14px', borderRadius:'11px', cursor:'pointer',
  border:`1.5px solid ${transferForm.dest_type===k?'#8b5cf6':'#e2e8f0'}`,
  background:transferForm.dest_type===k?'#f5f3ff':'white',
  color:transferForm.dest_type===k?'#7c3aed':'#475569', transition:'all 0.15s'
})
const destInfoBox   = { fontSize:'12px', fontWeight:'600', color:'#8b5cf6', marginTop:'7px', padding:'7px 11px', background:'#f5f3ff', borderRadius:'8px', display:'flex', alignItems:'center', gap:'6px' }
const qteShortcut   = { padding:'5px 11px', background:'#f1f5f9', border:'1px solid #e2e8f0', borderRadius:'6px', fontSize:'12px', fontWeight:'700', color:'#475569', cursor:'pointer' }

// Toast
const toastStyle = { position:'fixed', bottom:'28px', left:'50%', transform:'translateX(-50%)', background:'#10b981', color:'white', padding:'13px 22px', borderRadius:'12px', display:'flex', alignItems:'center', gap:'10px', fontSize:'14px', fontWeight:'700', boxShadow:'0 8px 24px rgba(16,185,129,0.35)', zIndex:9999, whiteSpace:'nowrap' }
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,700;0,9..40,800&display=swap');
* { font-family: 'DM Sans', -apple-system, sans-serif; }

@keyframes p-fadein { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:translateY(0); } }
@keyframes p-spin   { to { transform:rotate(360deg); } }

.p-fade { animation: p-fadein 0.45s cubic-bezier(0.16,1,0.3,1) backwards; }

/* Transitions modales */
.slide-modal-enter-active, .slide-modal-leave-active { transition: all 0.3s cubic-bezier(0.16,1,0.3,1); }
.slide-modal-enter-from, .slide-modal-leave-to       { opacity: 0; }
.slide-modal-enter-from .modal-panel                 { transform: scale(0.96) translateY(14px); }
.modal-panel                                         { transition: all 0.3s cubic-bezier(0.16,1,0.3,1); }

/* Toast */
.toast-enter-active, .toast-leave-active { transition: all 0.35s cubic-bezier(0.16,1,0.3,1); }
.toast-enter-from, .toast-leave-to       { opacity:0; transform: translateX(-50%) translateY(12px); }

/* Card hover shine effect */
.prod-item::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(255,255,255,0) 40%, rgba(255,255,255,0.3) 50%, rgba(255,255,255,0) 60%);
  transform: translateX(-100%) skewX(-15deg);
  transition: transform 0.7s;
  pointer-events: none;
  border-radius: 16px;
}
.prod-item:hover::after { transform: translateX(150%) skewX(-15deg); }

/* Scrollbar fine */
::-webkit-scrollbar       { width: 5px; height: 5px; }
::-webkit-scrollbar-track { background: #f1f5f9; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
</style>