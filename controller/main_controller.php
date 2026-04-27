<?php
/**
 * ZP BanList — Frontend Controller
 *
 * @package    marcozp/zp_banlist
 * @license    GPL-2.0-only
 */

namespace marcozp\zp_banlist\controller;

class main_controller
{
    protected $helper;
    protected $template;
    protected $language;
    protected $config;
    protected $request;
    protected $db;
    protected $user;
    protected $auth;
    protected $log;

    public function __construct(
        \phpbb\controller\helper $helper,
        \phpbb\template\template $template,
        \phpbb\language\language $language,
        \phpbb\config\config $config,
        \phpbb\request\request $request,
        \phpbb\db\driver\driver_interface $db,
        \phpbb\user $user,
        \phpbb\auth\auth $auth,
        \phpbb\log\log_interface $log
    )
    {
        $this->helper   = $helper;
        $this->template = $template;
        $this->language = $language;
        $this->config   = $config;
        $this->request  = $request;
        $this->db       = $db;
        $this->user     = $user;
        $this->auth     = $auth;
        $this->log      = $log;
    }

    public function view()
    {
        // Require registered user
        if ($this->user->data['user_id'] == ANONYMOUS)
        {
            login_box($this->helper->route('marcozp_zp_banlist_page'), $this->language->lang('LOGIN_REQUIRED'));
        }

        // Pagination
        $per_page = (int) ($this->config['zp_banlist_per_page'] ?? 20);
        if ($per_page < 1)
        {
            $per_page = 20;
        }
        $start = $this->request->variable('start', 0);

        // Filter params
        $filter_type   = $this->request->variable('filter_type', '');   // 'temporary' | 'permanent' | ''
        $filter_user   = $this->request->variable('filter_user', '', true);
        $sort_field    = $this->request->variable('sort_field', 'ban_start');
        $sort_order    = $this->request->variable('sort_order', 'DESC');

        // Validate sort params
        $allowed_fields = ['ban_start', 'ban_end', 'ban_userid'];
        if (!in_array($sort_field, $allowed_fields, true))
        {
            $sort_field = 'ban_start';
        }
        $sort_order = ($sort_order === 'ASC') ? 'ASC' : 'DESC';

        // Build WHERE clause — always exclude expired bans
        $now = time();
        $where_parts = ['(b.ban_end = 0 OR b.ban_end >= ' . $now . ')'];

        if ($filter_type === 'temporary')
        {
            $where_parts[] = 'b.ban_end > 0';
        }
        else if ($filter_type === 'permanent')
        {
            $where_parts[] = 'b.ban_end = 0';
        }

        if ($filter_user !== '')
        {
            $where_parts[] = 'u.username_clean LIKE \'' . $this->db->sql_escape(utf8_clean_string($filter_user)) . '%\'';
        }

        $where_sql = count($where_parts) ? 'WHERE ' . implode(' AND ', $where_parts) : '';

        // Count total
        $sql_count = 'SELECT COUNT(b.ban_id) AS total
            FROM ' . BANLIST_TABLE . ' b
            LEFT JOIN ' . USERS_TABLE . ' u ON (b.ban_userid = u.user_id)
            ' . $where_sql;
        $result = $this->db->sql_query($sql_count);
        $total  = (int) $this->db->sql_fetchfield('total');
        $this->db->sql_freeresult($result);

        // Fetch rows
        $sql = 'SELECT b.ban_id, b.ban_userid, b.ban_ip, b.ban_email,
                       b.ban_start, b.ban_end, b.ban_reason, b.ban_give_reason,
                       u.username, u.user_colour
            FROM ' . BANLIST_TABLE . ' b
            LEFT JOIN ' . USERS_TABLE . ' u ON (b.ban_userid = u.user_id)
            ' . $where_sql . '
            ORDER BY b.' . $sort_field . ' ' . $sort_order;

        $result = $this->db->sql_query_limit($sql, $per_page, $start);

        while ($row = $this->db->sql_fetchrow($result))
        {
            $ban_end_display = ($row['ban_end'] == 0)
                ? $this->language->lang('ZP_BANLIST_PERMANENT')
                : $this->user->format_date($row['ban_end']);

            $ban_end_raw = ($row['ban_end'] == 0) ? '' : date('Y-m-d\TH:i', (int) $row['ban_end']);

            // Calculate remaining time for temporary bans
            $ban_remaining = '';
            if ($row['ban_end'] > 0)
            {
                $delta = (int) $row['ban_end'] - $now;
                if ($delta > 0)
                {
                    $days    = (int) ($delta / 86400);
                    $hours   = (int) (($delta % 86400) / 3600);
                    $minutes = (int) (($delta % 3600) / 60);

                    if ($days >= 2)
                    {
                        $ban_remaining = $this->language->lang('ZP_BANLIST_REMAINING_DAYS', $days, $hours);
                    }
                    else if ($delta >= 3600)
                    {
                        $ban_remaining = $this->language->lang('ZP_BANLIST_REMAINING_HOURS', ($days * 24 + $hours), $minutes);
                    }
                    else
                    {
                        $ban_remaining = $this->language->lang('ZP_BANLIST_REMAINING_MINUTES', $minutes);
                    }
                }
            }

            $this->template->assign_block_vars('banlist', [
                'BAN_ID'         => (int) $row['ban_id'],
                'USERNAME'       => $row['username'] ?? ($row['ban_email'] ?: ($row['ban_ip'] ?: ('#' . (int) $row['ban_id']))),
                'USER_COLOUR'    => $row['user_colour'],
                'BAN_START'      => $this->user->format_date($row['ban_start']),
                'BAN_END'        => $ban_end_display,
                'BAN_END_RAW'    => $ban_end_raw,
                'BAN_REASON'     => $row['ban_reason'],
                'BAN_GIVE_REASON'=> $row['ban_give_reason'],
                'IS_PERMANENT'   => ($row['ban_end'] == 0),
                'BAN_REMAINING'  => $ban_remaining,
                'U_SAVE_REASON'  => $this->helper->route('marcozp_zp_banlist_save_reason'),
                'U_REVOKE_BAN'   => $this->helper->route('marcozp_zp_banlist_revoke_ban'),
            ]);
        }
        $this->db->sql_freeresult($result);

        // Build sort/filter URLs via helper->route() — Symfony handles query params correctly
        $current = [
            'filter_type' => $filter_type,
            'filter_user' => $filter_user,
            'sort_field'  => $sort_field,
            'sort_order'  => $sort_order,
            'start'       => 0,
        ];

        $prev_start = max(0, $start - $per_page);
        $next_start = $start + $per_page;

        $page_end = min($start + $per_page, $total);

        $mod_edit_enabled = !empty($this->config['zp_banlist_mod_edit']);
        $can_edit = $this->auth->acl_get('a_ban')
            || ($mod_edit_enabled && $this->auth->acl_get('m_'));

        $this->template->assign_vars([
            'S_ZP_BANLIST_EDIT'       => $can_edit,
            'ZP_BANLIST_TOTAL'        => $total,
            'ZP_BANLIST_START'        => $start,
            'ZP_BANLIST_PER_PAGE'     => $per_page,
            'ZP_BANLIST_PAGE_END'     => $page_end,
            'ZP_BANLIST_HAS_PREV'     => ($start > 0),
            'ZP_BANLIST_HAS_NEXT'     => ($start + $per_page < $total),
            'FILTER_TYPE'             => $filter_type,
            'FILTER_USER'             => $filter_user,
            'SORT_FIELD'              => $sort_field,
            'SORT_ORDER'              => $sort_order,
            'U_BANLIST'               => $this->helper->route('marcozp_zp_banlist_page'),
            'U_SORT_START_ASC'        => $this->helper->route('marcozp_zp_banlist_page', array_merge($current, ['sort_field' => 'ban_start', 'sort_order' => 'ASC',  'start' => 0])),
            'U_SORT_START_DESC'       => $this->helper->route('marcozp_zp_banlist_page', array_merge($current, ['sort_field' => 'ban_start', 'sort_order' => 'DESC', 'start' => 0])),
            'U_SORT_END_ASC'          => $this->helper->route('marcozp_zp_banlist_page', array_merge($current, ['sort_field' => 'ban_end',   'sort_order' => 'ASC',  'start' => 0])),
            'U_SORT_END_DESC'         => $this->helper->route('marcozp_zp_banlist_page', array_merge($current, ['sort_field' => 'ban_end',   'sort_order' => 'DESC', 'start' => 0])),
            'U_FILTER_ALL'            => $this->helper->route('marcozp_zp_banlist_page', array_merge($current, ['filter_type' => '', 'start' => 0])),
            'U_FILTER_TEMPORARY'      => $this->helper->route('marcozp_zp_banlist_page', array_merge($current, ['filter_type' => 'temporary', 'start' => 0])),
            'U_FILTER_PERMANENT'      => $this->helper->route('marcozp_zp_banlist_page', array_merge($current, ['filter_type' => 'permanent', 'start' => 0])),
            'U_PREV_PAGE'             => $this->helper->route('marcozp_zp_banlist_page', array_merge($current, ['start' => $prev_start])),
            'U_NEXT_PAGE'             => $this->helper->route('marcozp_zp_banlist_page', array_merge($current, ['start' => $next_start])),
        ]);

        return $this->helper->render('zp_banlist_body.html', $this->language->lang('ZP_BANLIST_TITLE'));
    }

