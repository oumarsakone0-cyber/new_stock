<template>
  <SidebarLayout currentPage="fournisseurs">
    <!-- Loading -->
    <div v-if="loading" :style="loadingContainerStyle">
      <div :style="spinnerStyle"></div>
      <p :style="loadingTextStyle">Chargement...</p>
    </div>

    <!-- Main Content -->
    <div v-if="!loading && fournisseur">
      <!-- Header -->
      <header :style="headerStyle" class="fade-in">
        <div :style="headerLeftStyle">
          <button :style="backButtonStyle" @click="goBack">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            Retour
          </button>
          <div>
            <h1 :style="titleStyle">{{ fournisseur.nom }}</h1>
            <p v-if="fournisseur.nom_entreprise" :style="entrepriseStyle">{{ fournisseur.nom_entreprise }}</p>
            <div :style="contactsStyle">
              <span v-if="fournisseur.telephone" :style="contactItemStyle">📞 {{ fournisseur.telephone }}</span>
              <span v-if="fournisseur.telephone2" :style="contactItemStyle">📞2 {{ fournisseur.telephone2 }}</span>
              <span v-if="fournisseur.email" :style="contactItemStyle">✉️ {{ fournisseur.email }}</span>
              <span v-if="fournisseur.tel_commercial" :style="contactItemStyle">📞 Commercial: {{ fournisseur.tel_commercial }}</span>
              <span v-if="fournisseur.nom_commercial" :style="contactItemStyle">👤 Nom commercial: {{ fournisseur.nom_commercial }}</span>
            </div>
          </div>
        </div>
        <span :style="getStatusBadgeStyle(fournisseur.statut)">{{ fournisseur.statut }}</span>
      </header>

      <!-- Stats Cards -->
      <div :style="statsGridStyle" class="fade-in">
        <div :style="statsCardStyle('#3b82f6')">
          <div :style="statsIconStyle('#3b82f6')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <rect x="1" y="4" width="22" height="16" rx="2"/>
              <line x1="1" y1="10" x2="23" y2="10"/>
            </svg>
          </div>
          <div>
            <p :style="statsLabelStyle">Produits catalogue</p>
            <p :style="statsValueStyle">{{ stats.nombre_produits || 0 }}</p>
          </div>
        </div>

        <div :style="statsCardStyle('#f59e0b')">
          <div :style="statsIconStyle('#f59e0b')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <circle cx="12" cy="12" r="10"/>
              <polyline points="12 6 12 12 16 14"/>
            </svg>
          </div>
          <div>
            <p :style="statsLabelStyle">Commandes</p>
            <p :style="statsValueStyle">{{ stats.nombre_commandes || 0 }}</p>
          </div>
        </div>

        <div :style="statsCardStyle('#10b981')">
          <div :style="statsIconStyle('#10b981')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <line x1="12" y1="1" x2="12" y2="23"/>
              <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
            </svg>
          </div>
          <div>
            <p :style="statsLabelStyle">Total achats</p>
            <p :style="statsValueStyle">{{ formatMoney(stats.total_achats || 0) }}</p>
          </div>
        </div>

        <div :style="statsCardStyle('#ef4444')">
          <div :style="statsIconStyle('#ef4444')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <circle cx="12" cy="12" r="10"/>
              <line x1="15" y1="9" x2="9" y2="15"/>
              <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
          </div>
          <div>
            <p :style="statsLabelStyle">Dette actuelle</p>
            <p :style="statsValueStyle">{{ formatMoney(stats.total_dettes || 0) }}</p>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div :style="tabsContainerStyle" class="fade-in">
        <div :style="tabsHeaderStyle">
          <button 
            v-for="tab in tabs" 
            :key="tab.id"
            :style="getTabStyle(tab.id)"
            @click="activeTab = tab.id"
          >
            {{ tab.label }}
          </button>
        </div>

        <div :style="tabContentStyle">
          <!-- TAB 1: PRODUITS -->
          <div v-if="activeTab === 'produits'">
            <div :style="tabHeaderStyle">
              <h3 :style="tabTitleStyle">Catalogue produits</h3>
              <button :style="addButtonStyle" @click="showProduitModal = true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M12 5v14M5 12h14"/>
                </svg>
                Nouveau produit
              </button>
            </div>

            <div :style="tableWrapperStyle">
              <table :style="tableStyle">
                <thead>
                  <tr>
                    <th :style="thStyle">Référence</th>
                    <th :style="thStyle">Produit</th>
                    <th :style="thStyle">Catégorie</th>
                    <th :style="thStyle">Prix unitaire</th>
                    <th :style="thStyle">Remise</th>
                    <th :style="thStyle">Prix net</th>
                    <th :style="thStyle">Unité</th>
                    <th :style="thStyle">Disponibilité</th>
                    <th :style="thStyle">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="produit in produits" :key="produit.id" :style="trStyle">
                    <td :style="tdStyle">
                      <span :style="refStyle">{{ produit.reference_fournisseur || 'N/A' }}</span>
                    </td>
                    <td :style="tdStyle">
                      <p :style="produitNameStyle">{{ produit.nom_produit }}</p>
                      <p v-if="produit.description" :style="produitDescStyle">{{ produit.description }}</p>
                    </td>
                    <td :style="tdStyle">{{ produit.categorie || 'N/A' }}</td>
                    <td :style="tdStyle">
                      <span :style="priceStyle">{{ formatMoney(produit.prix_unitaire) }}</span>
                    </td>
                    <td :style="tdStyle">
                      <span :style="remiseStyle">{{ produit.remise_pourcentage }}%</span>
                    </td>
                    <td :style="tdStyle">
                      <span :style="priceNetStyle">{{ formatMoney(produit.prix_unitaire_remise) }}</span>
                    </td>
                    <td :style="tdStyle">{{ produit.unite_mesure }}</td>
                    <td :style="tdStyle">
                      <span :style="getDispoBadgeStyle(produit.disponibilite)">
                        {{ produit.disponibilite }}
                      </span>
                    </td>
                    <td :style="tdStyle">
                      <div :style="actionsStyle">
                        <button 
                          :style="actionButtonStyle('#10b981')" 
                          @click="editProduit(produit)"
                          title="Modifier"
                        >
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                          </svg>
                        </button>
                        <button 
                          :style="actionButtonStyle('#ef4444')" 
                          @click="deleteProduit(produit.id)"
                          title="Supprimer"
                        >
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div v-if="produits.length === 0" :style="emptyStateStyle">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2">
                <rect x="1" y="4" width="22" height="16" rx="2"/>
              </svg>
              <p :style="emptyTextStyle">Aucun produit dans le catalogue</p>
            </div>
          </div>

          <!-- TAB 2: COMMANDES -->
          <div v-if="activeTab === 'commandes'">
            <div :style="tabHeaderStyle">
              <h3 :style="tabTitleStyle">Commandes</h3>
              <button :style="addButtonStyle" @click="showCommandeModal = true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M12 5v14M5 12h14"/>
                </svg>
                Nouvelle commande
              </button>
            </div>

            <div :style="tableWrapperStyle">
              <table :style="tableStyle">
                <thead>
                  <tr>
                    <th :style="thStyle">N° Commande</th>
                    <th :style="thStyle">Date</th>
                    <th :style="thStyle">Livraison prévue</th>
                    <th :style="thStyle">Montant total</th>
                    <th :style="thStyle">Payé</th>
                    <th :style="thStyle">Reste</th>
                    <th :style="thStyle">Statut</th>
                    <th :style="thStyle">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="commande in commandes" :key="commande.id" :style="trStyle">
                    <td :style="tdStyle">
                      <span :style="commandeNumStyle">{{ commande.numero_commande }}</span>
                    </td>
                    <td :style="tdStyle">{{ formatDate(commande.date_commande) }}</td>
                    <td :style="tdStyle">{{ formatDate(commande.date_livraison_prevue) }}</td>
                    <td :style="tdStyle">
                      <span :style="priceStyle">{{ formatMoney(commande.montant_total) }}</span>
                    </td>
                    <td :style="tdStyle">
                      <span :style="paidStyle">{{ formatMoney(commande.montant_paye) }}</span>
                    </td>
                    <td :style="tdStyle">
                      <span :style="debtStyle(commande.montant_restant)">
                        {{ formatMoney(commande.montant_restant) }}
                      </span>
                    </td>
                    <td :style="tdStyle">
                      <span :style="getCommandeStatutBadgeStyle(commande.statut)">
                        {{ commande.statut }}
                      </span>
                    </td>
                    <td :style="tdStyle">
                      <div :style="actionsStyle">
                        <button 
                          v-if="commande.montant_restant > 0"
                          :style="actionButtonStyle('#10b981')" 
                          @click="enregistrerPaiement(commande)"
                          title="Paiement"
                        >
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="1" x2="12" y2="23"/>
                            <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div v-if="commandes.length === 0" :style="emptyStateStyle">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
              </svg>
              <p :style="emptyTextStyle">Aucune commande</p>
            </div>
          </div>

          <!-- TAB 3: RAPPORT -->
          <div v-if="activeTab === 'rapport'">
            <div :style="tabHeaderStyle">
              <h3 :style="tabTitleStyle">Rapport fournisseur</h3>
            </div>
            <p :style="{fontSize: '14px', color: '#64748b'}">Composant rapport à implémenter</p>
          </div>
        </div>
      </div>

      <!-- Modal Produit -->
      <Transition name="modal">
        <div v-if="showProduitModal" :style="modalOverlayStyle" @click.self="closeProduitModal">
          <div :style="modalStyle" class="modal-content">
            <div :style="modalHeaderStyle">
              <div>
                <h2 :style="modalTitleStyle">{{ editingProduit ? 'Modifier' : 'Nouveau' }} produit</h2>
                <p :style="modalSubtitleStyle">Informations du produit</p>
              </div>
              <button :style="closeButtonStyle" @click="closeProduitModal">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
              </button>
            </div>
            
            <div :style="modalContentStyle">
              <div :style="formGridStyle">
                <div :style="formGroupStyle">
                  <label :style="labelStyle">Référence fournisseur</label>
                  <input v-model="formProduit.reference_fournisseur" type="text" :style="inputStyle" />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Nom produit *</label>
                  <input v-model="formProduit.nom_produit" type="text" :style="inputStyle" required />
                </div>

                <div :style="{...formGroupStyle, gridColumn: '1 / -1'}">
                  <label :style="labelStyle">Description</label>
                  <textarea v-model="formProduit.description" :style="textareaStyle" rows="2"></textarea>
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Catégorie</label>
                  <input v-model="formProduit.categorie" type="text" :style="inputStyle" />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Prix unitaire *</label>
                  <input v-model.number="formProduit.prix_unitaire" type="number" :style="inputStyle" required />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Remise (%)</label>
                  <input v-model.number="formProduit.remise_pourcentage" type="number" step="0.1" :style="inputStyle" />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Prix net</label>
                  <input 
                    :value="formatMoney(calculerPrixNet())" 
                    type="text" 
                    :style="{...inputStyle, backgroundColor: '#f8fafc', fontWeight: '700'}" 
                    disabled 
                  />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Quantité minimum</label>
                  <input v-model.number="formProduit.quantite_minimum" type="number" :style="inputStyle" />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Unité de mesure</label>
                  <input v-model="formProduit.unite_mesure" type="text" :style="inputStyle" placeholder="Ex: Kg, Carton" />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Disponibilité</label>
                  <select v-model="formProduit.disponibilite" :style="selectStyle">
                    <option value="En stock">En stock</option>
                    <option value="Sur commande">Sur commande</option>
                    <option value="Indisponible">Indisponible</option>
                  </select>
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Délai appro (jours)</label>
                  <input v-model.number="formProduit.delai_approvisionnement" type="number" :style="inputStyle" />
                </div>
              </div>

              <div :style="modalActionsStyle">
                <button :style="cancelButtonStyle" @click="closeProduitModal">Annuler</button>
                <button :style="saveButtonStyle" @click="saveProduit" :disabled="saving">
                  <div v-if="saving" :style="smallSpinnerStyle"></div>
                  <span v-else>{{ editingProduit ? 'Mettre à jour' : 'Créer' }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>

      <!-- Modal Commande -->
      <Transition name="modal">
        <div v-if="showCommandeModal" :style="modalOverlayStyle" @click.self="closeCommandeModal">
          <div :style="modalStyle" class="modal-content">
            <div :style="modalHeaderStyle">
              <div>
                <h2 :style="modalTitleStyle">Nouvelle commande</h2>
                <p :style="modalSubtitleStyle">Créer une commande pour {{ fournisseur.nom }}</p>
              </div>
              <button :style="closeButtonStyle" @click="closeCommandeModal">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
              </button>
            </div>
            
            <div :style="modalContentStyle">
              <div :style="formGridStyle">
                <div :style="formGroupStyle">
                  <label :style="labelStyle">N° Commande *</label>
                  <input v-model="formCommande.numero_commande" type="text" :style="inputStyle" required />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Date commande *</label>
                  <input v-model="formCommande.date_commande" type="date" :style="inputStyle" required />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Livraison prévue</label>
                  <input v-model="formCommande.date_livraison_prevue" type="date" :style="inputStyle" />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Montant total *</label>
                  <input v-model.number="formCommande.montant_total" type="number" :style="inputStyle" required />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Mode de paiement</label>
                  <select v-model="formCommande.mode_paiement" :style="selectStyle">
                    <option value="Crédit">Crédit</option>
                    <option value="Espèces">Espèces</option>
                    <option value="Mobile Money">Mobile Money</option>
                    <option value="Virement">Virement</option>
                    <option value="Chèque">Chèque</option>
                  </select>
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Échéance paiement</label>
                  <input v-model="formCommande.date_echeance_paiement" type="date" :style="inputStyle" />
                </div>

                <div :style="{...formGroupStyle, gridColumn: '1 / -1'}">
                  <label :style="labelStyle">Conditions de paiement</label>
                  <input v-model="formCommande.conditions_paiement" type="text" :style="inputStyle" placeholder="Ex: 30 jours fin de mois" />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Responsable</label>
                  <input v-model="formCommande.responsable" type="text" :style="inputStyle" />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Frais de livraison</label>
                  <input v-model.number="formCommande.frais_livraison" type="number" :style="inputStyle" />
                </div>

                <div :style="{...formGroupStyle, gridColumn: '1 / -1'}">
                  <label :style="labelStyle">Observation</label>
                  <textarea v-model="formCommande.observation" :style="textareaStyle" rows="2"></textarea>
                </div>
              </div>

              <div :style="modalActionsStyle">
                <button :style="cancelButtonStyle" @click="closeCommandeModal">Annuler</button>
                <button :style="saveButtonStyle" @click="saveCommande" :disabled="saving">
                  <div v-if="saving" :style="smallSpinnerStyle"></div>
                  <span v-else>Créer</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>

      <!-- Modal Paiement -->
      <Transition name="modal">
        <div v-if="showPaiementModal" :style="modalOverlayStyle" @click.self="closePaiementModal">
          <div :style="{...modalStyle, maxWidth: '500px'}" class="modal-content">
            <div :style="modalHeaderStyle">
              <div>
                <h2 :style="modalTitleStyle">Enregistrer un paiement</h2>
                <p :style="modalSubtitleStyle">Commande {{ selectedCommande?.numero_commande }}</p>
              </div>
              <button :style="closeButtonStyle" @click="closePaiementModal">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
              </button>
            </div>
            
            <div :style="modalContentStyle">
              <div :style="paiementInfoStyle">
                <div :style="paiementInfoRowStyle">
                  <span>Montant total:</span>
                  <span :style="boldStyle">{{ formatMoney(selectedCommande?.montant_total || 0) }}</span>
                </div>
                <div :style="paiementInfoRowStyle">
                  <span>Déjà payé:</span>
                  <span :style="boldStyle">{{ formatMoney(selectedCommande?.montant_paye || 0) }}</span>
                </div>
                <div :style="{...paiementInfoRowStyle, ...paiementRestantStyle}">
                  <span>Reste dû:</span>
                  <span :style="{...boldStyle, color: '#ef4444'}">{{ formatMoney(selectedCommande?.montant_restant || 0) }}</span>
                </div>
              </div>

              <div :style="formGridStyle">
                <div :style="{...formGroupStyle, gridColumn: '1 / -1'}">
                  <label :style="labelStyle">Montant payé *</label>
                  <input v-model.number="formPaiement.montant_paye" type="number" :style="inputStyle" required />
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Mode de paiement *</label>
                  <select v-model="formPaiement.mode_paiement" :style="selectStyle" required>
                    <option value="Espèces">Espèces</option>
                    <option value="Mobile Money">Mobile Money</option>
                    <option value="Virement">Virement</option>
                    <option value="Chèque">Chèque</option>
                  </select>
                </div>

                <div :style="formGroupStyle">
                  <label :style="labelStyle">Date paiement *</label>
                  <input v-model="formPaiement.date_paiement" type="date" :style="inputStyle" required />
                </div>

                <div :style="{...formGroupStyle, gridColumn: '1 / -1'}">
                  <label :style="labelStyle">Référence paiement</label>
                  <input v-model="formPaiement.reference_paiement" type="text" :style="inputStyle" placeholder="N° transaction, chèque..." />
                </div>

                <div :style="{...formGroupStyle, gridColumn: '1 / -1'}">
                  <label :style="labelStyle">Payeur</label>
                  <input v-model="formPaiement.payeur" type="text" :style="inputStyle" />
                </div>

                <div :style="{...formGroupStyle, gridColumn: '1 / -1'}">
                  <label :style="labelStyle">Observation</label>
                  <textarea v-model="formPaiement.observation" :style="textareaStyle" rows="2"></textarea>
                </div>
              </div>

              <div :style="modalActionsStyle">
                <button :style="cancelButtonStyle" @click="closePaiementModal">Annuler</button>
                <button :style="saveButtonStyle" @click="savePaiement" :disabled="saving">
                  <div v-if="saving" :style="smallSpinnerStyle"></div>
                  <span v-else>Enregistrer</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </div>
  </SidebarLayout>
</template>

<script setup>

import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import SidebarLayout from '../SidebarLayout.vue'
import api from '../../services/api.js'

const router = useRouter()
const route = useRoute()
const fournisseurId = route.params.id

// State
const loading = ref(false)
const saving = ref(false)
const fournisseur = ref(null)
const stats = ref({})
const produits = ref([])
const commandes = ref([])
const activeTab = ref('produits')
const showProduitModal = ref(false)
const showCommandeModal = ref(false)
const showPaiementModal = ref(false)
const editingProduit = ref(null)
const selectedCommande = ref(null)

const tabs = [
  { id: 'produits', label: 'Produits' },
  { id: 'commandes', label: 'Commandes' },
  { id: 'rapport', label: 'Rapport' }
]

const formProduit = ref({
  reference_fournisseur: '',
  nom_produit: '',
  description: '',
  categorie: '',
  prix_unitaire: 0,
  remise_pourcentage: 0,
  quantite_minimum: 1,
  unite_mesure: 'Unité',
  disponibilite: 'En stock',
  delai_approvisionnement: null,
  statut: 'Actif'
})

const formCommande = ref({
  numero_commande: '',
  date_commande: new Date().toISOString().split('T')[0],
  date_livraison_prevue: '',
  montant_total: 0,
  statut: 'Brouillon',
  mode_paiement: 'Crédit',
  conditions_paiement: '',
  date_echeance_paiement: '',
  adresse_livraison: '',
  frais_livraison: 0,
  responsable: '',
  observation: ''
})

const formPaiement = ref({
  montant_paye: 0,
  mode_paiement: 'Espèces',
  reference_paiement: '',
  date_paiement: new Date().toISOString().split('T')[0],
  payeur: '',
  observation: ''
})

const randomParam = () => `&_=${Date.now()}_${Math.floor(Math.random() * 100000)}`

// Methods
const loadFournisseur = async () => {
  try {
    const response = await api.get(`api_fournisseurs.php?action=get_fournisseur&id=${fournisseurId}`);
    if (response.data.success) {
      // Harmonisation des champs pour correspondre à la table
      const f = response.data.data || {};
      fournisseur.value = {
        ...f,
        contact: f.contact || f.telephone || '',
        telephone: f.telephone || f.contact || '',
        telephone2: f.telephone2 || '',
        email: f.email || '',
        email_commercial: f.email_commercial || '',
        nom_commercial: f.nom_commercial || '',

        nom_entreprise: f.nom_entreprise || '',
        quartier: f.quartier || '',
        ville: f.ville || '',
        pays: f.pays || '',
        adresse: f.adresse || '',
        categorie_fournisseur: f.categorie_fournisseur || f.type_fournisseur || '',
        type_fournisseur: f.type_fournisseur || f.categorie_fournisseur || '',
        conditions_paiement: f.conditions_paiement || '',
        delai_livraison: f.delai_livraison || f.delai_livraison_moyen || '',
        delai_livraison_moyen: f.delai_livraison_moyen || f.delai_livraison || '',
        statut: f.statut || 'Actif',
        evaluation: f.evaluation ?? f.note_evaluation ?? null,
        note_evaluation: f.note_evaluation ?? f.evaluation ?? null,
        notes: f.notes || '',
        for_shop: f.for_shop ? 1 : 0
      };
    }
  } catch (error) {
    console.error('Erreur:', error);
  }
}

const loadStats = async () => {
  try {
    const response = await api.get(`api_fournisseurs.php?action=stats_fournisseur&id=${fournisseurId}`);
    if (response.data.success) {
      stats.value = response.data.data;
    }
  } catch (error) {
    console.error('Erreur:', error);
  }
}

const loadProduits = async () => {
  try {
    const response = await api.get(`api_fournisseurs.php?action=list_produits&fournisseur_id=${fournisseurId}`);
    if (response.data.success) {
      produits.value = response.data.data;
    }
  } catch (error) {
    console.error('Erreur:', error);
  }
}

const loadCommandes = async () => {
  try {
    const response = await api.get(`api_fournisseurs.php?action=list_commandes&fournisseur_id=${fournisseurId}`);
    if (response.data.success) {
      commandes.value = response.data.data;
    }
  } catch (error) {
    console.error('Erreur:', error);
  }
}


const loadAll = async () => {
  loading.value = true
  try {
    await Promise.all([
      loadFournisseur(),
      loadStats(),
      loadProduits(),
      loadCommandes()
    ])
  } finally {
    loading.value = false
  }
}

const editProduit = (produit) => {
  editingProduit.value = produit
  formProduit.value = { ...produit }
  showProduitModal.value = true
}

const closeProduitModal = () => {
  showProduitModal.value = false
  editingProduit.value = null
  formProduit.value = {
    reference_fournisseur: '',
    nom_produit: '',
    description: '',
    categorie: '',
    prix_unitaire: 0,
    remise_pourcentage: 0,
    quantite_minimum: 1,
    unite_mesure: 'Unité',
    disponibilite: 'En stock',
    delai_approvisionnement: null,
    statut: 'Actif'
  }
}

const calculerPrixNet = () => {
  const prix = formProduit.value.prix_unitaire || 0
  const remise = formProduit.value.remise_pourcentage || 0
  return prix - (prix * remise / 100)
}

const saveProduit = async () => {
  if (!formProduit.value.nom_produit || !formProduit.value.prix_unitaire) {
      toast.error('Nom et prix requis');
    return;
  }

  saving.value = true;
  try {
    formProduit.value.fournisseur_id = fournisseurId;
    let response;
    if (editingProduit.value) {
      response = await api.post('api_fournisseurs.php?action=update_produit', formProduit.value);
    } else {
      response = await api.post('api_fournisseurs.php?action=add_produit', formProduit.value);
    }
    if (response.data.success) {
      await loadProduits();
      await loadStats();
      closeProduitModal();
      toast.success(response.data.message);
    } else {
      toast.error(response.data.message);
    }
  } catch (error) {
      console.error('Erreur:', error);
      toast.error('Erreur de sauvegarde');
  } finally {
    saving.value = false;
  }
}

const deleteProduit = async (id) => {
  if (!confirm('Supprimer ce produit ?')) return;
  try {
    const response = await api.get(`api_fournisseurs.php?action=delete_produit&id=${id}`);
    if (response.data.success) {
      await loadProduits();
      await loadStats();
      alert(response.data.message);
    }
  } catch (error) {
    console.error('Erreur:', error);
  }
}

const closeCommandeModal = () => {
  showCommandeModal.value = false
  formCommande.value = {
    numero_commande: '',
    date_commande: new Date().toISOString().split('T')[0],
    date_livraison_prevue: '',
    montant_total: 0,
    statut: 'Brouillon',
    mode_paiement: 'Crédit',
    conditions_paiement: '',
    date_echeance_paiement: '',
    adresse_livraison: '',
    frais_livraison: 0,
    responsable: '',
    observation: ''
  }
}

const saveCommande = async () => {
  if (!formCommande.value.numero_commande || !formCommande.value.montant_total) {
      toast.error('N° commande et montant requis');
    return;
  }

  saving.value = true;
  try {
    formCommande.value.fournisseur_id = fournisseurId;
    const response = await api.post('api_fournisseurs.php?action=add_commande', formCommande.value);
    if (response.data.success) {
      await loadCommandes();
      await loadStats();
      closeCommandeModal();
      toast.success(response.data.message);
    } else {
      toast.error(response.data.message);
    }
  } catch (error) {
      console.error('Erreur:', error);
      toast.error('Erreur de création');
  } finally {
    saving.value = false;
  }
}

const enregistrerPaiement = (commande) => {
  selectedCommande.value = commande
  formPaiement.value.montant_paye = commande.montant_restant
  showPaiementModal.value = true
}

const closePaiementModal = () => {
  showPaiementModal.value = false
  selectedCommande.value = null
  formPaiement.value = {
    montant_paye: 0,
    mode_paiement: 'Espèces',
    reference_paiement: '',
    date_paiement: new Date().toISOString().split('T')[0],
    payeur: '',
    observation: ''
  }
}

const savePaiement = async () => {
  if (!formPaiement.value.montant_paye) {
      toast.error('Montant requis');
    return;
  }
  if (formPaiement.value.montant_paye > selectedCommande.value.montant_restant) {
      toast.error('Le montant dépasse le reste dû');
    return;
  }
  saving.value = true;
  try {
    const payload = {
      ...formPaiement.value,
      commande_id: selectedCommande.value.id,
      fournisseur_id: fournisseurId
    };
    const response = await api.post('api_fournisseurs.php?action=add_paiement', payload);
    if (response.data.success) {
      await loadCommandes();
      await loadStats();
      closePaiementModal();
      toast.success(response.data.message);
    } else {
      toast.error(response.data.message);
    }
  } catch (error) {
      console.error('Erreur:', error);
      toast.error("Erreur d'enregistrement");
  } finally {
    saving.value = false;
  }
}

const goBack = () => {
  router.push('/fournisseurs')
}

const formatMoney = (amount) => {
  return new Intl.NumberFormat('fr-FR', { 
    style: 'currency', 
    currency: 'XOF',
    maximumFractionDigits: 0
  }).format(amount)
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR')
}

// Lifecycle
onMounted(() => {
  loadAll()
})

// Styles (concise version - main ones only)
const loadingContainerStyle = { textAlign: 'center', padding: '80px 20px' }
const loadingTextStyle = { marginTop: '16px', fontSize: '16px', fontWeight: '500', color: '#64748b' }
const spinnerStyle = { width: '48px', height: '48px', margin: '0 auto', border: '4px solid #f1f5f9', borderTop: '4px solid #10b981', borderRadius: '50%', animation: 'spin 1s linear infinite' }
const smallSpinnerStyle = { width: '18px', height: '18px', border: '2px solid rgba(255,255,255,0.3)', borderTop: '2px solid white', borderRadius: '50%', animation: 'spin 1s linear infinite' }

const headerStyle = { display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '32px', paddingBottom: '24px', borderBottom: '2px solid #f1f5f9' }
const headerLeftStyle = { display: 'flex', flexDirection: 'column', gap: '16px' }
const backButtonStyle = { display: 'flex', alignItems: 'center', gap: '8px', padding: '10px 16px', background: 'white', border: '1px solid #e2e8f0', borderRadius: '10px', cursor: 'pointer', fontSize: '14px', fontWeight: '600', color: '#64748b' }
const titleStyle = { fontSize: '32px', fontWeight: '700', color: '#0f172a', margin: '0 0 4px 0' }
const entrepriseStyle = { fontSize: '18px', color: '#64748b', margin: '0 0 8px 0' }
const contactsStyle = { display: 'flex', gap: '16px', flexWrap: 'wrap' }
const contactItemStyle = { fontSize: '14px', color: '#475569', fontWeight: '500' }

const getStatusBadgeStyle = (statut) => ({ padding: '8px 20px', borderRadius: '12px', fontSize: '14px', fontWeight: '600', backgroundColor: statut === 'Actif' ? '#dcfce7' : statut === 'Inactif' ? '#f1f5f9' : '#fee2e2', color: statut === 'Actif' ? '#166534' : statut === 'Inactif' ? '#64748b' : '#991b1b' })

const statsGridStyle = { display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))', gap: '20px', marginBottom: '32px' }
const statsCardStyle = (color) => ({ backgroundColor: 'white', borderRadius: '20px', padding: '24px', display: 'flex', alignItems: 'center', gap: '16px', boxShadow: '0 2px 8px rgba(0,0,0,0.06)', border: `2px solid ${color}20` })
const statsIconStyle = (color) => ({ width: '56px', height: '56px', background: `${color}12`, borderRadius: '16px', display: 'flex', alignItems: 'center', justifyContent: 'center', color: color, flexShrink: 0 })
const statsLabelStyle = { fontSize: '13px', color: '#64748b', margin: '0 0 4px 0', fontWeight: '500' }
const statsValueStyle = { fontSize: '26px', fontWeight: '700', color: '#0f172a', margin: '0' }

