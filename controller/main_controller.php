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
        $filter_age    = $this->request->variable('filter_age', '5years');   // '5years' | 'all'
        $sort_field    = $this->request->variable('sort_field', 'ban_start');
        $sort_order    = $this->request->variable('sort_order', 'DESC');

        // Validate sort params
        $allowed_fields = ['ban_start', 'ban_end', 'ban_userid', 'last_post'];
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

        if ($filter_age === '5years')
        {
            $five_years_ago = $now - (5 * 365 * 24 * 60 * 60);
            $where_parts[] = 'b.ban_start >= ' . $five_years_ago;
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
        $sort_clause = '';
        if ($sort_field === 'last_post') {
            $sort_clause = 'ORDER BY last_post_time ' . $sort_order;
        } else {
            $sort_clause = 'ORDER BY b.' . $sort_field . ' ' . $sort_order;
        }

        $sql = 'SELECT b.ban_id, b.ban_userid, b.ban_ip, b.ban_email,
                       b.ban_start, b.ban_end, b.ban_reason, b.ban_give_reason,
                       u.username, u.user_colour,
                       (SELECT MAX(p.post_time) FROM ' . POSTS_TABLE . ' p WHERE p.poster_id = b.ban_userid) AS last_post_time,
                       (SELECT p.post_id FROM ' . POSTS_TABLE . ' p WHERE p.poster_id = b.ban_userid ORDER BY p.post_time DESC LIMIT 1) AS last_post_id
            FROM ' . BANLIST_TABLE . ' b
            LEFT JOIN ' . USERS_TABLE . ' u ON (b.ban_userid = u.user_id)
            ' . $where_sql . '
            ' . $sort_clause;

        $result = $this->db->sql_query_limit($sql, $per_page, $start);

        while ($row = $this->db->sql_fetchrow($result))
        {
            $ban_end_display = ($row['ban_end'] == 0)
                ? $this->language->lang('ZP_BANLIST_PERMANENT')
                : $this->user->format_date($row['ban_end']);

            $ban_end_raw = ($row['ban_end'] == 0) ? '' : date('Y-m-d\TH:i', (int) $row['ban_end']);

            // Extract post_id from ban_give_reason (format: "Ban assiociato al tuo post: %d")
            $post_id = 0;
            $post_url = '';
            if ($row['ban_give_reason']) {
                if (preg_match('/post: (\d+)/i', $row['ban_give_reason'], $matches)) {
                    $post_id = (int) $matches[1];
                } elseif (preg_match('/post #(\d+)/i', $row['ban_give_reason'], $matches)) {
                    $post_id = (int) $matches[1];
                }
                if ($post_id) {
                    $post_url = generate_board_url(false) . '/viewtopic.php?p=' . $post_id . '#p' . $post_id;
                }
            }

            // Get last post information
            $last_post_time = $row['last_post_time'] ?? 0;
            $last_post_id = $row['last_post_id'] ?? 0;
            $last_post_url = '';
            $last_post_display = '';
            if ($last_post_id > 0) {
                $last_post_url = generate_board_url(false) . '/viewtopic.php?p=' . $last_post_id . '#p' . $last_post_id;
                $last_post_display = $this->user->format_date($last_post_time);
            }

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
                'POST_ID'        => $post_id,
                'U_POST_LINK'    => $post_url,
                'U_USER_PROFILE' => ($row['ban_userid'] > 0) ? append_sid(generate_board_url() . '/memberlist.php', 'mode=viewprofile&u=' . (int) $row['ban_userid']) : '',
                'U_SAVE_REASON'  => $this->helper->route('marcozp_zp_banlist_save_reason'),
                'U_REVOKE_BAN'   => $this->helper->route('marcozp_zp_banlist_revoke_ban'),
                'LAST_POST_DISPLAY' => $last_post_display,
                'U_LAST_POST_LINK' => $last_post_url,
            ]);
        }
        $this->db->sql_freeresult($result);

        // Build sort/filter URLs via helper->route() — Symfony handles query params correctly
        $current = [
            'filter_type' => $filter_type,
            'filter_user' => $filter_user,
            'filter_age'  => $filter_age,
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
            'FILTER_AGE'              => $filter_age,
            'SORT_FIELD'              => $sort_field,
            'SORT_ORDER'              => $sort_order,
            'U_BANLIST'               => $this->helper->route('marcozp_zp_banlist_page'),
            'U_SORT_START_ASC'        => $this->helper->route('marcozp_zp_banlist_page', array_merge($current, ['sort_field' => 'ban_start', 'sort_order' => 'ASC',  'start' => 0])),
            'U_SORT_START_DESC'       => $this->helper->route('marcozp_zp_banlist_page', array_merge($current, ['sort_field' => 'ban_start', 'sort_order' => 'DESC', 'start' => 0])),
            'U_SORT_END_ASC'          => $this->helper->route('marcozp_zp_banlist_page', array_merge($current, ['sort_field' => 'ban_end',   'sort_order' => 'ASC',  'start' => 0])),
            'U_SORT_END_DESC'         => $this->helper->route('marcozp_zp_banlist_page', array_merge($current, ['sort_field' => 'ban_end',   'sort_order' => 'DESC', 'start' => 0])),
            'U_SORT_USER_ASC'         => $this->helper->route('marcozp_zp_banlist_page', array_merge($current, ['sort_field' => 'ban_userid', 'sort_order' => 'ASC',  'start' => 0])),
            'U_SORT_USER_DESC'        => $this->helper->route('marcozp_zp_banlist_page', array_merge($current, ['sort_field' => 'ban_userid', 'sort_order' => 'DESC', 'start' => 0])),
            'U_SORT_LAST_POST_ASC'    => $this->helper->route('marcozp_zp_banlist_page', array_merge($current, ['sort_field' => 'last_post', 'sort_order' => 'ASC',  'start' => 0])),
            'U_SORT_LAST_POST_DESC'   => $this->helper->route('marcozp_zp_banlist_page', array_merge($current, ['sort_field' => 'last_post', 'sort_order' => 'DESC', 'start' => 0])),
            'U_FILTER_ALL'            => $this->helper->route('marcozp_zp_banlist_page', array_merge($current, ['filter_type' => '', 'start' => 0])),
            'U_FILTER_TEMPORARY'      => $this->helper->route('marcozp_zp_banlist_page', array_merge($current, ['filter_type' => 'temporary', 'start' => 0])),
            'U_FILTER_PERMANENT'      => $this->helper->route('marcozp_zp_banlist_page', array_merge($current, ['filter_type' => 'permanent', 'start' => 0])),
            'U_FILTER_AGE_5YEARS'     => $this->helper->route('marcozp_zp_banlist_page', array_merge($current, ['filter_age' => '5years', 'start' => 0])),
            'U_FILTER_AGE_ALL'        => $this->helper->route('marcozp_zp_banlist_page', array_merge($current, ['filter_age' => 'all', 'start' => 0])),
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

    public function quick_ban($user_id, $post_id)
    {
        // Permission check: admin with a_ban OR global mod if mod_edit enabled
        $mod_edit_enabled = !empty($this->config['zp_banlist_mod_edit']);
        $can_edit = ($this->auth->acl_get('a_ban'))
            || ($mod_edit_enabled && $this->auth->acl_get('m_'));

        if ($this->user->data['user_id'] == ANONYMOUS || !$can_edit)
        {
            return new \Symfony\Component\HttpFoundation\JsonResponse(['error' => 'NO_AUTH'], 403);
        }

        if (!$user_id || !$post_id)
        {
            return new \Symfony\Component\HttpFoundation\JsonResponse(['error' => 'INVALID_PARAMS'], 400);
        }

        // Fetch username for display
        $sql = 'SELECT username FROM ' . USERS_TABLE . ' WHERE user_id = ' . (int) $user_id;
        $result = $this->db->sql_query($sql);
        $username = $this->db->sql_fetchfield('username');
        $this->db->sql_freeresult($result);

        if (!$username)
        {
            return new \Symfony\Component\HttpFoundation\JsonResponse(['error' => 'USER_NOT_FOUND'], 404);
        }

        // POST: save ban
        if ($this->request->is_set_post('form_submit'))
        {
            $ban_reason = $this->request->variable('ban_reason', '', true);
            $ban_end_str = $this->request->variable('ban_end_datetime', '');
            $is_permanent = $this->request->variable('is_permanent', 0);

            // Convert datetime to UTC timestamp
            $ban_end = 0;
            if (!$is_permanent && $ban_end_str)
            {
                try
                {
                    $timezone = new \DateTimeZone($this->user->data['user_timezone'] ?: 'UTC');
                    $ban_end_dt = new \DateTime($ban_end_str, $timezone);
                    $ban_end_dt->setTimezone(new \DateTimeZone('UTC'));
                    $ban_end = $ban_end_dt->getTimestamp();
                }
                catch (\Exception $e)
                {
                    $ban_end = 0;
                }
            }

            // Insert ban
            $ban_data = [
                'ban_userid'    => $user_id,
                'ban_start'    => time(),
                'ban_end'      => $is_permanent ? 0 : $ban_end,
                'ban_reason'   => $ban_reason,
                'ban_give_reason' => $ban_reason,
            ];

            $sql = 'INSERT INTO ' . BANLIST_TABLE . ' ' . $this->db->sql_build_array('INSERT', $ban_data);
            $this->db->sql_query($sql);

            // Log
            $log_params = [$username, $ban_reason];
            $this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ZP_BANLIST_QUICK_BAN', false, $log_params);
            $this->log->add('mod',   $this->user->data['user_id'], $this->user->ip, 'LOG_ZP_BANLIST_QUICK_BAN', false, $log_params);

            // Redirect back to the same page with success parameter and ban details
            $current_url = $this->helper->route('marcozp_zp_banlist_quick_ban', ['user_id' => $user_id, 'post_id' => $post_id]);
            $params = ['success' => '1'];
            if ($is_permanent) {
                $params['permanent'] = '1';
            } else {
                $params['ban_end'] = $ban_end;
            }
            return new \Symfony\Component\HttpFoundation\RedirectResponse($current_url . '?' . http_build_query($params));
        }

        // GET: render modal
        $default_reason = $this->language->lang('ZP_BANLIST_QUICK_BAN_DEFAULT_REASON', $post_id) . "\n";
        $success = $this->request->variable('success', 0);
        $is_permanent = $this->request->variable('permanent', 0);
        $ban_end = $this->request->variable('ban_end', 0);

        // Calculate remaining time for temporary bans
        $ban_remaining_display = '';
        if ($success && !$is_permanent && $ban_end > 0) {
            $delta = (int) $ban_end - time();
            if ($delta > 0) {
                $days = (int) ($delta / 86400);
                $hours = (int) (($delta % 86400) / 3600);
                $minutes = (int) (($delta % 3600) / 60);
                $ban_remaining_display = sprintf('%02d:%02d:%02d', $days, $hours, $minutes);
            }
        }

        $ban_end_display = '';
        if ($success) {
            if ($is_permanent) {
                $ban_end_display = $this->language->lang('ZP_BANLIST_PERMANENT');
            } elseif ($ban_end > 0) {
                $ban_end_display = $this->user->format_date((int) $ban_end);
            }
        }

        $this->template->assign_vars([
            'S_ZP_BANLIST_QUICK_BAN' => true,
            'S_ZP_BANLIST_SUCCESS'   => $success,
            'ZP_BANLIST_USERNAME'    => $username,
            'ZP_BANLIST_USER_ID'    => $user_id,
            'ZP_BANLIST_POST_ID'     => $post_id,
            'ZP_BANLIST_DEFAULT_REASON' => $default_reason,
            'U_ACTION'               => $this->helper->route('marcozp_zp_banlist_quick_ban', ['user_id' => $user_id, 'post_id' => $post_id]),
            'U_POST_REDIRECT'        => generate_board_url(false) . '/viewtopic.php?p=' . $post_id . '#p' . $post_id,
            'ZP_BANLIST_IS_PERMANENT' => $is_permanent,
            'ZP_BANLIST_BAN_END'      => $ban_end_display,
            'ZP_BANLIST_REMAINING'   => $ban_remaining_display,
        ]);

        return $this->helper->render('zp_banlist_quick_ban.html', $this->language->lang('ZP_BANLIST_QUICK_BAN_TITLE'));
    }
}
