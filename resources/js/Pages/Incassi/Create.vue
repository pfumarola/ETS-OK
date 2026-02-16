<script setup>
import { CheckIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';
import { computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SearchableMemberSelect from '@/Components/SearchableMemberSelect.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    members: Array,
    paymentMethods: Array,
    preselectedType: { type: String, default: 'quota' },
    preselectedMember: Object,
    preselectedSubscriptionId: Number,
    preselectedAmount: String,
    preselectedDescription: String,
    quota_annuale: [Number, String],
    causale_default_quota: { type: String, default: 'Quota associativa' },
    causale_default_donazione: { type: String, default: 'Erogazione liberale' },
});

const form = useForm({
    type: props.preselectedType,
    donor_mode: props.preselectedType === 'donazione' ? (props.preselectedMember?.id ? 'member' : 'manual') : 'member',
    member_id: props.preselectedMember?.id ?? (props.preselectedType === 'donazione' ? '' : ''),
    donor_name: '',
    subscription_id: props.preselectedType === 'donazione' ? '' : (props.preselectedSubscriptionId ?? ''),
    amount: props.preselectedAmount ?? (props.preselectedType === 'quota' && props.quota_annuale != null ? String(Number(props.quota_annuale).toFixed(2)) : ''),
    paid_at: new Date().toISOString().slice(0, 10),
    payment_method_id: '',
    description: props.preselectedType === 'donazione' ? (props.causale_default_donazione ?? 'Erogazione liberale') : (props.preselectedDescription ?? props.causale_default_quota ?? ''),
    issue_receipt: true,
    genera_prima_nota: true,
});

const subscriptionsForMember = computed(() => {
    if (!form.member_id) return [];
    const m = props.members.find(m => m.id == form.member_id);
    return m?.subscriptions ?? [];
});

/** Al cambio tipo incasso imposta la causale di default da settings. */
watch(() => form.type, (newType) => {
    form.description = newType === 'donazione'
        ? (props.causale_default_donazione ?? 'Erogazione liberale')
        : (props.causale_default_quota ?? 'Quota associativa');
});

const submitLabel = computed(() => form.type === 'donazione' ? 'Registra donazione' : 'Registra incasso');

function onSubmit() {
    if (form.type === 'donazione') {
        if (form.donor_mode === 'manual') form.member_id = '';
        else form.donor_name = '';
    }
    form.post(route('incassi.store'));
}
</script>

<template>
    <AppLayout :title="form.type === 'donazione' ? 'Nuova donazione' : 'Nuovo incasso'">
        <Head :title="form.type === 'donazione' ? 'Nuova donazione' : 'Nuovo incasso'" />
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ form.type === 'donazione' ? 'Nuova donazione' : 'Nuovo incasso' }}</h2>
        </template>

        <div class="py-6 max-w-2xl mx-auto sm:px-6">
            <form @submit.prevent="onSubmit" class="space-y-4 bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div>
                    <InputLabel for="type" value="Tipo" />
                    <select id="type" v-model="form.type" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                        <option value="quota">Quota</option>
                        <option value="donazione">Donazione</option>
                    </select>
                </div>
                <template v-if="form.type === 'quota'">
                    <div>
                        <InputLabel for="member_id" value="Socio *" />
                        <SearchableMemberSelect
                            id="member_id"
                            v-model="form.member_id"
                            :members="members"
                            placeholder="Seleziona socio"
                        />
                        <InputError class="mt-1" :message="form.errors.member_id" />
                    </div>
                </template>
                <template v-else>
                    <div>
                        <InputLabel value="Donatore" />
                        <div class="mt-1 flex gap-4 items-center">
                            <label class="inline-flex items-center">
                                <input v-model="form.donor_mode" type="radio" value="member" class="rounded border-gray-300 dark:border-gray-700" />
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Da anagrafica</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input v-model="form.donor_mode" type="radio" value="manual" class="rounded border-gray-300 dark:border-gray-700" />
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Inserisci a mano</span>
                            </label>
                        </div>
                        <div v-if="form.donor_mode === 'member'" class="mt-2">
                            <SearchableMemberSelect
                                id="member_id"
                                v-model="form.member_id"
                                :members="members"
                                placeholder="Seleziona donatore"
                                empty-option="Anonimo"
                            />
                            <InputError class="mt-1" :message="form.errors.member_id" />
                        </div>
                        <div v-else class="mt-2">
                            <TextInput
                                id="donor_name"
                                v-model="form.donor_name"
                                type="text"
                                class="block w-full"
                                placeholder="Nome e cognome del donatore (opzionale)"
                            />
                            <InputError class="mt-1" :message="form.errors.donor_name" />
                        </div>
                    </div>
                </template>
                <div v-if="form.type === 'quota' && subscriptionsForMember.length" class="subscription-block">
                    <InputLabel for="subscription_id" value="Iscrizione (opzionale)" />
                    <select id="subscription_id" v-model="form.subscription_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                        <option value="">Nessuna</option>
                        <option v-for="s in subscriptionsForMember" :key="s.id" :value="s.id">Anno {{ s.year }} (scad. {{ s.ends_at ? new Date(s.ends_at).toLocaleDateString('it-IT') : '' }})</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="amount" value="Importo (€) *" />
                        <TextInput id="amount" v-model="form.amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="form.errors.amount" />
                    </div>
                    <div>
                        <InputLabel for="paid_at" value="Data *" />
                        <TextInput id="paid_at" v-model="form.paid_at" type="date" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="form.errors.paid_at" />
                    </div>
                </div>
                <div>
                    <InputLabel for="payment_method_id" value="Metodo di pagamento" />
                    <select id="payment_method_id" v-model="form.payment_method_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                        <option value="">—</option>
                        <option v-for="pm in paymentMethods" :key="pm.id" :value="pm.id">{{ pm.name }}</option>
                    </select>
                </div>
                <div>
                    <InputLabel for="description" value="Causale / Descrizione" />
                    <TextInput id="description" v-model="form.description" class="mt-1 block w-full" :placeholder="form.type === 'donazione' ? (causale_default_donazione || 'Erogazione liberale') : (causale_default_quota || 'Quota associativa')" />
                </div>
                <div class="flex items-center">
                    <input id="genera_prima_nota" v-model="form.genera_prima_nota" type="checkbox" class="rounded border-gray-300 dark:border-gray-700 shadow-sm" />
                    <label for="genera_prima_nota" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Genera movimento in prima nota</label>
                </div>
                <div v-if="form.type === 'quota' || form.member_id || (form.type === 'donazione' && form.donor_name)" class="flex items-center">
                    <input id="issue_receipt" v-model="form.issue_receipt" type="checkbox" class="rounded border-gray-300 dark:border-gray-700 shadow-sm" />
                    <label for="issue_receipt" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Emetti ricevuta</label>
                </div>
                <div class="flex gap-2">
                    <PrimaryButton type="submit" :disabled="form.processing"><CheckIcon class="size-4 me-2" aria-hidden="true" />{{ submitLabel }}</PrimaryButton>
                    <Link :href="route('incassi.index')" class="inline-flex items-center gap-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md font-medium text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700"><ArrowLeftIcon class="size-4 me-1" aria-hidden="true" />Annulla</Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
