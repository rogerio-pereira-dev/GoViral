<script setup lang="ts">
import { type BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
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
                        v-for="item in breadcrumbs"
                        :key="item.title"
                        :to="item.href"
                        :title="item.title"
                        color="primary"
                        class="goviral-nav-item"
                    />
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
</style>
