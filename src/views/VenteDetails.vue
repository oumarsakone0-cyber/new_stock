<template>
  <SidebarLayout currentPage="points-de-vente">

    <div :style="pageHeaderStyle" class="detail-fade">
      <div :style="backRowStyle">
        <button :style="backBtnStyle" @click="$router.back()">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
          Points de vente
        </button>
        <div :style="headerActionsStyle">
          <button :style="modalVenteBtnStyle" @click="openVenteModal" @mouseenter="hovMV=true" @mouseleave="hovMV=false">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
            Nouvelle Vente
          </button>
          <button :style="caisseBtnStyle" @click="openCaisse" @mouseenter="hovC=true" @mouseleave="hovC=false">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="2" y="7" width="20" height="15" rx="2"/><path d="M16 3H8a1 1 0 00-1 1v3h10V4a1 1 0 00-1-1z"/><path d="M12 12v4M10 14h4"/>
            </svg>
            Ouvrir la Caisse
          </button>
        </div>
      </div>

      <div :style="heroStyle">
        <div :style="heroIconStyle">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
          </svg>
        </div>
        <div :style="heroInfoStyle">
          <div :style="heroTagsStyle">
            <span :style="typeBadgeStyle">{{ pdv.type_pdv }}</span>
            <span :style="getStatusBadge(pdv.statut==='actif')">
              <span :style="statusDot(pdv.statut==='actif')"></span>{{ pdv.statut==='actif'?'Actif':'Inactif' }}
            </span>
          </div>
          <h1 :style="heroTitleStyle">{{ pdv.nom }}</h1>
          <p :style="heroLocStyle">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
            {{ pdv.ville }}{{ pdv.quartier?`, ${pdv.quartier}`:'' }}
          </p>
        </div>
        <div :style="heroRespStyle">
          <div :style="heroAvatarStyle">{{ getInitials(pdv.responsable) }}</div>
          <div>
            <div :style="heroRespName">{{ pdv.responsable }}</div>
            <div :style="heroRespRole">Responsable vente</div>
            <a v-if="pdv.contact" :href="'tel:'+pdv.contact" :style="heroContact">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6A19.79 19.79 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
              {{ pdv.contact }}
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats -->
    <div :style="statsGrid" class="detail-fade" style="animation-delay:.1s">
      <div v-for="s in statsData" :key="s.label" :style="s.style||getStatTile()">
        <div :style="statTop"><span :style="statLabel">{{ s.label }}</span><div :style="statIcon(s.color)" v-html="s.icon"></div></div>
        <div :style="statVal(s.color)">{{ s.value }}</div>
        <div :style="statSub" v-html="s.sub"></div>
      </div>
    </div>

    <!-- Historique -->
    <div :style="sectionStyle" class="detail-fade" style="animation-delay:.2s">
      <div :style="secHeader">
        <div><h2 :style="secTitle">Historique des Ventes</h2><p :style="secSub">{{ filteredVentes.length }} transaction(s)</p></div>
        <div :style="histoActions">
          <div :style="periodeTabs">
            <button v-for="p in PERIODES" :key="p.key" :style="getPeriodeTab(p.key)" @click="activePeriode=p.key">{{ p.label }}</button>
          </div>
          <div style="position:relative;display:flex;align-items:center">
            <svg style="position:absolute;left:10px;color:#94a3b8;pointer-events:none" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            <input v-model="venteSearch" :style="searchInput" placeholder="Rechercher..."/>
          </div>
        </div>
      </div>
      <div :style="tableWrap">
        <table :style="tableStyle">
          <thead><tr :style="thead">
            <th :style="th">#</th><th :style="th">Date</th><th :style="th">Produits</th>
            <th :style="th">Client</th><th :style="th">Montant</th><th :style="th">Payé</th>
            <th :style="th">Reste</th><th :style="th">Paiement</th><th :style="th">Statut</th><th :style="th">Act.</th>
          </tr></thead>
          <tbody>
            <tr v-if="!filteredVentes.length"><td colspan="10" :style="emptyTd"><p>Aucune vente pour cette période</p></td></tr>
            <tr v-for="(v,i) in filteredVentes" :key="v.id" :style="getTr(i)" @mouseenter="hovRow=i" @mouseleave="hovRow=null">
              <td :style="td"><span :style="venteNumStyle">#{{ v.id.toString().padStart(4,'0') }}</span></td>
              <td :style="td"><div style="display:flex;flex-direction:column;gap:1px"><span style="font-size:13px;font-weight:700;color:#0f172a">{{ formatDateShort(v.date) }}</span><span style="font-size:11px;color:#94a3b8">{{ formatTime(v.date) }}</span></div></td>
              <td :style="td"><div style="display:flex;flex-wrap:wrap;gap:4px"><span v-for="p in v.produits.slice(0,2)" :key="p.nom" :style="prodPillStyle">{{ p.nom }} ×{{ p.qte }}</span><span v-if="v.produits.length>2" :style="morePillStyle">+{{ v.produits.length-2 }}</span></div></td>
              <td :style="td"><span style="font-size:13px;color:#475569;font-weight:500">{{ v.client||'—' }}</span></td>
              <td :style="td"><span style="font-size:13px;font-weight:800;color:#0f172a">{{ formatAmount(v.montant) }}</span></td>
              <td :style="td"><span :style="{ fontSize:'13px',fontWeight:'700',color:v.montant_paye>=v.montant?'#10b981':'#f59e0b' }">{{ formatAmount(v.montant_paye||0) }}</span></td>
              <td :style="td"><span v-if="(v.montant-(v.montant_paye||0))>0" style="font-size:12px;font-weight:800;color:#ef4444">{{ formatAmount(v.montant-(v.montant_paye||0)) }}</span><span v-else style="color:#10b981;font-size:13px">✓</span></td>
              <td :style="td"><span :style="getPaiStyle(v.paiement)">{{ v.paiement }}</span></td>
              <td :style="td"><span :style="getVStatus(v.statut_paiement)">{{ v.statut_paiement }}</span></td>
              <td :style="td"><div style="display:flex;gap:5px">
                <button :style="aIco" @click="showVenteDetail(v)"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
                <button :style="aIco"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg></button>
              </div></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ════════ MODAL VENTE ÉLARGIE ════════ -->
    <Transition name="slide-modal">
      <div v-if="showVenteModal" :style="overlayStyle" @click.self="showVenteModal=false">
        <div :style="venteModalStyle" class="vente-modal-panel">
          <div :style="venteModalHeader">
            <div style="display:flex;align-items:center;gap:14px">
              <div :style="modalIconStyle"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg></div>
              <div><h2 :style="modalTitleStyle">Nouvelle Vente</h2><p :style="modalSubStyle">{{ pdv.nom }} — {{ formatDateToday() }}</p></div>
            </div>
            <button :style="closeBtnStyle" @click="showVenteModal=false"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg></button>
          </div>

          <div :style="venteModalBody">
            <div style="display:grid;grid-template-columns:1.4fr 1fr;gap:28px;align-items:start">

              <!-- Colonne gauche : produits -->
              <div>
                <!-- Client -->
                <div :style="vmSec">
                  <label :style="vmLabel">Client</label>
                  <select v-model="venteForm.client_id" :style="vmInput" @change="onClientChange">
                    <option value="">— Client de passage —</option>
                    <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.nom }} ({{ c.contact }})</option>
                    <option value="__new__">+ Saisir manuellement...</option>
                  </select>
                  <input v-if="venteForm.client_id==='__new__'" v-model="venteForm.client_nom" :style="{ ...vmInput, marginTop:'8px' }" placeholder="Nom du client passant..."/>
                </div>

                <!-- Lignes produits -->
                <div :style="vmSec">
                  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
                    <label :style="vmLabel">Produits</label>
                    <button :style="addLigneBtnStyle" @click="addLigne">
                      <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg> Ajouter
                    </button>
                  </div>

                  <div :style="ligneColHeader">
                    <span style="flex:3">Produit</span>
                    <span style="width:58px;text-align:center">Qté</span>
                    <span style="width:90px;text-align:center">Réd./u (F)</span>
                    <span style="width:105px;text-align:right">Total</span>
                    <span style="width:26px"></span>
                  </div>

                  <div v-for="(l,i) in venteForm.lignes" :key="i" :style="ligneRowStyle">
                    <select v-model="l.produit_id" :style="{ ...vmInput, flex:3, fontSize:'13px', padding:'9px 10px' }" @change="onProduitChange(l)">
                      <option value="">Choisir un produit...</option>
                      <option v-for="p in produits" :key="p.id" :value="p.id">{{ p.nom }} — {{ formatAmount(p.prix) }}</option>
                    </select>
                    <input v-model.number="l.qte" type="number" min="1" :style="{ ...vmInput, width:'58px', textAlign:'center', fontSize:'13px', padding:'9px 6px' }" placeholder="1"/>
                    <input v-model.number="l.remise_unite" type="number" min="0" :style="{ ...vmInput, width:'90px', textAlign:'center', fontSize:'13px', padding:'9px 6px' }" placeholder="0"/>
                    <div style="width:105px;display:flex;flex-direction:column;align-items:flex-end;gap:2px">
                      <span style="font-size:13px;font-weight:800;color:#0f172a">{{ formatAmount(getLigneTotal(l)) }}</span>
                      <span v-if="l.remise_unite>0" style="font-size:11px;font-weight:700;color:#10b981">-{{ formatAmount(l.remise_unite*(l.qte||1)) }}</span>
                    </div>
                    <button :style="removeLigneBtnStyle" @click="venteForm.lignes.splice(i,1)"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg></button>
                  </div>

                  <!-- Alertes vente à perte -->
                  <div v-for="(l,i) in venteForm.lignes.filter(l=>isVenteAPerte(l))" :key="'perte'+i" :style="perteAlertStyle">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    <span><strong>{{ getProdNom(l.produit_id) }}</strong> : prix net {{ formatAmount(getPrixNet(l)) }} &lt; coût {{ formatAmount(getPrixAchat(l.produit_id)) }} → <strong>VENTE À PERTE</strong></span>
                  </div>
                </div>
              </div>

              <!-- Colonne droite : paiement -->
              <div style="display:flex;flex-direction:column;gap:14px">

                <!-- Récap -->
                <div :style="recapBox">
                  <div :style="recapTitle">Récapitulatif</div>
                  <div v-for="l in venteForm.lignes.filter(l=>l.produit_id)" :key="'rl'+l.produit_id" style="display:flex;justify-content:space-between;font-size:13px;color:#475569;margin-bottom:5px">
                    <span>{{ getProdNom(l.produit_id) }} ×{{ l.qte||1 }}</span>
                    <span style="font-weight:700;color:#0f172a">{{ formatAmount(getLigneTotal(l)) }}</span>
                  </div>
                  <div v-if="venteForm.remise_globale>0" style="display:flex;justify-content:space-between;font-size:13px;font-weight:700;color:#10b981">
                    <span>Remise globale</span><span>− {{ formatAmount(venteForm.remise_globale) }}</span>
                  </div>
                  <div style="display:flex;justify-content:space-between;align-items:center;padding-top:10px;border-top:2px solid #e2e8f0;margin-top:8px;font-weight:800;font-size:14px;color:#0f172a">
                    <span>TOTAL</span><span style="font-size:20px;font-weight:800;color:#0ea5e9">{{ formatAmount(venteModalTotal) }}</span>
                  </div>
                </div>

                <!-- Remise globale -->
                <div :style="vmSec">
                  <label :style="vmLabel">Remise globale (FCFA)</label>
                  <input v-model.number="venteForm.remise_globale" type="number" min="0" :style="vmInput" placeholder="0"/>
                </div>

                <!-- Règlement -->
                <div :style="vmSec">
                  <label :style="vmLabel">Mode de règlement</label>
                  <div :style="reglGrid">
                    <button v-for="r in REGLEMENTS" :key="r.key" :style="getReglBtn(r.key,'vente')" @click="venteForm.reglement=r.key">
                      <span style="font-size:16px">{{ r.icon }}</span>
                      <span style="font-size:11px;font-weight:700;text-align:center">{{ r.label }}</span>
                    </button>
                  </div>
                  <div v-if="venteForm.reglement==='partiel'" :style="sousOptBox">
                    <label :style="{ ...vmLabel, marginBottom:'6px' }">Montant versé maintenant</label>
                    <input v-model.number="venteForm.montant_verse" type="number" min="0" :style="vmInput" placeholder="0"/>
                    <div v-if="venteForm.montant_verse>0" :style="resteInfo">Reste à payer : <strong>{{ formatAmount(Math.max(0,venteModalTotal-venteForm.montant_verse)) }}</strong></div>
                  </div>
                  <div v-if="venteForm.reglement==='credit'" :style="{ ...sousOptBox, background:'#fef2f2', borderColor:'#fca5a5' }">
                    <div style="display:flex;align-items:center;gap:8px;color:#ef4444;font-weight:700;font-size:13px">
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                      Crédit total : {{ formatAmount(venteModalTotal) }}
                    </div>
                    <div v-if="!venteForm.client_id||venteForm.client_id===''||venteForm.client_id==='__new__'" style="font-size:12px;color:#f59e0b;margin-top:5px;font-weight:600">⚠ Identifiez le client pour un crédit traçable</div>
                  </div>
                </div>

                <!-- Moyen de paiement -->
                <div v-if="venteForm.reglement!=='credit'" :style="vmSec">
                  <label :style="vmLabel">Moyen de paiement</label>
                  <div :style="paiTabs">
                    <button v-for="m in PAIEMENTS_MODES" :key="m.key" :style="getPaiTab(m.key,'vente')" @click="venteForm.paiement=m.key">
                      {{ m.icon }} {{ m.label }}
                    </button>
                  </div>
                  <div v-if="venteForm.paiement==='mobile_money'" :style="mmGrid">
                    <button v-for="mm in MOBILE_MONEY_OPS" :key="mm.key" :style="getMMBtn(mm.key,'vente')" @click="venteForm.mm_op=mm.key">
                      <span style="font-size:20px">{{ mm.emoji }}</span>
                      <span style="font-size:10px;font-weight:700;color:#475569;text-align:center">{{ mm.label }}</span>
                    </button>
                  </div>
                  <input v-if="venteForm.paiement==='mobile_money'||venteForm.paiement==='carte'" v-model="venteForm.ref_transaction" :style="{ ...vmInput, marginTop:'8px' }" placeholder="N° transaction / référence (optionnel)"/>
                </div>
              </div>
            </div>

            <div :style="vmActions">
              <button :style="vmCancel" @click="showVenteModal=false">Annuler</button>
              <button :style="vmSubmit" @click="saveVente" :disabled="saving">
                <svg v-if="!saving" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                <div v-else :style="spinSm"></div>
                {{ saving?'Enregistrement...':'Valider la vente' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- ════════ CAISSE PLEIN ÉCRAN — FOND CLAIR ════════ -->
    <Transition name="caisse-open">
      <div v-if="showCaisseMode" :style="caisseOverlay" class="caisse-screen">

        <!-- Topbar -->
        <div :style="caisseTopbar">
          <div :style="caisseTopLeft">
            <div :style="caisseLogo"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="15" rx="2"/><path d="M16 3H8a1 1 0 00-1 1v3h10V4a1 1 0 00-1-1z"/><path d="M12 12v4M10 14h4"/></svg></div>
            <div><div :style="caisseNom">{{ pdv.nom }}</div><div :style="caisseSub">Caisse · {{ formatDateToday() }}</div></div>
          </div>
          <div style="flex:1;padding:0 16px">
            <div style="position:relative;display:flex;align-items:center">
              <svg style="position:absolute;left:14px;color:#94a3b8;pointer-events:none" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
              <input v-model="caisseSearch" :style="caisseSearchInput" placeholder="Rechercher un produit, scanner code-barre..."/>
            </div>
          </div>
          <div style="display:flex;align-items:center;gap:10px">
            <div style="display:flex;gap:6px;overflow-x:auto;max-width:420px">
              <button v-for="cat in CATEGORIES" :key="cat" :style="getCatBtn(cat)" @click="activeCat=cat">{{ cat }}</button>
            </div>
            <button :style="caisseCloseBtn" @click="showCaisseMode=false">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg> Fermer
            </button>
          </div>
        </div>

        <!-- Corps -->
        <div :style="caisseBody">
          <!-- Grille produits -->
          <div :style="caisseProdWrap">
            <div :style="caisseProdGrid">
              <div v-for="prod in filteredCaisseProduits" :key="prod.id"
                :style="getCaisseCard(prod)"
                @mouseenter="hovProd=prod.id" @mouseleave="hovProd=null"
                @click="addToCart(prod)" class="prod-card">
                <div v-if="prod.stock>0&&prod.stock<=5" :style="lowStockBadge">Stock faible</div>
                <div v-if="prod.stock===0" :style="ruptureBadge">Rupture</div>
                <div :style="prodEmojiStyle">{{ prod.emoji||'📦' }}</div>
                <div :style="prodNomStyle">{{ prod.nom }}</div>
                <div :style="prodCatStyle">{{ prod.categorie }}</div>
                <div :style="prodFoot">
                  <span :style="prodPrixStyle">{{ formatAmount(prod.prix) }}</span>
                  <span :style="prodStockStyle(prod.stock)">{{ prod.stock }}</span>
                </div>
                <div v-if="getCartQty(prod.id)>0" :style="cartBadge">{{ getCartQty(prod.id) }}</div>
              </div>
            </div>
          </div>

          <!-- Panier -->
          <div :style="caissePanier">
            <div :style="panierHeader">
              <h3 :style="panierTitle">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                Panier
              </h3>
              <div style="display:flex;align-items:center;gap:7px">
                <span :style="panierCount">{{ cartItems.length }} article(s)</span>
                <button v-if="cartItems.length>0" :style="viderBtn" @click="cartItems=[];caisseRemise=0" title="Vider">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                </button>
              </div>
            </div>

            <div :style="panierClientRow">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              <select v-model="caisseClientId" :style="panierClientSelect">
                <option value="">Client de passage</option>
                <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.nom }}</option>
              </select>
            </div>

            <div :style="panierItems">
              <div v-if="!cartItems.length" :style="panierEmpty">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                <p style="margin:10px 0 3px;font-size:13px;font-weight:700;color:#94a3b8">Panier vide</p>
                <p style="font-size:12px;color:#cbd5e1">Cliquez sur un produit pour l'ajouter</p>
              </div>

              <div v-for="(item,ii) in cartItems" :key="item.id" :style="panierItem(ii)">
                <div style="font-size:20px;flex-shrink:0">{{ item.emoji||'📦' }}</div>
                <div style="flex:1;min-width:0">
                  <div style="font-size:13px;font-weight:700;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ item.nom }}</div>
                  <div style="font-size:11px;color:#94a3b8;margin-top:1px;display:flex;align-items:center;gap:4px">
                    <span>{{ formatAmount(item.prix) }}/u</span>
                    <span v-if="item.remise_unite>0" style="color:#10b981;font-weight:700">-{{ formatAmount(item.remise_unite) }}</span>
                  </div>
                  <!-- Réduction par produit -->
                  <div style="display:flex;align-items:center;gap:4px;margin-top:4px">
                    <span style="font-size:10px;color:#94a3b8;font-weight:600">Réd/u:</span>
                    <input v-model.number="item.remise_unite" type="number" min="0" :max="item.prix" :style="remiseProdInput" placeholder="0" @click.stop/>
                    <span style="font-size:10px;color:#94a3b8">F</span>
                    <span v-if="isPerteProd(item)" style="font-size:10px;font-weight:800;color:#ef4444;background:#fef2f2;padding:2px 5px;border-radius:4px;margin-left:2px">⚠ Perte</span>
                  </div>
                </div>
                <div :style="panierQteCtrl">
                  <button :style="qteBtn" @click.stop="changeQty(item,-1)">−</button>
                  <span :style="qteVal">{{ item.qte }}</span>
                  <button :style="qteBtn" @click.stop="changeQty(item,1)">+</button>
                </div>
                <div style="width:90px;text-align:right;font-size:13px;font-weight:800;" :style="{ color:isPerteProd(item)?'#ef4444':'#0f172a' }">
                  {{ formatAmount((item.prix-(item.remise_unite||0))*item.qte) }}
                </div>
                <button :style="removeItemBtn" @click.stop="removeFromCart(item.id)">
                  <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
                </button>
              </div>
            </div>

            <!-- Footer panier -->
            <div :style="panierFoot">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
                <span style="font-size:12px;color:#64748b;font-weight:700">Remise globale</span>
                <div style="display:flex;align-items:center;gap:5px;background:#f8fafc;border-radius:7px;padding:5px 9px;border:1px solid #e2e8f0">
                  <input v-model.number="caisseRemise" type="number" min="0" :style="remiseInput" placeholder="0"/>
                  <span style="font-size:11px;color:#94a3b8;font-weight:600">FCFA</span>
                </div>
              </div>

              <div :style="totalBox">
                <div :style="subRow"><span>Sous-total</span><span>{{ formatAmount(cartSubtotal) }}</span></div>
                <div v-if="cartRemiseProduits>0" :style="discRow"><span>Réductions produits</span><span style="color:#10b981">− {{ formatAmount(cartRemiseProduits) }}</span></div>
                <div v-if="caisseRemise>0" :style="discRow"><span>Remise globale</span><span style="color:#10b981">− {{ formatAmount(caisseRemise) }}</span></div>
                <div :style="totalRow"><span>TOTAL</span><span style="color:#0ea5e9;font-size:19px;font-weight:800">{{ formatAmount(cartTotal) }}</span></div>
              </div>

              <!-- Règlement caisse -->
              <div style="margin-bottom:10px">
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:5px;margin-bottom:6px">
                  <button v-for="r in REGLEMENTS" :key="r.key" :style="getReglBtn(r.key,'caisse')" @click="caisseReglement=r.key">
                    <span style="font-size:14px">{{ r.icon }}</span>
                    <span style="font-size:10px;font-weight:700;text-align:center">{{ r.label }}</span>
                  </button>
                </div>
                <div v-if="caisseReglement==='partiel'" style="margin-top:6px">
                  <input v-model.number="caisseMontantVerse" type="number" min="0" :style="{ ...remiseInput, width:'100%', padding:'8px 12px', textAlign:'left', border:'1px solid #e2e8f0', borderRadius:'8px', background:'white' }" placeholder="Montant versé..."/>
                  <div v-if="caisseMontantVerse>0" style="font-size:12px;color:#f59e0b;font-weight:700;margin-top:4px">Reste : {{ formatAmount(Math.max(0,cartTotal-caisseMontantVerse)) }}</div>
                </div>
                <div v-if="caisseReglement==='credit'" style="padding:8px 10px;background:#fef2f2;border-radius:8px;font-size:12px;font-weight:700;color:#ef4444;margin-top:6px">⚠ Crédit total — {{ formatAmount(cartTotal) }} à récupérer</div>
              </div>

              <!-- Mode paiement -->
              <div v-if="caisseReglement!=='credit'" style="margin-bottom:8px">
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:5px;margin-bottom:6px">
                  <button v-for="m in PAIEMENTS_MODES" :key="m.key" :style="getPaiTab(m.key,'caisse')" @click="caissePaiement=m.key">{{ m.icon }} {{ m.label }}</button>
                </div>
                <div v-if="caissePaiement==='mobile_money'" style="display:grid;grid-template-columns:repeat(5,1fr);gap:4px;margin-bottom:6px">
                  <button v-for="mm in MOBILE_MONEY_OPS" :key="mm.key" :style="getCaisseMMBtn(mm.key)" @click="caisseMMOp=mm.key">
                    <span style="font-size:18px">{{ mm.emoji }}</span>
                    <span style="font-size:10px;font-weight:700;color:#475569">{{ mm.label }}</span>
                  </button>
                </div>
              </div>

              <button :style="encaisserBtn" @click="validerVenteCaisse" :disabled="!cartItems.length||caisseSaving">
                <svg v-if="!caisseSaving" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <div v-else :style="spinWhite"></div>
                <span>{{ caisseSaving?'Encaissement...':`Encaisser ${formatAmount(cartTotal)}` }}</span>
              </button>

              <div style="display:flex;justify-content:center;gap:12px;margin-top:8px;font-size:10px;color:#cbd5e1;font-weight:600;letter-spacing:0.03em">
                <span>Entrée — Valider</span><span>Suppr — Vider</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Toast -->
        <Transition name="toast">
          <div v-if="showToast" :style="toastStyle">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Vente enregistrée avec succès !
          </div>
        </Transition>
      </div>
    </Transition>

  </SidebarLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import { usePdvSelectionStore } from '../stores/pdvSelectionStore'
