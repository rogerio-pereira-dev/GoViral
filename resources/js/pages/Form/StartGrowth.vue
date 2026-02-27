<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{
    locale: string;
    translations: {
        title: string;
        subtitle: string;
        copy_title: string;
        copy_lead: string;
        what_you_get_title: string;
        report_item_1: string;
        report_item_2: string;
        report_item_3: string;
        report_item_4: string;
        report_item_5: string;
        report_item_6: string;
        report_item_7: string;
        report_item_8: string;
        email_label: string;
        email_placeholder: string;
        email_hint: string;
        tiktok_username_label: string;
        tiktok_username_placeholder: string;
        aspiring_niche_label: string;
        aspiring_niche_placeholder: string;
        bio_label: string;
        bio_placeholder: string;
        video_url_1_label: string;
        video_url_2_label: string;
        video_url_3_label: string;
        video_url_placeholder: string;
        notes_label: string;
        notes_placeholder: string;
        submit_cta: string;
    };
}>();

const form = useForm({
    email: '',
    tiktok_username: '',
    bio: '',
    aspiring_niche: '',
    video_url_1: '',
    video_url_2: '',
    video_url_3: '',
    notes: '',
});

function submit(): void {
    form.post('/start-growth');
}
</script>

<template>
    <Head :title="translations.title" />

    <v-app theme="goviralDark">
        <v-main class="start-growth-main">
            <v-container class="start-growth-container" max-width="1200">
                <v-row class="ga-6" align="stretch">
                    <v-col cols="12" md="5">
                        <v-card class="copy-panel" elevation="12">
                            <v-card-text class="d-flex flex-column fill-height">
                                <h1 class="copy-title text-h3 font-weight-bold mb-4">
                                    {{ translations.copy_title }}
                                </h1>

                                <p class="copy-lead text-medium-emphasis mb-8">
                                    {{ translations.copy_lead }}
                                </p>

                                <h3 class="copy-section-title mb-3">
                                    {{ translations.what_you_get_title }}
                                </h3>

                                <v-list class="copy-list mb-6" bg-color="transparent">
                                    <v-list-item prepend-icon="mdi-check-circle-outline" :title="translations.report_item_1" />
                                    <v-list-item prepend-icon="mdi-check-circle-outline" :title="translations.report_item_2" />
                                    <v-list-item prepend-icon="mdi-check-circle-outline" :title="translations.report_item_3" />
                                    <v-list-item prepend-icon="mdi-check-circle-outline" :title="translations.report_item_4" />
                                    <v-list-item prepend-icon="mdi-check-circle-outline" :title="translations.report_item_5" />
                                    <v-list-item prepend-icon="mdi-check-circle-outline" :title="translations.report_item_6" />
                                    <v-list-item prepend-icon="mdi-check-circle-outline" :title="translations.report_item_7" />
                                    <v-list-item prepend-icon="mdi-check-circle-outline" :title="translations.report_item_8" />
                                </v-list>
                            </v-card-text>
                        </v-card>
                    </v-col>

                    <v-col cols="12" md="7">
                        <v-card elevation="12" class="form-panel">
                            <v-card-item>
                                <v-card-title class="form-title font-weight-bold">
                                    {{ translations.title }}
                                </v-card-title>
                                <v-card-subtitle class="form-subtitle mt-2">
                                    {{ translations.subtitle }}
                                </v-card-subtitle>
                            </v-card-item>

                            <v-card-text class="form-card-body">
                                <v-form @submit.prevent="submit">
                                    <v-row>
                                        <v-col cols="12">
                                            <v-text-field
                                                v-model="form.email"
                                                :label="translations.email_label"
                                                type="email"
                                                autocomplete="email"
                                                :placeholder="translations.email_placeholder"
                                                :error-messages="form.errors.email"
                                                required
                                                persistent-hint
                                                :hint="translations.email_hint"
                                            />
                                        </v-col>

                                        <v-col cols="12" sm="6">
                                            <v-text-field
                                                v-model="form.tiktok_username"
                                                :label="translations.tiktok_username_label"
                                                :placeholder="translations.tiktok_username_placeholder"
                                                :error-messages="form.errors.tiktok_username"
                                            />
                                        </v-col>

                                        <v-col cols="12" sm="6">
                                            <v-text-field
                                                v-model="form.aspiring_niche"
                                                :label="translations.aspiring_niche_label"
                                                :placeholder="translations.aspiring_niche_placeholder"
                                                :error-messages="form.errors.aspiring_niche"
                                                required
                                            />
                                        </v-col>

                                        <v-col cols="12">
                                            <v-textarea
                                                v-model="form.bio"
                                                :label="translations.bio_label"
                                                :placeholder="translations.bio_placeholder"
                                                :error-messages="form.errors.bio"
                                                rows="3"
                                                auto-grow
                                            />
                                        </v-col>

                                        <v-col cols="12" sm="4">
                                            <v-text-field
                                                v-model="form.video_url_1"
                                                :label="translations.video_url_1_label"
                                                type="url"
                                                :placeholder="translations.video_url_placeholder"
                                                :error-messages="form.errors.video_url_1"
                                            />
                                        </v-col>

                                        <v-col cols="12" sm="4">
                                            <v-text-field
                                                v-model="form.video_url_2"
                                                :label="translations.video_url_2_label"
                                                type="url"
                                                :placeholder="translations.video_url_placeholder"
                                                :error-messages="form.errors.video_url_2"
                                            />
                                        </v-col>

                                        <v-col cols="12" sm="4">
                                            <v-text-field
                                                v-model="form.video_url_3"
                                                :label="translations.video_url_3_label"
                                                type="url"
                                                :placeholder="translations.video_url_placeholder"
                                                :error-messages="form.errors.video_url_3"
                                            />
                                        </v-col>

                                        <v-col cols="12">
                                            <v-textarea
                                                v-model="form.notes"
                                                :label="translations.notes_label"
                                                :placeholder="translations.notes_placeholder"
                                                :error-messages="form.errors.notes"
                                                rows="3"
                                                auto-grow
                                            />
                                        </v-col>
                                    </v-row>

                                    <v-btn
                                        type="submit"
                                        color="primary"
                                        variant="flat"
                                        size="x-large"
                                        block
                                        :loading="form.processing"
                                        :disabled="form.processing"
                                        class="mt-4 cta-button"
                                    >
                                        {{ translations.submit_cta }}
                                    </v-btn>
                                </v-form>
                            </v-card-text>
                        </v-card>
                    </v-col>
                </v-row>
            </v-container>
        </v-main>
    </v-app>
