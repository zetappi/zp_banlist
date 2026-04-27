<?php
/**
 * ZP BanList — Language file (IT) — Frontend
 *
 * @package    marcozp/zp_banlist
 * @license    GPL-2.0-only
 */

if (!defined('IN_PHPBB')) { exit; }
if (empty($lang) || !is_array($lang)) { $lang = []; }

$lang = array_merge($lang, [
    'ZP_BANLIST_TITLE'       => 'Lista Ban',
    'ZP_BANLIST_LINK'        => 'Lista Ban',
    'ZP_BANLIST_EXPLAIN'     => 'Elenco di tutti gli utenti attualmente bannati da questo forum.',
    'ZP_BANLIST_PERMANENT'   => 'Permanente',
    'ZP_BANLIST_NO_RESULTS'  => 'Nessun ban trovato.',

    'ZP_BANLIST_COL_USER'    => 'Utente / IP',
    'ZP_BANLIST_COL_START'   => 'Data ban',
    'ZP_BANLIST_COL_END'     => 'Scadenza',
    'ZP_BANLIST_COL_REASON'  => 'Motivazione',
    'ZP_BANLIST_COL_TYPE'    => 'Tipo',

    'ZP_BANLIST_TYPE_ALL'       => 'Tutti',
    'ZP_BANLIST_TYPE_TEMPORARY' => 'Temporaneo',
    'ZP_BANLIST_TYPE_PERMANENT' => 'Permanente',

    'ZP_BANLIST_FILTER_LABEL' => 'Filtra:',
    'ZP_BANLIST_SORT_ASC'     => '▲',
    'ZP_BANLIST_SORT_DESC'    => '▼',

    'ZP_BANLIST_SEARCH_LABEL' => 'Cerca utente:',
    'ZP_BANLIST_SEARCH_BTN'   => 'Cerca',

    'ZP_BANLIST_PAGINATION_PREV' => '« Precedente',
    'ZP_BANLIST_PAGINATION_NEXT' => 'Successivo »',
    'ZP_BANLIST_PAGINATION_INFO' => 'Risultati %1$d - %2$d di %3$d',

    'ZP_BANLIST_EDIT_REASON_TITLE' => 'Modifica messaggio di ban',
    'ZP_BANLIST_SAVE_BTN'           => 'Salva',
    'ZP_BANLIST_CANCEL_BTN'         => 'Annulla',
    'ZP_BANLIST_EDIT_REASON_LABEL'  => 'Messaggio al bannato',
    'ZP_BANLIST_EDIT_END_LABEL'     => 'Scadenza ban',
    'ZP_BANLIST_REMAINING_DAYS'     => '%1$dg %2$dh',
    'ZP_BANLIST_REMAINING_HOURS'    => '%1$dh %2$dm',
    'ZP_BANLIST_REMAINING_MINUTES'  => '%1$dm',
    'ZP_BANLIST_REVOKE_BTN'         => 'Interrompi ban',
    'ZP_BANLIST_REVOKE_CONFIRM'     => 'Sei sicuro di voler interrompere il ban? Questa azione non può essere annullata.',
]);
