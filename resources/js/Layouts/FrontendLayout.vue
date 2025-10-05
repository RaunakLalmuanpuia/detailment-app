<template>
    <q-layout @scroll="handleScroll" style="background: #ffffff" view="hHh lpR fff" >

        <!-- Header -->
        <q-header height-hint="90" :class="classObject" elevated class="bg-primary">
            <q-toolbar class="q-px-lg q-py-md justify-between">

                <!-- Logo / Title -->
                <div class="row items-center">
                    <q-img name="event" size="32px" width="60px" class="q-mr-sm" src="/assets/Dipr_logo.png" />
                    <span class="text-h6"></span>
                </div>

                <!-- Desktop Nav -->
                <div class="row items-center gt-sm">
                    <q-btn flat label="Home" to="/dashboard" class="text-primary" />
                    <q-btn flat label="Events" to="/events" class="text-primary" />
                    <q-btn v-if="!$page.props.auth?.user" flat label="Login" @click="$inertia.get(route('login'))" class="text-primary" />
                    <q-btn v-if="$page.props.auth?.user" flat label="Logout" to="/reports" class="text-primary" />
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

                                <q-item v-if="!$page.props.auth?.user" clickable v-close-popup @click="$inertia.get(route('login'))">
                                    <q-item-section>Login</q-item-section>
                                </q-item>
                                <q-item v-if="$page.props.auth?.user" clickable v-close-popup to="/reports">
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
        <q-footer class="bg-negative text-white q-pt-xl q-pb-md">
            <div class="row q-col-gutter-xl q-px-lg">
                <div class="col-12 col-md-3">
                    <div class="text-h6 text-weight-bold q-mb-sm">DIPR Staffing</div>
                    <div class="text-grey-5">
                        Streamlining event staffing for government departments and public relations.
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="text-subtitle2 text-weight-bold q-mb-sm">Quick Links</div>
                    <q-list dense>
                        <q-item v-for="l in quickLinks" :key="l.label" clickable :to="l.to">
                            <q-item-section>{{ l.label }}</q-item-section>
                        </q-item>
                    </q-list>
                </div>

                <div class="col-6 col-md-3">
                    <div class="text-subtitle2 text-weight-bold q-mb-sm">Resources</div>
                    <q-list dense>
                        <q-item v-for="r in resources" :key="r.label" clickable :to="r.to">
                            <q-item-section>{{ r.label }}</q-item-section>
                        </q-item>
                    </q-list>
                </div>

                <div class="col-12 col-md-3">
                    <div class="text-subtitle2 text-weight-bold q-mb-sm">Contact</div>
                    <div class="text-grey-5">
                        Department of Information<br />
                        and Public Relations<br />
                        Government Complex<br />
                        <a href="mailto:contact@dipr.gov" class="text-white">contact@dipr.gov</a>
                    </div>
                </div>
            </div>

            <div class="text-center text-grey-6 q-mt-xl">
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
