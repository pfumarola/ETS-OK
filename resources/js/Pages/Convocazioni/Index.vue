<script setup>
import { PlusIcon, EyeIcon, PencilSquareIcon, ArrowLeftIcon, ArrowRightIcon } from '@heroicons/vue/24/outline';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    convocazioni: Object,
    filters: Object,
    tipoOptions: Array,
    statoOptions: Array,
});

const currentTipo = props.filters?.tipo ?? '';
const currentStato = props.filters?.stato ?? '';

function applyFilter(next) {
    router.get(route('convocazioni.index'), {
        tipo: (next.tipo ?? currentTipo) || undefined,
        stato: (next.stato ?? currentStato) || undefined,
    }, { preserveState: true });
}
</script>

<template>
    <AppLayout title="Convocazioni">
        <Head title="Convocazioni" />
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Convocazioni</h2>
                <Link :href="route('convocazioni.create')">
                    <PrimaryButton><PlusIcon class="size-4 me-2" aria-hidden="true" />Nuova convocazione</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-4 flex flex-wrap gap-2 items-center">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Tipo:</span>
                    <button type="button" class="rounded-md px-3 py-1.5 text-sm font-medium" :class="[!currentTipo ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']" @click="applyFilter({ tipo: '' })">Tutti</button>
                    <button v-for="opt in tipoOptions" :key="opt.value" type="button" class="rounded-md px-3 py-1.5 text-sm font-medium" :class="[currentTipo === opt.value ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']" @click="applyFilter({ tipo: opt.value })">{{ opt.label }}</button>
                    <span class="ms-2 text-sm font-medium text-gray-700 dark:text-gray-300">Stato:</span>
                    <button type="button" class="rounded-md px-3 py-1.5 text-sm font-medium" :class="[!currentStato ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']" @click="applyFilter({ stato: '' })">Tutti</button>
                    <button v-for="opt in statoOptions" :key="opt.value" type="button" class="rounded-md px-3 py-1.5 text-sm font-medium" :class="[currentStato === opt.value ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']" @click="applyFilter({ stato: opt.value })">{{ opt.label }}</button>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Data</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tipo</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Titolo</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Stato</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="c in convocazioni.data" :key="c.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-2">{{ c.scheduled_at ? new Date(c.scheduled_at).toLocaleString('it-IT') : '—' }}</td>
                                <td class="px-4 py-2">{{ c.tipo_label }}</td>
                                <td class="px-4 py-2">{{ c.titolo || '—' }}</td>
                                <td class="px-4 py-2">{{ c.stato_label }}</td>
                                <td class="px-4 py-2 text-right flex gap-2 justify-end">
                                    <Link :href="route('convocazioni.show', { convocazione: c.id })" class="inline-flex items-center gap-1 text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                        <EyeIcon class="size-4" aria-hidden="true" />Visualizza
                                    </Link>
                                    <Link v-if="c.in_bozza" :href="route('convocazioni.edit', { convocazione: c.id })" class="inline-flex items-center gap-1 text-sm text-gray-600 dark:text-gray-400 hover:underline">
                                        <PencilSquareIcon class="size-4" aria-hidden="true" />Modifica
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-if="!convocazioni.data?.length" class="px-4 py-8 text-center text-gray-500">Nessuna convocazione.</p>
                    <div v-if="convocazioni.prev_page_url || convocazioni.next_page_url" class="px-4 py-2 border-t flex justify-between">
                        <Link v-if="convocazioni.prev_page_url" :href="convocazioni.prev_page_url" class="inline-flex items-center gap-1 text-indigo-600 dark:text-indigo-400 hover:underline"><ArrowLeftIcon class="size-4" aria-hidden="true" />Indietro</Link>
                        <span v-else></span>
                        <Link v-if="convocazioni.next_page_url" :href="convocazioni.next_page_url" class="inline-flex items-center gap-1 text-indigo-600 dark:text-indigo-400 hover:underline">Avanti<ArrowRightIcon class="size-4" aria-hidden="true" /></Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
