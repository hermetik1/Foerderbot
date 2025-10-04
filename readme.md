# 🧠 KI_Kraft – Dual Chatbot Plugin (WordPress 6.7+)

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

### 🤖 FAQ-Bot (für Gäste)
- Beantwortet Standardfragen (z. B. Vereinsgründung) aus lokaler Wissensbasis.  
- Fuzzy Search + semantische Vektorsuche (z. B. OpenAI Embeddings).  
- Wenn keine Antwort gefunden → Terminvereinbarung anbieten.  
- Keine personenbezogenen Daten werden gespeichert.

### 🔒 Mitglieder-Bot (für eingeloggte Nutzer)
- Zugriff auf interne Wissensdatenbanken, Richtlinien und Protokolle.  
- Rollenbasierte Sichtbarkeit (`member`, `admin`).  
- Upload eigener Dateien mit Kontextbezug.  
- Persönliche Gesprächsverläufe (lokal gespeichert).  
- Dashboard mit Analytics (häufige Fragen, Antwortqualität).  
- Optional: automatische FAQ-Generierung aus Logs.

### ⚙️ White-Label-System
- Anpassbares Logo, Name, Farben, Impressum, Footer, Datenschutzlinks.  
- Admin-UI mit Live-Preview des Brandings.  
- Mandantenfähig (Multisite-ready).

### 🔐 DSGVO / Privacy
- Exporter & Eraser vollständig integriert.  
- Retention Policy (automatisches Löschen nach Zeitraum).  
- Keine externen API-Aufrufe ohne Opt-in.  

### 📊 Analytics
- Dashboard mit Recharts: Fragehäufigkeit, Feedbackrate, Sitzungsdauer.  
- Logging anonymisiert.  

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

