<script setup>
import { ArrowLeftIcon, PencilSquareIcon, PaperAirplaneIcon, DocumentArrowDownIcon } from '@heroicons/vue/24/outline';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    convocazione: Object,
    recipientSummary: Object,
});

const sendForm = useForm({});

function sendConvocazione() {
    if (!confirm('Confermi l\'invio della convocazione ai destinatari previsti?')) return;
    sendForm.post(route('convocazioni.send', { convocazione: props.convocazione.id }));
}
</script>

<template>
    <AppLayout title="Dettaglio convocazione">
        <Head title="Dettaglio convocazione" />
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Convocazione</h2>
                <Link :href="route('convocazioni.index')" class="inline-flex items-center gap-1 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-300"><ArrowLeftIcon class="size-4" aria-hidden="true" />Elenco</Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 space-y-4">
                    <div class="flex flex-wrap justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ convocazione.titolo || convocazione.tipo_label }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ convocazione.tipo_label }} - {{ convocazione.scheduled_at ? new Date(convocazione.scheduled_at).toLocaleString('it-IT') : '—' }}</p>
                        </div>
                        <div class="text-sm">
                            <p><span class="font-medium">Stato:</span> {{ convocazione.stato_label }}</p>
                            <p><span class="font-medium">Luogo:</span> {{ convocazione.luogo }}</p>
                        </div>
                    </div>

                    <div class="rounded border border-gray-200 dark:border-gray-700 p-3 text-sm">
                        <p class="font-medium mb-1">Destinatari previsti</p>
                        <p>Totali: {{ recipientSummary.total }} - Con email: {{ recipientSummary.with_email }} - Senza email: {{ recipientSummary.without_email }}</p>
                    </div>

                    <div>
                        <p class="font-medium text-sm mb-1">Ordine del giorno</p>
                        <div class="prose prose-sm dark:prose-invert max-w-none border border-gray-200 dark:border-gray-700 rounded p-3 bg-gray-50 dark:bg-gray-900/30" v-html="convocazione.ordine_del_giorno" />
                    </div>
                    <div>
                        <p class="font-medium text-sm mb-1">Anteprima testo email</p>
                        <div class="prose prose-sm dark:prose-invert max-w-none border border-gray-200 dark:border-gray-700 rounded p-3 bg-gray-50 dark:bg-gray-900/30" v-html="convocazione.testo_email" />
                    </div>

                    <div class="flex flex-wrap gap-2 pt-2">
                        <Link v-if="convocazione.in_bozza" :href="route('convocazioni.edit', { convocazione: convocazione.id })">
                            <PrimaryButton type="button"><PencilSquareIcon class="size-4 me-2" aria-hidden="true" />Modifica</PrimaryButton>
                        </Link>
                        <PrimaryButton v-if="convocazione.in_bozza" type="button" :disabled="sendForm.processing || recipientSummary.with_email === 0" @click="sendConvocazione">
                            <PaperAirplaneIcon class="size-4 me-2" aria-hidden="true" />Invia convocazione
                        </PrimaryButton>
                        <a :href="route('convocazioni.pdf', { convocazione: convocazione.id })" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700">
                            <DocumentArrowDownIcon class="size-4 me-2" aria-hidden="true" />Scarica PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
