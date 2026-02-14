<script setup>
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    rendiconto: Object,
    anno: Number,
});

const anni = Array.from({ length: 30 }, (_, i) => new Date().getFullYear() - 15 + i);

const changeAnno = (event) => {
    const y = event.target.value;
    if (y) router.get(route('reports.rendiconto-cassa'), { anno: y }, { preserveState: true });
};

const exportPdfUrl = () => {
    return route('reports.rendiconto-cassa.export-pdf') + '?anno=' + props.anno;
};

const formatEuro = (n) => '€ ' + Number(n).toLocaleString('it-IT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
</script>

<template>
    <AppLayout title="Rendiconto per cassa">
        <Head title="Rendiconto economico per cassa" />
        <template #header>
            <div class="flex flex-wrap justify-between items-center gap-4">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Rendiconto economico per cassa</h2>
                <div class="flex items-center gap-3">
                    <label class="text-sm text-gray-600 dark:text-gray-400">Anno</label>
                    <select
                        :value="anno"
                        @change="changeAnno"
                        class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                    >
                        <option v-for="y in anni" :key="y" :value="y">{{ y }}</option>
                    </select>
                    <a :href="exportPdfUrl()" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-gray-700">Esporta PDF</a>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Totale entrate</div>
                        <div class="text-xl font-semibold text-green-600">{{ formatEuro(rendiconto?.totale_entrate ?? 0) }}</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Totale uscite</div>
                        <div class="text-xl font-semibold text-red-600">{{ formatEuro(rendiconto?.totale_uscite ?? 0) }}</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Risultato per cassa</div>
                        <div class="text-xl font-semibold" :class="Number(rendiconto?.risultato_per_cassa ?? 0) >= 0 ? 'text-green-600' : 'text-red-600'">
                            {{ formatEuro(rendiconto?.risultato_per_cassa ?? 0) }}
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-600">
                        <span class="font-medium text-gray-900 dark:text-gray-100">Modello D – Anno {{ anno }}</span>
                    </div>
                    <div v-for="sezione in rendiconto?.sezioni" :key="sezione.sezione" class="border-b border-gray-200 dark:border-gray-600 last:border-b-0">
                        <div class="px-4 py-2 bg-gray-50 dark:bg-gray-700/50 font-medium text-gray-700 dark:text-gray-300">
                            Sezione {{ sezione.sezione }}
                        </div>
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-24">Codice</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Voce</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-32">Importo</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="voce in sezione.voci" :key="voce.codice_voce">
                                    <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">{{ voce.codice_voce }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ voce.descrizione }}</td>
                                    <td class="px-4 py-2 text-sm text-right" :class="voce.tipo === 'entrata' ? 'text-green-600' : 'text-red-600'">
                                        {{ formatEuro(voce.importo) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="px-4 py-2 flex justify-end gap-6 text-sm border-t border-gray-100 dark:border-gray-600">
                            <span v-if="sezione.totale_entrate > 0" class="text-green-600">Totale entrate: {{ formatEuro(sezione.totale_entrate) }}</span>
                            <span v-if="sezione.totale_uscite > 0" class="text-red-600">Totale uscite: {{ formatEuro(sezione.totale_uscite) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
