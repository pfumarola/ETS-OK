<script setup>
import { CheckIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({ memberTypes: Array, nextNumeroTessera: Number });

const form = useForm({
    member_type_id: '',
    numero_tessera: '',
    nome: '',
    cognome: '',
    email: '',
    codice_fiscale: '',
    data_iscrizione: '',
    stato: 'attivo',
    indirizzo: '',
    telefono: '',
    note: '',
    presentare_domanda: false,
});
</script>

<template>
    <AppLayout title="Nuovo socio">
        <Head title="Nuovo socio" />
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Nuovo socio / volontario</h2>
                <Link :href="route('members.index')" class="inline-flex items-center gap-1 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-300"><ArrowLeftIcon class="size-4" aria-hidden="true" />Annulla</Link>
            </div>
        </template>

        <div class="py-6 max-w-3xl mx-auto sm:px-6">
            <form @submit.prevent="form.post(route('members.store'))" class="space-y-4 bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div>
                    <InputLabel for="member_type_id" value="Tipologia *" />
                    <select id="member_type_id" v-model="form.member_type_id" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                        <option value="">Seleziona</option>
                        <option v-for="t in memberTypes" :key="t.id" :value="t.id">{{ t.display_name || t.name }}</option>
                    </select>
                    <InputError class="mt-1" :message="form.errors.member_type_id" />
                </div>
                <div>
                    <InputLabel for="numero_tessera" value="Numero tessera" />
                    <TextInput id="numero_tessera" v-model="form.numero_tessera" type="number" min="1" class="mt-1 block w-full" :placeholder="'Vuoto = prossimo disponibile (' + (nextNumeroTessera || 1) + ')'" />
                    <InputError class="mt-1" :message="form.errors.numero_tessera" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="cognome" value="Cognome *" />
                        <TextInput id="cognome" v-model="form.cognome" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="form.errors.cognome" />
                    </div>
                    <div>
                        <InputLabel for="nome" value="Nome *" />
                        <TextInput id="nome" v-model="form.nome" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="form.errors.nome" />
                    </div>
                </div>
                <div>
                    <InputLabel for="email" value="Email (anagrafica socio)" />
                    <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" />
                    <InputError class="mt-1" :message="form.errors.email" />
                </div>
                <div>
                    <InputLabel for="codice_fiscale" value="Codice fiscale" />
                    <TextInput id="codice_fiscale" v-model="form.codice_fiscale" class="mt-1 block w-full" maxlength="64" />
                    <InputError class="mt-1" :message="form.errors.codice_fiscale" />
                </div>
                <div class="flex items-center gap-2">
                    <input id="presentare_domanda" v-model="form.presentare_domanda" type="checkbox" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900">
                    <InputLabel for="presentare_domanda" value="Presenta come domanda di ammissione (aspirante socio)" class="!mb-0" />
                </div>
                <div v-if="!form.presentare_domanda" class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="data_iscrizione" value="Data iscrizione" />
                        <TextInput id="data_iscrizione" v-model="form.data_iscrizione" type="date" class="mt-1 block w-full" />
                        <InputError class="mt-1" :message="form.errors.data_iscrizione" />
                    </div>
                    <div>
                        <InputLabel for="stato" value="Stato" />
                        <select id="stato" v-model="form.stato" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                            <option value="attivo">Attivo</option>
                            <option value="sospeso">Sospeso</option>
                            <option value="cessato">Cessato</option>
                        </select>
                    </div>
                </div>
                <div>
                    <InputLabel for="indirizzo" value="Indirizzo" />
                    <TextInput id="indirizzo" v-model="form.indirizzo" class="mt-1 block w-full" />
                </div>
                <div>
                    <InputLabel for="telefono" value="Telefono" />
                    <TextInput id="telefono" v-model="form.telefono" class="mt-1 block w-full" />
                </div>
                <div>
                    <InputLabel for="note" value="Note" />
                    <textarea id="note" v-model="form.note" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm"></textarea>
                </div>
                <div class="flex gap-2">
                    <PrimaryButton type="submit" :disabled="form.processing"><CheckIcon class="size-4 me-2" aria-hidden="true" />Salva</PrimaryButton>
                    <Link :href="route('members.index')" class="inline-flex items-center gap-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md font-medium text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700"><ArrowLeftIcon class="size-4 me-1" aria-hidden="true" />Annulla</Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
