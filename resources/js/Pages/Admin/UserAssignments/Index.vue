<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    users: Array,
    divisions: Array,
});

const searchQuery = ref('');
const showImportModal = ref(false);
const showEditModal = ref(false);
const showAssignModal = ref(false);
const externalUsers = ref([]);
const allExternalUsers = ref([]);
const isSearching = ref(false);
const externalSearchQuery = ref('');
const selectedUser = ref(null);
const selectedDivision = ref(null);
const selectedStation = ref(null);

const filteredUsers = computed(() => {
    if (!searchQuery.value) return props.users;
    const query = searchQuery.value.toLowerCase();
    return props.users.filter(user => 
        user.name.toLowerCase().includes(query) ||
        user.email.toLowerCase().includes(query)
    );
});

const filteredStations = computed(() => {
    if (!selectedDivision.value) return [];
    const division = props.divisions.find(d => d.id === selectedDivision.value);
    return division ? division.stations : [];
});

const openImportModal = async () => {
    showImportModal.value = true;
    externalSearchQuery.value = '';
    await loadExternalUsers();
};

const closeImportModal = () => {
    showImportModal.value = false;
    externalUsers.value = [];
    allExternalUsers.value = [];
    externalSearchQuery.value = '';
};

const loadExternalUsers = async () => {
    isSearching.value = true;
    try {
        const response = await axios.get(route('external-users.index'));
        allExternalUsers.value = response.data.users;
        externalUsers.value = response.data.users;
    } catch (error) {
        console.error('Error loading users:', error);
    } finally {
        isSearching.value = false;
    }
};

const filterExternalUsers = () => {
    if (!externalSearchQuery.value) {
        externalUsers.value = allExternalUsers.value;
        return;
    }
    
    const query = externalSearchQuery.value.toLowerCase();
    externalUsers.value = allExternalUsers.value.filter(user => 
        user.name.toLowerCase().includes(query) ||
        user.email.toLowerCase().includes(query) ||
        (user.nomina && user.nomina.toString().includes(query))
    );
};

const importUser = async (externalUserId) => {
    try {
        const response = await axios.post(route('external-users.import'), {
            external_user_id: externalUserId
        });
        
        if (response.data.success) {
            const importedUser = response.data.user;
            closeImportModal();
            
            // Abrir modal de asignación con el usuario recién importado
            selectedUser.value = importedUser;
            selectedDivision.value = null;
            selectedStation.value = null;
            showAssignModal.value = true;
        }
    } catch (error) {
        console.error('Error importing user:', error);
        alert(error.response?.data?.message || 'Error al importar usuario');
    }
};

const openEditModal = (user) => {
    selectedUser.value = user;
    selectedDivision.value = null;
    selectedStation.value = null;
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    selectedUser.value = null;
    selectedDivision.value = null;
    selectedStation.value = null;
};

const addStationToUser = async () => {
    if (!selectedStation.value) {
        alert('Seleccione una estación');
        return;
    }

    try {
        await axios.post(route('user-assignments.assign'), {
            user_id: selectedUser.value.id,
            station_id: selectedStation.value,
        });
        
        selectedDivision.value = null;
        selectedStation.value = null;
        router.reload({ preserveScroll: true });
    } catch (error) {
        console.error('Error assigning station:', error);
        alert('Error al asignar estación');
    }
};

const deleteUser = async (user) => {
    if (!confirm(`¿Está seguro de eliminar al usuario ${user.name}?`)) {
        return;
    }

    try {
        const response = await axios.delete(route('users.destroy', user.id));
        
        if (response.data.success) {
            alert('Usuario eliminado exitosamente');
            router.reload();
        }
    } catch (error) {
        console.error('Error deleting user:', error);
        alert(error.response?.data?.message || 'Error al eliminar usuario');
    }
};

const openAssignModal = (user) => {
    selectedUser.value = user;
    selectedDivision.value = null;
    selectedStation.value = null;
    showAssignModal.value = true;
};

