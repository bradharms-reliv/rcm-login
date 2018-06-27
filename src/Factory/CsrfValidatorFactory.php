<?php

namespace RcmLogin\Factory;

use Rcm\Service\SessionManager;
use Zend\Session\Container;
use Zend\Validator\Csrf;

class CsrfValidatorFactory
{
    public function __invoke($serviceContainer)
    {
        $sessionContainer = new Container(self::class, $serviceContainer->get(SessionManager::class));

        return new Csrf([
            'session' => $sessionContainer,
            'timeout' => $serviceContainer->get('config')['rcmPlugin']['RcmLogin']['csrfTimeoutSeconds']
        ]);
    }
}
