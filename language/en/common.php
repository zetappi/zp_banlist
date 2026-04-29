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
    'ZP_BANLIST_COL_TYPE'    => 'Type',
    'ZP_BANLIST_COL_REASON'  => 'Reason',
    'ZP_BANLIST_COL_POST'    => 'Post',
    'ZP_BANLIST_VIEW_POST_TITLE'=> 'View post',
    'ZP_BANLIST_MOD_DECISION_TITLE'=> 'Moderator decision',
    'ZP_BANLIST_COL_END'     => 'Expires',

    'ZP_BANLIST_TYPE_ALL'       => 'All',
    'ZP_BANLIST_TYPE_TEMPORARY' => 'Temporary',
    'ZP_BANLIST_TYPE_PERMANENT' => 'Permanent',

    'ZP_BANLIST_FILTER_LABEL' => 'Filter:',
    'ZP_BANLIST_FILTER_AGE_LABEL' => 'Age:',
    'ZP_BANLIST_FILTER_AGE_5YEARS' => 'Last 5 years',
    'ZP_BANLIST_FILTER_AGE_ALL' => 'All',
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
    'ZP_BANLIST_QUICK_BAN_TITLE'    => 'Quick Ban',
    'ZP_BANLIST_QUICK_BAN_REASON_LABEL' => 'Reason',
    'ZP_BANLIST_QUICK_BAN_DEFAULT_REASON' => 'Ban associated with your post: %d',
    'ZP_BANLIST_QUICK_BAN_PRESET_LABEL' => 'Quick duration',
    'ZP_BANLIST_QUICK_BAN_1H'       => '1h',
    'ZP_BANLIST_QUICK_BAN_24H'      => '24h',
    'ZP_BANLIST_QUICK_BAN_48H'      => '48h',
    'ZP_BANLIST_QUICK_BAN_7D'       => '7d',
    'ZP_BANLIST_QUICK_BAN_END_LABEL' => 'Expiry',
    'ZP_BANLIST_QUICK_BAN_SUBMIT'   => 'Apply ban',
    'ZP_BANLIST_QUICK_BAN_SUCCESS'   => 'Ban assigned successfully!',
    'ZP_BANLIST_QUICK_BAN_REDIRECT'  => 'You will be redirected to the post...',
    'ZP_BANLIST_QUICK_BAN_SECONDS'   => 'seconds',
    'ZP_BANLIST_USER_ALREADY_BANNED' => 'User already banned',
    'ZP_BANLIST_USER_NOT_BANNABLE'  => 'Not bannable',
    'ZP_BANLIST_NOTICE_UNTIL'       => 'User banned until %s',
    'ZP_BANLIST_NOTICE_PERMANENT'   => 'User permanently banned',
    'ZP_BANLIST_BAN_STATUS'         => 'Ban Status',
    'ZP_BANLIST_USER_BANNED'        => 'User Banned',
    'ZP_BANLIST_EXPIRY'             => 'remaining',
    'ZP_BANLIST_PERMANENT_UC'       => 'PERMANENT',
    'ZP_BANLIST_CONFIRM_TITLE'       => 'Confirm Ban',
    'ZP_BANLIST_CONFIRM_BAN'         => 'You are about to apply a BAN',
    'ZP_BANLIST_CONFIRM_DURATION'    => 'Duration',
    'ZP_BANLIST_CONFIRM_TO'          => 'to user',
    'ZP_BANLIST_CONFIRM_QUESTION'    => 'CONFIRM?',
    'ZP_BANLIST_CONFIRM_OK'          => 'OK',
    'ZP_BANLIST_CONFIRM_CANCEL'      => 'CANCEL',
    'ZP_BANLIST_INSERT_DURATION'     => 'Insert expiry',
    'ZP_BANLIST_INVALID_EXPIRY'      => 'Invalid expiry date',
    'ZP_BANLIST_INVALID_DURATION'    => 'Expiry date error',
    'ZP_BANLIST_REVOKE_TITLE'        => 'Revoke Ban',
    'ZP_BANLIST_REVOKE_MESSAGE'      => 'You are revoking the ban expiring on %s for user %s',
    'ZP_BANLIST_REVOKE_QUESTION'    => 'Confirm?',
    'ZP_BANLIST_REVOKE_CONFIRM'      => 'Confirm',
    'ZP_BANLIST_REVOKE_CANCEL'       => 'Cancel',
    'ACP_ZP_BANLIST_AVATAR'        => 'Banned user avatar',
    'ACP_ZP_BANLIST_HIDE_BANNED_AVATAR' => 'Hide banned user avatar',
    'ACP_ZP_BANLIST_HIDE_BANNED_AVATAR_EXPLAIN' => 'If enabled, the avatar of banned users will be hidden.',
    'ACP_ZP_BANLIST_BANNED_AVATAR' => 'Replacement avatar image',
    'ACP_ZP_BANLIST_BANNED_AVATAR_EXPLAIN' => 'Upload an image to replace the avatar of banned users.',
]);
