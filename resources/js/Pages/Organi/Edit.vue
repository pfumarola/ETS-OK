<script setup>
import { CheckIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({ organo: Object });

const form = useForm({
    nome: props.organo.nome ?? '',
    durata_mesi: props.organo.durata_mesi ?? '',
    richiedi_elezioni_fine_mandato: !!props.organo.richiedi_elezioni_fine_mandato,
    mandato_da: props.organo.mandato_da ? props.organo.mandato_da.slice(0, 10) : '',
});
</script>

<template>
    <AppLayout :title="'Modifica ' + (organo.nome || 'organo')">
        <Head :title="'Modifica ' + (organo.nome || 'organo')" />
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Modifica organo</h2>
                <Link :href="route('organi.show', organo.id)" class="inline-flex items-center gap-1 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-300"><ArrowLeftIcon class="size-4" aria-hidden="true" />Indietro</Link>
            </div>
        </template>

        <div class="py-6 max-w-2xl mx-auto sm:px-6">
            <form @submit.prevent="form.put(route('organi.update', organo.id))" class="space-y-4 bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div>
                    <InputLabel for="nome" value="Nome *" />
                    <TextInput id="nome" v-model="form.nome" class="mt-1 block w-full" maxlength="255" />
                    <InputError class="mt-1" :message="form.errors.nome" />
                </div>
                <div>
                    <InputLabel for="durata_mesi" value="Durata (mesi)" />
                    <TextInput id="durata_mesi" v-model="form.durata_mesi" type="number" min="1" max="120" class="mt-1 block w-full" />
                    <InputError class="mt-1" :message="form.errors.durata_mesi" />
                </div>
                <div class="flex items-center gap-2">
                    <input id="richiedi_elezioni" v-model="form.richiedi_elezioni_fine_mandato" type="checkbox" class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
                    <InputLabel for="richiedi_elezioni" value="Richiedi elezioni a fine mandato" class="!mb-0" />
                </div>
                <div>
                    <InputLabel for="mandato_da" value="Mandato da" />
                    <TextInput id="mandato_da" v-model="form.mandato_da" type="date" class="mt-1 block w-full" />
                    <InputError class="mt-1" :message="form.errors.mandato_da" />
                </div>
                <div class="flex gap-2">
                    <PrimaryButton type="submit" :disabled="form.processing"><CheckIcon class="size-4 me-2" aria-hidden="true" />Salva</PrimaryButton>
                    <Link :href="route('organi.show', organo.id)" class="inline-flex items-center gap-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md font-medium text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700"><ArrowLeftIcon class="size-4 me-1" aria-hidden="true" />Annulla</Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
