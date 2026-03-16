<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <v-container class="dashboard-content py-6">
            <v-row class="mb-4">
                <v-col cols="12" md="4">
                    <v-card
                        class="dashboard-card"
                        elevation="0"
                        rounded="xl"
                    >
                        <v-card-text class="text-body-1 text-medium-emphasis">
                            Welcome to your dashboard.
                        </v-card-text>
                    </v-card>
                </v-col>
                <v-col cols="12" md="4">
                    <v-card
                        class="dashboard-card"
                        elevation="0"
                        rounded="xl"
                    >
                        <v-card-text class="text-body-1 text-medium-emphasis">
                            Content area
                        </v-card-text>
                    </v-card>
                </v-col>
                <v-col cols="12" md="4">
                    <v-card
                        class="dashboard-card"
                        elevation="0"
                        rounded="xl"
                    >
                        <v-card-text class="text-body-1 text-medium-emphasis">
                            Content area
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>
            <v-row>
                <v-col cols="12">
                    <v-card
                        class="dashboard-card dashboard-card-main"
                        elevation="0"
                        rounded="xl"
                    >
                        <v-card-text class="text-body-1 text-medium-emphasis">
                            Main content
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>
    </AppLayout>
</template>

<style scoped>
.dashboard-content {
    max-width: 100%;
}

.dashboard-card {
    border: 1px solid rgba(254, 44, 85, 0.25);
    background: rgba(18, 18, 18, 0.6);
}

.dashboard-card-main {
    min-height: 200px;
}
</style>
