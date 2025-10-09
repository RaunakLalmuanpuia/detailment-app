<template>
    <div class="q-pa-md">
        <q-card class="shadow-2 rounded-borders">
            <vue-cal
                class="calendar"
                style="height: 80vh"
                default-view="month"
                :disable-views="['years']"
                time-from="06:00"
                time-to="22:00"
                :events="events"
                :on-event-click="handleEventClick"
            />
        </q-card>

        <AssignmentDrawer
            v-model="showDrawer"
            :event="selectedEvent"
            @updated="fetchEvents"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import VueCal from 'vue-cal'
import 'vue-cal/dist/vuecal.css'
import AssignmentDrawer from '@/Components/Calendar/AssignmentDrawer.vue'

const events = ref([])
const showDrawer = ref(false)
const selectedEvent = ref(null)

const fetchEvents = async () => {
    try {
        const res = await axios.get('/api/events')
        events.value = res.data.map(e => ({
            id: e.id,
            start: `${e.date}T${e.start_time}`,
            end: `${e.date}T${e.end_time}`,
            title: e.title,
            class: e.status === 'upcoming' ? 'bg-green-2' : 'bg-grey-3',
            raw: e
        }))
    } catch (err) {
        console.error('Error loading events:', err)
    }
}

const handleEventClick = ({ event }) => {
    selectedEvent.value = event.raw
    showDrawer.value = true
}

onMounted(fetchEvents)
</script>

<style scoped>
.calendar {
    --vuecal-primary: #1976d2;
}
</style>
