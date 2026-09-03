# Artemisys — project map

School management system for an Italian academy. Plain PHP 8.3 MVC, no framework.
Domain language is **Italian** — keep new identifiers Italian to match (`studenti`, `percorsi`, `esami`, `rette`).

Stack: PHP 8.3 + Apache · MySQL (PDO) · Bootstrap 5.3 + Font Awesome 6 (CDN) · vanilla JS.
Only Composer dep: PHPMailer. Deployed via Docker on Hetzner/Coolify, auto-deploys from `main`.

---

## 1. Request lifecycle

```
index.php  →  parse PATH_INFO  →  auth/role guards  →  switch($controller)
           →  controllers/<X>Controller.php  →  models/<Y>.php (PDO)
           →  require header.php + sidebar.php + <view>.php + footer.php
```

- **All routing lives in one file: [index.php](index.php)** — a single large `switch`. Adding a page = add a `case` there.
- URL shape: `index.php/<controller>/<action>/<param>` (works without mod_rewrite).
- `BASE_URL` = route prefix (links/redirects). `ASSETS_URL` = static files (CSS/JS/uploads). Both defined at the top of [index.php](index.php).
- Controllers `require` views directly — no template engine, no view object. Views read the controller's local variables.

## 2. Route → controller → view lookup table

| Route (`index.php/…`) | Controller | Views | Main model(s) |
|---|---|---|---|
| `switch` (default) | [SwitchController](controllers/SwitchController.php) | `views/switch/index.php` | — |
| `auth/login`, `logout`, `password-dimenticata`, `reimposta-password` | [AuthController](controllers/AuthController.php) | `views/auth/*` | User, PasswordReset |
| `home` (admin dashboard) | [didattica/DashboardController](controllers/didattica/DashboardController.php) | `views/didattica/dashboard/index.php` | several |
| `bacheca` | [BachecaController](controllers/BachecaController.php) | `views/bacheca/*` **and** `views/studente/bacheca*.php` | Comunicazione |
| `insegnanti` | [didattica/InsegnantiController](controllers/didattica/InsegnantiController.php) | `views/didattica/insegnanti/{index,create,detail}.php` | Insegnante |
| `studenti` | [didattica/StudentiController](controllers/didattica/StudentiController.php) | `views/didattica/studenti/{index,create,detail,pagella}.php` | Studente |
| `percorsi` | [didattica/PercorsiController](controllers/didattica/PercorsiController.php) | `views/didattica/percorsi/{index,create,detail,anno,materia,lezione}.php` | Percorso, Lezione |
| `anno-scolastico` | [didattica/AnnoScolasticoController](controllers/didattica/AnnoScolasticoController.php) | `views/didattica/anno-scolastico/index.php` | AnnoScolastico |
| `materie` | [didattica/MaterieController](controllers/didattica/MaterieController.php) | `views/didattica/materie/index.php` | Materia |
| `sedi` | [SediController](controllers/SediController.php) | `views/sedi/index.php` | Sede |
| `assegna-studenti` | [didattica/AssegnaStudentiController](controllers/didattica/AssegnaStudentiController.php) | `views/didattica/assegna_studenti.php` | Studente, Percorso |
| `lezioni` (read-only list) | [didattica/LezioniController](controllers/didattica/LezioniController.php) | `views/didattica/lezioni/index.php` | Lezione |
| `calendario-presenze` | [didattica/CalendarioController](controllers/didattica/CalendarioController.php) | `views/didattica/calendario/index.php` | Lezione |
| `esami` | [didattica/EsamiController](controllers/didattica/EsamiController.php) | `views/didattica/esami/{index,detail}.php` | Esame |
| `esami-di-stato-prova` | [didattica/EsamiDiStatoProvaController](controllers/didattica/EsamiDiStatoProvaController.php) | `views/didattica/esami-di-stato-prova/{index,detail}.php` | EsameDiStato |
| `tutti-gli-esami-di-stato` | [didattica/TuttiGliEsamiDiStatoController](controllers/didattica/TuttiGliEsamiDiStatoController.php) | `views/didattica/tutti-gli-esami-di-stato/index.php` | EsameDiStato |
| `rette` | [didattica/RetteController](controllers/didattica/RetteController.php) | `views/didattica/rette/{index,detail}.php` | Retta |
| `diplomi` | [didattica/DiplomiController](controllers/didattica/DiplomiController.php) | `views/didattica/diplomi/index.php` | Diploma |
| `eventi` | [didattica/EventiController](controllers/didattica/EventiController.php) | `views/didattica/eventi/{index,detail}.php` | Evento |
| `stage` | [didattica/StageController](controllers/didattica/StageController.php) | `views/didattica/stage/{index,detail}.php` | Stage |
| `importazioni` | [didattica/ImportazioniController](controllers/didattica/ImportazioniController.php) | `views/didattica/importazioni/index.php` | Lezione |
| `prove-orari` | [didattica/ProveOrariController](controllers/didattica/ProveOrariController.php) | `views/didattica/prove-orari/index.php` | EsameDiStato |
| `mie-lezioni`, `mie-materie`, `mio-calendario`, `miei-esami`, `profilo` | [docente/DocenteController](controllers/docente/DocenteController.php) | `views/docente/*` (`miei-esami` reuses `views/didattica/esami/index.php`) | Lezione, Esame |
| `studente[/lezioni,voti,presenze,eventi,rette,profilo,iscriviti]` | [studente/StudenteController](controllers/studente/StudenteController.php) | `views/studente/*` | Studente, Lezione, Evento, Retta |

