# 🧠 KI_Kraft – Dual Chatbot Plugin (WordPress 6.7+)

**KI_Kraft** ist ein modernes WordPress‑Plugin (Stand 2025), das zwei Chatbots vereint: einen **FAQ‑Bot** für Gäste und einen **Mitglieder‑Bot** für eingeloggte Nutzer. Es ist DSGVO‑konform, vollständig **White‑Label‑fähig**, Multisite‑ready und setzt auf eine **React‑basierte Admin‑UI** mit REST‑First‑Architektur, i18n und A11y (WCAG AA).

> **Kurzprofil**: Performance • Sicherheit • DSGVO • Internationalisierung • Barrierefreiheit • White‑Label • Multisite • RAG (Retrieval‑Augmented Generation)

---

## 📦 Anforderungen

* WordPress **≥ 6.7** (Gutenberg/Block‑Editor aktiv)
* PHP **≥ 8.1**
* MySQL/MariaDB mit `InnoDB`; UTF8MB4
* Node.js **18/20** (für Build/Dev)

---

## 🚀 Hauptfunktionen (detailiert)

### 🤖 FAQ‑Bot (für Gäste)

* Öffentliche Komponente zur Beantwortung häufiger Besucherfragen (z. B. Vereinsgründung, Förderungen, Mitgliedschaft).
* Nutzt eine **lokale Wissensbasis** (hundert(e)/tausend FAQ‑Einträge) als Primärquelle.
* Kombiniert **Fuzzy Search** und **semantische Vektorsuche** (OpenAI Embeddings oder lokales Modell) zur robusten Treffergüte.
* Scoring per **Cosine Similarity**; Rückgabe nach Relevanz, optional mit Quellen‑Badges.
* Betrieb **ohne personenbezogene Daten** (Anfragen anonymisiert/pseudonymisiert).
* **Fallback**: Kein Treffer → Vorschlag zur **Terminvereinbarung/Kontakt** (konfigurierbare URL).
* Vollständig **i18n‑fähig** (DE/EN); alle Texte via Lokalisierung.
* **A11y**: Nachrichtenliste `role="log"`, `aria-live="polite"`; Tastaturnavigation & Focus‑Ringe.
* Einbindung via Shortcode oder Block:

  * Shortcode: `[ki_kraft_chatbot type="faq"]`
  * Block: **KI_Kraft → FAQ Chatbot** (mit Live‑Preview)
* **White‑Label** Styling über CSS‑Variablen (Farben, Abstände, Oberflächen) – serverseitig konfigurierbar.
* **Adaptive Caches** (TTL ~24 h) für häufige Fragen.
* Admin‑Option „Unklare Fragen sammeln“ → Vorschlagsliste für neue FAQs.

### 🔒 Mitglieder‑Bot (für eingeloggte Nutzer)

* Nur für **angemeldete** Nutzer; Cap‑Gate `read` + feinere Caps (z. B. `kk_upload_member_docs`, `kk_view_analytics`).
* **Rollenbasierter** Zugriff auf interne Wissensquellen (z. B. Satzung, Richtlinien, Protokolle). Rollen‑Scope: `public` / `members` / `role:<wp_role>`.
* Upload von Dokumenten (PDF/DOCX/MD/TXT) → **Extract → Chunk → Embed** → Index (DSGVO‑konform).
* **Persistente Gesprächsverläufe** je Nutzer (lokale DB), exportier‑/löschbar (DSAR).
* UI mit **Quellen‑Attribution**, Feedback (👍/👎), Handoff an Geschäftsstelle (Ticket/E‑Mail) bei komplexen Fällen.
* **Analytics‑Panel** (Recharts): Top‑Fragen, Unbeantwortete, Trends (7/30 Tage).
* **Auto‑FAQ‑Kandidaten**: Wiederkehrende offene Fragen werden vorgeschlagen; Admin kann daraus FAQ‑Einträge erzeugen (Entwurf → Freigabe).
* Reaktive UI (React Hooks/Context); State‑Persistenz (LocalStorage) für UX.
* Ende‑zu‑Ende **verschlüsselte** Kommunikation (HTTPS); Logs ohne personenbezogene Inhalte.

### ⚙️ White‑Label‑System

* Konfigurierbar: **Logo, Produktname, Primär/Sekundärfarbe, Favicon, Footer, Impressum, Datenschutzerklärung, „Powered by“‑Hinweis**.
* Live‑Preview im React‑Admin; Konfiguration per Options‑API + REST; Export/Import als **JSON Profile**.
* Multisite‑fähig: Jede Site verwaltet eigenes Branding.
* Zentrale CSS‑Variablen werden an Frontend/Widget geleitet; keine harten Styles.
* Validierung & Sanitizing sämtlicher Eingaben.

### 🔐 DSGVO / Privacy

