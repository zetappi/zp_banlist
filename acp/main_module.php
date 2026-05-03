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

        $path_helper = $phpbb_container->get('path_helper');
        $phpbb_root_path = defined('PHPBB_ROOT_PATH') ? PHPBB_ROOT_PATH : $path_helper->get_phpbb_root_path();
        $phpbb_root_path = rtrim($phpbb_root_path, '/') . '/';

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
                    $config->set('zp_banlist_hide_banned_avatar', (int) $request->variable('zp_banlist_hide_banned_avatar', 0));
                    $config->set('zp_banlist_hide_banned_post', (int) $request->variable('zp_banlist_hide_banned_post', 0));
                    $config->set('zp_banlist_hide_banned_signature', (int) $request->variable('zp_banlist_hide_banned_signature', 0));

                    // Handle avatar selection from existing images
                    $selected_avatar = $request->variable('zp_banlist_avatar_select', '', true);
                    if ($selected_avatar !== '')
                    {
                        $avatar_path = $phpbb_root_path . 'ext/marcozp/zp_banlist/styles/all/theme/image/';
                        $allowed_ext = ['gif', 'jpg', 'jpeg', 'png', 'webp'];
                        $extension = strtolower(pathinfo($selected_avatar, PATHINFO_EXTENSION));
                        
                        if (in_array($extension, $allowed_ext) && file_exists($avatar_path . $selected_avatar))
                        {
                            $config->set('zp_banlist_banned_avatar', $selected_avatar);
                        }
                    }

                    // Handle new file upload
                    $upload_file = $request->file('zp_banlist_banned_avatar');
                    if (!empty($upload_file['name']) && empty($upload_file['error']))
                    {
                        $extension = strtolower(pathinfo($upload_file['name'], PATHINFO_EXTENSION));
                        $allowed_ext = ['gif', 'jpg', 'jpeg', 'png', 'webp'];

                        if (in_array($extension, $allowed_ext))
                        {
                            // Verify file is actually an image by checking MIME type and content
                            $finfo = @finfo_open(FILEINFO_MIME_TYPE);
                            if ($finfo)
                            {
                                $mime_type = @finfo_file($finfo, $upload_file['tmp_name']);
                                @finfo_close($finfo);

                                $allowed_mime_types = [
                                    'gif' => 'image/gif',
                                    'jpg' => 'image/jpeg',
                                    'jpeg' => 'image/jpeg',
                                    'png' => 'image/png',
                                    'webp' => 'image/webp',
                                ];

                                if (!in_array($mime_type, $allowed_mime_types))
                                {
                                    trigger_error($language->lang('ACP_ZP_BANLIST_UPLOAD_INVALID_TYPE'), E_USER_WARNING);
                                }
                                else
                                {
                                    // Additional check using getimagesize to verify it's a valid image
                                    $image_info = @getimagesize($upload_file['tmp_name']);
                                    if ($image_info === false)
                                    {
                                        trigger_error($language->lang('ACP_ZP_BANLIST_UPLOAD_INVALID_IMAGE'), E_USER_WARNING);
                                    }
                                    else
                                    {
                                        $avatar_path = $phpbb_root_path . 'ext/marcozp/zp_banlist/styles/all/theme/image/';
                                        if (!is_dir($avatar_path))
                                        {
                                            @mkdir($avatar_path, 0755, true);
                                        }

                                        $filename = 'banned_avatar.' . $extension;
                                        $full_path = $avatar_path . $filename;
                                        @unlink($full_path);
                                        if (copy($upload_file['tmp_name'], $full_path) || move_uploaded_file($upload_file['tmp_name'], $full_path))
                                        {
                                            $config->set('zp_banlist_banned_avatar', $filename);
                                        }
                                        else
                                        {
                                            trigger_error('Upload failed: tmp=' . $upload_file['tmp_name'] . ' -> ' . $full_path, E_USER_WARNING);
                                        }
                                    }
                                }
                            }
                            else
                            {
                                // Fallback if finfo is not available
                                $image_info = @getimagesize($upload_file['tmp_name']);
                                if ($image_info === false)
                                {
                                    trigger_error($language->lang('ACP_ZP_BANLIST_UPLOAD_INVALID_IMAGE'), E_USER_WARNING);
                                }
                                else
                                {
                                    $avatar_path = $phpbb_root_path . 'ext/marcozp/zp_banlist/styles/all/theme/image/';
                                    if (!is_dir($avatar_path))
                                    {
                                        @mkdir($avatar_path, 0755, true);
                                    }

                                    $filename = 'banned_avatar.' . $extension;
                                    $full_path = $avatar_path . $filename;
                                    @unlink($full_path);
                                    if (copy($upload_file['tmp_name'], $full_path) || move_uploaded_file($upload_file['tmp_name'], $full_path))
                                    {
                                        $config->set('zp_banlist_banned_avatar', $filename);
                                    }
                                    else
                                    {
                                        trigger_error('Upload failed: tmp=' . $upload_file['tmp_name'] . ' -> ' . $full_path, E_USER_WARNING);
                                    }
                                }
                            }
                        }
                        else
                        {
                            trigger_error($language->lang('ACP_ZP_BANLIST_UPLOAD_INVALID_EXTENSION'), E_USER_WARNING);
                        }
                    }

                    trigger_error($language->lang('ACP_ZP_BANLIST_SAVED') . adm_back_link($this->u_action));
                }

                $banned_avatar_file = $config['zp_banlist_banned_avatar'] ?? '';
                $banned_avatar_url = '';
                if ($banned_avatar_file)
                {
                    $banned_avatar_url = '../ext/marcozp/zp_banlist/styles/all/theme/image/' . $banned_avatar_file;
                }

                // Get list of existing images in the directory
                $avatar_path = $phpbb_root_path . 'ext/marcozp/zp_banlist/styles/all/theme/image/';
                $existing_images = [];
                $allowed_ext = ['gif', 'jpg', 'jpeg', 'png', 'webp'];
                if (is_dir($avatar_path))
                {
                    $files = scandir($avatar_path);
                    foreach ($files as $file)
                    {
                        if ($file !== '.' && $file !== '..')
                        {
                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                            if (in_array($ext, $allowed_ext))
                            {
                                $existing_images[] = $file;
                            }
                        }
                    }
                }
                sort($existing_images);

                foreach ($existing_images as $image)
                {
                    $template->assign_block_vars('avatar_images', [
                        'FILENAME' => $image,
                        'URL' => '../ext/marcozp/zp_banlist/styles/all/theme/image/' . $image,
                        'SELECTED' => ($image === $banned_avatar_file),
                    ]);
                }

                $template->assign_vars([
                    'U_ACTION'                  => $this->u_action,
                    'ZP_BANLIST_PER_PAGE'       => (int) ($config['zp_banlist_per_page'] ?? 20),
                    'ZP_BANLIST_MOD_EDIT'       => (bool) ($config['zp_banlist_mod_edit'] ?? 0),
                    'ZP_BANLIST_HIDE_BANNED_AVATAR' => (bool) ($config['zp_banlist_hide_banned_avatar'] ?? 1),
                    'ZP_BANLIST_HIDE_BANNED_POST' => (bool) ($config['zp_banlist_hide_banned_post'] ?? 0),
                    'ZP_BANLIST_HIDE_BANNED_SIGNATURE' => (bool) ($config['zp_banlist_hide_banned_signature'] ?? 0),
                    'ZP_BANLIST_BANNED_AVATAR_URL' => $banned_avatar_url,
                    'S_HAS_AVATAR_IMAGES' => count($existing_images) > 0,
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
