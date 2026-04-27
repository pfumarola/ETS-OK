<script setup>
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    convocazione: Object,
    tipoOptions: Array,
});

const form = useForm({
    tipo: props.convocazione.tipo ?? 'assemblea',
    titolo: props.convocazione.titolo ?? '',
    scheduled_at: props.convocazione.scheduled_at_input ?? '',
    luogo: props.convocazione.luogo ?? '',
    ordine_del_giorno: props.convocazione.ordine_del_giorno ?? '',
});
</script>

<template>
    <AppLayout title="Modifica convocazione">
        <Head title="Modifica convocazione" />
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Modifica convocazione</h2>
                <Link :href="route('convocazioni.show', { convocazione: convocazione.id })" class="inline-flex items-center gap-1 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-300"><ArrowLeftIcon class="size-4" aria-hidden="true" />Dettaglio</Link>
            </div>
        </template>

        <div class="py-6 max-w-4xl mx-auto sm:px-6">
            <form @submit.prevent="form.put(route('convocazioni.update', { convocazione: convocazione.id }))" class="space-y-4 bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div>
                    <InputLabel for="tipo" value="Tipo *" />
                    <select id="tipo" v-model="form.tipo" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        <option v-for="opt in tipoOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                    </select>
                    <InputError class="mt-1" :message="form.errors.tipo" />
                </div>
                <div>
                    <InputLabel for="titolo" value="Titolo (opzionale)" />
                    <TextInput id="titolo" v-model="form.titolo" type="text" class="mt-1 block w-full" />
                    <InputError class="mt-1" :message="form.errors.titolo" />
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="scheduled_at" value="Data e ora *" />
                        <TextInput id="scheduled_at" v-model="form.scheduled_at" type="datetime-local" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="form.errors.scheduled_at" />
                    </div>
                    <div>
                        <InputLabel for="luogo" value="Luogo *" />
                        <TextInput id="luogo" v-model="form.luogo" type="text" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="form.errors.luogo" />
                    </div>
                </div>
                <div>
                    <InputLabel for="odg" value="Ordine del giorno *" />
                    <RichTextEditor
                        id="odg"
                        v-model="form.ordine_del_giorno"
                        placeholder="Inserisci i punti dell'ordine del giorno..."
                        :placeholder-items="[]"
                        min-height="220px"
                    />
                    <InputError class="mt-1" :message="form.errors.ordine_del_giorno" />
                </div>
                <div class="pt-2">
                    <PrimaryButton :disabled="form.processing">Salva modifiche</PrimaryButton>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
