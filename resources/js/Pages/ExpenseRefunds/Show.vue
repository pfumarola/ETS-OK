<script setup>
import { ArrowDownTrayIcon, ArrowLeftIcon, PaperClipIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    refund: Object,
    canApprove: { type: Boolean, default: false },
    uploadMaxFileSizeHuman: { type: String, default: '10 MB' },
});

const showItemForm = ref(false);
const newItem = ref({ description: '', amount: '' });

const form = useForm({
    items: (props.refund.refund_items || []).map(i => ({ description: i.description ?? '', amount: String(i.amount ?? 0) })),
});

const addItem = () => {
    if (!newItem.value.amount || Number(newItem.value.amount) <= 0) return;
    form.items.push({ description: newItem.value.description, amount: newItem.value.amount });
    newItem.value = { description: '', amount: '' };
    showItemForm.value = false;
};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

const saveItems = () => {
    form.put(route('expense-refunds.update', props.refund.id));
};

const printReceipt = () => {
    window.location.href = route('expense-refunds.print', props.refund.id);
};

const approva = () => {
    if (confirm('Approvare questa richiesta di rimborso?')) {
        router.post(route('expense-refunds.approva', props.refund.id));
    }
};

const canEditItems = () => props.refund.status === 'bozza' || props.refund.status === 'richiesta';

const statusLabel = (status) => {
    const labels = { bozza: 'Bozza', richiesta: 'Richiesta', approvato: 'Approvato', stampata: 'Stampata', contabilizzata: 'Contabilizzata' };
    return labels[status] || status;
};

const statusClass = (status) => {
    const classes = {
        bozza: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        richiesta: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
        approvato: 'bg-sky-100 text-sky-800 dark:bg-sky-900/30 dark:text-sky-400',
        stampata: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        contabilizzata: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    };
    return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};

const canDownloadReceipt = () =>
    props.refund.receipt || props.refund.receipt_id
    || ((props.refund.status === 'bozza' || props.refund.status === 'approvato' || props.refund.status === 'contabilizzata') && props.refund.refund_items?.length);

const canEditAttachments = () => props.refund.status === 'bozza' || props.refund.status === 'richiesta';

const formatSize = (bytes) => {
    if (bytes == null || bytes < 1024) return (bytes ?? 0) + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
};

const removeAttachment = (attachment) => {
    if (!confirm('Rimuovere questo allegato?')) return;
    router.delete(route('expense-refunds.attachments.destroy', [props.refund.id, attachment.id]));
};

const showAttachmentsSection = () => canEditAttachments() || (props.refund.attachments && props.refund.attachments.length > 0);

const page = usePage();
const attachmentError = computed(() => {
    const err = page.props.errors?.file;
    if (!err) return null;
    return Array.isArray(err) ? err[0] : err;
});
</script>

