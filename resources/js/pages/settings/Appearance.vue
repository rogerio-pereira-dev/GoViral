<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/appearance';
import { type BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Appearance settings',
        href: edit().url,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Appearance settings" />

        <SettingsLayout>
            <v-card
                class="goviral-card"
                elevation="6"
            >
                <v-card-title class="text-h6">
                    Appearance settings
                </v-card-title>
                <v-card-subtitle class="mb-4">
                    Appearance is fixed to the GoViral dark theme.
                </v-card-subtitle>

                <v-card-text>
                    <p class="mt-4 text-body-2 text-medium-emphasis">
                        Theme customization is currently disabled. The dashboard always uses
                        the GoViral dark layout defined in the branding manual.
                    </p>
                </v-card-text>
            </v-card>
        </SettingsLayout>
    </AppLayout>
</template>

<style scoped>
.goviral-card {
    background: rgba(18, 18, 18, 0.96);
    border-radius: 18px;
    border: 1px solid rgba(254, 44, 85, 0.3);
}
</style>
