<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    show: Boolean,
    currentColor: String,
    newColor: String,
    attributeName: String,
});

const emit = defineEmits(['close', 'confirm']);

const comment = ref('');

const colorNames = {
    'rojo': 'Rojo',
    'amarillo': 'Amarillo',
    'verde': 'Verde',
    'gris': 'Gris',
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

const handleConfirm = () => {
    emit('confirm', comment.value);
    comment.value = '';
};

const handleClose = () => {
    comment.value = '';
    emit('close');
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" @click.self="handleClose">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="handleClose"></div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Confirmar Cambio de Color</h3>
                        <button @click="handleClose" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2">Atributo: <span class="font-semibold">{{ attributeName }}</span></p>
                    </div>

                    <div class="mb-6 bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-center space-x-4">
                            <div class="flex flex-col items-center">
                                <span class="text-xs text-gray-600 mb-2">Color Actual</span>
                                <div :class="['w-16 h-16 rounded-full', getColorClass(currentColor)]"></div>
                                <span class="text-sm font-medium text-gray-800 mt-2">{{ colorNames[currentColor] }}</span>
                            </div>
                            
                            <div class="text-gray-400">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </div>
                            
                            <div class="flex flex-col items-center">
                                <span class="text-xs text-gray-600 mb-2">Nuevo Color</span>
                                <div :class="['w-16 h-16 rounded-full', getColorClass(newColor)]"></div>
                                <span class="text-sm font-medium text-gray-800 mt-2">{{ colorNames[newColor] }}</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Comentarios
                        </label>
                        <textarea 
                            v-model="comment"
                            rows="3"
                            maxlength="100"
                            placeholder="Ingrese un comentario (opcional)"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        ></textarea>
                        <p class="text-xs text-gray-500 mt-1">{{ comment.length }}/100 caracteres</p>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="handleConfirm" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">
                        Cambio de color
                    </button>
                    <button @click="handleClose" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