</template>

<style scoped>
.start-growth-main {
    background: radial-gradient(circle at top left, rgba(254, 44, 85, 0.14), transparent 40%),
        radial-gradient(circle at top right, rgba(37, 244, 238, 0.14), transparent 45%),
        #121212;
}

.start-growth-container {
    padding-top: 4rem;
    padding-bottom: 4rem;
}

.copy-panel,
.form-panel {
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(18, 18, 18, 0.82);
    backdrop-filter: blur(4px);
}

.copy-title {
    font-size: 3rem !important;
    line-height: 1.1;
    color: #25f4ee;
}

.copy-lead {
    font-size: 1.12rem;
    line-height: 1.55;
}

.copy-section-title {
    font-size: 1.4rem;
    font-weight: 700;
    line-height: 1.25;
    color: #fe2c55;
}

.form-title {
    font-size: 3rem;
    line-height: 1.1;
    letter-spacing: 0;
    color: #fe2c55;
}

.form-subtitle {
    font-size: 1rem;
    line-height: 1.4;
}

.form-card-body {
    padding-bottom: 3rem !important;
}

.copy-list :deep(.v-list-item-title) {
    white-space: normal;
    line-height: 1.35;
    font-size: 1.02rem;
}

.cta-button {
    height: 56px !important;
    border-radius: 9999px !important;
    font-size: 18px !important;
    text-transform: none !important;
    box-shadow: 0 0 20px rgba(254, 44, 85, 0.4);
    transition: box-shadow 0.2s ease, transform 0.2s ease;
}

.cta-button:hover {
    box-shadow: 0 0 28px rgba(254, 44, 85, 0.55);
    transform: scale(1.01);
}

@media (max-width: 960px) {
    .copy-title,
    .form-title {
        font-size: 2.2rem !important;
    }
}
</style>
