<script setup>
import { PlusIcon, PencilSquareIcon, TrashIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({ templates: Array });

function elimina(t) {
    if (!confirm(`Eliminare il template "${t.nome}"?`)) return;
    router.delete(route('document-templates.destroy', { document_template: t.id }), { preserveScroll: true });
}
</script>

<template>
    <AppLayout title="Template documenti">
        <Head title="Template documenti" />
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Template documenti</h2>
                <div class="flex gap-2">
                    <Link :href="route('documents.index')" class="inline-flex items-center gap-1 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-300"><ArrowLeftIcon class="size-4" aria-hidden="true" />Documenti</Link>
                    <Link :href="route('document-templates.create')">
                        <PrimaryButton><PlusIcon class="size-4 me-2" aria-hidden="true" />Nuovo template</PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nome</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="t in templates" :key="t.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-2 font-medium text-gray-900 dark:text-gray-100">{{ t.nome }}</td>
                                <td class="px-4 py-2 text-right">
                                    <Link :href="route('document-templates.edit', { document_template: t.id })" class="inline-flex items-center gap-1 text-sm text-indigo-600 dark:text-indigo-400 hover:underline me-3">
                                        <PencilSquareIcon class="size-4" aria-hidden="true" />Modifica
                                    </Link>
                                    <button
                                        type="button"
                                        @click="elimina(t)"
                                        class="inline-flex items-center gap-1 text-sm text-red-600 dark:text-red-400 hover:underline"
                                    >
                                        <TrashIcon class="size-4" aria-hidden="true" />Elimina
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-if="!templates?.length" class="px-4 py-8 text-center text-gray-500">Nessun template. Creane uno per usarlo nella redazione dei documenti.</div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
