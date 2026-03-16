<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import { edit as editProfile } from '@/routes/profile';
import { type BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const primaryNav = [
    {
        title: 'Dashboard',
        href: dashboard().url,
        icon: 'mdi-view-dashboard-outline',
    },
];

const footerNav = [
    {
        title: 'Profile',
        href: editProfile().url,
        icon: 'mdi-account-circle-outline',
    },
];
</script>

<template>
    <v-app theme="goviralDark">
        <v-layout class="goviral-shell">
            <v-navigation-drawer
                permanent
                color="#121212"
                class="goviral-drawer"
            >
                <v-sheet
                    class="d-flex align-center px-4 py-4 goviral-drawer-header"
                    color="transparent"
                >
                    <router-link
                        :href="breadcrumbs[0]?.href ?? '/'"
                        class="text-decoration-none"
                    >
                        <span class="goviral-logo">
                            Go<span class="goviral-logo-accent">Viral</span>
                        </span>
                    </router-link>
                </v-sheet>

                <v-divider class="mx-4 mb-2" />

                <v-list nav density="comfortable">
                    <v-list-item
                        v-for="item in primaryNav"
                        :key="item.title"
                        color="primary"
                        class="goviral-nav-item"
                    >
                        <Link
                            :href="item.href"
                            class="goviral-nav-link"
                            data-test="sidebar-dashboard-link"
                        >
                            <v-icon
                                v-if="item.icon"
                                :icon="item.icon"
                                size="20"
                                class="mr-3"
                            />
                            {{ item.title }}
                        </Link>
                    </v-list-item>
                </v-list>

                <v-spacer />

                <v-divider class="mx-4 mb-2" />

                <v-list nav density="comfortable">
                    <v-list-item
                        v-for="item in footerNav"
                        :key="item.title"
                        color="primary"
                        class="goviral-nav-item goviral-nav-footer-item"
                    >
                        <Link
                            :href="item.href"
                            class="goviral-nav-link"
                            data-test="sidebar-profile-link"
                        >
                            <v-icon
                                v-if="item.icon"
                                :icon="item.icon"
                                size="20"
                                class="mr-3"
                            />
                            {{ item.title }}
                        </Link>
                    </v-list-item>
                </v-list>
            </v-navigation-drawer>

            <v-main class="goviral-main">
                <v-container fluid class="py-6 px-4 px-md-8">
                    <slot />
                </v-container>
            </v-main>
        </v-layout>
    </v-app>
</template>

<style scoped>
.goviral-shell {
    min-height: 100vh;
    background: #121212;
}

.goviral-drawer {
    border-right: 1px solid rgba(254, 44, 85, 0.24);
}

.goviral-drawer-header {
    min-height: 64px;
}

.goviral-logo {
    font-weight: 700;
    font-size: 1.2rem;
    letter-spacing: 0.04em;
    color: rgba(255, 255, 255, 0.94);
}

.goviral-logo-accent {
    color: #fe2c55;
}

.goviral-main {
    background:
        radial-gradient(circle at top left, rgba(254, 44, 85, 0.16), transparent 55%),
        radial-gradient(circle at bottom right, rgba(37, 244, 238, 0.12), transparent 55%),
        #121212;
}

.goviral-nav-item :deep(.v-list-item-title) {
    font-weight: 500;
}

.goviral-nav-link {
    display: inline-flex;
    width: 100%;
    color: rgba(255, 255, 255, 0.78);
    text-decoration: none;
}

.goviral-nav-link:hover {
    color: rgb(var(--v-theme-primary));
}
</style>
