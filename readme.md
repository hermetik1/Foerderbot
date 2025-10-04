🧠 KI_Kraft – Dual Chatbot Plugin (WordPress 6.7+)

**KI_Kraft** ist ein modernes WordPress-Plugin (Stand 2025), das zwei intelligente Chatbots vereint:
einen **FAQ-Bot** für Gäste und einen **Mitglieder-Bot** für eingeloggte Nutzer.  
Beide sind DSGVO-konform, vollständig white-label-fähig und nutzen moderne WordPress-Technologien
(React, REST API, Tailwind, i18n, Privacy Tools, Multisite Support).

---

## 🚀 Ziel
Ein vollständig modernisiertes, professionelles WordPress-Chatbot-Plugin
mit modularer Architektur und Fokus auf:
- Performance, Sicherheit, DSGVO-Konformität  
- Barrierefreiheit (A11y, WCAG AA)  
- White-Label-Fähigkeit (Farben, Logo, Texte)  
- Multisite-Kompatibilität  
- Zukunftssichere React-Admin-Oberfläche  

---

## 🧩 Hauptfunktionen

🤖 FAQ-Bot (für Gäste)

Der FAQ-Bot dient als öffentlich zugängliche Komponente, um häufige Besucherfragen automatisiert zu beantworten.
Er basiert auf einer lokal gespeicherten Wissensdatenbank, die aus mehreren hundert bis tausend häufig gestellten Fragen besteht (z. B. zur Vereinsgründung, Fördermittel, Mitgliedschaft etc.).
Die Suchfunktion nutzt eine Kombination aus Fuzzy Search und semantischer Vektorsuche (mittels OpenAI Embeddings oder lokalem Modell), um kontextähnliche Antworten zu erkennen, auch wenn die Formulierung abweicht.
Antworten werden priorisiert nach Relevanzscore (Cosine Similarity) zurückgegeben.
Der gesamte Prozess läuft lokal oder über einen datenschutzkonformen Endpoint, der keine personenbezogenen Daten überträgt.
Wenn keine ausreichende Antwort gefunden wird, schlägt der Bot automatisch vor, einen persönlichen Beratungstermin zu buchen oder eine Kontaktanfrage auszulösen.
Die Oberfläche ist so gestaltet, dass sie vollständig barrierefrei (WCAG AA) und auf Mobilgeräten optimiert ist.
Alle Texte, Labels und Fehlermeldungen sind internationalisiert (DE/EN).
Das Widget wird über einen Shortcode [ki_kraft_chatbot type="faq"] oder über ein Gutenberg-Block-Element eingebunden.
Das Design ist per White-Label konfigurierbar und übernimmt automatisch Farbvariablen aus den Admin-Einstellungen.
Anfragen werden anonymisiert in einer lokalen Log-Tabelle gespeichert, um die Bot-Performance zu verbessern.
Diese Logs enthalten keine IPs oder personenbezogene Metadaten.
Optional kann ein Admin im Dashboard unklare Fragen einsehen und daraus neue FAQ-Einträge erstellen lassen.
So wächst die Wissensbasis mit jeder Interaktion kontinuierlich weiter.
Das System verwendet ein adaptives Caching mit TTL von 24 Stunden, um häufige Anfragen effizienter zu bedienen.

🔒 Mitglieder-Bot (für eingeloggte Nutzer)

