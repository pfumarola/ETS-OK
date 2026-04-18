<script setup>
import { PaperAirplaneIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    mailer: String,
    fromPreview: Object,
});

const form = useForm({
    to: '',
    cc: '',
    bcc: '',
    subject: '',
    body: '',
    is_html: false,
});

function submit() {
    form.post(route('mail.send'), { preserveScroll: true });
}

function fromLine() {
    const name = props.fromPreview?.name;
    const addr = props.fromPreview?.address;
    if (name && addr) {
        return `${name} <${addr}>`;
    }
    return addr || '—';
}
</script>

<template>
    <AppLayout title="Invio email">
        <Head title="Invio email" />
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Invio email</h2>
                <Link :href="route('documents.index')" class="inline-flex items-center gap-1 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:text-gray-300">
                    <ArrowLeftIcon class="size-4" aria-hidden="true" />Documenti
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
                <div
                    v-if="mailer === 'log'"
                    class="rounded-lg border border-amber-200 bg-amber-50 dark:border-amber-900/50 dark:bg-amber-950/40 px-4 py-3 text-sm text-amber-900 dark:text-amber-100"
                    role="status"
                >
                    Il driver di posta è impostato su <strong>log</strong>: i messaggi non vengono inviati via SMTP ma registrati nei log dell’applicazione.
                </div>

                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Mittente (da configurazione):
                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ fromLine() }}</span>
                </p>

                <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden p-6 space-y-4">
                    <div>
                        <InputLabel for="mail_to" value="A *" />
                        <TextInput
                            id="mail_to"
                            v-model="form.to"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="uno@esempio.it, altro@esempio.it"
                            required
                            autocomplete="off"
                        />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Separare più indirizzi con virgola, punto e virgola o a capo.</p>
                        <InputError class="mt-1" :message="form.errors.to" />
                    </div>
                    <div>
                        <InputLabel for="mail_cc" value="CC" />
                        <TextInput
                            id="mail_cc"
                            v-model="form.cc"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Opzionale"
                            autocomplete="off"
                        />
                        <InputError class="mt-1" :message="form.errors.cc" />
                    </div>
                    <div>
                        <InputLabel for="mail_bcc" value="BCC" />
                        <TextInput
                            id="mail_bcc"
                            v-model="form.bcc"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Opzionale"
                            autocomplete="off"
                        />
                        <InputError class="mt-1" :message="form.errors.bcc" />
                    </div>
                    <div>
                        <InputLabel for="mail_subject" value="Oggetto *" />
                        <TextInput
                            id="mail_subject"
                            v-model="form.subject"
                            type="text"
                            class="mt-1 block w-full"
                            required
                            maxlength="255"
                            autocomplete="off"
                        />
                        <InputError class="mt-1" :message="form.errors.subject" />
                    </div>
                    <div>
                        <InputLabel for="mail_body" value="Messaggio *" />
                        <textarea
                            id="mail_body"
                            v-model="form.body"
                            rows="14"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 dark:focus:ring-indigo-600 font-mono text-sm"
                            :placeholder="form.is_html ? 'Corpo HTML (es. paragrafi, link, …)' : 'Testo del messaggio'"
                        />
                        <InputError class="mt-1" :message="form.errors.body" />
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <Checkbox :checked="form.is_html" @update:checked="(v) => (form.is_html = v)" />
                        <span class="text-sm text-gray-700 dark:text-gray-300">Invia come HTML</span>
                    </label>

                    <div class="flex gap-2 pt-2">
                        <PrimaryButton type="submit" :disabled="form.processing">
                            <PaperAirplaneIcon class="size-4 me-2" aria-hidden="true" />
                            Invia
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