const tabsContainerStyle = { backgroundColor: 'white', borderRadius: '20px', boxShadow: '0 2px 8px rgba(0,0,0,0.06)', border: '1px solid #f1f5f9', overflow: 'hidden' }
const tabsHeaderStyle = { display: 'flex', borderBottom: '2px solid #f1f5f9', padding: '0 24px', gap: '8px' }
const getTabStyle = (tabId) => ({ padding: '16px 24px', background: 'none', border: 'none', cursor: 'pointer', fontSize: '15px', fontWeight: '600', color: activeTab.value === tabId ? '#10b981' : '#64748b', borderBottom: activeTab.value === tabId ? '3px solid #10b981' : '3px solid transparent', marginBottom: '-2px', transition: 'all 0.2s' })
const tabContentStyle = { padding: '32px' }
const tabHeaderStyle = { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '24px' }
const tabTitleStyle = { fontSize: '20px', fontWeight: '700', color: '#0f172a', margin: 0 }
const addButtonStyle = { display: 'flex', alignItems: 'center', gap: '8px', padding: '12px 20px', background: 'linear-gradient(135deg, #10b981 0%, #059669 100%)', color: 'white', border: 'none', borderRadius: '12px', cursor: 'pointer', fontSize: '14px', fontWeight: '600', boxShadow: '0 4px 12px rgba(16,185,129,0.25)' }

