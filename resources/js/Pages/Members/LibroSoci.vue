<script setup>
import { ArrowLeftIcon, ArrowRightIcon } from '@heroicons/vue/24/outline';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    members: Object,
});
</script>

<template>
    <AppLayout title="Libro soci">
        <Head title="Libro soci" />
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Libro soci</h2>
                <Link :href="route('members.index')" class="inline-flex items-center gap-1 text-indigo-600 dark:text-indigo-400 hover:underline"><ArrowLeftIcon class="size-4" aria-hidden="true" />Elenco soci</Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Elenco dei soci ammessi (con data di iscrizione), ordinato per data di ammissione. Sono esclusi i cessati, dimessi, esclusi e morosi.</p>
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">N.</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Cognome</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nome</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Data iscrizione</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Codice fiscale</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="(m, index) in members.data" :key="m.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">{{ index + 1 }}</td>
                                <td class="px-4 py-2">
                                    <Link :href="route('members.show', m.id)" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ m.cognome }}</Link>
                                </td>
                                <td class="px-4 py-2">{{ m.nome }}</td>
                                <td class="px-4 py-2 text-sm">{{ m.data_iscrizione ? new Date(m.data_iscrizione).toLocaleDateString('it-IT') : '—' }}</td>
                                <td class="px-4 py-2 text-sm font-mono">{{ m.codice_fiscale || '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-if="!members.data?.length" class="px-4 py-8 text-center text-gray-500">Nessun socio nel libro.</p>
                    <div v-if="members.prev_page_url || members.next_page_url" class="px-4 py-2 border-t flex justify-between">
                        <Link v-if="members.prev_page_url" :href="members.prev_page_url" class="inline-flex items-center gap-1 text-indigo-600 dark:text-indigo-400 hover:underline"><ArrowLeftIcon class="size-4" aria-hidden="true" />Indietro</Link>
                        <span v-else></span>
                        <Link v-if="members.next_page_url" :href="members.next_page_url" class="inline-flex items-center gap-1 text-indigo-600 dark:text-indigo-400 hover:underline">Avanti<ArrowRightIcon class="size-4" aria-hidden="true" /></Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