import { useRoute } from 'vue-router'
import SidebarLayout from './SidebarLayout.vue'
import { getPointVente, updatePointVente, deletePointVente } from '../services/api'

// ─── Constantes
const PERIODES=[{key:'today',label:"Aujourd'hui"},{key:'week',label:'7 jours'},{key:'month',label:'Ce mois'},{key:'all',label:'Tout'}]
const CATEGORIES=['Tout','Alimentation','Boissons','Hygiène','Électronique','Vêtements']
const REGLEMENTS=[{key:'total',label:'Paiement Total',icon:'✅'},{key:'partiel',label:'Paiement Partiel',icon:'⏳'},{key:'credit',label:'À Crédit',icon:'📋'}]
const PAIEMENTS_MODES=[{key:'especes',label:'Espèces',icon:'💵'},{key:'mobile_money',label:'Mobile Money',icon:'📱'},{key:'carte',label:'Carte',icon:'💳'}]
const MOBILE_MONEY_OPS=[{key:'wave',label:'Wave',emoji:'🔵'},{key:'orange',label:'Orange Money',emoji:'🟠'},{key:'mtn',label:'MTN Money',emoji:'🟡'},{key:'moov',label:'Moov Money',emoji:'🟢'},{key:'autre',label:'Autre',emoji:'📲'}]

