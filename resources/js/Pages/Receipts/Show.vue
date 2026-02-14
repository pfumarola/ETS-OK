<script setup>
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({ receipt: Object });
</script>

<template>
    <AppLayout title="Ricevuta">
        <Head :title="'Ricevuta ' + receipt.number" />
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Ricevuta {{ receipt.number }}</h2>
                <div class="flex gap-2">
                    <a v-if="receipt.file_path" :href="route('receipts.download', receipt.id)" target="_blank" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white">Scarica PDF</a>
                    <Link :href="route('receipts.index')" class="inline-flex items-center gap-1 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-300"><ArrowLeftIcon class="size-4" aria-hidden="true" />Elenco ricevute</Link>
                </div>
            </div>
        </template>

        <div class="py-6 max-w-4xl mx-auto sm:px-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><dt class="text-sm text-gray-500 dark:text-gray-400">Socio</dt><dd><Link v-if="receipt.member" :href="route('members.show', receipt.member.id)" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ receipt.member.cognome }} {{ receipt.member.nome }}</Link><template v-else>—</template></dd></div>
                    <div><dt class="text-sm text-gray-500 dark:text-gray-400">Data emissione</dt><dd>{{ receipt.issued_at ? new Date(receipt.issued_at).toLocaleDateString('it-IT') : '—' }}</dd></div>
                    <div><dt class="text-sm text-gray-500 dark:text-gray-400">Tipo</dt><dd>{{ receipt.type }}</dd></div>
                </dl>
            </div>
        </div>
    </AppLayout>
</template>
