# Guida per agenti AI – ETS OK

Documentazione per orientarsi rapidamente nella codebase. **ETS OK** è il gestionale per Enti del Terzo Settore. Lingua del progetto: **italiano** (backend, frontend, commenti, `lang/it`).

---

## 1. Panoramica e scopo

Gestionale per **Ente del Terzo Settore (ETS)**. Gestisce:

- **Soci e volontari**: anagrafica, tipi socio, stati (domanda, ammissione, cessazione, decesso, morosità, dimissioni, esclusione), libro soci
- **Cassa**: incassi (quote e donazioni), ricevute, rimborsi spese (approvazione → contabilizzazione automatica: una voce prima nota per riga; descrizione = descrizione riga + riferimento rimborso, es. «Rimborso spese #5»), fatture e invio SDI
- **Contabilità**: voci del rendiconto (Modello D, schema fisso in config), prima nota, report contabili, rendiconto di cassa (PDF)
- **Organi e votazioni**: organi, cariche sociali, incarichi, elezioni, candidature, voti
- **Patrimonio**: immobili e beni, magazzini, ubicazioni, articoli
- **Documenti**: verbali, documenti, allegati
- **Eventi**: eventi e iscrizioni
- **Impostazioni**: nome associazione, logo, quote, P.IVA, causali, email di test

---

## 2. Stack tecnologico

| Livello | Tecnologie |
|--------|------------|
| **Backend** | PHP 8.2+, Laravel 12, Laravel Jetstream (auth), Laravel Fortify, Sanctum, barryvdh/laravel-dompdf |
| **Frontend** | Vue 3 (Composition API, `<script setup>`), Inertia.js 2, Vite 7, Tailwind CSS 3, Heroicons, Ziggy (route) |
| **Database** | Configurabile (MySQL ecc.); migrations in `database/migrations/` |
| **Test** | Pest 4, pest-plugin-laravel, Laravel Pail |

---

## 3. Struttura delle cartelle principali

- **Backend**: `app/`  
  - `Http/Controllers/` – controller Inertia e logica richiesta/risposta  
  - `Models/` – Eloquent  
  - `Http/Middleware/` – HandleInertiaRequests, EnsureUserHasRole  
  - `Http/Requests/` – Form Request (es. StoreMemberRequest, UpdateMemberRequest)  
  - `Services/` – InvoiceSdiService, ReceiptService, RendicontoCassaService  
  - `Actions/` – Fortify/Jetstream (auth, profilo)  
  - `Policies/` – autorizzazioni (se usate)

- **Frontend**: `resources/js/`  
  - `Pages/` – una cartella per risorsa (es. `Organi/Show.vue`, `Members/Index.vue`)  
  - `Components/` – componenti riutilizzabili (pulsanti, input, modali, FlashToast…)  
  - `Layouts/AppLayout.vue` – layout principale con menu laterale  
  - `Composables/` – es. `useTheme.js`

- **Route**: `routes/web.php` – route nominate; area autenticata con `auth:sanctum` + `verified`; unica route pubblica significativa: `/` (Welcome).

- **Viste Blade**:  
  - `resources/views/app.blade.php` – root Inertia  
  - `resources/views/emails/`, `resources/views/receipts/`, `resources/views/rendiconto_cassa/` – email e template PDF

---

## 4. Modelli e dominio

- **User** (auth) ↔ **Member** (relazione 1:1 opzionale): un socio può avere un account per area self-service; `User` ha `member()` e `Member` ha `user()`.

- **Ruoli**: `admin`, `contabile`, `segreteria`, `socio` (tabelle `roles` e `role_user`). Middleware `role:admin,segreteria` ecc. registrato in `bootstrap/app.php`; usato nelle route o nei controller.

- **Modelli principali**:  
  Member, MemberType, Organo, CaricaSociale, Incarico, Elezione, Candidatura, Voto, Incasso, Receipt, Invoice, InvoiceLine, ExpenseRefund, RefundItem, PrimaNotaEntry, Property, Asset, Warehouse, WarehouseStock, Item, Location, Event, EventRegistration, Verbale, Document, Attachment, Settings, PaymentMethod, Supplier.

- **Contabilità**: le voci del rendiconto sono **fisse** (Modello D, GU 18-04-2020), definite in `config/rendiconto_cassa.php` e usate tramite `App\Services\RendicontoCassaSchema`. La prima nota (`PrimaNotaEntry`) ha `rendiconto_code` (nessun piano dei conti né mapping). Codici fissi per prima nota automatica: quota → INC_A_1, donazione → INC_A_4, rimborsi → EXP_A_5.

- **Relazioni chiave**:  
  - Organo → caricheSociali → incarichi → member  
  - Member → incassi, expenseRefunds, candidature, voti, registrazioni eventi  
  - Incassi, ricevute, fatture collegati a prima nota (rendiconto_code). **ExpenseRefund** → `primaNotaEntries()` (morphMany): le voci sono create all’approvazione, una per ogni RefundItem; descrizione movimento = descrizione riga + « – Rimborso spese #id ».  
  - Attachment polimorfico (Settings logo, documenti, ecc.)

---

## 5. Flusso richiesta/risposta (Inertia)

- I controller restituiscono `Inertia::render('Cartella/Page', [ ... props ])`.  
  Il path della pagina Vue deve corrispondere: `resources/js/Pages/Cartella/Page.vue` (es. `Organi/Show.vue`).

- **Dati condivisi** (ogni richiesta): definiti in `app/Http/Middleware/HandleInertiaRequests.php` → `share()`:  
  `user`, `userRoles`, `authMember`, `logo_url`, `flash`.  
  In frontend: `usePage().props` (es. per ruoli e flash).

- **Route nominate**: Ziggy espone `route('nome.route', parametri)` nel frontend. Usare sempre `route()` per link e submit (es. `route('organi.show', organo)`).

---

## 6. Convenzioni di codice

- **Controller**:  
  - Validazione spesso inline: `$request->validate([...])`; dove serve, usare Form Request in `Http/Requests/`.  
  - Redirect con messaggio: `redirect()->route(...)->with('flash', ['type' => 'success', 'message' => '...'])` (tipi: `success`, `error`, `info`).

- **Pagine Vue**:  
  - `<script setup>`, `defineProps` per i dati dalla pagina.  
  - Form: `useForm` di Inertia; invii con `form.post(route(...), { preserveScroll: true, onSuccess: ... })`.  
  - Navigazione: `router.get/post/put/delete` e `route()`.  
  - Layout comune: `AppLayout`; titolo con `Head` e `title`.

- **Alias**: in `jsconfig.json` `@/*` → `resources/js/*` (es. `@/Layouts/AppLayout.vue`, `@/Components/PrimaryButton.vue`).

- **Permessi**:  
  - Menu e sezioni in `AppLayout.vue` basate su `userRoles` e `route().current()`.  
  - Backend: middleware `role:admin,segreteria` (o simile) e, se necessario, `$user->hasRole(...)` / `$user->isAdmin()`.

---

## 7. Servizi e funzionalità speciali

- **Services**:  
  - `app/Services/InvoiceSdiService.php` – invio fatture allo SDI  
  - `app/Services/ReceiptService.php` – gestione ricevute  
  - `app/Services/RendicontoCassaService.php` – rendiconto di cassa (legge prima nota per `rendiconto_code`, struttura da schema MOD_D)  
  - `app/Services/RendicontoCassaSchema.php` – schema hardcoded Modello D, voci selezionabili, code→label
- **Config**: `config/rendiconto_cassa.php` – voci fisse del rendiconto (macro_areas e children)

- **Settings**: modello `Settings` (chiave-valore) per nome associazione, quote, P.IVA, causali, ecc. Logo: `Attachment::forSetting('logo')`.

- **PDF**: DomPDF per ricevute e rendiconto cassa; template in `resources/views/receipts/`, `resources/views/rendiconto_cassa/`.

- **Tema**: `resources/js/Composables/useTheme.js` e componente `ThemeTriStateButton` per tema chiaro/scuro/system.

---

## 8. Database e seed

- **Migrations**: in `database/migrations/`, numerate e descrittive.

- **Seeders**:  
  - `RoleSeeder` – ruoli: admin, contabile, segreteria, socio  
  - `MemberTypeSeeder`, `PaymentMethodSeeder`  
  - `DatabaseSeeder` – chiama i seeders sopra, imposta valori default in Settings e crea utente di test con ruolo admin.  
  - `InstallSeeder` – usato dall’installer web: ruoli, tipi socio, metodi pagamento, Settings e un solo utente admin (dati da config).

- **Installazione iniziale**: route `/install` (solo se `APP_KEY` vuoto); wizard per configurazione DB (MySQL o SQLite) e creazione utente amministratore; controller `InstallController`, viste in `resources/views/install/`, middleware `EnsureNotInstalled`.

---

## 9. Dove cercare per modifiche comuni

| Obiettivo | Dove intervenire |
|-----------|------------------|
| **Installazione iniziale** | Route `/install`, `App\Http\Controllers\InstallController`, `resources/views/install/`, middleware `EnsureNotInstalled`; configurazione DB e utente admin. |
| **Nuova pagina Inertia** | Aggiungere route in `routes/web.php`, metodo in controller con `Inertia::render('Cartella/NomePage', [...])`, creare `resources/js/Pages/Cartella/NomePage.vue`. |
| **Nuovo modello / CRUD** | Migration, Model in `app/Models/`, controller (e eventuale policy), route in `web.php`, pagine Vue in `Pages/Cartella/`. |
| **Menu laterale** | `resources/js/Layouts/AppLayout.vue` – sezioni e link in base a `route().current()` e `userRoles`. |
| **Messaggi flash** | Backend: `with('flash', ['type' => 'success'|'error'|'info', 'message' => '...'])`. Frontend: componente `FlashToast` e `page.props.flash`. |
| **Ruoli e permessi** | Middleware `app/Http/Middleware/EnsureUserHasRole.php`, registrazione in `bootstrap/app.php`, seed in `database/seeders/RoleSeeder.php`. |

---

## 10. File chiave

| Ruolo | Path |
|-------|------|
| Route web | `routes/web.php` |
| Installer | `app/Http/Controllers/InstallController.php`, `app/Http/Middleware/EnsureNotInstalled.php`, `resources/views/install/` |
| Dati condivisi Inertia | `app/Http/Middleware/HandleInertiaRequests.php` |
| Middleware ruoli | `app/Http/Middleware/EnsureUserHasRole.php` |
| Seed ruoli | `database/seeders/RoleSeeder.php` |
| Layout e menu | `resources/js/Layouts/AppLayout.vue` |
| Entry frontend | `resources/js/app.js` |
| Build frontend | `vite.config.js` |
| Alias JS | `jsconfig.json` |
