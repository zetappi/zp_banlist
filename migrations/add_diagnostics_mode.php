<?php
/**
 * ZP BanList — Migration: Add Diagnostics Mode
 *
 * @package    marcozp/zp_banlist
 * @license    GPL-2.0-only
 */

namespace marcozp\zp_banlist\migrations;

class add_diagnostics_mode extends \phpbb\db\migration\migration
{
    static public function depends_on()
    {
        return ['\marcozp\zp_banlist\migrations\add_config'];
    }

    public function update_data()
    {
        return [
            ['module.add', ['acp', 'ACP_ZP_BANLIST_TITLE', [
                'module_basename' => '\marcozp\zp_banlist\acp\main_module',
                'modes'           => ['diagnostics'],
            ]]],
        ];
    }

    public function revert_data()
    {
        return [
            ['module.remove', ['acp', 'ACP_ZP_BANLIST_TITLE', [
                'module_basename' => '\marcozp\zp_banlist\acp\main_module',
                'modes'           => ['diagnostics'],
            ]]],
        ];
    }
}