// ─── Données PDV (chargées via API)
const pdv = reactive({})
const clients=ref([{id:1,nom:'Koné Mariam',contact:'+225 07 11 22 33'},{id:2,nom:'Diabaté Ibrahim',contact:'+225 05 44 55 66'},{id:3,nom:'Touré Aminata',contact:'+225 01 77 88 99'},{id:4,nom:'Ouédraogo Pierre',contact:'+225 09 22 33 44'}])
const produits=ref([
  {id:1,nom:'Riz Parfumé 5kg',prix:5500,prix_achat:4800,stock:42,categorie:'Alimentation',emoji:'🌾'},
  {id:2,nom:'Huile Palme 1L',prix:1200,prix_achat:950,stock:30,categorie:'Alimentation',emoji:'🫙'},
  {id:3,nom:'Coca-Cola 1.5L',prix:800,prix_achat:600,stock:60,categorie:'Boissons',emoji:'🥤'},
  {id:4,nom:'Eau Minérale 1.5L',prix:400,prix_achat:280,stock:120,categorie:'Boissons',emoji:'💧'},
  {id:5,nom:'Fanta Orange 1L',prix:700,prix_achat:520,stock:45,categorie:'Boissons',emoji:'🍊'},
  {id:6,nom:'Savon Lux',prix:350,prix_achat:250,stock:80,categorie:'Hygiène',emoji:'🧼'},
  {id:7,nom:'Lait Condensé',prix:600,prix_achat:450,stock:55,categorie:'Alimentation',emoji:'🥛'},
  {id:8,nom:'Sardines Saupiquet',prix:900,prix_achat:700,stock:38,categorie:'Alimentation',emoji:'🐟'},
  {id:9,nom:'Pâtes Spaghetti',prix:750,prix_achat:580,stock:67,categorie:'Alimentation',emoji:'🍝'},
  {id:10,nom:'Tablette Chocolat',prix:1500,prix_achat:1100,stock:25,categorie:'Alimentation',emoji:'🍫'},
  {id:11,nom:'Dentifrice Signal',prix:1200,prix_achat:900,stock:40,categorie:'Hygiène',emoji:'🦷'},
  {id:12,nom:'Papier Toilette ×6',prix:1800,prix_achat:1400,stock:22,categorie:'Hygiène',emoji:'🧻'},
  {id:13,nom:'Biscuits Oréo',prix:600,prix_achat:420,stock:90,categorie:'Alimentation',emoji:'🍪'},
  {id:14,nom:'Bougie ×10',prix:500,prix_achat:350,stock:4,categorie:'Hygiène',emoji:'🕯️'},
  {id:15,nom:'T-shirt Blanc L',prix:3500,prix_achat:2200,stock:12,categorie:'Vêtements',emoji:'👕'},
  {id:16,nom:'Écouteurs Sans Fil',prix:15000,prix_achat:11000,stock:8,categorie:'Électronique',emoji:'🎧'},
])
const ventes=ref([
  {id:142,date:new Date().toISOString(),produits:[{nom:'Riz',qte:2},{nom:'Huile',qte:1}],client:'Koné Mariam',montant:12200,montant_paye:12200,paiement:'Espèces',statut:'Payée',statut_paiement:'Soldée'},
  {id:141,date:new Date(Date.now()-3600000).toISOString(),produits:[{nom:'Coca',qte:6},{nom:'Eau',qte:12}],client:null,montant:9600,montant_paye:5000,paiement:'Espèces',statut:'Payée',statut_paiement:'Partielle'},
  {id:140,date:new Date(Date.now()-7200000).toISOString(),produits:[{nom:'T-shirt',qte:2}],client:'Diabaté Sékou',montant:7000,montant_paye:0,paiement:'Crédit',statut:'Crédit',statut_paiement:'Crédit'},
  {id:139,date:new Date(Date.now()-86400000).toISOString(),produits:[{nom:'Sardines',qte:5}],client:null,montant:6750,montant_paye:6750,paiement:'Mobile Money (Wave)',statut:'Payée',statut_paiement:'Soldée'},
  {id:138,date:new Date(Date.now()-86400000*2).toISOString(),produits:[{nom:'Écouteurs',qte:1}],client:'Touré Aminata',montant:15000,montant_paye:15000,paiement:'Carte',statut:'Payée',statut_paiement:'Soldée'},
])