<template>
    <AppLayout title="Rimborso spese">
        <Head title="Rimborso spese" />
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Rimborso #{{ refund.id }} – {{ refund.member ? refund.member.cognome + ' ' + refund.member.nome : '' }}</h2>
                <Link :href="route('expense-refunds.index')" class="inline-flex items-center gap-1 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-300"><ArrowLeftIcon class="size-4" aria-hidden="true" />Elenco rimborsi</Link>
            </div>
        </template>

        <div class="py-6 max-w-4xl mx-auto sm:px-6 space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <dl class="grid grid-cols-2 gap-4">
                    <div><dt class="text-sm text-gray-500 dark:text-gray-400">Data</dt><dd>{{ refund.refund_date ? new Date(refund.refund_date).toLocaleDateString('it-IT') : '' }}</dd></div>
                    <div><dt class="text-sm text-gray-500 dark:text-gray-400">Stato</dt><dd><span class="px-2 py-0.5 rounded text-xs" :class="statusClass(refund.status)">{{ statusLabel(refund.status) }}</span></dd></div>
                    <div><dt class="text-sm text-gray-500 dark:text-gray-400">Totale</dt><dd class="font-medium">€ {{ Number(refund.total).toFixed(2) }}</dd></div>
                </dl>
            </div>

            <!-- Stato richiesta: in attesa di approvazione -->
            <div v-if="refund.status === 'richiesta'" class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4 flex flex-wrap items-center gap-3">
                <span class="text-amber-800 dark:text-amber-200">In attesa di approvazione.</span>
                <form v-if="canApprove" @submit.prevent="approva" class="inline">
                    <PrimaryButton type="submit">Approva</PrimaryButton>
                </form>
            </div>
            <!-- Stato approvato: puoi stampare -->
            <div v-if="refund.status === 'approvato'" class="bg-sky-50 dark:bg-sky-900/20 border border-sky-200 dark:border-sky-800 rounded-lg p-4">
                <span class="text-sky-800 dark:text-sky-200">Approvato. Puoi stampare la ricevuta.</span>
            </div>

            <!-- Step 2: Voci -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Voci di rimborso</h3>
                <template v-if="canEditItems()">
                    <form @submit.prevent="saveItems" class="space-y-4">
                        <div v-for="(item, index) in form.items" :key="index" class="flex gap-2 items-center">
                            <input v-model="item.description" placeholder="Descrizione" class="flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm" />
                            <input v-model="item.amount" type="number" step="0.01" min="0" placeholder="Importo" class="w-24 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm" />
                            <button type="button" @click="removeItem(index)" class="text-red-600 hover:underline">Elimina</button>
                        </div>
                        <div v-if="form.items.length === 0" class="text-gray-500 text-sm">Nessuna voce. Aggiungi almeno una voce per procedere.</div>
                        <div v-if="showItemForm" class="flex gap-2 items-center pt-2 border-t border-gray-200 dark:border-gray-600">
                            <input v-model="newItem.description" placeholder="Descrizione" class="flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm" />
                            <input v-model="newItem.amount" type="number" step="0.01" min="0" placeholder="Importo (€)" class="w-28 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm" />
                            <PrimaryButton type="button" @click="addItem">Aggiungi</PrimaryButton>
                            <button type="button" @click="showItemForm = false" class="text-sm text-gray-500 hover:underline">Annulla</button>
                        </div>
                        <div v-else class="flex gap-2 pt-2">
                            <button type="button" @click="showItemForm = true" class="inline-flex items-center gap-1 text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                <PlusIcon class="size-4" aria-hidden="true" />Aggiungi voce
                            </button>
                        </div>
                        <PrimaryButton type="submit" class="mt-4" :disabled="form.processing || form.items.length === 0">Salva voci</PrimaryButton>
                    </form>
                </template>
                <table v-else class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700"><tr><th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Descrizione</th><th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Importo</th></tr></thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="i in refund.refund_items" :key="i.id"><td class="px-4 py-2">{{ i.description || '—' }}</td><td class="px-4 py-2 text-right">€ {{ Number(i.amount).toFixed(2) }}</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Allegati -->
            <div v-if="showAttachmentsSection()" class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                    <PaperClipIcon class="size-5" aria-hidden="true" />
                    Allegati
                </h3>
                <ul v-if="refund.attachments?.length" class="space-y-2 mb-4">
                    <li v-for="a in refund.attachments" :key="a.id" class="flex items-center justify-between gap-2 py-2 border-b border-gray-200 dark:border-gray-600 last:border-0">
                        <a :href="route('attachments.show', a.id)" target="_blank" class="inline-flex items-center gap-2 text-indigo-600 dark:text-indigo-400 hover:underline flex-1 min-w-0">
                            <ArrowDownTrayIcon class="size-4 shrink-0" aria-hidden="true" />
                            <span class="truncate">{{ a.original_name }}</span>
                            <span class="text-sm text-gray-500 dark:text-gray-400 shrink-0">{{ formatSize(a.size) }}</span>
                        </a>
                        <button v-if="canEditAttachments()" type="button" @click="removeAttachment(a)" class="text-red-600 hover:underline shrink-0 flex items-center gap-1" title="Elimina">
                            <TrashIcon class="size-4" aria-hidden="true" />Elimina
                        </button>
                    </li>
                </ul>
                <p v-else class="text-sm text-gray-500 dark:text-gray-400 mb-4">Nessun allegato.</p>
                <p v-if="attachmentError" class="text-sm text-red-600 dark:text-red-400 mb-2">{{ attachmentError }}</p>
                <form v-if="canEditAttachments()" :action="route('expense-refunds.attachments.store', { expense_refund: refund.id })" method="post" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
                    <input type="hidden" name="_token" :value="$page.props.csrf_token" />
                    <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.xls,.xlsx" required class="text-sm text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-gray-100 file:text-gray-700 dark:file:bg-gray-700 dark:file:text-gray-300" />
                    <PrimaryButton type="submit">Carica allegato</PrimaryButton>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Max {{ uploadMaxFileSizeHuman }}. Formati: PDF, immagini, Word, Excel.</span>
                </form>
            </div>

            <!-- Scarica ricevuta: sempre disponibile quando esiste o è generabile -->
            <div v-if="canDownloadReceipt()" class="flex gap-2 items-center flex-wrap">
                <a :href="route('expense-refunds.print', refund.id)" target="_blank" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-md font-semibold text-xs uppercase">Scarica ricevuta</a>
            </div>
            <div v-if="refund.status === 'contabilizzata'" class="text-sm text-green-600">Rimborso contabilizzato.</div>
        </div>
    </AppLayout>
</template>
