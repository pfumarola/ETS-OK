<script setup>
import { PlusIcon, FunnelIcon, PencilSquareIcon } from '@heroicons/vue/24/outline';
import { reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({ entries: Object, rendicontoVoci: Array, filters: Object });

const form = reactive({
    from: props.filters?.from ?? '',
    to: props.filters?.to ?? '',
    rendiconto_code: props.filters?.rendiconto_code ?? '',
});

const search = () => router.get(route('prima-nota.index'), form);
</script>

<template>
    <AppLayout title="Prima nota">
        <Head title="Prima nota" />
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Prima nota</h2>
                <Link :href="route('prima-nota.create')">
                    <PrimaryButton><PlusIcon class="size-4 me-2" aria-hidden="true" />Nuovo movimento</PrimaryButton>
                </Link>
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
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Voce</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Descrizione</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Importo</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-20">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="e in entries.data" :key="e.id">
                                <td class="px-4 py-2">{{ e.date ? new Date(e.date).toLocaleDateString('it-IT') : '' }}</td>
                                <td class="px-4 py-2">{{ e.rendiconto_label || (e.rendiconto_code ?? '') }}</td>
                                <td class="px-4 py-2">{{ e.description || '—' }}</td>
                                <td class="px-4 py-2 text-right" :class="Number(e.amount) >= 0 ? 'text-green-600' : 'text-red-600'">€ {{ Number(e.amount).toFixed(2) }}</td>
                                <td class="px-4 py-2 text-right">
                                    <Link :href="route('prima-nota.edit', e.id)" class="inline-flex items-center gap-1 text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                        <PencilSquareIcon class="size-4" aria-hidden="true" />Modifica
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-if="!entries.data?.length" class="px-4 py-8 text-center text-gray-500">Nessun movimento.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