// ─── State
// Charger le détail du point de vente depuis l’API
const pdvSelectionStore = usePdvSelectionStore()
const id_pdv = pdvSelectionStore.selectedPdvId
const loading = ref(false)
const error = ref(null)

const loadPdv = async () => {
  if (!id_pdv) return
  loading.value = true
  error.value = null
  try {
    const res = await getPointVente(id_pdv)
    if (res.data && res.data.data) Object.assign(pdv, res.data.data)
  } catch (e) {
    error.value = e.response?.data?.error || 'Erreur lors du chargement du point de vente'
  } finally {
    loading.value = false
  }
}

onMounted(loadPdv)
// Fonction pour mettre à jour le point de vente
const savePdv = async (payload) => {
  try {
    payload.id_pdv = pdv.id_pdv
    const res = await updatePointVente(payload)
    if (res.data.success) await loadPdv()
  } catch (e) {
    alert('Erreur lors de la mise à jour')
  }
}

// Fonction pour supprimer le point de vente
const removePdv = async () => {
  if (!confirm('Supprimer ce point de vente ?')) return
  try {
    const res = await deletePointVente(pdv.id_pdv)
    if (res.data.success) {
      // Rediriger vers la liste après suppression
      window.location.href = '/ventes'
    }
  } catch (e) {
    alert('Erreur lors de la suppression')
  }
}
const saving=ref(false);const caisseSaving=ref(false)
const hovMV=ref(false);const hovC=ref(false);const hovRow=ref(null);const hovProd=ref(null)
const showVenteModal=ref(false);const showCaisseMode=ref(false);const showToast=ref(false)
const activePeriode=ref('today');const venteSearch=ref('');const activeCat=ref('Tout')
const caisseSearch=ref('');const caisseClientId=ref('');const caisseRemise=ref(0)
const caissePaiement=ref('especes');const caisseReglement=ref('total');const caisseMMOp=ref('wave');const caisseMontantVerse=ref(0)

const venteForm=reactive({client_id:'',client_nom:'',lignes:[{produit_id:'',qte:1,prix:0,prix_achat:0,remise_unite:0}],remise_globale:0,reglement:'total',montant_verse:0,paiement:'especes',mm_op:'wave',ref_transaction:''})
const cartItems=ref([])

// ─── Computed
const ventesAujourdhui=computed(()=>ventes.value.filter(v=>isToday(v.date)))
const venteMois=computed(()=>ventes.value.filter(v=>isSameMonth(v.date)).reduce((s,v)=>s+v.montant,0))
const filteredVentes=computed(()=>{
  let r=ventes.value
  const now=new Date()
  if(activePeriode.value==='today')r=r.filter(v=>isToday(v.date))
  else if(activePeriode.value==='week')r=r.filter(v=>(now-new Date(v.date))<7*86400000)
  else if(activePeriode.value==='month')r=r.filter(v=>isSameMonth(v.date))
  if(venteSearch.value.trim()){const q=venteSearch.value.toLowerCase();r=r.filter(v=>(v.client||'').toLowerCase().includes(q)||v.produits.some(p=>p.nom.toLowerCase().includes(q)))}
  return r
})
const filteredCaisseProduits=computed(()=>{
  let r=produits.value
  if(activeCat.value!=='Tout')r=r.filter(p=>p.categorie===activeCat.value)
  if(caisseSearch.value.trim()){const q=caisseSearch.value.toLowerCase();r=r.filter(p=>p.nom.toLowerCase().includes(q))}
  return r
})
const venteModalTotal=computed(()=>Math.max(0,venteForm.lignes.reduce((s,l)=>s+getLigneTotal(l),0)-(venteForm.remise_globale||0)))
const cartSubtotal=computed(()=>cartItems.value.reduce((s,i)=>s+i.prix*i.qte,0))
const cartRemiseProduits=computed(()=>cartItems.value.reduce((s,i)=>s+(i.remise_unite||0)*i.qte,0))
const cartTotal=computed(()=>Math.max(0,cartSubtotal.value-cartRemiseProduits.value-(caisseRemise.value||0)))

