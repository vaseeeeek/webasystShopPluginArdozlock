<?php

return [
    'shop_ardozlock_buyers' => [
        'id' => ['int', 11, 'null' => 0, 'autoincrement' => 1],
        'name' => ['varchar', 255, 'null' => 0],
        'email' => ['varchar', 255, 'null' => 0],
        ':keys' => [
            'PRIMARY' => ['id'], // Primary key definition
        ],
    ],
    'shop_ardozlock_blocked_pages' => [
        'id' => ['int', 11, 'null' => 0, 'autoincrement' => 1],
        'buyer_id' => ['int', 11, 'null' => 0],
        'page_id' => ['int', 11, 'null' => 0],
        'page_type' => ['enum', "'infopage','category'", 'null' => 0],
        'application_id' => ['varchar', 50, 'null' => 0], 
        ':keys' => [
            'PRIMARY' => ['id'],
        ],
    ],
    'shop_ardozlock_global_blocked_pages' => [
        'id' => ['int', 11, 'null' => 0, 'autoincrement' => 1],
        'page_id' => ['int', 11, 'null' => 0],
        'page_type' => ['enum', "'infopage','category'", 'null' => 0],
        'application_id' => ['varchar', 50, 'null' => 0], 
        ':keys' => [
            'PRIMARY' => ['id'],
        ],
    ],
];
