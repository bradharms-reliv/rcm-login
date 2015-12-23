<?php

/**
 * ZF2 Plugin Config file
 *
 * This file contains all the configuration for the Module as defined by ZF2.
 * See the docs for ZF2 for more information.
 *
 * PHP version 5.3
 *
 * LICENSE: No License yet
 *
 * @category  Reliv
 * @author    Westin Shafer <wshafer@relivinc.com>
 * @copyright 2012 Reliv International
 * @license   License.txt New BSD License
 * @version   GIT: <git_id>
 */

return [

    'rcmPlugin' => [
        'RcmLogin' => [
            'type' => 'Common',
            'display' => 'Login Area',
            'tooltip' => 'Adds login area to page',
            'icon' => '',
            'requireHttps' => true,
            'postLoginRedirectUrl' => '/login-home',
            'defaultInstanceConfig' => include __DIR__ .
                    '/defaultInstanceConfig.php',
            'canCache' => false,
            'uncategorizedErrorRedirect' => "/account-issue",
            'defaultSuccessRedirect' => '/'
        ],
        'RcmResetPassword' => [
            'type' => 'Common',
            'display' => 'Reset Password',
            'tooltip' => 'Reset Password',
            'icon' => '',
            'defaultInstanceConfig' => include
                __DIR__ . '/resetPasswordDefaultInstanceConfig.php',
            'canCache' => false,
        ],
        'RcmCreateNewPassword' => [
            'type' => 'Common',
            'display' => 'Create New Password',
            'tooltip' => 'Create New Password',
            'icon' => '',
            'defaultInstanceConfig' => include
                __DIR__ . '/createPasswordDefaultInstanceConfig.php',
            'canCache' => false,
        ],
    ],

    'doctrine' => [
        'driver' => [
            'RcmLogin' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/Entity'
                ]
            ],
            'orm_default' => [
                'drivers' => [
                    'RcmLogin' => 'RcmLogin'
                ]
            ]
        ]
    ],

    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'asset_manager' => [
        'resolver_configs' => [
            'aliases' => [
                'modules/rcm-login/' => __DIR__ . '/../public/',
            ],
            'collections' => [
                'modules/rcm-admin/admin.js' => [
                    'modules/rcm-login/rcm-login-edit.js',
                    'modules/rcm-login/rcm-reset-password-edit.js',
                    'modules/rcm-login/rcm-create-new-password-edit.js',
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            'RcmLogin' => 'RcmLogin\Factory\PluginControllerFactory',
            'RcmResetPassword' => 'RcmLogin\Factory\ResetPasswordPluginControllerFactory',
            'RcmCreateNewPassword' => 'RcmLogin\Factory\CreatePasswordPluginControllerFactory',
        ],
    ],

    'service_manager' => [
        'factories' => [
            'RcmLogin\EventListener\Login' => 'RcmLogin\Factory\LoginEventListenerFactory',
        ],

        'invokables' => [
            'RcmLogin\Filter\RedirectFilter' => 'RcmLogin\Filter\RedirectFilter'
        ],
    ],
];
