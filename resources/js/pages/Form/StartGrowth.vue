<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { nextTick, onMounted, ref } from 'vue';

const props = defineProps<{
    locale: string;
    turnstileSiteKey: string | null;
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
        payment_title: string;
        payment_description: string;
        payment_card_label: string;
        payment_submit_cta: string;
        payment_processing_cta: string;
        payment_init_error: string;
        payment_confirm_error: string;
        payment_declined_error: string;
        payment_insufficient_funds_error: string;
        payment_amount_label: string;
        validation_failed_message: string;
        coupon_code_label: string;
        coupon_apply_cta: string;
        coupon_invalid: string;
        coupon_applied_hint: string;
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

const paymentInitialized = ref(false);
const paymentLoading = ref(false);
const paymentError = ref('');
const stripeClient = ref<any>(null);
const cardElement = ref<any>(null);
const clientSecret = ref('');
const thankYouUrl = ref('/thank-you');
const paymentIntentId = ref('');
const amountDisplay = ref('');
const turnstileToken = ref('');
const turnstileReady = ref(false);
const showValidationFailedMessage = ref(false);
const couponCodeInput = ref('');
const couponError = ref('');
const appliedDiscountPercent = ref<number | null>(null);

function csrfToken(): string {
    const tokenFromMeta = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content') ?? '';

    if (tokenFromMeta) {
        return tokenFromMeta;
    }

    const xsrfCookie = document.cookie
        .split('; ')
        .find((item) => item.startsWith('XSRF-TOKEN='));

    if (! xsrfCookie) {
        return '';
    }

    return decodeURIComponent(xsrfCookie.split('=')[1] ?? '');
}

async function loadTurnstileScript(): Promise<void> {
    if ((window as any).turnstile) {
        return;
    }

    await new Promise<void>((resolve, reject) => {
        const script = document.createElement('script');
        script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js';
        script.async = true;
        script.defer = true;
        script.onload = () => resolve();
        script.onerror = () => reject(new Error('Turnstile load error'));
        document.head.appendChild(script);
    });
}

function onTurnstileSuccess(token: string): void {
    turnstileToken.value = token;
}

function onTurnstileExpired(): void {
    turnstileToken.value = '';
}

function registerTurnstileCallbacks(): void {
    (window as any).onTurnstileSuccess = onTurnstileSuccess;
    (window as any).onTurnstileExpired = onTurnstileExpired;
}

async function loadStripeLibrary(): Promise<void> {
    if ((window as any).Stripe) {
        return;
    }

    await new Promise<void>((resolve, reject) => {
        const script = document.createElement('script');
        script.src = 'https://js.stripe.com/v3/';
        script.async = true;
        script.onload = () => resolve();
        script.onerror = () => reject(new Error('Stripe load error'));
        document.head.appendChild(script);
    });
}

function formatAmount(cents: number, currency: string): string {
    const normalizedCurrency = currency.toUpperCase();

    return new Intl.NumberFormat(props.locale, {
        style: 'currency',
        currency: normalizedCurrency,
    }).format(cents / 100);
}

type InitPaymentOptions = {
    /** When re-fetching after invalid coupon, keep the error message visible. */
    skipClearCouponError?: boolean;
};

async function initializePayment(
    couponForIntent?: string,
    options?: InitPaymentOptions,
): Promise<void> {
    paymentError.value = '';
    if (! options?.skipClearCouponError) {
        couponError.value = '';
    }
    paymentLoading.value = true;

    if (cardElement.value) {
        try {
            cardElement.value.unmount();
        } catch {
            //
        }
        cardElement.value = null;
    }
    paymentInitialized.value = false;
    stripeClient.value = null;

    const paymentIntentUrl = new URL('/start-growth/payment-intent', window.location.origin);
    const trimmed = couponForIntent !== undefined ? String(couponForIntent).trim() : '';
    if (trimmed !== '') {
        paymentIntentUrl.searchParams.set('coupon_code', trimmed);
    }

    const response = await fetch(paymentIntentUrl.toString(), {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
        },
    });

    if (! response.ok) {
        const data = await response.json().catch(() => ({}));
        const msg = data.message ?? props.translations.payment_init_error;
        if (response.status === 422) {
            couponError.value = msg;
            appliedDiscountPercent.value = null;
            if (trimmed !== '') {
                await initializePayment('', { skipClearCouponError: true });
            } else {
                paymentLoading.value = false;
            }

            return;
        }
        paymentError.value = msg;
        paymentLoading.value = false;

        return;
    }

    const data = await response.json();

    paymentIntentId.value = data.paymentIntentId;
    clientSecret.value = data.clientSecret;
    amountDisplay.value = formatAmount(data.amountCents, data.currency);
    appliedDiscountPercent.value =
        typeof data.discountPercent === 'number' ? data.discountPercent : null;

    await loadStripeLibrary();

    stripeClient.value = (window as any).Stripe(data.publishableKey);
    const elements = stripeClient.value.elements();
    cardElement.value = elements.create('card', {
        hidePostalCode: true,
        style: {
            base: {
                color: '#f5f7fa',
                fontSize: '16px',
                fontFamily: 'Inter, sans-serif',
                iconColor: '#25f4ee',
                '::placeholder': {
                    color: 'rgba(245, 247, 250, 0.55)',
                },
            },
            invalid: {
                color: '#ff6b81',
                iconColor: '#ff6b81',
            },
        },
    });

    paymentInitialized.value = true;
    await nextTick();
    cardElement.value.mount('#stripe-card-element');
    paymentLoading.value = false;
}

