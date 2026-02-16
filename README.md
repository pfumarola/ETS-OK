# ETS OK

Software open source per la gestione di **Enti del Terzo Settore (ETS)**: anagrafica soci e volontari, cassa, contabilità, organi e votazioni, patrimonio, documenti ed eventi.

**Repository:** [github.com/pfumarola/ETS-OK](https://github.com/pfumarola/ETS-OK)

[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)
[![PHP 8.4+](https://img.shields.io/badge/PHP-8.4+-777BB4?logo=php)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel)](https://laravel.com)
[![Vue 3](https://img.shields.io/badge/Vue-3-4FC08D?logo=vue.js)](https://vuejs.org)

---

## Funzionalità

- **Soci e volontari** — Anagrafica, tipologie socio, stati (domanda, ammissione, cessazione, morosità, dimissioni, ecc.), libro soci
- **Cassa** — Incassi (quote e donazioni), ricevute, rimborsi spese con contabilizzazione automatica, fatture e invio SDI
- **Contabilità** — Prima nota, voci del rendiconto (Modello D), report contabili, rendiconto di cassa (PDF)
- **Organi e votazioni** — Organi, cariche sociali, incarichi, elezioni, candidature e voti
- **Patrimonio** — Immobili e beni, magazzini, sedi, articoli
- **Documenti** — Verbali, documenti, allegati, template
- **Eventi** — Gestione eventi e iscrizioni
- **Impostazioni** — Nome associazione, logo, quote, P.IVA, causali, email di test

Ruoli predefiniti: **admin**, **contabile**, **segreteria**, **socio** (con area self-service per i soci).

---

## Requisiti

- PHP 8.4+
- Composer
- Node.js (LTS) e npm
- Database MySQL/MariaDB o SQLite
- Estensioni PHP: mbstring, xml, ctype, json, bcmath, pdo, dom, fileinfo

---

## Installazione

### Da release (hosting)

1. Scarica l’ultima [release](https://github.com/pfumarola/ETS-OK/releases) (file `.zip`).
2. Estrai l’archivio nella root del sito; il **document root** del server deve puntare alla cartella `public`.
3. Apri nel browser l’URL di installazione: **`/install`**.
4. Completa il wizard: configurazione database (MySQL o SQLite) e creazione dell’utente amministratore.
5. Accedi con le credenziali inserite.

### Da sorgente (sviluppo)

```bash
git clone https://github.com/pfumarola/ETS-OK.git
cd ETS-OK
composer install
cp .env.example .env
php artisan key:generate
# Configura .env (DB, APP_URL, ecc.)
php artisan migrate
npm install
npm run build
# Oppure, per avviare tutto in un colpo: composer run dev
```

Per il primo utente admin puoi usare l’installer (`/install` se `APP_KEY` è vuoto) oppure:

```bash
php artisan db:seed
# Crea utente test@example.com / password (vedi DatabaseSeeder)
```

---

## Stack tecnologico

| Livello      | Tecnologie |
|-------------|------------|
| Backend     | PHP 8.4+, Laravel 12, Jetstream, Fortify, Sanctum, DomPDF |
| Frontend    | Vue 3 (Composition API), Inertia.js 2, Vite 7, Tailwind CSS 3, Heroicons, Ziggy |
| Database    | MySQL/MariaDB o SQLite (configurabile) |
| Test        | Pest 4, Laravel Pail |

---

## Sviluppo

- **Setup completo**: `composer run setup` (composer, .env, key, migrate, npm, build)
- **Ambiente dev** (server, queue, log, Vite): `composer run dev`
- **Test**: `composer run test` oppure `php artisan test`
- **Lingua**: italiano (backend, frontend, commenti)

Documentazione per sviluppatori e agenti AI: vedi [agent.md](agent.md).

---

## Release e deploy

Le release pronte per l’hosting vengono generate automaticamente al **merge sul branch `release`** tramite [GitHub Actions](.github/workflows/release.yml): build di produzione (Composer senza dev, build frontend), creazione dello zip e pubblicazione come [GitHub Release](https://docs.github.com/en/repositories/releasing-projects-on-github) con allegato.

La versione mostrata in app e nel tag di release è definita da **`APP_VERSION`** in `.env.example` (es. `1.0.0`).

---

## Contribuire

Contributi sono benvenuti: issue, pull request, miglioramenti alla documentazione. Per modifiche rilevanti è utile aprire prima una discussione in issue.

- Codice e commenti in **italiano**.
- Rispettare le convenzioni del progetto (vedi [agent.md](agent.md)).

---

## Sicurezza

Per segnalare vulnerabilità di sicurezza, apri una **security advisory** privata nel repository GitHub invece di una issue pubblica.

---

## Licenza

Questo progetto è open source sotto licenza [GNU GPL v3](https://www.gnu.org/licenses/gpl-3.0). Vedi il file [LICENSE](LICENSE) per il testo completo.