**Not routed:** [EsamiDiStatoController](controllers/didattica/EsamiDiStatoController.php) is orphaned — `esami-di-stato` has no `case` in index.php. The live one is `esami-di-stato-prova`.

## 3. Where to change what

| Task | Go to |
|---|---|
| Add/change a URL or action | [index.php](index.php) `switch` block |
| Left nav links, menu order, role-specific menus | [views/layout/sidebar.php](views/layout/sidebar.php) |
| `<head>`, CDN links, page title | [views/layout/header.php](views/layout/header.php) |
| Global styles: sidebar, cards, tables, avatars | [public/css/style.css](public/css/style.css) — `/* ===== SECTION ===== */` banners, CSS vars at top |
| Global JS (sidebar toggle, submenus) | [public/js/app.js](public/js/app.js) |
| DB connection / env | [config/database.php](config/database.php) |
| Email transport & sender identity | [config/mail.php](config/mail.php) + [services/Mailer.php](services/Mailer.php) |
| Notification emails (content/recipients) | [services/Notifiche.php](services/Notifiche.php) |
| .docx generation (verbali, schede, pagelle) | [services/DocxTemplate.php](services/DocxTemplate.php), [DocumentoEsameDiStato.php](services/DocumentoEsameDiStato.php), [VerbaleEsameDiStato.php](services/VerbaleEsameDiStato.php); templates in `documenti/templates/` |
| SQL schema (reference dump) | [sql/artemisys.sql](sql/artemisys.sql) — **often stale**, see §5 |
| Docker/PHP limits/Apache workers | [Dockerfile](Dockerfile), [docker-compose.yaml](docker-compose.yaml) |
| Env var names | [.env.example](.env.example) |

## 4. Conventions (follow these when adding code)

- **Controller constructor** instantiates its model(s) and calls `$model->createTable()` / `createTables()`.
- **Index action pattern:**
  ```php
  $page = 'materie';            // drives sidebar active state
  $pageTitle = 'Materie';       // <title>
  $success = $_SESSION['flash_success'] ?? null;
  $error   = $_SESSION['flash_error']   ?? null;
  unset($_SESSION['flash_success'], $_SESSION['flash_error']);
  require BASE_PATH . 'views/layout/header.php';
  require BASE_PATH . 'views/layout/sidebar.php';
  require BASE_PATH . 'views/<module>/<x>/index.php';
  require BASE_PATH . 'views/layout/footer.php';
  ```
- **Write actions** (`store`/`update`/`delete`) are POST-only, set `$_SESSION['flash_success'|'flash_error']`, then `header('Location: ' . BASE_URL . '<route>'); exit;`. They never render.
- **Models** are thin PDO wrappers: `Database::getInstance()->getConnection()`, always prepared statements, `fetchAll()` returns assoc arrays. No ORM, no entity objects.
- **Student area** uses its own chrome: `views/studente/_header.php` / `_footer.php`, not `views/layout/`.
- Escape output with `htmlspecialchars()` in views. Flash messages deliberately allow `<strong>` and are echoed raw.

