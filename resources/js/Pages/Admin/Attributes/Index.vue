<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    attributes: Array,
});

const deleteAttribute = (id) => {
    if (confirm('¿Está seguro de eliminar este atributo?')) {
        router.delete(route('attributes.destroy', id));
    }
};
</script>

<template>
    <Head title="Gestionar Atributos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Gestionar Atributos
                </h2>
                <Link :href="route('attributes.create')" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Nuevo Atributo
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <table class="w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estación</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">División</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-24">Color</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-16">Orden</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-20">Estado</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-32">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="attribute in attributes" :key="attribute.id">
                                    <td class="px-3 py-3 font-medium">{{ attribute.name }}</td>
                                    <td class="px-3 py-3">{{ attribute.station?.name }}</td>
                                    <td class="px-3 py-3">{{ attribute.station?.division?.name }}</td>
                                    <td class="px-3 py-3">
                                        <span class="px-2 py-1 text-xs rounded-full text-white whitespace-nowrap"
                                              :class="attribute.color === 'rojo' ? 'bg-red-500' : 
                                                      attribute.color === 'amarillo' ? 'bg-yellow-400' : 
                                                      attribute.color === 'verde' ? 'bg-green-500' : 'bg-gray-400'">
                                            {{ attribute.color }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-center">{{ attribute.order }}</td>
                                    <td class="px-3 py-3">
                                        <span :class="attribute.active ? 'text-green-600' : 'text-red-600'" class="whitespace-nowrap">
                                            {{ attribute.active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap">
                                        <Link :href="route('attributes.edit', attribute.id)" class="text-blue-600 hover:text-blue-900 mr-2">
                                            Editar
                                        </Link>
                                        <button @click="deleteAttribute(attribute.id)" class="text-red-600 hover:text-red-900">
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
