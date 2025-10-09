<template>
    <q-page class="container">
        <div class="row items-center q-mb-md">
            <div class="text-h5">Events</div>
        </div>

        <vue-cal
            class="calendar"
            :events="events"
            default-view="month"
            style="height:72vh;"
            @event-click="onEventClick"
        />



        <q-drawer v-model="drawer" side="right" width="420" behavior="desktop">
            <q-toolbar class="bg-grey-1">
                <q-btn flat round dense icon="close" @click="drawer = false" />
                <div class="q-toolbar-title">Event details</div>
            </q-toolbar>

            <div v-if="activeEvent" class="q-pa-md">
                <div class="text-h6 q-mb-sm">{{ activeEvent.title }}</div>
                <div class="text-caption q-mb-md">
                    {{ formatFullDateTime(activeEvent.start) }} — {{ formatFullDateTime(activeEvent.end) }}
                </div>
                <div class="text-subtitle2 q-mb-md">
                    Location: **{{ activeEvent.location ?? '—' }}**
                </div>

                <div v-if="activeEvent.extendedProps?.duties?.length">
                    <div
                        v-for="d in activeEvent.extendedProps.duties"
                        :key="d.id"
                        class="q-mb-md"
                    >
                        <div class="row items-center q-mb-sm">
                            <div class="text-subtitle1">{{ d.name }}</div>
                            <div class="text-caption q-ml-sm">({{ d.min_required }})</div>
                        </div>

                        <q-list bordered>
                            <q-item v-for="a in d.assignments" :key="a.id">
                                <q-item-section>
                                    <q-item-label>{{ a.employee_name ?? '—' }}</q-item-label>
                                    <q-item-label caption v-if="a.designation">
                                        {{ a.designation }}
                                    </q-item-label>
                                </q-item-section>

                                <q-item-section side top>
                                    <q-chip :label="a.status" dense />
                                </q-item-section>
                            </q-item>

                            <q-item
                                v-if="!d.assignments || d.assignments.length === 0"
                            >
                                <q-item-section>
                                    <q-item-label caption>No one assigned</q-item-label>
                                </q-item-section>
                            </q-item>
                        </q-list>
                    </div>
                </div>

                <div v-else>
                    <div>No duties defined for this event.</div>
                </div>
            </div>
        </q-drawer>
    </q-page>
</template>

<script setup>
import { ref } from 'vue'
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

// Transforming props data for vue-cal
const events = ref(
    props.events.map(e => ({
        id: e.id,
        title: e.title,
        start: new Date(e.start),
        end: new Date(e.end),
        content: e.location,
        class: 'red-event',
        extendedProps: {
            duties: e.extendedProps?.duties ?? []
        }
    }))
)

function onEventClick(payload) {
    const evt = payload?.event ?? payload
    activeEvent.value = evt
    drawer.value = true
}

function formatTime(date) {
    if (!date) return ''
    const d = date instanceof Date ? date : new Date(date)
    return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

function formatFullDateTime(date) {
    if (!date) return ''
    const d = date instanceof Date ? date : new Date(date)
    return d.toLocaleString([], {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>

<style scoped>
.calendar {
    --vuecal-primary: #bf6262;
}

/* 🎯 FIX 1: Enforce the event color with high specificity (targeting .calendar wrapper) */
.calendar .vuecal__event {

    /* Force the background color to red */
    background-color: #bf6262 !important;
    border-color: #a34e4e !important;
    color: white !important; /* Forces all text inside the event container to white */
}

</style>
