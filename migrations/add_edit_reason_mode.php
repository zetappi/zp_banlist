<?php
/**
 * ZP BanList — Migration: Add Edit Reason ACP Mode
 *
 * @package    marcozp/zp_banlist
 * @license    GPL-2.0-only
 */

namespace marcozp\zp_banlist\migrations;

class add_edit_reason_mode extends \phpbb\db\migration\migration
{
    static public function depends_on()
    {
        return ['\marcozp\zp_banlist\migrations\add_diagnostics_mode'];
    }

    public function update_data()
    {
        return [];
    }

    public function revert_data()
    {
        return [];
    }
}