* **Exporter** & **Eraser** via `wp_privacy_register_exporter/eraser` (Sitzungen, Nachrichten, optional Upload‑Metas).
* **Retention** (konfigurierbare Tage); Cron‑Cleanup.
* **Opt‑in** für externe KI‑Dienste; kein Tracking ohne Zustimmung.
* Frontend‑**Hinweis** (kurz): *„Ihre Eingaben werden an einen externen KI‑Dienst übermittelt. Keine sensiblen Daten eingeben.“* (anpassbar; DE/EN).
* Datenhaltung mit Pseudonymen/Hashes, Session‑IDs statt Klarnamen.
* PHPUnit‑Tests decken Export/Löschung/Retention ab.

### 📊 Analytics

* **Lokal** gespeicherte, anonymisierte Nutzungsdaten (Volumen, Themen, Feedback, Latenzen).
* **Dashboards** (Recharts) im Admin mit Zeitraum‑Filtern, Heatmap & CSV/JSON‑Export.
* **Unanswered Feed** als Input für Auto‑FAQ.
* REST‑Endpunkte liefern Echtzeitdaten; 429/5xx werden markiert und im Panel sichtbar.

---

## 🧱 Technische Architektur

| Bereich    | Technologie / Standard                                          |
| ---------- | --------------------------------------------------------------- |
| Admin‑UI   | React 18 + `@wordpress/scripts` + (optional) Tailwind           |
| Frontend   | Lightweight React/Preact (Widget) + CSS‑Variablen (Light/Dark)  |
| Backend    | WordPress **REST API** (`ki_kraft/v1`)                          |
| Daten      | Custom Tables + CPT `ki_kraft_conversation`                     |
| Sicherheit | Nonces, Caps, Prepared SQL, Output‑Escaping, Rate‑Limits        |
| DSGVO      | Exporter/Eraser, Retention‑Cron, Opt‑in‑Flows                   |
| i18n       | PHP: `__()/_x()` • JS: `wp.i18n` + `wp_localize_script` (DE/EN) |
| Tests      | PHPUnit (PHP) + Vitest (JS)                                     |
| Build      | npm (`wp-scripts`/Vite), ESLint, Prettier, PHPCS, PHPStan       |

---

## 🔌 Wichtige Hooks & REST‑Endpoints

| Typ    | Name/Route                           | Beschreibung                                          |
| ------ | ------------------------------------ | ----------------------------------------------------- |
| Action | `ki_kraft_register_routes`           | Registriert alle REST‑Routen beim Init                |
| Filter | `ki_kraft_chat_response`             | Post‑Processing/Moderation/Übersetzung der Antwort    |
| REST   | `POST /ki_kraft/v1/faq/query`        | FAQ‑Suche (Fuzzy + Vektor), liefert Antwort + Quellen |
| REST   | `POST /ki_kraft/v1/member/session`   | Legt Mitglieds‑Session an                             |
| REST   | `GET /ki_kraft/v1/member/sessions`   | Listet eigene Sessions (Pagination)                   |
| REST   | `POST /ki_kraft/v1/member/message`   | Fragt RAG mit Rollen‑Scope an                         |
| REST   | `POST /ki_kraft/v1/member/upload`    | Nimmt Upload an; Extract → Chunk → Embed              |
| REST   | `POST /ki_kraft/v1/member/handoff`   | Übergibt Gespräch an Geschäftsstelle (Ticket/E‑Mail)  |
| REST   | `GET /ki_kraft/v1/analytics/summary` | Aggregierte Analytics (Top/Unanswered/Trends)         |

---

## 🗂️ Projektstruktur (empfohlen)

```plaintext
ki-kraft/
├── ki-kraft.php
├── includes/
│   ├── class-ki-kraft-core.php
│   ├── class-ki-kraft-rest.php
│   ├── class-ki-kraft-faq.php
│   ├── class-ki-kraft-member.php
│   ├── class-ki-kraft-privacy.php
│   ├── class-ki-kraft-branding.php
│   └── class-ki-kraft-indexer.php        # Upload/Extract/Embeddings
├── admin/                                 # React-Admin-App
│   ├── index.tsx
│   ├── app/
│   │   ├── routes/
│   │   │   ├── Dashboard.tsx
│   │   │   ├── Settings/
│   │   │   │   ├── GeneralTab.tsx
│   │   │   │   ├── PrivacyTab.tsx
│   │   │   │   ├── WhiteLabelTab.tsx
│   │   │   │   ├── KnowledgeTab.tsx
│   │   │   │   └── AnalyticsTab.tsx
│   │   ├── components/
│   │   │   ├── forms/ (TextField/Switch/Color/File)
│   │   │   ├── charts/ (TrendChart/TopList)
│   │   │   ├── layout/ (Card/Page)
│   │   │   └── feedback/ (Notice/Spinner)
│   │   ├── lib/ (api/i18n/schema/store/utils)
│   │   └── styles/ (index.css/components.css/forms.css/charts.css/layout.css)
│   └── vite.config.ts (oder wp-scripts)
├── frontend/                               # Chat-Widget (Rail/Sidebar)
│   ├── index.ts
│   ├── ui/ (Rail/Sidebar/ChatWindow/MessageList/Composer/Badges)
│   ├── features/ (language-toggle/theme-toggle/rate-limit/accessibility)
│   ├── data/ (api.ts, i18n-client.ts)
│   └── styles/ (base.css/layout.css/components.css/themes.css)
├── assets/ (build output: js/css)
├── languages/ (ki-kraft-de_DE.*, ki-kraft-en_US.*)
├── tests/ (PHPUnit)
├── scripts/ (build-plugin.js/verify-zip.js/sync-version.js)
└── README.md
```

