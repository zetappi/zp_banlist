<?php
/**
 * ZP BanList — Migration: Add Banned Avatar Config
 *
 * @package    marcozp/zp_banlist
 * @license    GPL-2.0-only
 */

namespace marcozp\zp_banlist\migrations;

class add_banned_avatar_config extends \phpbb\db\migration\migration
{
    static public function depends_on()
    {
        return ['\marcozp\zp_banlist\migrations\add_config'];
    }

    public function update_data()
    {
        return [
            ['config.add', ['zp_banlist_hide_banned_avatar', 1]],
            ['config.add', ['zp_banlist_banned_avatar', '']],
        ];
    }

    public function revert_data()
    {
        return [
            ['config.remove', ['zp_banlist_hide_banned_avatar']],
            ['config.remove', ['zp_banlist_banned_avatar']],
        ];
    }
}