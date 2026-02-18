<script setup>
import { UserGroupIcon, BanknotesIcon, ExclamationTriangleIcon, UserCircleIcon, CalendarDaysIcon, ClipboardDocumentListIcon } from '@heroicons/vue/24/outline';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps({
    forSocio: Boolean,
    noRole: { type: Boolean, default: false },
    memberStatusBlocked: { type: Boolean, default: false },
    member: Object,
    votazioniAperte: { type: Array, default: () => [] },
    members_count: Number,
    payments_this_month: [Number, String],
    members_not_in_regola: { type: Number, default: 0 },
    upcoming_events: { type: Array, default: () => [] },
    recent_incassi: { type: Array, default: () => [] },
    organiInScadenza: { type: Array, default: () => [] },
    refunds_pending_approval: { type: Array, default: () => [] },
});
</script>

<template>
    <AppLayout title="Dashboard">
        <Head title="Dashboard" />
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Dashboard</h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Utente senza ruoli: nessun dato -->
                <div v-if="noRole" class="space-y-4">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Account in attesa di attivazione</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Il tuo account non ha ancora un ruolo assegnato. Non puoi accedere ai contenuti dell’area riservata. Contatta un amministratore per ottenere l’attivazione.</p>
                    </div>
                </div>

                <!-- Socio con stato diverso da Attivo: accesso area soci non disponibile -->
                <div v-else-if="memberStatusBlocked" class="space-y-4">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Accesso all'area soci non disponibile</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">L'accesso all'area riservata è consentito solo ai soci con stato <strong>Attivo</strong>. Per il tuo stato attuale non puoi accedere ai contenuti riservati. Contatta la segreteria per informazioni.</p>
                    </div>
                </div>

                <!-- Dashboard socio: solo area personale -->
                <div v-else-if="forSocio && member" class="space-y-4">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Benvenuto, {{ member.full_name }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Qui puoi vedere il riepilogo del tuo profilo socio e lo stato della quota.</p>
                        <div class="mb-4">
                            <span v-if="member.in_regola_con_quota" class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">In regola con la quota sociale</span>
                            <span v-else class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">Non in regola con la quota sociale</span>
                        </div>
                        <Link :href="route('members.show', member.id)">
                            <PrimaryButton><UserCircleIcon class="size-4 me-2" aria-hidden="true" />Vai al mio profilo</PrimaryButton>
                        </Link>
                    </div>
                    <div v-if="votazioniAperte?.length" class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Votazioni in corso</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Puoi esprimere il tuo voto (anonimo) per le seguenti votazioni.</p>
                        <ul class="space-y-2">
                            <li v-for="v in votazioniAperte" :key="v.id">
                                <Link :href="route('elezioni.vota', v.id)" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">{{ v.titolo }}</Link>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Dashboard staff: riepilogo compatto (solo se ha almeno un ruolo) -->
                <div v-else class="space-y-4">
                    <!-- Card Riepilogo: 3 KPI in un unico box -->
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="flex items-center gap-3">
                                <UserGroupIcon class="size-6 text-gray-400 dark:text-gray-500 shrink-0" aria-hidden="true" />
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Soci attivi</div>
                                    <div class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ members_count ?? 0 }}</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <BanknotesIcon class="size-6 text-gray-400 dark:text-gray-500 shrink-0" aria-hidden="true" />
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Incassi questo mese</div>
                                    <div class="text-xl font-semibold text-gray-900 dark:text-gray-100">€ {{ Number(payments_this_month ?? 0).toFixed(2) }}</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <ExclamationTriangleIcon class="size-6 text-gray-400 dark:text-gray-500 shrink-0" aria-hidden="true" />
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Soci non in regola</div>
                                    <div class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ members_not_in_regola ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Da fare: rimborsi da approvare (solo se ce ne sono) -->
                    <div v-if="refunds_pending_approval?.length" class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                        <h3 class="text-base font-medium text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                            <ClipboardDocumentListIcon class="size-5 text-amber-500 dark:text-amber-400" aria-hidden="true" />
                            Rimborsi da approvare
                        </h3>
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700 space-y-0">
                            <li v-for="r in refunds_pending_approval" :key="r.id" class="py-2 first:pt-0">
                                <Link :href="route('expense-refunds.show', r.id)" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                                    Rimborso #{{ r.id }}
                                </Link>
                                <span class="text-gray-600 dark:text-gray-300"> – {{ r.member ? r.member.cognome + ' ' + r.member.nome : '—' }} – € {{ Number(r.total ?? 0).toFixed(2) }} – {{ r.refund_date ? new Date(r.refund_date).toLocaleDateString('it-IT') : '—' }}</span>
                            </li>
                        </ul>
                        <Link :href="route('expense-refunds.index')" class="mt-2 inline-block text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Vedi tutti</Link>
                    </div>

                    <!-- Card Attività: eventi + incassi in due colonne -->
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-base font-medium text-gray-900 dark:text-gray-100 mb-2">Prossimi eventi</h3>
                                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <li v-for="e in upcoming_events" :key="e.id" class="py-1.5 first:pt-0">
                                        <Link :href="route('events.show', e.id)" class="text-indigo-600 dark:text-indigo-400 hover:underline text-sm">{{ e.title }}</Link>
                                        <span class="block text-xs text-gray-500 dark:text-gray-400">{{ e.start_at ? new Date(e.start_at).toLocaleString('it-IT') : '' }}</span>
                                    </li>
                                </ul>
                                <p v-if="!upcoming_events?.length" class="py-1.5 text-sm text-gray-500 dark:text-gray-400">Nessun evento in programma.</p>
                                <Link :href="route('events.index')" class="mt-2 inline-block text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Vedi tutti</Link>
                            </div>
                            <div>
                                <h3 class="text-base font-medium text-gray-900 dark:text-gray-100 mb-2">Ultimi incassi</h3>
                                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <li v-for="i in recent_incassi" :key="i.id" class="py-1.5 first:pt-0 text-sm">
                                        <span class="font-medium text-gray-900 dark:text-gray-100">€ {{ Number(i.amount ?? 0).toFixed(2) }}</span>
                                        <span class="text-gray-500 dark:text-gray-400 ms-2">{{ i.paid_at ? new Date(i.paid_at).toLocaleDateString('it-IT') : '' }}</span>
                                        <span class="text-gray-500 dark:text-gray-400 ms-2">{{ i.type === 'quota' ? 'Quota' : 'Donazione' }}</span>
                                        <span v-if="i.member" class="block text-xs text-gray-600 dark:text-gray-300">{{ i.member.cognome }} {{ i.member.nome }}</span>
                                    </li>
                                </ul>
                                <p v-if="!recent_incassi?.length" class="py-1.5 text-sm text-gray-500 dark:text-gray-400">Nessun incasso recente.</p>
                                <Link :href="route('incassi.index')" class="mt-2 inline-block text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Vedi tutti</Link>
                            </div>
                        </div>
                    </div>

                    <!-- Card Mandati in scadenza -->
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                        <h3 class="text-base font-medium text-gray-900 dark:text-gray-100 mb-2 flex items-center gap-2">
                            <CalendarDaysIcon class="size-5 text-gray-400 dark:text-gray-500" aria-hidden="true" />
                            Mandati in scadenza
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Organi il cui mandato scade nei prossimi 90 giorni.</p>
                        <div v-if="organiInScadenza?.length" class="space-y-3">
                            <div v-for="o in organiInScadenza" :key="o.id" class="border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                                <div class="flex flex-wrap items-baseline justify-between gap-2 mb-1">
                                    <Link :href="route('organi.show', o.id)" class="text-indigo-600 dark:text-indigo-400 hover:underline font-semibold text-sm">
                                        {{ o.nome }}
                                    </Link>
                                    <span class="text-xs text-red-600 dark:text-red-400 font-medium">
                                        Scade il {{ o.mandato_scadenza ? new Date(o.mandato_scadenza).toLocaleDateString('it-IT') : '—' }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                    Mandato dal {{ o.mandato_da ? new Date(o.mandato_da).toLocaleDateString('it-IT') : '—' }}, durata {{ o.durata_mesi }} {{ o.durata_mesi === 1 ? 'mese' : 'mesi' }}
                                </p>
                                <ul v-if="o.cariche_sociali?.length" class="space-y-0.5 text-xs">
                                    <li v-for="c in o.cariche_sociali" :key="c.id" class="flex flex-wrap items-center gap-x-2">
                                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ c.nome }}:</span>
                                        <template v-if="c.incarichi?.length">
                                            <span v-for="(inc, idx) in c.incarichi" :key="inc.id">
                                                <Link :href="route('members.show', inc.member_id)" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ inc.member?.cognome }} {{ inc.member?.nome }}</Link><span v-if="idx < c.incarichi.length - 1" class="text-gray-400">, </span>
                                            </span>
                                        </template>
                                        <span v-else class="text-gray-400 italic">non assegnata</span>
                                    </li>
                                </ul>
                                <p v-else class="text-xs text-gray-400 italic">Nessuna carica definita</p>
                            </div>
                        </div>
                        <p v-else class="py-2 text-sm text-gray-500 dark:text-gray-400">Nessun mandato in scadenza nei prossimi 90 giorni.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
