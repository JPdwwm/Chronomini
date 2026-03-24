<script setup>
import { ref, onMounted, computed } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { formatDate } from '@/_utils/dateUtils';
import { 
  getAllUsers, 
  deleteUser as deleteUserService,
  updateUser as updateUserService,
  getAllKids, 
  getAllRecords, 
} from '@/_services/AdminService';

// État
const activeTab = ref('users');
const stats = ref({
    userCount: 0,
    kidCount: 0,
    recordCount: 0,
});

const users = ref([]);
const kids = ref([]);
const records = ref([]);

const isLoading = ref({
    dashboard: true,
    users: true,
    kids: true,
});

// État pour l'édition d'utilisateur
const editingUser = ref(null);
const editUserForm = ref({
    first_name: '',
    last_name: '',
    email: '',
});

// Filtres
const userFilters = ref({ search: '', role: '' });
const kidFilters = ref({ search: '' });

// Fetch des données du tableau de bord
const fetchDashboardData = async () => {
  try {
    isLoading.value.dashboard = true;
    
    const [usersResponse, kidsResponse, recordsResponse] = await Promise.all([
      getAllUsers(),
      getAllKids(),
      getAllRecords(),
    ]);
    
    // être sur les réponses sont des tableaux
    users.value = Array.isArray(usersResponse) ? usersResponse : [];
    kids.value = Array.isArray(kidsResponse) ? kidsResponse : [];
    records.value = Array.isArray(recordsResponse) ? recordsResponse : [];

    
    // Calculer les statistiques
    stats.value = {
      userCount: users.value.length,
      kidCount: kids.value.length,
      recordCount: records.value.length,
    };
  } catch (error) {
  } finally {
    isLoading.value.dashboard = false;
    isLoading.value.users = false;
    isLoading.value.kids = false;
  }
};

// Fonction pour changer d'onglet
const changeTab = (tab) => {
    activeTab.value = tab;
};

// Fonction pour formater les dates en gérant les erreurs
const safeFormatDate = (dateString) => {
    if (!dateString) return 'N/A';
    try {
        return formatDate(dateString);
    } catch (error) {
        return 'Date invalide';
    }
};

const getRoleName = (roleId) => {
    switch(roleId) {
        case 1: return 'Admin';
        case 2: return 'Parent';
        case 3: return 'Ass. Mat.';
        default: return 'Inconnu';
    }
};

const getRoleClass = (roleId) => {
    switch(roleId) {
        case 1: return 'role-admin';
        case 2: return 'role-parent';
        case 3: return 'role-asmat';
        default: return '';
    }
};

// Fonctions pour les actions de suppression
const confirmDeleteUser = (user) => {
    if (confirm(`Êtes-vous sûr de vouloir supprimer l'utilisateur ${user.first_name} ${user.last_name} ?`)) {
        deleteUser(user.id);
    }
};

const deleteUser = async (userId) => {
    try {
        await deleteUserService(userId);
        users.value = users.value.filter(user => user.id !== userId);
        stats.value.userCount--;
        alert('Utilisateur supprimé avec succès');
    } catch (error) {
        alert('Erreur lors de la suppression de l\'utilisateur');
    }
};

// Fonctions pour les actions d'édition
const startEditUser = (user) => {
    editingUser.value = user.id;
    editUserForm.value = {
        first_name: user.first_name,
        last_name: user.last_name,
        email: user.email,
    };
};

const cancelEdit = () => {
    editingUser.value = null;
};

const saveUser = async () => {
    try {
        const updatedUser = await updateUserService(editingUser.value, editUserForm.value);
        
        // Mettre à jour l'utilisateur dans le tableau
        const index = users.value.findIndex(user => user.id === editingUser.value);
        if (index !== -1) {
            users.value[index] = { ...users.value[index], ...updatedUser };
        }
        
        editingUser.value = null;
        alert('Utilisateur mis à jour avec succès');
    } catch (error) {
    }
};

// Filtres computés
const filteredUsers = computed(() => {
    return users.value.filter(user => {
        const matchesSearch = 
            userFilters.value.search === '' || 
            user.first_name?.toLowerCase().includes(userFilters.value.search.toLowerCase()) ||
            user.last_name?.toLowerCase().includes(userFilters.value.search.toLowerCase()) ||
            user.email?.toLowerCase().includes(userFilters.value.search.toLowerCase());
        
        const matchesRole = 
            userFilters.value.role === '' || 
            user.role_id?.toString() === userFilters.value.role;
        
        return matchesSearch && matchesRole;
    });
});

