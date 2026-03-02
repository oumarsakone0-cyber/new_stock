
import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: null,
    isAuthenticated: false
  }),

  getters: {
    // Vérifier si l'utilisateur est authentifié
    isLoggedIn: (state) => state.isAuthenticated && state.user !== null,
    
    // Obtenir les informations utilisateur
    currentUser: (state) => state.user,
    
    // Vérifier le rôle
    isAdmin: (state) => state.user?.role === 'admin',
    isManager: (state) => state.user?.role === 'manager',
    isVendeur: (state) => state.user?.role === 'vendeur',
    
    // Fonction helper pour vérifier si l'utilisateur a tous les droits
    _hasAllRights: (state) => {
      if (!state.user) return false
      // Vérifier dans access (codes de permission)
      if (state.user.access) {
        const access = typeof state.user.access === 'string' 
          ? JSON.parse(state.user.access) 
          : state.user.access
        if (Array.isArray(access) && (access.includes('ALL') || access.includes('ALL_TEST'))) {
          return true
        }
      }
      // Vérifier aussi dans acces si c'est un tableau de codes
      if (Array.isArray(state.user.acces) && (state.user.acces.includes('ALL') || state.user.acces.includes('ALL_TEST'))) {
        return true
      }
      return false
    },
    
    // Vérifier si l'utilisateur a tous les droits (ALL ou ALL_TEST)
    hasAllRights: (state) => {
      if (!state.user) return false
      // Vérifier dans access (codes de permission)
      if (state.user.access) {
        const access = typeof state.user.access === 'string' 
          ? JSON.parse(state.user.access) 
          : state.user.access
        if (Array.isArray(access) && (access.includes('ALL') || access.includes('ALL_TEST'))) {
          return true
        }
      }
      // Vérifier aussi dans acces si c'est un tableau de codes
      if (Array.isArray(state.user.acces) && (state.user.acces.includes('ALL') || state.user.acces.includes('ALL_TEST'))) {
        return true
      }
      return false
    },
    
    // Accès aux modules
    hasAccessToClients: (state) => {
      if (!state.user) return false
      if (state.user.acces?.clients === true) return true
      
      // Vérifier si l'utilisateur a tous les droits
      if (state.user.access) {
        const access = typeof state.user.access === 'string' 
          ? JSON.parse(state.user.access) 
          : state.user.access
        if (Array.isArray(access) && (access.includes('ALL') || access.includes('ALL_TEST'))) {
          return true
        }
        if (Array.isArray(access) && access.includes('GCL')) return true
      }
      return false
    },
    
    hasAccessToFournisseurs: (state) => {
      if (!state.user) return false
      if (state.user.acces?.fournisseurs === true) return true
      
      // Vérifier si l'utilisateur a tous les droits
      if (state.user.access) {
        const access = typeof state.user.access === 'string' 
          ? JSON.parse(state.user.access) 
          : state.user.access
        if (Array.isArray(access) && (access.includes('ALL') || access.includes('ALL_TEST'))) {
          return true
        }
        if (Array.isArray(access) && access.includes('GF')) return true
      }
      return false
    },
    
    // Accès magasins
    hasAccessToMagasins: (state) => {
      if (!state.user) return false
      
      const acces = state.user.acces?.magasins
      if (acces === 'all') return true
      if (Array.isArray(acces) && acces.length > 0) return true
      
      // Vérifier si l'utilisateur a tous les droits ou les permissions spécifiques
      if (state.user.access) {
        const access = typeof state.user.access === 'string' 
          ? JSON.parse(state.user.access) 
          : state.user.access
        if (Array.isArray(access)) {
          if (access.includes('ALL') || access.includes('ALL_TEST')) return true
          if (access.includes('GS') || access.includes('GV') || access.includes('GP')) return true
        }
      }
      return false
    },
    
    hasAccessToMagasin: (state) => (magasinId) => {
      if (!state.user) return false
      
      // Vérifier si l'utilisateur a tous les droits
      if (state.user.access) {
        const access = typeof state.user.access === 'string' 
          ? JSON.parse(state.user.access) 
          : state.user.access
        if (Array.isArray(access) && (access.includes('ALL') || access.includes('ALL_TEST'))) {
          return true
        }
      }
      
      const acces = state.user.acces?.magasins
      if (acces === 'all') return true
      if (Array.isArray(acces)) {
        return acces.includes(parseInt(magasinId))
      }
      return false
    },
    
    // Accès entrepôts
    hasAccessToEntrepots: (state) => {
      if (!state.user) return false
      
      const acces = state.user.acces?.entrepots
      if (acces === 'all') return true
      if (Array.isArray(acces) && acces.length > 0) return true
      
      // Vérifier si l'utilisateur a tous les droits ou la permission GT
      if (state.user.access) {
        const access = typeof state.user.access === 'string' 
          ? JSON.parse(state.user.access) 
          : state.user.access
        if (Array.isArray(access)) {
          if (access.includes('ALL') || access.includes('ALL_TEST')) return true
          if (access.includes('GT')) return true
        }
      }
      return false
    },
    
    hasAccessToEntrepot: (state) => (entrepotId) => {
      if (!state.user) return false
      
      // Vérifier si l'utilisateur a tous les droits
      if (state.user.access) {
        const access = typeof state.user.access === 'string' 
          ? JSON.parse(state.user.access) 
          : state.user.access
        if (Array.isArray(access) && (access.includes('ALL') || access.includes('ALL_TEST'))) {
          return true
        }
      }
      
      const acces = state.user.acces?.entrepots
      if (acces === 'all') return true
      if (Array.isArray(acces)) {
        return acces.includes(parseInt(entrepotId))
      }
      return false
    },
    
    // Accès utilisateurs (admin uniquement ou GU permission)
    hasAccessToUsers: (state) => {
      if (!state.user) return false
      
      // Vérifier si l'utilisateur a tous les droits ou la permission GU
      if (state.user.access) {
        const access = typeof state.user.access === 'string' 
          ? JSON.parse(state.user.access) 
          : state.user.access
        if (Array.isArray(access)) {
          if (access.includes('ALL') || access.includes('ALL_TEST')) return true
          if (access.includes('GU')) return true
        }
      }
      return false
    },

    // Accès comptabilités (GC permission)
    hasAccessToComptabilites: (state) => {
      if (!state.user) return false

      if (state.user.access) {
        const access = typeof state.user.access === 'string'
          ? JSON.parse(state.user.access)
          : state.user.access
        if (Array.isArray(access)) {
          if (access.includes('ALL') || access.includes('ALL_TEST')) return true
          if (access.includes('GC')) return true
        }
      }
      return false
    },
    
    // Liste des magasins accessibles
    accessibleMagasins: (state) => {
      if (!state.user) return []
      const acces = state.user.acces?.magasins
      if (acces === 'all') return 'all'
      if (Array.isArray(acces)) return acces
      return []
    },
    
    // Liste des entrepôts accessibles
    accessibleEntrepots: (state) => {
      if (!state.user) return []
      const acces = state.user.acces?.entrepots
      if (acces === 'all') return 'all'
      if (Array.isArray(acces)) return acces
      return []
    }
  },

  actions: {
    // Connexion
    async login(userData) {
      this.user = userData
      this.token = userData.token
      this.isAuthenticated = true
      
      // Sauvegarder dans localStorage
      localStorage.setItem('user', JSON.stringify(userData))
      localStorage.setItem('token', userData.token)
      localStorage.setItem('isAuthenticated', 'true')
    },
    
    // Déconnexion
    logout() {
      this.user = null
      this.token = null
      this.isAuthenticated = false
      
      // Nettoyer localStorage
      localStorage.removeItem('user')
      localStorage.removeItem('token')
      localStorage.removeItem('isAuthenticated')
    },
    
    // Restaurer la session depuis localStorage
    restoreSession() {
      const user = localStorage.getItem('user')
      const token = localStorage.getItem('token')
      const isAuthenticated = localStorage.getItem('isAuthenticated')
      
      if (user && token && isAuthenticated === 'true') {
        try {
          this.user = JSON.parse(user)
          this.token = token
          this.isAuthenticated = true
          return true
        } catch (error) {
          console.error('Erreur lors de la restauration de la session:', error)
          this.logout()
          return false
        }
      }
      return false
    },
    
    // Mettre à jour les informations utilisateur
    updateUser(userData) {
      this.user = { ...this.user, ...userData }
      localStorage.setItem('user', JSON.stringify(this.user))
    }
  }
})