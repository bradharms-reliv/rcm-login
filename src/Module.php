<?php

/**
 * Module Config For ZF2
 *
 * PHP version 5.3
 *
 * LICENSE: No License yet
 *
 * @category  Reliv
 * @author    Rod McNew <rmcnew@relivinc.com>
 * @copyright 2012 Reliv International
 * @license   License.txt New BSD License
 * @version   GIT: <git_id>
 */

namespace RcmLogin;

use Zend\EventManager\EventInterface;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\MvcEvent;
use Zend\Console\Request as ConsoleRequest;

/**
 * ZF2 Module Config.  Required by ZF2
 *
 * ZF2 requires a Module.php file to load up all the Module Dependencies.  This
 * file has been included as part of the ZF2 standards.
 *
 * @category  Reliv
 * @author    Rod McNew <rmcnew@relivinc.com>
 * @copyright 2012 Reliv International
 * @license   License.txt New BSD License
 * @version   Release: 1.0
 */
class Module
{
    /**
     * getConfig() is a requirement for all Modules in ZF2.  This
     * function is included as part of that standard.  See Docs on ZF2 for more
     * information.
     *
     * @return array Returns array to be used by the ZF2 Module Manager
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * Bootstrap For Login.
     *
     * @param MvcEvent $event Zend MVC Event
     *
     * @return null
     */
    public function onBootstrap(MvcEvent $event)
    {
        $serviceManager = $event->getApplication()->getServiceManager();

        //Add Login Event Listener
        $loginEventListener = $serviceManager->get(
            'RcmLogin\EventListener\Login'
        );

        /** @var \Zend\EventManager\EventManager $eventManager */
        $eventManager = $event->getApplication()->getEventManager()->getSharedManager();

        // Check for redirects from the CMS
        $eventManager->attach(
            'Zend\Mvc\Controller\AbstractActionController',
            'LoginSuccessEvent',
            [$loginEventListener, 'loginSuccess'],
            10000
        );
    }
}
