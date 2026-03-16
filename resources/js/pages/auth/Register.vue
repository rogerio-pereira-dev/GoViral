<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
</script>

<template>
    <AuthBase
        title="Registration disabled"
        description="This application does not allow user registration."
    >
        <Head title="Register" />

        <div class="d-flex flex-column align-center gap-4 text-center">
            <p class="text-body-2 text-medium-emphasis">
                Registration is currently disabled. If you already have an account, you can log in
                below.
            </p>

            <v-btn
                :href="login()"
                color="primary"
                variant="tonal"
                data-test="register-login-link"
            >
                Log in
            </v-btn>
        </div>
    </AuthBase>
</template>