const closeAssignModal = () => {
    showAssignModal.value = false;
    selectedUser.value = null;
    selectedDivision.value = null;
    selectedStation.value = null;
};

const assignStation = async () => {
    if (!selectedStation.value) {
        alert('Seleccione una estación');
        return;
    }

    try {
        await axios.post(route('user-assignments.assign'), {
            user_id: selectedUser.value.id,
            station_id: selectedStation.value,
        });
        
        closeAssignModal();
        router.reload({ preserveScroll: true });
    } catch (error) {
        console.error('Error assigning station:', error);
        alert('Error al asignar estación');
    }
};

const removeAssignment = async (userId, stationId) => {
    if (!confirm('¿Está seguro de eliminar esta asignación?')) {
        return;
    }

    try {
        await axios.post(route('user-assignments.remove'), {
            user_id: userId,
            station_id: stationId,
        });
        
        router.reload();
    } catch (error) {
        console.error('Error removing assignment:', error);
        alert('Error al eliminar asignación');
    }
};
</script>

<template>
    <Head title="Usuarios" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Usuarios
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="mb-6 flex items-center justify-between">
                            <input 
                                v-model="searchQuery" 
                                type="text" 
                                placeholder="Buscar usuarios..."
                                class="flex-1 max-w-md rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                            <button @click="openImportModal" 
                                    class="ml-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                + Agregar usuario
                            </button>
                        </div>

                        <div v-if="filteredUsers.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha de creación</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ user.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ user.email }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ new Date(user.created_at).toLocaleDateString('es-MX') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                            <button @click="openAssignModal(user)" 
                                                    class="text-green-600 hover:text-green-900" 
                                                    title="Asignar estación">
                                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                </svg>
                                            </button>
                                            <button @click="openEditModal(user)" 
                                                    class="text-blue-600 hover:text-blue-900" 
                                                    title="Editar">
                                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button @click="deleteUser(user)" 
                                                    class="text-red-600 hover:text-red-900" 
                                                    title="Eliminar">
                                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-else class="text-center py-8 text-gray-500">
                            No hay usuarios registrados. Haz clic en "Agregar usuario" para importar usuarios.
                        </div>

                        <!-- Mostrar estaciones asignadas -->
                        <div v-if="filteredUsers.length > 0" class="mt-8">
                            <h3 class="text-lg font-semibold mb-4">Estaciones Asignadas</h3>
                            <div v-for="user in filteredUsers" :key="'stations-' + user.id" class="mb-4">
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-semibold text-gray-800">{{ user.name }}</h4>
                                        <span class="text-sm text-gray-600">{{ user.email }}</span>
                                    </div>
                                    <div v-if="user.assigned_stations && user.assigned_stations.length > 0" class="space-y-1">
                                        <div v-for="station in user.assigned_stations" :key="station.id" 
                                             class="inline-flex items-center px-3 py-1 mr-2 mb-2 bg-blue-100 text-blue-800 text-sm rounded">
                                            {{ station.division?.name }} - {{ station.name }}
                                            <button @click="removeAssignment(user.id, station.id)"
                                                    class="ml-2 text-blue-600 hover:text-blue-900">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div v-else class="text-sm text-gray-500 italic">
                                        Sin estaciones asignadas
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para importar usuario -->
        <div v-if="showImportModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeImportModal">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeImportModal"></div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Agregar Usuario</h3>
                            <button @click="closeImportModal" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <p class="text-sm text-gray-600 mb-4">
                            Selecciona un usuario de la lista para agregarlo al sistema.
                        </p>

                        <div class="mb-4">
                            <input 
                                v-model="externalSearchQuery"
                                @input="filterExternalUsers"
                                type="text" 
                                placeholder="Buscar por nombre o email..."
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>

                        <div v-if="isSearching" class="text-center py-4">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                            <p class="text-sm text-gray-600 mt-2">Cargando usuarios...</p>
                        </div>

                        <div v-else-if="externalUsers.length > 0" class="max-h-96 overflow-y-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 sticky top-0">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nómina</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="user in externalUsers" :key="user.id" class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ user.name }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ user.email }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ user.nomina }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <button 
                                                v-if="!user.is_imported"
                                                @click="importUser(user.id)"
                                                class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                                </svg>
                                                Agregar
                                            </button>
                                            <span v-else class="text-xs text-gray-500 italic">Ya importado</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-else class="text-center py-8 text-gray-400">
                            No se encontraron usuarios
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="closeImportModal" 
                                class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:ml-3 sm:w-auto sm:text-sm">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para editar usuario -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeEditModal">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeEditModal"></div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Editar Usuario</h3>
                            <button @click="closeEditModal" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre</label>
                                <input 
                                    :value="selectedUser?.name" 
                                    type="text" 
                                    disabled
                                    class="block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm cursor-not-allowed"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input 
                                    :value="selectedUser?.email" 
                                    type="email" 
                                    disabled
                                    class="block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm cursor-not-allowed"
                                />
                            </div>
                        </div>

                        <hr class="my-6" />

                        <div v-if="selectedUser && selectedUser.assigned_stations && selectedUser.assigned_stations.length > 0" class="mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Estaciones Asignadas:</h4>
                            <div class="space-y-2">
                                <div v-for="station in selectedUser.assigned_stations" :key="station.id" 
                                     class="flex items-center justify-between bg-gray-50 rounded p-3 border border-gray-200">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ station.division?.name }}</p>
                                        <p class="text-xs text-gray-600">{{ station.name }}</p>
                                    </div>
                                    <button @click="removeAssignment(selectedUser.id, station.id)"
                                            class="text-red-600 hover:text-red-900 text-sm font-medium">
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-else class="mb-4 text-sm text-gray-500 italic">
                            Sin estaciones asignadas
                        </div>

                        <h4 class="text-sm font-medium text-gray-700 mb-3">Agregar Nueva Estación:</h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">División</label>
                                <select v-model="selectedDivision" 
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option :value="null">Seleccione una división</option>
                                    <option v-for="division in divisions" :key="division.id" :value="division.id">
                                        {{ division.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Estación</label>
                                <select v-model="selectedStation" 
                                        :disabled="!selectedDivision"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-gray-100">
                                    <option :value="null">Seleccione una estación</option>
                                    <option v-for="station in filteredStations" :key="station.id" :value="station.id">
                                        {{ station.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="addStationToUser" 
                                :disabled="!selectedStation"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed sm:ml-3 sm:w-auto sm:text-sm">
                            Guardar
                        </button>
                        <button @click="closeEditModal" 
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para asignar estación -->
        <div v-if="showAssignModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeAssignModal">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeAssignModal"></div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Asignar Estación</h3>
                            <button @click="closeAssignModal" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div v-if="selectedUser" class="mb-4 p-3 bg-gray-50 rounded">
                            <p class="text-sm font-medium text-gray-700">Usuario seleccionado:</p>
                            <p class="text-base font-semibold text-gray-900">{{ selectedUser.name }}</p>
                            <p class="text-sm text-gray-600">{{ selectedUser.email }}</p>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">División</label>
                                <select v-model="selectedDivision" 
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option :value="null">Seleccione una división</option>
                                    <option v-for="division in divisions" :key="division.id" :value="division.id">
                                        {{ division.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Estación</label>
                                <select v-model="selectedStation" 
                                        :disabled="!selectedDivision"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-gray-100">
                                    <option :value="null">Seleccione una estación</option>
                                    <option v-for="station in filteredStations" :key="station.id" :value="station.id">
                                        {{ station.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="assignStation" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Asignar
                        </button>
                        <button @click="closeAssignModal" 
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
