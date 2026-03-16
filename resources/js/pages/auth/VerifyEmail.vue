<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import TextLink from '@/components/TextLink.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <AuthLayout
        title="Verify email"
        description="Please verify your email address by clicking on the link we just emailed to you."
    >
        <Head title="Email verification" />

        <div v-if="status === 'verification-link-sent'" class="mb-4 text-center">
            <v-alert
                type="success"
                density="comfortable"
                variant="tonal"
            >
                A new verification link has been sent to the email address you
                provided during registration.
            </v-alert>
        </div>

        <Form
            v-bind="send.form()"
            v-slot="{ processing }"
        >
            <div class="d-flex flex-column gap-4 text-center">
                <v-btn
                    type="submit"
                    color="primary"
                    variant="tonal"
                    :loading="processing"
                    :disabled="processing"
                >
                    Resend verification email
                </v-btn>

                <TextLink
                    :href="logout()"
                    as="button"
                    class="text-body-2"
                >
                    Log out
                </TextLink>
            </div>
        </Form>
    </AuthLayout>
</template>
