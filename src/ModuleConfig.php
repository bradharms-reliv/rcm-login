<?php

namespace RcmLogin;

use RcmLogin\Controller\LoginFormSubmitHandler;
use RcmLogin\Validator\RedirectValidator;
use RcmUser\Service\RcmUserService;
use Zend\EventManager\EventManager;

class ModuleConfig
{

    public function __invoke()
    {
        return [
            'dependencies' => [
                'config_factories' => [
                    LoginFormSubmitHandler::class => [
                        'arguments' => [
                            RcmUserService::class,
                            'EventManager',
                        ]
                    ]
                ]
            ],
            'routes' => [
                [
                    'path' => '/rcm-login/login-form-submit-handler',
                    'middleware' => LoginFormSubmitHandler::class,
                    'allowed_methods' => ['POST'],
                ],
            ]
        ];
    }
}
