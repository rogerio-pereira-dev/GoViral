<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import TextLink from '@/components/TextLink.vue';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
</script>

<template>
    <AuthBase
        title="Registration disabled"
        description="This application does not allow user registration."
    >
        <Head title="Register" />

        <div class="space-y-4 text-center">
            <p class="text-sm text-muted-foreground">
                Registration is currently disabled. If you already have an account, you can log in
                below.
            </p>

            <TextLink :href="login()" class="underline underline-offset-4">
                Log in
            </TextLink>
        </div>
    </AuthBase>
</template>
