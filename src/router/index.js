import { createRouter, createWebHistory } from "vue-router"
import { useAuthStore } from "../stores/authStore"

const routes = [
  {
    path: "/login",
    name: "login",
    component: () => import("../views/app_auth/Login.vue"),
    meta: { requiresAuth: false, public: true }
  },
  {
    path: "/register",
    name: "register",
    component: () => import("../views/app_auth/Register.vue"),
    meta: { requiresAuth: false, public: true }
  },
  {
    path: "/",
    name: "home",
    component: () => import("../views/Home.vue"),
    meta: { requiresAuth: true }
  },
  {
    path: "/magasins",
    name: "magasins",
    component: () => import("../views/magasins/MagasinsList.vue"),
    meta: { 
      requiresAuth: true,
      requiresAccess: 'magasins'
    }
  },
  {
    path: "/magasins/:id",
    name: "magasin-detail",
    component: () => import("../views/magasins/MagasinDetail.vue"),
    meta: { 
      requiresAuth: true,
      requiresAccess: 'magasin',
      checkMagasinId: true
    }
  },
  {
    path: "/magasins/:id/produits",
    name: "magasin-produits",
    component: () => import("../views/magasins/MagasinProduits.vue"),
    meta: { 
      requiresAuth: true,
      requiresAccess: 'magasin',
      checkMagasinId: true
    }
  },
  {
    path: "/magasins/:id/stock",
    name: "magasin-stock",
    component: () => import("../views/magasins/MagasinStock.vue"),
    meta: { 
      requiresAuth: true,
      requiresAccess: 'magasin',
      checkMagasinId: true
    }
  },
  {
    path: "/magasins/:id/ventes",
    name: "magasin-ventes",
    component: () => import("../views/magasins/MagasinVentes.vue"),
    meta: { 
      requiresAuth: true,
      requiresAccess: 'magasin',
      checkMagasinId: true
    }
  },
  {
    path: "/entrepots",
    name: "entrepots",
    component: () => import("../views/app_entrepots/Entrepots_list.vue"),
    meta: { 
      requiresAuth: true,
      requiresAccess: 'entrepots'
    }
  },
  {
    path: "/entrepots/:id",
    name: "entrepots-detail",
    component: () => import("../views/app_entrepots/Entrepots_detail.vue"),
    meta: { 
      requiresAuth: true,
      requiresAccess: 'entrepot',
      checkEntrepotId: true
    }
  },
  {
    path: "/clients",
    name: "clients",
    component: () => import("../views/app_clients/client_list.vue"),
    meta: { 
      requiresAuth: true,
      requiresAccess: 'clients'
    }
  },
  {

    path: "/clients/:id",
    name: "clients-detail",
    component: () => import("../views/app_clients/client_detail.vue"),
    meta: { 
      requiresAuth: true,
      requiresAccess: 'clients'
    }
  },
  {
    path: "/fournisseurs",
    name: "fournisseurs",
    component: () => import("../views/app_fournisseurs/fournisseurs_list.vue"),
    meta: { 
      requiresAuth: true,
      requiresAccess: 'fournisseurs'
    }
  },
  {
    path: "/fournisseurs/:id",
    name: "fournisseurs-detail",
    component: () => import("../views/app_fournisseurs/fournisseur_detail.vue"),
    meta: { 
      requiresAuth: true,
      requiresAccess: 'fournisseurs'
    }
  },
  {
    path: "/comptabilites",
    name: "comptabilites",
    component: () => import("../views/comptabilites/ComptabilitesDashboard.vue"),
    meta: {
      requiresAuth: true,
      requiresAccess: 'comptabilite'
    }
  },
  {
    path: "/comptabilites/depenses",
    name: "comptabilites-depenses",
    component: () => import("../views/comptabilites/GestionDepenses.vue"),
    meta: {
      requiresAuth: true,
      requiresAccess: 'comptabilite'
    }
  },
  {
    path: "/comptabilites/motifs",
    name: "comptabilites-motifs",
    component: () => import("../views/comptabilites/MotifsDepense.vue"),
    meta: {
      requiresAuth: true,
      requiresAccess: 'comptabilite'
    }
  },
  {
    path: "/comptabilites/factures",
    name: "comptabilites-factures",
    component: () => import("../views/comptabilites/Factures.vue"),
    meta: {
      requiresAuth: true,
      requiresAccess: 'comptabilite'
    }
  },
  {
    path: "/comptabilites/banque",
    name: "comptabilites-banque",
    component: () => import("../views/comptabilites/GestionBancaire.vue"),
    meta: {
      requiresAuth: true,
      requiresAccess: 'comptabilite'
    }
  },
  {
    path: "/comptabilites/banque/comptes",
    name: "comptabilites-banque-comptes",
    component: () => import("../views/comptabilites/ComptesBancaires.vue"),
    meta: {
      requiresAuth: true,
      requiresAccess: 'comptabilite'
    }
  },
  {
    path: "/comptabilites/banque/transactions",
    name: "comptabilites-banque-transactions",
    component: () => import("../views/comptabilites/TransactionsBancaires.vue"),
    meta: {
      requiresAuth: true,
      requiresAccess: 'comptabilite'
    }
  },
  {
    path: "/utilisateurs",
    name: "utilisateurs",
    component: () => import("../views/Users.vue"),
    meta: { 
      requiresAuth: true,
      requiresAccess: 'users',
      requiresAdmin: true
    }
  },
  {
    path: "/produits",
    name: "produits",
    component: () => import("../views/app_produits/Liste_produits.vue"),
    meta: { 
      requiresAuth: true,
      requiresAccess: 'users',
      requiresAdmin: true
    }
  },
  {
    path: "/points_de_vente",
    name: "points_de_vente",
    component: () => import("../views/Ventes.vue"),
    meta: { 
      requiresAuth: true,
      requiresAccess: 'users',
      requiresAdmin: true
    }
  },
  {
    path: "/points_de_vente_details",
    name: "points_de_vente_details",
    component: () => import("../views/VenteDetails.vue"),
    meta: { 
      requiresAuth: true,
      requiresAccess: 'users',
      requiresAdmin: true
    }
  },
  {
    path: "/departement/senasip",
    name: "senasip",
    component: () => import("../views/senasip/Senasipdashboard.vue"),
    meta: { requiresAuth: true }
  },
  // Page 403 - Accès refusé
  {
    path: "/403",
    name: "forbidden",
    component: () => import("../views/Forbidden.vue"),
    meta: { requiresAuth: false, public: true }
  },
  // Page 404 - Not Found
  {
    path: "/:pathMatch(.*)*",
    name: "not-found",
    component: () => import("../views/Notfound.vue"),
    meta: { requiresAuth: false, public: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Guard de navigation global
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  // Restaurer la session si elle existe
  if (!authStore.isLoggedIn) {
    authStore.restoreSession()
  }
  
  // Pages publiques
  if (to.meta.public) {
    // Si déjà connecté et va vers login ou register, rediriger vers home
    if ((to.name === 'login' || to.name === 'register') && authStore.isLoggedIn) {
      return next({ name: 'home' })
    }
    return next()
  }
  
  // Vérifier l'authentification
  if (to.meta.requiresAuth && !authStore.isLoggedIn) {
    return next({ 
      name: 'login',
      query: { redirect: to.fullPath }
    })
  }
  
  // Vérifier les accès spécifiques
  if (to.meta.requiresAccess) {
    const accessType = to.meta.requiresAccess
    
    switch (accessType) {
      case 'magasins':
        if (!authStore.hasAccessToMagasins) {
          return next({ name: 'forbidden' })
        }
        break
        
      case 'magasin':
        if (to.meta.checkMagasinId && to.params.id) {
          if (!authStore.hasAccessToMagasin(to.params.id)) {
            return next({ name: 'forbidden' })
          }
        }
        break
        
      case 'entrepots':
        if (!authStore.hasAccessToEntrepots) {
          return next({ name: 'forbidden' })
        }
        break
        
      case 'entrepot':
        if (to.meta.checkEntrepotId && to.params.id) {
          if (!authStore.hasAccessToEntrepot(to.params.id)) {
            return next({ name: 'forbidden' })
          }
        }
        break
        
      case 'clients':
        if (!authStore.hasAccessToClients) {
          return next({ name: 'forbidden' })
        }
        break
        
      case 'fournisseurs':
        if (!authStore.hasAccessToFournisseurs) {
          return next({ name: 'forbidden' })
        }
        break
        
      case 'users':
        if (!authStore.hasAccessToUsers) {
          return next({ name: 'forbidden' })
        }
        break

      case 'comptabilite':
        if (!authStore.hasAccessToComptabilites) {
          return next({ name: 'forbidden' })
        }
        break
    }
  }
  
  // Vérifier si admin requis
  // Un utilisateur avec ALL ou ALL_TEST (hasAllRights) doit aussi avoir accès à ces pages
  if (to.meta.requiresAdmin && !(authStore.isAdmin || authStore.hasAllRights)) {
    return next({ name: 'forbidden' })
  }
  
  next()
})

export default router