<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import ReportTab from './ReportTab.vue';
import CalendarTab from './CalendarTab.vue';

const props = defineProps({
    users: Array,
});

const activeTab = ref('report');
</script>

<template>
    <Head title="Reportes de Actividad de Usuarios" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Reportes de Actividad de Usuarios
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="border-b border-gray-200 mb-6">
                            <nav class="-mb-px flex space-x-8">
                                <button
                                    @click="activeTab = 'report'"
                                    :class="[
                                        activeTab === 'report'
                                            ? 'border-blue-500 text-blue-600'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                        'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                                    ]"
                                >
                                    📊 Reporte
                                </button>
                                <button
                                    @click="activeTab = 'calendar'"
                                    :class="[
                                        activeTab === 'calendar'
                                            ? 'border-blue-500 text-blue-600'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                        'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                                    ]"
                                >
                                    📅 Calendario
                                </button>
                            </nav>
                        </div>

                        <ReportTab v-if="activeTab === 'report'" :users="users" />
                        <CalendarTab v-if="activeTab === 'calendar'" :users="users" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
