<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Discount coupons', href: '/core/discount-coupons' },
    { title: 'Create', href: '/core/discount-coupons/create' },
];

const form = useForm({
    code: '',
    value: 10,
    expiration_type: 'never' as 'never' | 'days' | 'uses',
    expiration_days: 30,
    max_uses_input: 100,
});

function submit(): void {
    form.post('/core/discount-coupons', { preserveScroll: true });
}

const expirationItems = [
    { title: 'Never expires', value: 'never' },
    { title: 'After X days', value: 'days' },
    { title: 'After X uses', value: 'uses' },
];
</script>

<template>
    <Head title="Create coupon" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <h1 class="text-h5 font-weight-bold text-primary mb-6">
            Create coupon
        </h1>

        <v-card class="goviral-card pa-6" elevation="4" max-width="640" rounded="xl">
            <v-form data-test="discount-coupon-create-form" @submit.prevent="submit">
                <v-text-field
                    id="discount-coupon-code"
                    v-model="form.code"
                    label="Code"
                    name="code"
                    variant="outlined"
                    density="comfortable"
                    :error-messages="form.errors.code"
                    data-test="discount-coupon-code"
                />
                <v-text-field
                    id="discount-coupon-value"
                    v-model.number="form.value"
                    label="Discount % (0–100)"
                    name="value"
                    type="number"
                    min="0"
                    max="100"
                    variant="outlined"
                    class="mt-2"
                    :error-messages="form.errors.value"
                    data-test="discount-coupon-value"
                />
                <v-select
                    v-model="form.expiration_type"
                    :items="expirationItems"
                    item-title="title"
                    item-value="value"
                    label="Expiration"
                    variant="outlined"
                    class="mt-2"
                    :error-messages="form.errors.expiration_type"
                    data-test="discount-coupon-expiration-type"
                />
                <v-text-field
                    v-if="form.expiration_type === 'days'"
                    v-model.number="form.expiration_days"
                    label="Valid for (days from now)"
                    type="number"
                    min="1"
                    variant="outlined"
                    class="mt-2"
                    :error-messages="form.errors.expiration_days"
                    data-test="discount-coupon-expiration-days"
                />
                <v-text-field
                    v-if="form.expiration_type === 'uses'"
                    v-model.number="form.max_uses_input"
                    label="Maximum uses"
                    type="number"
                    min="1"
                    variant="outlined"
                    class="mt-2"
                    :error-messages="form.errors.max_uses_input"
                    data-test="discount-coupon-max-uses"
                />
                <v-btn
                    type="submit"
                    color="primary"
                    variant="flat"
                    class="mt-6"
                    :loading="form.processing"
                    data-test="discount-coupon-submit"
                >
                    Save
                </v-btn>
            </v-form>
        </v-card>
    </AppLayout>
</template>

<style scoped>
.goviral-card {
    border: 1px solid rgba(254, 44, 85, 0.25);
    background: rgba(18, 18, 18, 0.75);
}
</style>
