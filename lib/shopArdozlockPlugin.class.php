<?php

class shopArdozlockPlugin extends shopPlugin
{
	const RIGHT_BACKEND = 'ardozlock.backend';

    public function backendMenu()
    {
		if (wa()->getUser()->getRights('shop', self::RIGHT_BACKEND) == 0)
		{
			return array();
		}
        
        return array(
            'core_li' => '<li class="no-tab"><a href="?plugin=ardozlock&action=closedcatlist">' . _wp('Закрытые категории') . '</a></li>',
        );
    }

	public function rightsConfigHandler(waRightConfig $config)
	{
		$config->addItem('ardozlock_header', 'Закрытые категории', 'header', array('cssclass' => 'c-access-subcontrol-header', 'tag' => 'div'));
		$config->addItem(self::RIGHT_BACKEND, 'Доступ к списку', 'checkbox', array('cssclass' => 'c-access-subcontrol-item'));
	}
}
