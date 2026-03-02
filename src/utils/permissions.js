/**
 * Gestion des permissions et rôles
 * 
 * Ce fichier fournit des fonctions pour vérifier les permissions et rôles des utilisateurs
 * basées sur les codes de permission de la base de données et les rôles utilisateur.
 */

import { useAuthStore } from '../stores/authStore'

/**
 * Codes de permission de la base de données
 */
export const PERMISSION_CODES = {
  GP: 'GP',           // Gestion produit
  GS: 'GS',           // Gestion stock
  GV: 'GV',           // Gestion vente
  GU: 'GU',           // Gestion utilisateur
  GC: 'GC',           // Gestion comptabilite
  GT: 'GT',           // Gestion entrepot
  GCM: 'GCM',         // Gestion commandes
  GF: 'GF',           // Gestion fournisseur
  GCL: 'GCL',         // Gestion clients
  GCS: 'GCS',         // Gestion caisse
  ALL_TEST: 'ALL_TEST', // Test Utilisateur (donne accès à tout)
  ALL: 'ALL'          // Tous les droits
}

/**
 * Mapping des codes de permission vers les modules de l'application
 */
export const PERMISSION_MODULE_MAP = {
  'produits': PERMISSION_CODES.GP,
  'stock': PERMISSION_CODES.GS,
  'ventes': PERMISSION_CODES.GV,
  'utilisateurs': PERMISSION_CODES.GU,
  'comptabilite': PERMISSION_CODES.GC,
  'entrepots': PERMISSION_CODES.GT,
  'commandes': PERMISSION_CODES.GCM,
  'fournisseurs': PERMISSION_CODES.GF,
  'clients': PERMISSION_CODES.GCL,
  'caisse': PERMISSION_CODES.GCS
}

/**
 * Vérifie si l'utilisateur a une permission spécifique
 * 
 * @param {string|Array<string>} permissionCode - Code(s) de permission à vérifier (ex: 'GP', 'GS', ou ['GP', 'GS'])
 * @param {Object} user - Objet utilisateur (optionnel, utilise le store si non fourni)
 * @returns {boolean} - true si l'utilisateur a la permission
 * 
 * @example
 * hasPermission('GP') // Vérifie la permission Gestion produit
 * hasPermission(['GP', 'GS']) // Vérifie si l'utilisateur a GP OU GS
 * hasPermission('ALL') // Vérifie si l'utilisateur a tous les droits
 */
export function hasPermission(permissionCode, user = null) {
  const authStore = useAuthStore()
  const currentUser = user || authStore.user

  // Si pas d'utilisateur, pas de permission
  if (!currentUser) {
    return false
  }

  // Récupérer les permissions de l'utilisateur
  let userPermissions = []
  
  // Les permissions peuvent être dans user.access (tableau JSON) ou user.acces
  if (currentUser.access) {
    // Si c'est une chaîne JSON, la parser
    if (typeof currentUser.access === 'string') {
      try {
        userPermissions = JSON.parse(currentUser.access)
      } catch (e) {
        console.error('Erreur lors du parsing des permissions:', e)
        userPermissions = []
      }
    } else if (Array.isArray(currentUser.access)) {
      userPermissions = currentUser.access
    }
  } else if (currentUser.acces && Array.isArray(currentUser.acces)) {
    // Fallback vers l'ancien format
    userPermissions = currentUser.acces
  }

  // Si l'utilisateur a ALL ou ALL_TEST, il a tous les droits
  if (userPermissions.includes(PERMISSION_CODES.ALL) || 
      userPermissions.includes(PERMISSION_CODES.ALL_TEST)) {
    return true
  }

  // Si permissionCode est un tableau, vérifier si l'utilisateur a au moins une des permissions
  if (Array.isArray(permissionCode)) {
    return permissionCode.some(code => userPermissions.includes(code))
  }

  // Vérifier si l'utilisateur a la permission spécifique
  return userPermissions.includes(permissionCode)
}

/**
 * Vérifie si l'utilisateur a accès à un module spécifique
 * 
 * @param {string} moduleName - Nom du module (ex: 'produits', 'clients', 'ventes')
 * @param {Object} user - Objet utilisateur (optionnel, utilise le store si non fourni)
 * @returns {boolean} - true si l'utilisateur a accès au module
 * 
 * @example
 * hasModuleAccess('produits') // Vérifie l'accès au module produits
 * hasModuleAccess('clients') // Vérifie l'accès au module clients
 */
export function hasModuleAccess(moduleName, user = null) {
  const permissionCode = PERMISSION_MODULE_MAP[moduleName]
  
  if (!permissionCode) {
    console.warn(`Module inconnu: ${moduleName}`)
    return false
  }

  return hasPermission(permissionCode, user)
}

/**
 * Vérifie le rôle de l'utilisateur
 * 
 * @param {string|Array<string>} roleName - Nom(s) du rôle à vérifier (ex: 'admin', 'manager', ou ['admin', 'manager'])
 * @param {Object} user - Objet utilisateur (optionnel, utilise le store si non fourni)
 * @returns {boolean} - true si l'utilisateur a le rôle
 * 
 * @example
 * hasRole('admin') // Vérifie si l'utilisateur est admin
 * hasRole(['admin', 'manager']) // Vérifie si l'utilisateur est admin OU manager
 */
