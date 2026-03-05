<script setup>
import { computed } from 'vue';

const props = defineProps({
    show: Boolean,
    date: String,
    activities: Array,
    isLoading: Boolean,
});

const emit = defineEmits(['close']);

const formattedDate = computed(() => {
    if (!props.date) return '';
    return new Date(props.date).toLocaleDateString('es-MX', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
});

const formatTime = (datetime) => {
    return new Date(datetime).toLocaleTimeString('es-MX', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
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

const getActivityIcon = (type) => {
    return type === 'login' ? 'Login' : 'Operación';
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" @click.self="emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="emit('close')"></div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Actividades del {{ formattedDate }}
                        </h3>
                        <button @click="emit('close')" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div v-if="isLoading" class="text-center py-8">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <p class="text-sm text-gray-600 mt-2">Cargando actividades...</p>
                    </div>

                    <div v-else-if="activities.length === 0" class="text-center py-8 text-gray-500">
                        No hay actividades registradas para este día
                    </div>

                    <div v-else class="max-h-96 overflow-y-auto">
                        <div class="space-y-4">
                            <div 
                                v-for="(activity, index) in activities" 
                                :key="index"
                                class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50"
                            >
                                <div class="flex items-start space-x-3">
                                    <span class="font-semibold text-gray-700 text-sm">{{ getActivityIcon(activity.type) }}</span>
                                    
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between mb-2">
                                            <div>
                                                <div class="font-medium text-gray-900">{{ activity.user.name }}</div>
                                                <div class="text-sm text-gray-500">{{ activity.user.email }}</div>
                                            </div>
                                            <div class="text-sm text-gray-600">
                                                {{ formatTime(activity.datetime) }}
                                            </div>
                                        </div>

                                        <div v-if="activity.type === 'login'" class="mt-2 text-sm">
                                            <div class="text-gray-700 font-medium mb-1">Inicio de sesión</div>
                                            <div class="text-xs text-gray-500 space-y-1">
                                                <div>
                                                    <span class="font-medium">IP:</span> {{ activity.ip_address || 'N/A' }}
                                                </div>
                                                <div class="truncate" :title="activity.user_agent">
                                                    <span class="font-medium">Navegador:</span> 
                                                    {{ activity.user_agent ? activity.user_agent.substring(0, 80) + '...' : 'N/A' }}
                                                </div>
                                            </div>
                                        </div>

                                        <div v-else class="mt-2 text-sm">
                                            <div class="text-gray-700 font-medium mb-1">
                                                {{ activity.station?.name }} - {{ activity.attribute?.name }}
                                            </div>
                                            <div class="flex items-center space-x-2 mb-2">
                                                <div :class="['w-6 h-6 rounded-full', getColorClass(activity.previous_color)]"></div>
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                                </svg>
                                                <div :class="['w-6 h-6 rounded-full', getColorClass(activity.new_color)]"></div>
                                                <span class="text-xs text-gray-600">
                                                    ({{ activity.previous_color }} → {{ activity.new_color }})
                                                </span>
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                <span class="font-medium">Comentario:</span> {{ activity.comment || 'Sin comentarios' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button 
                        @click="emit('close')" 
                        class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:w-auto sm:text-sm"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
