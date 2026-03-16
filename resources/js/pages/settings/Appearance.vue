<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { edit } from '@/routes/appearance';

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

        <v-container class="py-4">
            <v-row justify="center">
                <v-col cols="12" md="8">
                    <v-card
                        class="goviral-card"
                        elevation="6"
                    >
                        <v-card-title class="text-h6">
                            Appearance settings
                        </v-card-title>
                        <v-card-subtitle class="mb-4">
                            Update your account's appearance preferences.
                        </v-card-subtitle>

                        <v-card-text>
                            <v-row dense>
                                <v-col cols="12">
                                    <v-switch
                                        name="dark_mode"
                                        label="Use dark mode"
                                        color="secondary"
                                        inset
                                    />
                                </v-col>

                                <v-col cols="12">
                                    <v-select
                                        name="accent_color"
                                        label="Accent color"
                                        :items="[
                                            { title: 'Pink (default)', value: 'pink' },
                                            { title: 'Teal', value: 'teal' },
                                        ]"
                                        variant="outlined"
                                        density="comfortable"
                                        color="primary"
                                        model-value="pink"
                                    />
                                </v-col>
                            </v-row>

                            <p class="mt-4 text-body-2 text-medium-emphasis">
                                These settings are visual only and do not affect your reports
                                or email content.
                            </p>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>
    </AppLayout>
</template>

<style scoped>
.goviral-card {
    background: rgba(18, 18, 18, 0.96);
    border-radius: 18px;
    border: 1px solid rgba(254, 44, 85, 0.3);
}
</style>
