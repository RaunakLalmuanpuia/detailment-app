<template>
    <q-layout @scroll="handleScroll" style="background: #ffffff" view="hHh lpR fff">

        <!-- Header -->
        <q-header height-hint="90" :class="classObject" elevated class="bg-primary">
            <q-toolbar class="q-px-lg q-py-md justify-between">

                <!-- Logo / Title -->
                <div class="row items-center">
                    <q-img name="event" size="32px" width="60px" class="q-mr-sm" src="/assets/Dipr_logo.png" />
                    <span class="text-h6">DIPR</span>
                </div>

                <!-- Desktop Nav -->
                <div class="row items-center gt-sm q-gutter-md">

                    <!-- Admin Navigation -->
                    <template v-if="isAdmin">
                        <div class="row items-center q-gutter-md">
                            <q-item clickable v-ripple to="/dashboard" class="text-primary">
                                <q-item-section>Dashboard</q-item-section>
                            </q-item>

                            <q-item clickable v-ripple @click="$inertia.get(route('events.index'))"
                                    :active="route().current()==='events.index'" active
                                    active-class="active-menu text-primary"
                                    class="text-primary">
                                <q-item-section>Events</q-item-section>
                            </q-item>

                            <q-item clickable v-ripple @click="$inertia.get(route('events.index'))" class="text-primary">
                                <q-item-section>Detailment</q-item-section>
                            </q-item>

                            <q-item clickable v-ripple class="text-primary relative-position">
                                <q-item-section>Admin</q-item-section>
                                <q-item-section side>
                                    <q-icon name="expand_more" size="18px" color="primary"/>
                                </q-item-section>

                                <q-menu anchor="bottom middle" self="top middle">
                                    <q-list style="min-width: 150px">
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
                                </q-menu>
                            </q-item>

                            <q-item clickable v-ripple to="/reports" class="text-primary">
                                <q-item-section>Reports</q-item-section>
                            </q-item>

                            <q-item clickable v-ripple @click.prevent="$inertia.delete(route('login.destroy'))" class="text-primary">
                                <q-item-section>Logout</q-item-section>
                                <q-item-section avatar>
                                    <q-icon name="logout" color="primary" />
                                </q-item-section>
                            </q-item>
                        </div>
                    </template>



                    <!-- Organizer Navigation -->
                    <template v-else-if="isOrganizer">
                        <q-btn flat label="Dashboard" to="/dashboard" class="text-primary"/>
                        <q-btn flat label="My Events" to="/events/mine" class="text-primary"/>
                        <q-btn-dropdown flat label="Assignments" class="text-primary">
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
                        <q-btn flat label="Logout" class="text-primary"
                               @click.prevent="$inertia.delete(route('login.destroy'))"/>
                    </template>

                    <!-- Employee Navigation -->
                    <template v-else-if="isEmployee">
                        <q-btn flat label="Dashboard" to="/dashboard" class="text-primary"/>
                        <q-btn flat label="Assignments" to="/assignments" class="text-primary"/>
                        <q-btn flat label="My Profile" to="/profile" class="text-primary"/>
                        <q-btn flat label="Logout" class="text-primary"
                               @click.prevent="$inertia.delete(route('login.destroy'))"/>
                    </template>

                    <!-- Viewer / Default Navigation -->
                    <template v-else>
                        <q-btn flat label="Dashboard" to="/dashboard" class="text-primary"/>
                        <q-btn flat label="Events" to="/events" class="text-primary"/>
                        <q-btn flat label="Logout" class="text-primary"
                               @click.prevent="$inertia.delete(route('login.destroy'))"/>
                    </template>

                </div>

                <!-- Mobile Menu Button -->
                <div class="lt-md">
                    <q-btn dense flat round icon="menu">
                        <q-menu anchor="bottom right" self="top right">
                            <q-list style="min-width: 180px">
                                <!-- Use same role logic for mobile -->
                                <template v-if="isAdmin">
                                    <q-item clickable v-close-popup to="/dashboard"><q-item-section>Dashboard</q-item-section></q-item>
                                    <q-item clickable v-close-popup to="/users"><q-item-section>Users</q-item-section></q-item>
                                    <q-item clickable v-close-popup to="/events"><q-item-section>Events</q-item-section></q-item>
                                    <q-item clickable v-close-popup to="/reports"><q-item-section>Reports</q-item-section></q-item>
                                </template>

                                <template v-else-if="isOrganizer">
                                    <q-item clickable v-close-popup to="/dashboard"><q-item-section>Dashboard</q-item-section></q-item>
                                    <q-item clickable v-close-popup to="/events/mine"><q-item-section>My Events</q-item-section></q-item>
                                    <q-expansion-item icon="assignment" label="Assignments" expand-separator>
                                        <q-item clickable v-close-popup to="/assignments"><q-item-section>All Assignments</q-item-section></q-item>
                                        <q-item clickable v-close-popup to="/assignments/pending"><q-item-section>Pending</q-item-section></q-item>
                                        <q-item clickable v-close-popup to="/assignments/completed"><q-item-section>Completed</q-item-section></q-item>
                                    </q-expansion-item>
                                </template>

                                <template v-else-if="isEmployee">
                                    <q-item clickable v-close-popup to="/dashboard"><q-item-section>Dashboard</q-item-section></q-item>
                                    <q-item clickable v-close-popup to="/assignments"><q-item-section>Assignments</q-item-section></q-item>
                                    <q-item clickable v-close-popup to="/profile"><q-item-section>My Profile</q-item-section></q-item>
                                </template>

                                <template v-else>
                                    <q-item clickable v-close-popup to="/dashboard"><q-item-section>Dashboard</q-item-section></q-item>
                                    <q-item clickable v-close-popup to="/events"><q-item-section>Events</q-item-section></q-item>
                                </template>

                                <!-- Logout button for all -->
                                <q-item clickable v-close-popup @click.prevent="$inertia.delete(route('login.destroy'))">
                                    <q-item-section>Logout</q-item-section>
                                </q-item>
                            </q-list>
                        </q-menu>
                    </q-btn>
                </div>

            </q-toolbar>
        </q-header>

        <!-- Page Content -->
        <q-page-container>
            <slot />
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
import { computed, reactive } from "vue";
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
    const { position } = detail;
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
