<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogo from '@/components/AppLogo.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <v-app theme="goviralDark">
        <v-main class="d-flex align-center justify-center auth-background">
            <v-container class="d-flex justify-center">
                <v-card
                    class="auth-card py-8 px-6 px-md-10"
                    elevation="10"
                >
                    <div class="d-flex flex-column align-center mb-6">
                        <Link
                            :href="home()"
                            class="d-flex flex-column align-center text-decoration-none"
                        >
                            <div class="mb-2">
                                <AppLogo />
                            </div>
                            <span class="visually-hidden">GoViral</span>
                        </Link>

                        <div class="text-center mt-2">
                            <h1 class="auth-title mb-2">
                                {{ title }}
                            </h1>
                            <p class="auth-description">
                                {{ description }}
                            </p>
                        </div>
                    </div>

                    <div class="auth-form">
                        <slot />
                    </div>
                </v-card>
            </v-container>
        </v-main>
    </v-app>
</template>

<style scoped>
.auth-background {
    min-height: 100vh;
    background:
        radial-gradient(circle at top left, rgba(254, 44, 85, 0.22), transparent 55%),
        radial-gradient(circle at bottom right, rgba(37, 244, 238, 0.18), transparent 55%),
        #121212;
}

.auth-card {
    max-width: 420px;
    width: 100%;
    border-radius: 18px;
    border: 1px solid rgba(254, 44, 85, 0.3);
    background: radial-gradient(circle at top, rgba(255, 255, 255, 0.04), transparent 60%),
        rgba(18, 18, 18, 0.96);
    box-shadow:
        0 18px 45px rgba(0, 0, 0, 0.85),
        0 0 32px rgba(254, 44, 85, 0.45);
}

.auth-logo {
    width: 40px;
    height: 40px;
    color: #ffffff;
}

.auth-title {
    font-size: 1.4rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.94);
}

.auth-description {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.7);
}

.auth-form {
    margin-top: 1.5rem;
}

.visually-hidden {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}
</style>
