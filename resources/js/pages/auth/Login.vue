<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import AuthBase from '@/layouts/AuthLayout.vue';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <AuthBase
        title="Log in to your account"
        description="Enter your email and password below to log in"
    >
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-center">
            <v-alert
                type="success"
                density="comfortable"
                variant="tonal"
            >
                {{ status }}
            </v-alert>
        </div>

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
        >
            <div class="d-flex flex-column gap-4">
                <v-text-field
                    id="email"
                    name="email"
                    type="email"
                    label="Email address"
                    autocomplete="email"
                    autofocus
                    :tabindex="1"
                    placeholder="email@example.com"
                    variant="outlined"
                    density="comfortable"
                    color="primary"
                    :error-messages="errors.email"
                />

                <div>
                    <div class="d-flex justify-space-between align-center mb-1">
                        <span class="text-body-2 text-medium-emphasis">
                            Password
                        </span>
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-body-2"
                            :tabindex="5"
                        >
                            Forgot password?
                        </TextLink>
                    </div>

                    <v-text-field
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        placeholder="Password"
                        variant="outlined"
                        density="comfortable"
                        color="primary"
                        :tabindex="2"
                        :error-messages="errors.password"
                    />
                </div>

                <v-checkbox
                    id="remember"
                    name="remember"
                    :tabindex="3"
                    label="Remember me"
                    color="secondary"
                    density="comfortable"
                    hide-details
                />

                <v-btn
                    type="submit"
                    class="mt-2"
                    block
                    color="primary"
                    :tabindex="4"
                    :loading="processing"
                    :disabled="processing"
                    data-test="login-button"
                >
                    Log in
                </v-btn>
            </div>
        </Form>
    </AuthBase>
</template>
