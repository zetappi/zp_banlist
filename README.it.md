# ZP BanList — Estensione phpBB 3.3.x

Estensione per phpBB 3.3.x che aggiunge una pagina pubblica con l'elenco completo dei ban attivi del forum, con paginazione, filtri, ordinamento e strumenti di gestione inline per amministratori e moderatori globali.

---

## Funzionalità

### Visualizzazione lista ban
- Elenco paginato di tutti i ban attivi (utente, IP, email)
- Colonne: **Utente / IP**, **Data ban**, **Scadenza**, **Motivazione**, **Tipo**
- Badge visivi per ban **Permanente** e ban **Temporaneo** con tempo rimanente (es. `3g 14h`, `45m`)
- Tooltip sulla scadenza esatta al passaggio del mouse sul badge
- Righe alternate (zebra striping) per una lettura più comoda
- I ban scaduti sono automaticamente esclusi dalla visualizzazione

### Filtri e ricerca
- Filtro per tipo: **Tutti** / **Temporaneo** / **Permanente**
- Ricerca per nome utente (ricerca parziale)
- Ordinamento per **Data ban** e **Scadenza** (crescente/decrescente)

### Modifica inline (admin e moderatori globali)
Cliccando sull'icona di modifica accanto a ogni ban si apre un form inline con:
- **Messaggio al bannato** — modifica del testo mostrato all'utente bannato
- **Scadenza ban** — date/time picker elegante (Flatpickr) con selezione giorno e ora
- **Checkbox Permanente** — abilita/disabilita il campo data; convertibile da temporaneo a permanente e viceversa
- **Salva** — salva via AJAX senza ricaricare la pagina; la riga si aggiorna live
- **Annulla** — chiude il form senza salvare
- **Interrompi ban** — revoca immediata del ban con richiesta di conferma; la riga sparisce dalla lista

### Logging
Ogni modifica e revoca viene registrata nel log del forum (ACP log e MCP log):
- Modifica del messaggio: `[ZP BanList] Modifica ban utente: ...`
- Modifica messaggio + scadenza: `[ZP BanList] Modifica ban utente: ... | Scadenza: vecchia → nuova`
- Revoca ban: `[ZP BanList] Ban revocato per utente: ...`

---

## Pannello di controllo ACP

### Impostazioni (`Gestione`)
| Impostazione | Tipo | Default | Descrizione |
|-------------|------|---------|-------------|
| `zp_banlist_per_page` | Intero | 20 | Numero di record per pagina |
| `zp_banlist_mod_edit` | Booleano | No | Abilita la modifica inline anche per i moderatori globali |

### Diagnostica
Pannello che verifica l'integrità dell'installazione:
- Stato di ogni migrazione
- Presenza delle chiavi di configurazione
- Registrazione del modulo ACP
- Stato attivazione estensione

---

## Requisiti

| Requisito | Versione |
|----------|---------|
| phpBB | ≥ 3.3.0 |
| PHP | ≥ 7.1.3 |

---

## Installazione

1. Copia la cartella `zp_banlist` in `ext/marcozp/` nella tua installazione phpBB
2. Accedi all'**ACP → Personalizza → Gestisci estensioni**
3. Trova **ZP BanList** e clicca **Abilita**
4. L'estensione esegue automaticamente le migrazioni del database
5. Configura le impostazioni in **ACP → ZP BanList → Gestione**

---

## Struttura file

```
ext/marcozp/zp_banlist/
├── acp/
│   ├── main_info.php               # Definizione moduli ACP
│   └── main_module.php             # Logica pannello ACP
├── adm/style/
│   ├── acp_zp_banlist.html         # Template ACP gestione
│   └── acp_zp_banlist_diagnostics.html  # Template ACP diagnostica
├── config/
│   ├── routing.yml                 # Definizione route
│   └── services.yml                # Servizi dependency injection
├── controller/
│   └── main_controller.php         # Controller pagina pubblica + AJAX
├── event/
│   └── main_listener.php           # Event listener phpBB
├── language/
│   ├── en/
│   │   ├── common.php
│   │   └── info_acp_zp_banlist.php
│   └── it/
│       ├── common.php
│       └── info_acp_zp_banlist.php
├── migrations/
│   ├── install_acp_module.php
│   ├── add_config.php
│   └── add_diagnostics_mode.php
└── styles/all/
    ├── template/
    │   ├── zp_banlist_body.html    # Template pagina lista ban
    │   └── event/
    │       ├── overall_header_head_append.html   # Iniezione asset Flatpickr
    │       └── overall_header_navigation_append.html  # Link menu navigazione
    └── theme/
        ├── zp_banlist.css          # Foglio di stile estensione
        ├── flatpickr.min.css       # Flatpickr (bundled)
        ├── flatpickr.min.js        # Flatpickr (bundled)
        └── flatpickr.it.js         # Flatpickr locale italiana
```

---

## Lingue supportate

| Lingua | Codice |
|--------|--------|
| Inglese | `en` |
| Italiano | `it` |

---

## Permessi richiesti

| Permesso | Descrizione |
|---------|-------------|
| `a_ban` | Visualizzazione lista e modifica inline (amministratori) |
| `m_` | Modifica inline (moderatori globali, se abilitato in ACP) |

---

## Licenza

GPL-2.0-only — vedi [LICENSE](LICENSE)

---

## Autore

**marcozp**
