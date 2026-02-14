<script setup>
import { ref } from 'vue';
import { PlusIcon, PencilSquareIcon, TrashIcon, ArrowLeftIcon, CheckIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({ organo: Object, members: { type: Array, default: () => [] } });
const showAddForm = ref(false);
const editingId = ref(null);
const showAssignForCaricaId = ref(null);
const formAdd = useForm({ nome: '', ordine: 0 });
const formEdit = useForm({ nome: '', ordine: 0 });
const formIncarico = useForm({ member_id: '' });

function eliminaCarica(c) {
    const hasIncarichi = c.incarichi && c.incarichi.length > 0;
    const message = hasIncarichi 
        ? `Rimuovere la carica "${c.nome}"?\n\nATTENZIONE: Verranno eliminati anche ${c.incarichi.length} incarico${c.incarichi.length === 1 ? '' : 'i'} associato${c.incarichi.length === 1 ? '' : 'i'}.`
        : `Rimuovere la carica "${c.nome}"?`;
    
    if (!confirm(message)) return;
    router.delete(route('cariche-sociali.destroy', c.id), { preserveScroll: true });
}

function avviaAggiunta() {
    showAddForm.value = true;
    formAdd.reset();
}

function annullaAggiunta() {
    showAddForm.value = false;
    formAdd.reset();
}

function salvaAggiunta() {
    formAdd.transform((data) => ({
        ...data,
        organo_id: props.organo.id,
    })).post(route('cariche-sociali.store'), {
        preserveScroll: true,
        onSuccess: () => { showAddForm.value = false; formAdd.reset(); },
    });
}

function avviaModifica(c) {
    editingId.value = c.id;
    formEdit.nome = c.nome;
    formEdit.ordine = c.ordine ?? 0;
}

function annullaModifica() {
    editingId.value = null;
    formEdit.reset();
}

function salvaModifica() {
    formEdit.transform((data) => ({
        ...data,
        organo_id: props.organo.id,
    })).put(route('cariche-sociali.update', editingId.value), {
        preserveScroll: true,
        onSuccess: () => { editingId.value = null; formEdit.reset(); },
    });
}

function avviaAssegna(carica) {
    showAssignForCaricaId.value = carica.id;
    formIncarico.reset();
    formIncarico.member_id = '';
}

function annullaAssegna() {
    showAssignForCaricaId.value = null;
    formIncarico.reset();
}

function salvaIncarico(caricaId) {
    if (!formIncarico.member_id) return;
    formIncarico.transform(() => ({
        carica_sociale_id: caricaId,
    })).post(route('members.incarichi.store', formIncarico.member_id), {
        preserveScroll: true,
        onSuccess: () => { showAssignForCaricaId.value = null; formIncarico.reset(); },
    });
}


</script>

<template>
    <AppLayout :title="organo.nome">
        <Head :title="organo.nome" />
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ organo.nome }}</h2>
                <div class="flex gap-2">
                    <Link :href="route('organi.edit', organo.id)" class="inline-flex items-center gap-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md font-medium text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <PencilSquareIcon class="size-4" aria-hidden="true" />Modifica
                    </Link>
                    <Link :href="route('organi.index')" class="inline-flex items-center gap-1 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-300"><ArrowLeftIcon class="size-4" aria-hidden="true" />Elenco organi</Link>
                </div>
            </div>
        </template>

        <div class="py-6 max-w-4xl mx-auto sm:px-6 space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Dettagli</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><dt class="text-sm text-gray-500 dark:text-gray-400">Durata</dt><dd>{{ organo.durata_mesi ? organo.durata_mesi + ' mesi' : '—' }}</dd></div>
                    <div><dt class="text-sm text-gray-500 dark:text-gray-400">Elezione fine mandato</dt><dd>{{ organo.richiedi_elezioni_fine_mandato ? 'Sì' : 'No' }}</dd></div>
                    <div><dt class="text-sm text-gray-500 dark:text-gray-400">Mandato da</dt><dd>{{ organo.mandato_da ? new Date(organo.mandato_da).toLocaleDateString('it-IT') : '—' }}</dd></div>
                </dl>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                <h3 class="px-4 py-3 text-lg font-medium text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700">Cariche sociali</h3>
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nome</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Ordine</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Assegnatari</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Azioni</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="c in organo.cariche_sociali" :key="c.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-2">
                                <template v-if="editingId === c.id">
                                    <TextInput v-model="formEdit.nome" class="w-full" placeholder="Nome carica" maxlength="255" />
                                    <InputError :message="formEdit.errors.nome" />
                                </template>
                                <span v-else class="font-medium">{{ c.nome }}</span>
                            </td>
                            <td class="px-4 py-2">
                                <template v-if="editingId === c.id">
                                    <TextInput v-model.number="formEdit.ordine" type="number" min="0" class="w-full" />
                                    <InputError :message="formEdit.errors.ordine" />
                                </template>
                                <span v-else>{{ c.ordine ?? 0 }}</span>
                            </td>
                            <td class="px-4 py-2">
                                <template v-if="showAssignForCaricaId === c.id">
                                    <div class="space-y-2">
                                        <div>
                                            <InputLabel for="incarico_member" value="Socio *" class="text-xs" />
                                            <select id="incarico_member" v-model="formIncarico.member_id" class="mt-0.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 text-sm" required>
                                                <option value="">Seleziona socio</option>
                                                <option v-for="m in members" :key="m.id" :value="m.id">{{ m.cognome }} {{ m.nome }}</option>
                                            </select>
                                            <InputError :message="formIncarico.errors.member_id" />
                                        </div>
                                        <div class="flex gap-2">
                                            <button type="button" class="text-sm text-green-600 dark:text-green-400 hover:underline" :disabled="formIncarico.processing" @click="salvaIncarico(c.id)">Salva</button>
                                            <button type="button" class="text-sm text-gray-500 hover:underline" :disabled="formIncarico.processing" @click="annullaAssegna">Annulla</button>
                                        </div>
                                    </div>
                                </template>
                                <template v-else>
                                    <ul class="text-sm space-y-0.5">
                                        <li v-for="inc in (c.incarichi || [])" :key="inc.id">
                                            <Link :href="route('members.show', inc.member_id)" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ inc.member?.cognome }} {{ inc.member?.nome }}</Link>
                                        </li>
                                    </ul>
                                    <button v-if="members?.length" type="button" class="mt-1 text-xs text-indigo-600 dark:text-indigo-400 hover:underline" @click="avviaAssegna(c)">Cambia assegnatario</button>
                                    <span v-if="!c.incarichi?.length && !showAssignForCaricaId" class="text-gray-500 text-sm">—</span>
                                </template>
                            </td>
                            <td class="px-4 py-2 text-right">
                                <template v-if="editingId === c.id">
                                    <button type="button" @click="salvaModifica" class="text-sm text-green-600 dark:text-green-400 hover:underline me-3" :disabled="formEdit.processing">
                                        <CheckIcon class="size-4 inline" aria-hidden="true" />
                                    </button>
                                    <button type="button" @click="annullaModifica" class="text-sm text-gray-600 dark:text-gray-400 hover:underline" :disabled="formEdit.processing">
                                        <XMarkIcon class="size-4 inline" aria-hidden="true" />
                                    </button>
                                </template>
                                <template v-else>
                                    <button type="button" @click="avviaModifica(c)" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline me-3">Modifica</button>
                                    <button type="button" @click="eliminaCarica(c)" class="text-sm text-red-600 dark:text-red-400 hover:underline">Elimina</button>
                                </template>
                            </td>
                        </tr>
                        <tr v-if="showAddForm">
                            <td class="px-4 py-2">
                                <TextInput v-model="formAdd.nome" class="w-full" placeholder="Nome carica" maxlength="255" />
                                <InputError :message="formAdd.errors.nome" />
                            </td>
                            <td class="px-4 py-2">
                                <TextInput v-model.number="formAdd.ordine" type="number" min="0" class="w-full" />
                                <InputError :message="formAdd.errors.ordine" />
                            </td>
                            <td class="px-4 py-2"></td>
                            <td class="px-4 py-2 text-right">
                                <button type="button" @click="salvaAggiunta" class="text-sm text-green-600 dark:text-green-400 hover:underline me-3" :disabled="formAdd.processing">
                                    <CheckIcon class="size-4 inline" aria-hidden="true" />
                                </button>
                                <button type="button" @click="annullaAggiunta" class="text-sm text-gray-600 dark:text-gray-400 hover:underline" :disabled="formAdd.processing">
                                    <XMarkIcon class="size-4 inline" aria-hidden="true" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="!organo.cariche_sociali?.length && !showAddForm" class="px-4 py-8 text-center text-gray-500">Nessuna carica.</div>
                <div v-if="!showAddForm" class="px-4 py-4 border-t">
                    <SecondaryButton type="button" @click="avviaAggiunta"><PlusIcon class="size-4 me-2" aria-hidden="true" />Aggiungi carica</SecondaryButton>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