## 5. Schema — read this before touching the DB

`sql/artemisys.sql` is a **reference dump that lags behind the code.** The live schema is created and migrated by the models at runtime:
- `createTable()` / `createTables()` → `CREATE TABLE IF NOT EXISTS`
- Additive migrations → `try { $db->exec("ALTER TABLE … ADD COLUMN …"); } catch (Exception $e) {}`

**To add a column: add the `ALTER TABLE` line to the model's `createTables()`, and update `sql/artemisys.sql` for the record.** Migrations not in the dump include: `lezioni.ora_inizio/ora_fine/argomento/online/link_online` ([Lezione.php:61](models/Lezione.php:61)), `percorso_anno_materie.insegnante_id`, `studenti.user_id`, `password_resets`, `sedi`, `commissione_esami_prova`, `percorso_anni.codice`.

Core tables and how they connect:

```
users(ruolo: admin|docente|studente) ─┬─ insegnanti.user_id
                                      └─ studenti.user_id

anni_scolastici (exactly one `attivo`)
percorsi_accademici → percorso_anni → percorso_anno_materie (PAM) ─┬─ materie
                                                                   ├─ pam_insegnanti
                                                                   ├─ argomenti
                                                                   ├─ lezioni → lezione_presenze
                                                                   │            lezione_insegnanti
                                                                   │            lezione_allegati
                                                                   ├─ esami → esame_iscrizioni / _supervisori / _allegati
                                                                   └─ voti

studenti → studente_iscrizioni (→percorso) · studente_anni (→percorso_anno + anno_sc.)
         · studente_materie_bonus · rette → rate_pagamento · stage → stage_allegati

esami_di_stato → esame_di_stato_{prove, commissione, iscrizioni, allegati}
eventi → evento_iscrizioni · comunicazioni · diplomi_template
```

**`percorso_anno_materie` (PAM) is the hub of the domain** — lessons, exams, topics and grades all hang off a PAM id, never off a bare subject.

## 6. Roles and access control

All enforced in [index.php](index.php) before routing, from `$_SESSION['user_ruolo']`:

- **admin** — everything. Lands on `home`.
- **docente** — blocked by an explicit denylist (`insegnanti`, `studenti`, `rette`, `home`, …). Lands on `mie-lezioni`; `switch` redirects there too.
- **studente** — allowlist only: `studente`, `bacheca`, `auth`. Everything else redirects to `studente`.

When adding an admin route, **also add it to `$bloccatePerDocente`** or teachers will reach it.

## 7. Uploads

Two roots, easy to confuse:
- `public/uploads/{lezioni,rate,diplomi}` — web-servable, Docker volume.
- `uploads/{insegnanti,studenti,esami,esami-di-stato,stage}` — served through PHP, Docker volume.

Files are stored under generated names; the original name lives in the `*_allegati` tables (`filename` vs `original_name`).

## 8. Known gotchas

- **Dead route:** `insegnanti/upload-foto` ([index.php:204](index.php:204)) calls `$ctrl->uploadFoto()`, which does not exist — `InsegnantiController` only has the private `handleFotoUpload()`. Hitting it is a fatal error.
- **`views/didattica/esami-di-stato/`** is reachable only through the unrouted `EsamiDiStatoController`; editing it changes nothing live. Use `esami-di-stato-prova`.
- `EsamiDiStatoProvaController` carries both `scaricaVerbale()` and an older `scaricaVerbale2()`.
- Errors are **silenced** unless `APP_DEBUG=1` ([index.php:2](index.php:2)) — a blank page in prod usually means a PHP fatal.
- Root-level scratch scripts (`debug.php`, `prove_orari.php`, `rimuovi_barbara.php`, `tmp_read_odt.php`, `sql/*.php`) are one-off dev tools, not part of the app, and are web-reachable inside the container.
- Biggest files, expect slow edits: `views/didattica/esami-di-stato-prova/detail.php` (1174 l.), `views/didattica/studenti/detail.php` (1110 l.), `models/EsameDiStato.php`, `controllers/didattica/PercorsiController.php` (27 actions).
