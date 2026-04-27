<?php
/**
 * ZP BanList — Event Listener
 *
 * @package    marcozp/zp_banlist
 * @license    GPL-2.0-only
 */

namespace marcozp\zp_banlist\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class main_listener implements EventSubscriberInterface
{
    protected $template;
    protected $language;
    protected $config;
    protected $helper;
    protected $user;

    public function __construct(
        \phpbb\template\template $template,
        \phpbb\language\language $language,
        \phpbb\config\config $config,
        \phpbb\controller\helper $helper,
        \phpbb\user $user
    )
    {
        $this->template = $template;
        $this->language = $language;
        $this->config   = $config;
        $this->helper   = $helper;
        $this->user     = $user;
    }

    static public function getSubscribedEvents()
    {
        return [
            'core.user_setup'  => 'load_language',
            'core.page_header' => 'inject_banlist_url',
        ];
    }

    public function load_language($event)
    {
        $lang_set_ext = $event['lang_set_ext'];
        $lang_set_ext[] = [
            'ext_name' => 'marcozp/zp_banlist',
            'lang_set' => 'common',
        ];
        $event['lang_set_ext'] = $lang_set_ext;
    }

    public function inject_banlist_url($event)
    {
        $this->template->assign_vars([
            'U_ZP_BANLIST'       => $this->helper->route('marcozp_zp_banlist_page'),
            'S_ZP_BANLIST_SHOW'  => ($this->user->data['user_id'] != ANONYMOUS),
        ]);
    }
}
