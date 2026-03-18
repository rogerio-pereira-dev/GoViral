<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

type Row = {
    id: string;
    code: string;
    value: number;
    expires_at: string | null;
    max_uses: number | null;
    times_used: number;
    created_at: string;
};

const props = defineProps<{
    coupons: Row[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Discount coupons', href: '/core/discount-coupons' },
];

const deleteTarget = ref<Row | null>(null);
const deleteDialog = ref(false);

function openDelete(row: Row): void {
    deleteTarget.value = row;
    deleteDialog.value = true;
}

function closeDelete(): void {
    deleteDialog.value = false;
    deleteTarget.value = null;
}

function confirmDelete(): void {
    if (! deleteTarget.value) {
        return;
    }
    router.delete(`/core/discount-coupons/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onFinish: () => closeDelete(),
    });
}

const headers = [
    { title: 'Code', key: 'code' },
    { title: 'Value %', key: 'value' },
    { title: 'Expires', key: 'expires_at' },
    { title: 'Max uses', key: 'max_uses' },
    { title: 'Times used', key: 'times_used' },
    { title: 'Actions', key: 'actions', sortable: false },
];
</script>

<template>
    <Head title="Discount coupons" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <v-row class="mb-4" align="center">
            <v-col cols="12" md="6">
                <h1 class="text-h5 font-weight-bold text-primary">
                    Discount coupons
                </h1>
            </v-col>
            <v-col cols="12" md="6" class="text-md-right">
                <Link
                    href="/core/discount-coupons/create"
                    class="text-decoration-none"
                    data-test="discount-coupons-create-link"
                >
                    <v-btn color="primary" variant="flat">
                        Create coupon
                    </v-btn>
                </Link>
            </v-col>
        </v-row>

        <v-card class="goviral-card" elevation="4" rounded="xl">
            <v-data-table
                :headers="headers"
                :items="props.coupons"
                class="discount-coupons-table"
                data-test="discount-coupons-table"
            >
                <template #item.expires_at="{ item }">
                    {{ item.expires_at ?? '—' }}
                </template>
                <template #item.max_uses="{ item }">
                    {{ item.max_uses ?? '—' }}
                </template>
                <template #item.actions="{ item }">
                    <Link
                        :href="`/core/discount-coupons/${item.id}/edit`"
                        class="mr-2 text-primary text-decoration-none"
                        :data-test="`discount-coupon-edit-${item.id}`"
                    >
                        Edit
                    </Link>
                    <v-btn
                        variant="text"
                        color="error"
                        size="small"
                        :data-test="`discount-coupon-delete-${item.id}`"
                        @click="openDelete(item)"
                    >
                        Delete
                    </v-btn>
                </template>
            </v-data-table>
        </v-card>

        <v-dialog
            v-model="deleteDialog"
            max-width="420"
            data-test="discount-coupon-delete-dialog"
        >
            <v-card rounded="xl">
                <v-card-title>Delete coupon</v-card-title>
                <v-card-text>
                    Soft-delete coupon <strong>{{ deleteTarget?.code }}</strong>? It will no longer apply to new checkouts.
                </v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn variant="text" data-test="discount-coupon-delete-cancel" @click="closeDelete">
                        Cancel
                    </v-btn>
                    <v-btn color="error" variant="flat" data-test="discount-coupon-delete-confirm" @click="confirmDelete">
                        Confirm
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </AppLayout>
</template>

<style scoped>
.goviral-card {
    border: 1px solid rgba(254, 44, 85, 0.25);
    background: rgba(18, 18, 18, 0.75);
}
</style>
