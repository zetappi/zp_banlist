<?php
/**
 * ZP BanList — Language file (EN) — Frontend
 *
 * @package    marcozp/zp_banlist
 * @license    GPL-2.0-only
 */

if (!defined('IN_PHPBB')) { exit; }
if (empty($lang) || !is_array($lang)) { $lang = []; }

$lang = array_merge($lang, [
    'ZP_BANLIST_TITLE'       => 'Ban List',
    'ZP_BANLIST_LINK'        => 'Ban List',
    'ZP_BANLIST_EXPLAIN'     => 'List of all users currently banned from this forum.',
    'ZP_BANLIST_PERMANENT'   => 'Permanent',
    'ZP_BANLIST_NO_RESULTS'  => 'No bans found.',

    'ZP_BANLIST_COL_USER'    => 'User / IP',
    'ZP_BANLIST_COL_START'   => 'Ban Date',
    'ZP_BANLIST_COL_END'     => 'Expires',
    'ZP_BANLIST_COL_REASON'  => 'Reason',
    'ZP_BANLIST_COL_TYPE'    => 'Type',

    'ZP_BANLIST_TYPE_ALL'       => 'All',
    'ZP_BANLIST_TYPE_TEMPORARY' => 'Temporary',
    'ZP_BANLIST_TYPE_PERMANENT' => 'Permanent',

    'ZP_BANLIST_FILTER_LABEL' => 'Filter:',
    'ZP_BANLIST_SORT_ASC'     => '▲',
    'ZP_BANLIST_SORT_DESC'    => '▼',

    'ZP_BANLIST_SEARCH_LABEL' => 'Search user:',
    'ZP_BANLIST_SEARCH_BTN'   => 'Search',

    'ZP_BANLIST_PAGINATION_PREV' => '« Previous',
    'ZP_BANLIST_PAGINATION_NEXT' => 'Next »',
    'ZP_BANLIST_PAGINATION_INFO' => 'Showing %1$d to %2$d of %3$d results',

    'ZP_BANLIST_EDIT_REASON_TITLE' => 'Edit ban message',
    'ZP_BANLIST_SAVE_BTN'           => 'Save',
    'ZP_BANLIST_CANCEL_BTN'         => 'Cancel',
    'ZP_BANLIST_EDIT_REASON_LABEL'  => 'Message to banned user',
    'ZP_BANLIST_EDIT_END_LABEL'     => 'Ban expiry',
    'ZP_BANLIST_REMAINING_DAYS'     => '%1$dd %2$dh',
    'ZP_BANLIST_REMAINING_HOURS'    => '%1$dh %2$dm',
    'ZP_BANLIST_REMAINING_MINUTES'  => '%1$dm',
    'ZP_BANLIST_REVOKE_BTN'         => 'Revoke ban',
    'ZP_BANLIST_REVOKE_CONFIRM'     => 'Are you sure you want to revoke this ban? This action cannot be undone.',
]);
