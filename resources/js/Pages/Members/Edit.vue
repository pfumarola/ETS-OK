<script setup>
import { CheckIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({ member: Object, memberTypes: Array });
const page = usePage();
const canManage = computed(() => page.props?.userRoles?.includes('admin') || page.props?.userRoles?.includes('segreteria'));
const authMember = computed(() => page.props?.authMember ?? null);
const isSocioEdit = computed(() => authMember.value && Number(authMember.value.id) === Number(props.member.id) && !canManage.value);

const formFull = useForm({
    member_type_id: props.member.member_type_id,
    numero_tessera: props.member.numero_tessera ?? '',
    nome: props.member.nome,
    cognome: props.member.cognome,
    email: props.member.email ?? '',
    codice_fiscale: props.member.codice_fiscale ?? '',
    data_iscrizione: props.member.data_iscrizione ? props.member.data_iscrizione.slice(0, 10) : '',
    stato: props.member.stato ?? 'attivo',
    indirizzo: props.member.indirizzo ?? '',
    telefono: props.member.telefono ?? '',
    note: props.member.note ?? '',
});

const formSocio = useForm({
    nome: props.member.nome,
    cognome: props.member.cognome,
    email: props.member.email ?? '',
    indirizzo: props.member.indirizzo ?? '',
    telefono: props.member.telefono ?? '',
});
</script>

<template>
    <AppLayout title="Modifica socio">
        <Head :title="'Modifica ' + member.cognome" />
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Modifica socio</h2>
                <Link :href="route('members.show', member.id)" class="inline-flex items-center gap-1 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-300"><ArrowLeftIcon class="size-4" aria-hidden="true" />Annulla</Link>
            </div>
        </template>

        <div class="py-6 max-w-3xl mx-auto sm:px-6">
            <!-- Form ridotta per socio (solo anagrafica personale) -->
            <form v-if="isSocioEdit" @submit.prevent="formSocio.put(route('members.update', member.id))" class="space-y-4 bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="socio_cognome" value="Cognome *" />
                        <TextInput id="socio_cognome" v-model="formSocio.cognome" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="formSocio.errors.cognome" />
                    </div>
                    <div>
                        <InputLabel for="socio_nome" value="Nome *" />
                        <TextInput id="socio_nome" v-model="formSocio.nome" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="formSocio.errors.nome" />
                    </div>
                </div>
                <div>
                    <InputLabel for="socio_email" value="Email (anagrafica socio)" />
                    <TextInput id="socio_email" v-model="formSocio.email" type="email" class="mt-1 block w-full" />
                    <InputError class="mt-1" :message="formSocio.errors.email" />
                </div>
                <div>
                    <InputLabel for="socio_indirizzo" value="Indirizzo" />
                    <TextInput id="socio_indirizzo" v-model="formSocio.indirizzo" class="mt-1 block w-full" />
                    <InputError class="mt-1" :message="formSocio.errors.indirizzo" />
                </div>
                <div>
                    <InputLabel for="socio_telefono" value="Telefono" />
                    <TextInput id="socio_telefono" v-model="formSocio.telefono" class="mt-1 block w-full" />
                    <InputError class="mt-1" :message="formSocio.errors.telefono" />
                </div>
                <div class="flex gap-2">
                    <PrimaryButton type="submit" :disabled="formSocio.processing"><CheckIcon class="size-4 me-2" aria-hidden="true" />Salva</PrimaryButton>
                    <Link :href="route('members.show', member.id)" class="inline-flex items-center gap-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md font-medium text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700"><ArrowLeftIcon class="size-4 me-1" aria-hidden="true" />Annulla</Link>
                </div>
            </form>

            <!-- Form completa per staff -->
            <form v-else @submit.prevent="formFull.put(route('members.update', member.id))" class="space-y-4 bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div>
                    <InputLabel for="member_type_id" value="Tipologia *" />
                    <select id="member_type_id" v-model="formFull.member_type_id" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                        <option v-for="t in memberTypes" :key="t.id" :value="t.id">{{ t.display_name || t.name }}</option>
                    </select>
                    <InputError class="mt-1" :message="formFull.errors.member_type_id" />
                </div>
                <div>
                    <InputLabel for="numero_tessera" value="Numero tessera" />
                    <TextInput id="numero_tessera" v-model="formFull.numero_tessera" type="number" min="1" class="mt-1 block w-full" />
                    <InputError class="mt-1" :message="formFull.errors.numero_tessera" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="cognome" value="Cognome *" />
                        <TextInput id="cognome" v-model="formFull.cognome" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="formFull.errors.cognome" />
                    </div>
                    <div>
                        <InputLabel for="nome" value="Nome *" />
                        <TextInput id="nome" v-model="formFull.nome" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="formFull.errors.nome" />
                    </div>
                </div>
                <div>
                    <InputLabel for="email" value="Email (anagrafica socio)" />
                    <TextInput id="email" v-model="formFull.email" type="email" class="mt-1 block w-full" />
                    <InputError class="mt-1" :message="formFull.errors.email" />
                </div>
                <div>
                    <InputLabel for="codice_fiscale" value="Codice fiscale *" />
                    <TextInput id="codice_fiscale" v-model="formFull.codice_fiscale" class="mt-1 block w-full" maxlength="16" required />
                    <InputError class="mt-1" :message="formFull.errors.codice_fiscale" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="data_iscrizione" value="Data iscrizione" />
                        <TextInput id="data_iscrizione" v-model="formFull.data_iscrizione" type="date" class="mt-1 block w-full" />
                        <InputError class="mt-1" :message="formFull.errors.data_iscrizione" />
                    </div>
                    <div>
                        <InputLabel for="stato" value="Stato" />
                        <select id="stato" v-model="formFull.stato" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                            <option value="aspirante">Aspirante</option>
                            <option value="attivo">Attivo</option>
                            <option value="sospeso">Sospeso</option>
                            <option value="cessato">Cessato</option>
                            <option value="rigettato">Rigettato</option>
                            <option value="in_ricorso">In ricorso</option>
                            <option value="decesso">Decesso</option>
                            <option value="dimesso">Dimesso</option>
                            <option value="escluso">Escluso</option>
                            <option value="moroso">Moroso</option>
                        </select>
                    </div>
                </div>
                <div>
                    <InputLabel for="indirizzo" value="Indirizzo" />
                    <TextInput id="indirizzo" v-model="formFull.indirizzo" class="mt-1 block w-full" />
                </div>
                <div>
                    <InputLabel for="telefono" value="Telefono" />
                    <TextInput id="telefono" v-model="formFull.telefono" class="mt-1 block w-full" />
                </div>
                <div>
                    <InputLabel for="note" value="Note" />
                    <textarea id="note" v-model="formFull.note" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm"></textarea>
                </div>
                <div class="flex gap-2">
                    <PrimaryButton type="submit" :disabled="formFull.processing"><CheckIcon class="size-4 me-2" aria-hidden="true" />Salva</PrimaryButton>
                    <Link :href="route('members.show', member.id)" class="inline-flex items-center gap-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md font-medium text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700"><ArrowLeftIcon class="size-4 me-1" aria-hidden="true" />Annulla</Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
