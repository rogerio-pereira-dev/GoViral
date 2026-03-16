<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import type { TwoFactorConfigContent } from '@/types';
import { store } from '@/routes/two-factor/login';

const authConfigContent = computed<TwoFactorConfigContent>(() => {
    if (showRecoveryInput.value) {
        return {
            title: 'Recovery Code',
            description:
                'Please confirm access to your account by entering one of your emergency recovery codes.',
            buttonText: 'login using an authentication code',
        };
    }

    return {
        title: 'Authentication Code',
        description:
            'Enter the authentication code provided by your authenticator application.',
        buttonText: 'login using a recovery code',
    };
});

const showRecoveryInput = ref<boolean>(false);

const toggleRecoveryMode = (clearErrors: () => void): void => {
    showRecoveryInput.value = !showRecoveryInput.value;
    clearErrors();
    code.value = '';
};

const code = ref<string>('');
</script>

<template>
    <AuthLayout
        :title="authConfigContent.title"
        :description="authConfigContent.description"
    >
        <Head title="Two-Factor Authentication" />

        <div class="space-y-6">
            <template v-if="!showRecoveryInput">
                <Form
                    v-bind="store.form()"
                    reset-on-error
                    @error="code = ''"
                    #default="{ errors, processing, clearErrors }"
                >
                    <div class="d-flex flex-column gap-4">
                        <div class="text-center">
                            <v-text-field
                                name="code"
                                type="text"
                                label="Authentication code"
                                placeholder="123456"
                                inputmode="numeric"
                                maxlength="6"
                                autofocus
                                :disabled="processing"
                                variant="outlined"
                                density="comfortable"
                                color="primary"
                                v-model="code"
                                :error-messages="errors.code"
                            />
                            <InputError :message="errors.code" />
                        </div>

                        <v-btn
                            type="submit"
                            block
                            color="primary"
                            :loading="processing"
                            :disabled="processing"
                        >
                            Continue
                        </v-btn>

                        <div class="text-center text-body-2 text-medium-emphasis">
                            <span>or you can </span>
                            <button
                                type="button"
                                class="text-primary text-decoration-underline ms-1"
                                @click="() => toggleRecoveryMode(clearErrors)"
                            >
                                {{ authConfigContent.buttonText }}
                            </button>
                        </div>
                    </div>
                </Form>
            </template>

            <template v-else>
                <Form
                    v-bind="store.form()"
                    reset-on-error
                    #default="{ errors, processing, clearErrors }"
                >
                    <div class="d-flex flex-column gap-4">
                        <div>
                            <v-text-field
                                name="recovery_code"
                                type="text"
                                label="Recovery code"
                                placeholder="Enter recovery code"
                                :autofocus="showRecoveryInput"
                                required
                                variant="outlined"
                                density="comfortable"
                                color="primary"
                                :error-messages="errors.recovery_code"
                            />
                            <InputError :message="errors.recovery_code" />
                        </div>

                        <v-btn
                            type="submit"
                            block
                            color="primary"
                            :loading="processing"
                            :disabled="processing"
                        >
                            Continue
                        </v-btn>

                        <div class="text-center text-body-2 text-medium-emphasis">
                            <span>or you can </span>
                            <button
                                type="button"
                                class="text-primary text-decoration-underline ms-1"
                                @click="() => toggleRecoveryMode(clearErrors)"
                            >
                                {{ authConfigContent.buttonText }}
                            </button>
                        </div>
                    </div>
                </Form>
            </template>
        </div>
    </AuthLayout>
</template>
