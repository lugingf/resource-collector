<?php

return
    [
        'paths' => [
            'migrations' => '%%PHINX_CONFIG_DIR%%/migration'
        ],
        'environments' => [
            'default_migration_table' => 'phinxlog',
            'default_database' => 'development',
            'production' => [
                'adapter' => 'mysql',
                'host' => getenv('MYSQL_HOST'),
                'name' => 'resourcecollector',
                'user' => 'resourcecollector',
                'pass' => getenv('MYSQL_PASSWORD'),
                'port' => getenv('MYSQL_PORT'),
                'charset' => 'utf8',
            ],
            'testing' => [
                'adapter' => 'sqlite',
                'name' => './db',
                'charset' => 'utf8',
            ],
        ],
        'version_order' => 'creation'
    ];
