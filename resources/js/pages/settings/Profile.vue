<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import DeleteUser from '@/components/DeleteUser.vue';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';

type Props = {
    mustVerifyEmail: boolean;
    status?: string;
};

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: edit().url,
    },
];

const page = usePage();
const user = page.props.auth.user;
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Profile settings" />

        <SettingsLayout>
            <v-card
                class="goviral-card"
                elevation="6"
            >
                <v-card-title class="text-h6">
                    Profile information
                </v-card-title>
                <v-card-subtitle class="mb-4">
                    Update your name and email address
                </v-card-subtitle>

                <v-card-text>
                    <Form
                        v-bind="ProfileController.update.form()"
                        v-slot="{ errors, processing, recentlySuccessful }"
                    >
                        <v-row dense>
                            <v-col cols="12">
                                <v-text-field
                                    id="name"
                                    name="name"
                                    label="Name"
                                    :model-value="user.name"
                                    autocomplete="name"
                                    placeholder="Full name"
                                    color="primary"
                                    variant="outlined"
                                    density="comfortable"
                                    :error-messages="errors.name"
                                />
                                <InputError :message="errors.name" />
                            </v-col>

                            <v-col cols="12">
                                <v-text-field
                                    id="email"
                                    name="email"
                                    type="email"
                                    label="Email address"
                                    :model-value="user.email"
                                    autocomplete="username"
                                    placeholder="Email address"
                                    color="primary"
                                    variant="outlined"
                                    density="comfortable"
                                    :error-messages="errors.email"
                                />
                                <InputError :message="errors.email" />
                            </v-col>
                        </v-row>

                        <div
                            v-if="mustVerifyEmail && !user.email_verified_at"
                            class="mt-4"
                        >
                            <p class="text-body-2 text-medium-emphasis">
                                Your email address is unverified.
                                <Link
                                    :href="send()"
                                    as="button"
                                    class="text-primary text-decoration-underline ms-1"
                                >
                                    Click here to resend the verification email.
                                </Link>
                            </p>

                            <v-alert
                                v-if="status === 'verification-link-sent'"
                                type="success"
                                density="comfortable"
                                variant="tonal"
                                class="mt-3"
                            >
                                A new verification link has been sent to your email
                                address.
                            </v-alert>
                        </div>

                        <div class="d-flex align-center gap-4 mt-6">
                            <v-btn
                                type="submit"
                                color="primary"
                                :loading="processing"
                                :disabled="processing"
                                data-test="update-profile-button"
                            >
                                Save
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

            <v-card
                class="goviral-card mt-6"
                elevation="6"
            >
                <v-card-title class="text-h6">
                    Danger zone
                </v-card-title>
                <v-card-subtitle class="mb-3">
                    Permanently delete your account and all associated data.
                </v-card-subtitle>
                <v-card-text>
                    <DeleteUser />
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
