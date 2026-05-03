<?php
/**
 * ZP BanList — Event Listener
 *
 * @package    marcozp/zp_banlist
 * @license    GPL-2.0-only
 */

namespace marcozp\zp_banlist\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class main_listener implements EventSubscriberInterface
{
    protected $template;
    protected $language;
    protected $config;
    protected $helper;
    protected $user;
    protected $auth;
    protected $db;
    protected static $banned_users_cache = null;
    protected static $banned_users_cache_time = 0;

    public function __construct(
        \phpbb\template\template $template,
        \phpbb\language\language $language,
        \phpbb\config\config $config,
        \phpbb\controller\helper $helper,
        \phpbb\user $user,
        \phpbb\auth\auth $auth,
        \phpbb\db\driver\driver_interface $db
    )
    {
        $this->template = $template;
        $this->language = $language;
        $this->config   = $config;
        $this->helper   = $helper;
        $this->user     = $user;
        $this->auth     = $auth;
        $this->db       = $db;
    }

    /**
     * Get cached list of banned users with their usernames
     * Cache is refreshed every 60 seconds
     */
    protected function get_banned_users_cache()
    {
        $now = time();
        $cache_lifetime = 60; // 60 seconds

        // Refresh cache if expired or not set
        if (self::$banned_users_cache === null || ($now - self::$banned_users_cache_time) > $cache_lifetime) {
            self::$banned_users_cache = [];
            $sql = 'SELECT b.ban_userid, u.username FROM ' . BANLIST_TABLE . ' b
                LEFT JOIN ' . USERS_TABLE . ' u ON (b.ban_userid = u.user_id)
                WHERE b.ban_userid > 0
                AND (b.ban_end = 0 OR b.ban_end >= ' . $now . ')';
            $result = $this->db->sql_query($sql);
            while ($row = $this->db->sql_fetchrow($result)) {
                if ($row['username']) {
                    self::$banned_users_cache[$row['username']] = true;
                }
            }
            $this->db->sql_freeresult($result);
            self::$banned_users_cache_time = $now;
        }

        return self::$banned_users_cache;
    }

    static public function getSubscribedEvents()
    {
        return [
            'core.user_setup'           => 'load_language',
            'core.page_header'         => 'inject_banlist_url',
            'core.viewtopic_modify_post_row' => 'check_user_ban_status',
        ];
    }

    public function load_language($event)
    {
        $lang_set_ext = $event['lang_set_ext'];
        $lang_set_ext[] = [
            'ext_name' => 'marcozp/zp_banlist',
            'lang_set' => 'common',
        ];
        $event['lang_set_ext'] = $lang_set_ext;
    }

    public function inject_banlist_url($event)
    {
        $mod_edit_enabled = !empty($this->config['zp_banlist_mod_edit']);
        $can_edit = ($this->auth->acl_get('a_ban'))
            || ($mod_edit_enabled && $this->auth->acl_get('m_'));

        $hide_banned_avatar = !empty($this->config['zp_banlist_hide_banned_avatar']);
        $banned_avatar_file = $this->config['zp_banlist_banned_avatar'] ?? '';
        $banned_avatar_url = '';
        if ($banned_avatar_file) {
            $banned_avatar_url = 'ext/marcozp/zp_banlist/styles/all/theme/image/' . $banned_avatar_file;
        }

        $hide_banned_post = !empty($this->config['zp_banlist_hide_banned_post']);
        $is_mod_or_admin = $this->auth->acl_get('a_ban') || $this->auth->acl_get('m_');

        $this->template->assign_vars([
            'U_ZP_BANLIST'      => $this->helper->route('marcozp_zp_banlist_page'),
            'S_ZP_BANLIST_SHOW' => ($this->user->data['user_id'] != ANONYMOUS),
            'S_ZP_BANLIST_EDIT' => ($this->user->data['user_id'] != ANONYMOUS && $can_edit),
            'S_ZP_HIDE_BANNED_AVATAR' => $hide_banned_avatar,
            'U_ZP_BANNED_AVATAR' => $banned_avatar_url,
            'S_ZP_HIDE_BANNED_POST' => $hide_banned_post,
            'S_ZP_IS_MOD_OR_ADMIN' => $is_mod_or_admin,
        ]);
    }

