<template>
    <q-page class="container">


        <!-- Hero Section -->
        <section class="bg-gradient text-white text-center q-py-xl q-px-md">
            <div class="q-mx-auto" style="max-width: 800px;">
                <div class="text-h4 text-weight-bold q-mb-md">
                    Streamline Your Event Detailment
                </div>
                <div class="text-subtitle1 q-mb-xl">
                    Efficiently assign roles and manage personnel for government events.
                </div>
                <div class="row justify-center q-gutter-sm">
                    <q-btn color="white" text-color="primary" label="Create New Event" to="/create-event" glossy />
                    <q-btn color="secondary"  text-color="dark" label="View Upcoming Events" to="/events" glossy />
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="bg-white q-py-xl q-px-lg">
            <div class="text-center q-mb-xl">
                <div class="text-h5 text-weight-bold text-primary">Key Features</div>
            </div>
            <div class="row q-col-gutter-lg justify-center">
                <q-card
                    v-for="(feature, i) in features"
                    :key="i"
                    class="q-pa-lg col-12 col-md-4 hover-scale"
                >
                    <div class="q-mb-md flex flex-center bg-indigo-1" style="width: 64px; height: 64px; border-radius: 50%;">
                        <q-icon :name="feature.icon" size="32px" color="primary" />
                    </div>
                    <div class="text-subtitle1 text-weight-medium q-mb-sm">{{ feature.title }}</div>
                    <div class="text-grey-7">{{ feature.desc }}</div>
                </q-card>
            </div>
        </section>

        <!-- Event Roles Section -->
        <section class="bg-grey-2 q-py-xl q-px-lg">
            <div class="text-center q-mb-xl">
                <div class="text-h5 text-weight-bold text-primary">Event Roles</div>
            </div>
            <div class="row q-col-gutter-lg justify-center">
                <q-card
                    v-for="(role, i) in roles"
                    :key="i"
                    class="q-pa-md col-12 col-sm-6 col-md-3 cursor-pointer transition hover-scale"
                >
                    <div class="q-mb-md flex flex-center bg-indigo-1" style="width: 48px; height: 48px; border-radius: 50%;">
                        <q-icon :name="role.icon" size="28px" color="primary" />
                    </div>
                    <div class="text-subtitle1 text-weight-medium q-mb-xs">{{ role.title }}</div>
                    <div class="text-grey-7 text-body2 q-mb-sm">{{ role.desc }}</div>
                    <q-badge color="indigo-2" text-color="primary" align="middle">Min: {{ role.min }}</q-badge>
                </q-card>
            </div>
        </section>

        <!-- Upcoming Events -->
        <section class="bg-white q-py-xl q-px-lg">
            <div class="row justify-between items-center q-mb-lg">
                <div class="text-h5 text-weight-bold text-primary">Upcoming Events</div>
                <q-btn flat color="primary" label="View All" to="/events" />
            </div>

            <div class="row q-col-gutter-lg">
                <q-card
                    v-for="(event, i) in upcomingEvents"
                    :key="i"
                    class="col-12 col-md-6"
                >
                    <div class="row no-wrap">
                        <div class="bg-primary flex flex-center" style="width: 120px;">
                            <q-icon :name="event.icon" size="48px" color="white" />
                        </div>
                        <div class="q-pa-md">
                            <div class="text-caption text-primary text-weight-medium">{{ event.date }}</div>
                            <div class="text-subtitle1 text-weight-bold">{{ event.title }}</div>
                            <div class="text-grey-7">{{ event.location }}</div>
                            <div class="q-mt-sm">
                                <q-badge color="indigo-2" text-color="primary">{{ event.status }}</q-badge>
                            </div>
                            <q-btn
                                flat
                                color="primary"
                                label="View Details"
                                icon-right="chevron_right"
                                to="/events"
                                class="q-mt-sm"
                            />
                        </div>
                    </div>
                </q-card>
            </div>
        </section>



    </q-page>
</template>

<script setup>
import FrontendLayout from "../Layouts/FrontendLayout.vue";

defineOptions({layout:FrontendLayout})
// Static content
const features = [
    { icon: 'groups', title: 'Role-Based Assignment', desc: 'Automatically assign staff based on designations and expertise for optimal coverage.' },
    { icon: 'event_note', title: 'Event Management', desc: 'Create, schedule, and manage all event details in one centralized platform.' },
    { icon: 'bar_chart', title: 'Reporting & Analytics', desc: 'Generate comprehensive reports on staff assignments and event coverage.' }
]

const roles = [
    { icon: 'work', title: 'Office Representative', desc: 'Attending offices and representing departments', min: 2 },
    { icon: 'edit', title: 'Press Officer', desc: 'Media liaison and press release management', min: 2 },
    { icon: 'videocam', title: 'Videographer', desc: 'Event video coverage and documentation', min: 2 },
    { icon: 'photo_camera', title: 'Photographer', desc: 'Event photo coverage and documentation', min: 2 }
]

const upcomingEvents = [
    { icon: 'flag', date: 'August 15, 2025', title: 'Independence Day Celebration', location: 'Central Ground | 09:00 AM - 12:00 PM', status: 'Upcoming' },
    { icon: 'mic', date: 'July 1, 2025', title: 'Press Conference', location: 'Media Hall | 11:00 AM - 1:00 PM', status: 'Upcoming' },
    { icon: 'flag', date: 'August 15, 2025', title: 'Independence Day Celebration', location: 'Central Ground | 09:00 AM - 12:00 PM', status: 'Upcoming' },
    { icon: 'mic', date: 'July 1, 2025', title: 'Press Conference', location: 'Media Hall | 11:00 AM - 1:00 PM', status: 'Upcoming' },
]

</script>

<style scoped>
.bg-gradient {
    background: linear-gradient(135deg, var(--q-primary) 0%, var(--q-secondary) 100%);
}

.hover-scale {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.hover-scale:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
}
</style>