async function applyCouponCode(): Promise<void> {
    await initializePayment(couponCodeInput.value);
}

async function persistAnalysisRequest(finalPaymentIntentId: string): Promise<void> {
    const response = await fetch('/start-growth', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify({
            ...form.data(),
            payment_intent_id: finalPaymentIntentId,
            'cf-turnstile-response': turnstileToken.value || undefined,
        }),
    });

    if (response.status === 422) {
        const data = await response.json();
        form.setError(data.errors ?? {});
        showValidationFailedMessage.value = true;
        paymentLoading.value = false;

        return;
    }

    if (! response.ok) {
        const data = await response.json().catch(() => ({}));
        paymentError.value = data.message ?? props.translations.payment_confirm_error;
        paymentLoading.value = false;

        return;
    }

    const data = await response.json();
    thankYouUrl.value = data.thankYouUrl;
    window.location.href = thankYouUrl.value;
}

async function submit(): Promise<void> {
    await submitPayment();
}

function validateRequiredFields(): boolean {
    const errors: Record<string, string[]> = {};
    const email = String(form.email ?? '').trim();
    const aspiringNiche = String(form.aspiring_niche ?? '').trim();
    if (! email) {
        errors.email = [props.translations.validation_failed_message];
    }
    if (! aspiringNiche) {
        errors.aspiring_niche = [props.translations.validation_failed_message];
    }
    if (Object.keys(errors).length > 0) {
        form.setError(errors);
        showValidationFailedMessage.value = true;
        return false;
    }
    return true;
}

async function submitPayment(): Promise<void> {
    form.clearErrors();
    paymentError.value = '';
    showValidationFailedMessage.value = false;
    paymentLoading.value = true;

    if (! validateRequiredFields()) {
        paymentLoading.value = false;
        return;
    }

    if (! stripeClient.value || ! cardElement.value || ! clientSecret.value) {
        paymentError.value = props.translations.payment_confirm_error;
        paymentLoading.value = false;

        return;
    }

    const result = await stripeClient.value.confirmCardPayment(clientSecret.value, {
        payment_method: {
            card: cardElement.value,
            billing_details: {
                email: form.email,
            },
        },
    });

    const status = result.paymentIntent?.status;
    const success = status === 'succeeded' || status === 'processing';
    if (result.error || ! success) {
        const stripeErrorCode = result.error?.code;
        const stripeDeclineCode = result.error?.decline_code;

        if (stripeDeclineCode === 'insufficient_funds' || stripeErrorCode === 'insufficient_funds') {
            paymentError.value = props.translations.payment_insufficient_funds_error;
        } else if (
            stripeErrorCode === 'card_declined'
            || stripeDeclineCode === 'card_declined'
            || stripeDeclineCode === 'do_not_honor'
        ) {
            paymentError.value = props.translations.payment_declined_error;
        } else {
            paymentError.value = props.translations.payment_confirm_error;
        }
        paymentLoading.value = false;

        return;
    }

    await persistAnalysisRequest(result.paymentIntent.id);
}

