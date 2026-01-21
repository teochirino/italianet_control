<script setup>
import { ref, computed, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import axios from 'axios';

const props = defineProps({
    show: Boolean,
    stationId: Number,
    attributeId: Number,
    stationName: String,
    attributeName: String,
});

const emit = defineEmits(['close']);

const histories = ref([]);
const loading = ref(false);
const currentPage = ref(1);
const lastPage = ref(1);
const total = ref(0);

const getColorClass = (color) => {
    const colors = {
        'rojo': 'bg-red-500',
        'amarillo': 'bg-yellow-400',
        'verde': 'bg-green-500',
        'gris': 'bg-gray-400',
    };
    return colors[color] || 'bg-gray-300';
};

const getColorName = (color) => {
    const names = {
        'rojo': 'Rojo',
        'amarillo': 'Amarillo',
        'verde': 'Verde',
        'gris': 'Gris',
    };
    return names[color] || color;
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleString('es-MX', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
};

const title = computed(() => {
    if (props.attributeId) {
        return `Historial de ${props.attributeName}`;
    }
    return `Historial de ${props.stationName}`;
});

const fetchHistory = async (page = 1) => {
    loading.value = true;
    try {
        let url = '';
        if (props.attributeId) {
            url = `/admin/attributes/${props.attributeId}/history?page=${page}`;
        } else if (props.stationId) {
            url = `/admin/stations/${props.stationId}/history?page=${page}`;
        }

        const response = await axios.get(url);
        histories.value = response.data.histories.data;
        currentPage.value = response.data.histories.current_page;
        lastPage.value = response.data.histories.last_page;
        total.value = response.data.histories.total;
    } catch (error) {
        console.error('Error fetching history:', error);
        alert('Error al cargar el historial');
    } finally {
        loading.value = false;
    }
};

const nextPage = () => {
    if (currentPage.value < lastPage.value) {
        fetchHistory(currentPage.value + 1);
    }
};

const prevPage = () => {
    if (currentPage.value > 1) {
        fetchHistory(currentPage.value - 1);
    }
};

watch(() => props.show, (newValue) => {
    if (newValue) {
        fetchHistory(1);
    } else {
        histories.value = [];
        currentPage.value = 1;
    }
});
</script>

<template>
    <Modal :show="show" @close="emit('close')" max-width="4xl">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">{{ title }}</h2>
                <button @click="emit('close')" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div v-if="loading" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
                <p class="mt-2 text-gray-600">Cargando historial...</p>
            </div>

            <div v-else-if="histories.length === 0" class="text-center py-8 text-gray-500">
                No hay cambios registrados
            </div>

            <div v-else>
                <div class="mb-4 text-sm text-gray-600">
                    Total de cambios: {{ total }}
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th v-if="!attributeId" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Atributo
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Color Anterior
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Color Nuevo
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Usuario
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fecha y Hora
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="history in histories" :key="history.id" class="hover:bg-gray-50">
                                <td v-if="!attributeId" class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ history.attribute.name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-2">
                                        <div :class="['w-6 h-6 rounded-full', getColorClass(history.previous_color)]"></div>
                                        <span class="text-sm text-gray-700">{{ getColorName(history.previous_color) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-2">
                                        <div :class="['w-6 h-6 rounded-full', getColorClass(history.new_color)]"></div>
                                        <span class="text-sm text-gray-700">{{ getColorName(history.new_color) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ history.user.name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatDate(history.created_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="lastPage > 1" class="mt-6 flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Página {{ currentPage }} de {{ lastPage }}
                    </div>
                    <div class="flex space-x-2">
                        <button
                            @click="prevPage"
                            :disabled="currentPage === 1"
                            :class="[
                                'px-4 py-2 text-sm font-medium rounded-md',
                                currentPage === 1
                                    ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                    : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
                            ]"
                        >
                            Anterior
                        </button>
                        <button
                            @click="nextPage"
                            :disabled="currentPage === lastPage"
                            :class="[
                                'px-4 py-2 text-sm font-medium rounded-md',
                                currentPage === lastPage
                                    ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                    : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
                            ]"
                        >
                            Siguiente
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Modal>
</template>
