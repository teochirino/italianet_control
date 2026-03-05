<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { exportToExcel, prepareActivityDataForExport } from '@/utils/exportToExcel';

const props = defineProps({
    users: Array,
});

const selectedUserId = ref(null);
const startDate = ref('');
const endDate = ref('');
const activityType = ref('all');
const activities = ref([]);
const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 25,
    total: 0,
});
const stats = ref({
    total_logins: 0,
    total_changes: 0,
    total_activities: 0,
});
const isLoading = ref(false);

const activityTypeOptions = [
    { value: 'all', label: 'Todas las actividades' },
    { value: 'logins', label: 'Solo logins' },
    { value: 'changes', label: 'Solo cambios de color' },
];

const setCurrentMonth = () => {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const firstDay = `${year}-${month}-01`;
    const lastDay = new Date(year, now.getMonth() + 1, 0);
    const lastDayStr = `${year}-${month}-${String(lastDay.getDate()).padStart(2, '0')}`;
    
    startDate.value = firstDay;
    endDate.value = lastDayStr;
};

const loadActivities = async (page = 1) => {
    isLoading.value = true;
    try {
        const response = await axios.get(route('user-activity-report.data'), {
            params: {
                user_id: selectedUserId.value,
                start_date: startDate.value,
                end_date: endDate.value,
                activity_type: activityType.value,
                page: page,
            },
        });
        
        activities.value = response.data.activities;
        pagination.value = response.data.pagination;
        stats.value = response.data.stats;
    } catch (error) {
        console.error('Error loading activities:', error);
        alert('Error al cargar las actividades');
    } finally {
        isLoading.value = false;
    }
};

const handleExport = () => {
    const exportData = prepareActivityDataForExport(activities.value);
    const filename = `reporte_actividades_${startDate.value}_${endDate.value}.xlsx`;
    exportToExcel(exportData, filename);
};

const formatDateTime = (datetime) => {
    return new Date(datetime).toLocaleString('es-MX', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
};

const getActivityIcon = (type) => {
    return type === 'login' ? 'Login' : 'Operación';
};

const getColorClass = (color) => {
    const colors = {
        'rojo': 'bg-red-500',
        'amarillo': 'bg-yellow-400',
        'verde': 'bg-green-500',
        'gris': 'bg-gray-400',
    };
    return colors[color] || 'bg-gray-300';
};

const goToPage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        loadActivities(page);
    }
};

onMounted(() => {
    setCurrentMonth();
    loadActivities();
});
</script>

<template>
    <div>
        <div class="mb-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Usuario</label>
                <select 
                    v-model="selectedUserId" 
                    @change="loadActivities(1)"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                    <option :value="null">Todos los usuarios</option>
                    <option v-for="user in users" :key="user.id" :value="user.id">
                        {{ user.name }} {{ user.is_admin ? '(Admin)' : '' }}
                    </option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Inicio</label>
                <input 
                    v-model="startDate" 
                    type="date"
                    @change="loadActivities(1)"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Fin</label>
                <input 
                    v-model="endDate" 
                    type="date"
                    @change="loadActivities(1)"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Actividad</label>
                <select 
                    v-model="activityType" 
                    @change="loadActivities(1)"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                    <option v-for="option in activityTypeOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
            </div>
        </div>

        <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <div class="text-sm text-blue-600 font-medium">Total Actividades</div>
                <div class="text-2xl font-bold text-blue-900">{{ stats.total_activities }}</div>
            </div>
            <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                <div class="text-sm text-green-600 font-medium">Total Logins</div>
                <div class="text-2xl font-bold text-green-900">{{ stats.total_logins }}</div>
            </div>
            <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                <div class="text-sm text-purple-600 font-medium">Total Cambios</div>
                <div class="text-2xl font-bold text-purple-900">{{ stats.total_changes }}</div>
            </div>
        </div>

        <div class="mb-4 flex justify-between items-center">
            <div class="text-sm text-gray-600">
                Mostrando {{ activities.length }} de {{ pagination.total }} actividades
            </div>
            <button 
                @click="handleExport"
                :disabled="activities.length === 0"
                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Exportar a Excel
            </button>
        </div>

        <div v-if="isLoading" class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="text-sm text-gray-600 mt-2">Cargando actividades...</p>
        </div>

        <div v-else-if="activities.length === 0" class="text-center py-8 text-gray-500">
            No se encontraron actividades para los filtros seleccionados
        </div>

        <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha y Hora</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detalles</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="activity in activities" :key="activity.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="font-semibold text-gray-700">{{ getActivityIcon(activity.type) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ formatDateTime(activity.datetime) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ activity.user.name }}</div>
                            <div class="text-sm text-gray-500">{{ activity.user.email }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div v-if="activity.type === 'login'">
                                <div class="font-medium">Inicio de sesión</div>
                                <div class="text-xs text-gray-500 mt-1">
                                    <div>IP: {{ activity.ip_address || 'N/A' }}</div>
                                    <div class="truncate max-w-md" :title="activity.user_agent">
                                        Navegador: {{ activity.user_agent ? activity.user_agent.substring(0, 60) + '...' : 'N/A' }}
                                    </div>
                                </div>
                            </div>
                            <div v-else>
                                <div class="font-medium">{{ activity.station?.name }} - {{ activity.attribute?.name }}</div>
                                <div class="flex items-center mt-1 space-x-2">
                                    <div :class="['w-4 h-4 rounded-full', getColorClass(activity.previous_color)]"></div>
                                    <span class="text-xs">→</span>
                                    <div :class="['w-4 h-4 rounded-full', getColorClass(activity.new_color)]"></div>
                                    <span class="text-xs text-gray-600">
                                        ({{ activity.previous_color }} → {{ activity.new_color }})
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ activity.comment }}
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="pagination.last_page > 1" class="mt-6 flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Página {{ pagination.current_page }} de {{ pagination.last_page }}
            </div>
            <div class="flex space-x-2">
                <button 
                    @click="goToPage(pagination.current_page - 1)"
                    :disabled="pagination.current_page === 1"
                    class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:bg-gray-100 disabled:cursor-not-allowed"
                >
                    Anterior
                </button>
                <button 
                    @click="goToPage(pagination.current_page + 1)"
                    :disabled="pagination.current_page === pagination.last_page"
                    class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:bg-gray-100 disabled:cursor-not-allowed"
                >
                    Siguiente
                </button>
            </div>
        </div>
    </div>
</template>
