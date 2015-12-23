<?php
/**
 * @category  RCM
 * @author    Westin Shafer <wshafer@relivinc.com>
 * @copyright 2012 Reliv International
 * @license   License.txt New BSD License
 * @version   GIT: reliv
 * @link      http://ci.reliv.com/confluence
 */

namespace RcmLogin\Factory;

use RcmLogin\EventListener\Login;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class LoginEventListenerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $eventListener = new Login(
            $serviceLocator->get('RcmLogin\Filter\RedirectFilter')
        );

        return $eventListener;
    }
}