const tableWrapperStyle = { overflowX: 'auto', borderRadius: '12px', border: '1px solid #e2e8f0' }
const tableStyle = { width: '100%', borderCollapse: 'collapse', minWidth: '1000px' }
const thStyle = { textAlign: 'left', padding: '16px', fontSize: '12px', fontWeight: '700', color: '#64748b', textTransform: 'uppercase', backgroundColor: '#f8fafc', borderBottom: '2px solid #e2e8f0' }
const trStyle = { borderBottom: '1px solid #f1f5f9' }
const tdStyle = { padding: '16px', fontSize: '14px', color: '#1e293b' }

const refStyle = { fontFamily: 'monospace', fontSize: '13px', fontWeight: '600', color: '#64748b', backgroundColor: '#f8fafc', padding: '4px 8px', borderRadius: '6px' }
const produitNameStyle = { margin: '0', fontWeight: '600', color: '#0f172a' }
const produitDescStyle = { margin: '4px 0 0 0', fontSize: '12px', color: '#94a3b8' }
const priceStyle = { fontWeight: '600', fontSize: '15px', color: '#1e293b' }
const remiseStyle = { fontSize: '12px', fontWeight: '600', color: '#f59e0b', backgroundColor: '#fef3c7', padding: '2px 8px', borderRadius: '6px' }
const priceNetStyle = { fontWeight: '700', fontSize: '16px', color: '#10b981' }
const getDispoBadgeStyle = (dispo) => ({ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: '600', backgroundColor: dispo === 'En stock' ? '#dcfce7' : dispo === 'Sur commande' ? '#fef3c7' : '#fee2e2', color: dispo === 'En stock' ? '#166534' : dispo === 'Sur commande' ? '#92400e' : '#991b1b' })

