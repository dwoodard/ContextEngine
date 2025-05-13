<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

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

const agents = ref<{ name: string; handle: string }[]>([]);

onMounted(() => {
    const page = usePage<{ agents: { name: string; handle: string }[] }>();
    agents.value = page.props.agents;
});
</script>

<template>
    <Head title="Agents" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto p-4">
            <h1 class="text-2xl font-bold mb-4">Agents</h1>
            <ul class="list-disc pl-5">
                <li v-for="agent in agents" :key="agent.handle">
                    <Link :href="`/agent/${agent.handle}`" class="text-blue-500 hover:underline">
                        {{ agent.name }}
                    </Link>
                </li>
            </ul>
        </div>
    </AppLayout>
</template>
