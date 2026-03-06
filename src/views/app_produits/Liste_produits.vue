<template>
  <SidebarLayout currentPage="produits">

    <!-- HEADER -->
    <header :style="headerStyle" class="p-fade">
      <div>
        <div :style="breadcrumbStyle">
          <span :style="bcItem">Tableau de bord</span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
          <span :style="bcActive">Produits &amp; Stock</span>
        </div>
        <h1 :style="titleStyle">Produits &amp; Stock</h1>
        <p :style="subtitleStyle">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
          {{ filteredProduits.length }} produit(s) &middot; {{ categories.length }} categorie(s)
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
        <button :style="secBtnStyle" @click="openCategorieModal()">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20h16a2 2 0 002-2V8a2 2 0 00-2-2h-7.93a2 2 0 01-1.66-.9l-.82-1.2A2 2 0 007.93 3H4a2 2 0 00-2 2v13c0 1.1.9 2 2 2z"/></svg>
          Categories
        </button>
        <button :style="primaryBtnStyle" @click="openProduitModal(null)">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
          Nouveau Produit
        </button>
      </div>
    </header>

    <!-- STATS -->
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

    <!-- BARRE FILTRES -->
    <div :style="filterBarStyle" class="p-fade" style="animation-delay:.1s">
      <div style="position:relative;flex:1;min-width:200px">
        <svg style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#94a3b8;pointer-events:none" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        <input v-model="search" :style="searchInputStyle" placeholder="Rechercher par nom, reference, marque..."/>
      </div>
      <select v-model="filterCat" :style="filterSelectStyle">
        <option value="">Toutes categories</option>
        <option v-for="c in categoriesForSelect" :key="c.id" :value="c.id">{{ c.libelle }}</option>
      </select>
      <select v-model="filterStatut" :style="filterSelectStyle">
        <option value="">Tous les statuts</option>
        <option value="actif">Actif</option>
        <option value="inactif">Inactif</option>
        <option value="archive">Archive</option>
      </select>
      <button v-if="search || filterCat || filterStatut" :style="clearBtnStyle" @click="search='';filterCat='';filterStatut=''">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
        Effacer
      </button>
    </div>

    <!-- LOADING -->
    <div v-if="loadingProduits" style="text-align:center;padding:60px">
      <div :style="spinLg"></div>
      <p style="margin-top:12px;color:#94a3b8;font-size:14px;font-weight:600">Chargement des produits...</p>
    </div>

    <!-- ERROR -->
    <div v-else-if="errorProduits" :style="errorBox">
      <p style="font-weight:700;color:#ef4444;margin:0 0 6px">Erreur de chargement</p>
      <p style="font-size:13px;color:#64748b;margin:0 0 12px">{{ errorProduits }}</p>
      <button :style="primaryBtnStyle" @click="loadProduits">Reessayer</button>
    </div>

    <template v-else>
      <!-- VUE GRILLE PRODUITS -->
      <div v-if="activeView === 'grid'" :style="prodGridStyle" class="p-fade" style="animation-delay:.14s">
        <div v-if="!filteredProduits.length" :style="emptyStateStyle">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg>
          <p style="font-size:16px;font-weight:700;color:#475569;margin:12px 0 4px">Aucun produit trouve</p>
          <p style="font-size:13px;color:#94a3b8">Modifiez votre recherche ou creez un nouveau produit</p>
        </div>
        <div
          v-for="(p, i) in filteredProduits"
          :key="p.id"
          :style="getProdCard(i)"
          @mouseenter="hovCard = i"
          @mouseleave="hovCard = null"
          class="prod-item"
        >
          <div :style="prodAccent(p)"></div>
          <div :style="prodCardActions">
            <button :style="pca('edit')" @click.stop="openProduitModal(p)" title="Modifier">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </button>
            <button :style="pca('delete')" @click.stop="deleteProduit(p.id)" title="Supprimer">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
            </button>
          </div>
          <div style="text-align:center;padding:14px 12px 6px">
            <div v-if="p.image" style="width:60px;height:60px;margin:0 auto;border-radius:10px;overflow:hidden;background:#f1f5f9">
              <img :src="p.image" style="width:100%;height:100%;object-fit:cover" alt=""/>
            </div>
            <span v-else style="font-size:38px">&#128230;</span>
          </div>
          <div style="padding:0 14px 6px;text-align:center">
            <div style="font-size:13px;font-weight:800;color:#0f172a;line-height:1.3;margin-bottom:3px">{{ p.libelle }}</div>
            <div style="font-size:10px;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.05em">{{ p.sku || p.reference || '---' }} &middot; {{ p.categorie_nom || 'Sans categorie' }}</div>
          </div>
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
          <div style="padding:8px 14px 14px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
              <span style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.04em">Stock init</span>
              <span :style="stockBadge(p)">{{ p.quantite_init }} {{ p.unite_mesure || 'unites' }}</span>
            </div>
            <div v-if="p.seuil_alerte > 0" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
              <span style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.04em">Seuil alerte</span>
              <span :style="seuilBadge(p)">{{ p.seuil_alerte }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center">
              <span :style="statusPillSmall(p)">{{ p.statut }}</span>
              <span v-if="p.marque" style="font-size:10px;color:#94a3b8">{{ p.marque }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- VUE LISTE PRODUITS -->
      <div v-if="activeView === 'list'" :style="tableSection" class="p-fade" style="animation-delay:.14s">
        <div :style="tableWrap">
          <table :style="tableStyle">
            <thead>
              <tr :style="theadTr">
                <th :style="th">Produit</th>
                <th :style="th">Reference</th>
                <th :style="th">Categorie</th>
                <th :style="th">Prix vente</th>
                <th :style="th">Prix achat</th>
                <th :style="th">Marge</th>
                <th :style="th">Qte init</th>
                <th :style="th">Seuil alerte</th>
                <th :style="th">Unite</th>
                <th :style="th">Statut</th>
                <th :style="th">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="!filteredProduits.length">
                <td colspan="11" :style="emptyTd">Aucun produit trouve</td>
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
                    <div v-if="p.image" style="width:32px;height:32px;border-radius:6px;overflow:hidden;background:#f1f5f9;flex-shrink:0">
                      <img :src="p.image" style="width:100%;height:100%;object-fit:cover" alt=""/>
                    </div>
                    <span v-else style="font-size:22px">&#128230;</span>
                    <div>
                      <div style="font-size:13px;font-weight:700;color:#0f172a">{{ p.libelle }}</div>
                      <div style="font-size:11px;color:#94a3b8">{{ p.marque || '' }}</div>
                    </div>
                  </div>
                </td>
                <td :style="td"><span style="font-family:monospace;font-size:12px;color:#64748b;font-weight:600;background:#f1f5f9;padding:3px 8px;border-radius:5px">{{ p.sku || p.reference || '---' }}</span></td>
                <td :style="td"><span :style="catPill(p.categorie_nom)">{{ p.categorie_nom || 'Sans cat.' }}</span></td>
                <td :style="td"><span style="font-size:13px;font-weight:800;color:#0ea5e9">{{ formatAmount(p.prix_vente) }}</span></td>
                <td :style="td"><span style="font-size:13px;font-weight:600;color:#64748b">{{ formatAmount(p.prix_achat) }}</span></td>
                <td :style="td">
                  <span :style="{ fontSize:'12px', fontWeight:'700', color: (p.prix_vente - p.prix_achat) > 0 ? '#10b981' : '#ef4444' }">
                    {{ formatAmount(p.prix_vente - p.prix_achat) }}
                    <span style="font-weight:500;opacity:.8">({{ p.prix_achat > 0 ? Math.round((p.prix_vente - p.prix_achat) / p.prix_achat * 100) : 0 }}%)</span>
                  </span>
                </td>
                <td :style="td"><span style="font-size:13px;font-weight:700;color:#0f172a">{{ p.quantite_init }}</span></td>
                <td :style="td">
                  <span v-if="p.seuil_alerte > 0" :style="{ fontSize:'13px', fontWeight:'700', color: p.quantite_init <= p.seuil_alerte ? '#ef4444' : '#f59e0b' }">
                    {{ p.seuil_alerte }}
                    <span v-if="p.quantite_init <= p.seuil_alerte" style="font-size:10px;margin-left:2px">&#9888;</span>
                  </span>
                  <span v-else style="font-size:12px;color:#cbd5e1">---</span>
                </td>
                <td :style="td"><span style="font-size:12px;color:#94a3b8">{{ p.unite_mesure || 'unite' }}</span></td>
                <td :style="td"><span :style="statusPill(p)">{{ p.statut }}</span></td>
                <td :style="td">
                  <div style="display:flex;gap:5px">
                    <button :style="aBtn('#3b82f6')" @click="openProduitModal(p)" title="Modifier">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button :style="aBtn('#ef4444')" @click="deleteProduit(p.id)" title="Supprimer">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- VUE CATEGORIES -->
      <div v-if="activeView === 'categories'" class="p-fade" style="animation-delay:.14s">
        <!-- Barre d'actions : catégories par défaut + ajouter une catégorie -->
        <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;margin-bottom:20px;padding:16px;background:#f8fafc;border-radius:12px;border:1px solid #e2e8f0">
          <span style="font-size:14px;font-weight:700;color:#475569">Catégories</span>
          <button :style="secBtnStyle" @click="openCategorieModal()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
            Ajouter une catégorie
          </button>
          <button :style="defaultCatsBtnStyle" :disabled="seedingDefaults" @click="addDefaultCategories">
            <span v-if="seedingDefaults" :style="spinSm"></span>
            <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20h16a2 2 0 002-2V8a2 2 0 00-2-2h-7.93a2 2 0 01-1.66-.9l-.82-1.2A2 2 0 007.93 3H4a2 2 0 00-2 2v13c0 1.1.9 2 2 2z"/></svg>
            {{ seedingDefaults ? 'Ajout...' : 'Utiliser les catégories par défaut' }}
          </button>
        </div>
        <div :style="catGridStyle">
          <div v-if="!categories.length" :style="{ ...emptyStateStyle, gridColumn: '1/-1' }">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5"><path d="M4 20h16a2 2 0 002-2V8a2 2 0 00-2-2h-7.93a2 2 0 01-1.66-.9l-.82-1.2A2 2 0 007.93 3H4a2 2 0 00-2 2v13c0 1.1.9 2 2 2z"/></svg>
            <p style="font-size:16px;font-weight:700;color:#475569;margin:12px 0 8px">Aucune categorie creee</p>
            <p style="font-size:13px;color:#94a3b8;margin:0 0 14px">Cliquez sur &laquo; Utiliser les catégories par défaut &raquo; ou &laquo; Ajouter une catégorie &raquo;</p>
            <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:center">
              <button :style="primaryBtnStyle" @click="addDefaultCategories" :disabled="seedingDefaults">Utiliser les catégories par défaut</button>
              <button :style="primaryBtnStyle" @click="openCategorieModal()">Creer une categorie</button>
            </div>
          </div>

          <div v-for="(c, i) in categories" :key="c.id ?? c.id_categorie" :style="getCatCard(i)" @mouseenter="hovCat = i" @mouseleave="hovCat = null">
            <div :style="{ height:'3px', background: CARD_COLORS[i % CARD_COLORS.length] }"></div>
            <div style="padding:20px">
              <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px">
                <div :style="catIconStyle(i)">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20h16a2 2 0 002-2V8a2 2 0 00-2-2h-7.93a2 2 0 01-1.66-.9l-.82-1.2A2 2 0 007.93 3H4a2 2 0 00-2 2v13c0 1.1.9 2 2 2z"/></svg>
                </div>
                <div style="display:flex;gap:6px">
                  <button :style="iconBtn('#3b82f6')" @click="openEditCategorieModal(c)" title="Modifier">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  </button>
                  <button :style="iconBtn('#ef4444')" @click="deleteCategorie(c.id ?? c.id_categorie)" title="Supprimer">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6"/></svg>
                  </button>
                </div>
              </div>
              <h3 style="font-size:17px;font-weight:800;color:#0f172a;margin:0 0 6px;letter-spacing:-0.02em">{{ c.libelle }}</h3>
              <p style="font-size:12px;color:#94a3b8;margin:0 0 14px">{{ getSubCatsForCat(c.id ?? c.id_categorie).length }} sous-categorie(s) &middot; {{ getProduitsCountForCat(c.id ?? c.id_categorie) }} produit(s)</p>

              <div style="background:#f8fafc;border-radius:10px;padding:12px;border:1px solid #f1f5f9">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                  <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em">Sous-categories</div>
                  <button :style="addSubCatBtn" @click="openSousCategorieModal(c.id ?? c.id_categorie)">+ Ajouter</button>
                </div>
                <div v-if="!getSubCatsForCat(c.id ?? c.id_categorie).length" style="font-size:12px;color:#cbd5e1;font-weight:500">Aucune sous-categorie</div>
                <div v-for="sc in getSubCatsForCat(c.id ?? c.id_categorie)" :key="sc.id" style="display:flex;justify-content:space-between;align-items:center;font-size:12px;margin-bottom:5px;padding:5px 8px;background:white;border-radius:6px;border:1px solid #f1f5f9">
                  <span style="font-weight:600;color:#475569">{{ sc.libelle }}</span>
                  <div style="display:flex;gap:4px;align-items:center">
                    <span v-if="sc.rayon" style="font-size:10px;color:#94a3b8">R:{{ sc.rayon }}</span>
                    <button style="background:none;border:none;cursor:pointer;color:#ef4444;font-size:14px;padding:2px" @click="deleteSousCategorie(sc.id)" title="Supprimer">&times;</button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div :style="addCatCard" @click="openCategorieModal()">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5"><path d="M12 5v14M5 12h14"/></svg>
            <span style="font-size:13px;font-weight:700;color:#94a3b8;margin-top:8px">Nouvelle categorie</span>
          </div>
        </div>
      </div>
    </template>

    <!-- MODAL CREER / MODIFIER PRODUIT -->
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
                <p :style="modalSub">{{ editingProduit ? 'Mettez a jour les informations' : 'Enregistrez un nouveau produit dans le catalogue' }}</p>
              </div>
            </div>
            <button :style="closeBtnStyle" @click="showProduitModal = false">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
          </div>
          <div :style="modalBody">
            <!-- Identite -->
            <div :style="formSec">
              <div :style="secTitle('#0ea5e9')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/></svg>
                Identite du produit
              </div>
              <div :style="formGrid">
                <!-- Ligne 1 : Libelle + SKU -->
                <div :style="fGroup">
                  <label :style="fLabel">Libelle <span style="color:#ef4444">*</span></label>
                  <input v-model="produitForm.libelle" :style="fInput" placeholder="Ex: Riz Parfume 5kg"/>
                </div>
                <div :style="fGroup">
                  <label :style="fLabel">SKU (auto-genere a partir du libelle)</label>
                  <input v-model="produitForm.sku" :style="{ ...fInput, backgroundColor: editingProduit ? undefined : '#f8fafc', color: editingProduit ? undefined : '#64748b' }" :readonly="!editingProduit" placeholder="Ex: RI512345"/>
                </div>
                <!-- Ligne 2 : Reference + QR Code -->
                <div :style="fGroup">
                  <label :style="fLabel">Reference</label>
                  <input v-model="produitForm.reference" :style="fInput" placeholder="REF-001"/>
                </div>
                <div :style="fGroup">
                  <label :style="fLabel">QR Code</label>
                  <input v-model="produitForm.qr_code" :style="fInput" placeholder="QR-..."/>
                </div>
                <!-- Ligne 3 : Catégorie + Sous-catégorie -->
                <div :style="fGroup">
                  <label :style="fLabel">Catégorie</label>
                  <select v-model="produitForm.id_categorie" :style="fInput" @change="onCategorieSelectChange">
                    <option :value="null">Aucune</option>
                    <option v-for="c in categoriesForSelect" :key="c.id" :value="c.id">{{ c.libelle }}</option>
                  </select>
                </div>
                <div :style="fGroup">
                  <label :style="fLabel">Sous-catégorie</label>
                  <select v-model="produitForm.id_sous_categorie" :style="fInput" :disabled="!produitForm.id_categorie">
                    <option :value="null">Aucune</option>
                    <option v-for="sc in filteredSousCategories" :key="sc.id_sous_categorie" :value="sc.id_sous_categorie">{{ sc.libelle }}</option>
                  </select>
                </div>
                <!-- Ligne 4 : Unite de mesure + Marque -->
                <div :style="fGroup">
                  <label :style="fLabel">Unite de mesure</label>
                  <select v-model="produitForm.unite_mesure" :style="fInput">
                    <option v-for="u in UNITES" :key="u" :value="u">{{ u }}</option>
                  </select>
                </div>
                <div :style="fGroup">
                  <label :style="fLabel">Marque</label>
                  <input v-model="produitForm.marque" :style="fInput" placeholder="Marque"/>
                </div>
                <!-- Ligne 5 : Fournisseur + Image -->
                <div :style="fGroup">
                  <label :style="fLabel">Fournisseur</label>
                  <select v-model="produitForm.id_fournisseur" :style="fInput">
                    <option :value="null">Aucun</option>
                    <option v-for="f in fournisseurs" :key="f.id" :value="f.id">{{ f.nom }}</option>
                  </select>
                </div>
                <div :style="fGroup">
                  <label :style="fLabel">Image du produit</label>
                  <div :style="imageUploadZone" @click="$refs.imageInput.click()">
                    <input ref="imageInput" type="file" accept="image/*" style="display:none" @change="onImageSelect"/>
                    <div v-if="imagePreview || produitForm.image" style="position:relative;width:100%">
                      <img :src="imagePreview || produitForm.image" style="width:100%;max-height:100px;object-fit:contain;border-radius:6px"/>
                      <button type="button" @click.stop="removeImage" :style="removeImgBtn">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
                      </button>
                    </div>
                    <div v-else style="text-align:center">
                      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin:0 auto 6px"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                      <div style="font-size:11px;color:#94a3b8;font-weight:600">Cliquer pour choisir une image</div>
                      <div style="font-size:10px;color:#cbd5e1;margin-top:2px">JPG, PNG, WebP (max 5MB)</div>
                    </div>
                    <div v-if="uploadingImage" :style="uploadOverlay">
                      <div :style="spinSm"></div>
                      <span style="font-size:11px;color:#0ea5e9;font-weight:700;margin-top:4px">Upload...</span>
                    </div>
                  </div>
                </div>
                <!-- Description (pleine largeur) -->
                <div :style="fGroup" style="grid-column:1/-1">
                  <label :style="fLabel">Description</label>
                  <textarea v-model="produitForm.description" :style="{ ...fInput, height:'66px', resize:'vertical' }" placeholder="Description detaillee du produit..."></textarea>
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
                  Marge : {{ formatAmount(produitForm.prix_vente - produitForm.prix_achat) }} ({{ Math.round((produitForm.prix_vente - produitForm.prix_achat) / produitForm.prix_achat * 100) }}%)
                </span>
                <span v-else style="color:#ef4444;font-weight:800">PRIX DE VENTE INFERIEUR AU PRIX D'ACHAT</span>
              </div>
            </div>

            <!-- Stock & Autres -->
            <div :style="formSec">
              <div :style="secTitle('#f59e0b')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="15" rx="2"/><path d="M16 3H8a1 1 0 00-1 1v3h10V4a1 1 0 00-1-1z"/></svg>
                Stock &amp; Autres
              </div>
              <div :style="formGrid">
                <div :style="fGroup">
                  <label :style="fLabel">Quantite initiale</label>
                  <input v-model.number="produitForm.quantite_init" type="number" min="0" :style="fInput" placeholder="0"/>
                </div>
                <div :style="fGroup">
                  <label :style="fLabel">Seuil d'alerte</label>
                  <input v-model.number="produitForm.seuil_alerte" type="number" min="0" :style="fInput" placeholder="0"/>
                  <div style="font-size:10px;color:#94a3b8;margin-top:4px;font-weight:500">Alerte si le stock descend sous ce seuil</div>
                </div>
                <div :style="fGroup">
                  <label :style="fLabel">Entrepot</label>
                  <select v-model="produitForm.entrepot" :style="fInput">
                    <option value="">Aucun entrepot</option>
                    <option v-for="e in entrepots" :key="e.id" :value="e.nom">{{ e.nom }}{{ e.code ? ' (' + e.code + ')' : '' }}</option>
                  </select>
                </div>
                <div :style="fGroup">
                  <label :style="fLabel">Peremption</label>
                  <input v-model="produitForm.peremption" type="date" :style="fInput"/>
                </div>
                <div :style="fGroup">
                  <label :style="fLabel">Statut</label>
                  <select v-model="produitForm.statut" :style="fInput">
                    <option value="actif">Actif</option>
                    <option value="inactif">Inactif</option>
                    <option value="archive">Archive</option>
                  </select>
                </div>
              </div>
            </div>

            <div :style="modalActions">
              <button :style="mCancel" @click="showProduitModal = false">Annuler</button>
              <button :style="mSubmit('#0ea5e9')" @click="saveProduit" :disabled="savingProduit">
                <div v-if="savingProduit" :style="spinSm"></div>
                <svg v-else width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                {{ savingProduit ? 'Enregistrement...' : (editingProduit ? 'Modifier le produit' : 'Creer le produit') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- MODAL CATEGORIE -->
    <Transition name="slide-modal">
      <div v-if="showCategorieModal" :style="overlayStyle" @click.self="showCategorieModal = false">
        <div :style="smallModalStyle" class="modal-panel">
          <div :style="modalHead">
            <div style="display:flex;align-items:center;gap:14px">
              <div :style="modalBadge('#f59e0b')">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20h16a2 2 0 002-2V8a2 2 0 00-2-2h-7.93a2 2 0 01-1.66-.9l-.82-1.2A2 2 0 007.93 3H4a2 2 0 00-2 2v13c0 1.1.9 2 2 2z"/></svg>
              </div>
              <div>
                <h2 :style="modalTitle">{{ editingCategorie ? 'Modifier la Categorie' : 'Nouvelle Categorie' }}</h2>
                <p :style="modalSub">Gerez vos categories de produits</p>
              </div>
            </div>
            <button :style="closeBtnStyle" @click="showCategorieModal = false">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
          </div>
          <div :style="modalBody">
            <div :style="fGroup">
              <label :style="fLabel">Libelle de la categorie <span style="color:#ef4444">*</span></label>
              <input v-model="categorieForm.libelle" :style="fInput" placeholder="Ex: Alimentation"/>
            </div>
            <div :style="modalActions">
              <button :style="mCancel" @click="showCategorieModal = false">Annuler</button>
              <button :style="mSubmit('#f59e0b')" @click="saveCategorie" :disabled="savingCategorie">
                <div v-if="savingCategorie" :style="spinSm"></div>
                {{ savingCategorie ? 'Enregistrement...' : (editingCategorie ? 'Modifier' : 'Creer la categorie') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- MODAL SOUS-CATEGORIE -->
    <Transition name="slide-modal">
      <div v-if="showSousCategorieModal" :style="overlayStyle" @click.self="showSousCategorieModal = false">
        <div :style="smallModalStyle" class="modal-panel">
          <div :style="modalHead">
            <div style="display:flex;align-items:center;gap:14px">
              <div :style="modalBadge('#8b5cf6')">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20h16a2 2 0 002-2V8a2 2 0 00-2-2h-7.93a2 2 0 01-1.66-.9l-.82-1.2A2 2 0 007.93 3H4a2 2 0 00-2 2v13c0 1.1.9 2 2 2z"/></svg>
              </div>
              <div>
                <h2 :style="modalTitle">Nouvelle Sous-categorie</h2>
                <p :style="modalSub">Ajoutez une sous-categorie</p>
              </div>
            </div>
            <button :style="closeBtnStyle" @click="showSousCategorieModal = false">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
          </div>
          <div :style="modalBody">
            <div :style="formGrid">
              <div :style="fGroup" style="grid-column:1/-1">
                <label :style="fLabel">Libelle <span style="color:#ef4444">*</span></label>
                <input v-model="sousCategorieForm.libelle" :style="fInput" placeholder="Ex: Cereales"/>
              </div>
              <div :style="fGroup">
                <label :style="fLabel">Rayon</label>
                <input v-model="sousCategorieForm.rayon" :style="fInput" placeholder="A1"/>
              </div>
              <div :style="fGroup">
                <label :style="fLabel">Palier</label>
                <input v-model="sousCategorieForm.palier" :style="fInput" placeholder="P1"/>
              </div>
              <div :style="fGroup" style="grid-column:1/-1">
                <label :style="fLabel">Zone</label>
                <input v-model="sousCategorieForm.zone" :style="fInput" placeholder="Zone A"/>
              </div>
            </div>
            <div :style="modalActions">
              <button :style="mCancel" @click="showSousCategorieModal = false">Annuler</button>
              <button :style="mSubmit('#8b5cf6')" @click="saveSousCategorie" :disabled="savingSousCategorie">
                <div v-if="savingSousCategorie" :style="spinSm"></div>
                {{ savingSousCategorie ? 'Enregistrement...' : 'Creer la sous-categorie' }}
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
import { ref, reactive, computed, watch, onMounted } from 'vue'
import SidebarLayout from '../SidebarLayout.vue'
import { uploadToCloudinary } from '../../services/cloudinaryService.js'

// ---- Config : passer par le proxy Vite (/api → aliadjame.com) pour éviter CORS en dev ----
const API_BASE = '/api/api_produit.php'
const UNITES = ['unite', 'kg', 'g', 'litre', 'ml', 'carton', 'sachet', 'boite', 'piece', 'metre', 'sac']

// Liste de catégories par défaut (affichée dans le select si l’API n’en a pas encore)
const CARD_COLORS = ['#0ea5e9', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#ec4899', '#06b6d4', '#84cc16']

const getHeaders = () => ({
  'Content-Type': 'application/json',
  'Authorization': `Bearer ${localStorage.getItem('token') || ''}`
})

const parseJsonOrText = async (res) => {
  const text = await res.text()
  try { return JSON.parse(text) } catch { return { success: false, error: text } }
}

// ---- State ----
const activeView    = ref('grid')
const search        = ref('')
const filterCat     = ref('')
const filterStatut  = ref('')
const hovCard       = ref(null)
const hovRow        = ref(null)
const hovCat        = ref(null)
const showToast     = ref(false)
const toastMsg      = ref('')

const showProduitModal        = ref(false)
const showCategorieModal      = ref(false)
const showSousCategorieModal  = ref(false)

const editingProduit    = ref(null)
const editingCategorie  = ref(null)

const savingProduit         = ref(false)
const savingCategorie       = ref(false)
const savingSousCategorie   = ref(false)
const seedingDefaults       = ref(false)

const produits          = ref([])
const categories        = ref([])
const sousCategories    = ref([])
const fournisseurs      = ref([])
const entrepots         = ref([])
const loadingProduits   = ref(false)
const errorProduits     = ref(null)
const imageFile         = ref(null)
const imagePreview      = ref(null)
const uploadingImage    = ref(false)

// ---- Forms ----
const emptyProduitForm = () => ({
  libelle: '', sku: '', reference: '', qr_code: '',
  description: '', prix_achat: 0, prix_vente: 0, image: '',
  unite_mesure: 'unite', peremption: '', statut: 'actif', marque: '',
  entrepot: '', quantite_init: 0, seuil_alerte: 0,
  categorie_nom: '', sous_categorie_nom: '',
  id_categorie: null, id_sous_categorie: null, id_fournisseur: null
})

const produitForm         = reactive(emptyProduitForm())
const categorieForm       = reactive({ libelle: '' })
const sousCategorieForm   = reactive({ libelle: '', rayon: '', palier: '', zone: '', id_categorie: null })

// ---- Computed ----
const filteredProduits = computed(() => {
  let r = produits.value
  if (search.value.trim()) {
    const q = search.value.toLowerCase()
    r = r.filter(p =>
      (p.libelle||'').toLowerCase().includes(q) ||
      (p.sku||'').toLowerCase().includes(q) ||
      (p.reference||'').toLowerCase().includes(q) ||
      (p.marque||'').toLowerCase().includes(q) ||
      (p.categorie_nom||'').toLowerCase().includes(q)
    )
  }
  if (filterCat.value)    r = r.filter(p => p.id_categorie == filterCat.value)
  if (filterStatut.value) r = r.filter(p => p.statut === filterStatut.value)
  return r
})

const filteredSousCategories = computed(() => {
  if (!produitForm.id_categorie) return sousCategories.value
  return sousCategories.value.filter(sc => sc.id_categorie == produitForm.id_categorie)
})

// Liste pour le select catégorie : API d’abord, sinon liste par défaut (toujours une liste déroulante remplie)
const categoriesForSelect = computed(() => {
  return (categories.value || []).map(c => ({
    id: c.id ?? c.id_categorie,
    libelle: (c.libelle || '').trim()
  }))
})

// Generation automatique du SKU : 2 premieres lettres + derniere lettre + 5 chiffres aleatoires (comme page Produits magasin)
const generateSku = (libelle) => {
  if (!libelle || String(libelle).trim().length < 2) return ''
  const clean = String(libelle).trim().toUpperCase().replace(/[^A-Z0-9]/g, '')
  if (clean.length < 2) return ''
  const prefix = clean.substring(0, 2)
  const last = clean.charAt(clean.length - 1)
  const random = Math.floor(10000 + Math.random() * 90000)
  return `${prefix}${last}${random}`
}

// Auto-generer le SKU quand le libelle change (en creation uniquement)
watch(() => produitForm.libelle, (newVal) => {
  if (!editingProduit.value) {
    produitForm.sku = generateSku(newVal)
  }
})

const statsCards = computed(() => [
  {
    label: 'Total produits',
    value: produits.value.length,
    color: '#0ea5e9',
    icon: '<path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/>'
  },
  {
    label: 'Actifs',
    value: produits.value.filter(p => p.statut === 'actif').length,
    color: '#10b981',
    icon: '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>'
  },
  {
    label: 'Inactifs',
    value: produits.value.filter(p => p.statut === 'inactif').length,
    color: '#f59e0b',
    icon: '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>'
  },
  {
    label: 'Archives',
    value: produits.value.filter(p => p.statut === 'archive').length,
    color: '#ef4444',
    icon: '<path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>'
  },
  {
    label: 'En alerte stock',
    value: produits.value.filter(p => p.seuil_alerte > 0 && p.quantite_init <= p.seuil_alerte).length,
    color: '#dc2626',
    icon: '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>'
  },
  {
    label: 'Categories',
    value: categories.value.length,
    color: '#8b5cf6',
    icon: '<path d="M4 20h16a2 2 0 002-2V8a2 2 0 00-2-2h-7.93a2 2 0 01-1.66-.9l-.82-1.2A2 2 0 007.93 3H4a2 2 0 00-2 2v13c0 1.1.9 2 2 2z"/>'
  },
])

const VIEWS = computed(() => [
  { key: 'grid',       label: 'Grille',      icon: '#', count: undefined },
  { key: 'list',       label: 'Liste',       icon: '=', count: undefined },
  { key: 'categories', label: 'Categories',  icon: 'F', count: categories.value.length },
])

// ---- Helpers ----
const formatAmount = n => new Intl.NumberFormat('fr-FR').format(n) + ' FCFA'
const toast = (msg) => { toastMsg.value = msg; showToast.value = true; setTimeout(() => showToast.value = false, 3200) }

const getSubCatsForCat = (catId) => sousCategories.value.filter(sc => sc.id_categorie == catId)
const getProduitsCountForCat = (catId) => produits.value.filter(p => p.id_categorie == catId).length
const onCategorieChange = () => {
  produitForm.id_sous_categorie = null
  produitForm.sous_categorie_nom = ''
}
const onCategorieSelectChange = () => {
  onCategorieChange()
}

// ---- API Calls ----
const loadProduits = async () => {
  loadingProduits.value = true
  errorProduits.value = null
  try {
    const res = await fetch(`${API_BASE}?action=list`, { headers: getHeaders(), credentials: 'include' })
    if (res.status === 401) {
      localStorage.removeItem('token')
      localStorage.removeItem('isAuthenticated')
      window.location.href = '/login'
      return
    }
    const data = await parseJsonOrText(res)
    if (!data.success) throw new Error(data.error || 'Erreur serveur')
    produits.value = data.data || []
  } catch (err) {
    console.error('[Produits] loadProduits error:', err)
    errorProduits.value = err.message
  } finally {
    loadingProduits.value = false
  }
}

const loadCategories = async () => {
  try {
    const res = await fetch(`${API_BASE}?action=list_categories`, { headers: getHeaders(), credentials: 'include' })
    if (res.status === 401) {
      localStorage.removeItem('token')
      localStorage.removeItem('isAuthenticated')
      window.location.href = '/login'
      return
    }
    const data = await parseJsonOrText(res)
    if (data.success && Array.isArray(data.data)) categories.value = data.data
    else categories.value = []
  } catch (err) {
    console.error('[Produits] loadCategories error:', err)
    categories.value = []
  }
}

const loadSousCategories = async () => {
  try {
    const res = await fetch(`${API_BASE}?action=list_sous_categories`, { headers: getHeaders(), credentials: 'include' })
    if (res.status === 401) {
      localStorage.removeItem('token')
      localStorage.removeItem('isAuthenticated')
      window.location.href = '/login'
      return
    }
    const data = await parseJsonOrText(res)
    if (data.success && Array.isArray(data.data)) sousCategories.value = data.data
    else sousCategories.value = []
  } catch (err) {
    console.error('[Produits] loadSousCategories error:', err)
    sousCategories.value = []
  }
}

const loadFournisseurs = async () => {
  try {
    const res = await fetch(`${API_BASE}?action=list_fournisseurs`, { headers: getHeaders(), credentials: 'include' })
    if (res.status === 401) {
      localStorage.removeItem('token')
      localStorage.removeItem('isAuthenticated')
      window.location.href = '/login'
      return
    }
    const data = await parseJsonOrText(res)
    if (data.success && Array.isArray(data.data)) fournisseurs.value = data.data
    else fournisseurs.value = []
  } catch (err) {
    console.error('[Produits] loadFournisseurs error:', err)
    fournisseurs.value = []
  }
}

const loadEntrepots = async () => {
  try {
    const res = await fetch(`${API_BASE}?action=list_entrepots`, { headers: getHeaders(), credentials: 'include' })
    if (res.status === 401) {
      localStorage.removeItem('token')
      localStorage.removeItem('isAuthenticated')
      window.location.href = '/login'
      return
    }
    const data = await parseJsonOrText(res)
    if (data.success && Array.isArray(data.data)) entrepots.value = data.data
    else entrepots.value = []
  } catch (err) {
    console.error('[Produits] loadEntrepots error:', err)
    entrepots.value = []
  }
}

// ---- CRUD Produits ----
const openProduitModal = (p) => {
  editingProduit.value = p
  if (p) {
    Object.assign(produitForm, {
      libelle: p.libelle || '', sku: p.sku || '', reference: p.reference || '',
      qr_code: p.qr_code || '',
      description: p.description || '', prix_achat: p.prix_achat || 0,
      prix_vente: p.prix_vente || 0, image: p.image || '',
      unite_mesure: p.unite_mesure || 'unite', peremption: p.peremption || '',
      statut: p.statut || 'actif', marque: p.marque || '', entrepot: p.entrepot || '',
      quantite_init: p.quantite_init || 0,
      seuil_alerte: p.seuil_alerte || 0,
      categorie_nom: p.categorie_nom || '',
      sous_categorie_nom: p.sous_categorie_nom || '',
      id_categorie: p.id_categorie != null ? Number(p.id_categorie) : null,
      id_sous_categorie: p.id_sous_categorie != null ? Number(p.id_sous_categorie) : null,
      id_fournisseur: p.id_fournisseur != null ? Number(p.id_fournisseur) : null
    })
    imagePreview.value = p.image || null
  } else {
    Object.assign(produitForm, emptyProduitForm())
    imagePreview.value = null
    imageFile.value = null
  }
  if (categories.value.length === 0) loadCategories()
  if (fournisseurs.value.length === 0) loadFournisseurs()
  if (entrepots.value.length === 0) loadEntrepots()
  showProduitModal.value = true
}

// ---- Image helpers ----
const onImageSelect = (e) => {
  const file = e.target.files?.[0]
  if (!file) return
  const allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']
  if (!allowed.includes(file.type)) { alert('Type non supporte. Utilisez JPG, PNG, GIF ou WebP.'); return }
  if (file.size > 5 * 1024 * 1024) { alert('Fichier trop volumineux (max 5MB)'); return }
  imageFile.value = file
  imagePreview.value = URL.createObjectURL(file)
}
const removeImage = () => {
  imageFile.value = null
  imagePreview.value = null
  produitForm.image = ''
}

const saveProduit = async () => {
  const lib = (produitForm.libelle || '').trim()
  if (!lib) { alert('Le libellé est requis'); return }
  const pa = Number(produitForm.prix_achat)
  const pv = Number(produitForm.prix_vente)
  if (Number.isNaN(pa) || pa < 0) { alert('Le prix d\'achat est requis et doit être >= 0'); return }
  if (Number.isNaN(pv) || pv < 0) { alert('Le prix de vente est requis et doit être >= 0'); return }
  savingProduit.value = true
  try {
    // Upload image via Cloudinary si un fichier a ete selectionne
    if (imageFile.value) {
      uploadingImage.value = true
      try {
        const url = await uploadToCloudinary(imageFile.value)
        produitForm.image = url
      } catch (err) {
        alert('Erreur upload image: ' + err.message)
        return
      } finally {
        uploadingImage.value = false
      }
    }

    const isEdit = !!editingProduit.value
    const action = isEdit ? 'update_produit' : 'add_produit'
    const body = { ...produitForm }
    if (isEdit) body.id_produit = editingProduit.value.id
    body.id_categorie = body.id_categorie != null && body.id_categorie !== '' ? Number(body.id_categorie) : null
    body.id_sous_categorie = body.id_sous_categorie != null && body.id_sous_categorie !== '' ? Number(body.id_sous_categorie) : null
    body.id_fournisseur = body.id_fournisseur != null && body.id_fournisseur !== '' ? Number(body.id_fournisseur) : null

    const res = await fetch(`${API_BASE}?action=${action}`, {
      method: 'POST', headers: getHeaders(), credentials: 'include',
      body: JSON.stringify(body)
    })
    const data = await parseJsonOrText(res)
    if (!data.success) throw new Error(data.details || data.error || 'Erreur serveur')

    toast(isEdit ? 'Produit modifie avec succes' : 'Produit cree avec succes')
    showProduitModal.value = false
    await loadProduits()
  } catch (err) {
    const msg = err.message || String(err)
    alert('Erreur: ' + msg)
  } finally {
    savingProduit.value = false
  }
}

const deleteProduit = async (id) => {
  if (!confirm('Supprimer ce produit ? Il sera archivé et retiré de la liste.')) return
  try {
    const res = await fetch(`${API_BASE}?action=delete_produit`, {
      method: 'POST', headers: getHeaders(), credentials: 'include',
      body: JSON.stringify({ id_produit: id })
    })
    const data = await parseJsonOrText(res)
    if (!data.success) throw new Error(data.error || 'Erreur serveur')
    toast('Produit supprimé')
    await loadProduits()
  } catch (err) {
    alert('Erreur: ' + err.message)
  }
}

// ---- CRUD Categories ----
const openCategorieModal = () => {
  editingCategorie.value = null
  categorieForm.libelle = ''
  showCategorieModal.value = true
}

const openEditCategorieModal = (c) => {
  editingCategorie.value = c
  categorieForm.libelle = c.libelle
  showCategorieModal.value = true
}

const addDefaultCategories = async () => {
  seedingDefaults.value = true
  try {
    const res = await fetch(`${API_BASE}?action=seed_default_categories`, {
      method: 'POST', headers: getHeaders(), credentials: 'include',
      body: JSON.stringify({})
    })
    const data = await parseJsonOrText(res)
    if (!data.success) throw new Error(data.error || 'Erreur serveur')
    const added = data.data?.added ?? 0
    toast(added > 0 ? `${added} categorie(s) par defaut ajoutee(s)` : 'Catégories par défaut déjà présentes')
    await loadCategories()
  } catch (err) {
    alert('Erreur: ' + err.message)
  } finally {
    seedingDefaults.value = false
  }
}

const saveCategorie = async () => {
  if (!categorieForm.libelle) { alert('Le libelle est requis'); return }
  savingCategorie.value = true
  try {
    const isEdit = !!editingCategorie.value
    const action = isEdit ? 'update_categorie' : 'add_categorie'
    const body = { libelle: categorieForm.libelle }
    if (isEdit) body.id_categorie = editingCategorie.value.id

    const res = await fetch(`${API_BASE}?action=${action}`, {
      method: 'POST', headers: getHeaders(), credentials: 'include',
      body: JSON.stringify(body)
    })
    const data = await parseJsonOrText(res)
    if (!data.success) throw new Error(data.error || 'Erreur serveur')

    toast(isEdit ? 'Categorie modifiee' : 'Categorie creee')
    showCategorieModal.value = false
    await loadCategories()
  } catch (err) {
    alert('Erreur: ' + err.message)
  } finally {
    savingCategorie.value = false
  }
}

const deleteCategorie = async (id) => {
  if (!confirm('Supprimer cette categorie ?')) return
  try {
    const res = await fetch(`${API_BASE}?action=delete_categorie`, {
      method: 'POST', headers: getHeaders(), credentials: 'include',
      body: JSON.stringify({ id_categorie: id })
    })
    const data = await parseJsonOrText(res)
    if (!data.success) throw new Error(data.error || 'Erreur serveur')
    toast('Categorie supprimee')
    await loadCategories()
  } catch (err) {
    alert('Erreur: ' + err.message)
  }
}

// ---- CRUD Sous-categories ----
const openSousCategorieModal = (catId) => {
  sousCategorieForm.libelle = ''
  sousCategorieForm.rayon = ''
  sousCategorieForm.palier = ''
  sousCategorieForm.zone = ''
  sousCategorieForm.id_categorie = catId
  showSousCategorieModal.value = true
}

const saveSousCategorie = async () => {
  if (!sousCategorieForm.libelle) { alert('Le libelle est requis'); return }
  savingSousCategorie.value = true
  try {
    const res = await fetch(`${API_BASE}?action=add_sous_categorie`, {
      method: 'POST', headers: getHeaders(), credentials: 'include',
      body: JSON.stringify(sousCategorieForm)
    })
    const data = await parseJsonOrText(res)
    if (!data.success) throw new Error(data.error || 'Erreur serveur')
    toast('Sous-categorie creee')
    showSousCategorieModal.value = false
    await loadSousCategories()
  } catch (err) {
    alert('Erreur: ' + err.message)
  } finally {
    savingSousCategorie.value = false
  }
}

const deleteSousCategorie = async (id) => {
  if (!confirm('Supprimer cette sous-categorie ?')) return
  try {
    const res = await fetch(`${API_BASE}?action=delete_sous_categorie`, {
      method: 'POST', headers: getHeaders(), credentials: 'include',
      body: JSON.stringify({ id_sous_categorie: id })
    })
    const data = await parseJsonOrText(res)
    if (!data.success) throw new Error(data.error || 'Erreur serveur')
    toast('Sous-categorie supprimee')
    await loadSousCategories()
  } catch (err) {
    alert('Erreur: ' + err.message)
  }
}

// ---- STYLES ----
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
const defaultCatsBtnStyle = { display:'flex', alignItems:'center', gap:'8px', padding:'11px 18px', background:'linear-gradient(135deg,#8b5cf6,#7c3aed)', color:'white', border:'none', borderRadius:'11px', fontSize:'13px', fontWeight:'700', cursor:'pointer', boxShadow:'0 2px 8px rgba(139,92,246,0.3)' }

const statsRow = { display:'grid', gridTemplateColumns:'repeat(auto-fit,minmax(160px,1fr))', gap:'12px', marginBottom:'20px' }
const statCard = { background:'white', borderRadius:'14px', padding:'18px', display:'flex', alignItems:'center', gap:'13px', border:'1px solid #f1f5f9', boxShadow:'0 1px 4px rgba(0,0,0,0.05)' }
const statIcon = c => ({ width:'42px', height:'42px', borderRadius:'11px', background:`${c}15`, display:'flex', alignItems:'center', justifyContent:'center', color:c, flexShrink:0 })
const statVal  = { fontSize:'20px', fontWeight:'800', letterSpacing:'-0.02em' }
const statLbl  = { fontSize:'12px', color:'#64748b', fontWeight:'500', marginTop:'2px' }

const filterBarStyle  = { display:'flex', gap:'10px', marginBottom:'20px', flexWrap:'wrap', alignItems:'center' }
const searchInputStyle= { width:'100%', padding:'11px 14px 11px 44px', border:'1px solid #e2e8f0', borderRadius:'10px', fontSize:'13px', fontWeight:'500', color:'#1e293b', outline:'none', boxSizing:'border-box' }
const filterSelectStyle={ padding:'10px 14px', border:'1px solid #e2e8f0', borderRadius:'10px', fontSize:'13px', fontWeight:'500', color:'#475569', outline:'none', background:'white' }
const clearBtnStyle   = { display:'flex', alignItems:'center', gap:'5px', padding:'10px 14px', background:'#fef2f2', border:'1px solid #fecaca', borderRadius:'10px', fontSize:'12px', fontWeight:'700', color:'#ef4444', cursor:'pointer' }

const errorBox = { textAlign:'center', padding:'40px', background:'#fef2f2', borderRadius:'16px', border:'1px solid #fecaca' }

const prodGridStyle   = { display:'grid', gridTemplateColumns:'repeat(auto-fill,minmax(220px,1fr))', gap:'14px' }
const emptyStateStyle = { textAlign:'center', padding:'60px 20px', background:'white', borderRadius:'16px', border:'1px solid #f1f5f9' }

const getProdCard = i => ({
  background:'white', borderRadius:'16px', overflow:'hidden', position:'relative',
  border:`1px solid ${hovCard.value === i ? '#e2e8f0' : '#f1f5f9'}`,
  boxShadow: hovCard.value === i ? '0 8px 24px rgba(0,0,0,0.1)' : '0 1px 4px rgba(0,0,0,0.04)',
  transition:'all 0.22s', transform: hovCard.value === i ? 'translateY(-3px)' : 'none'
})
const prodAccent  = p => ({ height:'3px', background: p.statut === 'archive' ? '#ef4444' : p.statut === 'inactif' ? '#f59e0b' : 'linear-gradient(90deg,#0ea5e9,#6366f1)' })
const prodCardActions = { display:'flex', justifyContent:'flex-end', gap:'4px', padding:'10px 10px 0' }
const pca = type => ({
  width:'26px', height:'26px', borderRadius:'7px', cursor:'pointer',
  display:'flex', alignItems:'center', justifyContent:'center',
  border:`1px solid ${type==='edit'?'#bfdbfe':'#fecaca'}`,
  background: type==='edit'?'#eff6ff':'#fef2f2',
  color: type==='edit'?'#3b82f6':'#ef4444'
})
const stockBadge = p => ({
  display:'inline-block', padding:'3px 9px', borderRadius:'20px', fontSize:'11px', fontWeight:'800',
  background:'#f0fdf4', color:'#10b981'
})
const seuilBadge = p => ({
  display:'inline-block', padding:'3px 9px', borderRadius:'20px', fontSize:'11px', fontWeight:'800',
  background: p.quantite_init <= p.seuil_alerte ? '#fef2f2' : '#fffbeb',
  color: p.quantite_init <= p.seuil_alerte ? '#ef4444' : '#f59e0b'
})
const statusPillSmall = p => ({
  display:'inline-block', padding:'2px 8px', borderRadius:'20px', fontSize:'10px', fontWeight:'700', textTransform:'uppercase',
  background: p.statut==='actif'?'#dcfce7':p.statut==='inactif'?'#fffbeb':'#fef2f2',
  color: p.statut==='actif'?'#166534':p.statut==='inactif'?'#92400e':'#991b1b'
})

// Liste
const tableSection = { background:'white', borderRadius:'16px', border:'1px solid #f1f5f9', overflow:'hidden', boxShadow:'0 1px 4px rgba(0,0,0,0.04)' }
const tableWrap    = { overflowX:'auto' }
const tableStyle   = { width:'100%', borderCollapse:'collapse' }
const theadTr      = { background:'#f8fafc' }
const th           = { padding:'12px 14px', textAlign:'left', fontSize:'11px', fontWeight:'800', color:'#64748b', textTransform:'uppercase', letterSpacing:'.06em', borderBottom:'1px solid #f1f5f9', whiteSpace:'nowrap' }
const getTr        = i => ({ background: hovRow.value===i ? '#f8fafc' : 'white', borderBottom:'1px solid #f8fafc', transition:'background 0.15s' })
const td           = { padding:'12px 14px', fontSize:'13px', verticalAlign:'middle' }
const emptyTd      = { textAlign:'center', padding:'48px', color:'#94a3b8', fontSize:'14px' }
const catPill      = c => { return { padding:'3px 10px', borderRadius:'20px', fontSize:'11px', fontWeight:'700', background:'#f1f5f9', color:'#475569' } }
const statusPill   = p => {
  return {
    padding:'3px 10px', borderRadius:'20px', fontSize:'11px', fontWeight:'700', textTransform:'uppercase',
    background: p.statut==='actif'?'#dcfce7':p.statut==='inactif'?'#fffbeb':'#fef2f2',
    color: p.statut==='actif'?'#166534':p.statut==='inactif'?'#92400e':'#991b1b'
  }
}
const aBtn = c => ({ width:'28px', height:'28px', border:`1px solid ${c}30`, background:`${c}15`, color:c, borderRadius:'7px', cursor:'pointer', display:'flex', alignItems:'center', justifyContent:'center', fontSize:'14px' })

// Categories
const catGridStyle = { display:'grid', gridTemplateColumns:'repeat(auto-fill,minmax(320px,1fr))', gap:'16px' }
const getCatCard   = i => ({
  background:'white', borderRadius:'18px', overflow:'hidden',
  border:`1px solid ${hovCat.value===i?'#e2e8f0':'#f1f5f9'}`,
  boxShadow: hovCat.value===i ? '0 8px 24px rgba(0,0,0,0.09)' : '0 1px 4px rgba(0,0,0,0.05)',
  transition:'all 0.22s', transform: hovCat.value===i ? 'translateY(-2px)' : 'none'
})
const catIconStyle = i => ({ width:'46px', height:'46px', background:`${CARD_COLORS[i%CARD_COLORS.length]}18`, borderRadius:'13px', display:'flex', alignItems:'center', justifyContent:'center', color:CARD_COLORS[i%CARD_COLORS.length] })
const iconBtn      = c => ({ width:'30px', height:'30px', border:`1px solid ${c}25`, background:`${c}12`, color:c, borderRadius:'8px', cursor:'pointer', display:'flex', alignItems:'center', justifyContent:'center' })
const addSubCatBtn = { padding:'3px 10px', background:'#f5f3ff', border:'1px solid #ddd6fe', borderRadius:'6px', fontSize:'11px', fontWeight:'700', color:'#8b5cf6', cursor:'pointer' }
const addCatCard   = { border:'2px dashed #e2e8f0', borderRadius:'18px', display:'flex', flexDirection:'column', alignItems:'center', justifyContent:'center', minHeight:'220px', cursor:'pointer', transition:'all 0.2s' }

// Modals
const overlayStyle     = { position:'fixed', inset:0, background:'rgba(15,23,42,0.72)', backdropFilter:'blur(8px)', display:'flex', alignItems:'center', justifyContent:'center', zIndex:1000, padding:'20px', overflowY:'auto' }
const prodModalStyle   = { background:'white', borderRadius:'22px', width:'100%', maxWidth:'720px', maxHeight:'92vh', overflow:'auto', boxShadow:'0 25px 50px rgba(0,0,0,0.25)' }
const smallModalStyle  = { background:'white', borderRadius:'22px', width:'100%', maxWidth:'500px', maxHeight:'90vh', overflow:'auto', boxShadow:'0 25px 50px rgba(0,0,0,0.25)' }
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
const spinLg           = { width:'32px', height:'32px', border:'3px solid #e2e8f0', borderTop:'3px solid #0ea5e9', borderRadius:'50%', animation:'p-spin 0.8s linear infinite', margin:'0 auto' }

// Form
const formSec  = { marginBottom:'20px' }
const secTitle = c => ({ display:'flex', alignItems:'center', gap:'7px', fontSize:'13px', fontWeight:'800', color:'#0f172a', paddingBottom:'10px', borderBottom:`2px solid ${c}22`, marginBottom:'14px' })
const formGrid = { display:'grid', gridTemplateColumns:'1fr 1fr', gap:'14px' }
const fGroup   = { marginBottom:'14px' }
const fLabel   = { display:'block', fontSize:'11px', fontWeight:'800', color:'#475569', textTransform:'uppercase', letterSpacing:'.06em', marginBottom:'7px' }
const fInput   = { width:'100%', padding:'10px 13px', border:'1px solid #e2e8f0', borderRadius:'9px', fontSize:'13px', fontWeight:'500', color:'#1e293b', boxSizing:'border-box', outline:'none', background:'white' }
const margeInfo= { fontSize:'12px', fontWeight:'600', padding:'8px 12px', background:'#f8fafc', borderRadius:'8px', marginTop:'6px' }
const imageUploadZone = { position:'relative', width:'100%', minHeight:'80px', border:'2px dashed #e2e8f0', borderRadius:'10px', display:'flex', alignItems:'center', justifyContent:'center', cursor:'pointer', padding:'12px', boxSizing:'border-box', transition:'border-color 0.2s', background:'#fafbfc' }
const removeImgBtn = { position:'absolute', top:'4px', right:'4px', width:'22px', height:'22px', borderRadius:'50%', background:'#ef4444', border:'none', color:'white', cursor:'pointer', display:'flex', alignItems:'center', justifyContent:'center', padding:0 }
const uploadOverlay = { position:'absolute', inset:0, background:'rgba(255,255,255,0.85)', borderRadius:'10px', display:'flex', flexDirection:'column', alignItems:'center', justifyContent:'center' }

// Toast
const toastStyle = { position:'fixed', bottom:'28px', left:'50%', transform:'translateX(-50%)', background:'#10b981', color:'white', padding:'13px 22px', borderRadius:'12px', display:'flex', alignItems:'center', gap:'10px', fontSize:'14px', fontWeight:'700', boxShadow:'0 8px 24px rgba(16,185,129,0.35)', zIndex:9999, whiteSpace:'nowrap' }

// ---- Init ----
onMounted(async () => {
  await Promise.all([loadProduits(), loadCategories(), loadSousCategories(), loadFournisseurs(), loadEntrepots()])
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,700;0,9..40,800&display=swap');
* { font-family: 'DM Sans', -apple-system, sans-serif; }

@keyframes p-fadein { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:translateY(0); } }
@keyframes p-spin   { to { transform:rotate(360deg); } }

.p-fade { animation: p-fadein 0.45s cubic-bezier(0.16,1,0.3,1) backwards; }

.slide-modal-enter-active, .slide-modal-leave-active { transition: all 0.3s cubic-bezier(0.16,1,0.3,1); }
.slide-modal-enter-from, .slide-modal-leave-to       { opacity: 0; }
.slide-modal-enter-from .modal-panel                 { transform: scale(0.96) translateY(14px); }
.modal-panel                                         { transition: all 0.3s cubic-bezier(0.16,1,0.3,1); }

.toast-enter-active, .toast-leave-active { transition: all 0.35s cubic-bezier(0.16,1,0.3,1); }
.toast-enter-from, .toast-leave-to       { opacity:0; transform: translateX(-50%) translateY(12px); }

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

::-webkit-scrollbar       { width: 5px; height: 5px; }
::-webkit-scrollbar-track { background: #f1f5f9; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
</style>