---

## 🔧 Installation & Quick‑Start

1. Repository klonen oder ZIP im WP‑Admin hochladen.
2. Aktivieren → **Admin‑Menü „KI Kraft“** erscheint.
3. **White‑Label** (Logo, Farben), **Privacy** (Opt‑ins/Retention) und **FAQ‑Wissensbasis** konfigurieren.
4. Optional: OpenAI API‑Key hinterlegen (Embeddings/Completions) — nur mit Opt‑in.
5. **Shortcode** in Seite/Beitrag einfügen:

   * FAQ‑Bot: `[ki_kraft_chatbot type="faq"]`
   * Mitglieder‑Bot: `[ki_kraft_chatbot type="member"]` (nur sichtbar für eingeloggte Nutzer)

---

## 🌍 Internationalisierung (i18n)

* Textdomain: `ki-kraft`.
* PHP‑Strings ausschließlich via `__()/_x()/_n()`.
* JS‑Strings über `wp_localize_script`/`@wordpress/i18n` (DE/EN Bundles); **Fallback EN**.
* Frontend‑**Language‑Toggle** im Rail (DE/EN), persistiert (user_meta oder LocalStorage).

---

## ♿ Barrierefreiheit (A11y)

* Nachrichtenliste: `role="log"`, `aria-live="polite"`.
* ESC schließt Sidebar; **Fokus‑Trap** aktiv; `:focus-visible` für Ringe.
* Farbkontraste ≥ **WCAG AA** (Light/Dark getestet).
* `prefers-reduced-motion` reduziert Animationen.

---

## 🛡️ Sicherheit

* Nonces (`wp_nonce_field`, `check_admin_referer`/`check_ajax_referer`).
* Caps: `manage_options`, `kk_upload_member_docs`, `kk_view_analytics` u. a.
* **Prepared SQL** und konsequentes **Escaping**.
* **Rate‑Limit** serverseitig; `429` inkl. `Retry‑After`.

---

## 🔁 RAG & HTTP‑Robustheit

* Embeddings **Pre‑Norm** gespeichert; **Top‑K** Vorsampling (z. B. 50) vor exakter Cosinus.
* Rollen‑Scoping (public/members/role) als SQL‑Grobfilter → Vektor‑Ranking.
* `ki_http_post_with_retry()` mit Timeouts, Exponential Backoff, `Retry‑After`‑Beachtung; Logs für Backoff‑Events.
* Optional: **pgvector‑Adapter** (Feature‑Flag) für große Korpora.

---

## 📈 Analytics

* Aggregationen: Top‑Fragen, Unbeantwortete, Volumen (täglich), Latenzen, Feedback‑Quote.
* REST‑Summary liefert Daten für Admin‑Charts; Export als CSV/JSON.
* Retention auch für Analytics; DSAR speichert keine PII.

---

## 🧪 Tests & CI/CD

* **PHPUnit**: REST‑Permissions, DSAR, Retention, Rate‑Limit, RAG‑Scope.
* **Vitest**: i18n‑Fallback, Rail‑Language‑Toggle, A11y‑DOM (role/aria), Retry‑Countdown.
* **GitHub Actions** Matrix: PHP 8.1/8.2/8.3 • Node 18/20; Steps: composer/npm install → phpcs/phpstan/phpunit/vitest → build‑verify.

---

## 🛠️ Build & Release

* `npm run build:plugin` erzeugt `dist/ki-kraft.zip` (nur Produktionsartefakte).
* Ausschlüsse: `.git`, `.github`, `tests`, `node_modules`, `vendor`.
* `scripts/verify-zip.js` prüft Paketinhalt; `scripts/sync-version.js` synchronisiert Versionen (PHP/JSON).

---

## 🤝 Mitwirken & Sicherheitshinweise

* **Contribution Guide** in `DEVELOPERS.md` (Coding Standards, Commits, Branching).
* Sicherheitsrelevante Meldungen **vertraulich** an `security@ki-kraft.at`.

---

## 📜 Lizenz

* GPLv3 • © 2025 **KI Kraft**.

---

## 🔮 Roadmap (Auszug)

* Web‑Component Wrapper für Widget (Shadow DOM).
* Optionaler KI_Kraft Cloud Index Connector.
* Admin‑Onboarding Wizard (erste Schritte + Live‑Checks).
* Tenant‑übergreifende Wissens‑Federation (Multisite‑Cluster).
