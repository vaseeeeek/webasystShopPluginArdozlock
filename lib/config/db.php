<?php

return [
    'ardozlock_links' => [
        'id' => ['int', 11, 'null' => 0, 'autoincrement' => 1],
        'unique_hash' => ['varchar', 255, 'null' => 0],
        'expires_at' => ['datetime', 'null' => 0],
        'created_at' => ['datetime', 'null' => 0],
        'email' => ['varchar', 255, 'null' => 0],
        'company_name' => ['varchar', 255, 'null' => 0],
        'type' => ['varchar', 255, 'null' => 0],
        ':keys' => [
            'PRIMARY' => ['id'], // Primary key definition
        ],
    ],
    'ardozlock_link_categories' => [
        'link_id' => ['int', 11, 'null' => 0],
        'category_id' => ['int', 11, 'null' => 0],
        ':keys' => [
            'PRIMARY' => ['link_id', 'category_id'],
            'link_id' => 'link_id',
            'category_id' => 'category_id',
        ],
    ],
    'ardozlock_link_shoppage' => [
        'link_id' => ['int', 11, 'null' => 0],
        'page_id' => ['int', 11, 'null' => 0],
        ':keys' => [
            'PRIMARY' => ['link_id', 'page_id'],
            'link_id' => 'link_id',
            'page_id' => 'page_id',
        ],
    ]    
];