const filteredKids = computed(() => {
    return kids.value.filter(kid => {
        return kidFilters.value.search === '' || 
            kid.first_name?.toLowerCase().includes(kidFilters.value.search.toLowerCase());
    });
});

onMounted(() => {
    fetchDashboardData();
});
</script>

<template>
    <div class="admin-dashboard">
        <h1>Tableau de bord administrateur</h1>
        
        <!-- Cartes de statistiques -->
        <div class="stats-overview">
            <div class="stat-card" @click="changeTab('users')">
                <div class="stat-icon">
                    <FontAwesomeIcon icon="fa-solid fa-users" />
                </div>
                <div class="stat-content">
                    <h3>Utilisateurs</h3>
                    <p>{{ stats.userCount || 0 }}</p>
                </div>
            </div>
            
            <div class="stat-card" @click="changeTab('kids')">
                <div class="stat-icon">
                    <FontAwesomeIcon icon="fa-solid fa-child" />
                </div>
                <div class="stat-content">
                    <h3>Enfants</h3>
                    <p>{{ stats.kidCount || 0 }}</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <FontAwesomeIcon icon="fa-solid fa-clock" />
                </div>
                <div class="stat-content">
                    <h3>Relevés</h3>
                    <p>{{ stats.recordCount || 0 }}</p>
                </div>
            </div>
        </div>
        
        <!-- Navigation par onglets -->
        <div class="admin-tabs">
            <div class="tab-header">
                <button 
                    @click="changeTab('users')" 
                    :class="{ active: activeTab === 'users' }"
                >
                    <FontAwesomeIcon icon="fa-solid fa-users" />
                    Utilisateurs
                </button>
                <button 
                    @click="changeTab('kids')" 
                    :class="{ active: activeTab === 'kids' }"
                >
                    <FontAwesomeIcon icon="fa-solid fa-child" />
                    Enfants
                </button>
            </div>
            
            <div class="tab-content">
                <!-- Onglet Utilisateurs -->
                <div v-if="activeTab === 'users'" class="tab-panel">
                    <div class="panel-header">
                        <h2>Gestion des utilisateurs</h2>
                        <div class="filters">
                            <input 
                                type="text" 
                                v-model="userFilters.search" 
                                placeholder="Rechercher un utilisateur..." 
                                class="search-input"
                            />
                            <select v-model="userFilters.role" class="role-filter">
                                <option value="">Tous les rôles</option>
                                <option value="1">Admin</option>
                                <option value="2">Parent</option>
                                <option value="3">Assistant(e) maternel(le)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div v-if="isLoading.users" class="loading">
                        <FontAwesomeIcon icon="fa-solid fa-spinner" spin />
                        Chargement...
                    </div>
                    
                    <div v-else-if="filteredUsers.length === 0" class="no-data">
                        Aucun utilisateur trouvé.
                    </div>
                    
                    <table v-else class="data-table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Inscription</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in filteredUsers" :key="user.id">
                                <!-- Affichage normal (non édité) -->
                                <template v-if="editingUser !== user.id">
                                    <td>{{ user.first_name }} {{ user.last_name }}</td>
                                    <td>{{ user.email }}</td>
                                    <td>
                                        <span :class="getRoleClass(user.role_id)">
                                            {{ getRoleName(user.role_id) }}
                                        </span>
                                    </td>
                                    <td>{{ safeFormatDate(user.created_at) }}</td>
                                    <td class="actions-cell">
                                        <button @click="startEditUser(user)" class="edit-btn" title="Modifier">
                                            <FontAwesomeIcon icon="fa-solid fa-edit" />
                                        </button>
                                        <button 
                                            v-if="user.role_id !== 1" 
                                            @click="confirmDeleteUser(user)" 
                                            class="delete-btn" 
                                            title="Supprimer"
                                        >
                                            <FontAwesomeIcon icon="fa-solid fa-trash" />
                                        </button>
                                    </td>
                                </template>
                                <!-- Formulaire d'édition -->
                                <template v-else>
                                    <td>
                                        <div class="edit-form-row">
                                            <input 
                                                type="text" 
                                                v-model="editUserForm.first_name" 
                                                placeholder="Prénom"
                                                class="edit-input"
                                            />
                                            <input 
                                                type="text" 
                                                v-model="editUserForm.last_name" 
                                                placeholder="Nom"
                                                class="edit-input"
                                            />
                                        </div>
                                    </td>
                                    <td>
                                        <input 
                                            type="email" 
                                            v-model="editUserForm.email" 
                                            placeholder="Email"
                                            class="edit-input full-width"
                                        />
                                    </td>
                                    <td>
                                        <span :class="getRoleClass(user.role_id)">
                                            {{ getRoleName(user.role_id) }}
                                        </span>
                                    </td>
                                    <td>{{ safeFormatDate(user.created_at) }}</td>
                                    <td class="actions-cell">
                                        <button @click="saveUser" class="save-btn" title="Enregistrer">
                                            <FontAwesomeIcon icon="fa-solid fa-check" />
                                        </button>
                                        <button @click="cancelEdit" class="cancel-btn" title="Annuler">
                                            <FontAwesomeIcon icon="fa-solid fa-times" />
                                        </button>
                                    </td>
                                </template>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Onglet Enfants -->
                <div v-if="activeTab === 'kids'" class="tab-panel">
                    <div class="panel-header">
                        <h2>Gestion des enfants</h2>
                        <div class="filters">
                            <input 
                                type="text" 
                                v-model="kidFilters.search" 
                                placeholder="Rechercher un enfant..." 
                                class="search-input"
                            />
                        </div>
                    </div>
                    
                    <div v-if="isLoading.kids" class="loading">
                        <FontAwesomeIcon icon="fa-solid fa-spinner" spin />
                        Chargement...
                    </div>
                    
                    <div v-else-if="filteredKids.length === 0" class="no-data">
                        Aucun enfant trouvé.
                    </div>
                    
                    <table v-else class="data-table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="kid in filteredKids" :key="kid.id">
                                <td>{{ kid.first_name }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.admin-dashboard {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

h1 {
    color: #358E9D;
    margin-bottom: 2rem;
    text-align: center;
}

.stats-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    display: flex;
    align-items: center;
    padding: 1.5rem;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.stat-icon {
    font-size: 2.5rem;
    color: #358E9D;
    margin-right: 1.5rem;
}

.stat-content {
    flex: 1;
}

.stat-content h3 {
    color: #666;
    font-size: 1rem;
    margin-bottom: 0.5rem;
}

.stat-content p {
    font-size: 2rem;
    font-weight: bold;
    color: #358E9D;
    margin: 0;
}

.admin-tabs {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.tab-header {
    display: flex;
    border-bottom: 1px solid #eee;
    background: #f5f5f5;
}

.tab-header button {
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border: none;
    background: transparent;
    cursor: pointer;
    font-weight: 500;
    color: #666;
    transition: all 0.2s;
}

.tab-header button:hover {
    background: #e9e9e9;
}

.tab-header button.active {
    background: white;
    color: #358E9D;
    border-bottom: 2px solid #358E9D;
}

.tab-content {
    padding: 2rem;
}

.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.panel-header h2 {
    margin: 0;
    font-size: 1.5rem;
    color: #333;
}

.filters {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.search-input, .role-filter, .date-input {
    padding: 0.7rem 1rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 0.9rem;
}

.search-input {
    min-width: 250px;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th, .data-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.data-table th {
    background: #f5f5f5;
    font-weight: 600;
    color: #333;
}

.actions-cell {
    display: flex;
    gap: 0.5rem;
}

.delete-btn, .edit-btn, .save-btn, .cancel-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    color: white;
}

.delete-btn {
    background: #E26D5C;
}

.delete-btn:hover {
    background: #c85a4a;
}

.edit-btn {
    background: #358E9D;
}

.edit-btn:hover {
    background: #2a7a8a;
}

.save-btn {
    background: #78BB99;
}

.save-btn:hover {
    background: #62a682;
}

.cancel-btn {
    background: #777;
}

.cancel-btn:hover {
    background: #666;
}

.role-admin, .role-parent, .role-asmat {
    padding: 0.3rem 0.6rem;
    border-radius: 4px;
    font-size: 0.8rem;
    display: inline-block;
    color: white;
}

.role-admin {
    background: #E26D5C;
}

.role-parent {
    background: #358E9D;
}

.role-asmat {
    background: #78BB99;
}

.loading, .no-data {
    text-align: center;
    padding: 3rem;
    color: #666;
}

.loading {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

/* Styles pour le formulaire d'édition */
.edit-form-row {
    display: flex;
    gap: 0.5rem;
}

.edit-input {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.9rem;
}

.edit-input.full-width {
    width: 100%;
}

@media (max-width: 768px) {
    .panel-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .tab-header {
        overflow-x: auto;
    }
    
    .filters {
        width: 100%;
    }
    
    .data-table {
        display: block;
        overflow-x: auto;
    }
    
    .edit-form-row {
        flex-direction: column;
    }
}
</style>