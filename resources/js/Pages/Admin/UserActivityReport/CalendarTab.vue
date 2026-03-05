<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import ActivityDetailModal from './ActivityDetailModal.vue';

const props = defineProps({
    users: Array,
});

const selectedUserId = ref(null);
const calendarRef = ref(null);
const calendarOptions = ref({
    plugins: [dayGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    locale: 'es',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: ''
    },
    buttonText: {
        today: 'Hoy'
    },
    events: [],
    dateClick: handleDateClick,
    dayCellContent: customDayCell,
    height: 'auto',
    datesSet: loadCalendarData,
});

const showModal = ref(false);
const selectedDate = ref('');
const dayActivities = ref([]);
const isLoadingModal = ref(false);

const activityDays = ref(new Map());

async function loadCalendarData(info) {
    let startDate, endDate;
    
    if (info && info.start && info.end) {
        startDate = info.start.toISOString().split('T')[0];
        endDate = info.end.toISOString().split('T')[0];
    } else {
        const now = new Date();
        const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
        const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
        startDate = firstDay.toISOString().split('T')[0];
        endDate = lastDay.toISOString().split('T')[0];
    }

    try {
        const response = await axios.get(route('user-activity-report.calendar-data'), {
            params: {
                user_id: selectedUserId.value,
                start_date: startDate,
                end_date: endDate,
            },
        });

        activityDays.value.clear();
        response.data.events.forEach(event => {
            activityDays.value.set(event.date, {
                logins_count: event.logins_count,
                changes_count: event.changes_count,
                has_logins: event.has_logins,
                has_changes: event.has_changes,
            });
        });

        calendarOptions.value.events = response.data.events.map(event => ({
            date: event.date,
            display: 'background',
            backgroundColor: getEventColor(event),
        }));
    } catch (error) {
        console.error('Error loading calendar data:', error);
    }
}

function getEventColor(event) {
    if (event.has_logins && event.has_changes) {
        return '#fb923c';
    } else if (event.has_logins) {
        return '#3b82f6';
    } else if (event.has_changes) {
        return '#10b981';
    }
    return 'transparent';
}

function customDayCell(arg) {
    const dateStr = arg.date.toISOString().split('T')[0];
    const dayData = activityDays.value.get(dateStr);
    
    if (dayData) {
        const container = document.createElement('div');
        container.className = 'relative';
        
        const dayNumber = document.createElement('div');
        dayNumber.className = 'fc-daygrid-day-number';
        dayNumber.textContent = arg.dayNumberText;
        
        const badge = document.createElement('div');
        badge.className = 'absolute top-0 right-0 flex space-x-1 text-xs';
        
        if (dayData.has_logins) {
            const loginBadge = document.createElement('span');
            loginBadge.className = 'bg-blue-500 text-white rounded-full w-5 h-5 flex items-center justify-center';
            loginBadge.textContent = dayData.logins_count;
            loginBadge.title = `${dayData.logins_count} login(s)`;
            badge.appendChild(loginBadge);
        }
        
        if (dayData.has_changes) {
            const changeBadge = document.createElement('span');
            changeBadge.className = 'bg-green-500 text-white rounded-full w-5 h-5 flex items-center justify-center';
            changeBadge.textContent = dayData.changes_count;
            changeBadge.title = `${dayData.changes_count} cambio(s)`;
            badge.appendChild(changeBadge);
        }
        
        container.appendChild(dayNumber);
        container.appendChild(badge);
        
        return { domNodes: [container] };
    }
    
    return { domNodes: [] };
}

async function handleDateClick(info) {
    selectedDate.value = info.dateStr;
    showModal.value = true;
    isLoadingModal.value = true;
    
    try {
        const response = await axios.get(route('user-activity-report.day-activities'), {
            params: {
                date: info.dateStr,
                user_id: selectedUserId.value,
            },
        });
        
        dayActivities.value = response.data.activities;
    } catch (error) {
        console.error('Error loading day activities:', error);
        alert('Error al cargar las actividades del día');
    } finally {
        isLoadingModal.value = false;
    }
}

function closeModal() {
    showModal.value = false;
    selectedDate.value = '';
    dayActivities.value = [];
}

watch(selectedUserId, () => {
    if (calendarRef.value) {
        const calendarApi = calendarRef.value.getApi();
        const view = calendarApi.view;
        loadCalendarData({
            start: view.activeStart,
            end: view.activeEnd
        });
    }
});
</script>

<template>
    <div>
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Filtrar por Usuario</label>
            <select 
                v-model="selectedUserId"
                class="block w-full md:w-1/3 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
                <option :value="null">Todos los usuarios</option>
                <option v-for="user in users" :key="user.id" :value="user.id">
                    {{ user.name }} {{ user.is_admin ? '(Admin)' : '' }}
                </option>
            </select>
        </div>

        <div class="mb-4 p-4 bg-gray-50 rounded-lg">
            <div class="text-sm font-medium text-gray-700 mb-2">Leyenda:</div>
            <div class="flex flex-wrap gap-4">
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-blue-500 rounded mr-2"></div>
                    <span class="text-sm text-gray-600">Solo logins</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-green-500 rounded mr-2"></div>
                    <span class="text-sm text-gray-600">Solo cambios de color</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-orange-400 rounded mr-2"></div>
                    <span class="text-sm text-gray-600">Logins y cambios</span>
                </div>
            </div>
            <div class="mt-2 text-xs text-gray-500">
                Haz clic en cualquier día para ver los detalles de las actividades
            </div>
        </div>

        <div class="calendar-container">
            <FullCalendar ref="calendarRef" :options="calendarOptions" />
        </div>

        <ActivityDetailModal
            :show="showModal"
            :date="selectedDate"
            :activities="dayActivities"
            :is-loading="isLoadingModal"
            @close="closeModal"
        />
    </div>
</template>

<style>
.fc {
    font-family: inherit;
}

.fc .fc-button {
    background-color: #3b82f6;
    border-color: #3b82f6;
    text-transform: capitalize;
}

.fc .fc-button:hover {
    background-color: #2563eb;
    border-color: #2563eb;
}

.fc .fc-button:disabled {
    background-color: #9ca3af;
    border-color: #9ca3af;
}

.fc .fc-daygrid-day {
    cursor: pointer;
}

.fc .fc-daygrid-day:hover {
    background-color: #f3f4f6;
}

.fc .fc-daygrid-day-number {
    padding: 4px;
}

.fc-day-today {
    background-color: #dbeafe !important;
}
</style>
