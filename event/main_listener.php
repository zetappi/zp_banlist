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

        $this->template->assign_vars([
            'U_ZP_BANLIST'      => $this->helper->route('marcozp_zp_banlist_page'),
            'S_ZP_BANLIST_SHOW' => ($this->user->data['user_id'] != ANONYMOUS),
            'S_ZP_BANLIST_EDIT' => ($this->user->data['user_id'] != ANONYMOUS && $can_edit),
            'S_ZP_HIDE_BANNED_AVATAR' => $hide_banned_avatar,
            'U_ZP_BANNED_AVATAR' => $banned_avatar_url,
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
            $sql = 'SELECT group_id FROM ' . GROUPS_TABLE . '
                WHERE group_id IN (5, 4)'; // 5 = ADMIN, 4 = GLOBAL_MOD
            $result = $this->db->sql_query($sql);
            while ($row = $this->db->sql_fetchrow($result)) {
                $admin_groups[] = $row['group_id'];
            }
            $this->db->sql_freeresult($result);

            if (isset($admin_groups)) {
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
        ];

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
