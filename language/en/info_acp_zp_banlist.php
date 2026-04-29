<?php
/**
 * ZP BanList — Language file (EN) — ACP
 *
 * @package    marcozp/zp_banlist
 * @license    GPL-2.0-only
 */

if (!defined('IN_PHPBB')) { exit; }
if (empty($lang) || !is_array($lang)) { $lang = []; }

$lang = array_merge($lang, [
    'ACP_ZP_BANLIST_TITLE'       => 'ZP BanList',
    'ACP_ZP_BANLIST_MANAGE'      => 'Settings',
    'ACP_ZP_BANLIST_EXPLAIN'     => 'Configure the Ban List display settings.',
    'ACP_ZP_BANLIST_SAVED'       => 'Settings saved successfully.',

    'ACP_ZP_BANLIST_PER_PAGE'         => 'Records per page',
    'ACP_ZP_BANLIST_PER_PAGE_EXPLAIN' => 'Number of ban records to show per page on the ban list.',

    'ACP_ZP_BANLIST_DIAGNOSTICS'      => 'Diagnostics',
    'ACP_ZP_BANLIST_DIAG_TITLE'       => 'ZP BanList — Diagnostics',
    'ACP_ZP_BANLIST_DIAG_EXPLAIN'     => 'Verifies that all migrations, config keys and ACP modules are correctly installed.',
    'ACP_ZP_BANLIST_DIAG_BANNER_OK'   => '✅ All checks passed — installation is complete and correct.',
    'ACP_ZP_BANLIST_DIAG_BANNER_ERR'  => '❌ One or more checks failed — see details below.',

    'ACP_ZP_BANLIST_DIAG_CHECK'       => 'Check',
    'ACP_ZP_BANLIST_DIAG_STATUS'      => 'Status',
    'ACP_ZP_BANLIST_DIAG_DETAIL'      => 'Detail',

    'ACP_ZP_BANLIST_DIAG_OK'          => 'OK',
    'ACP_ZP_BANLIST_DIAG_MISSING'     => 'MISSING',

    'ACP_ZP_BANLIST_DIAG_MIGRATION'   => 'Migration',
    'ACP_ZP_BANLIST_DIAG_CONFIG'      => 'Config key',
    'ACP_ZP_BANLIST_DIAG_MODULE'      => 'ACP Module',
    'ACP_ZP_BANLIST_DIAG_EXT'         => 'Extension active',

    'ACP_ZP_BANLIST_EDIT_REASON'         => 'Edit Ban Reason',
    'ACP_ZP_BANLIST_EDIT_REASON_TITLE'   => 'Edit Ban Message',
    'ACP_ZP_BANLIST_EDIT_REASON_LEGEND'  => 'Message shown to banned user',
    'ACP_ZP_BANLIST_EDIT_REASON_EXPLAIN' => 'Editing ban message for user: <strong>%s</strong>',
    'ACP_ZP_BANLIST_GIVE_REASON'         => 'Message to user',
    'ACP_ZP_BANLIST_GIVE_REASON_EXPLAIN' => 'This message is shown to the banned user when they try to log in. Leave empty to show no message.',
    'ACP_ZP_BANLIST_REASON_SAVED'        => 'Ban message updated successfully.',

    'ACP_ZP_BANLIST_PERMISSIONS'         => 'Edit Permissions',
    'ACP_ZP_BANLIST_MOD_EDIT'            => 'Allow global moderators',
    'ACP_ZP_BANLIST_MOD_EDIT_EXPLAIN'    => 'If enabled, global moderators can edit the ban message and expiry directly from the ban list.',

    'LOG_ZP_BANLIST_REASON_EDITED'          => '<strong>Ban message edited</strong> for user %1$s — message: %2$s',
    'LOG_ZP_BANLIST_REASON_AND_END_EDITED'  => '<strong>Ban message and expiry edited</strong> for user %1$s — message: %2$s — expiry: %3$s &rarr; %4$s',
    'LOG_ZP_BANLIST_REVOKED'                => '<strong>Ban revoked</strong> for user %1$s',
    'LOG_ZP_BANLIST_QUICK_BAN'             => '<strong>Quick ban</strong> assigned to user %1$s — reason: %2$s',
]);
