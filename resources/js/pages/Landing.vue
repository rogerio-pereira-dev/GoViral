<script setup lang="ts">
import { router } from '@inertiajs/vue3';

defineProps<{
    locale: string;
    supportedLocales: string[];
    translations: Record<string, string>;
}>();

const reportItems = [
    'report_summary',
    'report_score',
    'report_niche',
    'report_username',
    'report_bio',
    'report_profile_tips',
    'report_content_ideas',
    'report_30_plan',
    'report_viral_tips',
    'report_action_plan',
];

const painItems = [
    'pain_monetization',
    'pain_viral',
    'pain_planning',
    'pain_strategy',
    'pain_algorithm',
    'pain_no_time',
];

function switchLocale(locale: string) {
    router.get(`/locale/${locale}`);
}
</script>

<template>
    <v-app theme="goviralDark">
        <v-app-bar flat color="rgba(18, 18, 18, 0.9)" class="px-4">
            <v-toolbar-title class="text-h5 font-weight-bold">
                Go<span class="text-primary">Viral</span>
            </v-toolbar-title>
            <v-spacer />
            <v-btn-toggle
                :model-value="locale"
                mandatory
                variant="text"
                density="comfortable"
                color="secondary"
                class="goviral-lang-toggle"
            >
                <v-btn
                    v-for="loc in supportedLocales"
                    :key="loc"
                    :value="loc"
                    :class="{ 'v-btn--active': locale === loc }"
                    @click="switchLocale(loc)"
                >
                    {{ loc.toUpperCase() }}
                </v-btn>
            </v-btn-toggle>
        </v-app-bar>

        <v-main>
            <!-- 1. Hero -->
            <v-container class="py-12 py-md-16 text-center">
                <h1 class="text-h3 text-md-h2 font-weight-bold mb-4">
                    {{ translations.tagline_before }}
                    <span class="text-primary">{{ translations.tagline_highlight }}</span>
                </h1>
                <p class="text-h6 text-medium-emphasis mb-8 mx-auto" style="max-width: 560px">
                    {{ translations.subheadline }}
                </p>
                <p class="text-body-2 text-medium-emphasis mb-8">
                    {{ translations.hero_support }}
                </p>
                <v-btn
                    :href="'/start-growth'"
                    size="x-large"
                    color="primary"
                    class="goviral-cta-glow rounded-pill px-8"
                    height="56"
                >
                    {{ translations.cta_primary }}
                </v-btn>
                <p class="text-caption text-secondary font-weight-medium mt-4">
                    <v-icon start size="small" icon="mdi-timer-outline" />
                    {{ translations.delivery_in_minutes }}
                </p>
            </v-container>

            <!-- 2. Strategy not luck -->
            <v-container class="py-12 py-md-16">
                <v-row align="center">
                    <v-col cols="12" md="6">
                        <h2 class="text-h4 text-md-h3 font-weight-bold mb-4">
                            {{ translations.section_algorithm_before }}
                            <span class="text-secondary">{{ translations.section_algorithm_highlight }}</span>
                        </h2>
                        <p class="text-body-1 text-medium-emphasis mb-6">
                            {{ translations.section_algorithm_lead }}
                        </p>
                        <v-btn
                            :href="'/start-growth'"
                            color="secondary"
                            variant="outlined"
                            class="rounded-pill"
                        >
                            {{ translations.section_algorithm_cta }}
                        </v-btn>
                    </v-col>
                    <v-col cols="12" md="6" class="text-center">
                        <v-img
                            :src="'https://placehold.co/600x400/121212/25F4EE?text=Strategy'"
                            width="100%"
                            max-width="400"
                            aspect-ratio="1"
                            class="mx-auto rounded-lg"
                            cover
                        />
                    </v-col>
                </v-row>
            </v-container>

            <!-- 3. How it works -->
            <v-container class="py-12 py-md-16">
                <h2 class="text-h4 text-center font-weight-bold mb-12">
                    {{ translations.section_how_title }}
                </h2>
                <v-row>
                    <v-col cols="12" md="4" class="text-center">
                        <v-avatar color="primary" size="64" class="mb-4">
                            <v-icon icon="mdi-account-edit-outline" size="32" />
                        </v-avatar>
                        <h3 class="text-h6 font-weight-bold mb-2">
                            {{ translations.section_how_step1 }}
                        </h3>
                        <p class="text-body-2 text-medium-emphasis">
                            {{ translations.section_how_step1_desc }}
                        </p>
                    </v-col>
                    <v-col cols="12" md="4" class="text-center">
                        <v-avatar color="secondary" size="64" class="mb-4">
                            <v-icon icon="mdi-brain" size="32" color="surface" />
                        </v-avatar>
                        <h3 class="text-h6 font-weight-bold mb-2">
                            {{ translations.section_how_step2 }}
                        </h3>
                        <p class="text-body-2 text-medium-emphasis">
                            {{ translations.section_how_step2_desc }}
                        </p>
                    </v-col>
                    <v-col cols="12" md="4" class="text-center">
                        <v-avatar color="primary" size="64" class="mb-4">
                            <v-icon icon="mdi-email-check-outline" size="32" />
                        </v-avatar>
                        <h3 class="text-h6 font-weight-bold mb-2">
                            {{ translations.section_how_step3 }}
                        </h3>
                        <p class="text-body-2 text-medium-emphasis">
                            {{ translations.section_how_step3_desc }}
                        </p>
                    </v-col>
                </v-row>
                <div class="text-center mt-8">
                    <v-btn
                        :href="'/start-growth'"
                        color="primary"
                        variant="outlined"
                        class="rounded-pill"
                    >
                        {{ translations.cta_primary }}
                    </v-btn>
                </div>
            </v-container>

            <!-- 4. What you get (full report) -->
            <v-container class="py-12 py-md-16">
                <v-sheet color="rgba(255,255,255,0.03)" class="pa-8 pa-md-12 rounded-xl">
                    <h2 class="text-h4 text-center font-weight-bold mb-8">
                        {{ translations.section_what_you_get_title }}
                    </h2>
                    <v-list bg-color="transparent" class="mb-8">
                        <v-list-item
                            v-for="key in reportItems"
                            :key="key"
                            class="px-0"
                            border
                        >
                            <template #prepend>
                                <v-icon color="secondary" icon="mdi-check-circle-outline" />
                            </template>
                            <v-list-item-title class="text-body-2">
                                {{ translations[key] }}
                            </v-list-item-title>
                        </v-list-item>
                    </v-list>
                    <div class="text-center">
                        <v-btn
                            :href="'/start-growth'"
                            size="x-large"
                            color="primary"
                            class="goviral-cta-glow rounded-pill px-8"
                        >
                            {{ translations.cta_blueprint }}
                        </v-btn>
                    </div>
                </v-sheet>
            </v-container>

            <!-- 5. Pains -->
            <v-container class="py-12 py-md-16">
                <h2 class="text-h4 text-center font-weight-bold mb-4">
                    {{ translations.section_pains_title }}
                </h2>
                <p class="text-body-1 text-medium-emphasis text-center mb-8 mx-auto" style="max-width: 480px">
                    {{ translations.section_pains_lead }}
                </p>
                <v-row dense>
                    <v-col
                        v-for="key in painItems"
                        :key="key"
                        cols="12"
                        sm="6"
                        md="4"
                    >
                        <v-card variant="outlined" color="rgba(255,255,255,0.06)" class="pa-4 h-100">
                            <div class="d-flex align-center">
                                <v-icon color="primary" size="small" class="mr-2" icon="mdi-alert-circle-outline" />
                                <span class="text-body-2">{{ translations[key] }}</span>
                            </div>
                        </v-card>
                    </v-col>
                </v-row>
                <div class="text-center mt-8">
                    <v-btn
                        :href="'/start-growth'"
                        color="secondary"
                        class="rounded-pill"
                    >
                        {{ translations.cta_primary }}
                    </v-btn>
                </div>
            </v-container>

            <!-- 6. Final CTA -->
            <v-container class="py-12 py-md-16">
                <v-sheet color="primary" class="pa-10 pa-md-16 rounded-xl text-center">
                    <h2 class="text-h4 text-md-h3 font-weight-bold mb-4">
                        {{ translations.section_final_title }}
                    </h2>
                    <p class="text-h6 text-medium-emphasis mb-8 mx-auto" style="max-width: 480px">
                        {{ translations.section_final_lead }}
                    </p>
                    <v-btn
                        :href="'/start-growth'"
                        size="x-large"
                        color="surface"
                        class="text-primary rounded-pill px-10"
                        height="56"
                    >
                        {{ translations.section_final_cta }}
                    </v-btn>
                </v-sheet>
            </v-container>

            <!-- 7. Footer -->
            <v-footer class="py-8">
                <v-container class="text-center">
                    <div class="text-h6 font-weight-bold mb-2">
                        Go<span class="text-primary">Viral</span>
                    </div>
                    <div class="text-caption text-medium-emphasis">
                        {{ translations.footer_tagline }} · {{ translations.footer_price }}
                    </div>
                </v-container>
            </v-footer>
        </v-main>
    </v-app>
</template>

<style scoped>
.goviral-cta-glow {
    box-shadow: 0 0 20px rgba(254, 44, 85, 0.4);
    transition: box-shadow 0.2s ease, transform 0.2s ease;
}
.goviral-cta-glow:hover {
    box-shadow: 0 0 28px rgba(254, 44, 85, 0.55);
    transform: scale(1.02);
}
.goviral-lang-toggle :deep(.v-btn--active) {
    color: rgb(var(--v-theme-secondary));
    font-weight: 700;
}
</style>
