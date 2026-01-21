<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    attribute: Object,
    stations: Array,
});

const form = useForm({
    station_id: props.attribute.station_id,
    name: props.attribute.name,
    color: props.attribute.color,
    order: props.attribute.order,
    active: props.attribute.active,
});

const submit = () => {
    form.put(route('attributes.update', props.attribute.id));
};
</script>

<template>
    <Head title="Editar Atributo" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Editar Atributo
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Estación</label>
                                <select v-model="form.station_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    <option value="">Seleccione una estación</option>
                                    <option v-for="station in stations" :key="station.id" :value="station.id">
                                        {{ station.division?.name }} - {{ station.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Nombre</label>
                                <input v-model="form.name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Color</label>
                                <select v-model="form.color" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    <option value="verde">Verde</option>
                                    <option value="amarillo">Amarillo</option>
                                    <option value="rojo">Rojo</option>
                                    <option value="gris">Gris</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Orden</label>
                                <input v-model="form.order" type="number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                            </div>

                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input v-model="form.active" type="checkbox" class="rounded border-gray-300" />
                                    <span class="ml-2 text-sm text-gray-700">Activo</span>
                                </label>
                            </div>

                            <div class="flex justify-end space-x-2">
                                <a :href="route('attributes.index')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                                    Cancelar
                                </a>
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700" :disabled="form.processing">
                                    Actualizar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