Der Mitglieder-Bot ist exklusiv für registrierte und eingeloggte Vereinsmitglieder verfügbar.
Er erkennt die Rolle des aktuellen Nutzers (subscriber, member, admin) und passt die Wissensbasis sowie die Berechtigungen dynamisch an.
Admins sehen z. B. interne Leitfäden, Satzungen oder vertrauliche Protokolle, während Mitglieder nur relevante Themenbereiche erhalten.
Die Wissensdatenbanken können in Form von Markdown-, PDF- oder DOCX-Dokumenten hochgeladen werden, die über einen Parser in den internen Embedding-Index integriert werden.
Uploads werden mit WordPress’ Standardfunktionen (wp_handle_upload) verarbeitet und DSGVO-konform gespeichert.
Nutzer können auch eigene Dokumente hochladen, die nur ihnen zur Verfügung stehen (z. B. individuelle Vereinsunterlagen).
Das Frontend bietet personalisierte Gesprächsverläufe mit persistenter History, die lokal in der Datenbank (wp_ki_kraft_conversations) gespeichert werden.
Alle Interaktionen sind auf Wunsch löschbar und können exportiert werden (DSGVO Exporter).
Admins erhalten im Dashboard ein Analytics-Panel mit Diagrammen (Recharts), das zeigt, welche Themen häufig nachgefragt werden, welche Antworten zu Rückfragen führen und wo Lücken bestehen.
Wiederkehrende, häufige Fragen werden automatisch als potenzielle FAQ-Kandidaten markiert.
Ein Cron-Job kann daraus regelmäßig neue FAQ-Einträge generieren, die nach Admin-Freigabe veröffentlicht werden.
Das Chat-UI ist reaktiv (React Hooks + Context) und speichert UI-States im LocalStorage für eine nahtlose UX.
Jede Nachricht ist semantisch markiert (role="user"/"assistant") und visuell klar differenziert.
Der Mitglieder-Bot kann auch Nachrichten direkt an die Geschäftsstelle weiterleiten, wenn menschliche Rücksprache erforderlich ist.
Alle Kommunikationskanäle sind verschlüsselt und protokolliert, ohne personenbezogene Inhalte offenzulegen.
Das Design fügt sich harmonisch ins White-Label-Konzept ein und nutzt die global definierten Farbvariablen.

⚙️ White-Label-System

Das White-Label-System ermöglicht vollständige Individualisierung des Chatbots für unterschiedliche Organisationen oder Subsites.
Administratoren können im Backend Logo, Produktname, Primär- und Sekundärfarbe, Favicon, Footer-Text und rechtliche Links individuell festlegen.
Diese Werte werden in einer zentralen Options-API gespeichert und über wp_localize_script() ins Frontend übergeben.
Das Design des Widgets wird dynamisch anhand dieser Werte mit CSS-Variablen generiert.
Admins sehen sofort eine Live-Vorschau ihrer Änderungen in der React-basierten Admin-Oberfläche.
Der Branding-Editor ist modular aufgebaut (LogoUploader, ColorPicker, TextControl, LinkFields).
Es gibt optional eine „Powered by KI_Kraft“-Kennzeichnung, die ein- oder ausgeschaltet werden kann.
Das System unterstützt Multisite-Konfigurationen: Jede Subsite kann ihr eigenes Branding pflegen, ohne andere zu beeinflussen.
Die Konfiguration kann exportiert und importiert werden (JSON-basiert), um Branding-Vorlagen zwischen Instanzen zu teilen.
Alle Texte sind internationalisiert, und Standardwerte fallen auf Englisch zurück.
Das White-Label-Modul ist eigenständig, aber alle anderen Plugin-Module beziehen ihre Designvariablen daraus.
Damit kann der gesamte Chatbot (Frontend, Sidebar, Buttons, Focus-Zustände) zentral umgefärbt werden.
Die Daten werden validiert, sanitisiert und sind multisitefähig.
Optional können Admins White-Label-Settings per REST an externe Installationen synchronisieren.
Ein Branding-Reset-Button stellt jederzeit die Standardwerte wieder her.

🔐 DSGVO / Privacy

Das Plugin ist vollständig DSGVO-konform und integriert tief in die WordPress Privacy-API.
Alle gespeicherten Sitzungen, Chat-Nachrichten und Logs können vom Nutzer exportiert oder gelöscht werden.
Dafür werden eigene Exporter- und Eraser-Callbacks über wp_privacy_register_exporter() und wp_privacy_register_eraser() bereitgestellt.
Jede Anfrage ist mit Nonce und Capability abgesichert, um Missbrauch zu verhindern.
Es existiert eine konfigurierbare Aufbewahrungsrichtlinie (Retention Policy), die alte Chat-Sitzungen nach X Tagen automatisch löscht.
Diese Richtlinie kann pro Site angepasst werden.
Keine Daten werden an externe Dienste gesendet, es sei denn, der Nutzer stimmt ausdrücklich zu (z. B. OpenAI-Optionen).
Opt-ins werden protokolliert und können durch Nutzer widerrufen werden.
Im Admin-Tab „Data & Privacy“ können Betreiber alle relevanten Datenschutzoptionen einstellen.
Das Plugin bindet automatisch einen Datenschutzhinweis im Frontend ein, z. B. unterhalb des Chatfensters.
Alle gespeicherten Daten sind pseudonymisiert und über eindeutige Session-IDs referenziert.
Die Tabellen verwenden keine personenbezogenen Namen oder E-Mails, sondern Hashes.
Admins können über Tools → Datenschutz prüfen, ob Export- und Eraser-Funktionen korrekt eingebunden sind.
Alle DSGVO-Mechanismen sind durch Unit-Tests (PHPUnit) abgedeckt.
Optional können Audit-Logs aktiviert werden, die Datenschutzaktionen nachvollziehbar machen.

