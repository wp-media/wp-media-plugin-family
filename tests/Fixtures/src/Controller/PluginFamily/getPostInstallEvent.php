<?php

return [
    'testShouldReturnEmptyArrayIfProperGetParamAreNotSet' => [
        'config' => [
            'get_params' => [],
        ],
        'expected' => [],
    ],
    'testShouldReturnEmptyArrayIfActionIsNotValid' => [
        'config' => [
            'get_params' => [
                'action' => 'plugin_family_install_some-wierd-plugin',
                '_wpnonce' => '9a68f00b8d',
                'plugin_to_install' => 'some-wierd-plugin',
            ],
        ],
        'expected' => [],
    ],
    'testShouldReturnExpected' => [
        'config' => [
            'get_params' => [
                'action' => 'plugin_family_install_wp-rocket',
                '_wpnonce' => '9a68f00b8d',
                'plugin_to_install' => 'wp-rocket',
            ],
        ],
        'expected' => [
            'admin_post_plugin_family_install_wp-rocket' => 'install_activate',
        ],
    ],
];