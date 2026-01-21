<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ColorHistoryModal from '@/Components/ColorHistoryModal.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    divisions: Array,
    isAdmin: Boolean,
});

const divisions = ref(props.divisions);
let refreshInterval = null;

const showHistoryModal = ref(false);
const historyStationId = ref(null);
const historyAttributeId = ref(null);
const historyStationName = ref('');
const historyAttributeName = ref('');

const getColorClass = (color) => {
    const colors = {
        'rojo': 'bg-red-500',
        'amarillo': 'bg-yellow-400',
        'verde': 'bg-green-500',
        'gris': 'bg-gray-400',
    };
    return colors[color] || 'bg-gray-300';
};

const updateColor = async (attributeId, color) => {
    try {
        const response = await axios.post(`/attributes/${attributeId}/update-color`, {
            color: color
        });
        
        if (response.data.success) {
            refreshData();
        }
    } catch (error) {
        console.error('Error updating color:', error);
        alert('Error al actualizar el color');
    }
};

const showStationHistory = (station) => {
    historyStationId.value = station.id;
    historyAttributeId.value = null;
    historyStationName.value = station.name;
    historyAttributeName.value = '';
    showHistoryModal.value = true;
};

const showAttributeHistory = (station, attribute) => {
    historyStationId.value = station.id;
    historyAttributeId.value = attribute.id;
    historyStationName.value = station.name;
    historyAttributeName.value = attribute.name;
    showHistoryModal.value = true;
};

const closeHistoryModal = () => {
    showHistoryModal.value = false;
    historyStationId.value = null;
    historyAttributeId.value = null;
    historyStationName.value = '';
    historyAttributeName.value = '';
};

const refreshData = async () => {
    try {
        const response = await axios.get('/api/dashboard-data');
        divisions.value = response.data.divisions;
    } catch (error) {
        console.error('Error refreshing data:', error);
    }
};

onMounted(() => {
    refreshInterval = setInterval(refreshData, 5000);
});

onUnmounted(() => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>

<template>
    <Head title="Panel de Control de Producción" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Panel de Control de Producción
                </h2>
                <div v-if="isAdmin" class="space-x-2">
                    <Link :href="route('divisions.index')" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Gestionar Divisiones
                    </Link>
                    <Link :href="route('stations.index')" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Gestionar Estaciones
                    </Link>
                    <Link :href="route('attributes.index')" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Gestionar Atributos
                    </Link>
                    <Link :href="route('user-assignments.index')" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        Asignar Usuarios
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div v-if="divisions.length === 0" class="bg-white rounded-lg shadow p-6 text-center text-gray-500">
                    No hay divisiones disponibles
                </div>

                <div v-for="division in divisions" :key="division.id" class="mb-8">
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div :class="['w-6 h-6 rounded-full', getColorClass(division.color)]"></div>
                                <h3 class="text-2xl font-bold text-gray-800">{{ division.name }}</h3>
                            </div>
                        </div>

                        <div class="p-6">
                            <div v-if="division.stations.length === 0" class="text-center text-gray-500 py-4">
                                No hay estaciones en esta división
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div v-for="station in division.stations" :key="station.id" 
                                     class="border-2 rounded-lg overflow-hidden"
                                     :class="station.color === 'rojo' ? 'border-red-500' : 
                                             station.color === 'amarillo' ? 'border-yellow-400' : 
                                             station.color === 'verde' ? 'border-green-500' : 
                                             'border-gray-400'">
                                    
                                    <div class="px-4 py-3 flex items-center justify-between"
                                         :class="getColorClass(station.color)">
                                        <h4 class="text-lg font-semibold text-white">{{ station.name }}</h4>
                                        <button v-if="isAdmin" 
                                                @click="showStationHistory(station)"
                                                class="text-white hover:text-gray-200 transition-colors"
                                                title="Ver historial de la estación">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="p-4 bg-gray-50">
                                        <div v-if="station.attributes.length === 0" class="text-sm text-gray-500 text-center py-2">
                                            Sin atributos
                                        </div>

                                        <div v-for="attribute in station.attributes" :key="attribute.id" 
                                             class="mb-3 last:mb-0">
                                            <div class="flex items-center justify-between bg-white rounded-lg p-3 shadow-sm">
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-sm font-medium text-gray-700">
                                                        {{ attribute.name }}
                                                    </span>
                                                    <button v-if="isAdmin" 
                                                            @click="showAttributeHistory(station, attribute)"
                                                            class="text-gray-400 hover:text-gray-600 transition-colors"
                                                            title="Ver historial del atributo">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                    </button>
                                                </div>
                                                <div class="flex space-x-1">
                                                    <button @click="updateColor(attribute.id, 'rojo')"
                                                            :class="['w-8 h-8 rounded-full transition-all', 
                                                                     attribute.color === 'rojo' ? 'bg-red-500 ring-2 ring-red-300' : 'bg-red-300 hover:bg-red-400']"
                                                            title="Rojo">
                                                    </button>
                                                    <button @click="updateColor(attribute.id, 'amarillo')"
                                                            :class="['w-8 h-8 rounded-full transition-all', 
                                                                     attribute.color === 'amarillo' ? 'bg-yellow-400 ring-2 ring-yellow-300' : 'bg-yellow-200 hover:bg-yellow-300']"
                                                            title="Amarillo">
                                                    </button>
                                                    <button @click="updateColor(attribute.id, 'verde')"
                                                            :class="['w-8 h-8 rounded-full transition-all', 
                                                                     attribute.color === 'verde' ? 'bg-green-500 ring-2 ring-green-300' : 'bg-green-300 hover:bg-green-400']"
                                                            title="Verde">
                                                    </button>
                                                    <button @click="updateColor(attribute.id, 'gris')"
                                                            :class="['w-8 h-8 rounded-full transition-all', 
                                                                     attribute.color === 'gris' ? 'bg-gray-400 ring-2 ring-gray-300' : 'bg-gray-200 hover:bg-gray-300']"
                                                            title="Gris">
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ColorHistoryModal
            :show="showHistoryModal"
            :station-id="historyStationId"
            :attribute-id="historyAttributeId"
            :station-name="historyStationName"
            :attribute-name="historyAttributeName"
            @close="closeHistoryModal"
        />
    </AuthenticatedLayout>
</template>
