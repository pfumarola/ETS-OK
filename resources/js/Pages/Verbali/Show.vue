<script setup>
import { computed } from 'vue';
import { PencilSquareIcon, ArrowLeftIcon, TrashIcon, PaperClipIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    verbale: Object,
    canEdit: { type: Boolean, default: false },
    canEditAttachments: { type: Boolean, default: false },
    uploadMaxFileSizeHuman: { type: String, default: '10 MB' },
});

const verbaleId = computed(() => {
    const id = props.verbale?.id;
    const n = Number(id);
    return Number.isInteger(n) && n > 0 ? n : null;
});

const editUrl = computed(() => verbaleId.value != null && props.canEdit ? route('verbali.edit', { verbale: verbaleId.value }) : null);

const deleteVerbale = () => {
    if (!confirm('Eliminare questo verbale?')) return;
    if (verbaleId.value == null) return;
    router.delete(route('verbali.destroy', { verbale: verbaleId.value }), { onSuccess: () => router.visit(route('verbali.index')) });
};

const confermaVerbale = () => {
    if (!confirm('Confermare il verbale? Non sarà più modificabile.')) return;
    if (verbaleId.value == null) return;
    router.post(route('verbali.conferma', { verbale: verbaleId.value }));
};

const formatSize = (bytes) => {
    if (bytes == null || bytes < 1024) return (bytes ?? 0) + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
};

const removeAttachment = (attachment) => {
    if (!confirm('Rimuovere questo allegato?')) return;
    router.delete(route('verbali.attachments.destroy', { verbale: verbaleId.value, attachment: attachment.id }));
};

const showAttachmentsSection = () =>
    props.canEditAttachments || (props.verbale.attachments && props.verbale.attachments.length > 0);

const page = usePage();
const attachmentError = computed(() => {
    const err = page.props.errors?.file;
    if (!err) return null;
    return Array.isArray(err) ? err[0] : err;
});
</script>

<template>
    <AppLayout title="Verbale">
        <Head :title="verbale.titolo || 'Verbale'" />
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ verbale.titolo || 'Verbale' }}</h2>
                <div class="flex items-center gap-2 flex-wrap">
                    <Link :href="route('verbali.index')" class="inline-flex items-center gap-1 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-300"><ArrowLeftIcon class="size-4" aria-hidden="true" />Elenco</Link>
                    <a v-if="canEdit && verbaleId" :href="route('verbali.pdf', { verbale: verbaleId })" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-medium text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm">
                        <ArrowDownTrayIcon class="size-4" aria-hidden="true" />Scarica PDF
                    </a>
                    <Link v-if="editUrl" :href="editUrl">
                        <SecondaryButton><PencilSquareIcon class="size-4 me-2" aria-hidden="true" />Modifica</SecondaryButton>
                    </Link>
                    <DangerButton v-if="canEdit" type="button" @click="deleteVerbale"><TrashIcon class="size-4 me-2" aria-hidden="true" />Elimina</DangerButton>
                    <PrimaryButton v-if="canEdit" type="button" @click="confermaVerbale">Conferma verbale</PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-6 max-w-3xl mx-auto sm:px-6 space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2 text-sm">
                    <div><dt class="text-gray-500 dark:text-gray-400">Stato</dt><dd><span class="px-2 py-0.5 rounded text-xs font-medium" :class="verbale.stato === 'confermato' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400'">{{ verbale.stato === 'confermato' ? 'Confermato' : 'Bozza' }}</span></dd></div>
                    <dt class="text-gray-500 dark:text-gray-400">Data</dt>
                    <dd>{{ verbale.data ? new Date(verbale.data).toLocaleDateString('it-IT') : '—' }}</dd>
                    <dt class="text-gray-500 dark:text-gray-400">Tipo</dt>
                    <dd>{{ verbale.tipo_label || verbale.tipo }}</dd>
                    <dt class="text-gray-500 dark:text-gray-400">Titolo</dt>
                    <dd class="font-medium">{{ verbale.titolo }}</dd>
                    <dt v-if="verbale.numero != null" class="text-gray-500 dark:text-gray-400">Numero</dt>
                    <dd v-if="verbale.numero != null">n. {{ verbale.numero }}{{ verbale.anno ? ` / ${verbale.anno}` : '' }}</dd>
                </dl>
                <div v-if="verbale.contenuto" class="pt-4 border-t border-gray-200 dark:border-gray-600">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Contenuto</h3>
                    <div class="prose prose-sm dark:prose-invert max-w-none text-gray-800 dark:text-gray-200" v-html="verbale.contenuto"></div>
                </div>
                <p v-else class="pt-4 border-t border-gray-200 dark:border-gray-600 text-gray-500 dark:text-gray-400 text-sm">Nessun contenuto.</p>
            </div>

            <!-- Allegati -->
            <div v-if="showAttachmentsSection()" class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                    <PaperClipIcon class="size-5" aria-hidden="true" />
                    Allegati
                </h3>
                <p v-if="canEditAttachments" class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Puoi scaricare il verbale in PDF (pulsante «Scarica PDF» in alto), firmarlo sul computer e ricaricarlo qui come allegato.
                </p>
                <ul v-if="verbale.attachments?.length" class="space-y-2 mb-4">
                    <li v-for="a in verbale.attachments" :key="a.id" class="flex items-center justify-between gap-2 py-2 border-b border-gray-200 dark:border-gray-600 last:border-0">
                        <a :href="route('attachments.show', a.id)" target="_blank" class="inline-flex items-center gap-2 text-indigo-600 dark:text-indigo-400 hover:underline flex-1 min-w-0">
                            <ArrowDownTrayIcon class="size-4 shrink-0" aria-hidden="true" />
                            <span class="truncate">{{ a.original_name }}</span>
                            <span class="text-sm text-gray-500 dark:text-gray-400 shrink-0">{{ formatSize(a.size) }}</span>
                        </a>
                        <button v-if="canEditAttachments" type="button" @click="removeAttachment(a)" class="text-red-600 hover:underline shrink-0 flex items-center gap-1" title="Elimina">
                            <TrashIcon class="size-4" aria-hidden="true" />Elimina
                        </button>
                    </li>
                </ul>
                <p v-else class="text-sm text-gray-500 dark:text-gray-400 mb-4">Nessun allegato.</p>
                <p v-if="attachmentError" class="text-sm text-red-600 dark:text-red-400 mb-2">{{ attachmentError }}</p>
                <form v-if="canEditAttachments" :action="route('verbali.attachments.store', { verbale: verbale.id })" method="post" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
                    <input type="hidden" name="_token" :value="$page.props.csrf_token" />
                    <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.xls,.xlsx" required class="text-sm text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-gray-100 file:text-gray-700 dark:file:bg-gray-700 dark:file:text-gray-300" />
                    <PrimaryButton type="submit">Carica allegato</PrimaryButton>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Max {{ uploadMaxFileSizeHuman }}. Formati: PDF, immagini, Word, Excel.</span>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