    public function save_reason()
    {
        // Permission check: admin with a_ban OR global mod if mod_edit enabled
        $mod_edit_enabled = !empty($this->config['zp_banlist_mod_edit']);
        $can_edit = ($this->auth->acl_get('a_ban'))
            || ($mod_edit_enabled && $this->auth->acl_get('m_'));

        if ($this->user->data['user_id'] == ANONYMOUS || !$can_edit)
        {
            return new \Symfony\Component\HttpFoundation\JsonResponse(['error' => 'NO_AUTH'], 403);
        }

        $ban_id     = $this->request->variable('ban_id', 0);
        $new_reason = $this->request->variable('ban_give_reason', '', true);
        $ban_end_str = $this->request->variable('ban_end_datetime', '');

        if (!$ban_id)
        {
            return new \Symfony\Component\HttpFoundation\JsonResponse(['error' => 'NO_BAN_ID'], 400);
        }

        // Fetch current ban row for log
        $sql = 'SELECT b.ban_end, u.username FROM ' . BANLIST_TABLE . ' b
            LEFT JOIN ' . USERS_TABLE . ' u ON b.ban_userid = u.user_id
            WHERE b.ban_id = ' . (int) $ban_id;
        $result  = $this->db->sql_query($sql);
        $ban_row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        if (!$ban_row)
        {
            return new \Symfony\Component\HttpFoundation\JsonResponse(['error' => 'BAN_NOT_FOUND'], 404);
        }

        // Convert date string to UTC timestamp using user timezone
        $new_ban_end = (int) $ban_row['ban_end'];
        $ban_end_display_new = null;
        if ($ban_end_str !== '')
        {
            try
            {
                $tz = new \DateTimeZone($this->user->data['user_timezone'] ?: 'UTC');
                $dt = new \DateTime($ban_end_str, $tz);
                $new_ban_end = (int) $dt->getTimestamp();
                $ban_end_display_new = $this->user->format_date($new_ban_end);
            }
            catch (\Exception $e)
            {
                return new \Symfony\Component\HttpFoundation\JsonResponse(['error' => 'INVALID_DATE'], 400);
            }
        }
        else if ($this->request->variable('ban_permanent', 0))
        {
            $new_ban_end = 0;
            $ban_end_display_new = $this->language->lang('ZP_BANLIST_PERMANENT');
        }

        $sql = 'UPDATE ' . BANLIST_TABLE . '
            SET ban_give_reason = \'' . $this->db->sql_escape($new_reason) . '\',
                ban_end = ' . $new_ban_end . '
            WHERE ban_id = ' . (int) $ban_id;
        $this->db->sql_query($sql);

        $old_ban_end_display = ($ban_row['ban_end'] == 0)
            ? $this->language->lang('ZP_BANLIST_PERMANENT')
            : $this->user->format_date((int) $ban_row['ban_end']);

        $end_changed = ($ban_end_display_new !== null) && ($new_ban_end !== (int) $ban_row['ban_end']);

        if ($end_changed)
        {
            $log_key    = 'LOG_ZP_BANLIST_REASON_AND_END_EDITED';
            $log_params = [$ban_row['username'] ?? '#' . $ban_id, $new_reason, $old_ban_end_display, $ban_end_display_new];
        }
        else
        {
            $log_key    = 'LOG_ZP_BANLIST_REASON_EDITED';
            $log_params = [$ban_row['username'] ?? '#' . $ban_id, $new_reason];
        }

        $this->log->add('admin', $this->user->data['user_id'], $this->user->ip, $log_key, false, $log_params);
        $this->log->add('mod',   $this->user->data['user_id'], $this->user->ip, $log_key, false, $log_params);

        $response = ['success' => true, 'new_reason' => $new_reason];
        if ($ban_end_display_new !== null)
        {
            $response['new_ban_end']   = $ban_end_display_new;
            $response['is_permanent']  = ($new_ban_end === 0);
            $response['ban_end_raw']   = ($new_ban_end === 0) ? '' : date('Y-m-d\TH:i', $new_ban_end);

            // Calculate remaining time for the updated ban
            $remaining_str = '';
            if ($new_ban_end > 0)
            {
                $delta = $new_ban_end - time();
                if ($delta > 0)
                {
                    $days    = (int) ($delta / 86400);
                    $hours   = (int) (($delta % 86400) / 3600);
                    $minutes = (int) (($delta % 3600) / 60);
                    if ($days >= 2)
                    {
                        $remaining_str = $this->language->lang('ZP_BANLIST_REMAINING_DAYS', $days, $hours);
                    }
                    else if ($delta >= 3600)
                    {
                        $remaining_str = $this->language->lang('ZP_BANLIST_REMAINING_HOURS', ($days * 24 + $hours), $minutes);
                    }
                    else
                    {
                        $remaining_str = $this->language->lang('ZP_BANLIST_REMAINING_MINUTES', $minutes);
                    }
                }
            }
            $response['ban_remaining'] = $remaining_str;
        }

        return new \Symfony\Component\HttpFoundation\JsonResponse($response);
    }

