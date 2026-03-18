<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';

defineProps<{
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

</script>

<template>
    <PublicLayout>
        <!-- 1. Hero -->
        <section class="landing-section section-bg-pink">
            <v-container class="landing-section-inner text-center landing-hero">
                <h1 class="landing-h1 font-weight-bold mb-6 section-on-pink">
                    {{ translations.tagline_before }}
                    <span class="text-secondary">{{ translations.tagline_highlight }}</span>
                </h1>
                <h2 class="landing-hero-sub mb-6 mx-auto section-on-pink" style="max-width: 620px">
                    {{ translations.subheadline }}
                </h2>
                <p class="landing-text mb-10 mx-auto section-on-pink" style="max-width: 560px">
                    {{ translations.hero_support }}
                </p>
                <v-btn
                    :href="'/start-growth'"
                    dusk="landing-start-growth-button"
                    class="landing-btn goviral-cta-glow section-btn-on-pink"
                >
                    {{ translations.cta_primary }}
                </v-btn>
            </v-container>
        </section>

        <!-- 2. Strategy not luck -->
        <section class="landing-section section-bg-blue">
            <v-container class="landing-section-inner">
                <v-row justify="center">
                    <v-col cols="12" md="8" class="text-center">
                        <h2 class="landing-h2 font-weight-bold mb-5 section-on-blue">
                            {{ translations.section_algorithm_before }}
                            <span class="text-primary">{{ translations.section_algorithm_highlight }}</span>
                        </h2>
                        <p class="landing-text mb-8 mx-auto section-on-blue" style="max-width: 520px">
                            {{ translations.section_algorithm_lead }}
                        </p>
                        <v-btn
                            :href="'/start-growth'"
                            class="landing-btn goviral-cta-glow section-btn-on-blue"
                        >
                            {{ translations.section_algorithm_cta }}
                        </v-btn>
                    </v-col>
                </v-row>
            </v-container>
        </section>

        <!-- 3. How it works -->
        <section class="landing-section section-bg-pink">
            <v-container class="landing-section-inner">
                <h2 class="landing-h2 text-center font-weight-bold mb-12 section-on-pink">
                    {{ translations.section_how_title }}
                </h2>
                <v-row>
                    <v-col cols="12" md="4" class="text-center">
                        <v-avatar color="primary" size="72" class="mb-4">
                            <v-icon icon="mdi-account-edit-outline" size="36" />
                        </v-avatar>
                        <h3 class="landing-h4 font-weight-bold mb-3 section-on-pink">
                            {{ translations.section_how_step1 }}
                        </h3>
                        <p class="landing-text-small section-on-pink">
                            {{ translations.section_how_step1_desc }}
                        </p>
                    </v-col>
                    <v-col cols="12" md="4" class="text-center">
                        <v-avatar color="secondary" size="72" class="mb-4">
                            <v-icon icon="mdi-brain" size="36" color="surface" />
                        </v-avatar>
                        <h3 class="landing-h4 font-weight-bold mb-3 section-on-pink">
                            {{ translations.section_how_step2 }}
                        </h3>
                        <p class="landing-text-small section-on-pink">
                            {{ translations.section_how_step2_desc }}
                        </p>
                    </v-col>
                    <v-col cols="12" md="4" class="text-center">
                        <v-avatar color="primary" size="72" class="mb-4">
                            <v-icon icon="mdi-email-check-outline" size="36" />
                        </v-avatar>
                        <h3 class="landing-h4 font-weight-bold mb-3 section-on-pink">
                            {{ translations.section_how_step3 }}
                        </h3>
                        <p class="landing-text-small section-on-pink">
                            {{ translations.section_how_step3_desc }}
                        </p>
                    </v-col>
                </v-row>
                <div class="text-center mt-10">
                    <v-btn
                        :href="'/start-growth'"
                        class="landing-btn goviral-cta-glow section-btn-on-pink"
                    >
                        {{ translations.cta_primary }}
                    </v-btn>
                </div>
            </v-container>
        </section>

        <!-- 4. What you get (full report) -->
        <section class="landing-section section-bg-blue">
            <v-container class="landing-section-inner">
                <h2 class="landing-h2 text-center font-weight-bold mb-10 section-on-blue">
                    {{ translations.section_what_you_get_title }}
                </h2>
                <v-row class="report-list mb-10 justify-center">
                    <v-col cols="12" md="6" class="d-flex flex-column align-center">
                        <div
                            v-for="key in reportItems.slice(0, 5)"
                            :key="key"
                            class="d-flex align-center justify-center gap-2 py-2 report-item"
                        >
                            <v-icon color="primary" size="small" icon="mdi-check-circle-outline" />
                            <span class="landing-text section-on-blue">{{ translations[key] }}</span>
                        </div>
                    </v-col>
                    <v-col cols="12" md="6" class="d-flex flex-column align-center">
                        <div
                            v-for="key in reportItems.slice(5, 10)"
                            :key="key"
                            class="d-flex align-center justify-center gap-2 py-2 report-item"
                        >
                            <v-icon color="primary" size="small" icon="mdi-check-circle-outline" />
                            <span class="landing-text section-on-blue">{{ translations[key] }}</span>
                        </div>
                    </v-col>
                </v-row>
                <div class="text-center">
                    <v-btn
                        :href="'/start-growth'"
                        class="landing-btn goviral-cta-glow section-btn-on-blue"
                    >
                        {{ translations.cta_blueprint }}
                    </v-btn>
                </div>
            </v-container>
        </section>

        <!-- 5. Pains -->
        <section class="landing-section section-bg-pink pains-section">
            <v-container class="landing-section-inner">
                <h2 class="landing-h2 text-center font-weight-bold mb-4 section-on-pink">
                    {{ translations.section_pains_title }}
                </h2>
                <p class="landing-text text-center mb-8 mx-auto section-on-pink" style="max-width: 520px">
                    {{ translations.section_pains_lead }}
                </p>
                <v-row justify="center">
                    <v-col cols="12" md="6" class="d-flex flex-column align-center">
                        <div
                            v-for="key in painItems.slice(0, 3)"
                            :key="key"
                            class="d-flex align-center justify-center gap-2 py-2 pain-item"
                        >
                            <v-icon color="secondary" size="small" icon="mdi-alert-circle-outline" />
                            <span class="landing-text-small section-on-pink">{{ translations[key] }}</span>
                        </div>
                    </v-col>
                    <v-col cols="12" md="6" class="d-flex flex-column align-center">
                        <div
                            v-for="key in painItems.slice(3, 6)"
                            :key="key"
                            class="d-flex align-center justify-center gap-2 py-2 pain-item"
                        >
                            <v-icon color="secondary" size="small" icon="mdi-alert-circle-outline" />
                            <span class="landing-text-small section-on-pink">{{ translations[key] }}</span>
                        </div>
                    </v-col>
                </v-row>
                <div class="text-center mt-10">
                    <v-btn
                        :href="'/start-growth'"
                        class="landing-btn goviral-cta-glow section-btn-on-pink"
                    >
                        {{ translations.cta_primary }}
                    </v-btn>
                </div>
            </v-container>
        </section>

        <!-- 6. Final CTA (Clarity in minutes) -->
        <section class="landing-section section-bg-blue">
            <v-container class="landing-section-inner text-center">
                <h2 class="landing-h2 font-weight-bold mb-4 section-on-blue">
                    {{ translations.section_final_title }}
                </h2>
                <p class="landing-text mb-8 mx-auto section-on-blue" style="max-width: 480px">
                    {{ translations.section_final_lead }}
                </p>
                <v-btn
                    :href="'/start-growth'"
                    class="landing-btn goviral-cta-glow section-btn-on-blue"
                >
                    {{ translations.section_final_cta }}
                </v-btn>
            </v-container>
        </section>
    </PublicLayout>
</template>

<style scoped>
/* Typography: fixed sizes (no Vuetify text-h*) */
.landing-text { font-size: 18px; line-height: 1.5; }
.landing-text-small { font-size: 16px; line-height: 1.5; }
.landing-h1 { font-size: 34px; line-height: 1.2; }
.landing-h2 { font-size: 30px; line-height: 1.25; }
.landing-h3 { font-size: 26px; line-height: 1.3; }
.landing-h4, .landing-h5, .landing-h6 { font-size: 22px; line-height: 1.35; }
.landing-hero-sub { font-size: 26px; line-height: 1.4; opacity: 0.95; }

.report-list .gap-2 { gap: 0.5rem; }

/* Sections: tall, no lines between */
.landing-section {
    min-height: 75vh;
    display: flex;
    align-items: center;
}
.landing-section-inner {
    padding-top: 3rem;
    padding-bottom: 3rem;
    width: 100%;
}
@media (min-width: 960px) {
    .landing-section-inner { padding-top: 4rem; padding-bottom: 4rem; }
}

/* Section backgrounds: alternate rosa (detalhes azul) / azul (detalhes rosa) */
.section-bg-pink {
    background-color: #121212;
}
.section-bg-blue {
    background: linear-gradient(135deg, rgba(18, 18, 18, 0.96) 0%, rgba(25, 45, 50, 0.95) 40%, rgba(37, 244, 238, 0.12) 100%) !important;
    border: 1px solid rgba(254, 44, 85, 0.22);
}
.section-on-pink { color: rgba(255, 255, 255, 0.92); }
.section-on-blue { color: rgba(255, 255, 255, 0.92); }
.section-btn-on-pink { background-color: #25F4EE !important; color: #121212 !important; }
.section-btn-on-blue { background-color: #FE2C55 !important; color: #fff !important; }

/* All buttons: filled, same size and glow */
.landing-btn {
    min-width: 220px;
    height: 56px;
    border-radius: 9999px;
    padding-left: 2rem;
    padding-right: 2rem;
    font-size: 18px;
    text-transform: none;
}
.goviral-cta-glow {
    box-shadow: 0 0 20px rgba(254, 44, 85, 0.4);
    transition: box-shadow 0.2s ease, transform 0.2s ease;
}
.goviral-cta-glow:hover {
    box-shadow: 0 0 28px rgba(254, 44, 85, 0.55);
    transform: scale(1.02);
}
.landing-btn.section-btn-on-blue.goviral-cta-glow {
    box-shadow: 0 0 20px rgba(254, 44, 85, 0.4);
}
.landing-btn.section-btn-on-pink.goviral-cta-glow {
    box-shadow: 0 0 18px rgba(37, 244, 238, 0.45);
}

</style>
