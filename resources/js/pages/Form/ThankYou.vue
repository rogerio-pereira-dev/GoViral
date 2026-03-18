<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';

defineProps<{
    translations: {
        title: string;
        message: string;
        cta: string;
    };
}>();
</script>

<template>
    <Head :title="translations.title" />

    <PublicLayout>
        <div class="thank-you-main">
            <v-container class="thank-you-container" max-width="760">
                <v-card class="thank-you-card text-center" elevation="12">
                    <v-card-text class="py-12 px-8">
                        <v-avatar color="secondary" size="72" class="mb-6">
                            <v-icon icon="mdi-email-check-outline" size="38" color="surface" />
                        </v-avatar>

                        <h1 class="thank-you-title font-weight-bold mb-4">
                            {{ translations.title }}
                        </h1>

                        <p class="thank-you-message text-medium-emphasis mb-8">
                            {{ translations.message }}
                        </p>

                        <v-btn
                            href="/"
                            color="primary"
                            size="x-large"
                            class="thank-you-cta"
                        >
                            {{ translations.cta }}
                        </v-btn>
                    </v-card-text>
                </v-card>
            </v-container>
        </div>
    </PublicLayout>
</template>

<style scoped>
.thank-you-main {
    background: radial-gradient(circle at top left, rgba(254, 44, 85, 0.14), transparent 40%),
        radial-gradient(circle at top right, rgba(37, 244, 238, 0.14), transparent 45%),
        #121212;
}

.thank-you-container {
    padding-top: 5rem;
    padding-bottom: 5rem;
}

.thank-you-card {
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(18, 18, 18, 0.85);
    backdrop-filter: blur(4px);
}

.thank-you-title {
    font-size: 2.6rem;
    line-height: 1.1;
    color: #fe2c55;
}

.thank-you-message {
    font-size: 1.15rem;
    line-height: 1.55;
}

.thank-you-cta {
    min-width: 220px;
    height: 56px;
    border-radius: 9999px;
    text-transform: none;
    font-size: 18px;
    box-shadow: 0 0 20px rgba(254, 44, 85, 0.4);
}
</style>