    public function revoke_ban()
    {
        $mod_edit_enabled = !empty($this->config['zp_banlist_mod_edit']);
        $can_edit = ($this->auth->acl_get('a_ban'))
            || ($mod_edit_enabled && $this->auth->acl_get('m_'));

        if ($this->user->data['user_id'] == ANONYMOUS || !$can_edit)
        {
            return new \Symfony\Component\HttpFoundation\JsonResponse(['error' => 'NO_AUTH'], 403);
        }

        $ban_id = $this->request->variable('ban_id', 0);
        if (!$ban_id)
        {
            return new \Symfony\Component\HttpFoundation\JsonResponse(['error' => 'NO_BAN_ID'], 400);
        }

        $sql = 'SELECT b.ban_end, b.ban_start, u.username FROM ' . BANLIST_TABLE . ' b
            LEFT JOIN ' . USERS_TABLE . ' u ON b.ban_userid = u.user_id
            WHERE b.ban_id = ' . (int) $ban_id;
        $result  = $this->db->sql_query($sql);
        $ban_row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        if (!$ban_row)
        {
            return new \Symfony\Component\HttpFoundation\JsonResponse(['error' => 'BAN_NOT_FOUND'], 404);
        }

        // Set ban_end to 1 second past epoch — phpBB treats ban_end > 0 && < time() as expired
        $revoke_time = 1;
        $sql = 'UPDATE ' . BANLIST_TABLE . ' SET ban_end = ' . $revoke_time . ' WHERE ban_id = ' . (int) $ban_id;
        $this->db->sql_query($sql);

        $log_params = [$ban_row['username'] ?? '#' . $ban_id];
        $this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ZP_BANLIST_REVOKED', false, $log_params);
        $this->log->add('mod',   $this->user->data['user_id'], $this->user->ip, 'LOG_ZP_BANLIST_REVOKED', false, $log_params);

        return new \Symfony\Component\HttpFoundation\JsonResponse(['success' => true]);
    }
}
