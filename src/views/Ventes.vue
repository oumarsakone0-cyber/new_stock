<template>
  <SidebarLayout currentPage="points-de-vente">
    <!-- Header -->
    <header :style="headerStyle" class="pdv-fade-in">
      <div :style="headerContentStyle">
        <div :style="breadcrumbStyle">
          <span :style="breadcrumbItemStyle">Tableau de bord</span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
          </svg>
          <span :style="breadcrumbActiveStyle">Points de Vente</span>
        </div>
        <h1 :style="titleStyle">Points de Vente</h1>
        <p :style="subtitleStyle">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
            <polyline points="9 22 9 12 15 12 15 22"/>
          </svg>
          {{ filteredPdv.length }} point(s) de vente{{ activeFilter !== 'all' ? ' filtré(s)' : '' }}
        </p>

        <!-- Filtres statut -->
        <div :style="filtersContainerStyle">
          <button v-for="f in STATUS_FILTERS" :key="f.key" :style="getFilterBtnStyle(f.key)" @click="activeFilter = f.key">
            <span :style="filterDotStyle(f.key)"></span>
            {{ f.label }}
          </button>
        </div>
      </div>

      <button :style="addButtonStyle" @click="openCreateModal" @mouseenter="addHovered=true" @mouseleave="addHovered=false" class="pdv-fade-in" style="animation-delay:0.2s">
        <div :style="addButtonIconStyle">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
          </svg>
        </div>
        <span>Nouveau Point de Vente</span>
      </button>
    </header>

    <!-- Stats globales -->
    <div :style="statsRowStyle" class="pdv-fade-in" style="animation-delay:0.1s">
      <div :style="statCardStyle">
        <div :style="statIconStyle('#0ea5e9')">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
            <polyline points="9 22 9 12 15 12 15 22"/>
          </svg>
        </div>
        <div>
          <div :style="statValueStyle">{{ pdvList.length }}</div>
          <div :style="statLabelStyle">Total PDV</div>
        </div>
      </div>

      <div :style="statCardStyle">
        <div :style="statIconStyle('#10b981')">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
            <polyline points="17 6 23 6 23 12"/>
          </svg>
        </div>
        <div>
          <div :style="statValueStyle">{{ formatAmount(totalVenteJour) }}</div>
          <div :style="statLabelStyle">Ventes Aujourd'hui</div>
        </div>
      </div>

      <div :style="statCardStyle">
        <div :style="statIconStyle('#f59e0b')">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
        </div>
        <div>
          <div :style="statValueStyle">{{ formatAmount(totalVenteHier) }}</div>
          <div :style="statLabelStyle">Ventes Hier</div>
        </div>
      </div>

      <div :style="{ ...statCardStyle, background: 'linear-gradient(135deg, #fff7ed 0%, #fef3c7 100%)', border: '1px solid #fed7aa' }">
        <div :style="statIconStyle('#ef4444')">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
        </div>
        <div>
          <div :style="{ ...statValueStyle, color: '#ef4444' }">{{ formatAmount(totalAEncaisser) }}</div>
          <div :style="statLabelStyle">À Encaisser</div>
        </div>
      </div>

      <div :style="statCardStyle">
        <div :style="statIconStyle('#8b5cf6')">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
          </svg>
        </div>
        <div>
          <div :style="statValueStyle">{{ countByStatut('actif') }}</div>
          <div :style="statLabelStyle">PDV Actifs</div>
        </div>
      </div>
    </div>

    <!-- Barre de recherche -->
    <div :style="searchWrapperStyle" class="pdv-fade-in" style="animation-delay:0.15s">
      <div :style="searchInnerStyle">
        <svg style="position:absolute;left:16px;color:#94a3b8;pointer-events:none" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"/>
          <path d="M21 21l-4.35-4.35"/>
        </svg>
        <input v-model="searchQuery" type="text" placeholder="Rechercher par nom, ville, responsable..." :style="searchInputStyle"/>
        <button v-if="searchQuery" :style="searchClearStyle" @click="searchQuery=''">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" :style="loadingStyle">
      <div :style="spinnerStyle"></div>
      <p>Chargement des points de vente...</p>
    </div>

    <!-- Error -->
    <div v-if="error" :style="errorStyle">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      <p>{{ error }}</p>
      <button :style="retryBtnStyle" @click="loadPdv">Réessayer</button>
    </div>

    <!-- Empty State -->
    <div v-if="!loading && !error && filteredPdv.length === 0" :style="emptyStateStyle">
      <svg width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5">
        <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
        <polyline points="9 22 9 12 15 12 15 22"/>
      </svg>
      <p :style="emptyTitleStyle">Aucun point de vente trouvé</p>
      <p :style="emptySubStyle">{{ searchQuery ? 'Modifiez votre recherche' : 'Créez votre premier point de vente' }}</p>
    </div>

    <!-- Grille PDV -->
    <div v-if="!loading && !error && filteredPdv.length > 0" :style="gridStyle">
      <div
        v-for="(pdv, index) in filteredPdv"
        :key="pdv.id_pdv"
        :style="getCardStyle(index)"
        @click="goToDetails(pdv)"
        @mouseenter="hoveredCard = index"
        @mouseleave="hoveredCard = null"
        class="pdv-card pdv-fade-in"
      >
        <!-- Bande couleur haut de carte -->
        <div :style="getCardAccentStyle(index)"></div>
        <!-- Header carte -->
        <div :style="cardHeaderStyle">
          <div :style="getStoreIconStyle(index)">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
              <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
          </div>
          <div :style="cardActionsStyle">
            <button :style="getActionBtnStyle('edit', index)" @click.stop="editPdv(pdv)" @mouseenter="hoveredAction='edit-'+index" @mouseleave="hoveredAction=null" title="Modifier">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
              </svg>
            </button>
            <button :style="getActionBtnStyle('cloturer', index)" @click.stop="cloturerJournee(pdv)" @mouseenter="hoveredAction='cloturer-'+index" @mouseleave="hoveredAction=null" title="Clôturer la journée">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 11 12 14 22 4"/>
                <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
              </svg>
            </button>
            <button :style="getActionBtnStyle('delete', index)" @click.stop="deletePdv(pdv.id_pdv)" @mouseenter="hoveredAction='delete-'+index" @mouseleave="hoveredAction=null" title="Supprimer">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="3 6 5 6 21 6"/>
                <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Nom & Ville -->
        <h3 :style="cardNameStyle">{{ pdv.nom }}</h3>
        <p :style="cardVilleStyle">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
            <circle cx="12" cy="10" r="3"/>
          </svg>
          {{ pdv.ville }}{{ pdv.quartier ? ` — ${pdv.quartier}` : '' }}
        </p>

        <!-- Responsable & Contact -->
        <div :style="responsableBoxStyle">
          <div :style="responsableInfoStyle">
            <div :style="responsableAvatarStyle(index)">{{ getInitials(pdv.responsable) }}</div>
            <div>
              <div :style="responsableNameStyle">{{ pdv.responsable }}</div>
              <div :style="responsableRoleStyle">Responsable vente</div>
            </div>
          </div>
          <a v-if="pdv.contact" :href="'tel:' + pdv.contact" :style="contactBtnStyle">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
            </svg>
            {{ pdv.contact }}
          </a>
        </div>

        <!-- Stats ventes -->
        <div :style="venteStatsStyle">
          <div :style="venteStatItemStyle('#10b981')">
            <div :style="venteStatLabelStyle">Aujourd'hui</div>
            <div :style="venteStatValueStyle('#10b981')">{{ formatAmount(pdv.vente_jour || 0) }}</div>
          </div>
          <div :style="vDividerStyle"></div>
          <div :style="venteStatItemStyle('#64748b')">
            <div :style="venteStatLabelStyle">Hier</div>
            <div :style="venteStatValueStyle('#64748b')">{{ formatAmount(pdv.vente_hier || 0) }}</div>
          </div>
          <div :style="vDividerStyle"></div>
          <div :style="venteStatItemStyle('#ef4444')">
            <div :style="venteStatLabelStyle">À encaisser</div>
            <div :style="{ ...venteStatValueStyle('#ef4444'), fontWeight: '800' }">{{ formatAmount(pdv.a_encaisser || 0) }}</div>
          </div>
        </div>

        <!-- Indicateur à encaisser -->
        <div v-if="(pdv.a_encaisser || 0) > 0" :style="encaisserAlertStyle">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13" stroke="white" stroke-width="2"/>
            <line x1="12" y1="17" x2="12.01" y2="17" stroke="white" stroke-width="2"/>
          </svg>
          <span>Journée non clôturée — {{ formatAmount(pdv.a_encaisser) }} à récupérer</span>
        </div>

        <!-- Footer -->
        <div :style="cardFooterStyle">
          <button :style="getStatusToggleStyle(pdv.statut === 'actif')" @click.stop="toggleStatut(pdv.id)">
            <span :style="dotStyle(pdv.statut === 'actif')"></span>
            {{ pdv.statut === 'actif' ? 'Actif' : 'Inactif' }}
          </button>
          <span :style="dateStyle" v-if="pdv.derniere_cloture">
            Clôturé {{ formatDate(pdv.derniere_cloture) }}
          </span>
          <span :style="dateStyle" v-else-if="pdv.date_create">
            Créé {{ formatDate(pdv.date_create) }}
          </span>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <Transition name="pdv-modal">
      <div v-if="showModal" :style="overlayStyle" @click.self="closeModal">
        <div :style="modalStyle" class="pdv-modal-content">
          <!-- Header Modal -->
          <div :style="modalHeaderStyle">
            <div>
              <div :style="modalBadgeStyle">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                  <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
              </div>
              <h2 :style="modalTitleStyle">{{ editingPdv ? 'Modifier le Point de Vente' : 'Nouveau Point de Vente' }}</h2>
              <p :style="modalSubStyle">{{ editingPdv ? 'Modifiez les informations du point de vente' : 'Enregistrez un nouveau point de vente dans le système' }}</p>
            </div>
            <button :style="closeBtnStyle" @click="closeModal">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 6L6 18M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Formulaire -->
          <form @submit.prevent="savePdv" :style="formStyle">

            <!-- Infos du PDV -->
            <div :style="sectionTitleStyle">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
              </svg>
              Informations du point de vente
            </div>

            <div :style="rowStyle">
              <div :style="groupStyle">
                <label :style="labelStyle">Nom du PDV <span :style="reqStyle">*</span></label>
                <input v-model="form.nom" type="text" :style="inputStyle" placeholder="Ex: Boutique Plateau" required/>
              </div>
              <div :style="groupStyle">
                <label :style="labelStyle">Ville <span :style="reqStyle">*</span></label>
                <input v-model="form.ville" type="text" :style="inputStyle" placeholder="Ex: Abidjan" required/>
              </div>
            </div>

            <div :style="rowStyle">
              <div :style="groupStyle">
                <label :style="labelStyle">Quartier / Zone</label>
                <input v-model="form.quartier" type="text" :style="inputStyle" placeholder="Ex: Plateau, Zone 4..."/>
              </div>
              <div :style="groupStyle">
                <label :style="labelStyle">Adresse complète</label>
                <input v-model="form.adresse" type="text" :style="inputStyle" placeholder="Rue, bâtiment..."/>
              </div>
            </div>

            <!-- Responsable -->
            <div :style="sectionTitleStyle">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </svg>
              Responsable & Contact
            </div>

            <div :style="rowStyle">
              <div :style="groupStyle">
                <label :style="labelStyle">Responsable vente <span :style="reqStyle">*</span></label>
                <input v-model="form.responsable" type="text" :style="inputStyle" placeholder="Nom complet" required/>
              </div>
              <div :style="groupStyle">
                <label :style="labelStyle">Contact <span :style="reqStyle">*</span></label>
                <input v-model="form.contact" type="tel" :style="inputStyle" placeholder="+225 07 00 00 00 00" required/>
              </div>
            </div>

            <div :style="rowStyle">
              <div :style="groupStyle">
                <label :style="labelStyle">Email (optionnel)</label>
                <input v-model="form.email" type="email" :style="inputStyle" placeholder="pdv@example.com"/>
              </div>
              <div :style="groupStyle">
                <label :style="labelStyle">Statut</label>
                <select v-model="form.statut" :style="inputStyle">
                  <option value="actif">Actif</option>
                  <option value="inactif">Inactif</option>
                </select>
              </div>
            </div>

            <!-- Paramètres financiers -->
            <div :style="sectionTitleStyle">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
              </svg>
              Paramètres financiers
            </div>

            <div :style="rowStyle">
              <div :style="groupStyle">
                <label :style="labelStyle">Objectif journalier (FCFA)</label>
                <input v-model.number="form.objectif_jour" type="number" :style="inputStyle" placeholder="0" min="0"/>
              </div>
              <div :style="groupStyle">
                <label :style="labelStyle">Type de point de vente</label>
                <select v-model="form.type_pdv" :style="inputStyle">
                  <option value="boutique">Boutique</option>
                  <option value="kiosque">Kiosque</option>
                  <option value="depot">Dépôt</option>
                  <option value="mobile">Mobile</option>
                </select>
              </div>
            </div>

            <!-- Note -->
            <div :style="groupStyle">
              <label :style="labelStyle">Remarques / Notes</label>
              <textarea v-model="form.notes" :style="{ ...inputStyle, height: '80px', resize: 'vertical' }" placeholder="Informations supplémentaires..."></textarea>
            </div>

            <!-- Actions -->
            <div :style="actionsStyle">
              <button type="button" :style="cancelBtnStyle" @click="closeModal">Annuler</button>
              <button type="submit" :style="submitBtnStyle" :disabled="saving">
                <svg v-if="!saving" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                  <polyline points="17 21 17 13 7 13 7 21"/>
                  <polyline points="7 3 7 8 15 8"/>
                </svg>
                <div v-else :style="btnSpinnerStyle"></div>
                {{ saving ? 'Enregistrement...' : (editingPdv ? 'Modifier' : 'Créer le PDV') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <!-- Modal Clôture journée -->
    <Transition name="pdv-modal">
      <div v-if="showCloture" :style="overlayStyle" @click.self="showCloture=false">
        <div :style="{ ...modalStyle, maxWidth: '480px' }" class="pdv-modal-content">
          <div :style="modalHeaderStyle">
            <div>
              <div :style="{ ...modalBadgeStyle, background: 'linear-gradient(135deg, #f59e0b, #d97706)' }">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="9 11 12 14 22 4"/>
                  <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
                </svg>
              </div>
              <h2 :style="modalTitleStyle">Clôture de Journée</h2>
              <p :style="modalSubStyle">{{ cloturePdv?.nom }} — {{ formatDateToday() }}</p>
            </div>
            <button :style="closeBtnStyle" @click="showCloture=false">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 6L6 18M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <div style="padding: 24px 32px 32px">
            <!-- Résumé -->
            <div :style="clotureResStyle">
              <div :style="clotureResItemStyle('#10b981')">
                <span :style="clotureResLabelStyle">Total vendu aujourd'hui</span>
                <span :style="clotureResValStyle('#10b981')">{{ formatAmount(cloturePdv?.vente_jour || 0) }}</span>
              </div>
              <div :style="clotureResItemStyle('#ef4444')">
                <span :style="clotureResLabelStyle">Montant à encaisser</span>
                <span :style="clotureResValStyle('#ef4444')">{{ formatAmount(cloturePdv?.a_encaisser || 0) }}</span>
              </div>
            </div>

            <div :style="groupStyle">
              <label :style="labelStyle">Montant encaissé réellement (FCFA) <span :style="reqStyle">*</span></label>
              <input v-model.number="clotureForm.montant_encaisse" type="number" :style="inputStyle" placeholder="0" min="0"/>
            </div>
            <div :style="groupStyle">
              <label :style="labelStyle">Observations</label>
              <textarea v-model="clotureForm.observations" :style="{ ...inputStyle, height:'72px', resize:'vertical' }" placeholder="Remarques sur la journée..."></textarea>
            </div>

            <div :style="actionsStyle">
              <button type="button" :style="cancelBtnStyle" @click="showCloture=false">Annuler</button>
              <button :style="{ ...submitBtnStyle, background: 'linear-gradient(135deg, #f59e0b, #d97706)' }" @click="confirmerCloture" :disabled="saving">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="9 11 12 14 22 4"/>
                  <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
                </svg>
                Valider la clôture
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

  </SidebarLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { usePdvSelectionStore } from '../stores/pdvSelectionStore'
import { useRouter } from 'vue-router'
import SidebarLayout from './SidebarLayout.vue'
import { getPointsVente, addPointVente, updatePointVente, deletePointVente } from '../services/api'
const router = useRouter()
const pdvSelectionStore = usePdvSelectionStore()
// ─── Constantes ─────────────────────────────────────────────────────
const STATUS_FILTERS = [
  { key: 'all',    label: 'Tous' },
  { key: 'actif',  label: 'Actifs' },
  { key: 'inactif',label: 'Inactifs' },
]

const TYPE_PDV_COLORS = {
  boutique: { bg: '#dbeafe', text: '#1e40af', border: '#bfdbfe' },
  kiosque:  { bg: '#fef3c7', text: '#92400e', border: '#fde68a' },
  depot:    { bg: '#dcfce7', text: '#166534', border: '#bbf7d0' },
  mobile:   { bg: '#f3e8ff', text: '#6b21a8', border: '#e9d5ff' },
}

const CARD_COLORS = ['#0ea5e9','#10b981','#f59e0b','#8b5cf6','#ef4444','#ec4899','#06b6d4']

// ─── State ──────────────────────────────────────────────────────────
const pdvList = ref([])
const loading  = ref(false)
const saving   = ref(false)
const error    = ref(null)
const showModal  = ref(false)
const showCloture= ref(false)
const editingPdv = ref(null)
const cloturePdv = ref(null)
const hoveredCard = ref(null)
const hoveredAction = ref(null)
const addHovered  = ref(false)
const activeFilter = ref('all')
const searchQuery  = ref('')

const form = reactive({
  nom: '', ville: '', quartier: '', adresse: '',
  responsable: '', contact: '', email: '',
  statut: 'actif', objectif_jour: 0,
  type_pdv: 'boutique', notes: ''
})

const clotureForm = reactive({ montant_encaisse: 0, observations: '' })

// ─── Computed ───────────────────────────────────────────────────────
const filteredPdv = computed(() => {
  let r = pdvList.value
  if (activeFilter.value !== 'all') r = r.filter(p => p.statut === activeFilter.value)
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    r = r.filter(p =>
      (p.nom||'').toLowerCase().includes(q) ||
      (p.ville||'').toLowerCase().includes(q) ||
      (p.responsable||'').toLowerCase().includes(q) ||
      (p.contact||'').toLowerCase().includes(q)
    )
  }
  return r
})

const totalVenteJour   = computed(() => pdvList.value.reduce((s,p) => s+(p.vente_jour||0), 0))
const totalVenteHier   = computed(() => pdvList.value.reduce((s,p) => s+(p.vente_hier||0), 0))
const totalAEncaisser  = computed(() => pdvList.value.reduce((s,p) => s+(p.a_encaisser||0), 0))
const countByStatut    = (s) => pdvList.value.filter(p => p.statut === s).length

// ─── Helpers ────────────────────────────────────────────────────────
const getInitials = (name='') => name.split(' ').map(w=>w[0]).join('').toUpperCase().slice(0,2)
const formatAmount = (n) => new Intl.NumberFormat('fr-FR').format(n) + ' FCFA'
const formatDateToday = () => new Date().toLocaleDateString('fr-FR',{weekday:'long',day:'2-digit',month:'long',year:'numeric'})
const formatDate = (d) => {
  if (!d) return ''
  const dt = new Date(d), now = new Date()
  const diff = Math.floor((now-dt)/1000)
  if (diff < 60) return 'à l\'instant'
  if (diff < 3600) return `il y a ${Math.floor(diff/60)}m`
  if (diff < 86400) return `il y a ${Math.floor(diff/3600)}h`
  if (diff < 604800) return `il y a ${Math.floor(diff/86400)}j`
  return dt.toLocaleDateString('fr-FR',{day:'2-digit',month:'short',year:'numeric'})
}
const getColor = (index) => CARD_COLORS[index % CARD_COLORS.length]
const adjustColor = (color, amount) => {
  const num = parseInt(color.replace('#',''),16)
  const r = Math.max(0,Math.min(255,(num>>16)+amount))
  const g = Math.max(0,Math.min(255,((num>>8)&0xFF)+amount))
  const b = Math.max(0,Math.min(255,(num&0xFF)+amount))
  return '#'+((r<<16)|(g<<8)|b).toString(16).padStart(6,'0')
}

// ─── API ────────────────────────────────────────────────────────────
const loadPdv = async () => {
  loading.value = true
  error.value = null
  try {
    const res = await getPointsVente()
    pdvList.value = Array.isArray(res.data.data) ? res.data.data : []
  } catch(e) {
    error.value = e.response?.data?.error || 'Impossible de charger les points de vente'
  } finally {
    loading.value = false
  }
}

const savePdv = async () => {
  saving.value = true
  try {
    const payload = { ...form }
    let res
    if (editingPdv.value && editingPdv.value.id_pdv) {
      payload.id_pdv = editingPdv.value.id_pdv
      res = await updatePointVente(payload)
    } else {
      res = await addPointVente(payload)
    }
    if (res.data.success) {
      await loadPdv();
      closeModal()
    }
  } catch(e) {
    alert('Erreur serveur')
  } finally {
    saving.value = false
  }
}

const deletePdv = async (id_pdv) => {
  if (!confirm('Supprimer ce point de vente ?')) return
  try {
    const res = await deletePointVente(id_pdv)
    if (res.data.success) {
      await loadPdv()
    }
  } catch(e) { alert('Erreur lors de la suppression') }
}

const goToDetails = (pdv) => {
  pdvSelectionStore.setSelectedPdvId(pdv.id_pdv)
  router.push({ name: 'points_de_vente_details' })
}

const toggleStatut = async (id) => {
  const pdv = pdvList.value.find(p => p.id === id)
  if (!pdv) return
  pdv.statut = pdv.statut === 'actif' ? 'inactif' : 'actif'
  // await togglePdvStatut(id)
}

const cloturerJournee = (pdv) => {
  cloturePdv.value = pdv
  clotureForm.montant_encaisse = pdv.a_encaisser || 0
  clotureForm.observations = ''
  showCloture.value = true
}

const confirmerCloture = async () => {
  saving.value = true
  try {
    // await cloturerJourneePdv({ id: cloturePdv.value.id, ...clotureForm })
    const pdv = pdvList.value.find(p => p.id === cloturePdv.value.id)
    if (pdv) { pdv.a_encaisser = 0; pdv.derniere_cloture = new Date().toISOString() }
    showCloture.value = false
  } catch(e) { alert('Erreur lors de la clôture') } finally { saving.value = false }
}

const openCreateModal = () => { editingPdv.value = null; resetForm(); showModal.value = true }
const editPdv = (pdv) => {
  editingPdv.value = pdv
  Object.assign(form, {
    nom: pdv.nom, ville: pdv.ville, quartier: pdv.quartier||'', adresse: pdv.adresse||'',
    responsable: pdv.responsable, contact: pdv.contact, email: pdv.email||'',
    statut: pdv.statut, objectif_jour: pdv.objectif_jour||0,
    type_pdv: pdv.type_pdv||'boutique', notes: pdv.notes||''
  })
  showModal.value = true
}
const closeModal = () => { showModal.value = false; editingPdv.value = null; resetForm() }
const resetForm = () => Object.assign(form, { nom:'', ville:'', quartier:'', adresse:'', responsable:'', contact:'', email:'', statut:'actif', objectif_jour:0, type_pdv:'boutique', notes:'' })

onMounted(loadPdv)

// ─── Styles ─────────────────────────────────────────────────────────
const headerStyle = { display:'flex', justifyContent:'space-between', alignItems:'flex-start', marginBottom:'28px', flexWrap:'wrap', gap:'20px' }
const headerContentStyle = { display:'flex', flexDirection:'column', gap:'6px' }
const breadcrumbStyle = { display:'flex', alignItems:'center', gap:'6px', marginBottom:'4px' }
const breadcrumbItemStyle = { fontSize:'13px', color:'#94a3b8', fontWeight:'500' }
const breadcrumbActiveStyle = { fontSize:'13px', color:'#1e293b', fontWeight:'600' }
const titleStyle = { fontSize:'32px', fontWeight:'800', color:'#0f172a', margin:'0', letterSpacing:'-0.03em' }
const subtitleStyle = { fontSize:'14px', color:'#64748b', margin:'0', display:'flex', alignItems:'center', gap:'6px', fontWeight:'500' }

const filtersContainerStyle = { display:'flex', flexWrap:'wrap', gap:'8px', marginTop:'12px' }
const getFilterBtnStyle = (key) => ({
  display:'flex', alignItems:'center', gap:'7px', padding:'7px 14px',
  borderRadius:'10px', border: activeFilter.value===key ? 'none' : '1px solid #e2e8f0',
  background: activeFilter.value===key ? 'linear-gradient(135deg, #0ea5e9, #0284c7)' : 'white',
  color: activeFilter.value===key ? 'white' : '#64748b',
  fontSize:'12px', fontWeight:'600', cursor:'pointer',
  boxShadow: activeFilter.value===key ? '0 4px 12px rgba(14,165,233,0.25)' : 'none',
  transition:'all 0.2s'
})
const filterDotStyle = (key) => ({
  width:'6px', height:'6px', borderRadius:'50%',
  background: key==='actif' ? '#10b981' : key==='inactif' ? '#ef4444' : '#94a3b8'
})

const addButtonStyle = computed(() => ({
  display:'flex', alignItems:'center', gap:'10px', padding:'13px 22px',
  background: addHovered.value ? 'linear-gradient(135deg,#0284c7,#0369a1)' : 'linear-gradient(135deg,#0ea5e9,#0284c7)',
  color:'white', border:'none', borderRadius:'12px', cursor:'pointer', fontSize:'14px', fontWeight:'700',
  transition:'all 0.3s cubic-bezier(0.4,0,0.2,1)',
  boxShadow: addHovered.value ? '0 12px 28px rgba(14,165,233,0.35)' : '0 4px 12px rgba(14,165,233,0.25)',
  transform: addHovered.value ? 'translateY(-2px)' : 'translateY(0)'
}))
const addButtonIconStyle = { width:'32px', height:'32px', background:'rgba(255,255,255,0.2)', borderRadius:'8px', display:'flex', alignItems:'center', justifyContent:'center' }

const statsRowStyle = { display:'grid', gridTemplateColumns:'repeat(auto-fit,minmax(180px,1fr))', gap:'14px', marginBottom:'24px' }
const statCardStyle = { background:'white', borderRadius:'14px', padding:'18px', display:'flex', alignItems:'center', gap:'14px', boxShadow:'0 1px 3px rgba(0,0,0,0.05)', border:'1px solid #f1f5f9' }
const statIconStyle = (color) => ({ width:'44px', height:'44px', background:`${color}18`, borderRadius:'10px', display:'flex', alignItems:'center', justifyContent:'center', color, flexShrink:0 })
const statValueStyle = { fontSize:'22px', fontWeight:'800', color:'#0f172a', lineHeight:'1.2', letterSpacing:'-0.02em' }
const statLabelStyle = { fontSize:'12px', color:'#64748b', fontWeight:'500', marginTop:'2px' }

const searchWrapperStyle = { marginBottom:'22px' }
const searchInnerStyle = { position:'relative', display:'flex', alignItems:'center' }
const searchInputStyle = { width:'100%', padding:'13px 44px 13px 48px', border:'1px solid #e2e8f0', borderRadius:'12px', fontSize:'14px', fontWeight:'500', color:'#1e293b', background:'white', boxSizing:'border-box', outline:'none' }
const searchClearStyle = { position:'absolute', right:'12px', width:'26px', height:'26px', borderRadius:'7px', border:'none', background:'#f1f5f9', color:'#64748b', cursor:'pointer', display:'flex', alignItems:'center', justifyContent:'center' }

const loadingStyle = { textAlign:'center', padding:'60px 20px', color:'#64748b' }
const spinnerStyle = { width:'44px', height:'44px', margin:'0 auto 16px', border:'4px solid #f1f5f9', borderTop:'4px solid #0ea5e9', borderRadius:'50%', animation:'pdv-spin 1s linear infinite' }
const errorStyle = { textAlign:'center', padding:'60px 20px', color:'#ef4444' }
const retryBtnStyle = { marginTop:'16px', padding:'11px 24px', background:'#0ea5e9', color:'white', border:'none', borderRadius:'8px', cursor:'pointer', fontSize:'14px', fontWeight:'600' }
const emptyStateStyle = { textAlign:'center', padding:'80px 20px', color:'#94a3b8' }
const emptyTitleStyle = { fontSize:'18px', fontWeight:'700', color:'#475569', margin:'16px 0 8px' }
const emptySubStyle = { fontSize:'14px', color:'#94a3b8', margin:'0' }

const gridStyle = { display:'grid', gridTemplateColumns:'repeat(auto-fill,minmax(340px,1fr))', gap:'20px' }

const getCardStyle = (index) => ({
  backgroundColor:'white',
  borderRadius:'20px',
  padding:'0',
  boxShadow: hoveredCard.value===index ? '0 20px 40px rgba(0,0,0,0.1)' : '0 2px 8px rgba(0,0,0,0.06)',
  cursor:'pointer',
  transition:'all 0.35s cubic-bezier(0.4,0,0.2,1)',
  transform: hoveredCard.value===index ? 'translateY(-5px)' : 'translateY(0)',
  border: hoveredCard.value===index ? '1px solid #e2e8f0' : '1px solid #f1f5f9',
  overflow:'hidden'
})

const getCardAccentStyle = (index) => {
  const c = getColor(index)
  return { height:'4px', background:`linear-gradient(90deg,${c},${adjustColor(c,30)})` }
}

const cardHeaderStyle = { display:'flex', justifyContent:'space-between', alignItems:'flex-start', padding:'24px 24px 0' }
const getStoreIconStyle = (index) => {
  const c = getColor(index)
  return { width:'52px', height:'52px', borderRadius:'14px', background:`${c}18`, display:'flex', alignItems:'center', justifyContent:'center', color:c, flexShrink:0 }
}
const cardActionsStyle = { display:'flex', gap:'6px' }
const getActionBtnStyle = (type, index) => {
  const hovered = hoveredAction.value === `${type}-${index}`
  const colors = { edit:['#eff6ff','#3b82f6','#bfdbfe'], cloturer:['#fffbeb','#f59e0b','#fde68a'], delete:['#fef2f2','#ef4444','#fecaca'] }
  const [bg,text,border] = colors[type]||colors.edit
  return { width:'34px', height:'34px', borderRadius:'9px', border:`1px solid ${hovered?border:'#e2e8f0'}`, background:hovered?bg:'white', color:hovered?text:'#94a3b8', cursor:'pointer', display:'flex', alignItems:'center', justifyContent:'center', transition:'all 0.2s' }
}

const cardNameStyle = { fontSize:'18px', fontWeight:'800', color:'#0f172a', margin:'16px 24px 4px', letterSpacing:'-0.02em' }
const cardVilleStyle = { fontSize:'13px', color:'#94a3b8', margin:'0 24px 16px', display:'flex', alignItems:'center', gap:'5px', fontWeight:'500' }

const responsableBoxStyle = { display:'flex', justifyContent:'space-between', alignItems:'center', margin:'0 24px 16px', padding:'14px', background:'#f8fafc', borderRadius:'12px' }
const responsableInfoStyle = { display:'flex', alignItems:'center', gap:'10px' }
const responsableAvatarStyle = (index) => {
  const c = getColor(index)
  return { width:'36px', height:'36px', borderRadius:'10px', background:`${c}20`, color:c, display:'flex', alignItems:'center', justifyContent:'center', fontSize:'13px', fontWeight:'800', flexShrink:0 }
}
const responsableNameStyle = { fontSize:'14px', fontWeight:'700', color:'#0f172a' }
const responsableRoleStyle = { fontSize:'11px', color:'#94a3b8', fontWeight:'500', marginTop:'1px' }
const contactBtnStyle = { display:'flex', alignItems:'center', gap:'5px', padding:'6px 12px', background:'white', border:'1px solid #e2e8f0', borderRadius:'8px', fontSize:'12px', fontWeight:'600', color:'#0ea5e9', textDecoration:'none', transition:'all 0.2s' }

const venteStatsStyle = { display:'grid', gridTemplateColumns:'1fr 1px 1fr 1px 1fr', gap:'0', margin:'0 24px', padding:'14px 0', borderTop:'1px solid #f1f5f9' }
const venteStatItemStyle = () => ({ display:'flex', flexDirection:'column', gap:'3px', alignItems:'center' })
const venteStatLabelStyle = { fontSize:'11px', color:'#94a3b8', fontWeight:'600', letterSpacing:'0.03em', textTransform:'uppercase' }
const venteStatValueStyle = (color) => ({ fontSize:'14px', fontWeight:'800', color, letterSpacing:'-0.01em' })
const vDividerStyle = { background:'#f1f5f9' }

const encaisserAlertStyle = { display:'flex', alignItems:'center', gap:'8px', margin:'12px 24px 0', padding:'10px 14px', background:'linear-gradient(135deg,#fef2f2,#fff7ed)', border:'1px solid #fecaca', borderRadius:'10px', fontSize:'12px', fontWeight:'700', color:'#b91c1c' }

const cardFooterStyle = { display:'flex', justifyContent:'space-between', alignItems:'center', padding:'14px 24px 20px', marginTop:'12px', borderTop:'1px solid #f8fafc' }
const getStatusToggleStyle = (isActif) => ({ display:'inline-flex', alignItems:'center', gap:'6px', padding:'5px 12px', borderRadius:'20px', fontSize:'12px', fontWeight:'700', background:isActif?'#dcfce7':'#fee2e2', color:isActif?'#166534':'#991b1b', border:'none', cursor:'pointer', transition:'all 0.2s' })
const dotStyle = (isActif) => ({ width:'6px', height:'6px', borderRadius:'50%', background:isActif?'#22c55e':'#ef4444' })
const dateStyle = { fontSize:'11px', color:'#cbd5e1', fontWeight:'500' }

// Modal styles
const overlayStyle = { position:'fixed', top:0, left:0, right:0, bottom:0, background:'rgba(15,23,42,0.75)', backdropFilter:'blur(8px)', display:'flex', alignItems:'center', justifyContent:'center', zIndex:1000, padding:'20px', overflowY:'auto' }
const modalStyle = { background:'white', borderRadius:'24px', width:'100%', maxWidth:'680px', maxHeight:'90vh', overflow:'auto', boxShadow:'0 25px 50px -12px rgba(0,0,0,0.25)' }
const modalHeaderStyle = { display:'flex', justifyContent:'space-between', alignItems:'flex-start', padding:'28px 32px 22px', borderBottom:'1px solid #f1f5f9' }
const modalBadgeStyle = { width:'38px', height:'38px', background:'linear-gradient(135deg,#0ea5e9,#0284c7)', borderRadius:'10px', display:'flex', alignItems:'center', justifyContent:'center', color:'white', marginBottom:'14px' }
const modalTitleStyle = { fontSize:'22px', fontWeight:'800', color:'#0f172a', margin:'0 0 4px', letterSpacing:'-0.02em' }
const modalSubStyle = { fontSize:'13px', color:'#64748b', margin:0, fontWeight:'500' }
const closeBtnStyle = { background:'none', border:'none', cursor:'pointer', color:'#94a3b8', padding:'8px', borderRadius:'8px', transition:'all 0.2s' }
const formStyle = { padding:'22px 32px 30px' }
const sectionTitleStyle = { display:'flex', alignItems:'center', gap:'8px', fontSize:'15px', fontWeight:'700', color:'#0f172a', marginBottom:'16px', marginTop:'20px', paddingBottom:'10px', borderBottom:'2px solid #f1f5f9' }
const rowStyle = { display:'flex', gap:'14px' }
const groupStyle = { marginBottom:'18px', flex:1 }
const labelStyle = { display:'flex', alignItems:'center', gap:'4px', fontSize:'13px', fontWeight:'700', color:'#334155', marginBottom:'7px' }
const reqStyle = { color:'#ef4444' }
const inputStyle = { width:'100%', padding:'11px 14px', border:'1px solid #e2e8f0', borderRadius:'10px', fontSize:'14px', boxSizing:'border-box', fontWeight:'500', color:'#1e293b', outline:'none', transition:'all 0.2s' }
const actionsStyle = { display:'flex', gap:'12px', marginTop:'24px', paddingTop:'20px', borderTop:'1px solid #f1f5f9' }
const cancelBtnStyle = { flex:1, padding:'13px', background:'#f8fafc', border:'1px solid #e2e8f0', borderRadius:'10px', fontSize:'14px', fontWeight:'700', color:'#64748b', cursor:'pointer' }
const submitBtnStyle = { flex:1, padding:'13px', background:'linear-gradient(135deg,#0ea5e9,#0284c7)', border:'none', borderRadius:'10px', fontSize:'14px', fontWeight:'700', color:'white', cursor:'pointer', display:'flex', alignItems:'center', justifyContent:'center', gap:'8px', boxShadow:'0 4px 12px rgba(14,165,233,0.25)' }
const btnSpinnerStyle = { width:'16px', height:'16px', border:'2px solid rgba(255,255,255,0.3)', borderTop:'2px solid white', borderRadius:'50%', animation:'pdv-spin 0.8s linear infinite' }

// Clôture modal styles
const clotureResStyle = { display:'grid', gridTemplateColumns:'1fr 1fr', gap:'12px', marginBottom:'20px' }
const clotureResItemStyle = (color) => ({ padding:'14px', background:`${color}12`, borderRadius:'12px', border:`1px solid ${color}30` })
const clotureResLabelStyle = { fontSize:'11px', color:'#64748b', fontWeight:'600', textTransform:'uppercase', letterSpacing:'0.05em', display:'block', marginBottom:'6px' }
const clotureResValStyle = (color) => ({ fontSize:'18px', fontWeight:'800', color, letterSpacing:'-0.02em' })
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

* { font-family: 'Plus Jakarta Sans', -apple-system, sans-serif; }

@keyframes pdv-fadein {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: translateY(0); }
}
@keyframes pdv-spin {
  to { transform: rotate(360deg); }
}

.pdv-fade-in { animation: pdv-fadein 0.5s cubic-bezier(0.16,1,0.3,1) backwards; }

.pdv-card {
  position: relative;
  overflow: hidden;
}
.pdv-card::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(255,255,255,0) 40%, rgba(255,255,255,0.35) 50%, rgba(255,255,255,0) 60%);
  transform: translateX(-100%) skewX(-15deg);
  transition: transform 0.7s;
  pointer-events: none;
}
.pdv-card:hover::after {
  transform: translateX(150%) skewX(-15deg);
}

.pdv-modal-enter-active, .pdv-modal-leave-active {
  transition: all 0.3s cubic-bezier(0.16,1,0.3,1);
}
.pdv-modal-enter-from, .pdv-modal-leave-to {
  opacity: 0;
}
.pdv-modal-enter-from .pdv-modal-content,
.pdv-modal-leave-to .pdv-modal-content {
  transform: scale(0.95) translateY(20px);
}
.pdv-modal-content {
  transition: all 0.3s cubic-bezier(0.16,1,0.3,1);
}
</style>