<?php
/**
 * @category  RCM
 * @author    James Jervis <jjervis@relivinc.com>
 * @copyright 2012 Reliv International
 * @license   License.txt New BSD License
 * @version   GIT: reliv
 * @link      http://ci.reliv.com/confluence
 */

namespace RcmLogin\Factory;

use RcmLogin\Controller\PluginController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PluginControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $controllerMgr)
    {
        /** @var \Zend\Mvc\Controller\ControllerManager $cm For IDE */
        $cm = $controllerMgr;

        /** @var ServiceLocatorInterface $serviceLocator */
        $serviceLocator = $cm->getServiceLocator();

        $controller = new PluginController(
            $serviceLocator->get('config'),
            $serviceLocator->get('RcmUser\Service\RcmUserService')
        );
        return $controller;
    }
}