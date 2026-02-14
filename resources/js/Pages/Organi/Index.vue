<script setup>
import { ref } from 'vue';
import { PlusIcon, PencilSquareIcon, TrashIcon, FunnelIcon } from '@heroicons/vue/24/outline';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({ organi: Object });

function elimina(o) {
    if (!confirm(`Eliminare l'organo "${o.nome}"?`)) return;
    router.delete(route('organi.destroy', o.id), { preserveScroll: true });
}
</script>

<template>
    <AppLayout title="Organi">
        <Head title="Organi" />
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Organi</h2>
                <Link :href="route('organi.create')">
                    <PrimaryButton><PlusIcon class="size-4 me-2" aria-hidden="true" />Nuovo organo</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nome</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Durata</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="o in organi.data" :key="o.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-2 font-medium">
                                    <Link :href="route('organi.show', o.id)" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ o.nome }}</Link>
                                </td>
                                <td class="px-4 py-2">{{ o.durata_mesi ? o.durata_mesi + ' mesi' : '—' }}</td>
                                <td class="px-4 py-2 text-right">
                                    <Link :href="route('organi.edit', o.id)" class="inline-flex items-center gap-1 text-sm text-indigo-600 dark:text-indigo-400 hover:underline me-3">
                                        <PencilSquareIcon class="size-4" aria-hidden="true" />Modifica
                                    </Link>
                                    <button
                                        type="button"
                                        @click="elimina(o)"
                                        class="inline-flex items-center gap-1 text-sm text-red-600 dark:text-red-400 hover:underline"
                                    >
                                        <TrashIcon class="size-4" aria-hidden="true" />Elimina
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-if="!organi.data?.length" class="px-4 py-8 text-center text-gray-500">Nessun organo.</div>
                    <div v-if="organi.prev_page_url || organi.next_page_url" class="px-4 py-2 border-t flex justify-between">
                        <Link v-if="organi.prev_page_url" :href="organi.prev_page_url" class="text-sm text-indigo-600 dark:text-indigo-400">Precedente</Link>
                        <span v-else></span>
                        <Link v-if="organi.next_page_url" :href="organi.next_page_url" class="text-sm text-indigo-600 dark:text-indigo-400">Successiva</Link>
                        <span v-else></span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
