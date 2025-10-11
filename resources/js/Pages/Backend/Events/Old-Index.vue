<template>
    <q-page class="container q-py-lg">
        <div class="row items-center q-mb-lg">
            <div class="text-h4 text-weight-bold text-grey-9">Events Overview</div>
        </div>

        <div class="row q-col-gutter-md">
            <!-- Left: Date Picker -->


            <!-- Right: Calendar -->
            <div class="col-xs-12">
                <!--                <vue-cal-->
                <!--                    editable-events-->
                <!--                    ref="vuecalRef"-->
                <!--                    :events="displayedEvents"-->
                <!--                    default-view="month"-->
                <!--                    style="height:72vh;"-->
                <!--                    @event-click="onEventClick"-->
                <!--                    :disable-views="['year']"-->
                <!--                    active-view="month"-->
                <!--                    events-on-month-view-->
                <!--                    :views="['day', 'week', 'month','year']"-->
                <!--                    :time-from="8 * 60"-->
                <!--                    :time-to="20 * 60"-->
                <!--                    :time-step="60"-->
                <!--                />-->
                <vue-cal
                    date-picker
                    :views-bar="false"
                    v-model:selected-date="selectedDate"
                    @update:selected-date="viewDate = $event"
                    :view-date="viewDate">
                </vue-cal>

                <vue-cal
                    v-model:view-date="exSyncTwoCalendars.viewDate"
                    v-model:selected-date="exSyncTwoCalendars.selectedDate"
                    @update:view-date="exSyncTwoCalendars.viewDate = $event"
                    view="week"
                    :views="['day', 'week']"
                    :views-bar="false"
                    sm>
                </vue-cal>
            </div>
        </div>

        <!-- Event Details Drawer (No changes needed here) -->
        <q-dialog v-model="drawer">
            <q-card class="rounded-borders-lg">
                <!-- Toolbar -->
                <q-toolbar class="bg-primary text-white">
                    <q-btn flat round dense icon="close" @click="drawer = false" />
                    <q-toolbar-title class="text-weight-medium">Event Details</q-toolbar-title>
                </q-toolbar>

                <!-- Event Content -->
                <q-card-section>
                    <div v-if="activeEvent">
                        <div class="text-h5 text-weight-bold q-mb-sm">{{ activeEvent.title }}</div>
                        <div class="text-caption text-grey-7 q-mb-md">
                            <q-icon name="event" class="q-mr-xs" size="xs" />
                            {{ formatFullDateTime(activeEvent.start) }} — {{ formatFullDateTime(activeEvent.end) }}
                        </div>
                        <div class="text-subtitle1 text-grey-8 q-mb-md">
                            <q-icon name="place" class="q-mr-xs" size="xs" />
                            Location: <strong class="text-primary">{{ activeEvent.location ?? 'Not specified' }}</strong>
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
                                            <q-avatar color="red-1" text-color="red-8" icon="person" size="md" />
                                        </q-item-section>
                                        <q-item-section>
                                            <q-item-label class="text-body2 text-weight-medium">{{ a.employee_name ?? 'Unassigned' }}</q-item-label>
                                            <q-item-label caption v-if="a.designation">{{ a.designation }}</q-item-label>
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

                    <div v-else class="text-center text-grey-6 q-py-lg">
                        <q-icon name="event_note" size="xl" class="q-mb-md" />
                        <div class="text-h6">Select an event to see details</div>
                    </div>
                </q-card-section>
            </q-card>
        </q-dialog>

    </q-page>
</template>

<script setup>
import { ref, computed,reactive } from 'vue'
import { VueCal, addDatePrototypes } from 'vue-cal'
import 'vue-cal/style'

addDatePrototypes()
import BackendLayout from '../../../Layouts/BackendLayout.vue'

defineOptions({ layout: BackendLayout })

const props = defineProps({
    events: {
        type: Array,
        default: () => []
    }
})

const exSyncTwoCalendars = reactive({
    viewDate: new Date(),
    selectedDate: new Date(),
})

const drawer = ref(false)
const activeEvent = ref(null)
const vuecalRef = ref(null)
const selectedDate = ref(null)

// Base events from backend
const baseEvents = ref(
    props.events.map(e => ({
        id: e.id,
        title: e.title,
        start: new Date(e.start),
        end: new Date(e.end),
        content: e.location,
        class: 'event-modern',
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
        id: 'highlight-selected-date', // Give it a unique ID to avoid conflicts
        start: selected,
        end: selected,
        allDay: true,
        title: '',
        content: '',
        class: 'highlight-day'
    }

    // Filter out previous highlight if it exists and add the new one
    const filteredBaseEvents = baseEvents.value.filter(event => event.id !== 'highlight-selected-date');

    return [...filteredBaseEvents, highlightEvent]
})


// When a date is clicked in QDate

function onEventClick(payload) {
    const evt = payload?.event ?? payload
    if (evt.id === 'highlight-selected-date') return // Ignore clicks on highlight overlay
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
        hour12: true
    })
}
</script>

<style>
/* Other custom styles from your original template */
</style>
