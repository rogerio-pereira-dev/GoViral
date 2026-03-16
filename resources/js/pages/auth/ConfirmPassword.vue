<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { store } from '@/routes/password/confirm';
</script>

<template>
    <AuthLayout
        title="Confirm your password"
        description="This is a secure area of the application. Please confirm your password before continuing."
    >
        <Head title="Confirm password" />

        <Form
            v-bind="store.form()"
            reset-on-success
            v-slot="{ errors, processing }"
        >
            <div class="d-flex flex-column gap-4">
                <div>
                    <v-text-field
                        id="password"
                        name="password"
                        type="password"
                        label="Password"
                        autocomplete="current-password"
                        autofocus
                        variant="outlined"
                        density="comfortable"
                        color="primary"
                        :error-messages="errors.password"
                    />
                    <InputError :message="errors.password" />
                </div>

                <v-btn
                    type="submit"
                    block
                    color="primary"
                    :loading="processing"
                    :disabled="processing"
                    data-test="confirm-password-button"
                >
                    Confirm Password
                </v-btn>
            </div>
        </Form>
    </AuthLayout>
</template>