📊 Analytics

Das Analytics-Modul ermöglicht eine tiefe Einsicht in die Nutzung des Chatbots.
Es sammelt anonymisierte Daten über die Anzahl der Sitzungen, die Häufigkeit bestimmter Themen, Reaktionszeiten und Nutzerfeedback.
Die Daten werden ausschließlich lokal gespeichert und nicht an Dritte übertragen.
Im Admin-Interface (React Dashboard) werden diese Daten visuell aufbereitet (Recharts).
Admins können erkennen, welche Fragen häufig unbeantwortet bleiben, welche Antworten positiv bewertet wurden und welche Themen dominieren.
Es gibt Filter nach Zeiträumen, Benutzerrollen und Chat-Typ (FAQ oder Mitglied).
Eine Heatmap zeigt Aktivitätszeiten über den Tag verteilt.
Die Daten sind DSGVO-konform anonymisiert, Nutzer-IDs werden gehasht.
Das Modul integriert sich in das White-Label-Design und passt sich optisch an Primär- und Sekundärfarben an.
Die REST-API liefert Echtzeit-Daten für Live-Widgets.
Admins können Reports als CSV oder JSON exportieren.
Optional kann ein täglicher Report an Admins per E-Mail gesendet werden.
Alle Datenpunkte haben ein Ablaufdatum (Retention).
Das Analytics-System dient auch als Grundlage für Auto-FAQ-Vorschläge.
Fehlerhafte REST-Antworten (5xx oder 429) werden protokolliert und optional im Dashboard hervorgehoben.

---

## 🧱 Technische Architektur

| Bereich | Technologie / Standard |
|----------|-------------------------|
| Admin-UI | React 18 + @wordpress/scripts + Tailwind |
| Backend | REST API (namespace `ki_kraft/v1`) |
| Datenbank | Custom Tables + CPT `ki_kraft_conversation` |
| Sicherheit | Nonces, Sanitizing, Rate-Limits, Caps |
| DSGVO | `wp_privacy_register_exporter` + `wp_privacy_register_eraser` |
| I18n | @wordpress/i18n mit JSON Locales (DE/EN) |
| Tests | PHPUnit + Vitest |
| Build | npm, webpack, wp-scripts |

---

## 🧭 Geplante Struktur

ki-kraft/
├─ ki-kraft.php
├─ includes/
│ ├─ class-ki-kraft-core.php
│ ├─ class-ki-kraft-rest.php
│ ├─ class-ki-kraft-faq.php
│ ├─ class-ki-kraft-member.php
│ ├─ class-ki-kraft-privacy.php
│ └─ class-ki-kraft-branding.php
├─ admin/
│ └─ App.js (React Interface)
├─ assets/
│ ├─ js/
│ └─ css/
├─ languages/
└─ README.md

yaml
Code kopieren

---

## 💡 Entwicklung

- Verwende `npm install` und `npm run build:plugin` für Builds.  
- PHP-Code validieren mit `phpcs --standard=WordPress`.  
- Tests ausführen via `npm run test` (Vitest) und `vendor/bin/phpunit`.

---

## 🔮 Geplante Erweiterungen
- **Sprachumschaltung (DE/EN)** im Frontend.  
- **Rollenbasierte Knowledge-Views** für Mitglieder.  
- **REST-Logging API** mit Retry-Mechanismus.  
- **Progressive Enhancement** mit Web Components.  
- **KI-Kraft Cloud Index Connector** (optional extern).

---

## 🧑‍💻 Mitwirken
Dieses Projekt wird gemeinschaftlich im Rahmen des gemeinnützigen Vereins **[KI Kraft](https://www.ki-kraft.at)** entwickelt.

---

© 2025 KI Kraft – Dual Chatbot Plugin. GPLv3 lizenziert.
