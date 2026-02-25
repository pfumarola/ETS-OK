<script setup>
import { FolderIcon, DocumentIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    items: Array,
    breadcrumb: Array,
    currentPath: String,
});

function formatSize(bytes) {
    if (bytes == null || bytes === 0) return '—';
    const units = ['B', 'KB', 'MB', 'GB'];
    let i = 0;
    let n = bytes;
    while (n >= 1024 && i < units.length - 1) {
        n /= 1024;
        i++;
    }
    return n.toFixed(i > 1 ? 2 : 0) + ' ' + units[i];
}

function downloadUrl(item) {
    return route('media.download') + '?path=' + encodeURIComponent(item.path);
}
</script>

<template>
    <AppLayout title="Archivio media">
        <Head title="Media" />
        <template #header>
            <div class="flex flex-col gap-2">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Archivio media</h2>
                <nav class="flex flex-wrap items-center gap-1 text-sm text-gray-600 dark:text-gray-400">
                    <template v-for="(crumb, i) in breadcrumb" :key="crumb.path">
                        <span v-if="i > 0" class="text-gray-400 dark:text-gray-500">/</span>
                        <Link
                            v-if="i < breadcrumb.length - 1"
                            :href="route('media.index', crumb.path ? { path: crumb.path } : {})"
                            class="text-indigo-600 dark:text-indigo-400 hover:underline"
                        >
                            {{ crumb.label }}
                        </Link>
                        <span v-else class="text-gray-800 dark:text-gray-200 font-medium">{{ crumb.label }}</span>
                    </template>
                </nav>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-10"></th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nome</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Dimensione</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-24">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr
                                v-for="item in items"
                                :key="item.path"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700/50"
                            >
                                <td class="px-4 py-2">
                                    <FolderIcon v-if="item.type === 'folder'" class="size-5 text-amber-500 dark:text-amber-400" aria-hidden="true" />
                                    <DocumentIcon v-else class="size-5 text-gray-400 dark:text-gray-500" aria-hidden="true" />
                                </td>
                                <td class="px-4 py-2">
                                    <Link
                                        v-if="item.type === 'folder'"
                                        :href="route('media.index', { path: item.path })"
                                        class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium"
                                    >
                                        {{ item.name }}
                                    </Link>
                                    <span v-else class="text-gray-800 dark:text-gray-200">{{ item.name }}</span>
                                </td>
                                <td class="px-4 py-2 text-right text-sm text-gray-500 dark:text-gray-400">
                                    {{ item.type === 'file' ? formatSize(item.size) : '—' }}
                                </td>
                                <td class="px-4 py-2 text-right">
                                    <a
                                        v-if="item.type === 'file'"
                                        :href="downloadUrl(item)"
                                        class="inline-flex items-center gap-1 text-sm text-indigo-600 dark:text-indigo-400 hover:underline"
                                    >
                                        <ArrowDownTrayIcon class="size-4" aria-hidden="true" />
                                        Scarica
                                    </a>
                                    <span v-else>—</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-if="!items?.length" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Questa cartella è vuota.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