onMounted(async () => {
    if (props.turnstileSiteKey) {
        registerTurnstileCallbacks();
        await loadTurnstileScript();
        turnstileReady.value = true;
    }
    await initializePayment('');
});
</script>

<template>
    <Head :title="translations.title" />

    <PublicLayout>
        <div class="start-growth-main">
            <v-container class="start-growth-container" max-width="1200">
                <v-row class="ga-6" align="stretch">
                    <v-col cols="12" md="7" order="1" order-md="2">
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
                                                name="email"
                                                :label="translations.email_label"
                                                type="email"
                                                autocomplete="email"
                                                :placeholder="translations.email_placeholder"
                                                :error-messages="form.errors.email"
                                                required
                                                persistent-hint
                                                :hint="translations.email_hint"
                                                data-test="start-growth-email"
                                            />
                                        </v-col>

                                        <v-col cols="12" sm="6">
                                            <v-text-field
                                                v-model="form.tiktok_username"
                                                name="tiktok_username"
                                                :label="translations.tiktok_username_label"
                                                :placeholder="translations.tiktok_username_placeholder"
                                                :error-messages="form.errors.tiktok_username"
                                                data-test="start-growth-tiktok"
                                            />
                                        </v-col>

                                        <v-col cols="12" sm="6">
                                            <v-text-field
                                                v-model="form.aspiring_niche"
                                                name="aspiring_niche"
                                                :label="translations.aspiring_niche_label"
                                                :placeholder="translations.aspiring_niche_placeholder"
                                                :error-messages="form.errors.aspiring_niche"
                                                required
                                            />
                                        </v-col>

                                        <v-col cols="12">
                                            <v-textarea
                                                v-model="form.bio"
                                                name="bio"
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
                                                name="video_url_1"
                                                :label="translations.video_url_1_label"
                                                type="url"
                                                :placeholder="translations.video_url_placeholder"
                                                :error-messages="form.errors.video_url_1"
                                            />
                                        </v-col>

                                        <v-col cols="12" sm="4">
                                            <v-text-field
                                                v-model="form.video_url_2"
                                                name="video_url_2"
                                                :label="translations.video_url_2_label"
                                                type="url"
                                                :placeholder="translations.video_url_placeholder"
                                                :error-messages="form.errors.video_url_2"
                                            />
                                        </v-col>

                                        <v-col cols="12" sm="4">
                                            <v-text-field
                                                v-model="form.video_url_3"
                                                name="video_url_3"
                                                :label="translations.video_url_3_label"
                                                type="url"
                                                :placeholder="translations.video_url_placeholder"
                                                :error-messages="form.errors.video_url_3"
                                            />
                                        </v-col>

                                        <v-col cols="12">
                                            <v-textarea
                                                v-model="form.notes"
                                                name="notes"
                                                :label="translations.notes_label"
                                                :placeholder="translations.notes_placeholder"
                                                :error-messages="form.errors.notes"
                                                rows="3"
                                                auto-grow
                                            />
                                        </v-col>

                                        <v-col cols="12">
                                            <v-row dense align="end">
                                                <v-col cols="12" sm="8">
                                                    <v-text-field
                                                        v-model="couponCodeInput"
                                                        name="coupon_code"
                                                        :label="translations.coupon_code_label"
                                                        variant="outlined"
                                                        density="comfortable"
                                                        data-test="start-growth-coupon-code"
                                                        hide-details="auto"
                                                        @update:model-value="couponError = ''"
                                                    />
                                                </v-col>
                                                <v-col cols="12" sm="4">
                                                    <v-btn
                                                        color="secondary"
                                                        variant="tonal"
                                                        block
                                                        :loading="paymentLoading"
                                                        data-test="start-growth-coupon-apply"
                                                        @click="applyCouponCode"
                                                    >
                                                        {{ translations.coupon_apply_cta }}
                                                    </v-btn>
                                                </v-col>
                                            </v-row>
                                            <v-alert
                                                v-if="couponError"
                                                type="warning"
                                                variant="tonal"
                                                class="mt-2"
                                                density="compact"
                                                data-test="start-growth-coupon-error"
                                            >
                                                {{ couponError }}
                                            </v-alert>
                                            <v-alert
                                                v-if="appliedDiscountPercent !== null"
                                                type="success"
                                                variant="tonal"
                                                class="mt-2"
                                                density="compact"
                                                data-test="start-growth-coupon-applied"
                                            >
                                                {{
                                                    translations.coupon_applied_hint.replace(
                                                        ':percent',
                                                        String(appliedDiscountPercent),
                                                    )
                                                }}
                                            </v-alert>
                                        </v-col>

                                        <v-col v-if="paymentInitialized" cols="12">
                                            <label class="payment-card-label mb-2 d-block">
                                                {{ translations.payment_card_label }}
                                            </label>
                                            <div id="stripe-card-element" class="stripe-card-element" />
                                            <p class="payment-amount-highlight mt-3 text-right">
                                                {{ amountDisplay }}
                                            </p>
                                        </v-col>

                                        <v-col
                                            v-if="turnstileReady && turnstileSiteKey"
                                            cols="12"
                                            class="d-flex justify-center"
                                        >
                                            <div
                                                class="cf-turnstile"
                                                :data-sitekey="turnstileSiteKey"
                                                data-callback="onTurnstileSuccess"
                                                data-expired-callback="onTurnstileExpired"
                                                data-test="turnstile-widget"
                                            />
                                        </v-col>
                                    </v-row>

                                    <v-btn
                                        data-test="start-growth-submit"
                                        type="submit"
                                        color="primary"
                                        variant="flat"
                                        size="x-large"
                                        block
                                        :loading="paymentLoading"
                                        :disabled="paymentLoading"
                                        class="mt-4 cta-button"
                                    >
                                        {{
                                            paymentLoading
                                                ? translations.payment_processing_cta
                                                : translations.payment_submit_cta
                                        }}
                                    </v-btn>

                                    <v-alert
                                        v-if="showValidationFailedMessage || (form.errors && Object.keys(form.errors).length > 0)"
                                        type="warning"
                                        variant="tonal"
                                        class="mt-4"
                                        data-test="validation-failed-message"
                                    >
                                        {{ translations.validation_failed_message }}
                                    </v-alert>
                                    <v-alert
                                        v-if="paymentError"
                                        type="error"
                                        variant="tonal"
                                        class="mt-4"
                                    >
                                        {{ paymentError }}
                                    </v-alert>
                                </v-form>

                            </v-card-text>
                        </v-card>
                    </v-col>

                    <v-col cols="12" md="5" order="2" order-md="1">
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
                </v-row>
            </v-container>
        </div>
    </PublicLayout>
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

.payment-card-label {
    font-size: 0.95rem;
    color: rgba(255, 255, 255, 0.88);
}

.payment-amount-highlight {
    margin: 0;
    font-size: 1.45rem;
    font-weight: 700;
    color: #25f4ee;
    padding-top: 14px;
    padding-bottom: 10px;
}

.stripe-card-element {
    padding: 14px;
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    background: rgba(0, 0, 0, 0.2);
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