export function hasRole(roleName, user = null) {
  const authStore = useAuthStore()
  const currentUser = user || authStore.user

  // Si pas d'utilisateur, pas de rôle
  if (!currentUser) {
    return false
  }

  // Récupérer le rôle (peut être dans role, id_role, ou nom_role)
  const userRole = currentUser.role || currentUser.id_role || currentUser.nom_role

  if (!userRole) {
    return false
  }

  // Si roleName est un tableau, vérifier si l'utilisateur a au moins un des rôles
  if (Array.isArray(roleName)) {
    return roleName.some(role => {
      // Comparaison insensible à la casse
      return String(userRole).toLowerCase() === String(role).toLowerCase()
    })
  }

  // Comparaison insensible à la casse
  return String(userRole).toLowerCase() === String(roleName).toLowerCase()
}

/**
 * Vérifie si l'utilisateur a une permission OU un rôle spécifique
 * 
 * @param {string|Array<string>} permissionCode - Code(s) de permission
 * @param {string|Array<string>} roleName - Nom(s) du rôle
 * @param {Object} user - Objet utilisateur (optionnel)
 * @returns {boolean} - true si l'utilisateur a la permission OU le rôle
 * 
 * @example
 * hasPermissionOrRole('GP', 'admin') // Vérifie si l'utilisateur a GP OU est admin
 */
export function hasPermissionOrRole(permissionCode, roleName, user = null) {
  return hasPermission(permissionCode, user) || hasRole(roleName, user)
}

/**
 * Vérifie si l'utilisateur a une permission ET un rôle spécifique
 * 
 * @param {string|Array<string>} permissionCode - Code(s) de permission
 * @param {string|Array<string>} roleName - Nom(s) du rôle
 * @param {Object} user - Objet utilisateur (optionnel)
 * @returns {boolean} - true si l'utilisateur a la permission ET le rôle
 * 
 * @example
 * hasPermissionAndRole('GP', 'manager') // Vérifie si l'utilisateur a GP ET est manager
 */
export function hasPermissionAndRole(permissionCode, roleName, user = null) {
  return hasPermission(permissionCode, user) && hasRole(roleName, user)
}

/**
 * Retourne toutes les permissions de l'utilisateur
 * 
 * @param {Object} user - Objet utilisateur (optionnel, utilise le store si non fourni)
 * @returns {Array<string>} - Liste des codes de permission
 */
export function getUserPermissions(user = null) {
  const authStore = useAuthStore()
  const currentUser = user || authStore.user

  if (!currentUser) {
    return []
  }

  if (currentUser.access) {
    if (typeof currentUser.access === 'string') {
      try {
        return JSON.parse(currentUser.access)
      } catch (e) {
        return []
      }
    } else if (Array.isArray(currentUser.access)) {
      return currentUser.access
    }
  } else if (currentUser.acces && Array.isArray(currentUser.acces)) {
    return currentUser.acces
  }

  return []
}

/**
 * Retourne le rôle de l'utilisateur
 * 
 * @param {Object} user - Objet utilisateur (optionnel, utilise le store si non fourni)
 * @returns {string|null} - Nom du rôle ou null
 */
export function getUserRole(user = null) {
  const authStore = useAuthStore()
  const currentUser = user || authStore.user

  if (!currentUser) {
    return null
  }

  return currentUser.role || currentUser.id_role || currentUser.nom_role || null
}

/**
 * EXEMPLES D'UTILISATION
 * 
 * 1. Dans un composant Vue :
 * 
 * <script setup>
 * import { hasPermission, hasRole, hasModuleAccess } from '@/utils/permissions'
 * import { PERMISSION_CODES } from '@/utils/permissions'
 * 
 * // Vérifier une permission spécifique
 * const canManageProducts = hasPermission(PERMISSION_CODES.GP)
 * 
 * // Vérifier plusieurs permissions (OR)
 * const canManageProductsOrStock = hasPermission([PERMISSION_CODES.GP, PERMISSION_CODES.GS])
 * 
 * // Vérifier l'accès à un module
 * const canAccessClients = hasModuleAccess('clients')
 * 
 * // Vérifier un rôle
 * const isAdmin = hasRole('admin')
 * 
 * // Vérifier permission OU rôle
 * const canAccess = hasPermissionOrRole(PERMISSION_CODES.GU, 'admin')
 * </script>
 * 
 * <template>
 *   <div v-if="canManageProducts">
 *     <!-- Contenu visible uniquement si l'utilisateur a la permission GP -->
 *   </div>
 * </template>
 * 
 * 2. Dans le router (index.js) :
 * 
 * import { hasPermission, hasModuleAccess } from '@/utils/permissions'
 * import { PERMISSION_CODES } from '@/utils/permissions'
 * 
 * router.beforeEach((to, from, next) => {
 *   // Vérifier une permission pour une route
 *   if (to.meta.requiresPermission) {
 *     if (!hasPermission(to.meta.requiresPermission)) {
 *       return next({ name: 'forbidden' })
 *     }
 *   }
 *   
 *   // Vérifier l'accès à un module
 *   if (to.meta.requiresModule) {
 *     if (!hasModuleAccess(to.meta.requiresModule)) {
 *       return next({ name: 'forbidden' })
 *     }
 *   }
 *   
 *   next()
 * })
 * 
 * 3. Dans une fonction/composable :
 * 
 * import { hasPermission, PERMISSION_CODES } from '@/utils/permissions'
 * 
 * function handleAction() {
 *   if (!hasPermission(PERMISSION_CODES.GV)) {
 *     alert('Vous n\'avez pas la permission de gérer les ventes')
 *     return
 *   }
 *   // Continuer avec l'action
 * }
 */
