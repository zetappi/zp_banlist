<?php
/**
 * ZP BanList — Migration: Add Config
 *
 * @package    marcozp/zp_banlist
 * @license    GPL-2.0-only
 */

namespace marcozp\zp_banlist\migrations;

class add_config extends \phpbb\db\migration\migration
{
    static public function depends_on()
    {
        return ['\marcozp\zp_banlist\migrations\install_acp_module'];
    }

    public function update_data()
    {
        return [
            ['config.add', ['zp_banlist_per_page', 20]],
        ];
    }

    public function revert_data()
    {
        return [
            ['config.remove', ['zp_banlist_per_page']],
        ];
    }
}
