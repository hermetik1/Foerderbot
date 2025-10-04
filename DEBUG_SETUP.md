# Debug-Konfiguration für WordPress Plugin

## Schnelle Aktivierung

Um das umfassende Logging zu aktivieren, füge eine der folgenden Zeilen in deine `wp-config.php` ein:

```php
// Vollständiges Debug-Logging für Copilot
define('COPILOT_DEBUG', true);

// Oder alternatives Plugin-spezifisches Debug
define('PLUGIN_DEBUG', true);

// Oder Standard WordPress Debug
define('WP_DEBUG', true);
```

## Was wird geloggt

Die Debug-Datei `debug-complete.log` erfasst automatisch:

### WordPress Core Events
- ✅ Admin-Initialisierung und Menü-Aufbau  
- ✅ Formular-Submissions (POST-Daten)
- ✅ Settings API Änderungen
- ✅ Datenbankoperationen (plugin-spezifisch)
- ✅ REST API und AJAX Calls
- ✅ Plugin Aktivierung/Deaktivierung

### Plugin-spezifische Events  
- ✅ Alle Admin-Post Actions (dcb_save_privacy_settings, etc.)
- ✅ Settings-Registrierung und -Validierung
- ✅ User-Aktionen mit Kontext (IP, Rolle, etc.)
- ✅ API-Calls mit Request/Response Details

### Fehler-Behandlung
- ✅ PHP Errors und Warnings (plugin-spezifisch)
- ✅ WordPress wp_die() Aufrufe
- ✅ Datenbank-Fehler
- ✅ Memory Usage pro Log-Eintrag

## Log-Format

```
[2025-10-05 12:34:56] [CATEGORY ] [file.php:123] [MEM:2.5MB] Message | Context: {"key":"value"}
```

## Verwendung im Code

```php
// Einfaches Logging
debug_log("Privacy settings loaded", 'PLUGIN');

// Mit Kontext
debug_log("Form validation failed", 'ERROR', ['field' => 'email', 'value' => $email]);

// Formular-Submissions
debug_log_form($_POST, 'PRIVACY_SETTINGS');

// User-Aktionen
debug_log_user("Settings saved", ['tab' => 'privacy', 'changes' => $changes]);

// API-Calls
debug_log_api('https://api.openai.com/chat', 'POST', $request_data, $response);
```

## Für GitHub Copilot optimiert

Das Log-Format ist speziell für AI-Analyse strukturiert:
- **Zeitstempel**: Eindeutige Chronologie
- **Kategorien**: Schnelle Problemidentifikation  
- **Caller Info**: Exakte Code-Lokalisierung
- **Memory Tracking**: Performance-Monitoring
- **JSON Context**: Strukturierte Daten-Analyse

## Automatische Rotation

- Log rotiert automatisch bei >5MB
- Behält die letzten 5 Backup-Dateien
- Verhindert Speicherplatz-Probleme

## Copilot Prompts

Verwende diese Prompts in GitHub für optimale Analyse:

### Fehleranalyse
```
Analysiere debug-complete.log und identifiziere alle ERROR und FORM_SUBMIT Events. 
Finde Patterns in fehlgeschlagenen Form-Submissions und schlage Code-Fixes vor.
```

### Performance-Analyse  
```
Überprüfe debug-complete.log auf Memory-Spikes und lange Ladezeiten. 
Identifiziere Bottlenecks in DB_QUERY und API_CALL Events.
```

### Workflow-Analyse
```
Trace den kompletten User-Flow von FORM_SUBMIT bis Settings-Speicherung in debug-complete.log.
Dokumentiere alle Plugin-Actions und WordPress Hooks die ausgeführt werden.
```