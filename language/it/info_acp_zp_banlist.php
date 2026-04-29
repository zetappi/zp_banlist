<?php
/**
 * ZP BanList — Language file (IT) — ACP
 *
 * @package    marcozp/zp_banlist
 * @license    GPL-2.0-only
 */

if (!defined('IN_PHPBB')) { exit; }
if (empty($lang) || !is_array($lang)) { $lang = []; }

$lang = array_merge($lang, [
    'ACP_ZP_BANLIST_TITLE'       => 'ZP BanList',
    'ACP_ZP_BANLIST_MANAGE'      => 'Impostazioni',
    'ACP_ZP_BANLIST_EXPLAIN'     => 'Configura le impostazioni di visualizzazione della Lista Ban.',
    'ACP_ZP_BANLIST_SAVED'       => 'Impostazioni salvate con successo.',

    'ACP_ZP_BANLIST_PER_PAGE'         => 'Record per pagina',
    'ACP_ZP_BANLIST_PER_PAGE_EXPLAIN' => 'Numero di ban da mostrare per pagina nella lista ban.',

    'ACP_ZP_BANLIST_DIAGNOSTICS'      => 'Diagnostica',
    'ACP_ZP_BANLIST_DIAG_TITLE'       => 'ZP BanList — Diagnostica',
    'ACP_ZP_BANLIST_DIAG_EXPLAIN'     => 'Verifica che tutte le migration, le chiavi di configurazione e i moduli ACP siano installati correttamente.',
    'ACP_ZP_BANLIST_DIAG_BANNER_OK'   => '✅ Tutti i controlli superati — installazione completa e corretta.',
    'ACP_ZP_BANLIST_DIAG_BANNER_ERR'  => '❌ Uno o più controlli falliti — vedi i dettagli qui sotto.',

    'ACP_ZP_BANLIST_DIAG_CHECK'       => 'Controllo',
    'ACP_ZP_BANLIST_DIAG_STATUS'      => 'Stato',
    'ACP_ZP_BANLIST_DIAG_DETAIL'      => 'Dettaglio',

    'ACP_ZP_BANLIST_DIAG_OK'          => 'OK',
    'ACP_ZP_BANLIST_DIAG_MISSING'     => 'MANCANTE',

    'ACP_ZP_BANLIST_DIAG_MIGRATION'   => 'Migration',
    'ACP_ZP_BANLIST_DIAG_CONFIG'      => 'Chiave config',
    'ACP_ZP_BANLIST_DIAG_MODULE'      => 'Modulo ACP',
    'ACP_ZP_BANLIST_DIAG_EXT'         => 'Extension attiva',

    'ACP_ZP_BANLIST_EDIT_REASON'         => 'Modifica Motivazione Ban',
    'ACP_ZP_BANLIST_EDIT_REASON_TITLE'   => 'Modifica Messaggio Ban',
    'ACP_ZP_BANLIST_EDIT_REASON_LEGEND'  => 'Messaggio mostrato all\'utente bannato',
    'ACP_ZP_BANLIST_EDIT_REASON_EXPLAIN' => 'Modifica del messaggio di ban per l\'utente: <strong>%s</strong>',
    'ACP_ZP_BANLIST_GIVE_REASON'         => 'Messaggio all\'utente',
    'ACP_ZP_BANLIST_GIVE_REASON_EXPLAIN' => 'Questo messaggio viene mostrato all\'utente bannato al tentativo di login. Lascia vuoto per non mostrare alcun messaggio.',
    'ACP_ZP_BANLIST_REASON_SAVED'        => 'Messaggio di ban aggiornato con successo.',

    'ACP_ZP_BANLIST_PERMISSIONS'         => 'Permessi di modifica',
    'ACP_ZP_BANLIST_MOD_EDIT'            => 'Consenti ai moderatori globali',
    'ACP_ZP_BANLIST_MOD_EDIT_EXPLAIN'    => 'Se abilitato, i moderatori globali potranno modificare il messaggio di ban e la scadenza direttamente dalla lista ban.',

    'LOG_ZP_BANLIST_REASON_EDITED'          => '<strong>Messaggio di ban modificato</strong> per l\'utente %1$s — messaggio: %2$s',
    'LOG_ZP_BANLIST_REASON_AND_END_EDITED'  => '<strong>Messaggio e scadenza ban modificati</strong> per l\'utente %1$s — messaggio: %2$s — scadenza: %3$s &rarr; %4$s',
    'LOG_ZP_BANLIST_REVOKED'                => '<strong>Ban interrotto</strong> per l\'utente %1$s',
    'LOG_ZP_BANLIST_QUICK_BAN'             => '<strong>Ban rapido</strong> assegnato all\'utente %1$s — motivazione: %2$s',
]);
