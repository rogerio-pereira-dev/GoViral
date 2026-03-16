<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { update } from '@/routes/password';

const props = defineProps<{
    token: string;
    email: string;
}>();

const inputEmail = ref(props.email);
</script>

<template>
    <AuthLayout
        title="Reset password"
        description="Please enter your new password below"
    >
        <Head title="Reset password" />

        <Form
            v-bind="update.form()"
            :transform="(data) => ({ ...data, token, email })"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
        >
            <div class="d-flex flex-column gap-4">
                <div>
                    <v-text-field
                        id="email"
                        name="email"
                        type="email"
                        label="Email"
                        autocomplete="email"
                        v-model="inputEmail"
                        variant="outlined"
                        density="comfortable"
                        color="primary"
                        readonly
                        :error-messages="errors.email"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div>
                    <v-text-field
                        id="password"
                        name="password"
                        type="password"
                        label="Password"
                        autocomplete="new-password"
                        autofocus
                        placeholder="Password"
                        variant="outlined"
                        density="comfortable"
                        color="primary"
                        :error-messages="errors.password"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div>
                    <v-text-field
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        label="Confirm Password"
                        autocomplete="new-password"
                        placeholder="Confirm password"
                        variant="outlined"
                        density="comfortable"
                        color="primary"
                        :error-messages="errors.password_confirmation"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <v-btn
                    type="submit"
                    block
                    color="primary"
                    :loading="processing"
                    :disabled="processing"
                    data-test="reset-password-button"
                >
                    Reset password
                </v-btn>
            </div>
        </Form>
    </AuthLayout>
</template>