const statsData=computed(()=>[
  {label:"Ventes Aujourd'hui",color:'#0ea5e9',value:formatAmount(pdv.vente_jour||0),icon:'<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>',sub:`<span style="color:${(pdv.vente_jour||0)>=(pdv.vente_hier||0)?'#10b981':'#ef4444'};font-weight:700">${(pdv.vente_jour||0)>=(pdv.vente_hier||0)?'▲':'▼'} ${Math.abs(Math.round(((pdv.vente_jour||0)-(pdv.vente_hier||1))/(pdv.vente_hier||1)*100))}%</span> vs hier`},
  {label:'Ventes Hier',color:'#6366f1',value:formatAmount(pdv.vente_hier||0),icon:'<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',sub:`${ventes.value.filter(v=>isYesterday(v.date)).length} transactions`},
  {label:'Ce mois',color:'#10b981',value:formatAmount(venteMois.value),icon:'<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>',sub:`${ventes.value.filter(v=>isSameMonth(v.date)).length} ventes`},
  {label:'À Encaisser',color:'#ef4444',value:formatAmount(pdv.a_encaisser||0),icon:'<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',sub:`<span style="color:${(pdv.a_encaisser||0)>0?'#ef4444':'#10b981'};font-weight:700">${(pdv.a_encaisser||0)>0?'⚠ Non clôturé':'✓ Clôturé'}</span>`,style:(pdv.a_encaisser||0)>0?{...getStatTile(),border:'2px solid #fca5a5'}:undefined},
  {label:'Transactions/Jour',color:'#f59e0b',value:ventesAujourdhui.value.length.toString(),icon:'<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>',sub:'transactions ce jour'},
])

// ─── Helpers
const isToday=d=>{const t=new Date(d),n=new Date();return t.toDateString()===n.toDateString()}
const isYesterday=d=>{const t=new Date(d),n=new Date();n.setDate(n.getDate()-1);return t.toDateString()===n.toDateString()}
const isSameMonth=d=>{const t=new Date(d),n=new Date();return t.getMonth()===n.getMonth()&&t.getFullYear()===n.getFullYear()}
const getInitials=n=>(typeof n === 'string' && n.trim()) ? n.split(' ').map(w=>w[0]).join('').toUpperCase().slice(0,2) : ''
const formatAmount=n=>new Intl.NumberFormat('fr-FR').format(n)+' FCFA'
const formatDateToday=()=>new Date().toLocaleDateString('fr-FR',{weekday:'long',day:'2-digit',month:'long',year:'numeric'})
const formatDateShort=d=>new Date(d).toLocaleDateString('fr-FR',{day:'2-digit',month:'short'})
const formatTime=d=>new Date(d).toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'})

const getLigneTotal=l=>Math.max(0,((l.prix||0)-(l.remise_unite||0)))*(l.qte||1)
const getProdNom=id=>{const p=produits.value.find(x=>x.id==id);return p?p.nom:'?'}
const getPrixNet=l=>(l.prix||0)-(l.remise_unite||0)
const getPrixAchat=id=>{const p=produits.value.find(x=>x.id==id);return p?p.prix_achat:0}
const isVenteAPerte=l=>l.produit_id&&getPrixNet(l)<getPrixAchat(l.produit_id)
const isPerteProd=item=>(item.prix-(item.remise_unite||0))<(item.prix_achat||0)

const onProduitChange=l=>{const p=produits.value.find(x=>x.id==l.produit_id);if(p){l.prix=p.prix;l.nom=p.nom;l.prix_achat=p.prix_achat;l.remise_unite=0}}
const onClientChange=()=>{if(venteForm.client_id!=='__new__')venteForm.client_nom=''}
const addLigne=()=>venteForm.lignes.push({produit_id:'',qte:1,prix:0,prix_achat:0,remise_unite:0})

const getCartQty=id=>{const i=cartItems.value.find(x=>x.id===id);return i?i.qte:0}
const addToCart=prod=>{if(prod.stock===0)return;const ex=cartItems.value.find(i=>i.id===prod.id);if(ex)ex.qte++;else cartItems.value.push({...prod,qte:1,remise_unite:0})}
const changeQty=(item,d)=>{item.qte+=d;if(item.qte<=0)removeFromCart(item.id)}
const removeFromCart=id=>{cartItems.value=cartItems.value.filter(i=>i.id!==id)}

const buildPaiLabel=(paiement,mmOp,reglement)=>{
  if(reglement==='credit')return'Crédit'
  if(paiement==='especes')return'Espèces'
  if(paiement==='carte')return'Carte'
  if(paiement==='mobile_money'){const op=MOBILE_MONEY_OPS.find(m=>m.key===mmOp);return`Mobile Money (${op?.label||''})`}
  return paiement
}

const openCaisse=()=>{cartItems.value=[];caisseRemise.value=0;caisseClientId.value='';caissePaiement.value='especes';caisseReglement.value='total';caisseMontantVerse.value=0;showCaisseMode.value=true}
const openVenteModal=()=>{Object.assign(venteForm,{client_id:'',client_nom:'',lignes:[{produit_id:'',qte:1,prix:0,prix_achat:0,remise_unite:0}],remise_globale:0,reglement:'total',montant_verse:0,paiement:'especes',mm_op:'wave',ref_transaction:''});showVenteModal.value=true}

const saveVente=async()=>{
  saving.value=true;await new Promise(r=>setTimeout(r,700))
  const clientNom=venteForm.client_id&&venteForm.client_id!=='__new__'?clients.value.find(c=>c.id==venteForm.client_id)?.nom:venteForm.client_nom||null
  const montant_paye=venteForm.reglement==='total'?venteModalTotal.value:venteForm.reglement==='partiel'?(venteForm.montant_verse||0):0
  const newV={id:ventes.value.length+100,date:new Date().toISOString(),produits:venteForm.lignes.filter(l=>l.produit_id).map(l=>({nom:getProdNom(l.produit_id),qte:l.qte})),client:clientNom,montant:venteModalTotal.value,montant_paye,paiement:buildPaiLabel(venteForm.paiement,venteForm.mm_op,venteForm.reglement),statut:'Payée',statut_paiement:venteForm.reglement==='total'?'Soldée':venteForm.reglement==='partiel'?'Partielle':'Crédit'}
  ventes.value.unshift(newV);pdv.vente_jour=(pdv.vente_jour||0)+newV.montant
  if(newV.montant_paye<newV.montant)pdv.a_encaisser=(pdv.a_encaisser||0)+(newV.montant-newV.montant_paye)
  saving.value=false;showVenteModal.value=false
}

const validerVenteCaisse=async()=>{
  if(!cartItems.value.length)return;caisseSaving.value=true;await new Promise(r=>setTimeout(r,800))
  const montant_paye=caisseReglement.value==='total'?cartTotal.value:caisseReglement.value==='partiel'?(caisseMontantVerse.value||0):0
  const newV={id:ventes.value.length+100,date:new Date().toISOString(),produits:cartItems.value.map(i=>({nom:i.nom,qte:i.qte})),client:caisseClientId.value?clients.value.find(c=>c.id==caisseClientId.value)?.nom||null:null,montant:cartTotal.value,montant_paye,paiement:buildPaiLabel(caissePaiement.value,caisseMMOp.value,caisseReglement.value),statut:'Payée',statut_paiement:caisseReglement.value==='total'?'Soldée':caisseReglement.value==='partiel'?'Partielle':'Crédit'}
  ventes.value.unshift(newV);pdv.vente_jour=(pdv.vente_jour||0)+newV.montant
  if(newV.montant_paye<newV.montant)pdv.a_encaisser=(pdv.a_encaisser||0)+(newV.montant-newV.montant_paye)
  caisseSaving.value=false;cartItems.value=[];caisseRemise.value=0;caisseClientId.value=''
  showToast.value=true;setTimeout(()=>showToast.value=false,3000)
}

const showVenteDetail=v=>alert(`Vente #${v.id.toString().padStart(4,'0')}\nMontant: ${formatAmount(v.montant)}\nPayé: ${formatAmount(v.montant_paye)}\nReste: ${formatAmount(v.montant-v.montant_paye)}\nPaiement: ${v.paiement}\nStatut: ${v.statut_paiement}`)

const handleKeydown=e=>{if(!showCaisseMode.value)return;if(e.key==='Enter'&&cartItems.value.length>0)validerVenteCaisse();if(e.key==='Delete'){cartItems.value=[];caisseRemise.value=0}}
onMounted(()=>document.addEventListener('keydown',handleKeydown))
onUnmounted(()=>document.removeEventListener('keydown',handleKeydown))

