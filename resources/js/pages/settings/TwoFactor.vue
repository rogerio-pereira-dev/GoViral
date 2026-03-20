<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { onUnmounted, ref } from 'vue';
import TwoFactorRecoveryCodes from '@/components/TwoFactorRecoveryCodes.vue';
import TwoFactorSetupModal from '@/components/TwoFactorSetupModal.vue';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { disable, enable, show } from '@/routes/two-factor';
import type { BreadcrumbItem } from '@/types';

type Props = {
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
};

withDefaults(defineProps<Props>(), {
    requiresConfirmation: false,
    twoFactorEnabled: false,
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Two-Factor Authentication',
        href: show.url(),
    },
];

const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth();
const showSetupModal = ref<boolean>(false);

onUnmounted(() => {
    clearTwoFactorAuthData();
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Two-Factor Authentication" />

        <SettingsLayout>
            <v-card
                class="goviral-card"
                elevation="6"
            >
                <v-card-title class="text-h6">
                    Two-Factor Authentication
                </v-card-title>
                <v-card-subtitle class="mb-4">
                    Manage your two-factor authentication settings.
                </v-card-subtitle>

                <v-card-text>
                    <div v-if="!twoFactorEnabled">
                        <v-chip
                            label
                            color="error"
                            variant="outlined"
                            class="mb-4"
                        >
                            Disabled
                        </v-chip>

                        <p class="text-body-2 text-medium-emphasis mb-4">
                            When you enable two-factor authentication, you will be
                            prompted for a secure pin during login. This pin can be
                            retrieved from a TOTP-supported application on your
                            phone.
                        </p>

                        <div>
                            <v-btn
                                v-if="hasSetupData"
                                color="primary"
                                @click="showSetupModal = true"
                            >
                                Continue setup
                            </v-btn>
                            <Form
                                v-else
                                v-bind="enable.form()"
                                @success="showSetupModal = true"
                                #default="{ processing }"
                            >
                                <v-btn
                                    type="submit"
                                    color="primary"
                                    :loading="processing"
                                    :disabled="processing"
                                >
                                    Enable 2FA
                                </v-btn>
                            </Form>
                        </div>
                    </div>

                    <div v-else>
                        <v-chip
                            label
                            color="success"
                            variant="outlined"
                            class="mb-4"
                        >
                            Enabled
                        </v-chip>

                        <p class="text-body-2 text-medium-emphasis mb-4">
                            With two-factor authentication enabled, you will be
                            prompted for a secure, random pin during login, which
                            you can retrieve from the TOTP-supported application on
                            your phone.
                        </p>

                        <TwoFactorRecoveryCodes />

                        <div class="mt-4">
                            <Form v-bind="disable.form()" #default="{ processing }">
                                <v-btn
                                    type="submit"
                                    color="error"
                                    variant="outlined"
                                    :loading="processing"
                                    :disabled="processing"
                                >
                                    Disable 2FA
                                </v-btn>
                            </Form>
                        </div>
                    </div>

                    <TwoFactorSetupModal
                        v-model:isOpen="showSetupModal"
                        :requiresConfirmation="requiresConfirmation"
                        :twoFactorEnabled="twoFactorEnabled"
                    />
                </v-card-text>
            </v-card>
        </SettingsLayout>
    </AppLayout>
</template>

<style scoped>
.goviral-card {
    background: rgba(18, 18, 18, 0.96);
    border-radius: 18px;
    border: 1px solid rgba(254, 44, 85, 0.3);
}
</style>
