<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { email } from '@/routes/password';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <AuthLayout
        title="Forgot password"
        description="Enter your email to receive a password reset link"
    >
        <Head title="Forgot password" />

        <div v-if="status" class="mb-4 text-center">
            <v-alert
                type="success"
                density="comfortable"
                variant="tonal"
            >
                {{ status }}
            </v-alert>
        </div>

        <Form v-bind="email.form()" v-slot="{ errors, processing }">
            <div class="d-flex flex-column gap-4">
                <div>
                    <v-text-field
                        id="email"
                        name="email"
                        type="email"
                        label="Email address"
                        autocomplete="off"
                        autofocus
                        placeholder="email@example.com"
                        variant="outlined"
                        density="comfortable"
                        color="primary"
                        :error-messages="errors.email"
                    />
                    <InputError :message="errors.email" />
                </div>

                <v-btn
                    type="submit"
                    block
                    color="primary"
                    :loading="processing"
                    :disabled="processing"
                    data-test="email-password-reset-link-button"
                >
                    Email password reset link
                </v-btn>

                <div class="text-center">
                    <span class="text-body-2 text-medium-emphasis">
                        Or, return to
                    </span>
                    <TextLink :href="login()" class="text-body-2 ms-1">
                        log in
                    </TextLink>
                </div>
            </div>
        </Form>
    </AuthLayout>
</template>
