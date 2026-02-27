<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineProps<{
    locale: string;
}>();
</script>

<template>
    <Head title="Start My Growth" />

    <main class="min-h-screen bg-background text-foreground">
        <section class="mx-auto flex w-full max-w-4xl flex-col gap-8 px-6 py-16">
            <p class="text-xs uppercase tracking-[0.2em] text-primary">
                {{ locale.toUpperCase() }}
            </p>

            <header class="space-y-2">
                <h1 class="text-4xl font-semibold leading-tight">
                    Start My Growth
                </h1>
                <p class="max-w-2xl text-muted-foreground">
                    Share your profile details below so we can generate your growth report.
                </p>
            </header>

            <form class="grid gap-6 rounded-xl border border-border/70 bg-card/70 p-6 shadow-sm">
                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        name="email"
                        type="email"
                        autocomplete="email"
                        placeholder="you@example.com"
                        required
                    />
                    <p class="text-sm text-muted-foreground">
                        We deliver your analysis report by email. Use a valid email address to receive it.
                    </p>
                </div>

                <div class="grid gap-2">
                    <Label for="tiktok_username">TikTok Username</Label>
                    <Input
                        id="tiktok_username"
                        name="tiktok_username"
                        type="text"
                        placeholder="@yourusername"
                        required
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="bio">Current Bio</Label>
                    <textarea
                        id="bio"
                        name="bio"
                        class="border-input focus-visible:border-ring focus-visible:ring-ring/50 dark:bg-input/30 aria-invalid:border-destructive aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 min-h-28 w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                        placeholder="Paste your current TikTok bio"
                        required
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="aspiring_niche">Aspiring Niche</Label>
                    <Input
                        id="aspiring_niche"
                        name="aspiring_niche"
                        type="text"
                        placeholder="Fitness, Beauty, Cooking, etc."
                        required
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="video_url_1">Video URL 1</Label>
                    <Input
                        id="video_url_1"
                        name="video_url_1"
                        type="url"
                        placeholder="https://www.tiktok.com/@username/video/..."
                        required
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="video_url_2">Video URL 2</Label>
                    <Input
                        id="video_url_2"
                        name="video_url_2"
                        type="url"
                        placeholder="https://www.tiktok.com/@username/video/..."
                        required
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="video_url_3">Video URL 3</Label>
                    <Input
                        id="video_url_3"
                        name="video_url_3"
                        type="url"
                        placeholder="https://www.tiktok.com/@username/video/..."
                        required
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="notes">Notes (Optional)</Label>
                    <textarea
                        id="notes"
                        name="notes"
                        class="border-input focus-visible:border-ring focus-visible:ring-ring/50 dark:bg-input/30 aria-invalid:border-destructive aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 min-h-24 w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                        placeholder="Any extra context you want us to consider"
                    />
                </div>

                <Button
                    type="submit"
                    class="w-full md:w-auto"
                >
                    Start My Growth
                </Button>
            </form>
        </section>
    </main>
</template>
