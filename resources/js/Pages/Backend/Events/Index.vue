<template>
    <q-page class="container q-py-lg"> <!-- Added vertical padding to the container -->
        <div class="row items-center q-mb-lg"> <!-- Increased margin bottom -->
            <div class="text-h4 text-weight-bold text-grey-9">Events Overview</div> <!-- More prominent title -->
        </div>

        <div class="row q-col-gutter-md"> <!-- Added column gutter for spacing -->
            <!-- Left: Date Picker -->
            <div class="col-xs-12 col-md-3">
                <q-card flat bordered class="rounded-borders-lg shadow-sm">
                    <q-card-section>
                        <div class="text-h6 text-grey-8 q-mb-md">Select Date</div>
                        <q-date
                            v-model="selectedDate"
                            mask="YYYY-MM-DD"
                            @update:model-value="onDateChange"
                            minimal
                            class="full-width no-shadow rounded-borders-md"
                            color="primary"
                        text-color="dark"
                        />
                    </q-card-section>
                </q-card>
            </div>

            <!-- Right: Calendar -->
            <div class="col-xs-12 col-md-9"> <!-- Responsive column sizing -->
                <vue-cal
                    ref="vuecalRef"
                    :events="displayedEvents"
                    default-view="month"
                    style="height:72vh;"
                    @event-click="onEventClick"
                    class="vuecal-modern"
                    :disable-views="['year']"
                    active-view="month"
                    :time-from="8 * 60"
                    :time-to="20 * 60"
                    :time-step="60"
                />
            </div>
        </div>

        <!-- Event Details Drawer -->
        <q-drawer
            v-model="drawer"
            side="right"
            :width="420"
            :breakpoint="767"
            overlay
            elevated
        >
            <q-toolbar class="bg-primary text-white"> <!-- Uses Quasar's primary color -->
                <q-btn flat round dense icon="close" @click="drawer = false" />
                <q-toolbar-title class="text-weight-medium">Event Details</q-toolbar-title> <!-- Bolder title -->
            </q-toolbar>

            <div v-if="activeEvent" class="q-pa-md">
                <div class="text-h5 text-weight-bold q-mb-sm">{{ activeEvent.title }}</div>
                <div class="text-caption text-grey-7 q-mb-md">
                    <q-icon name="event" class="q-mr-xs" size="xs" />
                    {{ formatFullDateTime(activeEvent.start) }} — {{ formatFullDateTime(activeEvent.end) }}
                </div>
                <div class="text-subtitle1 text-grey-8 q-mb-md">
                    <q-icon name="place" class="q-mr-xs" size="xs" />
                    Location: <strong class="text-primary">{{ activeEvent.location ?? 'Not specified' }}</strong> <!-- Uses Quasar's primary color -->
                </div>

                <q-separator class="q-my-md" />

                <div v-if="activeEvent.extendedProps?.duties?.length">
                    <div class="text-h6 q-mb-md">Assigned Duties</div>
                    <div v-for="d in activeEvent.extendedProps.duties" :key="d.id" class="q-mb-lg">
                        <div class="row items-center q-mb-sm">
                            <div class="text-subtitle1 text-weight-medium">{{ d.name }}</div>
                            <q-chip dense outline color="grey-6" text-color="grey-9" class="q-ml-sm">
                                Required: {{ d.min_required }}
                            </q-chip>
                        </div>

                        <q-list bordered class="rounded-borders">
                            <q-item v-for="a in d.assignments" :key="a.id">
                                <q-item-section avatar>
                                    <q-avatar color="red-1" text-color="red-8" icon="person" size="md" /> <!-- Changed avatar background to red tone -->
                                </q-item-section>
                                <q-item-section>
                                    <q-item-label class="text-body2 text-weight-medium">{{ a.employee_name ?? 'Unassigned' }}</q-item-label>
                                    <q-item-label caption v-if="a.designation">
                                        {{ a.designation }}
                                    </q-item-label>
                                </q-item-section>

                                <q-item-section side>
                                    <q-chip
                                        :label="a.status"
                                        dense
                                        :color="a.status === 'Confirmed' ? 'green-5' : 'orange-5'"
                                        text-color="white"
                                        class="text-weight-medium"
                                    />
                                </q-item-section>
                            </q-item>

                            <q-item v-if="!d.assignments || d.assignments.length === 0">
                                <q-item-section>
                                    <q-item-label caption class="text-italic">No one assigned for this duty.</q-item-label>
                                </q-item-section>
                            </q-item>
                        </q-list>
                    </div>
                </div>

                <div v-else class="text-grey-6 text-center q-py-lg">
                    <q-icon name="info" size="md" class="q-mb-sm" />
                    <div>No duties defined for this event.</div>
                </div>
            </div>
            <div v-else class="q-pa-md text-center text-grey-6 q-py-xl">
                <q-icon name="event_note" size="xl" class="q-mb-md" />
                <div class="text-h6">Select an event to see details</div>
            </div>
        </q-drawer>
    </q-page>
