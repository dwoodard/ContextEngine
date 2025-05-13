<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Agents',
        href: '/agents',
    },
];

const agent = ref<{ name: string; handle: string; description?: string }>(
    usePage<{ agent: { name: string; handle: string; description?: string } }>().props.agent
);
</script>

<template>
    <Head :title="`Agent: ${agent.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto p-4">
            <h1 class="text-2xl font-bold mb-4">{{ agent.name }}</h1>
            <p class="mb-4">Handle: {{ agent.handle }}</p>
            <p v-if="agent.description">Description: {{ agent.description }}</p>
        </div>
    </AppLayout>
</template>
