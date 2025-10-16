<template>
    <q-layout @scroll="handleScroll" style="background: #ffffff" view="hHh lpR fff">

        <!-- Header -->
        <q-header height-hint="90" :class="classObject" elevated class="bg-primary">
            <q-toolbar class="q-my-md">


                <div class="row items-center q-ml-sm">
                    <q-img name="event" size="32px" width="60px" src="/assets/Dipr_logo.png"/>
                    <span class="text-h6 q-ml-sm">DIPR</span>
                </div>

                <q-space/>

                <!-- Desktop Navigation -->
                <div class="row items-center gt-sm q-gutter-sm">

                    <!-- Admin Navigation -->
                    <template v-if="isAdmin">
                        <q-btn flat stretch label="Dashboard"
                               @click="$inertia.get(route('dashboard'))"
                               :class="{ 'bg-primary text-white': route().current() === 'dashboard' }"/>
                        <q-separator dark vertical inset/>

                        <q-btn flat stretch label="Events" @click="$inertia.get(route('events.index'))"
                               :class="{ 'bg-primary text-white': route().current() === 'events.index' }"/>
                        <q-separator dark vertical inset/>

                        <q-btn flat stretch label="Detailment" @click="$inertia.get(route('events.index'))"/>
                        <q-separator dark vertical inset/>

                        <q-btn-dropdown stretch flat label="Admin">
                            <q-list>
                                <q-item clickable v-ripple to="/assignments">
                                    <q-item-section>Employees</q-item-section>
                                </q-item>
                                <q-item clickable v-ripple to="/assignments/pending">
                                    <q-item-section>Users</q-item-section>
                                </q-item>
                                <q-item clickable v-ripple to="/assignments/completed">
                                    <q-item-section>Reports</q-item-section>
                                </q-item>
                            </q-list>
                        </q-btn-dropdown>
                        <q-separator dark vertical inset/>

                        <q-btn flat stretch label="Reports" to="/reports"/>
                        <q-separator dark vertical inset/>

                        <q-btn flat stretch label="Logout" icon-right="logout"
                               @click.prevent="$inertia.delete(route('login.destroy'))"/>
                    </template>

                    <!-- Organizer Navigation -->
                    <template v-else-if="isOrganizer">
                        <q-btn flat stretch label="Dashboard" to="/dashboard"/>
                        <q-separator dark vertical inset/>

                        <q-btn flat stretch label="My Events" to="/events/mine"/>
                        <q-separator dark vertical inset/>

                        <q-btn-dropdown stretch flat label="Assignments">
                            <q-list>
                                <q-item clickable v-ripple to="/assignments">
                                    <q-item-section>All Assignments</q-item-section>
                                </q-item>
                                <q-item clickable v-ripple to="/assignments/pending">
                                    <q-item-section>Pending Assignments</q-item-section>
                                </q-item>
                                <q-item clickable v-ripple to="/assignments/completed">
                                    <q-item-section>Completed Assignments</q-item-section>
                                </q-item>
                            </q-list>
                        </q-btn-dropdown>
                        <q-separator dark vertical inset/>

                        <q-btn flat stretch label="Logout" icon-right="logout"
                               @click.prevent="$inertia.delete(route('login.destroy'))"/>
                    </template>

                    <!-- Employee Navigation -->
                    <template v-else-if="isEmployee">
                        <q-btn flat stretch label="Dashboard" to="/dashboard"/>
                        <q-separator dark vertical inset/>

                        <q-btn flat stretch label="Assignments" to="/assignments"/>
                        <q-separator dark vertical inset/>

                        <q-btn flat stretch label="My Profile" to="/profile"/>
                        <q-separator dark vertical inset/>

                        <q-btn flat stretch label="Logout" icon-right="logout"
                               @click.prevent="$inertia.delete(route('login.destroy'))"/>
                    </template>

                    <!-- Viewer / Default Navigation -->
                    <template v-else>
                        <q-btn flat stretch label="Dashboard" to="/dashboard"/>
                        <q-separator dark vertical inset/>

                        <q-btn flat stretch label="Events" to="/events"/>
                        <q-separator dark vertical inset/>

                        <q-btn flat stretch label="Logout" icon-right="logout"
                               @click.prevent="$inertia.delete(route('login.destroy'))"/>
                    </template>
                </div>

                <!-- Mobile Navigation -->
                <div class="lt-md">
                    <q-btn dense flat round icon="menu">
                        <q-menu anchor="bottom right" self="top right">
                            <q-list style="min-width: 180px">

                                <!-- Mobile - Role-based Navigation -->
                                <template v-if="isAdmin">
                                    <q-item clickable v-close-popup  @click="$inertia.get(route('dashboard'))"
                                            :class="{ 'bg-primary text-white': route().current() === 'dashboard' }">
                                        <q-item-section>Dashboard</q-item-section>
                                    </q-item>
                                    <q-item clickable v-close-popup @click="$inertia.get(route('events.index'))"
                                            :class="{ 'bg-primary text-white': route().current() === 'events.index' }">
                                        <q-item-section>Events</q-item-section>
                                    </q-item>
                                    <q-item clickable v-close-popup @click="$inertia.get(route('events.index'))">
                                        <q-item-section>Detailment</q-item-section>
                                    </q-item>
                                    <q-expansion-item icon="admin_panel_settings" label="Admin" expand-separator>
                                        <q-item clickable v-close-popup to="/assignments">
                                            <q-item-section>Employees</q-item-section>
                                        </q-item>
                                        <q-item clickable v-close-popup to="/assignments/pending">
                                            <q-item-section>Users</q-item-section>
                                        </q-item>
                                        <q-item clickable v-close-popup to="/assignments/completed">
                                            <q-item-section>Reports</q-item-section>
                                        </q-item>
                                    </q-expansion-item>
                                    <q-item clickable v-close-popup to="/reports">
                                        <q-item-section>Reports</q-item-section>
                                    </q-item>
                                    <q-item clickable v-close-popup
                                            @click.prevent="$inertia.delete(route('login.destroy'))">
                                        <q-item-section>Logout</q-item-section>
                                    </q-item>
                                </template>

                                <template v-else-if="isOrganizer">
                                    <q-item clickable v-close-popup to="/dashboard">
                                        <q-item-section>Dashboard</q-item-section>
                                    </q-item>
                                    <q-item clickable v-close-popup to="/events/mine">
                                        <q-item-section>My Events</q-item-section>
                                    </q-item>
                                    <q-expansion-item icon="assignment" label="Assignments" expand-separator>
                                        <q-item clickable v-close-popup to="/assignments">
                                            <q-item-section>All</q-item-section>
                                        </q-item>
                                        <q-item clickable v-close-popup to="/assignments/pending">
                                            <q-item-section>Pending</q-item-section>
                                        </q-item>
                                        <q-item clickable v-close-popup to="/assignments/completed">
                                            <q-item-section>Completed</q-item-section>
                                        </q-item>
                                    </q-expansion-item>
                                    <q-item clickable v-close-popup
                                            @click.prevent="$inertia.delete(route('login.destroy'))">
                                        <q-item-section>Logout</q-item-section>
                                    </q-item>
                                </template>

                                <template v-else-if="isEmployee">
                                    <q-item clickable v-close-popup to="/dashboard">
                                        <q-item-section>Dashboard</q-item-section>
                                    </q-item>
                                    <q-item clickable v-close-popup to="/assignments">
                                        <q-item-section>Assignments</q-item-section>
                                    </q-item>
                                    <q-item clickable v-close-popup to="/profile">
                                        <q-item-section>My Profile</q-item-section>
                                    </q-item>
                                    <q-item clickable v-close-popup
                                            @click.prevent="$inertia.delete(route('login.destroy'))">
                                        <q-item-section>Logout</q-item-section>
                                    </q-item>
                                </template>

                                <template v-else>
                                    <q-item clickable v-close-popup to="/dashboard">
                                        <q-item-section>Dashboard</q-item-section>
                                    </q-item>
                                    <q-item clickable v-close-popup to="/events">
                                        <q-item-section>Events</q-item-section>
                                    </q-item>
                                    <q-item clickable v-close-popup
                                            @click.prevent="$inertia.delete(route('login.destroy'))">
                                        <q-item-section>Logout</q-item-section>
                                    </q-item>
                                </template>

                            </q-list>
                        </q-menu>
                    </q-btn>
                </div>
            </q-toolbar>

        </q-header>

        <!-- Page Content -->
        <q-page-container>
            <slot/>
        </q-page-container>

        <!-- Footer -->
        <q-footer class="bg-negative text-dark q-pt-md q-pb-md">
            <div class="text-center text-dark q-mt-sm">
                © {{ new Date().getFullYear() }} DIPR Event Detailment System. All rights reserved.
            </div>
        </q-footer>

    </q-layout>
</template>

<script setup>
import {computed, reactive} from "vue";
import {usePage, router} from "@inertiajs/vue3";

const state = reactive({
    isTop: true,
});

const classObject = computed(() => ({
    "bg-transparent": state.isTop,
    "text-primary": state.isTop,
    "bg-white shadow-bottom-5 text-primary": !state.isTop,
}));

const handleScroll = (detail) => {
    const {position} = detail;
    state.isTop = position < 10;
};

const isAdmin = computed(() => !!usePage().props.roles?.find(item => item === 'Admin'));
const isOrganizer = computed(() => !!usePage().props.roles?.find(item => item === 'Organizer'));

const isEmployee = computed(() => !!usePage().props.roles?.find(item => item === 'Employee'));

const isViewer = computed(() => !!usePage().props.roles?.find(item => item === 'Viewer'));

</script>

<style scoped>
.shadow-bottom-5 {
    box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
}
</style>
