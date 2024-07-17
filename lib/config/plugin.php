<?php
return array (
  'name' => 'Блокировка страниц сайта',
  'img' => 'img/ardozlock.gif',
  'version' => '1.0.0',
  'vendor' => '123456',
  'handlers' => 
  array (
    'backend_menu' => 'backendMenu',
		'rights.config' => 'rightsConfigHandler',
  ),
  'frontend' => true,
	'custom_settings' => true,
);
