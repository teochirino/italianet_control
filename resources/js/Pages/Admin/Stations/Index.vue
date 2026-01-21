<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    stations: Array,
});

const deleteStation = (id) => {
    if (confirm('¿Está seguro de eliminar esta estación?')) {
        router.delete(route('stations.destroy', id));
    }
};
</script>

<template>
    <Head title="Gestionar Estaciones" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Gestionar Estaciones
                </h2>
                <Link :href="route('stations.create')" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Nueva Estación
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">División</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Color</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Orden</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="station in stations" :key="station.id">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ station.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ station.division?.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full text-white"
                                              :class="station.color === 'rojo' ? 'bg-red-500' : 
                                                      station.color === 'amarillo' ? 'bg-yellow-400' : 
                                                      station.color === 'verde' ? 'bg-green-500' : 'bg-gray-400'">
                                            {{ station.color }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ station.order }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="station.active ? 'text-green-600' : 'text-red-600'">
                                            {{ station.active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <Link :href="route('stations.edit', station.id)" class="text-blue-600 hover:text-blue-900">
                                            Editar
                                        </Link>
                                        <button @click="deleteStation(station.id)" class="text-red-600 hover:text-red-900">
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
