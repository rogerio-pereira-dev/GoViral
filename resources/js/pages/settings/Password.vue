<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import PasswordController from '@/actions/App/Http/Controllers/Settings/PasswordController';
import { edit } from '@/routes/user-password';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Password settings',
        href: edit().url,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Password settings" />

        <SettingsLayout>
            <v-card
                class="goviral-card"
                elevation="6"
            >
                <v-card-title class="text-h6">
                    Update password
                </v-card-title>
                <v-card-subtitle class="mb-4">
                    Ensure your account is using a long, random password to stay secure.
                </v-card-subtitle>

                <v-card-text>
                    <Form
                        v-bind="PasswordController.update.form()"
                        :options="{ preserveScroll: true }"
                        reset-on-success
                        :reset-on-error="[
                            'password',
                            'password_confirmation',
                            'current_password',
                        ]"
                        v-slot="{ errors, processing, recentlySuccessful }"
                    >
                        <v-row dense>
                            <v-col cols="12">
                                <v-text-field
                                    id="current_password"
                                    name="current_password"
                                    type="password"
                                    label="Current password"
                                    autocomplete="current-password"
                                    placeholder="Current password"
                                    variant="outlined"
                                    density="comfortable"
                                    color="primary"
                                    :error-messages="errors.current_password"
                                />
                                <InputError :message="errors.current_password" />
                            </v-col>

                            <v-col cols="12">
                                <v-text-field
                                    id="password"
                                    name="password"
                                    type="password"
                                    label="New password"
                                    autocomplete="new-password"
                                    placeholder="New password"
                                    variant="outlined"
                                    density="comfortable"
                                    color="primary"
                                    :error-messages="errors.password"
                                />
                                <InputError :message="errors.password" />
                            </v-col>

                            <v-col cols="12">
                                <v-text-field
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    type="password"
                                    label="Confirm password"
                                    autocomplete="new-password"
                                    placeholder="Confirm password"
                                    variant="outlined"
                                    density="comfortable"
                                    color="primary"
                                    :error-messages="errors.password_confirmation"
                                />
                                <InputError :message="errors.password_confirmation" />
                            </v-col>
                        </v-row>

                        <div class="d-flex align-center gap-4 mt-6">
                            <v-btn
                                type="submit"
                                color="primary"
                                :loading="processing"
                                :disabled="processing"
                                data-test="update-password-button"
                            >
                                Save password
                            </v-btn>

                            <Transition
                                enter-active-class="transition-opacity"
                                enter-from-class="opacity-0"
                                leave-active-class="transition-opacity"
                                leave-to-class="opacity-0"
                            >
                                <p
                                    v-show="recentlySuccessful"
                                    class="text-body-2 text-medium-emphasis"
                                >
                                    Saved.
                                </p>
                            </Transition>
                        </div>
                    </Form>
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