// ─── STYLES
const pageHeaderStyle={marginBottom:'24px'}
const backRowStyle={display:'flex',justifyContent:'space-between',alignItems:'center',marginBottom:'20px'}
const backBtnStyle={display:'flex',alignItems:'center',gap:'8px',padding:'8px 16px',background:'white',border:'1px solid #e2e8f0',borderRadius:'10px',fontSize:'13px',fontWeight:'600',color:'#64748b',cursor:'pointer'}
const headerActionsStyle={display:'flex',gap:'10px'}
const modalVenteBtnStyle=computed(()=>({display:'flex',alignItems:'center',gap:'8px',padding:'11px 20px',background:hovMV.value?'#f0f9ff':'white',border:'1px solid #bae6fd',borderRadius:'10px',fontSize:'14px',fontWeight:'700',color:'#0284c7',cursor:'pointer',transition:'all 0.2s'}))
const caisseBtnStyle=computed(()=>({display:'flex',alignItems:'center',gap:'8px',padding:'11px 22px',background:'linear-gradient(135deg,#1e293b,#0f172a)',border:'none',borderRadius:'10px',fontSize:'14px',fontWeight:'700',color:'white',cursor:'pointer',boxShadow:hovC.value?'0 8px 20px rgba(15,23,42,0.35)':'0 4px 12px rgba(15,23,42,0.2)',transform:hovC.value?'translateY(-1px)':'none',transition:'all 0.2s'}))
const heroStyle={display:'flex',alignItems:'flex-start',gap:'24px',background:'white',borderRadius:'20px',padding:'28px',boxShadow:'0 2px 8px rgba(0,0,0,0.06)',border:'1px solid #f1f5f9',flexWrap:'wrap'}
const heroIconStyle={width:'72px',height:'72px',background:'linear-gradient(135deg,#0ea5e9,#0284c7)',borderRadius:'18px',display:'flex',alignItems:'center',justifyContent:'center',color:'white',flexShrink:0}
const heroInfoStyle={flex:1,minWidth:'200px'}
const heroTagsStyle={display:'flex',gap:'8px',marginBottom:'8px'}
const typeBadgeStyle={padding:'4px 12px',background:'#f0f9ff',border:'1px solid #bae6fd',borderRadius:'20px',fontSize:'12px',fontWeight:'700',color:'#0284c7',textTransform:'capitalize'}
const getStatusBadge=a=>({display:'flex',alignItems:'center',gap:'5px',padding:'4px 12px',background:a?'#dcfce7':'#fee2e2',border:`1px solid ${a?'#86efac':'#fca5a5'}`,borderRadius:'20px',fontSize:'12px',fontWeight:'700',color:a?'#166534':'#991b1b'})
const statusDot=a=>({width:'6px',height:'6px',borderRadius:'50%',background:a?'#22c55e':'#ef4444'})
const heroTitleStyle={fontSize:'28px',fontWeight:'800',color:'#0f172a',margin:'0 0 6px',letterSpacing:'-0.03em'}
const heroLocStyle={fontSize:'14px',color:'#64748b',display:'flex',alignItems:'center',gap:'5px',fontWeight:'500'}
const heroRespStyle={display:'flex',alignItems:'center',gap:'14px',background:'#f8fafc',borderRadius:'14px',padding:'16px 20px',flexShrink:0}
const heroAvatarStyle={width:'48px',height:'48px',borderRadius:'14px',background:'linear-gradient(135deg,#0ea5e9,#6366f1)',display:'flex',alignItems:'center',justifyContent:'center',fontSize:'17px',fontWeight:'800',color:'white',flexShrink:0}
const heroRespName={fontSize:'15px',fontWeight:'700',color:'#0f172a'}
const heroRespRole={fontSize:'12px',color:'#94a3b8',fontWeight:'500',marginTop:'2px'}
const heroContact={display:'flex',alignItems:'center',gap:'5px',fontSize:'13px',color:'#0ea5e9',fontWeight:'600',textDecoration:'none',marginTop:'4px'}
const statsGrid={display:'grid',gridTemplateColumns:'repeat(auto-fit,minmax(180px,1fr))',gap:'14px',margin:'20px 0'}
const getStatTile=()=>({background:'white',borderRadius:'14px',padding:'20px',border:'1px solid #f1f5f9',boxShadow:'0 1px 4px rgba(0,0,0,0.05)'})
const statTop={display:'flex',justifyContent:'space-between',alignItems:'center',marginBottom:'12px'}
const statLabel={fontSize:'12px',color:'#94a3b8',fontWeight:'700',textTransform:'uppercase',letterSpacing:'0.05em'}
const statIcon=c=>({width:'36px',height:'36px',borderRadius:'9px',background:`${c}15`,display:'flex',alignItems:'center',justifyContent:'center',color:c})
const statVal=c=>({fontSize:'19px',fontWeight:'800',color:'#0f172a',marginBottom:'4px',letterSpacing:'-0.02em'})
const statSub={fontSize:'12px',color:'#94a3b8',fontWeight:'500',display:'flex',alignItems:'center',gap:'5px'}
const sectionStyle={background:'white',borderRadius:'16px',padding:'24px',border:'1px solid #f1f5f9',boxShadow:'0 1px 4px rgba(0,0,0,0.05)'}
const secHeader={display:'flex',justifyContent:'space-between',alignItems:'flex-start',marginBottom:'20px',flexWrap:'wrap',gap:'12px'}
const secTitle={fontSize:'18px',fontWeight:'800',color:'#0f172a',margin:'0 0 4px',letterSpacing:'-0.02em'}
const secSub={fontSize:'13px',color:'#94a3b8',margin:0,fontWeight:'500'}
const histoActions={display:'flex',gap:'10px',alignItems:'center',flexWrap:'wrap'}
const periodeTabs={display:'flex',background:'#f8fafc',borderRadius:'10px',padding:'3px',gap:'2px'}
const getPeriodeTab=key=>({padding:'6px 14px',borderRadius:'8px',border:'none',background:activePeriode.value===key?'white':'transparent',color:activePeriode.value===key?'#0f172a':'#94a3b8',fontSize:'12px',fontWeight:'700',cursor:'pointer',boxShadow:activePeriode.value===key?'0 1px 4px rgba(0,0,0,0.08)':'none',transition:'all 0.2s'})
const searchInput={padding:'8px 12px 8px 32px',border:'1px solid #e2e8f0',borderRadius:'9px',fontSize:'13px',fontWeight:'500',color:'#1e293b',outline:'none',width:'180px'}
const tableWrap={overflowX:'auto',borderRadius:'12px',border:'1px solid #f1f5f9'}
const tableStyle={width:'100%',borderCollapse:'collapse'}
const thead={background:'#f8fafc'}
const th={padding:'11px 14px',textAlign:'left',fontSize:'11px',fontWeight:'800',color:'#64748b',textTransform:'uppercase',letterSpacing:'0.06em',borderBottom:'1px solid #f1f5f9'}
const getTr=i=>({background:hovRow.value===i?'#f8fafc':'white',transition:'background 0.15s',borderBottom:'1px solid #f8fafc'})
const td={padding:'11px 14px',fontSize:'13px',color:'#1e293b',verticalAlign:'middle'}
const emptyTd={textAlign:'center',padding:'48px',color:'#94a3b8'}
const venteNumStyle={fontFamily:'monospace',fontWeight:'800',color:'#0ea5e9',fontSize:'13px'}
const prodPillStyle={padding:'3px 8px',background:'#f1f5f9',borderRadius:'6px',fontSize:'11px',fontWeight:'600',color:'#475569'}
const morePillStyle={padding:'3px 8px',background:'#e2e8f0',borderRadius:'6px',fontSize:'11px',fontWeight:'700',color:'#64748b'}
const getPaiStyle=p=>{const c=p?.includes('Wave')?{bg:'#dbeafe',txt:'#1e40af'}:p?.includes('Orange')?{bg:'#fff7ed',txt:'#c2410c'}:p?.includes('MTN')?{bg:'#fef9c3',txt:'#854d0e'}:p?.includes('Moov')?{bg:'#dcfce7',txt:'#166534'}:p==='Espèces'?{bg:'#f3f4f6',txt:'#374151'}:p==='Carte'?{bg:'#ede9fe',txt:'#5b21b6'}:{bg:'#fce7f3',txt:'#9d174d'};return{padding:'3px 10px',borderRadius:'20px',fontSize:'11px',fontWeight:'700',background:c.bg,color:c.txt}}
const getVStatus=s=>({padding:'3px 10px',borderRadius:'20px',fontSize:'11px',fontWeight:'700',background:s==='Soldée'?'#dcfce7':s==='Partielle'?'#fef3c7':s==='Crédit'?'#fce7f3':'#fee2e2',color:s==='Soldée'?'#166534':s==='Partielle'?'#92400e':s==='Crédit'?'#9d174d':'#991b1b'})
const aIco={width:'28px',height:'28px',border:'1px solid #e2e8f0',borderRadius:'7px',background:'white',color:'#64748b',cursor:'pointer',display:'flex',alignItems:'center',justifyContent:'center'}
// Modal vente
const overlayStyle={position:'fixed',inset:0,background:'rgba(15,23,42,0.72)',backdropFilter:'blur(8px)',display:'flex',alignItems:'center',justifyContent:'center',zIndex:1000,padding:'20px',overflowY:'auto'}
const venteModalStyle={background:'white',borderRadius:'22px',width:'100%',maxWidth:'1000px',maxHeight:'94vh',overflow:'auto',boxShadow:'0 25px 50px rgba(0,0,0,0.25)'}
const venteModalHeader={display:'flex',justifyContent:'space-between',alignItems:'center',padding:'22px 28px 18px',borderBottom:'1px solid #f1f5f9'}
const modalIconStyle={width:'40px',height:'40px',background:'linear-gradient(135deg,#0ea5e9,#0284c7)',borderRadius:'11px',display:'flex',alignItems:'center',justifyContent:'center',color:'white',flexShrink:0}
const modalTitleStyle={fontSize:'20px',fontWeight:'800',color:'#0f172a',margin:'0 0 3px',letterSpacing:'-0.02em'}
const modalSubStyle={fontSize:'12px',color:'#64748b',margin:0,fontWeight:'500'}
const closeBtnStyle={background:'none',border:'none',cursor:'pointer',color:'#94a3b8',padding:'8px',borderRadius:'8px'}
const venteModalBody={padding:'20px 28px 26px'}
const vmSec={marginBottom:'16px'}
const vmLabel={fontSize:'11px',fontWeight:'800',color:'#475569',textTransform:'uppercase',letterSpacing:'0.06em',display:'block',marginBottom:'8px'}
const vmInput={width:'100%',padding:'10px 13px',border:'1px solid #e2e8f0',borderRadius:'9px',fontSize:'13px',fontWeight:'500',color:'#1e293b',boxSizing:'border-box',outline:'none',background:'white'}
const addLigneBtnStyle={display:'flex',alignItems:'center',gap:'5px',padding:'6px 12px',background:'#f0f9ff',border:'1px solid #bae6fd',borderRadius:'7px',fontSize:'12px',fontWeight:'700',color:'#0284c7',cursor:'pointer'}
const ligneColHeader={display:'flex',gap:'8px',alignItems:'center',padding:'6px 8px',marginBottom:'4px',borderBottom:'1px solid #f1f5f9',fontSize:'11px',fontWeight:'700',color:'#94a3b8',textTransform:'uppercase',letterSpacing:'0.05em'}
const ligneRowStyle={display:'flex',gap:'8px',alignItems:'center',marginBottom:'8px',padding:'8px',background:'#f8fafc',borderRadius:'9px',border:'1px solid #f1f5f9'}
const removeLigneBtnStyle={width:'28px',height:'28px',flexShrink:0,border:'1px solid #fecaca',background:'#fef2f2',borderRadius:'7px',color:'#ef4444',cursor:'pointer',display:'flex',alignItems:'center',justifyContent:'center'}
const perteAlertStyle={display:'flex',alignItems:'flex-start',gap:'8px',padding:'9px 12px',background:'#fef2f2',border:'1px solid #fca5a5',borderRadius:'9px',fontSize:'12px',color:'#ef4444',fontWeight:'600',marginTop:'6px'}
const recapBox={background:'#f8fafc',borderRadius:'12px',padding:'16px',border:'1px solid #f1f5f9'}
const recapTitle={fontSize:'11px',fontWeight:'800',color:'#94a3b8',textTransform:'uppercase',letterSpacing:'0.06em',marginBottom:'12px'}
const reglGrid={display:'grid',gridTemplateColumns:'repeat(3,1fr)',gap:'6px',marginBottom:'10px'}
const getReglBtn=(k,ctx)=>{const active=ctx==='vente'?venteForm.reglement===k:caisseReglement.value===k;return{display:'flex',flexDirection:'column',alignItems:'center',gap:'3px',padding:'8px 4px',borderRadius:'9px',border:`1px solid ${active?'#0ea5e9':'#e2e8f0'}`,background:active?'#f0f9ff':'white',color:active?'#0284c7':'#475569',fontSize:'11px',fontWeight:'700',cursor:'pointer',transition:'all 0.15s'}}
const sousOptBox={padding:'10px 12px',background:'#fffbeb',border:'1px solid #fde68a',borderRadius:'9px',marginTop:'6px'}
const resteInfo={fontSize:'12px',color:'#f59e0b',fontWeight:'700',marginTop:'6px'}
const paiTabs={display:'flex',gap:'6px',flexWrap:'wrap',marginBottom:'8px'}
const getPaiTab=(k,ctx)=>{const active=ctx==='vente'?venteForm.paiement===k:caissePaiement.value===k;return{display:'flex',alignItems:'center',gap:'5px',padding:'7px 12px',borderRadius:'8px',border:`1px solid ${active?'#bae6fd':'#e2e8f0'}`,background:active?'#e0f2fe':'white',color:active?'#0284c7':'#64748b',fontSize:'12px',fontWeight:'700',cursor:'pointer',transition:'all 0.15s'}}
const mmGrid={display:'grid',gridTemplateColumns:'repeat(5,1fr)',gap:'6px',marginTop:'8px',marginBottom:'4px'}
const getMMBtn=(k,ctx)=>{const active=ctx==='vente'?venteForm.mm_op===k:caisseMMOp.value===k;return{display:'flex',flexDirection:'column',alignItems:'center',gap:'4px',padding:'8px 4px',borderRadius:'9px',border:`1px solid ${active?'#a5b4fc':'#e2e8f0'}`,background:active?'#eef2ff':'white',cursor:'pointer',transition:'all 0.15s'}}
const vmActions={display:'flex',gap:'10px',marginTop:'22px',paddingTop:'18px',borderTop:'1px solid #f1f5f9'}
const vmCancel={flex:1,padding:'13px',background:'#f8fafc',border:'1px solid #e2e8f0',borderRadius:'10px',fontSize:'14px',fontWeight:'700',color:'#64748b',cursor:'pointer'}
const vmSubmit={flex:2,padding:'13px',background:'linear-gradient(135deg,#0ea5e9,#0284c7)',border:'none',borderRadius:'10px',fontSize:'14px',fontWeight:'700',color:'white',cursor:'pointer',display:'flex',alignItems:'center',justifyContent:'center',gap:'8px',boxShadow:'0 4px 14px rgba(14,165,233,0.3)'}
const spinSm={width:'16px',height:'16px',border:'2px solid rgba(255,255,255,0.3)',borderTop:'2px solid white',borderRadius:'50%',animation:'detail-spin 0.8s linear infinite'}
// Caisse fond clair
const caisseOverlay={position:'fixed',inset:0,background:'#f1f5f9',zIndex:2000,display:'flex',flexDirection:'column'}
const caisseTopbar={display:'flex',alignItems:'center',gap:'16px',padding:'12px 20px',background:'white',borderBottom:'2px solid #e2e8f0',flexShrink:0,boxShadow:'0 2px 8px rgba(0,0,0,0.06)'}
const caisseTopLeft={display:'flex',alignItems:'center',gap:'14px',minWidth:'220px'}
const caisseLogo={width:'42px',height:'42px',background:'linear-gradient(135deg,#0ea5e9,#0284c7)',borderRadius:'12px',display:'flex',alignItems:'center',justifyContent:'center',color:'white',flexShrink:0}
const caisseNom={fontSize:'15px',fontWeight:'800',color:'#0f172a'}
const caisseSub={fontSize:'11px',color:'#94a3b8',fontWeight:'500'}
const caisseSearchInput={width:'100%',padding:'11px 16px 11px 44px',background:'#f8fafc',border:'1.5px solid #e2e8f0',borderRadius:'12px',fontSize:'14px',fontWeight:'500',color:'#0f172a',outline:'none'}
const getCatBtn=cat=>({padding:'7px 14px',borderRadius:'8px',border:`1.5px solid ${activeCat.value===cat?'#0ea5e9':'#e2e8f0'}`,background:activeCat.value===cat?'#0ea5e9':'white',color:activeCat.value===cat?'white':'#64748b',fontSize:'12px',fontWeight:'700',cursor:'pointer',whiteSpace:'nowrap',transition:'all 0.2s'})
const caisseCloseBtn={display:'flex',alignItems:'center',gap:'7px',padding:'9px 16px',background:'#f1f5f9',border:'1px solid #e2e8f0',borderRadius:'9px',color:'#475569',fontSize:'13px',fontWeight:'700',cursor:'pointer'}
const caisseBody={display:'flex',flex:1,overflow:'hidden'}
const caisseProdWrap={flex:1,overflow:'auto',padding:'18px'}
const caisseProdGrid={display:'grid',gridTemplateColumns:'repeat(auto-fill,minmax(155px,1fr))',gap:'10px'}
const getCaisseCard=prod=>({background:'white',borderRadius:'14px',padding:'14px',cursor:prod.stock===0?'not-allowed':'pointer',border:`1.5px solid ${hovProd.value===prod.id&&prod.stock>0?'#0ea5e9':'#e2e8f0'}`,transition:'all 0.2s',position:'relative',overflow:'hidden',transform:hovProd.value===prod.id&&prod.stock>0?'translateY(-3px)':'none',boxShadow:hovProd.value===prod.id&&prod.stock>0?'0 6px 20px rgba(14,165,233,0.15)':'0 1px 4px rgba(0,0,0,0.05)',opacity:prod.stock===0?0.5:1})
const lowStockBadge={position:'absolute',top:'7px',right:'7px',padding:'2px 7px',background:'#f59e0b',borderRadius:'20px',fontSize:'9px',fontWeight:'800',color:'white',textTransform:'uppercase'}
const ruptureBadge={position:'absolute',top:'7px',right:'7px',padding:'2px 7px',background:'#ef4444',borderRadius:'20px',fontSize:'9px',fontWeight:'800',color:'white',textTransform:'uppercase'}
const prodEmojiStyle={fontSize:'30px',marginBottom:'8px',display:'block'}
const prodNomStyle={fontSize:'13px',fontWeight:'700',color:'#0f172a',marginBottom:'2px',lineHeight:'1.3'}
const prodCatStyle={fontSize:'10px',color:'#94a3b8',fontWeight:'600',textTransform:'uppercase',letterSpacing:'0.05em',marginBottom:'8px'}
const prodFoot={display:'flex',justifyContent:'space-between',alignItems:'center'}
const prodPrixStyle={fontSize:'13px',fontWeight:'800',color:'#0ea5e9'}
const prodStockStyle=s=>({fontSize:'11px',fontWeight:'700',color:s<=5?'#ef4444':s<=20?'#f59e0b':'#10b981'})
const cartBadge={position:'absolute',top:'-5px',right:'-5px',width:'20px',height:'20px',background:'#0ea5e9',borderRadius:'50%',display:'flex',alignItems:'center',justifyContent:'center',fontSize:'11px',fontWeight:'800',color:'white',boxShadow:'0 2px 6px rgba(14,165,233,0.4)'}
const caissePanier={width:'390px',background:'white',borderLeft:'2px solid #e2e8f0',display:'flex',flexDirection:'column',flexShrink:0,boxShadow:'-4px 0 16px rgba(0,0,0,0.04)'}
const panierHeader={display:'flex',justifyContent:'space-between',alignItems:'center',padding:'16px 18px 12px',borderBottom:'1px solid #f1f5f9'}
const panierTitle={fontSize:'15px',fontWeight:'800',color:'#0f172a',display:'flex',alignItems:'center',gap:'8px',margin:0}
const panierCount={fontSize:'12px',color:'#64748b',fontWeight:'600',background:'#f1f5f9',padding:'4px 10px',borderRadius:'20px'}
const viderBtn={width:'28px',height:'28px',border:'1px solid #fecaca',background:'#fef2f2',borderRadius:'7px',color:'#ef4444',cursor:'pointer',display:'flex',alignItems:'center',justifyContent:'center'}
const panierClientRow={display:'flex',alignItems:'center',gap:'8px',padding:'10px 16px',borderBottom:'1px solid #f1f5f9',background:'#fafbfc'}
const panierClientSelect={flex:1,background:'transparent',border:'none',fontSize:'13px',fontWeight:'500',color:'#475569',outline:'none',cursor:'pointer'}
const panierItems={flex:1,overflow:'auto',padding:'8px'}
const panierEmpty={textAlign:'center',padding:'36px 20px'}
const panierItem=i=>({display:'flex',alignItems:'center',gap:'8px',padding:'10px',borderRadius:'10px',background:i%2===0?'#f8fafc':'white',marginBottom:'4px',border:'1px solid #f1f5f9'})
const remiseProdInput={width:'52px',padding:'3px 6px',border:'1px solid #e2e8f0',borderRadius:'5px',fontSize:'11px',fontWeight:'600',color:'#0f172a',outline:'none',textAlign:'center',background:'white'}
const panierQteCtrl={display:'flex',alignItems:'center',gap:'4px',background:'#f1f5f9',borderRadius:'8px',padding:'3px',flexShrink:0}
const qteBtn={width:'22px',height:'22px',border:'none',background:'white',borderRadius:'5px',color:'#0f172a',cursor:'pointer',display:'flex',alignItems:'center',justifyContent:'center',fontSize:'14px',fontWeight:'700',boxShadow:'0 1px 2px rgba(0,0,0,0.06)'}
const qteVal={fontSize:'13px',fontWeight:'800',color:'#0f172a',minWidth:'18px',textAlign:'center'}
const removeItemBtn={width:'22px',height:'22px',border:'1px solid #fecaca',background:'white',borderRadius:'5px',color:'#ef4444',cursor:'pointer',display:'flex',alignItems:'center',justifyContent:'center',flexShrink:0}
const panierFoot={padding:'12px 16px 16px',borderTop:'2px solid #f1f5f9'}
const remiseInput={background:'transparent',border:'none',fontSize:'13px',fontWeight:'700',color:'#0f172a',outline:'none',width:'70px',textAlign:'right'}
const totalBox={background:'#f8fafc',borderRadius:'10px',padding:'12px',marginBottom:'10px',border:'1px solid #f1f5f9'}
const subRow={display:'flex',justifyContent:'space-between',fontSize:'13px',color:'#64748b',fontWeight:'600',marginBottom:'5px'}
const discRow={display:'flex',justifyContent:'space-between',fontSize:'12px',fontWeight:'600',marginBottom:'5px'}
const totalRow={display:'flex',justifyContent:'space-between',fontSize:'16px',fontWeight:'800',color:'#0f172a',paddingTop:'8px',borderTop:'1.5px solid #e2e8f0'}
const getCaisseMMBtn=k=>{const active=caisseMMOp.value===k;return{display:'flex',flexDirection:'column',alignItems:'center',gap:'3px',padding:'7px 3px',borderRadius:'8px',border:`1.5px solid ${active?'#a5b4fc':'#e2e8f0'}`,background:active?'#eef2ff':'white',cursor:'pointer',transition:'all 0.15s'}}
const encaisserBtn=computed(()=>({width:'100%',padding:'14px',background:cartItems.value.length>0?'linear-gradient(135deg,#0ea5e9,#0284c7)':'#e2e8f0',border:'none',borderRadius:'12px',fontSize:'14px',fontWeight:'800',color:cartItems.value.length>0?'white':'#94a3b8',cursor:cartItems.value.length>0?'pointer':'not-allowed',display:'flex',alignItems:'center',justifyContent:'center',gap:'8px',boxShadow:cartItems.value.length>0?'0 6px 20px rgba(14,165,233,0.3)':'none',transition:'all 0.2s',marginTop:'6px'}))
const spinWhite={width:'18px',height:'18px',border:'2px solid rgba(255,255,255,0.3)',borderTop:'2px solid white',borderRadius:'50%',animation:'detail-spin 0.8s linear infinite'}
const toastStyle={position:'fixed',bottom:'28px',left:'50%',transform:'translateX(-50%)',background:'#10b981',color:'white',padding:'13px 22px',borderRadius:'12px',display:'flex',alignItems:'center',gap:'10px',fontSize:'14px',fontWeight:'700',boxShadow:'0 8px 24px rgba(16,185,129,0.4)',zIndex:9999}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,700;0,9..40,800&display=swap');
* { font-family: 'DM Sans', -apple-system, sans-serif; }
@keyframes detail-fadein { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:translateY(0); } }
@keyframes detail-spin { to { transform: rotate(360deg); } }
.detail-fade { animation: detail-fadein 0.45s cubic-bezier(0.16,1,0.3,1) backwards; }
.slide-modal-enter-active, .slide-modal-leave-active { transition: all 0.3s cubic-bezier(0.16,1,0.3,1); }
.slide-modal-enter-from, .slide-modal-leave-to { opacity: 0; }
.slide-modal-enter-from .vente-modal-panel { transform: scale(0.96) translateY(14px); }
.vente-modal-panel { transition: all 0.3s cubic-bezier(0.16,1,0.3,1); }
.caisse-open-enter-active, .caisse-open-leave-active { transition: all 0.3s cubic-bezier(0.16,1,0.3,1); }
.caisse-open-enter-from, .caisse-open-leave-to { opacity: 0; transform: scale(0.99); }
.toast-enter-active, .toast-leave-active { transition: all 0.35s cubic-bezier(0.16,1,0.3,1); }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateX(-50%) translateY(12px); }
.prod-card::after { content:''; position:absolute; inset:0; background:linear-gradient(135deg,rgba(14,165,233,0.05),transparent); opacity:0; transition:opacity 0.2s; border-radius:14px; pointer-events:none; }
.prod-card:hover::after { opacity:1; }
.caisse-screen ::-webkit-scrollbar { width:5px; height:5px; }
.caisse-screen ::-webkit-scrollbar-track { background:#f1f5f9; }
.caisse-screen ::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:4px; }
</style>