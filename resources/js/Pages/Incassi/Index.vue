<script setup>
import { PlusIcon, FunnelIcon, EyeIcon, ArrowLeftIcon, ArrowRightIcon } from '@heroicons/vue/24/outline';
import { reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({ incassi: Object, filters: Object });

const form = reactive({
    member_id: props.filters?.member_id ?? '',
    from: props.filters?.from ?? '',
    to: props.filters?.to ?? '',
    type: props.filters?.type ?? 'all',
});

const search = () => router.get(route('incassi.index'), form);
</script>

<template>
    <AppLayout title="Incassi">
        <Head title="Incassi" />
        <template #header>
            <div class="flex flex-wrap justify-between items-center gap-2">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Incassi</h2>
                <div class="flex gap-2">
                    <Link :href="route('incassi.create')">
                        <PrimaryButton><PlusIcon class="size-4 me-2" aria-hidden="true" />Nuovo incasso</PrimaryButton>
                    </Link>
                    <Link :href="route('incassi.create', { type: 'donazione' })" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700">
                        Nuova donazione
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="search" class="mb-4 flex flex-wrap gap-2 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo</label>
                        <select v-model="form.type" name="type" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                            <option value="all">Tutti</option>
                            <option value="quota">Quota</option>
                            <option value="donazione">Donazione</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Da data</label>
                        <input v-model="form.from" type="date" name="from" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">A data</label>
                        <input v-model="form.to" type="date" name="to" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm" />
                    </div>
                    <PrimaryButton type="submit"><FunnelIcon class="size-4 me-2" aria-hidden="true" />Filtra</PrimaryButton>
                </form>

                <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Data</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tipo</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Socio / Donatore</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Importo</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Metodo</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Ricevuta</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="i in incassi.data" :key="i.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-2">{{ i.paid_at ? new Date(i.paid_at).toLocaleDateString('it-IT') : '' }}</td>
                                <td class="px-4 py-2">
                                    <span v-if="i.type === 'quota'" class="inline-flex px-2 py-0.5 text-xs font-medium rounded bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">Quota</span>
                                    <span v-else-if="i.type === 'donazione'" class="inline-flex px-2 py-0.5 text-xs font-medium rounded bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">Donazione</span>
                                    <span v-else>—</span>
                                </td>
                                <td class="px-4 py-2">{{ i.member ? i.member.cognome + ' ' + i.member.nome : (i.donor_name || (i.type === 'donazione' ? 'Anonimo' : '—')) }}</td>
                                <td class="px-4 py-2">€ {{ Number(i.amount).toFixed(2) }}</td>
                                <td class="px-4 py-2">{{ i.payment_method?.name ?? '—' }}</td>
                                <td class="px-4 py-2">{{ i.receipt ? i.receipt.number : '—' }}</td>
                                <td class="px-4 py-2 text-right">
                                    <Link :href="route('incassi.show', i.id)" class="inline-flex items-center gap-1 text-sm text-indigo-600 dark:text-indigo-400 hover:underline"><EyeIcon class="size-4" aria-hidden="true" />Dettaglio</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-if="!incassi.data?.length" class="px-4 py-8 text-center text-gray-500">Nessun incasso.</p>
                    <div v-if="incassi.prev_page_url || incassi.next_page_url" class="px-4 py-2 border-t flex justify-between">
                        <Link v-if="incassi.prev_page_url" :href="incassi.prev_page_url" class="inline-flex items-center gap-1 text-indigo-600 dark:text-indigo-400 hover:underline"><ArrowLeftIcon class="size-4" aria-hidden="true" />Indietro</Link>
                        <span v-else></span>
                        <Link v-if="incassi.next_page_url" :href="incassi.next_page_url" class="inline-flex items-center gap-1 text-indigo-600 dark:text-indigo-400 hover:underline">Avanti<ArrowRightIcon class="size-4" aria-hidden="true" /></Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
