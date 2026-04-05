<script setup>
import { PlusIcon, FunnelIcon, PencilSquareIcon, TrashIcon, ArrowLeftIcon, ArrowRightIcon } from '@heroicons/vue/24/outline';
import { reactive, ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({ entries: Object, rendicontoVoci: Array, filters: Object });
const page = usePage();
const showDeleteConfirmModal = ref(false);
const pendingDeleteId = ref(null);
const showConfirmDestroyModal = ref(false);
const entryIdToDestroy = ref(null);

const form = reactive({
    from: props.filters?.from ?? '',
    to: props.filters?.to ?? '',
    rendiconto_code: props.filters?.rendiconto_code ?? '',
});

watch(() => page.props.flash, (flash) => {
    if (flash?.type === 'confirm_anno_precedente_required' && flash?.destroy_entry_id) {
        entryIdToDestroy.value = flash.destroy_entry_id;
        showConfirmDestroyModal.value = true;
    }
}, { immediate: true });

const search = () => router.get(route('prima-nota.index'), form);

function requestDelete(id) {
    pendingDeleteId.value = id;
    showDeleteConfirmModal.value = true;
}

function closeDeleteConfirmModal() {
    showDeleteConfirmModal.value = false;
    pendingDeleteId.value = null;
}

function confirmDeleteProceed() {
    const id = pendingDeleteId.value;
    showDeleteConfirmModal.value = false;
    pendingDeleteId.value = null;
    if (id) router.delete(route('prima-nota.destroy', id));
}

function confirmDestroyProceed() {
    const id = entryIdToDestroy.value;
    showConfirmDestroyModal.value = false;
    entryIdToDestroy.value = null;
    if (id) router.delete(route('prima-nota.destroy', id) + '?confirm_anno_precedente=1');
}

function closeConfirmDestroyModal() {
    showConfirmDestroyModal.value = false;
    entryIdToDestroy.value = null;
}
</script>

<template>
    <AppLayout title="Prima nota">
        <Head title="Prima nota" />
        <template #header>
            <div class="flex justify-between items-center flex-wrap gap-2">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Prima nota</h2>
                <div class="flex gap-2">
                    <Link :href="route('prima-nota.giroconto.create')">
                        <PrimaryButton type="button" class="!bg-gray-600 hover:!bg-gray-700">Giroconto</PrimaryButton>
                    </Link>
                    <Link :href="route('prima-nota.create')">
                        <PrimaryButton><PlusIcon class="size-4 me-2" aria-hidden="true" />Nuovo movimento</PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="search" class="mb-4 flex flex-wrap gap-2 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Da</label>
                        <input v-model="form.from" type="date" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">A</label>
                        <input v-model="form.to" type="date" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Voce</label>
                        <select v-model="form.rendiconto_code" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                            <option value="">Tutte</option>
                            <option v-for="v in rendicontoVoci" :key="v.code" :value="v.code">{{ v.ministerial_code ? v.ministerial_code + ' – ' + v.name : v.name }}</option>
                        </select>
                    </div>
                    <PrimaryButton type="submit"><FunnelIcon class="size-4 me-2" aria-hidden="true" />Filtra</PrimaryButton>
                </form>

                <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Data</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Conto</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Voce</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Descrizione</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Importo</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-20">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="e in entries.data" :key="e.id">
                                <td class="px-4 py-2">{{ e.date ? new Date(e.date).toLocaleDateString('it-IT') : '' }}</td>
                                <td class="px-4 py-2">{{ e.conto?.name ?? '—' }}</td>
                                <td class="px-4 py-2">{{ e.rendiconto_label || (e.rendiconto_code ?? '') }}</td>
                                <td class="px-4 py-2">{{ e.description || '—' }}</td>
                                <td class="px-4 py-2 text-right" :class="Number(e.amount) >= 0 ? 'text-green-600' : 'text-red-600'">€ {{ Number(e.amount).toFixed(2) }}</td>
                                <td class="px-4 py-2 text-right flex gap-2 justify-end">
                                    <Link :href="route('prima-nota.edit', e.id)" class="inline-flex items-center gap-1 text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                        <PencilSquareIcon class="size-4" aria-hidden="true" />Modifica
                                    </Link>
                                    <button type="button" @click="requestDelete(e.id)" class="inline-flex items-center gap-1 text-sm text-red-600 dark:text-red-400 hover:underline">
                                        <TrashIcon class="size-4" aria-hidden="true" />Elimina
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-if="!entries.data?.length" class="px-4 py-8 text-center text-gray-500">Nessun movimento.</p>
                    <div v-if="entries.prev_page_url || entries.next_page_url" class="px-4 py-2 border-t flex justify-between">
                        <Link v-if="entries.prev_page_url" :href="entries.prev_page_url" class="inline-flex items-center gap-1 text-indigo-600 dark:text-indigo-400 hover:underline"><ArrowLeftIcon class="size-4" aria-hidden="true" />Indietro</Link>
                        <span v-else></span>
                        <Link v-if="entries.next_page_url" :href="entries.next_page_url" class="inline-flex items-center gap-1 text-indigo-600 dark:text-indigo-400 hover:underline">Avanti<ArrowRightIcon class="size-4" aria-hidden="true" /></Link>
                    </div>
                </div>
            </div>
        </div>

        <Teleport to="body">
            <div v-if="showDeleteConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6" role="dialog" aria-modal="true" aria-labelledby="delete-confirm-title">
                    <h3 id="delete-confirm-title" class="text-lg font-medium text-gray-900 dark:text-gray-100">Eliminare il movimento?</h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Questa azione non può essere annullata.</p>
                    <div class="mt-4 flex justify-end gap-2">
                        <SecondaryButton @click="closeDeleteConfirmModal">Annulla</SecondaryButton>
                        <PrimaryButton class="!bg-red-600 hover:!bg-red-700" @click="confirmDeleteProceed">Elimina</PrimaryButton>
                    </div>
                </div>
            </div>
            <div v-if="showConfirmDestroyModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
                    <p class="text-gray-700 dark:text-gray-300">{{ page.props.flash?.message || 'Operazioni su anni precedenti possono alterare i rendiconti già generati. Vuoi procedere?' }}</p>
                    <div class="mt-4 flex justify-end gap-2">
                        <SecondaryButton @click="closeConfirmDestroyModal">Annulla</SecondaryButton>
                        <PrimaryButton @click="confirmDestroyProceed">Procedi</PrimaryButton>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