const commandeNumStyle = { fontFamily: 'monospace', fontSize: '14px', fontWeight: '700', color: '#3b82f6' }
const paidStyle = { fontWeight: '600', color: '#10b981' }
const debtStyle = (dette) => ({ fontWeight: '700', fontSize: '16px', color: dette > 0 ? '#ef4444' : '#10b981' })
const getCommandeStatutBadgeStyle = (statut) => {
  const colors = {
    'Brouillon': { bg: '#f1f5f9', color: '#64748b' },
    'Envoyée': { bg: '#dbeafe', color: '#1e40af' },
    'Confirmée': { bg: '#e0e7ff', color: '#4338ca' },
    'En livraison': { bg: '#fef3c7', color: '#92400e' },
    'Livrée': { bg: '#dcfce7', color: '#166534' },
    'Partiellement livrée': { bg: '#fef3c7', color: '#92400e' },
    'Annulée': { bg: '#fee2e2', color: '#991b1b' }
  }
  const style = colors[statut] || { bg: '#f1f5f9', color: '#64748b' }
  return { padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: '600', backgroundColor: style.bg, color: style.color }
}

const actionsStyle = { display: 'flex', gap: '8px' }
const actionButtonStyle = (color) => ({ width: '36px', height: '36px', borderRadius: '10px', border: `1px solid ${color}20`, backgroundColor: `${color}10`, cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center', color: color })

const emptyStateStyle = { display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', padding: '60px 20px', gap: '16px' }
const emptyTextStyle = { fontSize: '16px', color: '#94a3b8', fontWeight: '500' }

const modalOverlayStyle = { position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, backgroundColor: 'rgba(15,23,42,0.7)', backdropFilter: 'blur(8px)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 1000, padding: '20px' }
const modalStyle = { backgroundColor: 'white', borderRadius: '24px', width: '100%', maxWidth: '800px', maxHeight: '90vh', overflow: 'auto' }
const modalHeaderStyle = { display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', padding: '32px 32px 24px', borderBottom: '1px solid #f1f5f9' }
const modalTitleStyle = { fontSize: '24px', fontWeight: '700', color: '#0f172a', margin: '0 0 6px 0' }
const modalSubtitleStyle = { fontSize: '14px', color: '#64748b', margin: 0 }
const closeButtonStyle = { background: 'none', border: 'none', cursor: 'pointer', color: '#94a3b8', padding: '8px', borderRadius: '8px' }
const modalContentStyle = { padding: '24px 32px 32px' }
const formGridStyle = { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px', marginBottom: '24px' }
const formGroupStyle = { display: 'flex', flexDirection: 'column', gap: '8px' }
const labelStyle = { fontSize: '14px', fontWeight: '600', color: '#334155' }
const inputStyle = { padding: '12px 16px', border: '1px solid #e2e8f0', borderRadius: '12px', fontSize: '14px', fontWeight: '500', color: '#1e293b' }
const selectStyle = { padding: '12px 16px', border: '1px solid #e2e8f0', borderRadius: '12px', fontSize: '14px', fontWeight: '500', color: '#1e293b', backgroundColor: 'white' }
const textareaStyle = { padding: '12px 16px', border: '1px solid #e2e8f0', borderRadius: '12px', fontSize: '14px', fontWeight: '500', color: '#1e293b', resize: 'vertical' }
const modalActionsStyle = { display: 'flex', gap: '12px', paddingTop: '24px', borderTop: '1px solid #f1f5f9' }
const cancelButtonStyle = { flex: 1, padding: '14px', backgroundColor: '#f8fafc', border: '1px solid #e2e8f0', borderRadius: '12px', fontSize: '14px', fontWeight: '600', color: '#64748b', cursor: 'pointer' }
const saveButtonStyle = computed(() => ({ flex: 1, padding: '14px', background: 'linear-gradient(135deg, #10b981 0%, #059669 100%)', border: 'none', borderRadius: '12px', fontSize: '14px', fontWeight: '600', color: 'white', cursor: saving.value ? 'not-allowed' : 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '8px', opacity: saving.value ? 0.6 : 1 }))

const paiementInfoStyle = { backgroundColor: '#f8fafc', borderRadius: '12px', padding: '20px', marginBottom: '24px' }
const paiementInfoRowStyle = { display: 'flex', justifyContent: 'space-between', padding: '8px 0', fontSize: '15px', color: '#475569' }
const paiementRestantStyle = { borderTop: '2px solid #e2e8f0', marginTop: '8px', paddingTop: '16px', fontSize: '16px' }
const boldStyle = { fontWeight: '700' }
</script>

<style scoped>
@keyframes spin {
  to { transform: rotate(360deg); }
}
.fade-in { animation: fadeIn 0.6s ease-in; }
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
.modal-enter-active, .modal-leave-active { transition: all 0.3s; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-content { transition: all 0.3s; }
tr:hover { background-color: #f8fafc; }
</style>