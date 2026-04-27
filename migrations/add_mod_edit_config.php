<?php
/**
 * ZP BanList — Migration: Add mod_edit config
 *
 * @package    marcozp/zp_banlist
 * @license    GPL-2.0-only
 */

namespace marcozp\zp_banlist\migrations;

class add_mod_edit_config extends \phpbb\db\migration\migration
{
    static public function depends_on()
    {
        return ['\marcozp\zp_banlist\migrations\add_edit_reason_mode'];
    }

    public function update_data()
    {
        return [
            ['config.add', ['zp_banlist_mod_edit', 0]],
        ];
    }

    public function revert_data()
    {
        return [
            ['config.remove', ['zp_banlist_mod_edit']],
        ];
    }
}
