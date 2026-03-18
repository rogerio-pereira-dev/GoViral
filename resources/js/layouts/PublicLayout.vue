<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const locale = computed<string>(() => String(page.props.locale ?? 'en'));

const supportedLocales = computed<string[]>(() => {
    const value = page.props.supportedLocales;

    if (Array.isArray(value)) {
        return value.map((item) => String(item));
    }

    return ['en', 'es', 'pt'];
});

const footerTagline = computed<string>(() => String(page.props.footerTagline ?? ''));

function switchLocale(nextLocale: string): void {
    router.get(`/locale/${nextLocale}`);
}
</script>

<template>
    <v-app theme="goviralDark">
        <v-app-bar
            flat
            color="rgba(18, 18, 18, 0.9)"
            class="px-4"
        >
            <a
                href="#top"
                class="landing-logo font-weight-bold text-white text-decoration-none d-flex align-center"
            >
                Go<span class="text-primary">Viral</span>
            </a>

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

        <v-main id="top">
            <slot />
        </v-main>

        <v-footer class="py-6 landing-footer">
            <v-container class="text-center">
                <div class="landing-h4 font-weight-bold mb-1">
                    Go<span class="text-primary">Viral</span>
                </div>

                <div class="landing-footer-tagline text-medium-emphasis">
                    {{ footerTagline }}
                </div>
            </v-container>
        </v-footer>
    </v-app>
</template>

<style scoped>
.landing-logo { font-size: 22px; }

.landing-h4, .landing-h5, .landing-h6 {
    font-size: 22px;
    line-height: 1.35;
}

.goviral-lang-toggle :deep(.v-btn--active) {
    color: rgb(var(--v-theme-secondary));
    font-weight: 700;
}

.landing-footer-tagline { font-size: 13px; }

.landing-footer { border-top: none; background: #121212; }
</style>