</template>

<script setup>
import { ref, computed } from 'vue'
import VueCal from 'vue-cal'
import 'vue-cal/dist/vuecal.css'
import BackendLayout from '../../../Layouts/BackendLayout.vue'

defineOptions({ layout: BackendLayout })

const props = defineProps({
    events: {
        type: Array,
        default: () => []
    }
})

const drawer = ref(false)
const activeEvent = ref(null)
const vuecalRef = ref(null)
const selectedDate = ref(null) // Initialize as null

// Base events from backend
const baseEvents = ref(
    props.events.map(e => ({
        id: e.id,
        title: e.title,
        start: new Date(e.start),
        end: new Date(e.end),
        content: e.location,
        class: 'event-modern', // Use the modern event class
        extendedProps: {
            duties: e.extendedProps?.duties ?? []
        }
    }))
)

// Computed: add a highlight event for the selected day
const displayedEvents = computed(() => {
    if (!selectedDate.value) return baseEvents.value

    const selected = new Date(selectedDate.value)
    // Create a highlight event that spans the entire selected day
    const highlightEvent = {
        id: 'highlight',
        start: selected, // Use selected date for start
        end: selected, // Use selected date for end
        allDay: true, // Make it an all-day event for proper highlighting
        title: '',
        content: '',
        class: 'highlight-day'
    }

    // Filter out potential duplicate highlight if already in baseEvents
    const filteredBaseEvents = baseEvents.value.filter(event => event.id !== 'highlight');

    return [...filteredBaseEvents, highlightEvent]
})


// When a date is clicked in QDate
function onDateChange(dateStr) {
    if (vuecalRef.value && dateStr) {
        const date = new Date(dateStr)
        // Switch to day view focused on the selected date for more precision
        vuecalRef.value.switchView('week', date)
    }
}

function onEventClick(payload) {
    // VueCal sometimes passes the event directly, sometimes wrapped in an object
    const evt = payload?.event ?? payload
    if (evt.id === 'highlight') return // Ignore clicks on highlight overlay
    activeEvent.value = evt
    drawer.value = true
}

function formatFullDateTime(date) {
    if (!date) return ''
    const d = date instanceof Date ? date : new Date(date)
    return d.toLocaleString([], {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true // Use 12-hour format with AM/PM
    })
}
</script>

<style scoped>
/* These scoped styles can override or add to the global ones */
.q-date {
    box-shadow: none !important; /* Remove shadow from QDate */
}

.rounded-borders-lg {
    border-radius: 12px; /* Consistent with VueCal and cards */
}

.rounded-borders-md {
    border-radius: 8px;
}

.shadow-sm {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08); /* Lighter shadow */
}

/* Specific adjustments for vuecal to ensure it fits the modern theme */
.vuecal-modern {
    border: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}

/* Adjust QToolbar title for drawer */
.q-toolbar-title {
    font-size: 1.25rem; /* Equivalent to text-h6 */
}

/* Custom styling for status chips */
.q-chip[label="Confirmed"] {
    background-color: #4caf50 !important; /* Green for confirmed */
    color: white !important;
}

.q-chip[label="Pending"] {
    background-color: #ff9800 !important; /* Orange for pending */
    color: white !important;
}

.q-chip[label="Cancelled"] {
    background-color: #f44336 !important; /* Red for cancelled */
    color: white !important;
}

</style>