    public function check_user_ban_status($event)
    {
        $user_id = $event['poster_id'];
        if ($user_id == ANONYMOUS) {
            return;
        }

        // Check if user is already banned
        $now = time();
        $sql = 'SELECT ban_id, ban_end FROM ' . BANLIST_TABLE . '
            WHERE ban_userid = ' . (int) $user_id . '
            AND (ban_end = 0 OR ban_end >= ' . $now . ')';
        $result = $this->db->sql_query_limit($sql, 1);
        $ban_row = $this->db->sql_fetchrow($result);
        $is_banned = (bool) $ban_row;
        $ban_end = $is_banned ? $ban_row['ban_end'] : 0;
        $this->db->sql_freeresult($result);

        // Check if user is admin or global mod (cannot be banned via quickban)
        $is_admin_or_global_mod = false;
        $sql = 'SELECT user_type, group_id FROM ' . USERS_TABLE . '
            WHERE user_id = ' . (int) $user_id;
        $result = $this->db->sql_query($sql);
        $user_row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        if ($user_row) {
            // Check if user is founder (user_type = 3) or admin (user_type = 2)
            if ($user_row['user_type'] == USER_FOUNDER || $user_row['user_type'] == USER_INACTIVE) {
                $is_admin_or_global_mod = true;
            }
            // Check if user is in admin or global mod groups
            // Note: In phpBB, group IDs can vary, but typically ADMIN=5 and GLOBAL_MOD=4
            // We use a query to find groups with appropriate permissions instead of hardcoded IDs
            $sql = 'SELECT group_id FROM ' . GROUPS_TABLE . '
                WHERE group_founder_manage = 1
                OR group_colour = \'AA0000\''; // Typical admin group color
            $result = $this->db->sql_query($sql);
            $admin_groups = [];
            while ($row = $this->db->sql_fetchrow($result)) {
                $admin_groups[] = (int) $row['group_id'];
            }
            $this->db->sql_freeresult($result);

            if (!empty($admin_groups)) {
                $sql = 'SELECT group_id FROM ' . USER_GROUP_TABLE . '
                    WHERE user_id = ' . (int) $user_id . '
                    AND group_id IN (' . implode(',', $admin_groups) . ')';
                $result = $this->db->sql_query($sql);
                $is_admin_or_global_mod = (bool) $this->db->sql_fetchrow($result);
                $this->db->sql_freeresult($result);
            }
        }

        $extra_data = [
            'S_ZP_USER_BANNED' => $is_banned,
            'S_ZP_USER_NOT_BANNABLE' => $is_admin_or_global_mod,
            'ZP_BAN_END' => $ban_end,
            'S_ZP_HIDE_BANNED_POST' => !empty($this->config['zp_banlist_hide_banned_post']),
            'S_ZP_HIDE_BANNED_SIGNATURE' => !empty($this->config['zp_banlist_hide_banned_signature']),
            'S_ZP_IS_MOD_OR_ADMIN' => ($this->auth->acl_get('a_ban') || $this->auth->acl_get('m_')),
        ];

        // Hide signature for banned users if option is enabled
        if ($is_banned && !empty($this->config['zp_banlist_hide_banned_signature'])) {
            $post_row = $event['post_row'];
            if (isset($post_row['SIGNATURE'])) {
                // Hide signature for everyone
                $post_row['SIGNATURE'] = '';
                $event['post_row'] = $post_row;
            }
        }

        // Handle post content display based on user ban status and permissions
        if ($is_banned && !empty($this->config['zp_banlist_hide_banned_post'])) {
            $post_row = $event['post_row'];
            if (isset($post_row['MESSAGE'])) {
                $is_mod_or_admin = ($this->auth->acl_get('a_ban') || $this->auth->acl_get('m_'));

                if ($is_mod_or_admin) {
                    // Show content with notice for moderators/admins
                    $notice = '<div class="zp-banned-post-notice zp-notice-mod"><i class="icon fa-shield fa-fw" aria-hidden="true"></i> ' . $this->language->lang('ZP_BANLIST_POST_VISIBLE_TO_MODS') . '</div>';
                    $post_row['MESSAGE'] = $notice . $post_row['MESSAGE'];
                } else {
                    // Hide content for regular users
                    $post_row['MESSAGE'] = '<div class="zp-banned-post-notice zp-notice-hidden"><i class="icon fa-exclamation-triangle fa-fw" aria-hidden="true"></i> ' . $this->language->lang('ZP_BANLIST_POST_HIDDEN_NOTICE') . '</div>';
                }

                // Remove quote button from post actions for banned users
                if (isset($post_row['U_QUOTE'])) {
                    $post_row['U_QUOTE'] = '';
                }
                if (isset($post_row['S_QUOTE'])) {
                    $post_row['S_QUOTE'] = false;
                }

                $event['post_row'] = $post_row;
            }
        }

        // Remove quotes from banned users in all posts (for non-mod/admin users)
        if (!empty($this->config['zp_banlist_hide_banned_post']) && !($this->auth->acl_get('a_ban') || $this->auth->acl_get('m_'))) {
            $post_row = $event['post_row'];
            if (isset($post_row['MESSAGE'])) {
                // Use cached list of banned usernames
                $banned_usernames = $this->get_banned_users_cache();

                if (!empty($banned_usernames)) {
                    // Remove BBCode quotes from banned users
                    $message = $post_row['MESSAGE'];
                    foreach ($banned_usernames as $username => $dummy) {
                        // Remove quotes with username
                        $message = preg_replace('/\[quote="' . preg_quote($username, '/') . '"\](.*?)\[\/quote\]/is', '<div class="zp-banned-quote-removed">' . $this->language->lang('ZP_BANLIST_QUOTE_REMOVED') . '</div>', $message);
                    }

                    $post_row['MESSAGE'] = $message;
                    $event['post_row'] = $post_row;
                }
            }
        }

        // Generate ban notice message
        if ($is_banned) {
            if ($ban_end == 0) {
                $extra_data['ZP_BAN_END_DISPLAY'] = '';
                $extra_data['ZP_BAN_NOTICE'] = $this->language->lang('ZP_BANLIST_NOTICE_PERMANENT');
            } else {
                $remaining = max(0, $ban_end - time());
                $days = floor($remaining / 86400);
                $hours = floor(($remaining % 86400) / 3600);
                $minutes = floor(($remaining % 3600) / 60);
                $extra_data['ZP_BAN_END_DISPLAY'] = sprintf('%02d:%02d:%02d', $days, $hours, $minutes);
                $extra_data['ZP_BAN_END_DATE'] = $this->user->format_date($ban_end);
                $extra_data['ZP_BAN_NOTICE'] = $this->language->lang('ZP_BANLIST_NOTICE_UNTIL', $extra_data['ZP_BAN_END_DISPLAY']);
            }
        }

        $event['post_row'] = array_merge($event['post_row'], $extra_data);
    }
}
