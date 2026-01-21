<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    divisions: Array,
});

const deleteDivision = (id) => {
    if (confirm('¿Está seguro de eliminar esta división?')) {
        router.delete(route('divisions.destroy', id));
    }
};
</script>

<template>
    <Head title="Gestionar Divisiones" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Gestionar Divisiones
                </h2>
                <Link :href="route('divisions.create')" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Nueva División
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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Color</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Orden</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="division in divisions" :key="division.id">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ division.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full text-white"
                                              :class="division.color === 'rojo' ? 'bg-red-500' : 
                                                      division.color === 'amarillo' ? 'bg-yellow-400' : 
                                                      division.color === 'verde' ? 'bg-green-500' : 'bg-gray-400'">
                                            {{ division.color }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ division.order }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="division.active ? 'text-green-600' : 'text-red-600'">
                                            {{ division.active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <Link :href="route('divisions.edit', division.id)" class="text-blue-600 hover:text-blue-900">
                                            Editar
                                        </Link>
                                        <button @click="deleteDivision(division.id)" class="text-red-600 hover:text-red-900">
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
