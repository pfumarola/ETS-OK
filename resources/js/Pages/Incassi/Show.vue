<script setup>
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({ incasso: Object });
</script>

<template>
    <AppLayout title="Dettaglio incasso">
        <Head title="Dettaglio incasso" />
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Incasso #{{ incasso.id }}</h2>
                <Link :href="route('incassi.index')" class="inline-flex items-center gap-1 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-300"><ArrowLeftIcon class="size-4" aria-hidden="true" />Elenco incassi</Link>
            </div>
        </template>

        <div class="py-6 max-w-4xl mx-auto sm:px-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Tipo</dt>
                        <dd>
                            <span v-if="incasso.type === 'quota'" class="inline-flex px-2 py-0.5 text-xs font-medium rounded bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">Quota</span>
                            <span v-else-if="incasso.type === 'donazione'" class="inline-flex px-2 py-0.5 text-xs font-medium rounded bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">Donazione</span>
                            <span v-else>—</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">{{ incasso.type === 'donazione' ? 'Donatore' : 'Socio' }}</dt>
                        <dd>
                            <Link v-if="incasso.member" :href="route('members.show', incasso.member.id)" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ incasso.member.cognome }} {{ incasso.member.nome }}</Link>
                            <template v-else>{{ incasso.type === 'donazione' ? 'Anonimo' : '—' }}</template>
                        </dd>
                    </div>
                    <div><dt class="text-sm text-gray-500 dark:text-gray-400">Importo</dt><dd class="font-medium">€ {{ Number(incasso.amount).toFixed(2) }}</dd></div>
                    <div><dt class="text-sm text-gray-500 dark:text-gray-400">Data</dt><dd>{{ incasso.paid_at ? new Date(incasso.paid_at).toLocaleDateString('it-IT') : '—' }}</dd></div>
                    <div><dt class="text-sm text-gray-500 dark:text-gray-400">Metodo</dt><dd>{{ incasso.payment_method?.name ?? '—' }}</dd></div>
                    <div v-if="incasso.type === 'quota'"><dt class="text-sm text-gray-500 dark:text-gray-400">Iscrizione</dt><dd>{{ incasso.subscription ? 'Anno ' + incasso.subscription.year + (incasso.subscription.ends_at ? ' (fino al ' + new Date(incasso.subscription.ends_at).toLocaleDateString('it-IT') + ')' : '') : '—' }}</dd></div>
                    <div><dt class="text-sm text-gray-500 dark:text-gray-400">Ricevuta</dt><dd><template v-if="incasso.receipt"><Link :href="route('receipts.show', incasso.receipt.id)" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ incasso.receipt.number }}</Link> <a :href="route('receipts.download', incasso.receipt.id)" target="_blank" class="text-sm text-gray-500 hover:underline">Scarica PDF</a></template><template v-else>—</template></dd></div>
                    <div><dt class="text-sm text-gray-500 dark:text-gray-400">Prima nota</dt><dd><template v-if="incasso.prima_nota_entry">Sì ({{ incasso.prima_nota_entry.rendiconto_label || incasso.prima_nota_entry.rendiconto_code }})</template><template v-else>No</template></dd></div>
                    <div v-if="incasso.description" class="sm:col-span-2"><dt class="text-sm text-gray-500 dark:text-gray-400">Causale</dt><dd>{{ incasso.description }}</dd></div>
                </dl>
            </div>
        </div>
    </AppLayout>
</template>
