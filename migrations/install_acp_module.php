<?php
/**
 * ZP BanList — Migration: Install ACP Module
 *
 * @package    marcozp/zp_banlist
 * @license    GPL-2.0-only
 */

namespace marcozp\zp_banlist\migrations;

class install_acp_module extends \phpbb\db\migration\migration
{
    static public function depends_on()
    {
        return ['\phpbb\db\migration\data\v330\v330'];
    }

    public function update_data()
    {
        return [
            ['module.add', ['acp', 'ACP_CAT_DOT_MODS', 'ACP_ZP_BANLIST_TITLE']],
            ['module.add', ['acp', 'ACP_ZP_BANLIST_TITLE', [
                'module_basename' => '\marcozp\zp_banlist\acp\main_module',
                'modes'           => ['manage'],
            ]]],
        ];
    }

    public function revert_data()
    {
        return [
            ['module.remove', ['acp', 'ACP_ZP_BANLIST_TITLE', [
                'module_basename' => '\marcozp\zp_banlist\acp\main_module',
                'modes'           => ['manage'],
            ]]],
            ['module.remove', ['acp', 'ACP_CAT_DOT_MODS', 'ACP_ZP_BANLIST_TITLE']],
        ];
    }
}
