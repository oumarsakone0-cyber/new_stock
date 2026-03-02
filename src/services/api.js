// ==================== POINTS DE VENTE ====================
// Liste des points de vente
export const getPointsVente = () => {
  return api.get('api_points_vente.php?action=list');
};

// Détail d'un point de vente
export const getPointVente = (id_pdv) => {
  return api.get('api_points_vente.php?action=get', { params: { id_pdv } });
};

// Ajouter un point de vente
export const addPointVente = (payload) => {
  return api.post('api_points_vente.php?action=add', payload);
};

// Modifier un point de vente
export const updatePointVente = (payload) => {
  return api.post('api_points_vente.php?action=update', payload);
};

// Supprimer un point de vente
export const deletePointVente = (id_pdv) => {
  return api.post('api_points_vente.php?action=delete', { id_pdv });
};
// Statistiques globales clients
export const getStatsGlobal = (params = {}) => {
  return api.get('api_clients.php?action=rapport_global', { params });
};

// Crédits en cours clients
export const getCreditsData = (params = {}) => {
  return api.get('api_clients.php?action=credits_en_cours', { params });
};

// ==================== CLIENTS ====================
// Liste des clients
export const getClients = () => {
  // Plus de paramètre à passer
  return api.get('api_clients.php?action=list_clients');
};

// Détail d'un client
export const getClient = (id_client) => {
  return api.get('api_clients.php?action=get_client', { params: { id_client } });
};

// Ajouter un client
export const addClient = (payload) => {
  // payload doit contenir tous les champs de la table app_clients
  return api.post('api_clients.php?action=add_client', payload);
};

// Modifier un client
export const updateClient = (payload) => {
  // payload doit contenir id_client et tous les champs à modifier
  return api.post('api_clients.php?action=update_client', payload);
};

// Supprimer un client
export const deleteClient = (id_client) => {
  return api.post('api_clients.php?action=delete_client', { id_client });
};

// ==================== ENTREPOTS ====================
// Liste des entrepôts
export const getEntrepots = (params = {}) => {
  return api.get('api_entrepots.php?action=list_entrepots', { params });
};

// Détail d'un entrepôt
export const getEntrepot = (id) => {
  return api.get('api_entrepots.php?action=get_entrepot', { params: { id } });
};

// Statistiques d’un entrepôt
export const getEntrepotStats = (id) => {
  return api.get('api_entrepots.php?action=stats_entrepot', { params: { id } });
};

// Liste des produits d’un entrepôt
export const getEntrepotProduits = (entrepot_id) => {
  return api.get('api_entrepots.php?action=list_produits', { params: { entrepot_id } });
};

// Liste des mouvements d’un entrepôt
export const getEntrepotMouvements = (entrepot_id) => {
  return api.get('api_entrepots.php?action=list_mouvements', { params: { entrepot_id } });
};

// Ajouter un produit
export const addEntrepotProduit = (payload) => {
  return api.post('api_entrepots.php?action=add_produit', payload);
};

// Modifier un produit
export const updateEntrepotProduit = (payload) => {
  return api.post('api_entrepots.php?action=update_produit', payload);
};

// Ajouter un mouvement
export const addEntrepotMouvement = (payload) => {
  return api.post('api_entrepots.php?action=add_mouvement', payload);
};

// Ajouter un entrepôt
export const addEntrepot = (payload) => {
  return api.post('api_entrepots.php?action=add_entrepot', payload);
};

// Modifier un entrepôt
export const updateEntrepot = (payload) => {
  return api.post('api_entrepots.php?action=update_entrepot', payload);
};

// Supprimer un entrepôt
export const deleteEntrepot = (id) => {
  return api.post('api_entrepots.php?action=delete_entrepot', { id });
};

export const getUsers = () => api.get('api_users.php?action=list')
export const addUser = (payload) => api.post('api_users.php?action=add', payload)
export const updateUser = (payload) => api.put('api_users.php?action=update', payload)
export const adeleteUser = (id) => api.delete(`api_users.php?action=delete&id=${id}`)
export const toggleUserStatus = (id) => api.put('api_users.php?action=toggle-status', { id })

import axios from 'axios';

// Création d'une instance Axios pour toutes les requêtes API
const api = axios.create({
  baseURL: '/api', // Utilise le proxy Vite pour éviter CORS en dev
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Intercepteur pour ajouter le token d'authentification à chaque requête
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Intercepteur pour gérer les erreurs globalement
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response) {
      if (error.response.status === 401) {
        // Déconnexion automatique si le token n'est plus valide
        localStorage.removeItem('token');
        // window.location.href = '/login'; // Décommentez pour rediriger
        console.warn('Session expirée ou non autorisée');
      }
    }
    return Promise.reject(error);
  }
);

// Méthodes utilitaires pour les requêtes courantes
export const apiGet = (url, config = {}) => api.get(url, config);
export const apiPost = (url, data, config = {}) => api.post(url, data, config);
export const apiPut = (url, data, config = {}) => api.put(url, data, config);
export const apiDelete = (url, config = {}) => api.delete(url, config);

// Base URL de l'API (évite de la mettre en dur dans les vues)
export const getApiBaseUrl = () => api.defaults.baseURL;

// Authentification (api_auth.php)
// Utiliser directement l'URL distante pour éviter les problèmes de proxy avec les redirections
const authApi = axios.create({
  baseURL: 'https://www.aliadjame.com/api',
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Connexion : POST api_auth.php?action=login
export const authLogin = (email, password) => {
  return authApi.post('api_auth.php?action=login', { email, password });
};

export const authActivate = (email, code) => {
  return authApi.post('api_auth.php?action=activate', { email, code });
};

// Inscription : POST api_auth.php?action=register2
export const authRegister = (payload) => {
  return authApi.post('api_auth.php?action=register2', payload);
};

/**
 * Définir le mot de passe après validation du code OTP
 * POST /api/auth.php?action=set_password
 * @param {string} email    - Email de l'utilisateur
 * @param {string} password - Nouveau mot de passe en clair
 */
export const authSetPassword = (email, password) =>
  authApi.post('api_auth.php?action=set_password', { email, password })

export default api;
