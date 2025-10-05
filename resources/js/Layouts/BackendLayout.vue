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
                <div class="row items-center gt-sm">
                    <q-btn flat label="Dashboard" to="/dashboard" class="text-primary" />
                    <q-btn flat label="Events" to="/events" class="text-primary" />

                    <!-- Assignments Dropdown -->
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
                            <q-item clickable v-ripple to="/assignments/create">
                                <q-item-section>Create Assignment</q-item-section>
                            </q-item>
                        </q-list>
                    </q-btn-dropdown>

                    <q-btn flat label="Staff" to="/employees" class="text-primary" />
                    <q-btn flat label="Reports" to="/reports" class="text-primary" />
                    <q-btn  flat label="Logout" class="text-primary" @click.prevent="$inertia.delete(route('login.destroy'))" />
                </div>


                <!-- Mobile Menu Button -->
                <div class="lt-md">
                    <q-btn dense flat round icon="menu">
                        <q-menu anchor="bottom right" self="top right">
                            <q-list style="min-width: 180px">
                                <q-item clickable v-close-popup to="/dashboard">
                                    <q-item-section>Dashboard</q-item-section>
                                </q-item>
                                <q-item clickable v-close-popup to="/events">
                                    <q-item-section>Events</q-item-section>
                                </q-item>

                                <!-- Assignment submenu inside mobile -->
                                <q-expansion-item icon="assignment" label="Assignments" expand-separator>
                                    <q-item clickable v-close-popup to="/assignments">
                                        <q-item-section>All Assignments</q-item-section>
                                    </q-item>
                                    <q-item clickable v-close-popup to="/assignments/pending">
                                        <q-item-section>Pending</q-item-section>
                                    </q-item>
                                    <q-item clickable v-close-popup to="/assignments/completed">
                                        <q-item-section>Completed</q-item-section>
                                    </q-item>
                                    <q-item clickable v-close-popup to="/assignments/create">
                                        <q-item-section>Create</q-item-section>
                                    </q-item>
                                </q-expansion-item>

                                <q-item clickable v-close-popup to="/employees">
                                    <q-item-section>Staff</q-item-section>
                                </q-item>
                                <q-item clickable v-close-popup to="/reports">
                                    <q-item-section>Reports</q-item-section>
                                </q-item>
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
        <q-footer class="bg-negative text-dark q-pt-xl q-pb-md">
            <div class="text-center text-dark q-mt-sm">
                © {{ new Date().getFullYear() }} DIPR Event Staffing System. All rights reserved.
            </div>
        </q-footer>

    </q-layout>
</template>

<script setup>
import { computed, reactive } from "vue";

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

const menuItems = [
    { label: "Dashboard", to: "/dashboard" },
    { label: "Events", to: "/events" },
    { label: "Assignments", to: "/assignments" },
    { label: "Staff", to: "/employees" },
    { label: "Reports", to: "/reports" },
];

const quickLinks = [
    { label: "Dashboard", to: "/dashboard" },
    { label: "Events", to: "/events" },
    { label: "Staff Directory", to: "/employees" },
    { label: "Reports", to: "/reports" },
];

const resources = [
    { label: "Documentation", to: "#" },
    { label: "Training", to: "#" },
    { label: "Support", to: "#" },
];
</script>

<style scoped>
.shadow-bottom-5 {
    box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
}
</style>
