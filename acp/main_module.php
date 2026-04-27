<?php
/**
 * ZP BanList — ACP Module
 *
 * @package    marcozp/zp_banlist
 * @license    GPL-2.0-only
 */

namespace marcozp\zp_banlist\acp;

class main_module
{
    public $u_action;

    public function main($id, $mode)
    {
        global $phpbb_container;

        $language = $phpbb_container->get('language');
        $language->add_lang('common',             'marcozp/zp_banlist');
        $language->add_lang('info_acp_zp_banlist', 'marcozp/zp_banlist');

        $template = $phpbb_container->get('template');
        $request  = $phpbb_container->get('request');
        $config   = $phpbb_container->get('config');

        $this->page_title = $language->lang('ACP_ZP_BANLIST_TITLE');

        switch ($mode)
        {
            case 'manage':
                $this->tpl_name = 'acp_zp_banlist';

                add_form_key('marcozp_zp_banlist_manage');

                if ($request->is_set_post('submit'))
                {
                    if (!check_form_key('marcozp_zp_banlist_manage'))
                    {
                        trigger_error('FORM_INVALID');
                    }

                    $per_page = $request->variable('zp_banlist_per_page', 20);
                    if ($per_page < 1)
                    {
                        $per_page = 1;
                    }
                    $config->set('zp_banlist_per_page', $per_page);
                    $config->set('zp_banlist_mod_edit', (int) $request->variable('zp_banlist_mod_edit', 0));

                    trigger_error($language->lang('ACP_ZP_BANLIST_SAVED') . adm_back_link($this->u_action));
                }

                $template->assign_vars([
                    'U_ACTION'             => $this->u_action,
                    'ZP_BANLIST_PER_PAGE'  => (int) ($config['zp_banlist_per_page'] ?? 20),
                    'ZP_BANLIST_MOD_EDIT'  => (bool) ($config['zp_banlist_mod_edit'] ?? 0),
                ]);
            break;

            case 'diagnostics':
                $this->tpl_name = 'acp_zp_banlist_diagnostics';

                $db = $phpbb_container->get('dbal.conn');

                $checks = [];

                // Migration checks
                $migrations = [
                    '\marcozp\zp_banlist\migrations\install_acp_module',
                    '\marcozp\zp_banlist\migrations\add_config',
                    '\marcozp\zp_banlist\migrations\add_diagnostics_mode',
                    '\marcozp\zp_banlist\migrations\add_edit_reason_mode',
                ];
                foreach ($migrations as $migration_name)
                {
                    $sql = 'SELECT migration_data_done
                        FROM ' . MIGRATIONS_TABLE . '
                        WHERE migration_name = \'' . $db->sql_escape($migration_name) . '\'';
                    $result = $db->sql_query($sql);
                    $row    = $db->sql_fetchrow($result);
                    $db->sql_freeresult($result);

                    $checks[] = [
                        'label'  => $language->lang('ACP_ZP_BANLIST_DIAG_MIGRATION') . ': ' . basename(str_replace('\\', '/', $migration_name)),
                        'status' => ($row && (int) $row['migration_data_done'] === 1),
                        'detail' => ($row && (int) $row['migration_data_done'] === 1)
                            ? $language->lang('ACP_ZP_BANLIST_DIAG_OK')
                            : $language->lang('ACP_ZP_BANLIST_DIAG_MISSING'),
                    ];
                }

                // Config key check
                $sql = 'SELECT config_name FROM ' . CONFIG_TABLE . ' WHERE config_name = \'zp_banlist_per_page\'';
                $result = $db->sql_query($sql);
                $row    = $db->sql_fetchrow($result);
                $db->sql_freeresult($result);
                $checks[] = [
                    'label'  => $language->lang('ACP_ZP_BANLIST_DIAG_CONFIG') . ': zp_banlist_per_page',
                    'status' => ($row !== false),
                    'detail' => ($row !== false)
                        ? $language->lang('ACP_ZP_BANLIST_DIAG_OK')
                        : $language->lang('ACP_ZP_BANLIST_DIAG_MISSING'),
                ];

                // ACP module check
                $sql = 'SELECT module_id FROM ' . MODULES_TABLE . '
                    WHERE module_basename = \'' . $db->sql_escape('\marcozp\zp_banlist\acp\main_module') . '\'
                    AND module_class = \'acp\'';
                $result = $db->sql_query($sql);
                $row    = $db->sql_fetchrow($result);
                $db->sql_freeresult($result);
                $checks[] = [
                    'label'  => $language->lang('ACP_ZP_BANLIST_DIAG_MODULE') . ': main_module',
                    'status' => ($row !== false),
                    'detail' => ($row !== false)
                        ? $language->lang('ACP_ZP_BANLIST_DIAG_OK')
                        : $language->lang('ACP_ZP_BANLIST_DIAG_MISSING'),
                ];

                // Extension active check
                $sql = 'SELECT ext_active FROM ' . EXT_TABLE . ' WHERE ext_name = \'marcozp/zp_banlist\'';
                $result = $db->sql_query($sql);
                $row    = $db->sql_fetchrow($result);
                $db->sql_freeresult($result);
                $checks[] = [
                    'label'  => $language->lang('ACP_ZP_BANLIST_DIAG_EXT'),
                    'status' => ($row && (int) $row['ext_active'] === 1),
                    'detail' => ($row && (int) $row['ext_active'] === 1)
                        ? $language->lang('ACP_ZP_BANLIST_DIAG_OK')
                        : $language->lang('ACP_ZP_BANLIST_DIAG_MISSING'),
                ];

                $all_ok = true;
                foreach ($checks as $check)
                {
                    if (!$check['status'])
                    {
                        $all_ok = false;
                    }
                    $template->assign_block_vars('checks', [
                        'LABEL'  => $check['label'],
                        'STATUS' => $check['status'],
                        'DETAIL' => $check['detail'],
                    ]);
                }

                $template->assign_vars([
                    'ZP_BANLIST_DIAG_ALL_OK' => $all_ok,
                    'U_ACTION'               => $this->u_action,
                ]);
            break;
        }
    }
}
