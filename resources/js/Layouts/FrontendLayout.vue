<template>
    <q-layout @scroll="handleScroll" style="background: #ffffff" view="hHh lpR fff">

        <!-- Header -->
        <q-header height-hint="90" :class="classObject" elevated class="bg-primary">
            <q-toolbar class="q-my-md">

                <!-- Left Side: Logo & Menu -->
                <div class="row items-center">
                    <q-btn flat round dense icon="menu" class="q-mr-sm lt-md" />
                    <q-separator dark vertical inset class="lt-md" />

                    <q-img src="/assets/Dipr_logo.png" width="60px" class="q-mx-sm" />
                    <span class="text-h6 text-weight-bold">DIPR Portal</span>
                </div>

                <!-- Push nav items to right -->
                <q-space />

                <!-- Desktop Navigation (Right Side) -->
                <div class="row items-center gt-sm">
                    <q-btn stretch flat label="Home"
                           @click="$inertia.get(route('home'))"
                           :class="{ 'bg-primary text-white': route().current() === 'home' }"
                    />
                    <q-separator dark vertical />
                    <q-btn stretch flat label="Events" to="/events" />

                    <template v-if="!$page.props.auth?.user">
                        <q-separator dark vertical />
                        <q-btn
                            stretch
                            flat
                            label="Login"
                            @click="$inertia.get(route('login'))"
                            :class="{ 'bg-primary text-white': route().current() === 'login' }"
                        />
                    </template>

                    <template v-else>
                        <q-separator dark vertical />
                        <q-btn
                            stretch
                            flat
                            label="Logout"
                            @click.prevent="$inertia.delete(route('login.destroy'))"
                        />
                    </template>
                </div>

                <!-- Mobile Menu (Right-aligned) -->
                <div class="lt-md">
                    <q-btn flat round dense icon="menu">
                        <q-menu anchor="bottom right" self="top right">
                            <q-list style="min-width: 180px">
                                <q-item clickable v-close-popup to="/dashboard">
                                    <q-item-section>Home</q-item-section>
                                </q-item>

                                <q-item clickable v-close-popup to="/events">
                                    <q-item-section>Events</q-item-section>
                                </q-item>

                                <q-item
                                    v-if="!$page.props.auth?.user"
                                    clickable
                                    v-close-popup
                                    @click="$inertia.get(route('login'))"
                                >
                                    <q-item-section>Login</q-item-section>
                                </q-item>

                                <q-item
                                    v-if="$page.props.auth?.user"
                                    clickable
                                    v-close-popup
                                    @click.prevent="$inertia.delete(route('login.destroy'))"
                                >
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
                    <div class="text-h6 text-weight-bold q-mb-sm">DIPR Detailment</div>
                    <div class="text-grey-5">
                        Streamlining event detailment for government departments and public relations.
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
                © {{ new Date().getFullYear() }} DIPR Event Detailment System. All rights reserved.
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
.no-padding {
    padding: 0 !important;
}
</style>
