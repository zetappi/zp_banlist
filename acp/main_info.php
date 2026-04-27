<?php
/**
 * ZP BanList — ACP Info
 *
 * @package    marcozp/zp_banlist
 * @license    GPL-2.0-only
 */

namespace marcozp\zp_banlist\acp;

class main_info
{
    public function module()
    {
        return [
            'filename' => '\marcozp\zp_banlist\acp\main_module',
            'title'    => 'ACP_ZP_BANLIST_TITLE',
            'modes'    => [
                'manage' => [
                    'title' => 'ACP_ZP_BANLIST_MANAGE',
                    'auth'  => 'ext_marcozp/zp_banlist && acl_a_board',
                    'cat'   => ['ACP_ZP_BANLIST_TITLE'],
                ],
                'diagnostics' => [
                    'title' => 'ACP_ZP_BANLIST_DIAGNOSTICS',
                    'auth'  => 'ext_marcozp/zp_banlist && acl_a_board',
                    'cat'   => ['ACP_ZP_BANLIST_TITLE'],
                ],
            ],
        ];
    }
}
