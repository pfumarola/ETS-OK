<script setup>
import { PlusIcon, EyeIcon, PencilSquareIcon, ArrowLeftIcon, ArrowRightIcon } from '@heroicons/vue/24/outline';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({ documents: Object });
</script>

<template>
    <AppLayout title="Documenti">
        <Head title="Documenti" />
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Documenti</h2>
                <div class="flex items-center gap-2">
                    <Link :href="route('documents.create')">
                        <PrimaryButton><PlusIcon class="size-4 me-2" aria-hidden="true" />Nuovo documento</PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Data</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Titolo</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="d in documents.data" :key="d.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-2">{{ d.data ? new Date(d.data).toLocaleDateString('it-IT') : '—' }}</td>
                                <td class="px-4 py-2">{{ d.titolo || '—' }}</td>
                                <td class="px-4 py-2 text-right flex gap-2 justify-end">
                                    <Link :href="route('documents.show', { document: d.id })" class="inline-flex items-center gap-1 text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                        <EyeIcon class="size-4" aria-hidden="true" />Visualizza
                                    </Link>
                                    <Link :href="route('documents.edit', { document: d.id })" class="inline-flex items-center gap-1 text-sm text-gray-600 dark:text-gray-400 hover:underline">
                                        <PencilSquareIcon class="size-4" aria-hidden="true" />Modifica
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-if="!documents.data?.length" class="px-4 py-8 text-center text-gray-500">Nessun documento.</p>
                    <div v-if="documents.prev_page_url || documents.next_page_url" class="px-4 py-2 border-t flex justify-between">
                        <Link v-if="documents.prev_page_url" :href="documents.prev_page_url" class="inline-flex items-center gap-1 text-indigo-600 dark:text-indigo-400 hover:underline"><ArrowLeftIcon class="size-4" aria-hidden="true" />Indietro</Link>
                        <span v-else></span>
                        <Link v-if="documents.next_page_url" :href="documents.next_page_url" class="inline-flex items-center gap-1 text-indigo-600 dark:text-indigo-400 hover:underline">Avanti<ArrowRightIcon class="size-4" aria-hidden="true" /></Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
