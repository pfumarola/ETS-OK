<script setup>
import { PlusIcon, PencilSquareIcon, TrashIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({ conti: Array });

function tipoLabel(type) {
    const labels = { cassa: 'Cassa', banca: 'Banca', altro: 'Altro' };
    return labels[type] ?? type;
}

function canDelete(c) {
    return (c.movimenti_count ?? 0) === 0 && (c.incassi_count ?? 0) === 0;
}
function deleteConto(conto) {
    if (!canDelete(conto)) return;
    if (!confirm('Eliminare il conto "' + conto.name + '"?')) return;
    router.delete(route('conti.destroy', conto.id));
}
</script>

<template>
    <AppLayout title="Conti tesoreria">
        <Head title="Conti tesoreria" />
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Conti tesoreria</h2>
                <Link :href="route('conti.create')">
                    <PrimaryButton><PlusIcon class="size-4 me-2" aria-hidden="true" />Nuovo conto</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nome</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Codice</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tipo</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Ordine</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Stato</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Movimenti</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="c in conti" :key="c.id">
                                <td class="px-4 py-2 font-medium text-gray-900 dark:text-gray-100">{{ c.name }}</td>
                                <td class="px-4 py-2">{{ c.code || '—' }}</td>
                                <td class="px-4 py-2">{{ tipoLabel(c.type) }}</td>
                                <td class="px-4 py-2">{{ c.ordine ?? 0 }}</td>
                                <td class="px-4 py-2">{{ c.attivo ? 'Attivo' : 'Non attivo' }}</td>
                                <td class="px-4 py-2">{{ c.movimenti_count ?? 0 }}</td>
                                <td class="px-4 py-2 text-right">
                                    <Link :href="route('conti.edit', c.id)" class="inline-flex items-center gap-1 text-sm text-indigo-600 dark:text-indigo-400 hover:underline me-2"><PencilSquareIcon class="size-4" aria-hidden="true" />Modifica</Link>
                                    <button
                                        v-if="canDelete(c)"
                                        type="button"
                                        class="inline-flex items-center gap-1 text-sm text-red-600 dark:text-red-400 hover:underline"
                                        @click="deleteConto(c)"
                                    >
                                        <TrashIcon class="size-4" aria-hidden="true" />Elimina
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-if="!conti?.length" class="px-4 py-8 text-center text-gray-500">Nessun conto. <Link :href="route('conti.create')" class="text-indigo-600 dark:text-indigo-400 hover:underline">Aggiungi il primo conto</Link>.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
