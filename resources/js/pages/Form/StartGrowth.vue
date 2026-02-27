<script setup lang="ts">
defineProps<{
    locale: string;
}>();
</script>

<template>
    <main class="min-h-screen bg-background text-foreground">
        <section class="mx-auto flex w-full max-w-4xl flex-col gap-3 px-6 py-20">
            <p class="text-sm uppercase tracking-[0.2em] text-primary">
                {{ locale.toUpperCase() }}
            </p>

            <h1 class="text-4xl font-semibold leading-tight">
                Start My Growth
            </h1>

            <p class="max-w-2xl text-muted-foreground">
                This page is ready for the form implementation in the next task.
            </p>
        </section>
    </main>
</template>
