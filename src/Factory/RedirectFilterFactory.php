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

use RcmLogin\Filter\RedirectFilter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RedirectFilterFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $filter = new RedirectFilter(
            $serviceLocator->get('RcmLogin\Validator\RedirectValidator')
        );

        return $filter;
    }
}
