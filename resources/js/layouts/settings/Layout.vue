<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { show } from '@/routes/two-factor';
import { edit as editPassword } from '@/routes/user-password';
import { type NavItem } from '@/types';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profile',
        href: editProfile(),
        icon: 'mdi-account-circle-outline',
    },
    {
        title: 'Password',
        href: editPassword(),
        icon: 'mdi-lock-outline',
    },
    {
        title: 'Two-Factor Auth',
        href: show(),
        icon: 'mdi-shield-key-outline',
    },
];

const { isCurrentUrl } = useCurrentUrl();
</script>

<template>
    <v-container class="py-4">
        <v-row>
            <v-col cols="12" md="3">
                <v-list
                    nav
                    density="comfortable"
                    aria-label="Settings"
                    class="goviral-settings-nav"
                >
                    <v-list-item
                        v-for="item in sidebarNavItems"
                        :key="item.title"
                        color="primary"
                        :class="{ 'goviral-settings-nav-active': isCurrentUrl(item.href) }"
                    >
                        <Link
                            :href="item.href"
                            class="goviral-settings-nav-link"
                            :data-test="`settings-nav-${item.title.toLowerCase().replace(/\\s+/g, '-')}`"
                        >
                            <v-icon
                                v-if="item.icon"
                                :icon="item.icon"
                                size="20"
                                class="mr-3"
                            />
                            {{ item.title }}
                        </Link>
                    </v-list-item>
                </v-list>
            </v-col>

            <v-col cols="12" md="9">
                <section>
                    <slot />
                </section>
            </v-col>
        </v-row>
    </v-container>
</template>

<style scoped>
.goviral-settings-nav {
    background: transparent;
}

.goviral-settings-nav-link {
    display: inline-flex;
    width: 100%;
    color: rgba(255, 255, 255, 0.78);
    text-decoration: none;
}

.goviral-settings-nav-active .goviral-settings-nav-link {
    color: rgb(var(--v-theme-primary));
    font-weight: 600;
}
</style>
